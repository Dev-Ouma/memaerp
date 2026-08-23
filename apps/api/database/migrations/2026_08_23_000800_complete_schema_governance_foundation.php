<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Completes the cross-cutting schema conventions in PLAN/03-DATA-ARCHITECTURE.md.
 *
 * This is deliberately forward-only from the existing migration baseline. Existing migrations
 * may already have run outside this workstation and must remain immutable.
 */
return new class extends Migration
{
    /** @var list<string> */
    private array $governedSchemas = [
        'iam', 'institution', 'curriculum', 'course', 'admission', 'student',
        'enrollment', 'finance', 'examination', 'graduation', 'hr',
        'procurement', 'research', 'cms', 'analytics', 'documents',
    ];

    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS documents');

        DB::statement(<<<'SQL'
            CREATE TABLE documents.files (
                id                  uuid        PRIMARY KEY,
                institution_id      uuid        NOT NULL REFERENCES institution.institutions(id),
                owner_type          varchar(128) NOT NULL,
                owner_id            uuid        NOT NULL,
                classification      varchar(32) NOT NULL DEFAULT 'CONFIDENTIAL',
                purpose             varchar(128) NOT NULL,
                original_name       text        NOT NULL,
                media_type          varchar(255) NOT NULL,
                size_bytes          bigint      NOT NULL CHECK (size_bytes >= 0),
                storage_disk        varchar(32) NOT NULL,
                storage_key         text        NOT NULL,
                checksum_sha256     char(64)    NOT NULL,
                malware_status      varchar(32) NOT NULL DEFAULT 'PENDING',
                retention_until     date        NULL,
                legal_hold_at       timestamptz NULL,
                created_by          uuid        NULL REFERENCES iam.users(id) ON DELETE SET NULL,
                updated_by          uuid        NULL REFERENCES iam.users(id) ON DELETE SET NULL,
                created_at          timestamptz NOT NULL DEFAULT now(),
                updated_at          timestamptz NOT NULL DEFAULT now(),
                deleted_at          timestamptz NULL,
                CONSTRAINT files_classification_check CHECK (
                    classification IN ('PUBLIC', 'INTERNAL', 'CONFIDENTIAL', 'RESTRICTED')
                ),
                CONSTRAINT files_malware_status_check CHECK (
                    malware_status IN ('PENDING', 'CLEAN', 'QUARANTINED', 'REJECTED')
                ),
                CONSTRAINT files_storage_key_unique UNIQUE (institution_id, storage_disk, storage_key)
            )
        SQL);

        DB::statement(<<<'SQL'
            CREATE TABLE documents.file_versions (
                id                  uuid        PRIMARY KEY,
                institution_id      uuid        NOT NULL REFERENCES institution.institutions(id),
                file_id             uuid        NOT NULL REFERENCES documents.files(id) ON DELETE RESTRICT,
                version_number      integer     NOT NULL CHECK (version_number > 0),
                storage_key         text        NOT NULL,
                size_bytes          bigint      NOT NULL CHECK (size_bytes >= 0),
                checksum_sha256     char(64)    NOT NULL,
                created_by          uuid        NULL REFERENCES iam.users(id) ON DELETE SET NULL,
                updated_by          uuid        NULL REFERENCES iam.users(id) ON DELETE SET NULL,
                created_at          timestamptz NOT NULL DEFAULT now(),
                updated_at          timestamptz NOT NULL DEFAULT now(),
                CONSTRAINT file_versions_number_unique UNIQUE (file_id, version_number),
                CONSTRAINT file_versions_storage_key_unique UNIQUE (
                    institution_id, storage_key
                )
            )
        SQL);

        DB::statement('CREATE INDEX files_owner_idx
            ON documents.files (institution_id, owner_type, owner_id)
            WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX files_retention_idx
            ON documents.files (institution_id, retention_until)
            WHERE retention_until IS NOT NULL AND legal_hold_at IS NULL');
        DB::statement('CREATE INDEX file_versions_file_idx
            ON documents.file_versions (institution_id, file_id, version_number DESC)');

        $schemaList = implode(', ', array_map(
            static fn (string $schema): string => DB::getPdo()->quote($schema),
            $this->governedSchemas,
        ));

        // Existing rows remain valid because actor columns are nullable. New application writes
        // populate them through the request context; database imports may leave them null only
        // when no human actor exists.
        DB::unprepared(<<<SQL
            DO \$governance\$
            DECLARE
                target record;
                constraint_name text;
            BEGIN
                FOR target IN
                    SELECT table_schema, table_name
                    FROM information_schema.tables
                    WHERE table_type = 'BASE TABLE'
                      AND table_schema IN ({$schemaList})
                      AND table_schema <> 'documents'
                      AND NOT (table_schema = 'audit' AND table_name LIKE 'activity_log%')
                LOOP
                    EXECUTE format(
                        'ALTER TABLE %I.%I ADD COLUMN IF NOT EXISTS created_by uuid NULL',
                        target.table_schema,
                        target.table_name
                    );
                    EXECUTE format(
                        'ALTER TABLE %I.%I ADD COLUMN IF NOT EXISTS updated_by uuid NULL',
                        target.table_schema,
                        target.table_name
                    );

                    constraint_name := substr(
                        target.table_schema || '_' || target.table_name || '_created_by_fk',
                        1,
                        63
                    );
                    IF NOT EXISTS (
                        SELECT 1 FROM pg_constraint WHERE conname = constraint_name
                    ) THEN
                        EXECUTE format(
                            'ALTER TABLE %I.%I ADD CONSTRAINT %I FOREIGN KEY (created_by) REFERENCES iam.users(id) ON DELETE SET NULL',
                            target.table_schema,
                            target.table_name,
                            constraint_name
                        );
                    END IF;

                    constraint_name := substr(
                        target.table_schema || '_' || target.table_name || '_updated_by_fk',
                        1,
                        63
                    );
                    IF NOT EXISTS (
                        SELECT 1 FROM pg_constraint WHERE conname = constraint_name
                    ) THEN
                        EXECUTE format(
                            'ALTER TABLE %I.%I ADD CONSTRAINT %I FOREIGN KEY (updated_by) REFERENCES iam.users(id) ON DELETE SET NULL',
                            target.table_schema,
                            target.table_name,
                            constraint_name
                        );
                    END IF;
                END LOOP;
            END
            \$governance\$;
        SQL);
    }

    public function down(): void
    {
        $schemaList = implode(', ', array_map(
            static fn (string $schema): string => DB::getPdo()->quote($schema),
            $this->governedSchemas,
        ));

        DB::unprepared(<<<SQL
            DO \$governance\$
            DECLARE
                target record;
                constraint_name text;
            BEGIN
                FOR target IN
                    SELECT table_schema, table_name
                    FROM information_schema.tables
                    WHERE table_type = 'BASE TABLE'
                      AND table_schema IN ({$schemaList})
                      AND table_schema <> 'documents'
                      AND NOT (table_schema = 'audit' AND table_name LIKE 'activity_log%')
                LOOP
                    constraint_name := substr(
                        target.table_schema || '_' || target.table_name || '_created_by_fk',
                        1,
                        63
                    );
                    EXECUTE format(
                        'ALTER TABLE %I.%I DROP CONSTRAINT IF EXISTS %I',
                        target.table_schema,
                        target.table_name,
                        constraint_name
                    );

                    constraint_name := substr(
                        target.table_schema || '_' || target.table_name || '_updated_by_fk',
                        1,
                        63
                    );
                    EXECUTE format(
                        'ALTER TABLE %I.%I DROP CONSTRAINT IF EXISTS %I',
                        target.table_schema,
                        target.table_name,
                        constraint_name
                    );

                    EXECUTE format(
                        'ALTER TABLE %I.%I DROP COLUMN IF EXISTS created_by, DROP COLUMN IF EXISTS updated_by',
                        target.table_schema,
                        target.table_name
                    );
                END LOOP;
            END
            \$governance\$;
        SQL);

        DB::statement('DROP TABLE IF EXISTS documents.file_versions');
        DB::statement('DROP TABLE IF EXISTS documents.files');
        DB::statement('DROP SCHEMA IF EXISTS documents');
    }
};

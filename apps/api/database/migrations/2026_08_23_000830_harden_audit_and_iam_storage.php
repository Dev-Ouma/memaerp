<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE TABLE iam.personal_access_tokens (
                id              bigserial     PRIMARY KEY,
                institution_id  uuid          NOT NULL REFERENCES institution.institutions(id) ON DELETE CASCADE,
                tokenable_type  varchar(255)  NOT NULL,
                tokenable_id    uuid          NOT NULL,
                name            text          NOT NULL,
                token           varchar(64)   NOT NULL UNIQUE,
                abilities       text          NULL,
                last_used_at    timestamptz   NULL,
                expires_at      timestamptz   NULL,
                created_by      uuid          NULL REFERENCES iam.users(id) ON DELETE SET NULL,
                updated_by      uuid          NULL REFERENCES iam.users(id) ON DELETE SET NULL,
                created_at      timestamptz   NULL,
                updated_at      timestamptz   NULL
            )
        SQL);

        DB::statement('CREATE INDEX personal_access_tokens_tokenable_idx
            ON iam.personal_access_tokens (tokenable_type, tokenable_id)');
        DB::statement('CREATE INDEX personal_access_tokens_expiry_idx
            ON iam.personal_access_tokens (institution_id, expires_at)
            WHERE expires_at IS NOT NULL');

        // Expand/contract migration: retain the legacy public table until all deployed versions
        // use the IAM model, but copy any existing tokens without invalidating sessions.
        DB::statement(<<<'SQL'
            INSERT INTO iam.personal_access_tokens (
                id, institution_id, tokenable_type, tokenable_id, name, token,
                abilities, last_used_at, expires_at, created_at, updated_at
            )
            SELECT
                pat.id,
                users.institution_id,
                pat.tokenable_type,
                pat.tokenable_id,
                pat.name,
                pat.token,
                pat.abilities,
                pat.last_used_at,
                pat.expires_at,
                pat.created_at,
                pat.updated_at
            FROM public.personal_access_tokens pat
            JOIN iam.users users ON users.id = pat.tokenable_id
            ON CONFLICT (token) DO NOTHING
        SQL);

        DB::statement("SELECT setval(
            pg_get_serial_sequence('iam.personal_access_tokens', 'id'),
            GREATEST(COALESCE((SELECT MAX(id) FROM iam.personal_access_tokens), 1), 1),
            true
        )");

        DB::statement('CREATE INDEX activity_log_old_values_gin_idx
            ON audit.activity_log USING gin (old_values jsonb_path_ops)');
        DB::statement('CREATE INDEX activity_log_new_values_gin_idx
            ON audit.activity_log USING gin (new_values jsonb_path_ops)');

        DB::statement('REVOKE UPDATE, DELETE, TRUNCATE ON audit.activity_log FROM PUBLIC');

        DB::statement(<<<'SQL'
            CREATE OR REPLACE VIEW audit.partition_health AS
            SELECT
                child_ns.nspname AS schema_name,
                child.relname AS partition_name,
                pg_get_expr(child.relpartbound, child.oid) AS partition_bounds,
                pg_total_relation_size(child.oid) AS total_bytes
            FROM pg_inherits inheritance
            JOIN pg_class parent ON parent.oid = inheritance.inhparent
            JOIN pg_namespace parent_ns ON parent_ns.oid = parent.relnamespace
            JOIN pg_class child ON child.oid = inheritance.inhrelid
            JOIN pg_namespace child_ns ON child_ns.oid = child.relnamespace
            WHERE parent_ns.nspname = 'audit'
              AND parent.relname = 'activity_log'
            ORDER BY child.relname
        SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS audit.partition_health');
        DB::statement('DROP INDEX IF EXISTS audit.activity_log_new_values_gin_idx');
        DB::statement('DROP INDEX IF EXISTS audit.activity_log_old_values_gin_idx');
        DB::statement('DROP TABLE IF EXISTS iam.personal_access_tokens');
    }
};

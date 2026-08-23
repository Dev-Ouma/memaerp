<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * MOD-00-05 — Make the governance-column contract re-runnable.
 *
 * Migration 000800 swept every table that existed at the time and gave it created_by,
 * updated_by, created_at and updated_at. That sweep was a one-shot DO block, so every table
 * created by a later migration silently missed it — eight of them had, by the time this was
 * written, and DatabaseArchitectureTest was the only thing that noticed.
 *
 * The fix is to promote the sweep from a statement into a callable function. A migration that
 * adds tables to a governed schema now ends with one line:
 *
 *     DB::statement('SELECT public.enforce_governance_columns()');
 *
 * and the test stops being the only enforcement. The function is idempotent by construction —
 * ADD COLUMN IF NOT EXISTS, and foreign keys checked by the column they cover rather than by
 * name, so a table that already carries a differently-named actor FK is left alone.
 */
return new class extends Migration
{
    /**
     * The audit log is excluded because it is append-only and partitioned: it records who acted
     * in its own actor_id column, and its partitions are created by a scheduled command that
     * knows nothing about this contract.
     *
     * @var list<string>
     */
    private array $governedSchemas = [
        'iam', 'institution', 'curriculum', 'course', 'admission', 'student',
        'enrollment', 'finance', 'examination', 'graduation', 'hr',
        'procurement', 'research', 'cms', 'analytics', 'documents',
    ];

    public function up(): void
    {
        $schemaList = implode(', ', array_map(
            static fn (string $schema): string => DB::getPdo()->quote($schema),
            $this->governedSchemas,
        ));

        DB::unprepared(<<<SQL
            CREATE OR REPLACE FUNCTION public.enforce_governance_columns()
            RETURNS integer
            LANGUAGE plpgsql
            AS \$governance\$
            DECLARE
                target      record;
                actor_col   text;
                fk_name     text;
                touched     integer := 0;
            BEGIN
                FOR target IN
                    SELECT table_schema, table_name
                    FROM information_schema.tables
                    WHERE table_type = 'BASE TABLE'
                      AND table_schema IN ({$schemaList})
                      AND NOT (table_schema = 'audit' AND table_name LIKE 'activity_log%')
                LOOP
                    -- Lifecycle timestamps. NOT NULL with a default is safe on a populated table:
                    -- PostgreSQL 11+ stores the default in the catalogue rather than rewriting rows.
                    EXECUTE format(
                        'ALTER TABLE %I.%I
                            ADD COLUMN IF NOT EXISTS created_at timestamptz NOT NULL DEFAULT now(),
                            ADD COLUMN IF NOT EXISTS updated_at timestamptz NOT NULL DEFAULT now()',
                        target.table_schema, target.table_name
                    );

                    FOREACH actor_col IN ARRAY ARRAY['created_by', 'updated_by']
                    LOOP
                        -- Nullable on purpose: system-originated rows (imports, scheduled jobs,
                        -- migrations) have no human actor, and a NOT NULL here would force a
                        -- sentinel user account that would then appear in audit trails as a person.
                        EXECUTE format(
                            'ALTER TABLE %I.%I ADD COLUMN IF NOT EXISTS %I uuid NULL',
                            target.table_schema, target.table_name, actor_col
                        );

                        -- Checked by covered column, not by constraint name: documents.files
                        -- already carries files_created_by_fkey from its own migration, and
                        -- adding a second identical FK would double every write's check cost.
                        IF NOT EXISTS (
                            SELECT 1
                            FROM pg_constraint con
                            JOIN pg_class rel ON rel.oid = con.conrelid
                            JOIN pg_namespace nsp ON nsp.oid = rel.relnamespace
                            JOIN pg_attribute att
                              ON att.attrelid = con.conrelid
                             AND att.attnum = ANY (con.conkey)
                            WHERE con.contype = 'f'
                              AND nsp.nspname = target.table_schema
                              AND rel.relname = target.table_name
                              AND att.attname = actor_col
                              AND array_length(con.conkey, 1) = 1
                        ) THEN
                            fk_name := substr(
                                target.table_schema || '_' || target.table_name || '_' || actor_col || '_fk',
                                1, 63
                            );

                            -- ON DELETE SET NULL, not RESTRICT: deleting a user account must never
                            -- be blocked by the thousands of rows they touched. The audit log holds
                            -- the durable attribution; this column is a convenience join.
                            EXECUTE format(
                                'ALTER TABLE %I.%I ADD CONSTRAINT %I
                                 FOREIGN KEY (%I) REFERENCES iam.users(id) ON DELETE SET NULL',
                                target.table_schema, target.table_name, fk_name, actor_col
                            );

                            touched := touched + 1;
                        END IF;
                    END LOOP;
                END LOOP;

                RETURN touched;
            END
            \$governance\$;
        SQL);

        DB::statement("COMMENT ON FUNCTION public.enforce_governance_columns() IS
            'Idempotent sweep: gives every governed table created_by, updated_by, created_at, updated_at. Call at the end of any migration that creates tables.'");

        // Catch up the tables that were created after migration 000800 ran.
        DB::statement('SELECT public.enforce_governance_columns()');
    }

    public function down(): void
    {
        // The function goes; the columns stay. They were established by migration 000800 as part
        // of the governance contract, application code writes to them, and dropping them here
        // would destroy actor attribution on tables this migration merely caught up.
        DB::statement('DROP FUNCTION IF EXISTS public.enforce_governance_columns()');
    }
};

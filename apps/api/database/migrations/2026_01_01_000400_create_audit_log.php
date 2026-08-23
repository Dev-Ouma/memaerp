<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * MOD-00-04 — Append-only audit governance.
 *
 * The immutability of this table is enforced by a PostgreSQL trigger, not by application
 * discipline. An UPDATE or DELETE against audit.activity_log raises an exception regardless of
 * which credential issues it, so a compromised application account cannot rewrite history.
 *
 * Partitioned by month: audit volume is the fastest-growing data in any ERP, and monthly
 * partitions make the 7-year retention policy a detach-and-archive operation rather than a
 * DELETE that would be blocked by the trigger anyway.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            CREATE TABLE audit.activity_log (
                id                uuid          NOT NULL,
                institution_id    uuid          NOT NULL,
                occurred_at       timestamptz   NOT NULL DEFAULT now(),

                -- Who
                actor_id          uuid          NULL,
                actor_label       text          NULL,
                on_behalf_of_id   uuid          NULL,
                impersonation_id  uuid          NULL,

                -- What
                event             varchar(64)   NOT NULL,
                auditable_type    varchar(128)  NOT NULL,
                auditable_id      uuid          NULL,
                module            varchar(64)   NOT NULL,

                -- The change itself
                old_values        jsonb         NULL,
                new_values        jsonb         NULL,
                changed_columns   text[]        NULL,

                -- Why and from where
                reason            text          NULL,
                ip_address        inet          NULL,
                user_agent        text          NULL,
                correlation_id    uuid          NULL,
                request_id        uuid          NULL,

                PRIMARY KEY (id, occurred_at)
            ) PARTITION BY RANGE (occurred_at)
        ');

        // Bootstrap partitions. A scheduled command creates each month ahead of time;
        // this default catches anything that would otherwise fail to route.
        $start = new DateTimeImmutable('first day of this month');
        for ($i = -1; $i <= 12; $i++) {
            $from = $start->modify("{$i} month");
            $to = $from->modify('+1 month');
            $name = 'activity_log_'.$from->format('Y_m');

            DB::statement(sprintf(
                'CREATE TABLE IF NOT EXISTS audit.%s PARTITION OF audit.activity_log
                 FOR VALUES FROM (%s) TO (%s)',
                $name,
                "'".$from->format('Y-m-d')."'",
                "'".$to->format('Y-m-d')."'"
            ));
        }

        DB::statement('CREATE TABLE IF NOT EXISTS audit.activity_log_default
            PARTITION OF audit.activity_log DEFAULT');

        DB::statement('CREATE INDEX activity_log_auditable_idx
            ON audit.activity_log (auditable_type, auditable_id, occurred_at DESC)');
        DB::statement('CREATE INDEX activity_log_actor_idx
            ON audit.activity_log (actor_id, occurred_at DESC)');
        DB::statement('CREATE INDEX activity_log_correlation_idx
            ON audit.activity_log (correlation_id)');
        DB::statement('CREATE INDEX activity_log_module_idx
            ON audit.activity_log (institution_id, module, occurred_at DESC)');

        // The enforcement. This is the whole point of the module.
        DB::unprepared('
            CREATE OR REPLACE FUNCTION audit.prevent_mutation()
            RETURNS trigger
            LANGUAGE plpgsql
            AS $$
            BEGIN
                RAISE EXCEPTION
                    USING MESSAGE = format(
                        \'audit.activity_log is append-only; %s is not permitted\', TG_OP
                    ),
                    ERRCODE = \'42501\';
            END;
            $$;
        ');

        DB::statement('
            CREATE TRIGGER activity_log_no_update
            BEFORE UPDATE ON audit.activity_log
            FOR EACH ROW EXECUTE FUNCTION audit.prevent_mutation()
        ');

        DB::statement('
            CREATE TRIGGER activity_log_no_delete
            BEFORE DELETE ON audit.activity_log
            FOR EACH ROW EXECUTE FUNCTION audit.prevent_mutation()
        ');

        DB::statement("COMMENT ON TABLE audit.activity_log IS
            'Append-only. UPDATE and DELETE raise 42501 via trigger. Retention 7 years by partition detach.'");
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS activity_log_no_delete ON audit.activity_log');
        DB::statement('DROP TRIGGER IF EXISTS activity_log_no_update ON audit.activity_log');
        DB::statement('DROP FUNCTION IF EXISTS audit.prevent_mutation()');
        DB::statement('DROP TABLE IF EXISTS audit.activity_log CASCADE');
    }
};

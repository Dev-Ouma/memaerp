<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Database-level enforcement of the invariants the module must never violate.
 *
 * Purpose: application code is one bug away from allowing a second authoritative payment, an edit to a
 * frozen roll, or a rewritten audit row. Those invariants therefore live in PostgreSQL, where imports,
 * background jobs and a future second service are equally bound by them.
 *
 * Everything here is reversible — the migration only creates indexes, checks and triggers.
 */
return new class extends Migration
{
    private function isPostgres(): bool
    {
        return Schema::getConnection()->getDriverName() === 'pgsql';
    }

    public function up(): void
    {
        if (! $this->isPostgres()) {
            // The invariants below are PostgreSQL-specific. Any other driver is a developer convenience
            // only; the module is not supported on it and the test suite runs on PostgreSQL.
            return;
        }

        // --- Payments -------------------------------------------------------------------------
        // At most one authoritative settled application fee per application. This is the constraint the
        // KES 1,000 submission gate ultimately rests on.
        DB::statement(<<<'SQL'
            create unique index payment_transactions_authoritative_fee_unique
                on payment_transactions (admission_application_id)
                where is_authoritative_fee and status in ('PAID', 'WAIVED')
        SQL);

        DB::statement(<<<'SQL'
            create unique index payment_transactions_provider_ref_unique
                on payment_transactions (provider, provider_transaction_ref)
                where provider_transaction_ref is not null
        SQL);

        DB::statement('alter table payment_transactions add constraint payment_transactions_amount_positive check (amount > 0)');
        DB::statement("alter table payment_transactions add constraint payment_transactions_currency_iso check (currency ~ '^[A-Z]{3}$')");
        DB::statement('alter table payment_fee_setups add constraint payment_fee_setups_amount_positive check (amount > 0)');
        DB::statement("alter table payment_fee_setups add constraint payment_fee_setups_currency_iso check (currency ~ '^[A-Z]{3}$')");
        DB::statement('alter table payment_fee_setups add constraint payment_fee_setups_effective_range check (effective_to is null or effective_to >= effective_from)');
        DB::statement('alter table payment_receipts add constraint payment_receipts_amount_positive check (amount > 0)');
        DB::statement(<<<'SQL'
            create unique index payment_waivers_active_unique
                on payment_waivers (admission_application_id)
                where status = 'ACTIVE'
        SQL);

        // --- Applications ---------------------------------------------------------------------
        // Replace the blanket uniqueness with "one *live* application per applicant and offering,
        // unless a duplicate was explicitly authorised". Withdrawn or rejected applicants may reapply.
        DB::statement(<<<'SQL'
            do $$
            declare idx text;
            begin
                select indexname into idx
                  from pg_indexes
                 where tablename = 'admission_applications'
                   and indexdef like '%UNIQUE%'
                   and indexdef like '%applicant_profile_id%'
                   and indexdef like '%programme_offering_id%'
                 limit 1;
                if idx is not null then
                    execute format('alter table admission_applications drop constraint if exists %I', idx);
                    execute format('drop index if exists %I', idx);
                end if;
            end $$;
        SQL);

        DB::statement(<<<'SQL'
            create unique index admission_applications_live_per_offering_unique
                on admission_applications (applicant_profile_id, programme_offering_id)
                where deleted_at is null
                  and not duplicate_authorised
                  and status not in ('WITHDRAWN', 'REJECTED', 'CLOSED', 'DEFERRED')
        SQL);

        DB::statement(<<<'SQL'
            alter table admission_applications add constraint admission_applications_payment_status_enum
                check (payment_status in ('NOT_STARTED','INITIATED','PENDING','PAID','FAILED','CANCELLED','EXPIRED','REVERSED','REFUNDED','WAIVED'))
        SQL);

        DB::statement(<<<'SQL'
            alter table admission_applications add constraint admission_applications_status_enum
                check (status in ('LEAD','ACCOUNT_CREATED','EMAIL_VERIFIED','DRAFT','VALIDATION_FAILED','SUBMITTED',
                    'UNDER_REVIEW','INFO_REQUESTED','VERIFIED','SHORTLISTED','APPROVAL_PENDING','ADMITTED_CONDITIONAL',
                    'ADMITTED','WAITLISTED','ACCEPTED','READY_TO_ENROL','ENROLLED','REJECTED','DEFER_REQUESTED',
                    'DEFERRED','WITHDRAWN','REVOKED','CLOSED'))
        SQL);

        // A submitted application must carry the evidence that let it through the gate.
        DB::statement(<<<'SQL'
            alter table admission_applications add constraint admission_applications_submission_evidence
                check (
                    submitted_at is null
                    or (declarations_accepted and payment_status in ('PAID','WAIVED') and submitted_version_id is not null)
                )
        SQL);

        DB::statement('create index admission_applications_status_created_idx on admission_applications (status, created_at desc)');
        DB::statement('create index admission_applications_submitted_idx on admission_applications (submitted_at desc) where submitted_at is not null');
        DB::statement('create index admission_applications_form_data_gin on admission_applications using gin (form_data)');

        // --- Identity -------------------------------------------------------------------------
        DB::statement('create unique index users_email_normalised_unique on users (lower(email))');
        DB::statement(<<<'SQL'
            create unique index people_identity_hash_unique
                on people (identity_type, identity_number_hash)
                where identity_number_hash is not null and deleted_at is null
        SQL);

        // --- Immutable evidence ----------------------------------------------------------------
        // Application versions: the snapshot itself is frozen; only the supersession markers move.
        DB::statement(<<<'SQL'
            create or replace function admission_guard_application_version() returns trigger as $$
            begin
                if (tg_op = 'DELETE') then
                    raise exception 'application_versions rows are immutable evidence and cannot be deleted'
                        using errcode = '42501';
                end if;
                if (new.snapshot is distinct from old.snapshot
                    or new.checksum is distinct from old.checksum
                    or new.version is distinct from old.version
                    or new.admission_application_id is distinct from old.admission_application_id) then
                    raise exception 'a submitted application version cannot be rewritten'
                        using errcode = '42501';
                end if;
                return new;
            end;
            $$ language plpgsql;
        SQL);
        DB::statement(<<<'SQL'
            create trigger application_versions_immutable
                before update or delete on application_versions
                for each row execute function admission_guard_application_version();
        SQL);

        // Audit events: append-only, no exceptions.
        DB::statement(<<<'SQL'
            create or replace function admission_guard_audit_events() returns trigger as $$
            begin
                raise exception 'audit_events is append-only' using errcode = '42501';
            end;
            $$ language plpgsql;
        SQL);
        DB::statement(<<<'SQL'
            create trigger audit_events_append_only
                before update or delete on audit_events
                for each row execute function admission_guard_audit_events();
        SQL);

        // Frozen admission rolls: entries cannot be added, changed or removed.
        DB::statement(<<<'SQL'
            create or replace function admission_guard_frozen_roll_entries() returns trigger as $$
            declare frozen timestamptz;
            begin
                select frozen_at into frozen from admission_rolls
                 where id = coalesce(new.admission_roll_id, old.admission_roll_id);
                if frozen is not null then
                    raise exception 'admission roll % is frozen and its entries cannot be changed',
                        coalesce(new.admission_roll_id, old.admission_roll_id) using errcode = '42501';
                end if;
                return coalesce(new, old);
            end;
            $$ language plpgsql;
        SQL);
        DB::statement(<<<'SQL'
            create trigger admission_roll_entries_frozen_guard
                before insert or update or delete on admission_roll_entries
                for each row execute function admission_guard_frozen_roll_entries();
        SQL);

        // A frozen roll header may only be superseded, never edited.
        DB::statement(<<<'SQL'
            create or replace function admission_guard_frozen_roll() returns trigger as $$
            begin
                if (tg_op = 'DELETE') then
                    if old.frozen_at is not null then
                        raise exception 'a frozen admission roll cannot be deleted' using errcode = '42501';
                    end if;
                    return old;
                end if;
                if old.frozen_at is not null then
                    if (new.reference is distinct from old.reference
                        or new.query_snapshot is distinct from old.query_snapshot
                        or new.checksum is distinct from old.checksum
                        or new.total_entries is distinct from old.total_entries
                        or new.version is distinct from old.version
                        or new.frozen_at is distinct from old.frozen_at
                        or new.approved_by is distinct from old.approved_by) then
                        raise exception 'a frozen admission roll is immutable; issue a new version instead'
                            using errcode = '42501';
                    end if;
                end if;
                return new;
            end;
            $$ language plpgsql;
        SQL);
        DB::statement(<<<'SQL'
            create trigger admission_rolls_frozen_guard
                before update or delete on admission_rolls
                for each row execute function admission_guard_frozen_roll();
        SQL);

        // Submitted document evidence: the stored object reference and hash are frozen.
        DB::statement(<<<'SQL'
            create or replace function admission_guard_immutable_document() returns trigger as $$
            begin
                if (tg_op = 'DELETE') then
                    if old.is_immutable then
                        raise exception 'submitted document evidence cannot be deleted' using errcode = '42501';
                    end if;
                    return old;
                end if;
                if old.is_immutable and (
                       new.storage_path is distinct from old.storage_path
                    or new.sha256 is distinct from old.sha256
                    or new.size_bytes is distinct from old.size_bytes
                    or new.mime_type is distinct from old.mime_type) then
                    raise exception 'submitted document evidence cannot be replaced in place' using errcode = '42501';
                end if;
                return new;
            end;
            $$ language plpgsql;
        SQL);
        DB::statement(<<<'SQL'
            create trigger application_documents_immutable_guard
                before update or delete on application_documents
                for each row execute function admission_guard_immutable_document();
        SQL);

        // --- Generated evidence and verification ------------------------------------------------
        DB::statement(<<<'SQL'
            create unique index generated_documents_subject_version_unique
                on generated_documents (subject_type, subject_id, document_type, version)
        SQL);
        DB::statement(<<<'SQL'
            create unique index qr_verification_tokens_active_subject_unique
                on qr_verification_tokens (subject_type, subject_id)
                where status = 'ACTIVE'
        SQL);

        // --- Conversion --------------------------------------------------------------------------
        DB::statement(<<<'SQL'
            alter table student_conversions add constraint student_conversions_completed_requires_student
                check (status <> 'COMPLETED' or (student_id is not null and student_number is not null))
        SQL);

        // --- Workload and analytics indexes -------------------------------------------------------
        DB::statement("create index review_assignments_open_idx on review_assignments (assignee_id, due_at) where status in ('PENDING','IN_PROGRESS')");
        DB::statement("create index workflow_steps_overdue_idx on workflow_steps (due_at) where status = 'ACTIVE'");
        DB::statement('create index outbox_events_unpublished_idx on outbox_events (available_at) where published_at is null');
        DB::statement("create index communications_pending_idx on communications (queued_at) where status = 'QUEUED'");
        DB::statement("create index export_jobs_active_idx on export_jobs (requested_at) where status in ('QUEUED','RUNNING')");
    }

    public function down(): void
    {
        if (! $this->isPostgres()) {
            return;
        }

        foreach ([
            'application_versions_immutable on application_versions',
            'audit_events_append_only on audit_events',
            'admission_roll_entries_frozen_guard on admission_roll_entries',
            'admission_rolls_frozen_guard on admission_rolls',
            'application_documents_immutable_guard on application_documents',
        ] as $trigger) {
            DB::statement('drop trigger if exists '.$trigger);
        }
        foreach ([
            'admission_guard_application_version', 'admission_guard_audit_events',
            'admission_guard_frozen_roll_entries', 'admission_guard_frozen_roll',
            'admission_guard_immutable_document',
        ] as $function) {
            DB::statement('drop function if exists '.$function.'()');
        }
        foreach ([
            'payment_transactions_amount_positive' => 'payment_transactions',
            'payment_transactions_currency_iso' => 'payment_transactions',
            'payment_fee_setups_amount_positive' => 'payment_fee_setups',
            'payment_fee_setups_currency_iso' => 'payment_fee_setups',
            'payment_fee_setups_effective_range' => 'payment_fee_setups',
            'payment_receipts_amount_positive' => 'payment_receipts',
            'admission_applications_payment_status_enum' => 'admission_applications',
            'admission_applications_status_enum' => 'admission_applications',
            'admission_applications_submission_evidence' => 'admission_applications',
            'student_conversions_completed_requires_student' => 'student_conversions',
        ] as $constraint => $table) {
            DB::statement("alter table {$table} drop constraint if exists {$constraint}");
        }
        foreach ([
            'payment_transactions_authoritative_fee_unique', 'payment_transactions_provider_ref_unique',
            'payment_waivers_active_unique', 'admission_applications_live_per_offering_unique',
            'admission_applications_status_created_idx', 'admission_applications_submitted_idx',
            'admission_applications_form_data_gin', 'users_email_normalised_unique',
            'people_identity_hash_unique', 'generated_documents_subject_version_unique',
            'qr_verification_tokens_active_subject_unique', 'review_assignments_open_idx',
            'workflow_steps_overdue_idx', 'outbox_events_unpublished_idx',
            'communications_pending_idx', 'export_jobs_active_idx',
        ] as $index) {
            DB::statement("drop index if exists {$index}");
        }

        // Restore the original blanket uniqueness so the schema returns to its pre-migration shape.
        DB::statement(<<<'SQL'
            create unique index if not exists admission_applications_applicant_offering_unique
                on admission_applications (applicant_profile_id, programme_offering_id)
        SQL);
    }
};

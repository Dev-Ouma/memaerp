<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Finance truth is append-only. Mutable invoice balance columns remain temporarily for backward
 * compatibility, but all new balance reads must use the derived ledger views created here.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE TABLE finance.ledger_accounts (
                id                  uuid          PRIMARY KEY,
                institution_id      uuid          NOT NULL REFERENCES institution.institutions(id),
                code                varchar(64)   NOT NULL,
                name                varchar(255)  NOT NULL,
                account_type        varchar(32)   NOT NULL,
                parent_account_id   uuid          NULL REFERENCES finance.ledger_accounts(id) ON DELETE RESTRICT,
                currency            char(3)       NOT NULL DEFAULT 'KES',
                is_control_account  boolean       NOT NULL DEFAULT false,
                is_active           boolean       NOT NULL DEFAULT true,
                created_by          uuid          NULL REFERENCES iam.users(id) ON DELETE SET NULL,
                updated_by          uuid          NULL REFERENCES iam.users(id) ON DELETE SET NULL,
                created_at          timestamptz   NOT NULL DEFAULT now(),
                updated_at          timestamptz   NOT NULL DEFAULT now(),
                deleted_at          timestamptz   NULL,
                CONSTRAINT ledger_accounts_code_unique UNIQUE (institution_id, code),
                CONSTRAINT ledger_accounts_type_check CHECK (
                    account_type IN ('ASSET', 'LIABILITY', 'EQUITY', 'REVENUE', 'EXPENSE')
                )
            )
        SQL);

        DB::statement(<<<'SQL'
            CREATE TABLE finance.journals (
                id                  uuid          PRIMARY KEY,
                institution_id      uuid          NOT NULL REFERENCES institution.institutions(id),
                journal_number      varchar(64)   NOT NULL,
                source_type         varchar(64)   NOT NULL,
                source_id           uuid          NULL,
                description         text          NOT NULL,
                currency            char(3)       NOT NULL DEFAULT 'KES',
                status              varchar(16)   NOT NULL DEFAULT 'DRAFT',
                effective_on        date          NOT NULL,
                posted_at           timestamptz   NULL,
                posted_by           uuid          NULL REFERENCES iam.users(id) ON DELETE SET NULL,
                reversal_of_id      uuid          NULL REFERENCES finance.journals(id) ON DELETE RESTRICT,
                created_by          uuid          NULL REFERENCES iam.users(id) ON DELETE SET NULL,
                updated_by          uuid          NULL REFERENCES iam.users(id) ON DELETE SET NULL,
                created_at          timestamptz   NOT NULL DEFAULT now(),
                updated_at          timestamptz   NOT NULL DEFAULT now(),
                CONSTRAINT journals_number_unique UNIQUE (institution_id, journal_number),
                CONSTRAINT journals_reversal_unique UNIQUE (reversal_of_id),
                CONSTRAINT journals_status_check CHECK (status IN ('DRAFT', 'POSTED', 'REVERSED')),
                CONSTRAINT journals_posting_state_check CHECK (
                    (status = 'DRAFT' AND posted_at IS NULL AND posted_by IS NULL)
                    OR (status IN ('POSTED', 'REVERSED') AND posted_at IS NOT NULL)
                ),
                CONSTRAINT journals_not_self_reversal CHECK (reversal_of_id IS NULL OR reversal_of_id <> id)
            )
        SQL);

        DB::statement(<<<'SQL'
            CREATE TABLE finance.journal_entries (
                id                  uuid          PRIMARY KEY,
                institution_id      uuid          NOT NULL REFERENCES institution.institutions(id),
                journal_id          uuid          NOT NULL REFERENCES finance.journals(id) ON DELETE RESTRICT,
                account_id          uuid          NOT NULL REFERENCES finance.ledger_accounts(id) ON DELETE RESTRICT,
                person_id           uuid          NULL REFERENCES student.persons(id) ON DELETE RESTRICT,
                invoice_id          uuid          NULL REFERENCES finance.invoices(id) ON DELETE RESTRICT,
                debit               numeric(15,2) NOT NULL DEFAULT 0,
                credit              numeric(15,2) NOT NULL DEFAULT 0,
                memo                text          NULL,
                created_by          uuid          NULL REFERENCES iam.users(id) ON DELETE SET NULL,
                updated_by          uuid          NULL REFERENCES iam.users(id) ON DELETE SET NULL,
                created_at          timestamptz   NOT NULL DEFAULT now(),
                updated_at          timestamptz   NOT NULL DEFAULT now(),
                CONSTRAINT journal_entries_amount_check CHECK (
                    debit >= 0 AND credit >= 0
                    AND ((debit > 0 AND credit = 0) OR (credit > 0 AND debit = 0))
                )
            )
        SQL);

        DB::statement(<<<'SQL'
            CREATE TABLE finance.student_ledger_entries (
                id                  uuid          PRIMARY KEY,
                institution_id      uuid          NOT NULL REFERENCES institution.institutions(id),
                person_id           uuid          NOT NULL REFERENCES student.persons(id) ON DELETE RESTRICT,
                term_id             uuid          NULL REFERENCES institution.terms(id) ON DELETE RESTRICT,
                invoice_id          uuid          NULL REFERENCES finance.invoices(id) ON DELETE RESTRICT,
                payment_id          uuid          NULL REFERENCES finance.payments(id) ON DELETE RESTRICT,
                journal_id          uuid          NOT NULL REFERENCES finance.journals(id) ON DELETE RESTRICT,
                entry_type          varchar(32)   NOT NULL,
                debit               numeric(15,2) NOT NULL DEFAULT 0,
                credit              numeric(15,2) NOT NULL DEFAULT 0,
                currency            char(3)       NOT NULL DEFAULT 'KES',
                description         text          NOT NULL,
                effective_at        timestamptz   NOT NULL,
                reversal_of_id      uuid          NULL REFERENCES finance.student_ledger_entries(id) ON DELETE RESTRICT,
                created_by          uuid          NULL REFERENCES iam.users(id) ON DELETE SET NULL,
                updated_by          uuid          NULL REFERENCES iam.users(id) ON DELETE SET NULL,
                created_at          timestamptz   NOT NULL DEFAULT now(),
                updated_at          timestamptz   NOT NULL DEFAULT now(),
                CONSTRAINT student_ledger_reversal_unique UNIQUE (reversal_of_id),
                CONSTRAINT student_ledger_amount_check CHECK (
                    debit >= 0 AND credit >= 0
                    AND ((debit > 0 AND credit = 0) OR (credit > 0 AND debit = 0))
                ),
                CONSTRAINT student_ledger_type_check CHECK (
                    entry_type IN ('CHARGE', 'PAYMENT', 'CREDIT', 'REFUND', 'REVERSAL', 'ADJUSTMENT')
                ),
                CONSTRAINT student_ledger_not_self_reversal CHECK (
                    reversal_of_id IS NULL OR reversal_of_id <> id
                )
            )
        SQL);

        DB::statement(<<<'SQL'
            CREATE TABLE finance.payment_allocations (
                id                  uuid          PRIMARY KEY,
                institution_id      uuid          NOT NULL REFERENCES institution.institutions(id),
                payment_id          uuid          NOT NULL REFERENCES finance.payments(id) ON DELETE RESTRICT,
                invoice_id          uuid          NOT NULL REFERENCES finance.invoices(id) ON DELETE RESTRICT,
                ledger_entry_id     uuid          NOT NULL REFERENCES finance.student_ledger_entries(id) ON DELETE RESTRICT,
                amount              numeric(15,2) NOT NULL CHECK (amount > 0),
                allocated_at        timestamptz   NOT NULL DEFAULT now(),
                allocation_rule     varchar(64)   NOT NULL,
                reversed_by_id      uuid          NULL REFERENCES finance.payment_allocations(id) ON DELETE RESTRICT,
                created_by          uuid          NULL REFERENCES iam.users(id) ON DELETE SET NULL,
                updated_by          uuid          NULL REFERENCES iam.users(id) ON DELETE SET NULL,
                created_at          timestamptz   NOT NULL DEFAULT now(),
                updated_at          timestamptz   NOT NULL DEFAULT now(),
                CONSTRAINT payment_allocations_entry_unique UNIQUE (ledger_entry_id),
                CONSTRAINT payment_allocations_reversal_unique UNIQUE (reversed_by_id),
                CONSTRAINT payment_allocations_not_self_reversal CHECK (
                    reversed_by_id IS NULL OR reversed_by_id <> id
                )
            )
        SQL);

        DB::statement(<<<'SQL'
            CREATE TABLE finance.receipts (
                id                  uuid          PRIMARY KEY,
                institution_id      uuid          NOT NULL REFERENCES institution.institutions(id),
                payment_id          uuid          NOT NULL REFERENCES finance.payments(id) ON DELETE RESTRICT,
                receipt_number      varchar(64)   NOT NULL,
                issued_at           timestamptz   NOT NULL DEFAULT now(),
                voided_at           timestamptz   NULL,
                void_reason         text          NULL,
                replacement_id      uuid          NULL REFERENCES finance.receipts(id) ON DELETE RESTRICT,
                created_by          uuid          NULL REFERENCES iam.users(id) ON DELETE SET NULL,
                updated_by          uuid          NULL REFERENCES iam.users(id) ON DELETE SET NULL,
                created_at          timestamptz   NOT NULL DEFAULT now(),
                updated_at          timestamptz   NOT NULL DEFAULT now(),
                CONSTRAINT receipts_number_unique UNIQUE (institution_id, receipt_number),
                CONSTRAINT receipts_payment_unique UNIQUE (payment_id),
                CONSTRAINT receipts_void_state_check CHECK (
                    (voided_at IS NULL AND void_reason IS NULL)
                    OR (voided_at IS NOT NULL AND void_reason IS NOT NULL)
                )
            )
        SQL);

        DB::statement(<<<'SQL'
            CREATE TABLE finance.reconciliation_exceptions (
                id                  uuid          PRIMARY KEY,
                institution_id      uuid          NOT NULL REFERENCES institution.institutions(id),
                payment_id          uuid          NULL REFERENCES finance.payments(id) ON DELETE RESTRICT,
                provider            varchar(64)   NOT NULL,
                provider_reference  varchar(128)  NOT NULL,
                reason_code         varchar(64)   NOT NULL,
                evidence            jsonb         NOT NULL DEFAULT '{}',
                status              varchar(24)   NOT NULL DEFAULT 'OPEN',
                resolved_at         timestamptz   NULL,
                resolved_by         uuid          NULL REFERENCES iam.users(id) ON DELETE SET NULL,
                resolution_notes    text          NULL,
                created_by          uuid          NULL REFERENCES iam.users(id) ON DELETE SET NULL,
                updated_by          uuid          NULL REFERENCES iam.users(id) ON DELETE SET NULL,
                created_at          timestamptz   NOT NULL DEFAULT now(),
                updated_at          timestamptz   NOT NULL DEFAULT now(),
                CONSTRAINT reconciliation_reference_unique UNIQUE (
                    institution_id, provider, provider_reference
                ),
                CONSTRAINT reconciliation_status_check CHECK (
                    status IN ('OPEN', 'IN_REVIEW', 'RESOLVED', 'DISMISSED')
                ),
                CONSTRAINT reconciliation_resolution_check CHECK (
                    (status IN ('OPEN', 'IN_REVIEW') AND resolved_at IS NULL AND resolved_by IS NULL)
                    OR (status IN ('RESOLVED', 'DISMISSED') AND resolved_at IS NOT NULL AND resolved_by IS NOT NULL)
                )
            )
        SQL);

        DB::statement('CREATE INDEX journal_entries_journal_idx
            ON finance.journal_entries (institution_id, journal_id)');
        DB::statement('CREATE INDEX journal_entries_account_idx
            ON finance.journal_entries (institution_id, account_id, created_at)');
        DB::statement('CREATE INDEX student_ledger_person_idx
            ON finance.student_ledger_entries (institution_id, person_id, effective_at, id)');
        DB::statement('CREATE INDEX student_ledger_invoice_idx
            ON finance.student_ledger_entries (institution_id, invoice_id)
            WHERE invoice_id IS NOT NULL');
        DB::statement('CREATE INDEX reconciliation_open_idx
            ON finance.reconciliation_exceptions (institution_id, created_at)
            WHERE status IN (\'OPEN\', \'IN_REVIEW\')');

        $this->createImmutabilityTriggers();
        $this->createBalanceConstraintTriggers();
        $this->createDerivedViews();
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS finance.invoice_ledger_balances');
        DB::statement('DROP VIEW IF EXISTS finance.student_balances');
        DB::statement('DROP FUNCTION IF EXISTS finance.assert_balanced_journal_trigger()');
        DB::statement('DROP FUNCTION IF EXISTS finance.prevent_posted_journal_mutation()');
        DB::statement('DROP FUNCTION IF EXISTS finance.prevent_immutable_finance_mutation()');
        DB::statement('DROP TABLE IF EXISTS finance.reconciliation_exceptions');
        DB::statement('DROP TABLE IF EXISTS finance.receipts');
        DB::statement('DROP TABLE IF EXISTS finance.payment_allocations');
        DB::statement('DROP TABLE IF EXISTS finance.student_ledger_entries');
        DB::statement('DROP TABLE IF EXISTS finance.journal_entries');
        DB::statement('DROP TABLE IF EXISTS finance.journals');
        DB::statement('DROP TABLE IF EXISTS finance.ledger_accounts');
    }

    private function createImmutabilityTriggers(): void
    {
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION finance.prevent_immutable_finance_mutation()
            RETURNS trigger
            LANGUAGE plpgsql
            AS $$
            BEGIN
                RAISE EXCEPTION USING
                    MESSAGE = format('%s.%s is append-only; %s is not permitted', TG_TABLE_SCHEMA, TG_TABLE_NAME, TG_OP),
                    ERRCODE = '42501';
            END;
            $$
        SQL);

        foreach (['journal_entries', 'student_ledger_entries'] as $table) {
            DB::statement("CREATE TRIGGER {$table}_no_update
                BEFORE UPDATE ON finance.{$table}
                FOR EACH ROW EXECUTE FUNCTION finance.prevent_immutable_finance_mutation()");
            DB::statement("CREATE TRIGGER {$table}_no_delete
                BEFORE DELETE ON finance.{$table}
                FOR EACH ROW EXECUTE FUNCTION finance.prevent_immutable_finance_mutation()");
        }

        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION finance.prevent_posted_journal_mutation()
            RETURNS trigger
            LANGUAGE plpgsql
            AS $$
            BEGIN
                IF OLD.status IN ('POSTED', 'REVERSED') THEN
                    RAISE EXCEPTION USING
                        MESSAGE = 'posted journals are immutable; create a reversing journal',
                        ERRCODE = '42501';
                END IF;
                IF TG_OP = 'DELETE' THEN
                    RETURN OLD;
                END IF;

                RETURN NEW;
            END;
            $$
        SQL);
        DB::statement('CREATE TRIGGER journals_no_posted_update
            BEFORE UPDATE ON finance.journals
            FOR EACH ROW EXECUTE FUNCTION finance.prevent_posted_journal_mutation()');
        DB::statement('CREATE TRIGGER journals_no_posted_delete
            BEFORE DELETE ON finance.journals
            FOR EACH ROW EXECUTE FUNCTION finance.prevent_posted_journal_mutation()');
    }

    private function createBalanceConstraintTriggers(): void
    {
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION finance.assert_balanced_journal_trigger()
            RETURNS trigger
            LANGUAGE plpgsql
            AS $$
            DECLARE
                target_journal_id uuid;
                journal_status varchar(16);
                debit_total numeric(15,2);
                credit_total numeric(15,2);
                line_count integer;
            BEGIN
                target_journal_id := CASE
                    WHEN TG_TABLE_NAME = 'journals' THEN NEW.id
                    ELSE NEW.journal_id
                END;

                SELECT status INTO journal_status
                FROM finance.journals
                WHERE id = target_journal_id;

                IF journal_status IN ('POSTED', 'REVERSED') THEN
                    SELECT COALESCE(SUM(debit), 0), COALESCE(SUM(credit), 0), COUNT(*)
                    INTO debit_total, credit_total, line_count
                    FROM finance.journal_entries
                    WHERE journal_id = target_journal_id;

                    IF line_count < 2 OR debit_total <> credit_total THEN
                        RAISE EXCEPTION USING
                            MESSAGE = format(
                                'journal %s is not balanced: %s debit, %s credit, %s lines',
                                target_journal_id,
                                debit_total,
                                credit_total,
                                line_count
                            ),
                            ERRCODE = '23514';
                    END IF;
                END IF;

                RETURN NEW;
            END;
            $$
        SQL);

        DB::statement('CREATE CONSTRAINT TRIGGER journal_entries_balance_check
            AFTER INSERT ON finance.journal_entries
            DEFERRABLE INITIALLY DEFERRED
            FOR EACH ROW EXECUTE FUNCTION finance.assert_balanced_journal_trigger()');
        DB::statement('CREATE CONSTRAINT TRIGGER journals_balance_on_post
            AFTER UPDATE OF status ON finance.journals
            DEFERRABLE INITIALLY DEFERRED
            FOR EACH ROW EXECUTE FUNCTION finance.assert_balanced_journal_trigger()');
    }

    private function createDerivedViews(): void
    {
        DB::statement(<<<'SQL'
            CREATE VIEW finance.student_balances AS
            SELECT
                institution_id,
                person_id,
                currency,
                SUM(debit - credit)::numeric(15,2) AS balance,
                MAX(effective_at) AS last_activity_at
            FROM finance.student_ledger_entries
            GROUP BY institution_id, person_id, currency
        SQL);

        DB::statement(<<<'SQL'
            CREATE VIEW finance.invoice_ledger_balances AS
            SELECT
                institution_id,
                invoice_id,
                currency,
                SUM(debit - credit)::numeric(15,2) AS balance,
                MAX(effective_at) AS last_activity_at
            FROM finance.student_ledger_entries
            WHERE invoice_id IS NOT NULL
            GROUP BY institution_id, invoice_id, currency
        SQL);
    }
};

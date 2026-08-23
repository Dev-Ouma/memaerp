<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Database-level invariants protect every write path, including imports and background jobs.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE TABLE course.rooms (
                id                  uuid          PRIMARY KEY,
                institution_id      uuid          NOT NULL REFERENCES institution.institutions(id),
                campus_id           uuid          NOT NULL REFERENCES institution.campuses(id) ON DELETE RESTRICT,
                code                varchar(64)   NOT NULL,
                name                varchar(255)  NOT NULL,
                capacity            integer       NOT NULL CHECK (capacity > 0),
                room_type           varchar(32)   NOT NULL DEFAULT 'CLASSROOM',
                accessibility       jsonb         NOT NULL DEFAULT '{}',
                is_active           boolean       NOT NULL DEFAULT true,
                created_by          uuid          NULL REFERENCES iam.users(id) ON DELETE SET NULL,
                updated_by          uuid          NULL REFERENCES iam.users(id) ON DELETE SET NULL,
                created_at          timestamptz   NOT NULL DEFAULT now(),
                updated_at          timestamptz   NOT NULL DEFAULT now(),
                deleted_at          timestamptz   NULL,
                CONSTRAINT rooms_code_unique UNIQUE (institution_id, campus_id, code),
                CONSTRAINT rooms_type_check CHECK (
                    room_type IN ('CLASSROOM', 'LABORATORY', 'LECTURE_HALL', 'EXAM_HALL', 'ONLINE')
                )
            )
        SQL);

        DB::statement(<<<'SQL'
            CREATE TABLE course.teaching_slots (
                id                  uuid          PRIMARY KEY,
                institution_id      uuid          NOT NULL REFERENCES institution.institutions(id),
                course_offering_id  uuid          NOT NULL REFERENCES course.course_offerings(id) ON DELETE CASCADE,
                room_id             uuid          NULL REFERENCES course.rooms(id) ON DELETE RESTRICT,
                lecturer_id         uuid          NULL REFERENCES iam.users(id) ON DELETE RESTRICT,
                starts_at           timestamptz   NOT NULL,
                ends_at             timestamptz   NOT NULL,
                status              varchar(16)   NOT NULL DEFAULT 'ACTIVE',
                created_by          uuid          NULL REFERENCES iam.users(id) ON DELETE SET NULL,
                updated_by          uuid          NULL REFERENCES iam.users(id) ON DELETE SET NULL,
                created_at          timestamptz   NOT NULL DEFAULT now(),
                updated_at          timestamptz   NOT NULL DEFAULT now(),
                CONSTRAINT teaching_slots_time_check CHECK (ends_at > starts_at),
                CONSTRAINT teaching_slots_status_check CHECK (
                    status IN ('DRAFT', 'ACTIVE', 'CANCELLED')
                ),
                CONSTRAINT teaching_slots_room_required CHECK (
                    status <> 'ACTIVE' OR room_id IS NOT NULL
                )
            )
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE course.teaching_slots
            ADD CONSTRAINT teaching_slots_no_room_overlap
            EXCLUDE USING gist (
                institution_id WITH =,
                room_id WITH =,
                tstzrange(starts_at, ends_at, '[)') WITH &&
            ) WHERE (status = 'ACTIVE')
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE course.teaching_slots
            ADD CONSTRAINT teaching_slots_no_lecturer_overlap
            EXCLUDE USING gist (
                institution_id WITH =,
                lecturer_id WITH =,
                tstzrange(starts_at, ends_at, '[)') WITH &&
            ) WHERE (status = 'ACTIVE' AND lecturer_id IS NOT NULL)
        SQL);

        DB::statement('CREATE INDEX teaching_slots_offering_idx
            ON course.teaching_slots (institution_id, course_offering_id, starts_at)');
        DB::statement('CREATE INDEX rooms_active_idx
            ON course.rooms (institution_id, campus_id, code)
            WHERE deleted_at IS NULL AND is_active = true');

        $this->addConstraint(
            'institution.terms',
            'terms_date_order_check',
            'ends_on >= starts_on',
        );
        $this->addConstraint(
            'institution.grade_bands',
            'grade_bands_range_check',
            'min_mark >= 0 AND max_mark <= 100 AND max_mark >= min_mark',
        );
        $this->addConstraint(
            'course.courses',
            'courses_credit_check',
            'credits > 0 AND credits <= 30',
        );
        $this->addConstraint(
            'course.course_prerequisites',
            'course_prerequisites_not_self_check',
            'course_id <> prerequisite_course_id',
        );
        $this->addConstraint(
            'course.course_offerings',
            'course_offerings_capacity_check',
            'max_capacity > 0 AND enrolled_count >= 0 AND enrolled_count <= max_capacity',
        );
        $this->addConstraint(
            'finance.fee_structures',
            'fee_structures_amount_check',
            'tuition_fee >= 0 AND statutory_fees >= 0 AND total_amount >= 0',
        );
        $this->addConstraint(
            'finance.invoices',
            'invoices_amount_check',
            'amount_due >= 0 AND amount_paid >= 0 AND balance >= 0 AND amount_paid <= amount_due',
        );
        $this->addConstraint(
            'finance.payments',
            'payments_amount_check',
            'amount > 0',
        );
        $this->addConstraint(
            'examination.student_marks',
            'student_marks_range_check',
            'cat_score BETWEEN 0 AND 100 AND exam_score BETWEEN 0 AND 100 AND total_score BETWEEN 0 AND 100',
        );
        $this->addConstraint(
            'examination.term_gpas',
            'term_gpas_range_check',
            'gpa >= 0 AND cgpa >= 0 AND credits_earned <= credits_attempted',
        );

        // Tenant-safe identifiers replace global uniqueness. Existing values already satisfy the
        // weaker composite constraint, so this is safe and enables future institutions.
        $this->replaceGlobalUnique(
            'admission.applications',
            'applications_application_number_unique',
            'applications_institution_application_number_unique',
            ['institution_id', 'application_number'],
        );
        $this->replaceGlobalUnique(
            'student.students',
            'students_student_number_unique',
            'students_institution_student_number_unique',
            ['institution_id', 'student_number'],
        );
        $this->replaceGlobalUnique(
            'finance.invoices',
            'invoices_invoice_number_unique',
            'invoices_institution_invoice_number_unique',
            ['institution_id', 'invoice_number'],
        );
        $this->replaceGlobalUnique(
            'finance.payments',
            'payments_receipt_number_unique',
            'payments_institution_receipt_number_unique',
            ['institution_id', 'receipt_number'],
        );
        $this->replaceGlobalUnique(
            'finance.payments',
            'payments_transaction_reference_unique',
            'payments_institution_transaction_reference_unique',
            ['institution_id', 'transaction_reference'],
        );
    }

    public function down(): void
    {
        $this->restoreGlobalUnique(
            'finance.payments',
            'payments_institution_transaction_reference_unique',
            'payments_transaction_reference_unique',
            ['transaction_reference'],
        );
        $this->restoreGlobalUnique(
            'finance.payments',
            'payments_institution_receipt_number_unique',
            'payments_receipt_number_unique',
            ['receipt_number'],
        );
        $this->restoreGlobalUnique(
            'finance.invoices',
            'invoices_institution_invoice_number_unique',
            'invoices_invoice_number_unique',
            ['invoice_number'],
        );
        $this->restoreGlobalUnique(
            'student.students',
            'students_institution_student_number_unique',
            'students_student_number_unique',
            ['student_number'],
        );
        $this->restoreGlobalUnique(
            'admission.applications',
            'applications_institution_application_number_unique',
            'applications_application_number_unique',
            ['application_number'],
        );

        foreach ([
            ['examination.term_gpas', 'term_gpas_range_check'],
            ['examination.student_marks', 'student_marks_range_check'],
            ['finance.payments', 'payments_amount_check'],
            ['finance.invoices', 'invoices_amount_check'],
            ['finance.fee_structures', 'fee_structures_amount_check'],
            ['course.course_offerings', 'course_offerings_capacity_check'],
            ['course.course_prerequisites', 'course_prerequisites_not_self_check'],
            ['course.courses', 'courses_credit_check'],
            ['institution.grade_bands', 'grade_bands_range_check'],
            ['institution.terms', 'terms_date_order_check'],
        ] as [$table, $constraint]) {
            DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$constraint}");
        }

        DB::statement('DROP TABLE IF EXISTS course.teaching_slots');
        DB::statement('DROP TABLE IF EXISTS course.rooms');

        // A rollback after onboarding multiple institutions can fail if identifiers overlap.
        // That is intentional: silently discarding valid tenant data would be destructive.
    }

    private function addConstraint(string $table, string $name, string $expression): void
    {
        DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$name} CHECK ({$expression})");
    }

    /** @param list<string> $columns */
    private function replaceGlobalUnique(
        string $table,
        string $oldConstraint,
        string $newConstraint,
        array $columns,
    ): void {
        DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$oldConstraint}");
        DB::statement(sprintf(
            'ALTER TABLE %s ADD CONSTRAINT %s UNIQUE (%s)',
            $table,
            $newConstraint,
            implode(', ', $columns),
        ));
    }

    /** @param list<string> $columns */
    private function restoreGlobalUnique(
        string $table,
        string $currentConstraint,
        string $restoredConstraint,
        array $columns,
    ): void {
        DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$currentConstraint}");
        DB::statement(sprintf(
            'ALTER TABLE %s ADD CONSTRAINT %s UNIQUE (%s)',
            $table,
            $restoredConstraint,
            implode(', ', $columns),
        ));
    }
};

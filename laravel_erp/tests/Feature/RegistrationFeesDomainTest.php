<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\FeePaymentAccount;
use App\Models\FeeStructure;
use App\Models\RegistrationPeriod;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RegistrationFeesDomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_to_fees_vertical_enrols_invoices_and_receipts(): void
    {
        $this->seedRbac();
        $officer = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $this->grantRole($officer, 'registration_officer');
        $this->grantRole($officer, 'finance_officer');

        $course = Course::create(['code' => 'BCS', 'name' => 'BSc Computer Science']);
        $session = AcademicSession::create(['start_date' => '2026-09-01', 'end_date' => '2027-08-31']);
        $studentUser = User::factory()->create(['role' => 'student', 'is_active' => true, 'name' => 'Amina Domain Student']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'course_id' => $course->id,
            'academic_session_id' => $session->id,
            'admission_number' => 'BCS/100/2026',
        ]);

        $this->actingAs($officer)->post(route('fees.structures.store'), [
            'code' => 'FS-BCS-T1',
            'title' => 'BCS Trimester tuition',
            'course_id' => $course->id,
            'cohort' => '2026',
            'tuition_amount' => 45000,
            'admin_amount' => 5000,
            'status' => 'ACTIVE',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('registration.periods.store'), [
            'code' => 'REG-2027-T1',
            'title' => 'Trimester I 2027 registration',
            'starts_on' => '2027-01-05',
            'regular_deadline' => '2027-01-20',
            'late_deadline' => '2027-01-27',
            'min_units' => 3,
            'max_units' => 8,
            'late_penalty_amount' => 2000,
            'status' => 'OPEN',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $period = RegistrationPeriod::query()->where('code', 'REG-2027-T1')->firstOrFail();

        $this->post(route('registration.enrolments.store'), [
            'registration_period_id' => $period->id,
            'student_id' => $student->id,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('course_enrolments', [
            'registration_period_id' => $period->id,
            'student_id' => $student->id,
            'status' => 'REGISTERED',
        ]);
        $invoice = FeeInvoice::query()->where('student_id', $student->id)->firstOrFail();
        $this->assertSame(50000.0, (float) $invoice->amount_invoiced);
        $this->assertSame('OPEN', $invoice->status);

        $this->post(route('fees.payments.store'), [
            'fee_invoice_id' => $invoice->id,
            'amount' => 50000,
            'method' => 'MPESA',
            'transaction_ref' => 'QRT-DOMAIN-001',
            'confirm' => 1,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $invoice->refresh();
        $this->assertSame('SETTLED', $invoice->status);
        $this->assertSame(50000.0, (float) $invoice->amount_paid);
        $this->assertDatabaseHas('fee_payments', [
            'fee_invoice_id' => $invoice->id,
            'status' => 'CONFIRMED',
            'transaction_ref' => 'QRT-DOMAIN-001',
        ]);

        $this->get(route('registration.course-registration-periods'))
            ->assertOk()
            ->assertSee('REG-2027-T1')
            ->assertSee('Active / Open');
        $this->get(route('fees.fee-setup'))
            ->assertOk()
            ->assertSee('FS-BCS-T1')
            ->assertSee('KES 45,000');
        $this->get(route('fees.fee-payables'))
            ->assertOk()
            ->assertSee($invoice->invoice_no)
            ->assertSee('Amina Domain Student')
            ->assertSee('Settled');
        $this->get(route('fees.payment-receipt'))
            ->assertOk()
            ->assertSee('Amina Domain Student')
            ->assertSee('KES 50,000')
            ->assertSee('QRT-DOMAIN-001');
    }

    public function test_pending_payment_requires_confirmation_before_settling_invoice(): void
    {
        $finance = $this->admissionOfficer('finance_officer');
        $course = Course::create(['code' => 'BIT', 'name' => 'BSc IT']);
        $studentUser = User::factory()->create(['role' => 'student', 'is_active' => true]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'course_id' => $course->id,
            'admission_number' => 'BIT/200/2026',
        ]);
        $structure = FeeStructure::create([
            'code' => 'FS-BIT',
            'title' => 'BIT fees',
            'course_id' => $course->id,
            'tuition_amount' => 10000,
            'admin_amount' => 0,
            'status' => 'ACTIVE',
        ]);
        $account = FeePaymentAccount::create([
            'code' => 'ACC-BANK-1',
            'name' => 'Co-op Collection',
            'bank_name' => 'Co-operative Bank',
            'integration_type' => 'Bank IPN',
            'status' => 'ACTIVE',
        ]);
        $invoice = FeeInvoice::create([
            'invoice_no' => 'INV-TEST-1',
            'student_id' => $student->id,
            'fee_structure_id' => $structure->id,
            'amount_invoiced' => 10000,
            'amount_paid' => 0,
            'status' => 'OPEN',
            'issued_at' => now(),
        ]);

        $this->actingAs($finance)->post(route('fees.payments.store'), [
            'fee_invoice_id' => $invoice->id,
            'payment_account_id' => $account->id,
            'amount' => 10000,
            'method' => 'BANK',
            'transaction_ref' => 'SLIP-9',
            'confirm' => 0,
        ])->assertRedirect();

        $payment = FeePayment::query()->where('transaction_ref', 'SLIP-9')->firstOrFail();
        $this->assertSame('PENDING', $payment->status);
        $this->assertSame('OPEN', $invoice->fresh()->status);
        $this->assertSame($account->id, (int) $payment->payment_account_id);

        $this->get(route('fees.pending-payments'))
            ->assertOk()
            ->assertSee('SLIP-9')
            ->assertSee('Confirm');
        $this->post(route('fees.payments.confirm', $payment))->assertRedirect();
        $this->assertSame('CONFIRMED', $payment->fresh()->status);
        $this->assertSame('SETTLED', $invoice->fresh()->status);
        $this->assertNotNull($payment->fresh()->receipt_no);

        $this->get(route('fees.payment-accounts'))
            ->assertOk()
            ->assertSee('ACC-BANK-1')
            ->assertSee('KES 10,000');
    }

    public function test_staff_without_fees_grant_cannot_create_structure(): void
    {
        $this->seedRbac();
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);

        $this->actingAs($staff)->post(route('fees.structures.store'), [
            'code' => 'FS-X',
            'title' => 'Unauthorized',
            'tuition_amount' => 1,
        ])->assertForbidden();
        $this->assertDatabaseCount('fee_structures', 0);
        $this->assertDatabaseCount('course_enrolments', 0);
    }

    public function test_finance_officer_can_manage_payment_types_and_funding_sources(): void
    {
        $finance = $this->admissionOfficer('finance_officer');

        $this->actingAs($finance)->post(route('fees.types.store'), [
            'code' => 'TUITION',
            'name' => 'Tuition Fee',
            'category' => 'Academic',
            'mandatory' => 1,
            'ledger_allocation' => '4000-Tuition',
            'refund_policy' => 'Pro-rata before week 4',
            'status' => 'ACTIVE',
        ])->assertRedirect();

        $this->post(route('fees.sources.store'), [
            'code' => 'HELB',
            'name' => 'HELB Loan',
            'description' => 'Government student loan',
            'allocation_rule' => 'Priority to tuition',
            'candidates_count' => 12,
            'status' => 'ACTIVE',
        ])->assertRedirect();

        $this->assertDatabaseHas('fee_payment_types', ['code' => 'TUITION', 'mandatory' => true]);
        $this->assertDatabaseHas('fee_funding_sources', ['code' => 'HELB', 'candidates_count' => 12]);
        $this->get(route('fees.payment-types'))->assertOk()->assertSee('TUITION')->assertSee('Yes');
        $this->get(route('fees.payment-source'))->assertOk()->assertSee('HELB Loan')->assertSee('12');
    }

    public function test_domain_screens_render_empty_without_demo_names(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->seedRbac();

        foreach ([
            'registration.course-registration-periods',
            'fees.fee-setup',
            'fees.fee-payables',
            'fees.pending-payments',
            'fees.payment-receipt',
            'fees.payment-accounts',
            'fees.payment-types',
            'fees.payment-source',
        ] as $route) {
            $this->actingAs($admin)->get(route($route))
                ->assertOk()
                ->assertDontSee('Brenda Chepkoech')
                ->assertDontSee('14,850');
        }
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\CpdEnrolment;
use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\FeePaymentAccount;
use App\Models\FeeStructure;
use App\Models\KuccpsPlacement;
use App\Models\MoodleSyncLog;
use App\Models\RegistrationPeriod;
use App\Models\RegistrationReminderCampaign;
use App\Models\Student;
use App\Models\StudentInfoUpdateRequest;
use App\Models\StudentPromotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RegistrationDeskEndToEndTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_registration_desk_and_fees_ledger_end_to_end(): void
    {
        $this->seedRbac();
        $officer = User::factory()->create(['role' => 'staff', 'is_active' => true, 'name' => 'Desk Officer']);
        $this->grantRole($officer, 'registration_officer');
        $this->grantRole($officer, 'finance_officer');

        $course = Course::create(['code' => 'BCS', 'name' => 'BSc Computer Science']);
        $session = AcademicSession::create(['start_date' => '2026-09-01', 'end_date' => '2027-08-31']);
        $studentUser = User::factory()->create(['role' => 'student', 'is_active' => true, 'name' => 'E2E Scholar']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'course_id' => $course->id,
            'academic_session_id' => $session->id,
            'admission_number' => 'BCS/E2E/2026',
        ]);

        $this->actingAs($officer);

        // Registration desk domain writes
        $this->post(route('registration.kuccps.store'), [
            'kuccps_index' => 'IDX-9001',
            'student_name' => 'E2E Scholar',
            'placed_programme' => 'BSc Computer Science',
            'gender' => 'F',
            'county' => 'Nairobi',
            'cluster_points' => 42.5,
            'mema_reg_no' => 'BCS/E2E/2026',
            'reporting_status' => 'Reported',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('registration.promotions.store'), [
            'student_name' => 'E2E Scholar',
            'reg_no' => 'BCS/E2E/2026',
            'programme' => 'BSc Computer Science',
            'from_stage' => 'Year 1',
            'to_stage' => 'Year 2',
            'cumulative_gpa' => 3.4,
            'credits_passed' => 36,
            'promotion_verdict' => 'Promoted',
            'senate_date' => '2027-02-01',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('registration.cpd.store'), [
            'participant_no' => 'CPD-77',
            'full_name' => 'Guest Professional',
            'organization' => 'Konza Tech Ltd',
            'course_enrolled' => 'Cybersecurity Short Course',
            'completion_progress' => '80%',
            'cpd_points_awarded' => 12,
            'certificate_ref' => 'CERT-77',
            'status' => 'Active',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('registration.moodle.store'), [
            'unit_code' => 'CS101',
            'unit_title' => 'Intro Computing',
            'moodle_course_id' => 'MD-101',
            'enrolled_students' => 40,
            'instructor_assigned' => 'Dr Lecturer',
            'sync_status' => 'OK',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('registration.info-updates.store'), [
            'request_no' => 'INF-01',
            'student_name' => 'E2E Scholar',
            'reg_no' => 'BCS/E2E/2026',
            'update_type' => 'Name',
            'current_details' => 'Old Name',
            'requested_details' => 'E2E Scholar',
            'verification_status' => 'Pending',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('registration.reminders.store'), [
            'campaign_code' => 'REM-REG',
            'title' => 'Register now',
            'target_audience' => 'Continuing students',
            'dispatch_channels' => 'SMS, Email',
            'trigger_schedule' => 'Daily 08:00',
            'total_recipients' => 200,
            'status' => 'Active',
        ])->assertRedirect()->assertSessionHasNoErrors();

        // Fees + enrolment vertical
        $this->post(route('fees.accounts.store'), [
            'code' => 'ACC-E2E',
            'name' => 'E2E Collection',
            'bank_name' => 'Equity Bank',
            'integration_type' => 'Bank IPN',
            'status' => 'ACTIVE',
        ])->assertRedirect();
        $this->post(route('fees.structures.store'), [
            'code' => 'FS-E2E',
            'title' => 'E2E tuition',
            'course_id' => $course->id,
            'tuition_amount' => 30000,
            'admin_amount' => 2000,
            'status' => 'ACTIVE',
        ])->assertRedirect();
        $this->post(route('registration.periods.store'), [
            'code' => 'REG-E2E',
            'title' => 'E2E Window',
            'starts_on' => '2027-01-01',
            'regular_deadline' => '2027-01-15',
            'late_deadline' => '2027-01-22',
            'status' => 'OPEN',
        ])->assertRedirect();

        $period = RegistrationPeriod::query()->where('code', 'REG-E2E')->firstOrFail();
        $account = FeePaymentAccount::query()->where('code', 'ACC-E2E')->firstOrFail();

        $this->post(route('registration.enrolments.store'), [
            'registration_period_id' => $period->id,
            'student_id' => $student->id,
        ])->assertRedirect();

        $invoice = FeeInvoice::query()->where('student_id', $student->id)->firstOrFail();
        $this->post(route('fees.payments.store'), [
            'fee_invoice_id' => $invoice->id,
            'payment_account_id' => $account->id,
            'amount' => 32000,
            'method' => 'MPESA',
            'transaction_ref' => 'E2E-PAY-1',
            'confirm' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('kuccps_placements', ['kuccps_index' => 'IDX-9001', 'reporting_status' => 'Reported']);
        $this->assertDatabaseHas('student_promotions', ['reg_no' => 'BCS/E2E/2026', 'promotion_verdict' => 'Promoted']);
        $this->assertDatabaseHas('cpd_enrolments', ['participant_no' => 'CPD-77']);
        $this->assertDatabaseHas('moodle_sync_logs', ['unit_code' => 'CS101', 'sync_status' => 'OK']);
        $this->assertDatabaseHas('student_info_update_requests', ['request_no' => 'INF-01']);
        $this->assertDatabaseHas('registration_reminder_campaigns', ['campaign_code' => 'REM-REG']);
        $this->assertSame('SETTLED', $invoice->fresh()->status);
        $this->assertDatabaseHas('fee_payments', ['transaction_ref' => 'E2E-PAY-1', 'status' => 'CONFIRMED']);

        // Every registration + fees screen renders live domain data
        $this->get(route('registration.kuccps-registration'))->assertOk()->assertSee('IDX-9001')->assertSee('Reported');
        $this->get(route('registration.promotions'))->assertOk()->assertSee('E2E Scholar')->assertSee('Promoted');
        $this->get(route('registration.professional-development-users'))->assertOk()->assertSee('CPD-77')->assertSee('Konza Tech Ltd');
        $this->get(route('registration.moodle-sync'))->assertOk()->assertSee('CS101')->assertSee('OK');
        $this->get(route('registration.student-info-update'))->assertOk()->assertSee('INF-01')->assertSee('Pending');
        $this->get(route('registration.reminders'))->assertOk()->assertSee('REM-REG')->assertSee('Active');
        $this->get(route('registration.course-registration-periods'))->assertOk()->assertSee('REG-E2E');
        $this->get(route('registration.student-registrations'))->assertOk()->assertSee('BCS/E2E/2026');
        $this->get(route('fees.fee-payables'))->assertOk()->assertSee($invoice->invoice_no)->assertSee('Settled');
        $this->get(route('fees.payment-receipt'))->assertOk()->assertSee('E2E Scholar')->assertSee('E2E-PAY-1');
        $this->get(route('fees.payment-accounts'))->assertOk()->assertSee('ACC-E2E')->assertSee('KES 32,000');
    }

    public function test_all_registration_and_fees_screens_render_without_demo_names(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->seedRbac();

        $routes = [
            'registration.application-verification',
            'registration.application-approval',
            'registration.rejected-list',
            'registration.kuccps-registration',
            'registration.student-registrations',
            'registration.course-registration-periods',
            'registration.promotions',
            'registration.professional-development-users',
            'registration.moodle-sync',
            'registration.student-info-update',
            'registration.reminders',
            'registration.user-registration',
            'registration.student-password',
            'registration.staff-password',
            'registration.password-reset',
            'fees.payment-accounts',
            'fees.payment-types',
            'fees.payment-source',
            'fees.fee-setup',
            'fees.fee-payables',
            'fees.pending-payments',
            'fees.payment-receipt',
        ];

        foreach ($routes as $route) {
            $this->actingAs($admin)->get(route($route))
                ->assertOk()
                ->assertDontSee('Brenda Chepkoech')
                ->assertDontSee('14,850')
                ->assertDontSee('Prof. Peter Ondieki');
        }

        $this->assertSame(0, KuccpsPlacement::query()->count());
        $this->assertSame(0, StudentPromotion::query()->count());
        $this->assertSame(0, CpdEnrolment::query()->count());
        $this->assertSame(0, MoodleSyncLog::query()->count());
        $this->assertSame(0, StudentInfoUpdateRequest::query()->count());
        $this->assertSame(0, RegistrationReminderCampaign::query()->count());
        $this->assertSame(0, FeePayment::query()->count());
        $this->assertSame(0, FeeStructure::query()->count());
    }

    public function test_staff_without_registration_grant_cannot_write_desk_records(): void
    {
        $this->seedRbac();
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);

        $this->actingAs($staff)->post(route('registration.kuccps.store'), [
            'kuccps_index' => 'IDX-X',
            'student_name' => 'Nope',
        ])->assertForbidden();
        $this->assertDatabaseCount('kuccps_placements', 0);
    }
}

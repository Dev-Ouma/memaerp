<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\GraduationAlumnus;
use App\Models\GraduationCeremony;
use App\Models\GraduationCeremonyReport;
use App\Models\GraduationCertificateTemplate;
use App\Models\GraduationClearanceNode;
use App\Models\GraduationCriterion;
use App\Models\GraduationFinanceClearance;
use App\Models\GraduationGradeEntry;
use App\Models\GraduationListBatch;
use App\Models\GraduationListReport;
use App\Models\GraduationListValidation;
use App\Models\GraduationPublication;
use App\Models\GraduationSummary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GraduationDeskEndToEndTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_graduation_desk_end_to_end(): void
    {
        $this->seedRbac();
        $officer = User::factory()->create(['role' => 'staff', 'is_active' => true, 'name' => 'Graduation Officer']);
        $this->grantRole($officer, 'graduation_officer');
        $this->actingAs($officer);

        $this->post(route('graduation.criteria.store'), [
            'programme' => 'BSc Computer Science',
            'min_credits' => '120',
            'min_cgpa' => '2.00',
            'thesis_required' => 'No',
            'clearance_nodes' => 'Finance, Library',
            'status' => 'Active',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('graduation.clearance-checklist.store'), [
            'node_name' => 'Library Clearance',
            'check_type' => 'Manual',
            'assigned_role' => 'Librarian',
            'requires_approval' => 'Yes',
            'status' => 'Active',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('graduation.finance-clearance.store'), [
            'student_name' => 'E2E Graduand',
            'reg_no' => 'BCS/GR/2026',
            'programme' => 'BSc Computer Science',
            'ledger_balance' => 'KES 0',
            'last_payment_date' => '2026-08-01',
            'status' => 'Cleared',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('graduation.grade-list.store'), [
            'student_name' => 'E2E Graduand',
            'reg_no' => 'BCS/GR/2026',
            'cgpa' => '3.45',
            'classification' => 'Second Upper',
            'grades_distribution' => 'A:4 B:8',
            'status' => 'Eligible',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('graduation.generate-list.store'), [
            'generation_run' => 'RUN-2026-01',
            'school' => 'School of Computing',
            'cohort' => '2022',
            'run_date' => '2026-09-01',
            'total_qualified' => 12,
            'status' => 'Pending',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('graduation.validate-list.store'), [
            'validation_code' => 'VAL-2026-01',
            'school' => 'School of Computing',
            'total_candidates' => 12,
            'dean_signoff' => 'Signed',
            'registrar_audit' => 'Passed',
            'status' => 'Validated',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('graduation.publish-list.store'), [
            'publication_code' => 'PUB-2026-01',
            'list_title' => 'September 2026 Graduands',
            'date_published' => '2026-09-10',
            'published_by' => 'Registrar',
            'total_graduands' => 12,
            'status' => 'Published',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('graduation.list-report.store'), [
            'report_ref' => 'RPT-LIST-01',
            'school' => 'School of Computing',
            'department' => 'Computer Science',
            'total_candidates' => 12,
            'file_format' => 'PDF',
            'status' => 'Verified',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('graduation.summary-list.store'), [
            'school' => 'School of Computing',
            'degree_count' => 10,
            'diploma_count' => 1,
            'masters_count' => 1,
            'phd_count' => 0,
            'total' => 12,
            'status' => 'Active',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('graduation.certification-setup.store'), [
            'template_code' => 'CERT-UG',
            'name' => 'Undergraduate Certificate',
            'dimensions' => 'A4 Landscape',
            'security_features' => 'Watermark, QR',
            'signatories' => 'VC, Registrar',
            'status' => 'Active',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('graduation.alumni-list.store'), [
            'alumni_code' => 'ALM-9001',
            'student_name' => 'E2E Graduand',
            'reg_no' => 'BCS/GR/2026',
            'programme' => 'BSc Computer Science',
            'contact' => 'e2e.grad@alumni.mema.ac.ke',
            'grad_year' => '2026',
            'status' => 'Engaged',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('graduation.ceremony.store'), [
            'congregation_number' => 'CONG-58',
            'date' => '2026-11-20',
            'chief_guest' => 'Cabinet Secretary',
            'gown_return_deadline' => '2026-11-27',
            'gown_fine_rate' => 'KES 500/day',
            'status' => 'Upcoming',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('graduation.ceremony-report.store'), [
            'report_ref' => 'RPT-CER-01',
            'title' => '58th Congregation Report',
            'audit_date' => '2026-11-21',
            'compiled_by' => 'Graduation Desk',
            'senate_submission' => 'Submitted',
            'status' => 'Published',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('graduation_criteria', ['programme' => 'BSc Computer Science']);
        $this->assertDatabaseHas('graduation_clearance_nodes', ['node_name' => 'Library Clearance']);
        $this->assertDatabaseHas('graduation_finance_clearances', ['reg_no' => 'BCS/GR/2026', 'status' => 'Cleared']);
        $this->assertDatabaseHas('graduation_grade_entries', ['classification' => 'Second Upper']);
        $this->assertDatabaseHas('graduation_list_batches', ['generation_run' => 'RUN-2026-01']);
        $this->assertDatabaseHas('graduation_list_validations', ['validation_code' => 'VAL-2026-01']);
        $this->assertDatabaseHas('graduation_publications', ['publication_code' => 'PUB-2026-01']);
        $this->assertDatabaseHas('graduation_list_reports', ['report_ref' => 'RPT-LIST-01']);
        $this->assertDatabaseHas('graduation_summaries', ['school' => 'School of Computing', 'total' => 12]);
        $this->assertDatabaseHas('graduation_certificate_templates', ['template_code' => 'CERT-UG']);
        $this->assertDatabaseHas('graduation_alumni', ['alumni_code' => 'ALM-9001']);
        $this->assertDatabaseHas('graduation_ceremonies', ['congregation_number' => 'CONG-58']);
        $this->assertDatabaseHas('graduation_ceremony_reports', ['report_ref' => 'RPT-CER-01']);

        $this->get(route('graduation.criteria'))->assertOk()->assertSee('BSc Computer Science')->assertSee('Finance, Library');
        $this->get(route('graduation.finance-clearance'))->assertOk()->assertSee('E2E Graduand')->assertSee('KES 0');
        $this->get(route('graduation.alumni-list'))->assertOk()->assertSee('ALM-9001')->assertSee('e2e.grad@alumni.mema.ac.ke');
        $this->get(route('graduation.ceremony'))->assertOk()->assertSee('CONG-58')->assertSee('Cabinet Secretary');
    }

    public function test_graduation_screens_render_empty_without_demo_noise(): void
    {
        $this->seedRbac();
        $officer = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $this->grantRole($officer, 'graduation_officer');

        foreach ([
            'graduation.criteria',
            'graduation.clearance-checklist',
            'graduation.finance-clearance',
            'graduation.grade-list',
            'graduation.generate-list',
            'graduation.validate-list',
            'graduation.publish-list',
            'graduation.list-report',
            'graduation.summary-list',
            'graduation.certification-setup',
            'graduation.alumni-list',
            'graduation.ceremony',
            'graduation.ceremony-report',
        ] as $route) {
            $this->actingAs($officer)->get(route($route))->assertOk();
        }

        $this->assertSame(0, GraduationCriterion::query()->count());
        $this->assertSame(0, GraduationClearanceNode::query()->count());
        $this->assertSame(0, GraduationFinanceClearance::query()->count());
        $this->assertSame(0, GraduationGradeEntry::query()->count());
        $this->assertSame(0, GraduationListBatch::query()->count());
        $this->assertSame(0, GraduationListValidation::query()->count());
        $this->assertSame(0, GraduationPublication::query()->count());
        $this->assertSame(0, GraduationListReport::query()->count());
        $this->assertSame(0, GraduationSummary::query()->count());
        $this->assertSame(0, GraduationCertificateTemplate::query()->count());
        $this->assertSame(0, GraduationAlumnus::query()->count());
        $this->assertSame(0, GraduationCeremony::query()->count());
        $this->assertSame(0, GraduationCeremonyReport::query()->count());
    }

    public function test_staff_without_graduation_manage_cannot_write(): void
    {
        $this->seedRbac();
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);

        $this->actingAs($staff)->post(route('graduation.criteria.store'), [
            'programme' => 'Denied Programme',
            'status' => 'Active',
        ])->assertForbidden();

        $this->assertDatabaseMissing('graduation_criteria', ['programme' => 'Denied Programme']);
    }
}

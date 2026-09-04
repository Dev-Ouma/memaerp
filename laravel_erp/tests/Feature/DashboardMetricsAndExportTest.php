<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DashboardMetricsAndExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_loads_with_real_database_metrics(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Create sample records
        $studentUser = User::factory()->create(['role' => 'student', 'gender' => 'F']);
        $course = Course::create(['code' => 'BCS', 'name' => 'Bachelor of Computer Science', 'faculty' => 'School of Computing']);
        Student::create([
            'user_id' => $studentUser->id,
            'course_id' => $course->id,
            'admission_number' => 'BCS/2026/001',
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Institutional Operations & Telemetry Filters', false);
        $response->assertSee('Academic Year');
        $response->assertSee('Semester / Term');
        $response->assertSee('Cohort / Intake');
        $response->assertSee('Programme');
        $response->assertSee('Study Level');
        $response->assertSee('Live PostgreSQL Data');
        $response->assertSee('Export Report / Data');
        $response->assertSee('Applications');
        $response->assertSee('Admitted');
        $response->assertSee('Enrolled');
        $response->assertSee('Graduated');
    }

    public function test_admin_dashboard_filters_end_to_end(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::create(['code' => 'BBA', 'name' => 'Bachelor of Business Administration', 'faculty' => 'School of Business']);

        $response = $this->actingAs($admin)->get(route('dashboard', [
            'academic_year' => '2026/2027',
            'semester' => 'Semester 1',
            'cohort' => '2026/2027 - September Intake',
            'programme' => 'BBA',
            'level' => 'undergraduate',
        ]));

        $response->assertOk();
        $response->assertSee('5 Active Filters');
        $response->assertSee('BBA');
        $response->assertSee('2026/2027');
        $response->assertSee('Clear All Filters');
    }

    public function test_admin_dashboard_filters_by_individual_criteria(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $bcs = Course::create(['code' => 'BCS', 'name' => 'Bachelor of Computer Science', 'faculty' => 'School of Computing']);
        $msc = Course::create(['code' => 'MSC-DS', 'name' => 'Master of Science in Data Science', 'faculty' => 'School of Computing']);

        $studentUser1 = User::factory()->create(['role' => 'student', 'gender' => 'F']);
        $studentUser2 = User::factory()->create(['role' => 'student', 'gender' => 'M']);

        Student::create(['user_id' => $studentUser1->id, 'course_id' => $bcs->id, 'admission_number' => 'BCS/2026/001']);
        Student::create(['user_id' => $studentUser2->id, 'course_id' => $msc->id, 'admission_number' => 'MSC/2026/002']);

        // Filter by Programme BCS
        $resProg = $this->actingAs($admin)->get(route('dashboard', ['programme' => 'BCS']));
        $resProg->assertOk();
        $resProg->assertSee('1 Active Filter');
        $resProg->assertSee('BCS');

        // Filter by Level: Postgraduate
        $resLevel = $this->actingAs($admin)->get(route('dashboard', ['level' => 'Postgraduate']));
        $resLevel->assertOk();
        $resLevel->assertSee('1 Active Filter');
        $resLevel->assertSee('Postgraduate');

        // Filter by Academic Year
        $resYear = $this->actingAs($admin)->get(route('dashboard', ['academic_year' => '2026/2027']));
        $resYear->assertOk();
        $resYear->assertSee('1 Active Filter');
        $resYear->assertSee('2026/2027');

        // Filter by Cohort
        $resCohort = $this->actingAs($admin)->get(route('dashboard', ['cohort' => 'September Intake']));
        $resCohort->assertOk();
        $resCohort->assertSee('1 Active Filter');
        $resCohort->assertSee('September Intake');

        // Filter by Semester
        $resSem = $this->actingAs($admin)->get(route('dashboard', ['semester' => 'Semester 1']));
        $resSem->assertOk();
        $resSem->assertSee('1 Active Filter');
        $resSem->assertSee('Semester 1');
    }

    public function test_dashboard_csv_export_returns_valid_stream_with_bom(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('dashboard.export', [
            'dataset' => 'applications',
            'format' => 'csv',
        ]));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename="mema_applications_', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_dashboard_xlsx_export_returns_valid_openxml_sheet(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('dashboard.export', [
            'dataset' => 'enrolments',
            'format' => 'xlsx',
        ]));

        $response->assertOk();
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('.xlsx', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_dashboard_pdf_export_renders_quicksand_and_brand_colors(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('dashboard.export', [
            'dataset' => 'executive_kpis',
            'format' => 'pdf',
        ]));

        $response->assertOk();
        $response->assertSee('MEMA UNIVERSITY COLLEGE');
        $response->assertSee('Quicksand');
        $response->assertSee('#0A3E50');
        $response->assertSee('#E67E22');
        $response->assertSee('Official Record');
    }

    public function test_all_datasets_are_exportable(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $datasets = [
            'applications',
            'admissions',
            'enrolments',
            'graduated',
            'programmes',
            'financials',
            'demographics',
            'staff',
            'executive_kpis',
        ];

        foreach ($datasets as $ds) {
            $csvRes = $this->actingAs($admin)->get(route('dashboard.export', ['dataset' => $ds, 'format' => 'csv']));
            $csvRes->assertOk();

            $xlsxRes = $this->actingAs($admin)->get(route('dashboard.export', ['dataset' => $ds, 'format' => 'xlsx']));
            $xlsxRes->assertOk();

            $pdfRes = $this->actingAs($admin)->get(route('dashboard.export', ['dataset' => $ds, 'format' => 'pdf']));
            $pdfRes->assertOk();
        }
    }

    public function test_dashboard_records_preview_endpoint_returns_json_payload(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('dashboard.records-preview', ['dataset' => 'applications']));

        $response->assertOk();
        $response->assertJsonStructure([
            'dataset',
            'title',
            'headers',
            'rows',
            'total',
            'summary',
        ]);
    }
}

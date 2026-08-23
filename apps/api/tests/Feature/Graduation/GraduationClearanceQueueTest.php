<?php

declare(strict_types=1);

namespace Tests\Feature\Graduation;

use App\Modules\Graduation\Models\GraduationApplication;
use App\Modules\Graduation\Models\GraduationClearanceCheckpoint;
use App\Modules\Iam\Database\Seeders\PermissionSeeder;
use App\Modules\Iam\Database\Seeders\RoleSeeder;
use App\Modules\Iam\Models\User;
use App\Modules\Institution\Database\Seeders\InstitutionSeeder;
use App\Modules\Student\Models\Student;
use Database\Seeders\AdmissionsAndFinanceSeeder;
use Database\Seeders\CurriculumAndCourseSeeder;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\StudentLifecycleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class GraduationClearanceQueueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(InstitutionSeeder::class);
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        $this->seed(DemoUserSeeder::class);
        $this->seed(CurriculumAndCourseSeeder::class);
        $this->seed(AdmissionsAndFinanceSeeder::class);
        $this->seed(StudentLifecycleSeeder::class);
    }

    public function test_registrar_can_view_and_clear_pending_checkpoints(): void
    {
        $student = Student::query()->firstOrFail();
        $application = GraduationApplication::query()->create([
            'institution_id' => $student->institution_id,
            'student_id' => $student->id,
            'programme_id' => $student->programme_id,
            'curriculum_version_id' => $student->curriculum_version_id,
            'status' => 'PENDING',
            'audit_credits_required' => 120,
            'audit_credits_earned' => 120,
            'audit_passed' => true,
            'applied_at' => now(),
        ]);
        $checkpoint = GraduationClearanceCheckpoint::query()->create([
            'institution_id' => $student->institution_id,
            'graduation_application_id' => $application->id,
            'department_code' => 'REG',
            'department_name' => 'Registry',
            'status' => 'PENDING',
        ]);

        $registrar = User::query()->where('email', 'registrar@mema.ac.ke')->firstOrFail();
        Sanctum::actingAs($registrar);

        $this->getJson('/api/v1/graduation/clearance-queue')
            ->assertOk()
            ->assertJsonFragment(['id' => $checkpoint->id]);

        $this->postJson("/api/v1/graduation/checkpoints/{$checkpoint->id}/clear", [
            'notes' => 'Registry clearance complete.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'CLEARED');
    }

    public function test_student_clearance_status_includes_finance_snapshot(): void
    {
        $studentUser = User::query()->where('email', 'student@mema.ac.ke')->firstOrFail();
        Sanctum::actingAs($studentUser);

        $this->getJson('/api/v1/graduation/clearance-status')
            ->assertOk()
            ->assertJsonStructure(['data' => ['audit', 'finance_clearance']]);
    }
}

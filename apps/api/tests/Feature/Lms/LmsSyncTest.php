<?php

declare(strict_types=1);

namespace Tests\Feature\Lms;

use App\Modules\Course\Models\CourseOffering;
use App\Modules\Enrollment\Models\CourseEnrollment;
use App\Modules\Iam\Database\Seeders\PermissionSeeder;
use App\Modules\Iam\Database\Seeders\RoleSeeder;
use App\Modules\Iam\Models\User;
use App\Modules\Institution\Database\Seeders\InstitutionSeeder;
use App\Modules\Lms\Models\CourseMapping;
use App\Modules\Student\Models\Student;
use Database\Seeders\AdmissionsAndFinanceSeeder;
use Database\Seeders\CurriculumAndCourseSeeder;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\StudentLifecycleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class LmsSyncTest extends TestCase
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

    public function test_admin_can_sync_course_offering_to_moodle_stub(): void
    {
        $admin = User::query()->where('email', 'admin@mema.ac.ke')->firstOrFail();
        Sanctum::actingAs($admin);
        $offering = CourseOffering::query()->firstOrFail();

        $this->postJson('/api/v1/lms/sync/courses', ['offering_id' => $offering->id])
            ->assertCreated()
            ->assertJsonPath('data.status', 'SYNCED');

        $this->assertDatabaseHas('lms.course_mappings', [
            'course_offering_id' => $offering->id,
            'status' => 'SYNCED',
        ]);
    }

    public function test_student_can_get_moodle_launch_url(): void
    {
        $studentUser = User::query()->where('email', 'student@mema.ac.ke')->firstOrFail();
        Sanctum::actingAs($studentUser);

        $this->getJson('/api/v1/lms/launch')
            ->assertOk()
            ->assertJsonStructure(['data' => ['url']]);
    }

    public function test_enrollment_sync_is_idempotent(): void
    {
        $admin = User::query()->where('email', 'admin@mema.ac.ke')->firstOrFail();
        Sanctum::actingAs($admin);

        $enrollment = CourseEnrollment::query()->where('status', 'ENROLLED')->firstOrFail();
        CourseMapping::query()->create([
            'institution_id' => $enrollment->institution_id,
            'course_offering_id' => $enrollment->course_offering_id,
            'moodle_course_id' => 101,
            'moodle_shortname' => 'TEST-101',
            'status' => 'SYNCED',
            'last_synced_at' => now(),
        ]);

        $this->postJson('/api/v1/lms/sync/enrollments', ['enrollment_id' => $enrollment->id])
            ->assertCreated()
            ->assertJsonPath('data.status', 'SYNCED');

        $this->assertSame(1, \App\Modules\Lms\Models\EnrollmentMapping::query()->where('course_enrollment_id', $enrollment->id)->count());
    }

    public function test_sync_status_dashboard_returns_health_metrics(): void
    {
        $admin = User::query()->where('email', 'admin@mema.ac.ke')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/lms/sync/status')
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['enabled', 'queue_depth', 'failed_count', 'course_mappings', 'enrollment_mappings', 'recent'],
            ]);
    }
}

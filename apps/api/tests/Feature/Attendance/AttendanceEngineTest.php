<?php

declare(strict_types=1);

namespace Tests\Feature\Attendance;

use App\Modules\Course\Models\CourseOffering;
use App\Modules\Iam\Database\Seeders\PermissionSeeder;
use App\Modules\Iam\Database\Seeders\RoleSeeder;
use App\Modules\Iam\Models\User;
use App\Modules\Institution\Database\Seeders\InstitutionSeeder;
use Database\Seeders\AdmissionsAndFinanceSeeder;
use Database\Seeders\CurriculumAndCourseSeeder;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\StudentLifecycleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class AttendanceEngineTest extends TestCase
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

    public function test_lecturer_can_open_session_and_student_can_check_in(): void
    {
        $lecturer = User::query()->where('email', 'lecturer@mema.ac.ke')->firstOrFail();
        $studentUser = User::query()->where('email', 'student@mema.ac.ke')->firstOrFail();
        $offering = CourseOffering::query()->where('lecturer_id', $lecturer->id)->first()
            ?? CourseOffering::query()->firstOrFail();

        if ($offering->lecturer_id !== $lecturer->id) {
            $offering->update(['lecturer_id' => $lecturer->id]);
        }

        Sanctum::actingAs($lecturer);
        $open = $this->postJson('/api/v1/attendance/sessions/open', [
            'offering_id' => $offering->id,
        ])->assertCreated();

        $token = $open->json('data.qr_token');
        $sessionId = $open->json('data.session.id');
        $this->assertNotEmpty($token);

        Sanctum::actingAs($studentUser);
        $this->postJson('/api/v1/attendance/check-in', ['token' => $token])
            ->assertCreated()
            ->assertJsonPath('data.status', 'PRESENT');

        Sanctum::actingAs($lecturer);
        $this->postJson("/api/v1/attendance/sessions/{$sessionId}/close")
            ->assertOk()
            ->assertJsonPath('data.status', 'CLOSED');
    }

    public function test_student_can_view_own_attendance_record(): void
    {
        $studentUser = User::query()->where('email', 'student@mema.ac.ke')->firstOrFail();
        Sanctum::actingAs($studentUser);

        $this->getJson('/api/v1/attendance/my-record')
            ->assertOk()
            ->assertJsonStructure(['data' => ['student_id', 'courses']]);
    }

    public function test_lecturer_can_view_course_attendance_report(): void
    {
        $lecturer = User::query()->where('email', 'lecturer@mema.ac.ke')->firstOrFail();
        $offering = CourseOffering::query()->firstOrFail();
        if ($offering->lecturer_id !== $lecturer->id) {
            $offering->update(['lecturer_id' => $lecturer->id]);
        }

        Sanctum::actingAs($lecturer);
        $this->getJson("/api/v1/attendance/course/{$offering->id}/report")
            ->assertOk()
            ->assertJsonStructure(['data' => ['offering', 'sessions', 'students']]);
    }
}

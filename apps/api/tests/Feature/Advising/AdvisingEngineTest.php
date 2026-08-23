<?php

declare(strict_types=1);

namespace Tests\Feature\Advising;

use App\Modules\Advising\Models\AdvisorAssignment;
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

final class AdvisingEngineTest extends TestCase
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

    public function test_hod_can_assign_advisor_and_lecturer_sees_advisee(): void
    {
        $hod = User::query()->where('email', 'registrar@mema.ac.ke')->firstOrFail();
        $lecturer = User::query()->where('email', 'lecturer@mema.ac.ke')->firstOrFail();
        $student = Student::query()->firstOrFail();

        Sanctum::actingAs($hod);
        $this->postJson('/api/v1/advising/assignments', [
            'student_id' => $student->id,
            'advisor_user_id' => $lecturer->id,
            'assignment_reason' => 'Department advising allocation',
        ])->assertCreated()
            ->assertJsonPath('data.is_active', true);

        $this->assertDatabaseHas('advising.advisor_assignments', [
            'student_id' => $student->id,
            'advisor_user_id' => $lecturer->id,
            'is_active' => true,
        ]);

        Sanctum::actingAs($lecturer);
        $this->getJson('/api/v1/advising/my-advisees')
            ->assertOk()
            ->assertJsonFragment(['student_number' => $student->student_number]);

        $this->getJson("/api/v1/advising/student/{$student->id}/degree-audit")
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'credits_required', 'credits_earned', 'completed', 'in_progress', 'remaining', 'recommendations',
            ]]);
    }

    public function test_advisor_can_add_note_and_student_sees_visible_progress(): void
    {
        $lecturer = User::query()->where('email', 'lecturer@mema.ac.ke')->firstOrFail();
        $studentUser = User::query()->where('email', 'student@mema.ac.ke')->firstOrFail();
        $student = Student::query()->where('person_id', $studentUser->person_id)->firstOrFail();

        AdvisorAssignment::query()->create([
            'institution_id' => $student->institution_id,
            'advisor_user_id' => $lecturer->id,
            'student_id' => $student->id,
            'assigned_at' => now(),
            'is_active' => true,
            'assigned_by' => $lecturer->id,
        ]);

        Sanctum::actingAs($lecturer);
        $this->postJson('/api/v1/advising/notes', [
            'student_id' => $student->id,
            'note_text' => 'Please prioritise remaining core units next term.',
            'note_type' => 'RECOMMENDATION',
            'visible_to_student' => true,
        ])->assertCreated();

        Sanctum::actingAs($studentUser);
        $this->getJson('/api/v1/advising/my-progress')
            ->assertOk()
            ->assertJsonPath('data.student_id', $student->id)
            ->assertJsonPath('data.advisor.email', 'lecturer@mema.ac.ke');
    }

    public function test_student_can_request_advisory_session(): void
    {
        $lecturer = User::query()->where('email', 'lecturer@mema.ac.ke')->firstOrFail();
        $studentUser = User::query()->where('email', 'student@mema.ac.ke')->firstOrFail();
        $student = Student::query()->where('person_id', $studentUser->person_id)->firstOrFail();

        AdvisorAssignment::query()->create([
            'institution_id' => $student->institution_id,
            'advisor_user_id' => $lecturer->id,
            'student_id' => $student->id,
            'assigned_at' => now(),
            'is_active' => true,
        ]);

        Sanctum::actingAs($studentUser);
        $this->postJson('/api/v1/advising/sessions/request', [
            'scheduled_at' => now()->addDays(3)->toIso8601String(),
            'mode' => 'ONLINE',
            'topic' => 'Course selection for next semester',
        ])->assertCreated()
            ->assertJsonPath('data.status', 'REQUESTED');
    }
}

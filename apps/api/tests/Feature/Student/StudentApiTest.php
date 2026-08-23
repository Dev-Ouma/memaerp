<?php

declare(strict_types=1);

namespace Tests\Feature\Student;

use App\Modules\Curriculum\Models\Programme;
use App\Modules\Institution\Models\AcademicYear;
use App\Modules\Institution\Models\Campus;
use App\Modules\Institution\Models\Department;
use App\Modules\Student\Models\Person;
use App\Modules\Student\Models\Student;
use App\Platform\Support\Scope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StudentApiTest extends TestCase
{
    use RefreshDatabase;

    private Department $computerScience;

    private Department $nursing;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedReferenceData();
        $this->computerScience = Department::query()->where('code', 'CS')->firstOrFail();
        $this->nursing = Department::query()->where('code', 'NUR')->firstOrFail();
    }

    #[Test]
    public function unauthenticated_callers_are_rejected(): void
    {
        $this->getJson('/api/v1/students')->assertUnauthorized();
    }

    #[Test]
    public function users_without_the_view_permission_are_rejected(): void
    {
        $this->actingAs($this->userWithNoRoles())
            ->getJson('/api/v1/students')
            ->assertForbidden();
    }

    #[Test]
    public function a_department_scoped_user_sees_only_students_in_their_department(): void
    {
        $visible = $this->makeStudent($this->computerScience, 'MEMA/CS/001');
        $this->makeStudent($this->nursing, 'MEMA/NUR/001');
        $hod = $this->userWithRole('head-of-department', Scope::department($this->computerScience->id));

        $response = $this->actingAs($hod)->getJson('/api/v1/students');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $visible->id)
            ->assertJsonPath('meta.pagination.has_more', false)
            ->assertJsonStructure(['meta' => ['request_id']]);
    }

    #[Test]
    public function an_out_of_scope_student_is_hidden_as_not_found(): void
    {
        $outside = $this->makeStudent($this->nursing, 'MEMA/NUR/002');
        $hod = $this->userWithRole('head-of-department', Scope::department($this->computerScience->id));

        $this->actingAs($hod)
            ->getJson("/api/v1/students/{$outside->id}")
            ->assertNotFound();
    }

    #[Test]
    public function list_filters_and_search_are_validated_and_applied(): void
    {
        $this->makeStudent($this->computerScience, 'MEMA/CS/ACTIVE', 'ACTIVE', 'Amina');
        $this->makeStudent($this->computerScience, 'MEMA/CS/LEAVE', 'ON_LEAVE', 'Kamau');
        $registrar = $this->userWithRole('registrar-academic', Scope::institution());

        $this->actingAs($registrar)
            ->getJson('/api/v1/students?filter[status]=ACTIVE&filter[search]=Amina')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.student_number', 'MEMA/CS/ACTIVE');

        $this->actingAs($registrar)
            ->getJson('/api/v1/students?filter[unknown]=value')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('filter');
    }

    #[Test]
    public function a_registrar_can_update_mutable_fields_with_an_audited_reason(): void
    {
        $student = $this->makeStudent($this->computerScience, 'MEMA/CS/003');
        $registrar = $this->userWithRole('registrar-academic', Scope::institution());

        $this->actingAs($registrar)
            ->patchJson("/api/v1/students/{$student->id}", [
                'status' => 'ON_LEAVE',
                'change_reason' => 'Approved leave of absence for the current term.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'ON_LEAVE');

        $this->assertDatabaseHas('student.students', [
            'id' => $student->id,
            'status' => 'ON_LEAVE',
        ]);
        $this->assertDatabaseHas('audit.activity_log', [
            'auditable_id' => $student->id,
            'event' => 'updated',
            'reason' => 'Approved leave of absence for the current term.',
        ]);
    }

    #[Test]
    public function readers_cannot_update_and_updates_require_a_change_and_reason(): void
    {
        $student = $this->makeStudent($this->computerScience, 'MEMA/CS/004');
        $hod = $this->userWithRole('head-of-department', Scope::department($this->computerScience->id));
        $registrar = $this->userWithRole('registrar-academic', Scope::institution());

        $this->actingAs($hod)
            ->patchJson("/api/v1/students/{$student->id}", [
                'status' => 'SUSPENDED',
                'change_reason' => 'Attempted unauthorized status change.',
            ])
            ->assertForbidden();

        $this->actingAs($registrar)
            ->patchJson("/api/v1/students/{$student->id}", [
                'change_reason' => 'No actual student field was supplied.',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('record');
    }

    private function makeStudent(
        Department $department,
        string $studentNumber,
        string $status = 'ACTIVE',
        string $givenName = 'Test',
    ): Student {
        $person = Person::query()->create([
            'institution_id' => $this->institution->id,
            'given_name' => $givenName,
            'family_name' => 'Student',
            'primary_email' => strtolower($studentNumber).'@example.test',
        ]);
        $programme = Programme::query()->create([
            'institution_id' => $this->institution->id,
            'department_id' => $department->id,
            'code' => 'P-'.str_replace('/', '-', $studentNumber),
            'name' => $department->name.' Programme',
            'award_level' => 'BACHELORS',
            'duration_years' => 4,
            'total_credits_required' => 120,
            'is_active' => true,
        ]);

        return Student::query()->create([
            'institution_id' => $this->institution->id,
            'person_id' => $person->id,
            'programme_id' => $programme->id,
            'campus_id' => Campus::query()->where('code', 'MAIN')->firstOrFail()->id,
            'department_id' => $department->id,
            'faculty_id' => $department->faculty_id,
            'admission_year_id' => AcademicYear::query()->where('is_current', true)->firstOrFail()->id,
            'student_number' => $studentNumber,
            'status' => $status,
            'academic_standing' => 'GOOD_STANDING',
            'current_year_level' => 1,
            'current_semester' => 1,
            'matriculated_on' => now()->toDateString(),
        ]);
    }
}

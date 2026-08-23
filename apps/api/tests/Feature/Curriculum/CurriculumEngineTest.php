<?php

declare(strict_types=1);

namespace Tests\Feature\Curriculum;

use App\Modules\Course\Models\Course;
use App\Modules\Curriculum\Models\ApprovalLedger;
use App\Modules\Curriculum\Models\CurriculumCourse;
use App\Modules\Curriculum\Models\CurriculumVersion;
use App\Modules\Curriculum\Models\Programme;
use App\Modules\Iam\Models\Role;
use App\Modules\Iam\Models\RoleAssignment;
use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\AcademicYear;
use App\Modules\Institution\Models\Campus;
use App\Modules\Institution\Models\Department;
use App\Modules\Student\Models\Person;
use App\Modules\Student\Models\Student;
use App\Platform\Support\Scope;
use Database\Seeders\CurriculumAndCourseSeeder;
use Database\Seeders\DemoUserSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use LogicException;
use Tests\TestCase;

final class CurriculumEngineTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
        $this->seed(DemoUserSeeder::class);
        $this->seed(CurriculumAndCourseSeeder::class);
        Notification::fake();
        $this->admin = $this->userWithRole('senate-member', Scope::institution());
        $registrarRole = Role::query()->where('institution_id', $this->institution->id)->where('code', 'registrar-academic')->firstOrFail();
        RoleAssignment::query()->create([
            'institution_id' => $this->institution->id,
            'user_id' => $this->admin->id,
            'role_id' => $registrarRole->id,
            'scope_type' => Scope::INSTITUTION,
            'scope_id' => null,
            'grant_reason' => 'Curriculum workflow test operator',
        ]);
        $this->admin->unsetRelation('roleAssignments');
        $this->token = $this->admin->createToken('curriculum-tests')->plainTextToken;
    }

    public function test_builds_reviews_approves_and_locks_a_complete_curriculum(): void
    {
        [$programme, $version, $courses] = $this->draft('SOFT', 7);
        $this->map($version, $courses['CSC 101'], 1, 1);
        $this->map($version, $courses['CSC 102'], 1, 2);

        $this->authorized()->postJson("/api/v1/curriculum/versions/{$version->id}/requirements", [
            'course_id' => $courses['CSC 102']->id,
            'required_course_id' => $courses['CSC 101']->id,
            'requirement_type' => 'PREREQUISITE',
        ])->assertCreated();

        $this->authorized()->postJson("/api/v1/curriculum/versions/{$version->id}/submit")
            ->assertOk()->assertJsonPath('data.status', 'UNDER_REVIEW')
            ->assertJsonPath('data.review_steps.0.stage', 'HOD')
            ->assertJsonCount(4, 'data.review_steps');

        foreach (['HOD', 'DEAN', 'ACADEMIC_BOARD', 'SENATE'] as $stage) {
            $this->approve($version, $stage)->assertOk();
        }

        $approved = $version->fresh();
        self::assertInstanceOf(CurriculumVersion::class, $approved);
        self::assertSame('APPROVED', $approved->status);
        self::assertTrue($approved->is_approved);
        self::assertNotNull($approved->locked_at);
        self::assertSame(64, strlen((string) $approved->structure_hash));
        self::assertTrue(ApprovalLedger::query()->where('curriculum_version_id', $version->id)->exists());

        $this->authorized()->postJson("/api/v1/curriculum/versions/{$version->id}/courses", [
            'course_id' => $courses['MAT 101']->id, 'year_level' => 2, 'semester' => 1, 'course_type' => 'CORE',
        ])->assertStatus(409)->assertJsonPath('error.code', 'ERR-CUR-002');

        $entry = CurriculumCourse::query()->where('curriculum_version_id', $version->id)->firstOrFail();
        $this->expectException(QueryException::class);
        $entry->delete();
    }

    public function test_rejects_a_cyclic_prerequisite_graph(): void
    {
        [, $version, $courses] = $this->draft('CYBER', 7);
        $this->map($version, $courses['CSC 101'], 1, 1);
        $this->map($version, $courses['CSC 102'], 1, 2);

        $this->authorized()->postJson("/api/v1/curriculum/versions/{$version->id}/requirements", [
            'course_id' => $courses['CSC 102']->id,
            'required_course_id' => $courses['CSC 101']->id,
            'requirement_type' => 'PREREQUISITE',
        ])->assertCreated();

        $this->authorized()->postJson("/api/v1/curriculum/versions/{$version->id}/requirements", [
            'course_id' => $courses['CSC 101']->id,
            'required_course_id' => $courses['CSC 102']->id,
            'requirement_type' => 'PREREQUISITE',
        ])->assertStatus(400)->assertJsonPath('error.code', 'ERR-CUR-CYCLE');
    }

    public function test_supports_editing_and_removing_draft_grid_entries_and_elective_clusters(): void
    {
        [, $version, $courses] = $this->draft('DATA', 6);
        $group = $this->authorized()->postJson("/api/v1/curriculum/versions/{$version->id}/elective-groups", [
            'code' => 'TECH', 'name' => 'Technical electives', 'minimum_courses' => 1, 'minimum_credits' => 3,
        ])->assertCreated()->json('data');
        $this->map($version, $courses['CSC 101'], 1, 1);
        $item = $this->map($version, $courses['MAT 101'], 1, 2, 'ELECTIVE', $group['id']);

        $this->authorized()->patchJson("/api/v1/curriculum/versions/{$version->id}/courses/{$item['id']}", [
            'year_level' => 2, 'semester' => 1,
        ])->assertOk()->assertJsonPath('data.year_level', 2);

        $requirement = $this->authorized()->postJson("/api/v1/curriculum/versions/{$version->id}/requirements", [
            'course_id' => $courses['MAT 101']->id,
            'required_course_id' => $courses['CSC 101']->id,
            'requirement_type' => 'COREQUISITE',
        ])->assertCreated()->json('data');
        $this->authorized()->deleteJson("/api/v1/curriculum/versions/{$version->id}/requirements/{$requirement['id']}")->assertOk();
        $this->authorized()->deleteJson("/api/v1/curriculum/versions/{$version->id}/courses/{$item['id']}")->assertOk();
        $this->authorized()->deleteJson("/api/v1/curriculum/versions/{$version->id}/elective-groups/{$group['id']}")->assertOk();
    }

    public function test_enforces_credit_integrity_before_senate_approval(): void
    {
        [, $version, $courses] = $this->draft('CREDIT', 10);
        $this->map($version, $courses['CSC 101'], 1, 1);
        $this->map($version, $courses['CSC 102'], 1, 2);
        $this->authorized()->postJson("/api/v1/curriculum/versions/{$version->id}/submit")->assertOk();
        foreach (['HOD', 'DEAN', 'ACADEMIC_BOARD'] as $stage) {
            $this->approve($version, $stage)->assertOk();
        }
        $this->approve($version, 'SENATE')->assertUnprocessable()
            ->assertJsonPath('error.fields.graduation_credits_required.0', 'Core credits (7) plus minimum elective credits (0) must equal 10.');
        self::assertSame('UNDER_REVIEW', $version->fresh()?->status);
    }

    public function test_assigns_only_unassigned_students_in_the_selected_cohort(): void
    {
        [$programme, $version, $courses] = $this->draft('AI', 7);
        $this->map($version, $courses['CSC 101'], 1, 1);
        $this->map($version, $courses['CSC 102'], 1, 2);
        $this->authorized()->postJson("/api/v1/curriculum/versions/{$version->id}/submit")->assertOk();
        foreach (['HOD', 'DEAN', 'ACADEMIC_BOARD', 'SENATE'] as $stage) {
            $this->approve($version, $stage)->assertOk();
        }
        $year = AcademicYear::query()->where('institution_id', $this->institution->id)->current()->firstOrFail();
        $student = $this->student($programme, $year);

        $this->authorized()->postJson("/api/v1/curriculum/versions/{$version->id}/assign-cohort", [
            'admission_year_id' => $year->id,
        ])->assertOk()->assertJsonPath('data.assigned_count', 1);
        self::assertSame($version->id, $student->fresh()?->curriculum_version_id);

        $this->authorized()->postJson("/api/v1/curriculum/versions/{$version->id}/assign-cohort", [
            'admission_year_id' => $year->id,
        ])->assertOk()->assertJsonPath('data.assigned_count', 0);
    }

    public function test_exports_curriculum_pdf_and_csv_and_flags_expiring_accreditation(): void
    {
        $programme = Programme::query()->where('code', 'BSC-CS')->firstOrFail();
        $programme->forceFill(['accreditation_expires_on' => now()->addMonth(), 'accreditation_body' => 'CUE'])->save();
        $version = $programme->versions()->firstOrFail();

        $this->authorized()->getJson('/api/v1/curriculum/programmes')
            ->assertOk()->assertJsonFragment(['code' => 'BSC-CS', 'accreditation_warning' => true]);

        $csv = $this->authorized()->get("/api/v1/curriculum/versions/{$version->id}/report?format=csv")
            ->assertOk()->assertDownload();
        self::assertStringContainsString('course_code,course_title', $csv->streamedContent());

        $pdf = $this->authorized()->get("/api/v1/curriculum/versions/{$version->id}/report?format=pdf")
            ->assertOk()->assertDownload();
        self::assertStringStartsWith('%PDF', (string) $pdf->getContent());
    }

    public function test_denies_curriculum_management_to_an_unprivileged_user(): void
    {
        $user = $this->userWithNoRoles();
        $token = $user->createToken('denied')->plainTextToken;
        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/curriculum/versions', [])->assertForbidden();
    }

    /** @return array{Programme, CurriculumVersion, array<string, Course>} */
    private function draft(string $code, int $credits): array
    {
        $department = Department::query()->where('institution_id', $this->institution->id)->where('code', 'CS')->firstOrFail();
        $year = AcademicYear::query()->where('institution_id', $this->institution->id)->current()->firstOrFail();
        $programmeData = $this->authorized()->postJson('/api/v1/curriculum/programmes', [
            'department_id' => $department->id,
            'code' => "BSC-{$code}",
            'name' => "Bachelor of {$code}",
            'award_level' => 'BACHELORS',
            'duration_years' => 4,
            'total_credits_required' => $credits,
            'minimum_residency_credits' => $credits,
        ])->assertCreated()->json('data');
        $versionData = $this->authorized()->postJson('/api/v1/curriculum/versions', [
            'programme_id' => $programmeData['id'],
            'effective_year_id' => $year->id,
            'version_code' => '2026-V1',
            'graduation_credits_required' => $credits,
        ])->assertCreated()->json('data');
        $courses = [];
        foreach (Course::query()->where('institution_id', $this->institution->id)->get() as $course) {
            $courses[$course->code] = $course;
        }

        return [
            Programme::query()->whereKey($programmeData['id'])->firstOrFail(),
            CurriculumVersion::query()->whereKey($versionData['id'])->firstOrFail(),
            $courses,
        ];
    }

    /** @return array<string, mixed> */
    private function map(CurriculumVersion $version, Course $course, int $year, int $semester, string $type = 'CORE', ?string $groupId = null): array
    {
        return $this->authorized()->postJson("/api/v1/curriculum/versions/{$version->id}/courses", [
            'course_id' => $course->id,
            'year_level' => $year,
            'semester' => $semester,
            'course_type' => $type,
            'elective_group_id' => $groupId,
        ])->assertCreated()->json('data');
    }

    /** @return TestResponse<JsonResponse> */
    private function approve(CurriculumVersion $version, string $stage): TestResponse
    {
        return $this->authorized()->postJson("/api/v1/curriculum/versions/{$version->id}/approve", [
            'stage' => $stage,
            'reference' => "{$stage}-2026-001",
        ]);
    }

    private function student(Programme $programme, AcademicYear $year): Student
    {
        $department = $programme->department;
        if (! $department instanceof Department) {
            throw new LogicException('The test programme requires a department.');
        }
        $campus = Campus::query()->where('institution_id', $this->institution->id)->firstOrFail();
        $person = Person::query()->create([
            'institution_id' => $this->institution->id,
            'given_name' => 'Cohort',
            'family_name' => 'Student',
            'primary_email' => 'cohort.student@mema.ac.ke',
        ]);

        return Student::query()->create([
            'institution_id' => $this->institution->id,
            'person_id' => $person->id,
            'programme_id' => $programme->id,
            'campus_id' => $campus->id,
            'department_id' => $department->id,
            'faculty_id' => $department->faculty_id,
            'admission_year_id' => $year->id,
            'student_number' => 'MEMA/TEST/2026/001',
            'current_year_level' => 1,
            'current_semester' => 1,
            'academic_standing' => 'GOOD',
            'status' => 'ACTIVE',
            'matriculated_on' => now()->toDateString(),
        ]);
    }

    private function authorized(): static
    {
        return $this->withHeader('Authorization', 'Bearer '.$this->token);
    }
}

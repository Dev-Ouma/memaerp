<?php

declare(strict_types=1);

namespace Tests\Feature\Iam;

use App\Modules\Course\Models\Course;
use App\Modules\Course\Models\CourseOffering;
use App\Modules\Iam\Services\AccessControl;
use App\Modules\Institution\Models\Campus;
use App\Modules\Institution\Models\Department;
use App\Modules\Institution\Models\Faculty;
use App\Modules\Institution\Models\Term;
use App\Platform\Support\Scope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Scope applied as a QUERY FILTER, not a post-fetch check.
 *
 * This distinction is the reason the class exists. Filtering after the fact still leaks through
 * pagination totals, aggregates and export counts even when the rows themselves are stripped —
 * a Head of Computer Science paging a student list would learn exactly how many students exist
 * in Nursing. So these tests assert on what the QUERY returns, including its count.
 */
final class ScopedQueryTest extends TestCase
{
    use RefreshDatabase;

    private AccessControl $access;

    private Department $computerScience;

    private Department $nursing;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedReferenceData();
        $this->access = app(AccessControl::class);

        $this->computerScience = Department::query()->where('code', 'CS')->firstOrFail();
        $this->nursing = Department::query()->where('code', 'NUR')->firstOrFail();

        $this->makeOffering($this->computerScience, 'CS101');
        $this->makeOffering($this->computerScience, 'CS201');
        $this->makeOffering($this->nursing, 'NUR101');
    }

    #[Test]
    public function an_institution_wide_grant_sees_every_row(): void
    {
        $registrar = $this->userWithRole('registrar-academic', Scope::institution());

        $visible = $this->access
            ->scopeQuery(CourseOffering::query(), $registrar, 'course.offering.view')
            ->count();

        $this->assertSame(3, $visible);
    }

    #[Test]
    public function a_department_grant_sees_only_that_department(): void
    {
        $hod = $this->userWithRole('head-of-department', Scope::department($this->computerScience->id));

        $visible = $this->access
            ->scopeQuery(CourseOffering::query(), $hod, 'course.offering.view')
            ->pluck('department_id')
            ->unique()
            ->values();

        $this->assertCount(1, $visible);
        $this->assertSame($this->computerScience->id, $visible->first());
    }

    #[Test]
    public function a_department_grant_cannot_even_count_rows_outside_it(): void
    {
        $hod = $this->userWithRole('head-of-department', Scope::department($this->computerScience->id));

        // The count is the leak. Two CS offerings exist and one Nursing offering does; if the
        // filter ran after fetching, this would be 3.
        $this->assertSame(
            2,
            $this->access->scopeQuery(CourseOffering::query(), $hod, 'course.offering.view')->count(),
        );
    }

    #[Test]
    public function a_faculty_grant_reaches_its_departments_but_no_further(): void
    {
        $science = Faculty::query()->where('code', 'FSCI')->firstOrFail();
        $dean = $this->userWithRole('dean', Scope::faculty($science->id));

        $rows = $this->access
            ->scopeQuery(CourseOffering::query(), $dean, 'course.offering.view')
            ->get();

        $this->assertCount(2, $rows);
        $this->assertNotContains($this->nursing->id, $rows->pluck('department_id')->all());
    }

    #[Test]
    public function a_user_with_no_grant_sees_nothing_rather_than_everything(): void
    {
        $user = $this->userWithNoRoles();

        // The failure mode this asserts against: an empty scope set leaving the query
        // unfiltered, which would return the entire table to someone with no access at all.
        $this->assertSame(
            0,
            $this->access->scopeQuery(CourseOffering::query(), $user, 'course.offering.view')->count(),
        );
    }

    #[Test]
    public function an_unauthenticated_query_returns_nothing(): void
    {
        $this->assertSame(
            0,
            $this->access->scopeQuery(CourseOffering::query(), null, 'course.offering.view')->count(),
        );
    }

    #[Test]
    public function a_self_scoped_lecturer_sees_only_their_own_offerings(): void
    {
        $lecturer = $this->userWithRole('lecturer', Scope::self());
        $other = $this->userWithRole('lecturer', Scope::self());

        CourseOffering::query()->where('id', CourseOffering::query()->first()->id)
            ->update(['lecturer_id' => $lecturer->id]);
        CourseOffering::query()->whereNull('lecturer_id')
            ->limit(1)->update(['lecturer_id' => $other->id]);

        $rows = $this->access
            ->scopeQuery(CourseOffering::query(), $lecturer, 'course.offering.view')
            ->get();

        $this->assertCount(1, $rows);
        $this->assertSame($lecturer->id, $rows->first()->lecturer_id);
    }

    #[Test]
    public function a_grant_that_maps_to_no_column_on_the_model_matches_nothing(): void
    {
        // Students carry no SELF column by design. A self-scoped lecturer grant must therefore
        // match zero student rows — not every student row.
        $lecturer = $this->userWithRole('lecturer', Scope::self());

        $this->assertSame(
            0,
            $this->access
                ->scopeQuery(\App\Modules\Student\Models\Student::query(), $lecturer, 'student.record.view')
                ->count(),
        );
    }

    #[Test]
    public function filtering_a_model_that_is_not_scope_aware_fails_loudly(): void
    {
        $hod = $this->userWithRole('head-of-department', Scope::department($this->computerScience->id));

        // Silently returning everything would be the dangerous behaviour. Refusing to guess is
        // the correct one.
        $this->expectException(LogicException::class);

        $this->access->scopeQuery(Course::query(), $hod, 'course.catalogue.view')->count();
    }

    private function makeOffering(Department $department, string $courseCode): CourseOffering
    {
        $course = Course::query()->create([
            'institution_id' => $this->institution->id,
            'department_id' => $department->id,
            'code' => $courseCode,
            'title' => $courseCode.' Course',
            'credits' => 3,
            'is_active' => true,
        ]);

        return CourseOffering::query()->create([
            'institution_id' => $this->institution->id,
            'course_id' => $course->id,
            'term_id' => Term::query()->where('is_current', true)->firstOrFail()->id,
            'campus_id' => Campus::query()->where('code', 'MAIN')->firstOrFail()->id,
            'department_id' => $department->id,
            'faculty_id' => $department->faculty_id,
            'section_code' => 'A',
            'max_capacity' => 60,
        ]);
    }
}

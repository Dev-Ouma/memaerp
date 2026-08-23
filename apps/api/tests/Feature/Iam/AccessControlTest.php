<?php

declare(strict_types=1);

namespace Tests\Feature\Iam;

use App\Modules\Iam\Models\Permission;
use App\Modules\Iam\Models\Role;
use App\Modules\Iam\Models\RoleAssignment;
use App\Modules\Iam\Services\AccessControl;
use App\Modules\Iam\Services\ScopeResolver;
use App\Modules\Institution\Models\Department;
use App\Modules\Institution\Models\Faculty;
use App\Platform\Support\Scope;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The authorization layer's contract.
 *
 * Most of these are DENY tests, on purpose. An authorization test suite that only proves the
 * right people get in proves nothing — the failure that matters is the wrong person getting in,
 * and only a negative test can catch it.
 */
final class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    private AccessControl $access;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedReferenceData();
        $this->access = app(AccessControl::class);
    }

    // ---------------------------------------------------------------- permission dimension

    #[Test]
    public function a_user_with_no_roles_is_denied_everything(): void
    {
        $user = $this->userWithNoRoles();

        $this->assertFalse($this->access->allows($user, 'student.record.view'));
        $this->assertFalse($this->access->allows($user, 'examination.marks.enter'));
        $this->assertFalse($this->access->allows($user, 'finance.payment.record'));
    }

    #[Test]
    public function an_unauthenticated_caller_is_denied(): void
    {
        $this->assertFalse($this->access->allows(null, 'student.record.view'));
    }

    #[Test]
    public function a_deactivated_account_is_denied_despite_holding_the_role(): void
    {
        $user = $this->userWithRole('registrar-academic', Scope::institution());
        $this->assertTrue($this->access->allows($user, 'student.record.view'));

        $user->update(['is_active' => false]);

        // Deactivation must take effect on the next check, not at the next login.
        $this->assertFalse($this->access->allows($user->fresh(), 'student.record.view'));
    }

    #[Test]
    public function a_locked_account_is_denied(): void
    {
        $user = $this->userWithRole('registrar-academic', Scope::institution());
        $user->forceFill(['locked_until' => CarbonImmutable::now()->addHour()])->save();

        $this->assertFalse($this->access->allows($user->fresh(), 'student.record.view'));
    }

    #[Test]
    public function holding_a_role_does_not_grant_permissions_outside_it(): void
    {
        $lecturer = $this->userWithRole('lecturer', Scope::self());

        $this->assertTrue($this->access->allows($lecturer, 'examination.marks.enter'));

        // A lecturer enters marks but must never approve them — that is the whole point of the
        // enter/moderate/verify/approve separation.
        $this->assertFalse($this->access->allows($lecturer, 'examination.marks.approve'));
        $this->assertFalse($this->access->allows($lecturer, 'examination.marks.publish'));
        $this->assertFalse($this->access->allows($lecturer, 'finance.payment.record'));
        $this->assertFalse($this->access->allows($lecturer, 'iam.role.assign'));
    }

    #[Test]
    public function the_system_administrator_cannot_approve_marks_or_payroll(): void
    {
        $admin = $this->userWithRole('system-admin', Scope::institution());

        $this->assertTrue($this->access->allows($admin, 'iam.user.create'));
        $this->assertTrue($this->access->allows($admin, 'iam.role.assign'));

        // Segregation of duties: whoever administers the system must not also be able to change
        // a grade or authorise a payment. If this test ever fails, the seed has drifted.
        $this->assertFalse($this->access->allows($admin, 'examination.marks.approve'));
        $this->assertFalse($this->access->allows($admin, 'examination.marks.enter'));
        $this->assertFalse($this->access->allows($admin, 'hr.payroll.approve'));
        $this->assertFalse($this->access->allows($admin, 'finance.payment.reverse'));
    }

    #[Test]
    public function the_auditor_can_read_everything_relevant_and_write_nothing(): void
    {
        $auditor = $this->userWithRole('auditor', Scope::institution());

        $this->assertTrue($this->access->allows($auditor, 'audit.log.view'));
        $this->assertTrue($this->access->allows($auditor, 'finance.payment.view'));
        $this->assertTrue($this->access->allows($auditor, 'examination.marks.view'));

        $this->assertFalse($this->access->allows($auditor, 'finance.payment.record'));
        $this->assertFalse($this->access->allows($auditor, 'examination.marks.enter'));
        $this->assertFalse($this->access->allows($auditor, 'student.record.update'));
        $this->assertFalse($this->access->allows($auditor, 'iam.user.create'));
    }

    // ---------------------------------------------------------------- time dimension

    #[Test]
    public function an_expired_role_assignment_grants_nothing(): void
    {
        $user = $this->userWithRole('registrar-academic', Scope::institution());
        $this->assertTrue($this->access->allows($user, 'student.record.view'));

        RoleAssignment::query()->where('user_id', $user->id)->update([
            'ends_at' => CarbonImmutable::now()->subDay(),
        ]);

        // Acting appointments and exam-season grants must lapse on their own.
        $this->assertFalse($this->access->allows($user->fresh(), 'student.record.view'));
    }

    #[Test]
    public function a_future_role_assignment_grants_nothing_yet(): void
    {
        $user = $this->userWithRole('registrar-academic', Scope::institution());

        RoleAssignment::query()->where('user_id', $user->id)->update([
            'starts_at' => CarbonImmutable::now()->addWeek(),
        ]);

        $this->assertFalse($this->access->allows($user->fresh(), 'student.record.view'));
    }

    #[Test]
    public function revoking_a_role_takes_effect_immediately(): void
    {
        $user = $this->userWithRole('registrar-academic', Scope::institution());
        $this->assertTrue($this->access->allows($user, 'student.record.view'));

        // Deleting through the model fires the observer that flushes the resolver cache. If this
        // fails, revocation is silently delayed by the cache TTL.
        RoleAssignment::query()->where('user_id', $user->id)->get()->each->delete();

        $this->assertFalse($this->access->allows($user->fresh(), 'student.record.view'));
    }

    // ---------------------------------------------------------------- scope dimension

    #[Test]
    public function a_faculty_grant_expands_to_its_departments(): void
    {
        $science = Faculty::query()->where('code', 'FSCI')->firstOrFail();
        $dean = $this->userWithRole('dean', Scope::faculty($science->id));

        $resolved = app(ScopeResolver::class)
            ->resolve($dean, 'examination.marks.approve');

        $scienceDepartments = Department::query()
            ->where('faculty_id', $science->id)
            ->pluck('id')
            ->all();

        $this->assertNotEmpty($scienceDepartments);

        foreach ($scienceDepartments as $departmentId) {
            $this->assertContains(
                $departmentId,
                $resolved->departmentIds,
                'A Dean must reach every department inside their faculty.',
            );
        }
    }

    #[Test]
    public function a_faculty_grant_does_not_reach_another_faculty(): void
    {
        $science = Faculty::query()->where('code', 'FSCI')->firstOrFail();
        $health = Faculty::query()->where('code', 'FHEA')->firstOrFail();

        $dean = $this->userWithRole('dean', Scope::faculty($science->id));

        $resolved = app(ScopeResolver::class)
            ->resolve($dean, 'examination.marks.approve');

        $healthDepartments = Department::query()
            ->where('faculty_id', $health->id)
            ->pluck('id')
            ->all();

        $this->assertNotEmpty($healthDepartments);

        foreach ($healthDepartments as $departmentId) {
            $this->assertNotContains(
                $departmentId,
                $resolved->departmentIds,
                'The Dean of Science must not reach Health Sciences departments.',
            );
        }
    }

    #[Test]
    public function a_department_grant_does_not_expand_upward(): void
    {
        $cs = Department::query()->where('code', 'CS')->firstOrFail();
        $hod = $this->userWithRole('head-of-department', Scope::department($cs->id));

        $resolved = app(ScopeResolver::class)
            ->resolve($hod, 'examination.marks.verify');

        $this->assertSame([$cs->id], $resolved->departmentIds);
        $this->assertFalse($resolved->institutionWide);
        $this->assertEmpty($resolved->facultyIds);
    }

    #[Test]
    public function a_scoped_grant_is_never_institution_wide(): void
    {
        $cs = Department::query()->where('code', 'CS')->firstOrFail();
        $hod = $this->userWithRole('head-of-department', Scope::department($cs->id));

        $resolved = app(ScopeResolver::class)
            ->resolve($hod, 'student.record.view');

        $this->assertFalse(
            $resolved->institutionWide,
            'A department-scoped grant that resolves as institution-wide would expose the '
            .'whole student body to a Head of Department.',
        );
    }

    #[Test]
    public function a_permission_held_nowhere_resolves_to_an_empty_scope_set(): void
    {
        $user = $this->userWithNoRoles();

        $resolved = app(ScopeResolver::class)
            ->resolve($user, 'finance.payment.reverse');

        // Empty must mean "denied everywhere". Callers that treat it as "unrestricted" is the
        // exact inversion this assertion exists to pin down.
        $this->assertTrue($resolved->isEmpty());
        $this->assertFalse($resolved->institutionWide);
    }

    // ---------------------------------------------------------------- gate bridge

    #[Test]
    public function the_gate_routes_permission_checks_through_access_control(): void
    {
        $lecturer = $this->userWithRole('lecturer', Scope::self());

        $this->assertTrue($lecturer->can('examination.marks.enter'));
        $this->assertFalse($lecturer->can('examination.marks.approve'));
    }

    #[Test]
    public function the_gate_denies_abilities_that_are_not_permissions(): void
    {
        $admin = $this->userWithRole('system-admin', Scope::institution());

        // Not `module.resource.action` shaped, so the bridge must not answer it. An undefined
        // ability has to deny rather than fall through to a permissive default.
        $this->assertFalse($admin->can('do-whatever-i-want'));
    }

    // ---------------------------------------------------------------- catalogue integrity

    #[Test]
    public function every_role_in_the_catalogue_seeds_with_its_permissions(): void
    {
        $roles = Role::query()->with('permissions')->get();

        $this->assertCount(15, $roles);

        foreach ($roles as $role) {
            $this->assertNotEmpty(
                $role->permissions,
                "System role [{$role->code}] seeded with no permissions.",
            );
        }
    }

    #[Test]
    public function no_seeded_role_holds_every_permission(): void
    {
        $total = Permission::query()->count();

        foreach (Role::query()->withCount('permissions')->get() as $role) {
            $this->assertLessThan(
                $total,
                $role->permissions_count,
                "Role [{$role->code}] holds every permission in the system. No single role "
                .'should — that is what segregation of duties means.',
            );
        }
    }
}

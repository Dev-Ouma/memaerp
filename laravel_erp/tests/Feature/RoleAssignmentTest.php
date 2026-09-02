<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Platform\Role;
use App\Models\Platform\UserRole;
use App\Models\User;
use App\Modules\Platform\Rbac\PermissionCatalogue;
use Database\Seeders\RbacCatalogueSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RoleAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
    }

    public function test_catalogue_seeding_is_idempotent_and_preserves_segregation_of_duties(): void
    {
        $permissionCount = count(PermissionCatalogue::permissions());
        $roleCount = count(PermissionCatalogue::roles());

        $this->seed(RbacCatalogueSeeder::class);

        $this->assertDatabaseCount('permissions', $permissionCount);
        $this->assertDatabaseCount('roles', $roleCount);
        $systemAdmin = Role::query()->where('code', 'system_administrator')->firstOrFail();
        $this->assertFalse($systemAdmin->permissions()->where('code', 'platform.retention.execute')->exists());
    }

    public function test_system_administrator_can_grant_scoped_role_but_cannot_execute_retention(): void
    {
        $administrator = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create(['role' => 'staff']);
        $this->grantRole($administrator, 'system_administrator');
        $dpoRole = Role::query()->where('code', 'data_protection_officer')->firstOrFail();

        $this->actingAs($administrator)->get(route('admin.setups.access.index'))->assertOk()->assertSee('Role assignments');
        $this->post(route('admin.setups.access.assignments.store'), [
            'user_id' => $target->id,
            'role_id' => $dpoRole->id,
            'scope_type' => 'institution',
            'grant_reason' => 'Appointed by the institutional data governance committee.',
        ])->assertRedirect();

        $this->assertDatabaseHas('user_roles', ['user_id' => $target->id, 'role_id' => $dpoRole->id, 'granted_by' => $administrator->id]);
        $this->assertDatabaseHas('audit_events', ['action' => 'role.assignment.granted', 'subject_id' => (string) $target->id]);
        $this->post(route('admin.setups.governance.retention.store'), [])->assertForbidden();
    }

    public function test_dpo_can_access_governance_but_cannot_manage_role_assignments(): void
    {
        $dpo = User::factory()->create(['role' => 'staff']);
        $this->grantRole($dpo, 'data_protection_officer');

        $this->actingAs($dpo)->get(route('admin.setups.governance.index'))->assertOk();
        $this->get(route('admin.setups.access.index'))->assertForbidden();
    }

    public function test_system_administrator_cannot_accumulate_segregated_retention_authority(): void
    {
        $administrator = User::factory()->create(['role' => 'admin']);
        $this->grantRole($administrator, 'system_administrator');
        $dpoRole = Role::query()->where('code', 'data_protection_officer')->firstOrFail();

        $this->actingAs($administrator)->post(route('admin.setups.access.assignments.store'), [
            'user_id' => $administrator->id,
            'role_id' => $dpoRole->id,
            'scope_type' => 'institution',
            'grant_reason' => 'Attempting to combine conflicting institutional duties.',
        ])->assertStatus(422);

        $this->assertDatabaseMissing('user_roles', ['user_id' => $administrator->id, 'role_id' => $dpoRole->id]);
    }

    public function test_role_revocation_requires_reason_and_is_audited(): void
    {
        $administrator = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create(['role' => 'staff']);
        $this->grantRole($administrator, 'system_administrator');
        $assignmentId = $this->grantRole($target, 'auditor', $administrator);
        $assignment = UserRole::query()->findOrFail($assignmentId);

        $this->actingAs($administrator)->delete(route('admin.setups.access.assignments.destroy', $assignment))
            ->assertSessionHasErrors('revocation_reason');
        $this->delete(route('admin.setups.access.assignments.destroy', $assignment), [
            'revocation_reason' => 'Audit engagement ended and access is no longer required.',
        ])->assertRedirect();

        $this->assertDatabaseMissing('user_roles', ['id' => $assignmentId]);
        $this->assertDatabaseHas('audit_events', ['action' => 'role.assignment.revoked', 'subject_id' => (string) $target->id]);
    }

    public function test_administrator_cannot_revoke_their_own_system_admin_role(): void
    {
        $administrator = User::factory()->create(['role' => 'admin']);
        $assignment = UserRole::query()->findOrFail($this->grantRole($administrator, 'system_administrator'));

        $this->actingAs($administrator)->delete(route('admin.setups.access.assignments.destroy', $assignment), [
            'revocation_reason' => 'Attempting an unsafe self-lockout operation.',
        ])->assertStatus(422);

        $this->assertDatabaseHas('user_roles', ['id' => $assignment->id]);
    }
}

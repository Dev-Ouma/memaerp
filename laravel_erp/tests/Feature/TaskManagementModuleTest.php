<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\InstitutionalTask;
use App\Models\Platform\Role;
use App\Models\Platform\UserRole;
use App\Models\TaskManagementRole;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

final class TaskManagementModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_manages_college_users_from_the_task_management_landing_page(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)->get(route('task-management.index'))
            ->assertOk()
            ->assertSee('College Users')
            ->assertSee('Role administration');

        $this->post(route('task-management.users.store'), [
            'first_name' => 'Faith',
            'last_name' => 'Chebet',
            'email' => 'faith.chebet@mema.test',
            'phone_number' => '+254712345678',
            'title' => 'Admissions Officer',
            'department' => 'Admissions',
            'account_type' => 'staff',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $staff = User::query()->where('email', 'faith.chebet@mema.test')->firstOrFail();
        $this->assertTrue($staff->is_active);
        $this->assertSame('staff', $staff->role);
        $this->assertNotNull($staff->password);
        Notification::assertSentTo($staff, ResetPassword::class);
        $this->assertDatabaseHas('audit_logs', ['action' => 'platform.user_created', 'subject_id' => (string) $staff->id]);

        $this->patch(route('task-management.users.update', $staff), [
            'name' => 'Faith W. Chebet',
            'phone_number' => '+254712345678',
            'title' => 'Senior Admissions Officer',
            'department' => 'Admissions and Recruitment',
            'is_active' => '0',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertFalse($staff->refresh()->is_active);
        $this->assertSame('Admissions and Recruitment', $staff->department);
        $this->assertDatabaseHas('audit_logs', ['action' => 'platform.user_updated', 'subject_id' => (string) $staff->id]);
    }

    public function test_user_management_rejects_unauthorised_access_and_admin_self_deactivation(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);

        $this->actingAs($staff)->get(route('task-management.users'))->assertForbidden();
        $this->actingAs($staff)->post(route('task-management.users.store'), [])->assertForbidden();
        $this->actingAs($staff)->get(route('task-management.index'))
            ->assertOk()
            ->assertSee('Institutional Task', false)
            ->assertSee('Workflow Manager');

        $this->actingAs($admin)->patch(route('task-management.users.update', $admin), [
            'name' => $admin->name,
            'phone_number' => null,
            'title' => null,
            'department' => null,
            'is_active' => '0',
        ])->assertStatus(422);
        $this->assertTrue($admin->refresh()->is_active);
    }

    public function test_admin_configures_roles_and_task_bindings(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin)->post(route('task-management.roles.store'), [
            'role_code' => 'ROL-REG', 'name' => 'Registrar', 'department' => 'Registry', 'privilege_level' => 'Academic approvals',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $role = TaskManagementRole::firstOrFail();
        $this->post(route('task-management.task-roles.store'), [
            'task_management_role_id' => $role->id, 'mapping_ref' => 'MAP-REG-AUDIT',
            'task_template' => 'Registration audit', 'trigger_event' => 'Registration closes', 'sla_hours' => 48,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('task_role_bindings', ['mapping_ref' => 'MAP-REG-AUDIT', 'sla_hours' => 48]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'task.role_binding_created']);
    }

    public function test_admin_creates_task_and_assignee_controls_its_workflow(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $assignee = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $this->actingAs($admin)->post(route('task-management.tasks.store'), [
            'title' => 'Audit progression records', 'description' => 'Validate the complete progression register.',
            'assignee_user_id' => $assignee->id, 'priority' => 'HIGH', 'due_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
        ])->assertRedirect()->assertSessionHasNoErrors();

        $task = InstitutionalTask::firstOrFail();
        $this->assertSame(sprintf('TSK-%d-%06d', now()->year, $task->id), $task->task_ref);
        $this->actingAs($assignee)->get(route('task-management.task-manager'))
            ->assertOk()
            ->assertSee($task->task_ref)
            ->assertSee('Audit progression records');
        $this->actingAs($assignee)->post(route('task-management.tasks.transition', $task), [
            'status' => 'IN_PROGRESS', 'note' => 'Started validation.', 'lock_version' => $task->lock_version,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $task->refresh();
        $this->post(route('task-management.tasks.transition', $task), [
            'status' => 'COMPLETED', 'note' => 'All records reconciled.', 'lock_version' => $task->lock_version,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('COMPLETED', $task->refresh()->status);
        $this->assertNotNull($task->completed_at);
        $this->assertDatabaseCount('institutional_task_events', 2);
    }

    public function test_unrelated_user_cannot_view_or_change_another_users_tasks(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $assignee = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $outsider = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $task = InstitutionalTask::create([
            'task_ref' => 'TSK-2026-000001', 'title' => 'Restricted task', 'assignee_user_id' => $assignee->id,
            'created_by' => $admin->id, 'priority' => 'MEDIUM', 'due_at' => now()->addDay(),
        ])->refresh();

        $this->actingAs($outsider)->get(route('task-management.task-manager'))->assertOk()->assertDontSee('Restricted task');
        $this->post(route('task-management.tasks.transition', $task), ['status' => 'IN_PROGRESS', 'lock_version' => $task->lock_version])->assertForbidden();
    }

    public function test_user_management_aliases_and_role_assignment_workflows(): void
    {
        $this->seedRbac();
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $targetUser = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $role = Role::query()->where('code', 'admissions_officer')->firstOrFail();

        $this->actingAs($admin)->get('/user-management')->assertRedirect(route('task-management.index'));
        $this->actingAs($admin)->get('/users')->assertRedirect(route('task-management.index'));

        $this->post(route('task-management.users.assign-role'), [
            'user_id' => $targetUser->id,
            'role_id' => $role->id,
            'scope_type' => 'institution',
            'grant_reason' => 'Appointed as lead admissions processing officer.',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $assignment = UserRole::query()
            ->where('user_id', $targetUser->id)
            ->where('role_id', $role->id)
            ->firstOrFail();
        $this->assertSame('institution', $assignment->scope_type);

        $this->post(route('task-management.users.toggle-status', $targetUser))
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertFalse($targetUser->refresh()->is_active);

        $this->post(route('task-management.users.toggle-status', $targetUser))
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertTrue($targetUser->refresh()->is_active);

        $this->delete(route('task-management.users.revoke-role', $assignment))
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('user_roles', ['id' => $assignment->id]);
    }

    public function test_college_users_with_roles_are_routed_to_task_management_after_login(): void
    {
        $this->seedRbac();
        $admin = User::factory()->create([
            'email' => 'dean@mema.test',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);
        $staff = User::factory()->create([
            'email' => 'officer@mema.test',
            'password' => 'password',
            'role' => 'staff',
            'is_active' => true,
        ]);
        $this->grantRole($staff, 'admissions_officer');

        $this->post(route('login.store'), [
            'email' => 'dean@mema.test',
            'password' => 'password',
        ])->assertRedirect(route('task-management.index'));
        $this->get(route('task-management.index'))
            ->assertOk()
            ->assertSee('College Users')
            ->assertSee('Role administration');
        $this->post(route('logout'));

        $this->post(route('login.store'), [
            'email' => 'officer@mema.test',
            'password' => 'password',
        ])->assertRedirect(route('task-management.index'));
        $this->get(route('task-management.index'))
            ->assertOk()
            ->assertSee('Institutional Task');

        $role = Role::query()->where('code', 'admissions_officer')->firstOrFail();
        $this->actingAs($admin)->post(route('task-management.users.store'), [
            'first_name' => 'Mercy',
            'last_name' => 'Achieng',
            'email' => 'mercy.achieng@mema.test',
            'account_type' => 'staff',
            'department' => 'Admissions',
            'role_id' => $role->id,
            'scope_type' => 'institution',
            'grant_reason' => 'Appointed to the admissions processing desk.',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $created = User::query()->where('email', 'mercy.achieng@mema.test')->firstOrFail();
        $this->assertDatabaseHas('user_roles', ['user_id' => $created->id, 'role_id' => $role->id]);
    }
}

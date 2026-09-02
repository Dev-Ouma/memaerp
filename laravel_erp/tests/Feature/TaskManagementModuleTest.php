<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\InstitutionalTask;
use App\Models\TaskManagementRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TaskManagementModuleTest extends TestCase
{
    use RefreshDatabase;

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
}

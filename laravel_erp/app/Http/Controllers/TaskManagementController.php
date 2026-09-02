<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\InstitutionalTask;
use App\Models\InstitutionalTaskEvent;
use App\Models\TaskManagementRole;
use App\Models\TaskRoleBinding;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class TaskManagementController extends Controller
{
    public function roles(Request $request): View
    {
        abort_unless($request->user()->isAdmin(), 403);
        $roles = TaskManagementRole::query()->withCount('bindings')->orderBy('name')->get();
        $stats = ['totalRoles' => $roles->count(), 'assignedUsers' => User::query()->where('is_active', true)->count(), 'permissionPolicies' => $roles->sum('bindings_count'), 'auditStatus' => 'RBAC Verified'];

        return view('task-management.roles', compact('stats', 'roles'));
    }

    public function storeRole(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        $data = $request->validate([
            'role_code' => ['required', 'string', 'max:40', 'regex:/^[A-Z0-9_-]+$/', 'unique:task_management_roles,role_code'],
            'name' => ['required', 'string', 'max:190'], 'department' => ['required', 'string', 'max:190'],
            'privilege_level' => ['required', 'string', 'max:190'],
        ]);
        $role = TaskManagementRole::create($data + ['is_active' => true]);
        AuditLog::record('task.role_created', $role, null, $role->toArray());

        return back()->with('success', 'Task role created.');
    }

    public function taskRoles(Request $request): View
    {
        abort_unless($request->user()->isAdmin(), 403);
        $taskRoles = TaskRoleBinding::query()->with('role')->latest()->get();
        $roles = TaskManagementRole::query()->where('is_active', true)->orderBy('name')->get();
        $stats = ['roleMappedTasks' => $taskRoles->count(), 'defaultTaskTemplates' => $taskRoles->pluck('task_template')->unique()->count(), 'activeRoleEscalations' => $taskRoles->where('is_active', true)->count(), 'slaSchedules' => $taskRoles->pluck('sla_hours')->unique()->count()];

        return view('task-management.task-roles', compact('stats', 'taskRoles', 'roles'));
    }

    public function storeTaskRole(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        $data = $request->validate([
            'task_management_role_id' => ['required', 'exists:task_management_roles,id'],
            'mapping_ref' => ['required', 'string', 'max:60', 'regex:/^[A-Z0-9_-]+$/', 'unique:task_role_bindings,mapping_ref'],
            'task_template' => ['required', 'string', 'max:190'], 'trigger_event' => ['required', 'string', 'max:190'],
            'sla_hours' => ['required', 'integer', 'between:1,8760'],
        ]);
        $binding = TaskRoleBinding::create($data + ['is_active' => true]);
        AuditLog::record('task.role_binding_created', $binding, null, $binding->toArray());

        return back()->with('success', 'Task template linked to role.');
    }

    public function taskManager(Request $request): View
    {
        $tasksQuery = InstitutionalTask::query()->with('assignee')->latest();
        if (! $request->user()->isAdmin()) {
            $tasksQuery->where('assignee_user_id', $request->user()->id);
        }
        $tasks = $tasksQuery->paginate(25);
        $scope = InstitutionalTask::query();
        if (! $request->user()->isAdmin()) {
            $scope->where('assignee_user_id', $request->user()->id);
        }
        $stats = [
            'totalTasksLogged' => (clone $scope)->count(),
            'completedTasks' => (clone $scope)->where('status', 'COMPLETED')->count(),
            'activeInProgress' => (clone $scope)->whereIn('status', ['OPEN', 'IN_PROGRESS', 'BLOCKED'])->count(),
            'overdueTasks' => (clone $scope)->whereNotIn('status', ['COMPLETED', 'CANCELLED'])->where('due_at', '<', now())->count(),
        ];
        $users = User::query()->where('is_active', true)->orderBy('name')->get();

        return view('task-management.task-manager', compact('stats', 'tasks', 'users'));
    }

    public function storeTask(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        $data = $request->validate([
            'title' => ['required', 'string', 'min:5', 'max:255'], 'description' => ['nullable', 'string', 'max:5000'],
            'assignee_user_id' => ['required', Rule::exists('users', 'id')->where('is_active', true)],
            'priority' => ['required', Rule::in(['LOW', 'MEDIUM', 'HIGH', 'CRITICAL'])],
            'due_at' => ['required', 'date', 'after:now'],
        ]);
        $task = DB::transaction(function () use ($data, $request): InstitutionalTask {
            $task = InstitutionalTask::create([...$data, 'task_ref' => 'PENDING-'.Str::uuid(), 'created_by' => $request->user()->id]);
            $task->update(['task_ref' => sprintf('TSK-%d-%06d', now()->year, $task->id)]);

            return $task;
        });
        AuditLog::record('task.created', $task, null, $task->toArray());

        return back()->with('success', "Task {$task->task_ref} created.");
    }

    public function transitionTask(Request $request, InstitutionalTask $task): RedirectResponse
    {
        abort_unless($request->user()->isAdmin() || $task->assignee_user_id === $request->user()->id, 403);
        $allowed = match ($task->status) {
            'OPEN' => ['IN_PROGRESS', 'CANCELLED'],
            'IN_PROGRESS' => ['BLOCKED', 'COMPLETED', 'CANCELLED'],
            'BLOCKED' => ['IN_PROGRESS', 'COMPLETED', 'CANCELLED'],
            default => [],
        };
        $data = $request->validate([
            'status' => ['required', Rule::in($allowed)], 'note' => ['nullable', 'string', 'max:2000'],
            'lock_version' => ['required', 'integer'],
        ]);
        abort_if((int) $data['lock_version'] !== $task->lock_version, 409, 'This task changed in another session. Refresh and try again.');
        DB::transaction(function () use ($task, $data, $request): void {
            $locked = InstitutionalTask::query()->lockForUpdate()->findOrFail($task->id);
            abort_if($locked->lock_version !== (int) $data['lock_version'], 409);
            $from = $locked->status;
            $locked->update(['status' => $data['status'], 'lock_version' => $locked->lock_version + 1, 'completed_at' => $data['status'] === 'COMPLETED' ? now() : null]);
            InstitutionalTaskEvent::create(['institutional_task_id' => $locked->id, 'actor_user_id' => $request->user()->id, 'from_status' => $from, 'to_status' => $data['status'], 'note' => $data['note'] ?? null, 'occurred_at' => now()]);
            AuditLog::record('task.transitioned', $locked, ['status' => $from], $locked->toArray());
        });

        return back()->with('success', 'Task status updated.');
    }
}

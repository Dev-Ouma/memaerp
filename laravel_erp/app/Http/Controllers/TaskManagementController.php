<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\InstitutionalTask;
use App\Models\InstitutionalTaskEvent;
use App\Models\Platform\Role;
use App\Models\Platform\UserRole;
use App\Models\TaskManagementRole;
use App\Models\TaskRoleBinding;
use App\Models\User;
use App\Modules\Platform\Rbac\PermissionCatalogue;
use App\Modules\Platform\Rbac\RoleAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class TaskManagementController extends Controller
{
    public function landing(Request $request): View
    {
        if ($request->user()->isAdmin()) {
            return $this->collegeUsers($request);
        }

        abort_unless($request->user()->isCollegeUser(), 403);

        return $this->taskManager($request);
    }

    public function collegeUsers(Request $request): View
    {
        abort_unless($request->user()->isAdmin(), 403);
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'account_type' => ['nullable', Rule::in(['admin', 'staff', 'student', 'applicant', 'parent'])],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);
        $users = User::query()
            ->with(['rbacAssignments' => fn ($query) => $query->with('role')->where(fn ($active) => $active->whereNull('expires_at')->orWhere('expires_at', '>', now()))])
            ->when($filters['q'] ?? null, fn ($query, $value) => $query->where(fn ($search) => $search
                ->where('name', 'ilike', "%{$value}%")
                ->orWhere('email', 'ilike', "%{$value}%")
                ->orWhere('department', 'ilike', "%{$value}%")
                ->orWhere('phone_number', 'ilike', "%{$value}%")))
            ->when($filters['account_type'] ?? null, fn ($query, $value) => $query->where('role', $value))
            ->when(($filters['status'] ?? null) === 'active', fn ($query) => $query->where('is_active', true))
            ->when(($filters['status'] ?? null) === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();
        $roles = Role::query()->orderBy('name')->get();
        $scopeTypes = PermissionCatalogue::SCOPE_TYPES;
        $stats = [
            'total' => User::query()->count(),
            'active' => User::query()->where('is_active', true)->count(),
            'staff' => User::query()->whereIn('role', ['admin', 'staff'])->count(),
            'roleAssignments' => DB::table('user_roles')
                ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->count(),
        ];

        $assignableUsers = User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'email', 'role']);

        return view('task-management.users', compact('users', 'roles', 'stats', 'filters', 'scopeTypes', 'assignableUsers'));
    }

    public function storeUser(Request $request, RoleAssignmentService $assignments): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        $data = $request->validate([
            'first_name' => ['required', 'string', 'min:2', 'max:60'],
            'last_name' => ['required', 'string', 'min:2', 'max:60'],
            'email' => ['required', 'email:filter', 'max:255', 'unique:users,email'],
            'phone_number' => ['nullable', 'string', 'max:40'],
            'title' => ['nullable', 'string', 'max:30'],
            'department' => ['nullable', 'string', 'max:100'],
            'account_type' => ['required', Rule::in(['admin', 'staff'])],
            'role_id' => ['nullable', 'uuid', 'exists:roles,id'],
            'scope_type' => ['exclude_without:role_id', 'required_with:role_id', Rule::in(PermissionCatalogue::SCOPE_TYPES)],
            'scope_id' => ['exclude_without:role_id', 'nullable', 'string', 'max:64', 'required_unless:scope_type,institution'],
            'grant_reason' => ['exclude_without:role_id', 'required_with:role_id', 'string', 'min:10', 'max:255'],
        ]);

        $user = DB::transaction(function () use ($data, $request, $assignments): User {
            $user = User::create([
                'name' => trim($data['first_name'].' '.$data['last_name']),
                'first_name' => trim($data['first_name']),
                'last_name' => trim($data['last_name']),
                'email' => mb_strtolower(trim($data['email'])),
                'phone_number' => $data['phone_number'] ?? null,
                'title' => $data['title'] ?? null,
                'department' => $data['department'] ?? null,
                'role' => $data['account_type'],
                'password' => Str::password(32),
                'is_active' => true,
                'password_changed_at' => null,
            ]);
            AuditLog::record('platform.user_created', $user, null, [
                'name' => $user->name, 'email' => $user->email, 'account_type' => $user->role,
                'created_by' => $request->user()->id,
            ]);
            if (! empty($data['role_id'])) {
                $assignments->grant($request->user(), $user, [
                    'role_id' => $data['role_id'],
                    'scope_type' => $data['scope_type'] ?? 'institution',
                    'scope_id' => $data['scope_id'] ?? null,
                    'grant_reason' => $data['grant_reason'],
                ]);
            }

            return $user;
        });

        Password::sendResetLink(['email' => $user->email]);

        return back()->with('success', "College user {$user->name} created. A password-creation link has been queued.");
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless($user->isCollegeAccount(), 404);
        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:190'],
            'phone_number' => ['nullable', 'string', 'max:40'],
            'title' => ['nullable', 'string', 'max:30'],
            'department' => ['nullable', 'string', 'max:100'],
            'is_active' => ['required', 'boolean'],
        ]);
        abort_if($user->is($request->user()) && ! (bool) $data['is_active'], 422, 'You cannot deactivate your own account.');
        $before = $user->only(['name', 'phone_number', 'title', 'department', 'is_active']);
        $user->update($data);
        AuditLog::record('platform.user_updated', $user, $before, $user->fresh()->only(['name', 'phone_number', 'title', 'department', 'is_active']));

        return back()->with('success', "College user {$user->name} updated.");
    }

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless($user->isCollegeAccount(), 404);
        abort_if($user->is($request->user()), 422, 'You cannot deactivate your own account.');
        $newStatus = ! $user->is_active;
        $before = $user->only(['is_active']);
        $user->update(['is_active' => $newStatus]);
        AuditLog::record('platform.user_status_toggled', $user, $before, ['is_active' => $newStatus]);

        return back()->with('success', "User {$user->name} has been ".($newStatus ? 'activated' : 'deactivated').'.');
    }

    public function assignRole(Request $request, RoleAssignmentService $assignments): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role_id' => ['required', 'uuid', 'exists:roles,id'],
            'scope_type' => ['required', Rule::in(PermissionCatalogue::SCOPE_TYPES)],
            'scope_id' => ['nullable', 'string', 'max:64', 'required_unless:scope_type,institution'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'grant_reason' => ['required', 'string', 'min:10', 'max:255'],
        ]);
        $target = User::query()->findOrFail($data['user_id']);
        abort_unless($target->isCollegeAccount(), 404);
        $assignment = $assignments->grant($request->user(), $target, $data);

        return back()->with('success', "Role assignment {$assignment->id} granted to {$target->name}.");
    }

    public function revokeRoleAssignment(Request $request, UserRole $assignment, RoleAssignmentService $assignments): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        $data = $request->validate([
            'revocation_reason' => ['nullable', 'string', 'min:10', 'max:500'],
        ]);
        $assignments->revoke(
            $request->user(),
            $assignment,
            $data['revocation_reason'] ?? 'Revoked from the college users console.',
        );

        return back()->with('success', 'Role assignment successfully revoked.');
    }

    public function roles(Request $request): View
    {
        abort_unless($request->user()->isAdmin(), 403);
        $roles = TaskManagementRole::query()->withCount('bindings')->orderBy('name')->get();
        $stats = ['totalRoles' => $roles->count(), 'assignedUsers' => User::query()->where('is_active', true)->whereIn('role', ['admin', 'staff'])->count(), 'permissionPolicies' => $roles->sum('bindings_count'), 'auditStatus' => 'RBAC Verified'];

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
        $users = User::query()->where('is_active', true)->whereIn('role', ['admin', 'staff'])->orderBy('name')->get();

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

<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Platform\Role;
use App\Models\Platform\UserRole;
use App\Models\User;
use App\Modules\Platform\Rbac\PermissionCatalogue;
use App\Modules\Platform\Rbac\RoleAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class RoleAssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:120']]);
        $users = User::query()->with(['stakeholderTypes'])
            ->when($filters['q'] ?? null, fn ($query, $value) => $query->where(fn ($search) => $search->where('name', 'ilike', "%{$value}%")->orWhere('email', 'ilike', "%{$value}%")))
            ->withCount('rbacAssignments')->orderBy('name')->paginate(20)->withQueryString();
        $roles = Role::query()->with('permissions')->orderBy('name')->get();
        $assignments = UserRole::query()->with(['user', 'role'])->latest('granted_at')->paginate(25, ['*'], 'assignments_page');

        return view('admin.access.index', compact('users', 'roles', 'assignments'));
    }

    public function store(Request $request, RoleAssignmentService $assignments): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role_id' => ['required', 'uuid', 'exists:roles,id'],
            'scope_type' => ['required', Rule::in(PermissionCatalogue::SCOPE_TYPES)],
            'scope_id' => ['nullable', 'string', 'max:64', 'required_unless:scope_type,institution'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'grant_reason' => ['required', 'string', 'min:10', 'max:255'],
        ]);
        $target = User::query()->findOrFail($data['user_id']);
        $assignment = $assignments->grant($request->user(), $target, $data);

        return back()->with('success', "Role assignment {$assignment->id} granted.");
    }

    public function destroy(Request $request, UserRole $assignment, RoleAssignmentService $assignments): RedirectResponse
    {
        $data = $request->validate(['revocation_reason' => ['required', 'string', 'min:10', 'max:500']]);
        $assignments->revoke($request->user(), $assignment, $data['revocation_reason']);

        return back()->with('success', 'Role assignment revoked and audited.');
    }
}

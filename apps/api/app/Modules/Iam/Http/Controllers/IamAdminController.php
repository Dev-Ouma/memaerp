<?php

declare(strict_types=1);

namespace App\Modules\Iam\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Iam\Models\Role;
use App\Modules\Iam\Models\RoleAssignment;
use App\Modules\Iam\Models\User;
use App\Modules\Iam\Services\PasswordPolicy;
use App\Modules\Iam\Services\SessionManager;
use App\Modules\Student\Models\Person;
use App\Modules\Student\Models\PersonIdentity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

final class IamAdminController extends Controller
{
    public function __construct(
        private readonly PasswordPolicy $passwordPolicy,
        private readonly SessionManager $sessions,
    ) {}

    public function users(Request $request): JsonResponse
    {
        Gate::authorize('iam.user.view');
        $actor = $this->actor($request);
        $users = User::query()->where('institution_id', $actor->institution_id)
            ->with(['person', 'roleAssignments.role'])->orderBy('email')->paginate(25);

        $data = [];
        foreach ($users->items() as $user) {
            $assignments = [];
            foreach ($user->roleAssignments as $assignment) {
                $assignments[] = [
                    'id' => $assignment->id, 'code' => $assignment->role?->code,
                    'name' => $assignment->role?->name, 'scope_type' => $assignment->scope_type,
                    'scope_id' => $assignment->scope_id,
                ];
            }
            $data[] = [
                'id' => $user->id, 'email' => $user->email, 'username' => $user->username,
                'name' => $user->person?->full_name, 'status' => $user->status,
                'is_active' => $user->is_active, 'mfa_enabled' => $user->mfa_enabled,
                'last_login_at' => $user->last_login_at, 'roles' => $assignments,
            ];
        }

        return response()->json(['data' => $data, 'meta' => ['current_page' => $users->currentPage(), 'total' => $users->total()]]);
    }

    public function storeUser(Request $request): JsonResponse
    {
        Gate::authorize('iam.user.create');
        $actor = $this->actor($request);
        $validated = $request->validate([
            'given_name' => ['required', 'string', 'max:100'], 'family_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:255', 'unique:iam.users,email'],
            'username' => ['required', 'alpha_dash', 'max:64', 'unique:iam.users,username'],
            'identity_type' => ['required', 'in:APPLICANT,STUDENT,EMPLOYEE,ALUMNI'],
            'identifier' => ['required', 'string', 'max:100'], 'password' => ['required', 'string'],
        ]);
        $this->passwordPolicy->validate($validated['password']);

        $user = DB::transaction(function () use ($actor, $validated): User {
            $person = Person::query()->create([
                'institution_id' => $actor->institution_id, 'given_name' => $validated['given_name'],
                'family_name' => $validated['family_name'], 'primary_email' => mb_strtolower($validated['email']),
            ]);
            PersonIdentity::query()->create([
                'institution_id' => $actor->institution_id, 'person_id' => $person->id,
                'identity_type' => $validated['identity_type'], 'identifier' => $validated['identifier'],
                'status' => PersonIdentity::STATUS_ACTIVE, 'started_on' => now()->toDateString(),
            ]);

            return User::query()->create([
                'institution_id' => $actor->institution_id, 'person_id' => $person->id,
                'email' => mb_strtolower($validated['email']), 'username' => mb_strtolower($validated['username']),
                'password' => Hash::make($validated['password']), 'status' => 'PENDING',
                'is_active' => true, 'must_change_password' => true,
            ]);
        });

        return response()->json(['message' => 'User provisioned.', 'data' => ['id' => $user->id]], 201);
    }

    public function updateStatus(Request $request, User $user): JsonResponse
    {
        Gate::authorize('iam.user.suspend');
        $actor = $this->actor($request);
        abort_unless($user->institution_id === $actor->institution_id, 404);
        $validated = $request->validate(['status' => ['required', 'in:PENDING,ACTIVE,LOCKED,SUSPENDED,DEACTIVATED'], 'reason' => ['required', 'string', 'min:8', 'max:500']]);
        $active = $validated['status'] === 'ACTIVE';
        $user->auditReason($validated['reason'])->forceFill(['status' => $validated['status'], 'is_active' => $active])->save();
        if (! $active) {
            $user->tokens()->delete();
            DB::table('iam.user_sessions')->where('user_id', $user->id)->whereNull('revoked_at')->update([
                'revoked_at' => now(), 'revoked_reason' => $validated['status'], 'updated_at' => now(),
            ]);
        }

        return response()->json(['message' => 'Account status updated.']);
    }

    public function roles(Request $request): JsonResponse
    {
        Gate::authorize('iam.role.view');
        $actor = $this->actor($request);
        $roles = Role::query()->where('institution_id', $actor->institution_id)
            ->withCount(['permissions', 'assignments'])->orderBy('name')->get();

        return response()->json(['data' => $roles]);
    }

    public function assignRole(Request $request, User $user): JsonResponse
    {
        Gate::authorize('iam.role.assign');
        $actor = $this->actor($request);
        abort_unless($user->institution_id === $actor->institution_id, 404);
        $validated = $request->validate([
            'role_id' => ['required', 'uuid', 'exists:iam.roles,id'],
            'scope_type' => ['required', 'in:institution,campus,faculty,department,self'],
            'scope_id' => ['nullable', 'uuid'], 'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'], 'reason' => ['required', 'string', 'min:8'],
        ]);
        $role = Role::query()->where('institution_id', $actor->institution_id)
            ->whereKey($validated['role_id'])->firstOrFail();
        $assignment = RoleAssignment::query()->create([
            'institution_id' => $actor->institution_id, 'user_id' => $user->id, 'role_id' => $role->id,
            'scope_type' => $validated['scope_type'], 'scope_id' => $validated['scope_id'] ?? null,
            'starts_at' => $validated['starts_at'] ?? now(), 'ends_at' => $validated['ends_at'] ?? null,
            'granted_by' => $actor->id, 'grant_reason' => $validated['reason'],
        ]);
        $user->increment('access_version');
        $user->refresh();
        $this->sessions->revokeAll($user, 'ROLE_ELEVATED');

        return response()->json(['message' => 'Role assigned.', 'data' => ['id' => $assignment->id]], 201);
    }

    public function resetMfa(Request $request, User $user): JsonResponse
    {
        Gate::authorize('iam.user.reset-password');
        $actor = $this->actor($request);
        abort_unless($user->institution_id === $actor->institution_id, 404);
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:8', 'max:500'],
        ]);

        $user->auditReason($validated['reason'])->forceFill([
            'mfa_enabled' => false,
            'mfa_secret' => null,
            'mfa_recovery_codes' => null,
        ])->save();
        DB::table('iam.mfa_challenges')->where('user_id', $user->id)->delete();
        $user->refresh();
        $this->sessions->revokeAll($user, 'MFA_ADMIN_RESET');

        return response()->json(['message' => 'Multi-factor authentication reset. The user must enroll again.']);
    }

    private function actor(Request $request): User
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);

        return $actor;
    }
}

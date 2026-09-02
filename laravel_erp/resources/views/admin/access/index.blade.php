@extends('layouts.app')
@section('title', 'Access Control')
@section('section', 'Administration · Access control')
@section('content')
<div class="page-head"><div><div class="eyebrow">Deny by default</div><h1 class="heading">Role assignments</h1><p class="sub">Grant scoped, expiring roles with a documented reason.</p></div></div>
<div class="cols">
<section class="panel"><div class="panel-head"><div><h2>Grant role</h2><small>Segregated permissions are only present in their dedicated role bundles.</small></div></div><div class="panel-body">
<form method="POST" action="{{ route('admin.setups.access.assignments.store') }}" class="form-grid">@csrf
<div class="field full"><label>User</label><select name="user_id" required>@foreach($users as $user)<option value="{{ $user->id }}">{{ $user->name }} · {{ $user->email }} · {{ $user->rbac_assignments_count }} assignments</option>@endforeach</select></div>
<div class="field full"><label>Role</label><select name="role_id" required>@foreach($roles as $role)<option value="{{ $role->id }}">{{ $role->name }} — {{ $role->permissions->count() }} permissions</option>@endforeach</select></div>
<div class="field"><label>Scope</label><select name="scope_type" required>@foreach(\App\Modules\Platform\Rbac\PermissionCatalogue::SCOPE_TYPES as $scope)<option value="{{ $scope }}">{{ ucfirst($scope) }}</option>@endforeach</select></div>
<div class="field"><label>Scope identifier</label><input name="scope_id" placeholder="Required for narrowed scopes"></div>
<div class="field"><label>Expires at</label><input name="expires_at" type="datetime-local"></div>
<div class="field full"><label>Grant reason</label><textarea name="grant_reason" minlength="10" maxlength="255" required></textarea></div>
<div class="field full"><button class="btn">Grant role</button></div>
</form></div></section>
<section class="panel"><div class="panel-head"><div><h2>Canonical roles</h2><small>Permission membership is catalogue-controlled.</small></div></div><div class="panel-body">
@foreach($roles as $role)<div class="result-line"><span><strong>{{ $role->name }}</strong><small>{{ $role->description }}</small></span><span class="badge">{{ $role->permissions->count() }}</span></div>@endforeach
</div></section></div>
<section class="panel" style="margin-top:20px"><div class="panel-head"><div><h2>Current assignments</h2><small>Expired grants are automatically ignored by authorization.</small></div></div><div class="panel-body"><div class="table-wrap"><table><thead><tr><th>User</th><th>Role</th><th>Scope</th><th>Expiry</th><th>Reason</th><th>Revoke</th></tr></thead><tbody>
@forelse($assignments as $assignment)<tr><td>{{ $assignment->user?->name }}</td><td>{{ $assignment->role?->name }}</td><td>{{ $assignment->scope_type }}{{ $assignment->scope_id ? ': '.$assignment->scope_id : '' }}</td><td>{{ $assignment->expires_at?->format('d M Y H:i') ?? 'No expiry' }}</td><td>{{ $assignment->grant_reason }}</td><td><form method="POST" action="{{ route('admin.setups.access.assignments.destroy', $assignment) }}">@csrf @method('DELETE')<input name="revocation_reason" minlength="10" maxlength="500" required placeholder="Revocation reason"><button class="btn btn-danger">Revoke</button></form></td></tr>@empty<tr><td colspan="6">No role assignments.</td></tr>@endforelse
</tbody></table></div>{{ $assignments->links() }}</div></section>
@endsection

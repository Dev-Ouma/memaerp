@extends('layouts.app')
@section('title', 'Data Governance')
@section('section', 'Administration · Data governance')
@section('content')
<div class="page-head">
    <div><div class="eyebrow">Platform administration</div><h1 class="heading">Data governance</h1><p class="sub">Effective-dated retention, legal holds and independent purge approval.</p></div>
    <a class="btn btn-secondary" href="{{ route('admin.setups.governance.audit') }}">View audit trail <i data-lucide="scroll-text"></i></a>
</div>

<div class="grid4" style="margin-bottom:20px">
    @foreach ([['Active retention rules', $stats['activeRules']], ['Active legal holds', $stats['activeHolds']], ['Pending purge checks', $stats['pendingPurges']], ['Audit events', $stats['auditEvents']]] as [$label, $value])
        <article class="stat"><div class="stat-head"><span>{{ $label }}</span><i data-lucide="shield"></i></div><b>{{ $value }}</b></article>
    @endforeach
</div>

<section class="panel" style="margin-bottom:20px">
    <div class="panel-head"><div><h2>Retention rule versions</h2><small>Historical deletion records retain the exact version used.</small></div></div>
    <div class="panel-body">
        <form method="POST" action="{{ route('admin.setups.governance.retention.store') }}" class="form-grid" data-processing-message="Publishing retention version…">
            @csrf
            <div class="field"><label>Rule code</label><input name="code" required pattern="[A-Z0-9_-]+" placeholder="CURRICULUM-MASTER-DATA"></div>
            <div class="field"><label>Subject type</label><input name="subject_type" required placeholder="curriculum_master_data"></div>
            <div class="field full"><label>Description</label><input name="description" required></div>
            <div class="field"><label>Retention months</label><input name="retention_months" type="number" min="1" max="1200" required></div>
            <div class="field"><label>Disposal action</label><select name="disposal_action" required><option>PURGE</option><option>ARCHIVE</option><option>ANONYMISE</option></select></div>
            <div class="field"><label>Effective from</label><input name="effective_from" type="date" required></div>
            <div class="field"><label>Effective to</label><input name="effective_to" type="date"></div>
            <div class="field full"><label>Change reason</label><textarea name="change_reason" minlength="10" maxlength="500" required></textarea></div>
            <div class="field full"><button class="btn" type="submit">Publish new version</button></div>
        </form>
        <div class="table-wrap" style="margin-top:20px"><table><thead><tr><th>Code</th><th>Version</th><th>Subject</th><th>Retention</th><th>Effective dates</th><th>Status</th></tr></thead><tbody>
        @forelse ($rules as $rule)<tr><td>{{ $rule->code }}</td><td>v{{ $rule->version }}</td><td>{{ $rule->subject_type }}</td><td>{{ $rule->retention_months }} months · {{ $rule->disposal_action }}</td><td>{{ $rule->effective_from?->toDateString() }} — {{ $rule->effective_to?->toDateString() ?? 'open' }}</td><td>{{ $rule->status }}</td></tr>@empty<tr><td colspan="6">No retention rules configured.</td></tr>@endforelse
        </tbody></table></div>{{ $rules->links() }}
    </div>
</section>

<section class="panel" style="margin-bottom:20px">
    <div class="panel-head"><div><h2>Pending purge checker queue</h2><small>The requester cannot approve their own permanent deletion.</small></div></div>
    <div class="panel-body"><div class="table-wrap"><table><thead><tr><th>Record</th><th>Requested by</th><th>Reason</th><th>Decision</th></tr></thead><tbody>
    @forelse ($pendingPurges as $action)
        <tr><td>{{ $action->deletionRecord?->entity_type }} #{{ $action->deletionRecord?->record_id }}</td><td>{{ $action->requester?->name }}</td><td>{{ $action->reason }}</td><td><form method="POST" action="{{ route('admin.setups.recycle-bin.purge.approve', $action) }}">@csrf<input name="decision_note" minlength="10" maxlength="500" required placeholder="Independent review note"><button class="btn" type="submit">Approve purge</button></form></td></tr>
    @empty<tr><td colspan="4">No purge requests await approval.</td></tr>@endforelse
    </tbody></table></div>{{ $pendingPurges->links() }}</div>
</section>

<section class="panel">
    <div class="panel-head"><div><h2>Legal holds</h2><small>Holds override elapsed retention and block purge.</small></div></div>
    <div class="panel-body">
        <form method="POST" action="{{ route('admin.setups.governance.holds.store') }}" class="form-grid">@csrf
            <div class="field"><label>Deletion record UUID</label><input name="deletion_record_id" required></div>
            <div class="field"><label>Hold reason</label><input name="reason" minlength="10" maxlength="500" required></div>
            <div class="field full"><button class="btn" type="submit">Place legal hold</button></div>
        </form>
        <div class="table-wrap" style="margin-top:20px"><table><thead><tr><th>Subject</th><th>Reason</th><th>Placed</th><th>Release</th></tr></thead><tbody>
        @forelse ($activeHolds as $hold)<tr><td>{{ class_basename($hold->subject_type) }} #{{ $hold->subject_id }}</td><td>{{ $hold->reason }}</td><td>{{ $hold->placed_at?->format('d M Y H:i') }}</td><td><form method="POST" action="{{ route('admin.setups.governance.holds.release', $hold) }}">@csrf @method('PATCH')<input name="release_reason" minlength="10" maxlength="500" required placeholder="Release reason"><button class="btn btn-secondary">Release</button></form></td></tr>@empty<tr><td colspan="4">No active legal holds.</td></tr>@endforelse
        </tbody></table></div>{{ $activeHolds->links() }}
    </div>
</section>
@endsection

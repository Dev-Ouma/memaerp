@extends('layouts.app')
@section('title', 'Audit Trail')
@section('section', 'Administration · Data governance · Audit')
@section('content')
<div class="page-head"><div><div class="eyebrow">Append-only evidence</div><h1 class="heading">Platform audit trail</h1><p class="sub">Search governed actions by actor, subject and date.</p></div><a class="btn btn-secondary" href="{{ route('admin.setups.governance.index') }}">Back to governance</a></div>
<section class="panel"><div class="panel-body">
    <form method="GET" class="form-grid">
        <div class="field"><label>Action contains</label><input name="action" value="{{ request('action') }}"></div>
        <div class="field"><label>Subject type contains</label><input name="subject_type" value="{{ request('subject_type') }}"></div>
        <div class="field"><label>Actor user ID</label><input name="actor_user_id" type="number" value="{{ request('actor_user_id') }}"></div>
        <div class="field"><label>From</label><input name="from" type="date" value="{{ request('from') }}"></div>
        <div class="field"><label>To</label><input name="to" type="date" value="{{ request('to') }}"></div>
        <div class="field"><button class="btn" type="submit">Apply filters</button></div>
    </form>
    <div class="table-wrap" style="margin-top:20px"><table><thead><tr><th>Sequence</th><th>Occurred</th><th>Actor</th><th>Action</th><th>Subject</th><th>Evidence hash</th></tr></thead><tbody>
    @forelse ($events as $event)<tr><td>{{ $event->sequence_no }}</td><td>{{ $event->occurred_at?->format('d M Y H:i:s') }}</td><td>{{ $event->actor_user_id ?? 'system' }} · {{ $event->actor_role }}</td><td>{{ $event->action }}</td><td>{{ class_basename($event->subject_type ?? '') }} #{{ $event->subject_id }}</td><td><code title="{{ $event->evidence_hash }}">{{ substr($event->evidence_hash, 0, 16) }}…</code></td></tr>@empty<tr><td colspan="6">No audit events match these filters.</td></tr>@endforelse
    </tbody></table></div>{{ $events->links() }}
</div></section>
@endsection

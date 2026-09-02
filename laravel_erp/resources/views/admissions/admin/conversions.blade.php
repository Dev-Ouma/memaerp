@extends('layouts.app') @section('title','Student conversions') @section('section','Admissions') @section('content')
<div class="page-head"><div><div class="eyebrow">Applicant to student</div><h1 class="heading">Student conversion ledger</h1><p class="sub" style="margin:0">Every application that has crossed into academic records, and every attempt that failed.</p></div><a class="btn btn-secondary" href="{{ route('admissions.index') }}"><i data-lucide="arrow-left"></i>Application queue</a></div>
<div class="grid4">
<div class="stat"><div class="stat-head"><span>Completed</span><i data-lucide="badge-check"></i></div><b>{{ $tally['COMPLETED'] ?? 0 }}</b><small>Students created</small></div>
<div class="stat"><div class="stat-head"><span>Failed</span><i data-lucide="triangle-alert"></i></div><b>{{ $tally['FAILED'] ?? 0 }}</b><small>Need attention</small></div>
<div class="stat"><div class="stat-head"><span>Pending</span><i data-lucide="loader"></i></div><b>{{ $tally['PENDING'] ?? 0 }}</b><small>Claimed, not finished</small></div>
<div class="stat"><div class="stat-head"><span>Awaiting enrolment</span><i data-lucide="hourglass"></i></div><b>{{ $awaiting }}</b><small>Offers accepted</small></div>
</div>
<section class="panel" style="margin-top:18px"><div class="panel-head"><h2>Conversions</h2><form style="display:flex;gap:7px"><select name="status" style="padding:8px;border:1px solid var(--line);border-radius:5px"><option value="">All outcomes</option>@foreach(['PENDING','COMPLETED','FAILED'] as $status)<option @selected(request('status')===$status)>{{ $status }}</option>@endforeach</select><button class="btn">Filter</button></form></div>
<div class="table-wrap"><table><thead><tr><th>Applicant</th><th>Application</th><th>Programme</th><th>Student number</th><th>Outcome</th><th>Converted</th><th></th></tr></thead><tbody>
@forelse($conversions as $conversion)
<tr>
<td><strong>{{ $conversion->application?->applicant?->user?->name ?? '—' }}</strong><small style="display:block;color:var(--muted)">{{ $conversion->application?->applicant?->applicant_number }}</small></td>
<td>{{ $conversion->application?->application_number ?? '—' }}</td>
<td>{{ $conversion->application?->offering?->course?->name ?? '—' }}</td>
<td>{{ $conversion->student_number ?? '—' }}</td>
<td><span class="badge">{{ $conversion->status }}</span>@if($conversion->status==='FAILED')<small style="display:block;color:var(--muted)">{{ $conversion->failure_code }}: {{ $conversion->failure_reason }}</small>@endif</td>
<td>{{ $conversion->converted_at?->format('d M Y H:i') ?? '—' }}</td>
<td>@if($conversion->status==='FAILED' && auth()->user()->isAdmin())<form method="post" action="{{ route('admissions.conversions.retry',$conversion) }}">@csrf<button class="btn">Retry</button></form>@endif</td>
</tr>
@empty
<tr><td colspan="7">No conversions recorded yet. Applications reach this ledger once an applicant completes enrolment.</td></tr>
@endforelse
</tbody></table></div>
<div style="padding:12px">{{ $conversions->links() }}</div>
</section>
@endsection

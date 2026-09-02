@extends('layouts.app')

@section('title', 'Application ' . $application->application_number)
@section('section', 'Admissions')

@section('content')
<div class="mema-dashboard-container py-2">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <div class="flex items-center gap-2">
                <span class="font-mono text-xs font-bold text-[#0A3E50] bg-teal-50 px-2 py-0.5 rounded border border-teal-200">
                    Ref: {{ $application->application_number }}
                </span>
                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold
                    @if(in_array($application->status, ['ADMITTED', 'ACCEPTED', 'READY_TO_ENROL', 'ENROLLED'])) bg-emerald-100 text-emerald-800 border border-emerald-300
                    @elseif(in_array($application->status, ['UNDER_REVIEW', 'SHORTLISTED', 'APPROVAL_PENDING', 'SUBMITTED'])) bg-blue-100 text-blue-800 border border-blue-300
                    @elseif(in_array($application->status, ['RETURNED_FOR_CORRECTION', 'INFO_REQUESTED', 'WAITLISTED', 'DRAFT'])) bg-amber-100 text-amber-800 border border-amber-300
                    @else bg-red-100 text-red-800 border border-red-300 @endif">
                    {{ str_replace('_', ' ', $application->status) }}
                </span>
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 mt-1 tracking-tight">{{ $application->applicant->user->name }}</h1>
            <p class="text-xs text-slate-500 font-medium">
                {{ $application->offering->course->name }} ({{ $application->offering->course->code }}) · {{ $application->offering->intake->name }} · {{ $application->offering->campus }} ({{ $application->offering->study_mode }})
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admissions.application.letter', $application) }}" target="_blank" class="px-3.5 py-1.5 rounded-md bg-[#0A3E50] hover:bg-[#08303e] font-bold text-xs transition-colors shadow-2xs inline-flex items-center gap-1.5" style="color: #ffffff !important; text-decoration: none !important;">
                <i data-lucide="file-text" class="w-3.5 h-3.5" style="color: #ffffff !important;"></i>
                <span style="color: #ffffff !important;">Official Letter</span>
            </a>
            @if(in_array($application->status, ['ADMITTED', 'ACCEPTED', 'READY_TO_ENROL']) && !$application->isEnrolled())
                <form method="post" action="{{ route('admissions.application.convert', $application) }}" onsubmit="return confirm('Convert this admitted applicant into an official enrolled student?');" class="inline">
                    @csrf
                    <button type="submit" class="px-3.5 py-1.5 rounded-md bg-[#1E8449] hover:bg-[#166534] text-white font-bold text-xs transition-colors shadow-2xs inline-flex items-center gap-1.5">
                        <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                        Convert to Student
                    </button>
                </form>
            @endif
            <a href="{{ route('admissions.index') }}" class="px-3 py-1.5 rounded-md border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-colors">
                &larr; Back to List
            </a>
        </div>
    </div>

    {{-- Top 4 KPI Metric Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Application Fee</div>
            <div class="text-2xl font-extrabold @if($application->isPaid()) text-[#1E8449] @else text-amber-700 @endif mt-2 mb-1.5 leading-none">
                {{ $application->isPaid() ? 'PAID & SETTLED' : 'PAYMENT PENDING' }}
            </div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">KES {{ number_format($application->offering->application_fee) }} standard fee.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold @if($application->isPaid()) text-emerald-800 bg-emerald-50 border border-emerald-200 @else text-amber-800 bg-amber-50 border border-amber-200 @endif">
                    {{ $application->payments()->where('status', 'PAID')->value('reference') ?? 'Gated at submission' }}
                </span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Evidence Documents</div>
            <div class="text-3xl font-extrabold text-[#0A3E50] mt-2 mb-1.5 leading-none">{{ $application->documents->count() }} Files</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">{{ $application->documents->where('verification_status', 'VERIFIED')->count() }} verified, {{ $application->documents->where('verification_status', 'PENDING')->count() }} pending.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">SHA-256 Checksummed</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Academic Assessment</div>
            <div class="text-3xl font-extrabold text-purple-800 mt-2 mb-1.5 leading-none">
                {{ $application->reviews->avg('score') ? round($application->reviews->avg('score')) . '/100' : 'Pending' }}
            </div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">{{ $application->reviews->count() }} faculty reviews recorded.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-purple-800 bg-purple-50 border border-purple-200">Academic Merit</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Processing Turnaround</div>
            <div class="text-3xl font-extrabold text-slate-800 mt-2 mb-1.5 leading-none">{{ $application->created_at->diffInDays(now()) }} Days</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Applied on {{ $application->created_at ? (is_string($application->created_at) ? date('d M, Y', strtotime($application->created_at)) : $application->created_at->format('d M, Y')) : '—' }}.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200">Within Target SLA</span></div>
        </div>
    </div>

    {{-- Main 2-Column Workspace --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Left: Applicant Profile & Evidence (2 cols) --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Personal and Profile Details --}}
            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs">
                <div class="flex justify-between items-center pb-3 border-b border-slate-100 mb-4">
                    <h2 class="text-sm font-bold text-[#0A3E50] uppercase tracking-wide">Applicant Information &amp; Bio-Data</h2>
                    <span class="font-mono text-xs text-slate-500 font-semibold">{{ $application->applicant->applicant_number }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-slate-500 font-medium block">Full Legal Name</span>
                        <span class="font-bold text-slate-900 text-sm">{{ $application->applicant->user->name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 font-medium block">Primary Email</span>
                        <span class="font-bold text-slate-900">{{ $application->applicant->user->email }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 font-medium block">Phone Contact</span>
                        <span class="font-bold text-slate-900">{{ $application->applicant->phone ?? 'Not provided' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 font-medium block">Date of Birth / Nationality</span>
                        <span class="font-bold text-slate-900">{{ $application->applicant->date_of_birth ? date('d M, Y', strtotime($application->applicant->date_of_birth)) : '—' }} · {{ $application->applicant->nationality ?? 'Kenyan' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 font-medium block">Identity Type &amp; Number</span>
                        <span class="font-bold text-slate-900">{{ strtoupper($application->applicant->identity_type ?? 'National ID') }}: {{ $application->applicant->identity_number ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 font-medium block">County of Residence</span>
                        <span class="font-bold text-slate-900">{{ $application->applicant->county ?? '—' }}</span>
                    </div>
                </div>

                {{-- Academic Statement --}}
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <span class="text-xs font-bold text-slate-700 block mb-1">Academic Background &amp; Entry Qualifications</span>
                    <div class="p-3 bg-slate-50 rounded-lg text-xs text-slate-800 leading-relaxed font-mono whitespace-pre-line border border-slate-200">
                        {{ $application->form_data['education'] ?? 'No prior academic statement supplied.' }}
                    </div>
                </div>
            </div>

            {{-- Supporting Documents & Evidence --}}
            <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
                <div class="bg-[#0A3E50] text-white px-5 py-3 flex justify-between items-center">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-white">Uploaded Supporting Documents &amp; Certificates</h2>
                    <span class="text-[11px] font-mono text-teal-100 font-semibold">{{ $application->documents->count() }} Files</span>
                </div>
                <div class="p-5">
                    @forelse($application->documents as $doc)
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 p-3.5 mb-3 rounded-lg border border-slate-200 bg-slate-50/50 hover:bg-slate-50 transition-colors">
                            <div class="flex items-start gap-3">
                                <div class="w-9 h-9 rounded bg-[#0A3E50]/10 text-[#0A3E50] flex items-center justify-center shrink-0 mt-0.5">
                                    <i data-lucide="file-check" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-xs">{{ ucfirst(str_replace('_', ' ', $doc->document_type)) }}</div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">
                                        {{ $doc->original_name }} · {{ number_format($doc->size_bytes / 1024, 1) }} KB · Uploaded {{ $doc->created_at ? (is_string($doc->created_at) ? date('d M, Y', strtotime($doc->created_at)) : $doc->created_at->format('d M, Y')) : '—' }}
                                    </div>
                                    <div class="font-mono text-[10px] text-slate-400 mt-0.5">
                                        SHA-256: {{ substr($doc->sha256, 0, 20) }}...
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 self-end sm:self-center flex-wrap">
                                <span class="px-2 py-0.5 rounded text-[10.5px] font-bold
                                    @if($doc->verification_status === 'VERIFIED') bg-emerald-100 text-emerald-800 border border-emerald-200
                                    @elseif($doc->verification_status === 'REJECTED') bg-red-100 text-red-800 border border-red-200
                                    @else bg-amber-100 text-amber-800 border border-amber-200 @endif">
                                    {{ $doc->verification_status }}
                                </span>

                                <a href="{{ route('admissions.document.download', $doc) }}" target="_blank" class="px-2.5 py-1 rounded bg-white border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-semibold inline-flex items-center gap-1">
                                    <i data-lucide="download" class="w-3 h-3"></i>
                                    Download
                                </a>

                                @if(in_array(auth()->user()->role, ['admin', 'staff'], true))
                                    @if($doc->verification_status !== 'VERIFIED')
                                        <form method="post" action="{{ route('admissions.document.verify', $doc) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="VERIFIED">
                                            <button type="submit" class="px-2.5 py-1 rounded bg-[#1E8449] hover:bg-[#166534] text-white text-xs font-bold transition-colors">
                                                Verify
                                            </button>
                                        </form>
                                    @endif
                                    @if($doc->verification_status !== 'REJECTED')
                                        <form method="post" action="{{ route('admissions.document.verify', $doc) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="REJECTED">
                                            <button type="submit" class="px-2.5 py-1 rounded bg-red-600 hover:bg-red-700 text-white text-xs font-bold transition-colors">
                                                Reject
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-slate-500 text-xs">
                            No evidence documents uploaded for this application.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Right: Actions, Scoring Rubric & State Controls (1 col) --}}
        <div class="space-y-6">
            {{-- Review & Academic Scoring Rubric --}}
            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs">
                <div class="flex items-center gap-2 pb-3 border-b border-slate-100 mb-4">
                    <i data-lucide="clipboard-check" class="w-4 h-4 text-[#0A3E50]"></i>
                    <h2 class="text-xs font-bold text-[#0A3E50] uppercase tracking-wide">Academic Eligibility Assessment</h2>
                </div>
                <form method="post" action="{{ route('admissions.review', $application) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Merit / Rubric Score (0 - 100)</label>
                        <input type="number" name="score" min="0" max="100" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-mono font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#0A3E50]" placeholder="e.g. 85" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Faculty Recommendation</label>
                        <select name="recommendation" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-800 bg-white focus:outline-none focus:ring-2 focus:ring-[#0A3E50]">
                            <option value="verify">Recommend Verification</option>
                            <option value="shortlist">Recommend Shortlisting</option>
                            <option value="waitlist">Recommend Waitlist</option>
                            <option value="reject">Recommend Rejection</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Evidence &amp; Academic Rationale</label>
                        <textarea name="notes" rows="3" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#0A3E50]" placeholder="Explain eligibility verdict, cluster scores, or prerequisites..." required></textarea>
                    </div>

                    <button type="submit" class="w-full py-2 rounded-lg bg-[#0A3E50] hover:bg-[#08303e] text-white font-bold text-xs transition-colors shadow-xs">
                        Record Faculty Evaluation
                    </button>
                </form>

                {{-- Previous Reviews --}}
                @if($application->reviews->isNotEmpty())
                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wide block mb-2">Recorded Reviews</span>
                        @foreach($application->reviews as $rev)
                            <div class="p-2.5 bg-slate-50 rounded border border-slate-200 text-xs mb-2">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="font-bold text-slate-800 uppercase text-[10px]">{{ $rev->recommendation }}</span>
                                    <span class="font-mono font-bold text-purple-900">{{ $rev->score }}/100</span>
                                </div>
                                <p class="text-slate-600 text-[11px]">{{ $rev->notes }}</p>
                                <span class="text-[10px] text-slate-400 block mt-1">By {{ $rev->reviewer->name ?? 'Faculty Reviewer' }} · {{ $rev->created_at ? (is_string($rev->created_at) ? date('d M, Y', strtotime($rev->created_at)) : $rev->created_at->format('d M, Y')) : '—' }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Controlled Status Transitions --}}
            @if(auth()->user()->isAdmin())
                <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs">
                    <div class="flex items-center gap-2 pb-3 border-b border-slate-100 mb-4">
                        <i data-lucide="sliders" class="w-4 h-4 text-[#E67E22]"></i>
                        <h2 class="text-xs font-bold text-[#0A3E50] uppercase tracking-wide">Lifecycle Decision &amp; State Transition</h2>
                    </div>
                    <form method="post" action="{{ route('admissions.transition', $application) }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Target Lifecycle Status</label>
                            <select name="status" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-800 bg-white focus:outline-none focus:ring-2 focus:ring-[#0A3E50]">
                                @foreach(['UNDER_REVIEW', 'RETURNED_FOR_CORRECTION', 'INFO_REQUESTED', 'VERIFIED', 'SHORTLISTED', 'APPROVAL_PENDING', 'ADMITTED', 'ADMITTED_CONDITIONAL', 'WAITLISTED', 'READY_TO_ENROL', 'ENROLLED', 'REJECTED', 'DEFERRED'] as $st)
                                    <option value="{{ $st }}" @if($application->status === $st) selected @endif>{{ str_replace('_', ' ', $st) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Reason Code</label>
                            <input name="reason" value="admissions_board_decision" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#0A3E50]" required>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Formal Decision Note / Feedback to Applicant</label>
                            <textarea name="note" rows="3" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#0A3E50]" placeholder="Enter reason, requirements for correction, or Senate resolution details..." required>Authorized by Admissions Committee.</textarea>
                        </div>

                        <button type="submit" class="w-full py-2 rounded-lg bg-[#E67E22] hover:bg-[#d35400] text-white font-bold text-xs transition-colors shadow-xs">
                            Execute State Transition
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    {{-- Immutable Audit Timeline History --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="bg-[#0A3E50] text-white px-5 py-3 flex justify-between items-center">
            <h2 class="text-xs font-bold uppercase tracking-wider text-white">Immutable Admissions Audit History &amp; Transitions</h2>
            <span class="text-[11px] font-mono text-teal-100 font-semibold">{{ $application->histories->count() }} Events</span>
        </div>
        <div class="p-5 divide-y divide-slate-100">
            @forelse($application->histories->sortByDesc('created_at') as $history)
                <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-slate-800">{{ $history->from_status ?: 'Draft Created' }}</span>
                            <span class="text-slate-400">&rarr;</span>
                            <span class="font-bold text-[#0A3E50]">{{ $history->to_status }}</span>
                            <span class="text-[10.5px] font-mono text-slate-400">({{ $history->reason_code }})</span>
                        </div>
                        <p class="text-slate-600 text-xs mt-0.5">{{ $history->note }}</p>
                    </div>
                    <div class="font-mono text-[11px] text-slate-400 shrink-0">
                        {{ $history->created_at ? (is_string($history->created_at) ? date('d M, Y H:i:s', strtotime($history->created_at)) : $history->created_at->format('d M, Y H:i:s')) : '—' }}
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-slate-500 text-xs">
                    No transition history logged yet.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

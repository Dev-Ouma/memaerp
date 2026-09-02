@extends('layouts.app')

@section('title', 'Document Verification & Authentication')
@section('section', 'Admissions')

@section('content')
<div class="mema-dashboard-container py-2">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Academic Document Verification &amp; Authenticity Auditing</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Verify KNEC KCSE result slips, National IDs/Passports, foreign certificates with CUE equivalence, and leaving certificates</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="px-4 py-1.5 rounded-md border border-slate-300 text-slate-400 font-bold text-xs cursor-not-allowed shadow-2xs" disabled
                    title="The KNEC results feed is not connected. Verify each bundle from the application file until it is.">
                Batch KNEC Sync
            </button>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Pending Document Audit</div>
            <div class="text-3xl font-extrabold text-amber-700 mt-2 mb-1.5 leading-none">{{ $stats['pendingVerification'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Awaiting clerk verification.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-200">Active Queue</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Authenticated Today</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['verifiedToday'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Passed KCSE/KNEC checks.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">High Throughput</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Authenticity Rate</div>
            <div class="text-3xl font-extrabold text-[#0A3E50] mt-2 mb-1.5 leading-none">{{ $stats['authenticityRate'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Tamper-evident verification.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">KNEC Certified</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">CUE / KNEC Escalations</div>
            <div class="text-3xl font-extrabold text-red-700 mt-2 mb-1.5 leading-none">{{ $stats['knecEscalations'] }} Cases</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Discrepancy or foreign degree.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-red-800 bg-red-50 border border-red-200">Manual Vetting</span></div>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    @include('admissions.admin.workspaces.partials.toolbar', [
        'rows' => $verifications,
        'noun' => 'application document bundles',
        'search' => 'Search applicant, app no...',
        'selects' => [
            ['name' => 'status', 'label' => 'All Statuses', 'options' => ['Verified' => 'Verified', 'Pending Verification' => 'Pending Verification', 'Flagged' => 'Flagged']],
        ],
    ])

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">App Number &amp; Applicant</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Applied Programme</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Documents Uploaded</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Entry Qualifications</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Authenticity Check</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Status</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-center uppercase text-[11px] w-24" style="color:#ffffff !important;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($verifications as $ver)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $ver['app_no'] }}</span>
                                <div class="font-bold text-slate-900 text-xs mt-1">{{ $ver['applicant_name'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800">{{ $ver['programme'] }}</td>
                            <td class="py-3.5 px-4 text-slate-600">{{ $ver['docs_uploaded'] }}</td>
                            <td class="py-3.5 px-4 font-bold text-purple-900">{{ $ver['qualification'] }}</td>
                            <td class="py-3.5 px-4 font-mono text-[11px] text-[#0A3E50] font-semibold">{{ $ver['authenticity_check'] }}</td>
                            <td class="py-3.5 px-4">
                                @if(str_contains($ver['status'], 'Verified'))
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">{{ $ver['status'] }}</span>
                                @elseif(str_contains($ver['status'], 'Ready'))
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-cyan-100 text-cyan-800">{{ $ver['status'] }}</span>
                                @elseif(str_contains($ver['status'], 'Escalated'))
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-red-100 text-red-800">{{ $ver['status'] }}</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-amber-100 text-amber-800">{{ $ver['status'] }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <a href="{{ route('admissions.show', $ver['application_id']) }}" class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">
                                    Verify
                                </a>
                            </td>
                        </tr>
                    @empty
                        @include('admissions.admin.workspaces.partials.empty', [
                            'colspan' => 7,
                            'message' => 'No document bundles are awaiting verification.',
                            'hint' => 'Bundles appear here once applicants upload supporting documents.',
                        ])
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('admissions.admin.workspaces.partials.pagination', ['rows' => $verifications])
    </div>
</div>
@endsection

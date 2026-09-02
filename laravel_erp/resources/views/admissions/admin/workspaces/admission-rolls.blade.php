@extends('layouts.app')

@section('title', 'Admission Rolls (Matriculation Register)')
@section('section', 'Admissions')

@section('content')
<div class="mema-dashboard-container py-2">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Official University Admission Rolls &amp; Matriculation Register</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Permanent senate-approved admission register grouped by School, Department, Programme, and Cohort Year with issued registration numbers</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admissions.rolls.export', request()->query()) }}" class="px-4 py-1.5 rounded-md bg-[#0A3E50] hover:bg-[#08303e] text-white font-bold text-xs transition-colors shadow-2xs inline-flex items-center gap-1.5">
                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                Print Official Roll
            </a>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Total Matriculated</div>
            <div class="text-3xl font-extrabold text-[#0A3E50] mt-2 mb-1.5 leading-none">{{ $stats['totalMatriculated'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Active first year students.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">Official Register</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Reg Numbers Issued</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['regNumbersIssued'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Unique permanent student IDs.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">100% Allocated</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Female Representation</div>
            <div class="text-3xl font-extrabold text-purple-800 mt-2 mb-1.5 leading-none">{{ $stats['femaleRatio'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Gender parity balance.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-purple-800 bg-purple-50 border border-purple-200">Balanced Intake</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Academic Schools</div>
            <div class="text-3xl font-extrabold text-amber-700 mt-2 mb-1.5 leading-none">{{ $stats['schoolsRepresented'] }} Schools</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Represented in current roll.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-200">Full Coverage</span></div>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    @include('admissions.admin.workspaces.partials.toolbar', [
        'rows' => $rolls,
        'noun' => 'entries on the admission roll',
        'search' => 'Search student, app no...',
        'selects' => [
            ['name' => 'cohort', 'label' => 'All Cohorts', 'options' => $cohorts],
            ['name' => 'status', 'label' => 'All Statuses', 'options' => ['ENROLLED' => 'Enrolled', 'ADMITTED' => 'Admitted', 'ACCEPTED' => 'Accepted', 'READY_TO_ENROL' => 'Ready to Enrol']],
        ],
    ])

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Admission Number</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Student Full Name</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Programme Name</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">School / Faculty</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Cohort Batch</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Enrolment Date</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Status</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-center uppercase text-[11px] w-28" style="color:#ffffff !important;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($rolls as $roll)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-extrabold text-[#0A3E50] bg-slate-100 px-2 py-0.5 rounded border border-slate-300">{{ $roll['admission_number'] }}</span>
                                <div class="font-mono text-[10px] text-slate-400 mt-1">{{ $roll['app_no'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-900">{{ $roll['student_name'] }}</td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800">{{ $roll['programme'] }}</td>
                            <td class="py-3.5 px-4 text-slate-600">{{ $roll['school'] }}</td>
                            <td class="py-3.5 px-4 font-mono text-[11px] text-purple-900 font-semibold">{{ $roll['cohort'] }}</td>
                            <td class="py-3.5 px-4 font-mono text-[11px] text-slate-600">{{ $roll['enrolment_date'] }}</td>
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    {{ $roll['status'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <a href="{{ route('admissions.show', $roll['application_id']) }}" class="px-3 py-1 rounded border border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold text-xs transition-colors">
                                    Profile
                                </a>
                            </td>
                        </tr>
                    @empty
                        @include('admissions.admin.workspaces.partials.empty', [
                            'colspan' => 8,
                            'message' => 'No student has been matriculated yet.',
                            'hint' => 'Entries are created when an accepted applicant completes enrolment.',
                        ])
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('admissions.admin.workspaces.partials.pagination', ['rows' => $rolls])
    </div>
</div>
@endsection

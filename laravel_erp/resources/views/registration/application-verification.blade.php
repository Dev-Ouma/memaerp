@extends('layouts.app')

@section('title', 'Application Verification')

@section('content')
<div class="mema-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Application Verification & Document Authentication</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Verify incoming direct and international applications, KNEC KCSE result slips, national IDs, and academic qualification criteria</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                Batch KNEC Verify
            </button>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Pending Verification</div>
            <div class="text-3xl font-extrabold text-amber-700 mt-2 mb-1.5 leading-none">{{ $stats['pendingVerification'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">In document audit queue.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-200">Awaiting Review</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Verified Today</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['verifiedToday'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Passed academic audit.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">High Throughput</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Authenticity Rate</div>
            <div class="text-3xl font-extrabold text-blue-900 mt-2 mb-1.5 leading-none">{{ $stats['docAuthenticityRate'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">KNEC & CUE verified.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">Validated</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">KNEC Escalations</div>
            <div class="text-3xl font-extrabold text-red-700 mt-2 mb-1.5 leading-none">{{ $stats['escalatedToKNEC'] }} Cases</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Index discrepancies.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-red-800 bg-red-50 border border-red-200">Special Audit</span></div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">App No & Applicant</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Applied Programme & Faculty</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Entry Qualifications & Documents</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Verification Stage</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Status</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($applications as $app)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $app['app_no'] }}</span>
                                <div class="font-bold text-slate-900 text-xs mt-1">{{ $app['applicant_name'] }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-800 text-xs">{{ $app['programme'] }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ $app['school'] }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-purple-900 text-xs">{{ $app['entry_qualifications'] }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ $app['docs_status'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[11px] text-[#0A3E50] font-bold">{{ $app['verification_stage'] }}</td>
                            <td class="py-3.5 px-4">
                                @if(str_contains($app['status'], 'Ready'))
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">{{ $app['status'] }}</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-amber-100 text-amber-800">{{ $app['status'] }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <button type="button" class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">Verify</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

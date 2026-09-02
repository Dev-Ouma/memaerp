@extends('layouts.app')

@section('title', 'Imprest Permissions')

@section('content')
<div class="ouk-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Imprest Permissions & Financial Authority Thresholds</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Configure institutional imprest limits, signing mandates by administrative tier, permissible expenditure categories, and dual sign-off policies</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                Add Authority Tier
            </button>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Authority Tiers</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['activeAuthorityTiers'] }} Levels</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">VC down to Section Heads.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">Delegated Mandates</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Max Approval Ceiling</div>
            <div class="text-2xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['maxApprovalLimit'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Vice Chancellor mandate ceiling.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">Executive Tier</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Authorized Approvers</div>
            <div class="text-3xl font-extrabold text-blue-900 mt-2 mb-1.5 leading-none">{{ $stats['authorizedApprovers'] }} Officers</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Deans, HODs, & Directors.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">Active Mandates</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Active Vote Heads</div>
            <div class="text-3xl font-extrabold text-purple-900 mt-2 mb-1.5 leading-none">{{ $stats['activeVoteHeads'] }} Accounts</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Departmental fiscal codes.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-purple-800 bg-purple-50 border border-purple-200">Budget Linked</span></div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Authority Level & Role</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Financial Limit Range (Min - Max)</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Allowed Expenditure Categories</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Mandate & Governance Rule</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Status</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($permissions as $perm)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $perm['authority_level'] }}</span>
                                <div class="font-bold text-slate-900 text-xs mt-1">{{ $perm['role_title'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[11px] text-slate-800">
                                <div><strong class="text-emerald-800">{{ $perm['min_limit'] }}</strong> to <strong class="text-purple-900">{{ $perm['max_limit'] }}</strong></div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-700 text-xs">{{ $perm['allowed_categories'] }}</td>
                            <td class="py-3.5 px-4 font-semibold text-[#0A3E50] text-xs">{{ $perm['mandate_rule'] }}</td>
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">{{ $perm['status'] }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <button type="button" class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">Edit</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

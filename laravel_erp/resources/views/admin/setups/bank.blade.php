@extends('layouts.app')

@section('title', 'Bank Setup')

@section('content')
<div class="mema-dashboard-container py-2">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Direct Bank API & Integration Setup</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Link corporate banking channels, configure direct IPN endpoint webhooks, and map transaction feeds</p>
        </div>
        <button class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">Link Bank Account</button>
    </div>

    {{-- Setup Grid --}}
    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-xs">
        <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wide border-b border-slate-100 pb-2 mb-4">Direct Banking Integration Status</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/50">
                <span class="inline-block px-2.5 py-0.5 rounded text-[10px] font-bold bg-[#0A3E50] text-white uppercase mb-2">Connected Channels</span>
                <h3 class="text-sm font-bold text-slate-900 mb-1">{{ $stats['linkedBanks'] }} Bank Accounts Mapped</h3>
                <p class="text-xs text-slate-500">Corporate transaction collection channels established with Equity, KCB, Co-op Bank, and Safaricom paybills.</p>
            </div>

            <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/50">
                <span class="inline-block px-2.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase mb-2">Bridge Webhooks</span>
                <h3 class="text-sm font-bold text-slate-900 mb-1">{{ $stats['apiBridges'] }} Active IPN Integrations</h3>
                <p class="text-xs text-slate-500">Real-time callbacks matches direct deposit entries against student invoices immediately. Feed speed status: {{ $stats['clearedFeeds'] }}.</p>
            </div>
        </div>
    </div>
</div>
@endsection

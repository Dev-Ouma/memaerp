@extends('layouts.app')

@section('title', 'Supplier Invoice Permission')

@section('content')
<div class="mema-dashboard-container py-2">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Supplier Invoice Upload Permissions</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Verify administrative roles authorized to upload bills, match purchase orders, and clear initial invoices</p>
        </div>
        <button class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">Add Invoice Uploader</button>
    </div>

    {{-- Rules Grid --}}
    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-xs">
        <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wide border-b border-slate-100 pb-2 mb-4">Invoice Verification Policy Settings</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/50">
                <span class="inline-block px-2.5 py-0.5 rounded text-[10px] font-bold bg-[#0A3E50] text-white uppercase mb-2">Policy Level</span>
                <h3 class="text-sm font-bold text-slate-900 mb-1">{{ $stats['policyLevel'] }}</h3>
                <p class="text-xs text-slate-500">Requires matching of PO reference numbers, KRA PIN compliance verification, and HOD stamp approvals before bills enter the payout queue.</p>
            </div>

            <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/50">
                <span class="inline-block px-2.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase mb-2">Audit Compliance</span>
                <h3 class="text-sm font-bold text-slate-900 mb-1">{{ $stats['lastAudited'] }}</h3>
                <p class="text-xs text-slate-500">Last audit verified by Central Treasury and Internal Compliance Board. Cryptographic transaction logs are enabled for all uploader roles.</p>
            </div>
        </div>
    </div>
</div>
@endsection

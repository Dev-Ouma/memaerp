@extends('layouts.app')

@section('title', 'Supplier Payment Permission')

@section('content')
<div class="mema-dashboard-container py-2">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Supplier Payout Approval permissions</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Verify administrative roles authorized to sign off payment claims, approve EFT transactions, and execute bank wire payouts</p>
        </div>
        <button class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">Add Payout Signatory</button>
    </div>

    {{-- Rules Grid --}}
    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-xs">
        <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wide border-b border-slate-100 pb-2 mb-4">Supplier Payout Limit Policies</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/50">
                <span class="inline-block px-2.5 py-0.5 rounded text-[10px] font-bold bg-[#0A3E50] text-white uppercase mb-2">Limit Threshold</span>
                <h3 class="text-sm font-bold text-slate-900 mb-1">{{ $stats['tierLimit'] }}</h3>
                <p class="text-xs text-slate-500">Expenses exceeding this amount escalate to the Chief Financial Officer (CFO) and require Vice Chancellor central registry approval.</p>
            </div>

            <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/50">
                <span class="inline-block px-2.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase mb-2">Compliance Framework</span>
                <h3 class="text-sm font-bold text-slate-900 mb-1">{{ $stats['compliance'] }}</h3>
                <p class="text-xs text-slate-500">Authorized payouts logs are bound to staff user-specific biometrics codes and verified bank API callbacks.</p>
            </div>
        </div>
    </div>
</div>
@endsection

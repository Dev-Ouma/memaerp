@extends('layouts.app')

@section('title', 'Invoicing Setup')

@section('content')
<div class="mema-dashboard-container py-2">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Student Invoicing & Billing Cycle Config</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Configure automated student invoicing runs, billing schedules, and portal check-in rules</p>
        </div>
        <button class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">Configure Billing</button>
    </div>

    {{-- Setup Grid --}}
    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-xs">
        <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wide border-b border-slate-100 pb-2 mb-4">Billing Execution Parameters</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/50">
                <span class="inline-block px-2.5 py-0.5 rounded text-[10px] font-bold bg-[#0A3E50] text-white uppercase mb-2">Billing Cycles</span>
                <h3 class="text-sm font-bold text-slate-900 mb-1">{{ $stats['billingCycles'] }} Trimester Cycles Mapped</h3>
                <p class="text-xs text-slate-500">Automated invoices generate on cohort check-in. Invoicing includes tuition fee setups, library fees, and tax schemes VAT: {{ $stats['taxSchemes'] }}.</p>
            </div>

            <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/50">
                <span class="inline-block px-2.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase mb-2">Check-in Rules</span>
                <h3 class="text-sm font-bold text-slate-900 mb-1">{{ $stats['paymentRules'] }}</h3>
                <p class="text-xs text-slate-500">Students with outstanding arrears above policy thresholds are blocked from course unit confirmations and exams gate passes.</p>
            </div>
        </div>
    </div>
</div>
@endsection

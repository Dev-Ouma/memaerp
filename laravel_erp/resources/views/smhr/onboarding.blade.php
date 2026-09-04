@extends('layouts.app')

@section('title', 'Staff Onboarding & Induction Pipeline - SMHR')
@section('section', 'SMHR')

@section('content')
<div class="mema-dashboard-container py-2">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('smhr.dashboard') }}" class="text-xs font-semibold text-[#0A3E50] hover:underline">&larr; SMHR Dashboard</a>
                <span class="text-slate-300">/</span>
                <span class="text-xs font-bold text-slate-700">Staff Onboarding</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight mt-1">Staff Onboarding &amp; Induction Pipeline</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">New hire credential audit, bank details capture, IT provisioning (email/ERP), and departmental induction</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="document.getElementById('onboardModal').classList.remove('hidden')" class="px-3.5 py-2 rounded-lg bg-[#0A3E50] hover:bg-[#08303e] font-bold text-xs transition-colors shadow-2xs inline-flex items-center gap-1.5 text-white cursor-pointer" style="color:#ffffff !important;">
                <i data-lucide="user-plus" class="w-3.5 h-3.5 text-white"></i>
                <span style="color:#ffffff !important;">Initiate Onboarding</span>
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-7">
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-600 uppercase tracking-wide">Active In-Pipeline</div>
            <div class="text-2xl font-extrabold text-[#0A3E50] mt-1.5">{{ $onboardingStats['inProgress'] }} Candidates</div>
            <p class="text-[11px] text-slate-500 mt-0.5">Currently being inducted</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-600 uppercase tracking-wide">Completed This Quarter</div>
            <div class="text-2xl font-extrabold text-[#1E8449] mt-1.5">{{ $onboardingStats['completedThisQuarter'] }} Onboarded</div>
            <p class="text-[11px] text-slate-500 mt-0.5">Fully operational</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-600 uppercase tracking-wide">Avg Turnaround</div>
            <div class="text-2xl font-extrabold text-blue-700 mt-1.5">{{ $onboardingStats['avgOnboardingDays'] }}</div>
            <p class="text-[11px] text-slate-500 mt-0.5">Offer acceptance to desk</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-600 uppercase tracking-wide">IT Accounts Ready</div>
            <div class="text-2xl font-extrabold text-purple-700 mt-1.5">{{ $onboardingStats['itAccountsProvisioned'] }}</div>
            <p class="text-[11px] text-slate-500 mt-0.5">Email &amp; ERP access</p>
        </div>
    </div>

    {{-- Onboarding Candidates List --}}
    <div class="space-y-4 mb-8">
        @foreach($candidates as $cand)
            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs hover:border-[#0A3E50] transition-all">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-3 border-b border-slate-100 mb-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-slate-900">{{ $cand['name'] }}</h3>
                            <span class="font-mono text-[11px] font-bold text-[#0A3E50] bg-slate-100 px-1.5 py-0.5 rounded">{{ $cand['id'] }}</span>
                        </div>
                        <p class="text-xs text-slate-600 mt-0.5">{{ $cand['designation'] }} &middot; <strong>{{ $cand['department'] }}</strong></p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <span class="text-[11px] text-slate-500 block">Joining Date</span>
                            <span class="text-xs font-bold text-slate-800">{{ $cand['joining_date'] }}</span>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-extrabold @if($cand['progress'] === 100) bg-emerald-100 text-emerald-800 @else bg-amber-100 text-amber-800 @endif">
                            {{ $cand['stage'] }}
                        </span>
                    </div>
                </div>

                {{-- Progress Bar --}}
                <div class="mb-4">
                    <div class="flex justify-between text-xs font-semibold text-slate-700 mb-1">
                        <span>Onboarding Progress</span>
                        <span class="font-mono font-bold">{{ $cand['progress'] }}%</span>
                    </div>
                    <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-300 @if($cand['progress'] === 100) bg-[#1E8449] @else bg-blue-600 @endif" style="width: {{ $cand['progress'] }}%;"></div>
                    </div>
                </div>

                {{-- 5 Step Verification Checklist --}}
                <div class="grid grid-cols-1 sm:grid-cols-5 gap-2 text-xs">
                    @foreach(($cand['checklist'] ?? []) as $task => $done)
                        <div class="p-2.5 rounded-lg border @if($done) bg-emerald-50/60 border-emerald-200 text-emerald-950 @else bg-slate-50 border-slate-200 text-slate-600 @endif flex items-center gap-2">
                            <i data-lucide="{{ $done ? 'check-circle-2' : 'clock' }}" class="w-4 h-4 @if($done) text-emerald-600 @else text-slate-400 @endif shrink-0"></i>
                            <span class="font-medium text-[11px] leading-tight">{{ $task }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- Initiate Onboard Modal --}}
<div id="onboardModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl border border-slate-200 max-w-lg w-full p-6 relative">
        <div class="flex justify-between items-center pb-3 border-b border-slate-100 mb-4">
            <h3 class="text-base font-bold text-slate-900">Initiate New Staff Onboarding</h3>
            <button type="button" onclick="document.getElementById('onboardModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('smhr.onboarding.store') }}" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 mb-1">Appointee Full Name</label>
                <input type="text" name="name" required placeholder="e.g. Dr. Jane Mwangi" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Email</label>
                    <input type="email" name="email" required placeholder="j.mwangi@mema.ac.ke" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Phone</label>
                    <input type="text" name="phone" required placeholder="+254 7..." class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Designation</label>
                    <input type="text" name="designation" required placeholder="Senior Lecturer" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Department</label>
                    <input type="text" name="department" required placeholder="Computer Science" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Official Reporting / Joining Date</label>
                <input type="date" name="joining_date" required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('onboardModal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 font-semibold text-xs hover:bg-slate-50">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-[#0A3E50] hover:bg-[#08303e] text-white font-bold text-xs transition-colors" style="color:#ffffff !important;">Start Onboarding Pipeline</button>
            </div>
        </form>
    </div>
</div>
@endsection

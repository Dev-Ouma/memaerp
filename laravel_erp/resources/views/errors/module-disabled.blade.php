@extends('layouts.app')

@section('title', 'Module Disabled')

@section('content')
<div class="mema-dashboard-container py-10 flex items-center justify-center min-h-[60vh]">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-10 max-w-lg w-full text-center">
        
        <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-5">
            <i data-lucide="lock" class="w-7 h-7 text-red-600"></i>
        </div>

        <h1 class="text-lg font-extrabold text-slate-900 mb-2">Module Disabled</h1>
        <p class="text-sm font-semibold text-slate-500 mb-1">
            The <span class="text-[#0A3E50] font-bold">{{ $moduleName }}</span> module is currently inactive.
        </p>
        <p class="text-xs text-slate-400 mb-6 leading-relaxed">
            An administrator has temporarily disabled this module. All associated routes and functions are inaccessible until it is re-enabled via the <strong>Admin Setups → Module Manager</strong>.
        </p>

        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('dashboard') }}"
               class="px-5 py-2 rounded-lg bg-[#0A3E50] text-white text-xs font-bold hover:bg-[#082f3e] transition-colors flex items-center justify-center gap-1.5">
                <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                Back to Dashboard
            </a>
            @if(auth()->user()?->isAdmin())
                <a href="{{ route('admin.setups.module-manager') }}"
                   class="px-5 py-2 rounded-lg border border-orange-400 text-orange-600 text-xs font-bold hover:bg-orange-50 transition-colors flex items-center justify-center gap-1.5">
                    <i data-lucide="toggle-right" class="w-3.5 h-3.5"></i>
                    Re-enable Module
                </a>
            @endif
        </div>

        <p class="text-[10.5px] text-slate-300 mt-6 font-mono">
            503 · Module key: <code class="text-slate-400">{{ $moduleKey }}</code>
        </p>
    </div>
</div>
@endsection

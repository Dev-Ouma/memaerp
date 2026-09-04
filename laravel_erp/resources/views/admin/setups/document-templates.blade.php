@extends('layouts.app')

@section('title', 'Document Templates & Dynamic Generation Hub - Admin Setups')
@section('section', 'Admin Setups')

@section('content')
<div class="mema-dashboard-container py-2">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.setups.index') }}" class="text-xs font-semibold text-[#0A3E50] hover:underline">&larr; Admin Setups</a>
                <span class="text-slate-300">/</span>
                <span class="text-xs font-bold text-slate-700">Document Templates Centre</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight mt-1">Institutional Document Templates &amp; Live Generation</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Dynamic template preview, tokenized variable injection, PDF generation, and QR verification for official university letters and forms.</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.setups.document-templates.pdf', ['templateKey' => $selectedKey, 'application_id' => $application?->id]) }}" class="px-3.5 py-2 rounded-lg bg-[#0A3E50] hover:bg-[#072c39] font-bold text-xs transition-colors shadow-2xs inline-flex items-center gap-1.5 text-white" style="color:#ffffff !important;">
                <i data-lucide="download" class="w-3.5 h-3.5 text-white"></i>
                <span style="color:#ffffff !important;">Download PDF</span>
            </a>
            <button type="button" onclick="printPreviewFrame()" class="px-3.5 py-2 rounded-lg bg-[#1E8449] hover:bg-[#166534] font-bold text-xs transition-colors shadow-2xs inline-flex items-center gap-1.5 text-white" style="color:#ffffff !important;">
                <i data-lucide="printer" class="w-3.5 h-3.5 text-white"></i>
                <span style="color:#ffffff !important;">Print Document</span>
            </button>
            <a href="{{ route('admin.setups.document-templates.preview', ['templateKey' => $selectedKey, 'application_id' => $application?->id]) }}" target="_blank" class="px-3.5 py-2 rounded-lg border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-colors shadow-2xs inline-flex items-center gap-1.5">
                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                <span>Fullscreen</span>
            </a>
        </div>
    </div>

    {{-- Metrics Bar --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-600 uppercase tracking-wide">Registered Templates</div>
            <div class="text-2xl font-extrabold text-[#0A3E50] mt-1.5">{{ count($catalogue) }} Documents</div>
            <p class="text-[11px] text-slate-500 mt-0.5">Senate &amp; Registrar Approved</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-600 uppercase tracking-wide">Dynamic Placeholders</div>
            <div class="text-2xl font-extrabold text-blue-700 mt-1.5">{{ count($activeTemplate['placeholders'] ?? []) }} Variables</div>
            <p class="text-[11px] text-slate-500 mt-0.5">Active in {{ $activeTemplate['name'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-600 uppercase tracking-wide">Selected Candidate</div>
            <div class="text-sm font-extrabold text-[#1E8449] mt-2 truncate">{{ $payload['applicant']['name'] }}</div>
            <p class="text-[11px] text-slate-500 mt-0.5 font-mono">{{ $payload['application']['admission_number'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-600 uppercase tracking-wide">Security &amp; QR Audit</div>
            <div class="text-sm font-extrabold text-slate-800 mt-2 font-mono truncate">SHA-256 Validated</div>
            <p class="text-[11px] text-emerald-700 font-bold mt-0.5">&check; Tamper-Proof Cryptographic Token</p>
        </div>
    </div>

    {{-- Main 2-Column Studio Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        {{-- Left Control Sidebar: Template Switcher & Context Selector --}}
        <div class="lg:col-span-4 space-y-5">
            
            {{-- Template Selector Card --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="layers" class="w-4 h-4 text-[#0A3E50]"></i>
                        Document Templates ({{ count($catalogue) }})
                    </h2>
                </div>
                <div class="divide-y divide-slate-100 max-h-[420px] overflow-y-auto">
                    @foreach($catalogue as $key => $tpl)
                    <a href="{{ route('admin.setups.document-templates', ['template' => $key, 'application_id' => $application?->id]) }}" 
                       class="p-3.5 block transition-colors {{ $selectedKey === $key ? 'bg-sky-50/70 border-l-4 border-[#0A3E50]' : 'hover:bg-slate-50' }}">
                        <div class="flex items-start justify-between gap-2">
                            <div class="font-bold text-xs {{ $selectedKey === $key ? 'text-[#0A3E50]' : 'text-slate-800' }}">
                                {{ $tpl['name'] }}
                            </div>
                            <span class="px-1.5 py-0.5 rounded text-[9.5px] font-bold {{ $selectedKey === $key ? 'bg-[#0A3E50] text-white' : 'bg-slate-100 text-slate-600' }}">
                                {{ $tpl['code'] }}
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1 line-clamp-2 leading-relaxed">
                            {{ $tpl['description'] }}
                        </p>
                    </a>
                    @endforeach
                </div>
            </div>

            {{-- Candidate Data Context Selector --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-4">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                    <i data-lucide="user-check" class="w-4 h-4 text-[#1E8449]"></i>
                    Candidate Data Context
                </h3>
                <p class="text-[11.5px] text-slate-500 mb-3">
                    Switch between the standard benchmark candidate or real admitted students from the admissions database.
                </p>
                <form method="GET" action="{{ route('admin.setups.document-templates') }}" class="space-y-3">
                    <input type="hidden" name="template" value="{{ $selectedKey }}">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Select Candidate Context:</label>
                        <select name="application_id" onchange="this.form.submit()" class="w-full text-xs font-medium border border-slate-300 rounded-lg p-2 bg-white text-slate-800 focus:outline-none focus:border-[#0A3E50]">
                            <option value="" {{ empty($application) ? 'selected' : '' }}>
                                &bull; Benchmark Prototype: Ms. Jackline Chebet (BCS/042/2026)
                            </option>
                            @foreach($recentAdmitted as $adm)
                            <option value="{{ $adm->id }}" {{ ($application?->id === $adm->id) ? 'selected' : '' }}>
                                &bull; {{ $adm->applicant?->user?->name ?? $adm->application_number }} &mdash; {{ $adm->offering?->course?->code ?? 'Admitted' }} ({{ $adm->application_number }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

            {{-- Dynamic Placeholders Inspector --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-4">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                    <i data-lucide="code" class="w-4 h-4 text-amber-600"></i>
                    Tokenized Placeholders
                </h3>
                <div class="space-y-1.5 max-h-[220px] overflow-y-auto pr-1">
                    @foreach(($activeTemplate['placeholders'] ?? []) as $ph)
                    <div class="p-2 rounded bg-slate-50 border border-slate-200/80 text-[11px] flex items-center justify-between">
                        <div>
                            <span class="font-mono font-bold text-[#0A3E50]">&#123;&#123; {{ $ph['key'] }} &#125;&#125;</span>
                            <div class="text-[10px] text-slate-500">{{ $ph['label'] }}</div>
                        </div>
                        <span class="text-[10.5px] font-semibold text-slate-700 truncate max-w-[120px]">{{ $ph['example'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- Right Pane: Interactive Live A4 Document Preview --}}
        <div class="lg:col-span-8 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
            
            {{-- Document Canvas Header --}}
            <div class="p-3.5 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2 text-xs">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-[#0A3E50] text-white">A4 SIMULATION</span>
                    <span class="font-bold text-slate-800">{{ $activeTemplate['name'] }}</span>
                    <span class="text-slate-400">&bull;</span>
                    <span class="text-slate-500 font-mono text-[11px]">{{ $activeTemplate['code'] }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="printPreviewFrame()" class="text-[#0A3E50] hover:underline font-bold text-[11px] flex items-center gap-1 cursor-pointer">
                        <i data-lucide="printer" class="w-3.5 h-3.5"></i> Print Clean Copy
                    </button>
                </div>
            </div>

            {{-- Document Preview Frame --}}
            <div class="p-6 sm:p-10 bg-slate-200/60 overflow-x-auto flex justify-center">
                <div class="bg-white border border-slate-300 shadow-xl rounded-sm w-full max-w-[780px] min-h-[960px] p-8 sm:p-12 transition-all">
                    <iframe id="doc-preview-frame" 
                            src="{{ route('admin.setups.document-templates.preview', ['templateKey' => $selectedKey, 'application_id' => $application?->id]) }}" 
                            class="w-full min-h-[900px] border-none"
                            onload="resizeIframe(this)">
                    </iframe>
                </div>
            </div>

        </div>

    </div>
</div>

<script>
    function printPreviewFrame() {
        const frame = document.getElementById('doc-preview-frame');
        if (frame && frame.contentWindow) {
            frame.contentWindow.focus();
            frame.contentWindow.print();
        } else {
            window.print();
        }
    }

    function resizeIframe(obj) {
        try {
            if (obj.contentWindow && obj.contentWindow.document.body) {
                obj.style.height = (obj.contentWindow.document.documentElement.scrollHeight + 40) + 'px';
            }
        } catch (e) {
            console.log('Frame resize error', e);
        }
    }
</script>
@endsection

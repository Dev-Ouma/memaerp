@extends('layouts.app')

@section('title', 'MEMA OpsCenter - System Administration')

@section('content')
<style>
    /* Premium cyber dark theme styling for the OpsCenter */
    .opscenter-container {
        background-color: #070d14 !important;
        color: #cbd5e1 !important;
        font-family: 'Outfit', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        border-radius: 16px;
        padding: 24px;
        box-shadow: inset 0 0 100px rgba(0, 0, 0, 0.8);
        border: 1px solid #142334;
        margin-top: -10px;
    }
    .opscenter-header {
        background: linear-gradient(90deg, #0a111a 0%, #101c2b 100%);
        border: 1px solid #162a3f;
        border-radius: 12px;
        padding: 12px 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.4);
    }
    .opscenter-tab-btn {
        transition: all 0.2s ease-in-out;
        color: #94a3b8;
        border: 1px solid transparent;
    }
    .opscenter-tab-btn.active {
        color: #00f2fe;
        background: rgba(0, 242, 254, 0.08);
        border: 1px solid rgba(0, 242, 254, 0.25);
        text-shadow: 0 0 8px rgba(0, 242, 254, 0.5);
    }
    .opscenter-tab-btn:hover:not(.active) {
        color: #f8fafc;
        background: rgba(255, 255, 255, 0.03);
    }
    .ticker-bar {
        background: #090e15;
        border: 1px solid #112031;
        border-radius: 8px;
        font-family: monospace;
        font-size: 11px;
    }
    .opscenter-card {
        background: #0a121c !important;
        border: 1px solid #16273b !important;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        border-radius: 10px;
        transition: all 0.25s ease;
    }
    .opscenter-card:hover {
        border-color: #1f3d5c !important;
        box-shadow: 0 6px 20px rgba(0,242,254,0.06);
    }
    .text-cyan-glow {
        color: #00f2fe;
        text-shadow: 0 0 6px rgba(0, 242, 254, 0.4);
    }
    .text-green-glow {
        color: #10b981;
        text-shadow: 0 0 6px rgba(16, 185, 129, 0.4);
    }
    .text-yellow-glow {
        color: #fbbf24;
        text-shadow: 0 0 6px rgba(251, 191, 36, 0.4);
    }
    .text-red-glow {
        color: #ef4444;
        text-shadow: 0 0 6px rgba(239, 68, 68, 0.4);
    }
    .gauge-circle {
        transform: rotate(-90deg);
        transform-origin: 50% 50%;
    }
    .console-block {
        background: #05090f !important;
        border: 1px solid #101c2b !important;
        font-family: 'JetBrains Mono', 'Fira Code', 'Courier New', monospace;
        font-size: 11px;
        color: #38bdf8;
    }
    .console-block::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    .console-block::-webkit-scrollbar-thumb {
        background: #142334;
        border-radius: 3px;
    }
    .override-btn {
        background: linear-gradient(135deg, #101c2b 0%, #0d1622 100%);
        border: 1px solid #1b2f47;
        transition: all 0.2s;
    }
    .override-btn:hover {
        border-color: #00f2fe;
        background: rgba(0, 242, 254, 0.05);
        color: #ffffff;
    }
    /* Hide standard page elements for true fullscreen dashboard feel if needed */
    .mema-dashboard-container {
        padding: 0 !important;
    }
</style>

<div class="opscenter-container">
    
    {{-- OpsCenter Header Bar --}}
    <header class="opscenter-header flex flex-col md:flex-row justify-between items-center gap-4 mb-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#00f2fe] to-[#4facfe] flex items-center justify-center text-slate-900 shadow-md">
                <i data-lucide="terminal" class="w-5.5 h-5.5 text-slate-950 font-bold"></i>
            </div>
            <div>
                <h1 class="text-lg font-extrabold text-white tracking-wide uppercase flex items-center gap-1.5">
                    MEMA <span class="text-[#00f2fe]">OpsCenter</span>
                </h1>
                <p class="text-[10px] text-slate-400 font-mono tracking-widest uppercase">System Continuous Monitoring &amp; Infrastructure Operations</p>
            </div>
        </div>
        
        {{-- Navigation Menu Tabs --}}
        <div class="flex items-center gap-1 bg-[#05090f] p-1 rounded-lg border border-[#142334]">
            <button onclick="switchOpsTab('overview')" id="tab-btn-overview" class="opscenter-tab-btn active px-3.5 py-1.5 rounded-md text-xs font-bold flex items-center gap-1.5">
                <i data-lucide="activity" class="w-3.5 h-3.5"></i> Overview
            </button>
            <button onclick="switchOpsTab('lockdown')" id="tab-btn-lockdown" class="opscenter-tab-btn px-3.5 py-1.5 rounded-md text-xs font-bold flex items-center gap-1.5">
                <i data-lucide="shield-alert" class="w-3.5 h-3.5"></i> System Lockdown
            </button>
            <button onclick="switchOpsTab('backups')" id="tab-btn-backups" class="opscenter-tab-btn px-3.5 py-1.5 rounded-md text-xs font-bold flex items-center gap-1.5">
                <i data-lucide="archive" class="w-3.5 h-3.5"></i> Backups
            </button>
            <button onclick="switchOpsTab('upgrades')" id="tab-btn-upgrades" class="opscenter-tab-btn px-3.5 py-1.5 rounded-md text-xs font-bold flex items-center gap-1.5">
                <i data-lucide="git-merge" class="w-3.5 h-3.5"></i> Upgrades &amp; Migrations
            </button>
            <button onclick="switchOpsTab('specs')" id="tab-btn-specs" class="opscenter-tab-btn px-3.5 py-1.5 rounded-md text-xs font-bold flex items-center gap-1.5">
                <i data-lucide="cpu" class="w-3.5 h-3.5"></i> System Specs
            </button>
            <button onclick="switchOpsTab('logs')" id="tab-btn-logs" class="opscenter-tab-btn px-3.5 py-1.5 rounded-md text-xs font-bold flex items-center gap-1.5">
                <i data-lucide="file-text" class="w-3.5 h-3.5"></i> Activity Logs
            </button>
        </div>

        {{-- Meta Controls & User Pill --}}
        <div class="flex items-center gap-2.5">
            <span class="text-xs font-mono bg-slate-900 border border-slate-800 px-2.5 py-1 rounded text-slate-400" id="live-clock">15:21:33</span>
            <button onclick="window.location.reload()" class="p-1.5 bg-[#05090f] border border-[#142334] rounded text-slate-400 hover:text-white transition-colors">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
            </button>
            <div class="flex items-center gap-2 bg-[#0d1e15] border border-emerald-800/60 px-3 py-1 rounded-lg">
                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-ping"></span>
                <span class="text-[10px] font-mono font-bold text-emerald-400 uppercase">SUPER ADMIN</span>
            </div>
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="px-3 py-1 rounded bg-rose-950 border border-rose-800 hover:bg-rose-900 text-rose-300 hover:text-white font-bold text-[10px] uppercase transition-all">
                Logout
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
        </div>
    </header>

    {{-- Live Alerts Marquee Ticker --}}
    <div class="ticker-bar px-4 py-2 mb-4 flex items-center gap-4 overflow-hidden text-slate-300">
        <div class="flex items-center gap-1.5 shrink-0">
            <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
            <strong class="uppercase text-[10px] text-rose-400">System Bulletins:</strong>
        </div>
        <div class="marquee-content flex gap-8 whitespace-nowrap text-[11.5px] font-mono">
            @forelse($broadcasts as $broadcast)
                <span class="text-sky-400">● {{ $broadcast->message }}</span>
            @empty
                <span class="text-slate-400">● No operator broadcasts. System continuous monitoring active.</span>
            @endforelse
        </div>
    </div>

    {{-- ------------------- PANEL 1: OVERVIEW ------------------- --}}
    <div id="panel-overview" class="tab-panel space-y-4">
        
        {{-- KPI Metrics Grid --}}
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-3.5">
            
            <div class="opscenter-card p-3">
                <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest block">Primary Storage</span>
                <strong class="text-lg font-bold text-white block mt-1.5">{{ $health['disk_used'] }}</strong>
                <small class="text-[10px] text-slate-400 block mt-0.5">of {{ $health['disk_total'] }} ({{ $health['disk_percentage'] }}%)</small>
            </div>

            <div class="opscenter-card p-3">
                <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest block">System Uptime</span>
                <strong class="text-lg font-bold text-emerald-400 block mt-1.5">{{ $health['uptime'] }}</strong>
                <small class="text-[10px] text-emerald-500/80 block mt-0.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> {{ strtoupper($health['status']) }}
                </small>
            </div>

            <div class="opscenter-card p-3">
                <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest block">System Load</span>
                <strong class="text-lg font-bold {{ $health['cpu_percentage'] >= 85 ? 'text-rose-400' : 'text-emerald-400' }} block mt-1.5">{{ $health['cpu_load'] }}</strong>
                <small class="text-[10px] text-slate-400 block mt-0.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full {{ $health['cpu_percentage'] >= 85 ? 'bg-rose-500' : 'bg-emerald-500' }}"></span> {{ $health['cpu_percentage'] }}% of {{ $health['cpu_cores'] }} cores
                </small>
            </div>

            <div class="opscenter-card p-3">
                <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest block">Current Version</span>
                <strong class="text-lg font-bold text-sky-400 block mt-1.5">{{ $currentVersion?->version ?? 'n/a' }}</strong>
                <small class="text-[10px] text-sky-500/80 block mt-0.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span> {{ $currentVersion?->type ?? 'unversioned' }}
                </small>
            </div>

            <div class="opscenter-card p-3">
                <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest block">Database</span>
                <strong class="text-lg font-bold {{ $health['db_healthy'] ? 'text-emerald-400' : 'text-rose-400' }} block mt-1.5">{{ $health['db_healthy'] ? 'HEALTHY' : 'DOWN' }}</strong>
                <small class="text-[10px] text-emerald-500/80 block mt-0.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full {{ $health['db_healthy'] ? 'bg-emerald-500' : 'bg-rose-500' }}"></span> {{ $health['db_status'] }}
                </small>
            </div>

            <div class="opscenter-card p-3">
                <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest block">Active Users</span>
                <strong class="text-lg font-bold text-white block mt-1.5">{{ $health['active_users'] }}</strong>
                <small class="text-[10px] text-slate-400 block mt-0.5">sessions last 15m</small>
            </div>

            <div class="opscenter-card p-3">
                <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest block">Cron Status</span>
                <strong class="text-lg font-bold text-emerald-400 block mt-1.5">{{ $health['last_cron_at'] ? 'RECORDED' : 'IDLE' }}</strong>
                <small class="text-[10px] text-slate-400 block mt-0.5">{{ $health['last_cron_at'] ? \Carbon\Carbon::parse($health['last_cron_at'])->diffForHumans() : 'No heartbeat yet' }}</small>
            </div>

            <div class="opscenter-card p-3">
                <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest block">Process RAM</span>
                <strong class="text-lg font-bold text-white block mt-1.5">{{ $health['ram_used'] }} MB</strong>
                <small class="text-[10px] text-slate-400 block mt-0.5">of {{ $health['ram_limit'] }} MB ({{ $health['ram_percentage'] }}%)</small>
            </div>
        </div>

        {{-- Mid-section (Health, Alerts, Overrides) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
            
            {{-- Circular Health score --}}
            <div class="lg:col-span-3 opscenter-card p-4 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-bold uppercase text-slate-400">Health Score</span>
                        <span class="px-2 py-0.5 {{ $health['status'] === 'healthy' ? 'bg-emerald-950 text-emerald-400 border-emerald-800' : ($health['status'] === 'degraded' ? 'bg-amber-950 text-amber-400 border-amber-800' : 'bg-rose-950 text-rose-400 border-rose-800') }} text-[9px] rounded font-bold uppercase border">{{ strtoupper($health['status']) }}</span>
                    </div>
                    
                    {{-- Circular SVG Gauge --}}
                    <div class="relative w-36 h-36 mx-auto mt-4 flex items-center justify-center">
                        <svg class="w-full h-full" viewBox="0 0 100 100">
                            <circle class="text-slate-800" stroke-width="8" stroke="currentColor" fill="transparent" r="38" cx="50" cy="50"/>
                            <circle class="{{ $health['health_score'] >= 70 ? 'text-emerald-500' : 'text-amber-500' }}" stroke-width="8" stroke-dasharray="238" stroke-dashoffset="{{ (int) round(238 - (238 * $health['health_score'] / 100)) }}" stroke-linecap="round" stroke="currentColor" fill="transparent" r="38" cx="50" cy="50" class="gauge-circle" style="transform: rotate(-90deg); transform-origin: 50px 50px;"/>
                        </svg>
                        <div class="absolute text-center">
                            <span class="text-4xl font-extrabold text-white leading-none">{{ $health['health_score'] }}</span>
                        </div>
                    </div>

                    <div class="flex justify-center gap-4 text-[10px] mt-4 font-semibold text-slate-400">
                        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Healthy</span>
                        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-rose-500"></span> Degraded</span>
                    </div>
                </div>

                <div class="border-t border-slate-800 mt-4 pt-3.5 space-y-2">
                    <div class="text-[10px] {{ $health['status'] === 'healthy' ? 'text-emerald-400' : 'text-amber-400' }} font-bold uppercase tracking-wider">{{ $health['status'] === 'healthy' ? 'No immediate mitigation required.' : 'Review load, disk and RAM gauges.' }}</div>
                    
                    <div class="space-y-1.5 text-[11px]">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Storage</span>
                            <span class="font-bold text-white">{{ $health['disk_percentage'] }}%</span>
                        </div>
                        <div class="w-full bg-slate-800 h-1 rounded-full overflow-hidden">
                            <div class="bg-cyan-400 h-full" style="width: {{ $health['disk_percentage'] }}%"></div>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-slate-400">CPU</span>
                            <span class="font-bold text-white">{{ $health['cpu_percentage'] }}%</span>
                        </div>
                        <div class="w-full bg-slate-800 h-1 rounded-full overflow-hidden">
                            <div class="bg-emerald-500 h-full" style="width: {{ $health['cpu_percentage'] }}%"></div>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-slate-400">RAM</span>
                            <span class="font-bold text-white">{{ $health['ram_percentage'] }}%</span>
                        </div>
                        <div class="w-full bg-slate-800 h-1 rounded-full overflow-hidden">
                            <div class="bg-sky-500 h-full" style="width: {{ $health['ram_percentage'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Center: Active Alerts List --}}
            <div class="lg:col-span-6 opscenter-card p-4 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center border-b border-slate-800 pb-2">
                        <span class="text-xs font-bold uppercase text-slate-400">Active Alerts</span>
                        <span class="px-2 py-0.5 bg-slate-900 text-slate-300 text-[9px] rounded font-bold uppercase border border-slate-700">{{ $broadcasts->count() }} BROADCASTS</span>
                    </div>

                    <div class="mt-4 space-y-3">
                        @forelse($broadcasts as $broadcast)
                            <div class="p-3 bg-[#0d121b] border-l-4 border-sky-500 rounded-r-lg">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
                                    <strong class="text-xs font-bold text-white">Operator broadcast</strong>
                                </div>
                                <p class="text-[11px] text-slate-400 mt-1">{{ $broadcast->message }}</p>
                                <small class="text-[9px] text-sky-400 font-mono block mt-1 uppercase font-bold">{{ $broadcast->created_at?->diffForHumans() }}</small>
                            </div>
                        @empty
                            <div class="p-3 bg-[#0d121b] border-l-4 border-emerald-500 rounded-r-lg">
                                <strong class="text-xs font-bold text-white">No active operator alerts</strong>
                                <p class="text-[11px] text-slate-400 mt-1">Database {{ $health['db_status'] }}. Load {{ $health['cpu_load'] }} on {{ $health['cpu_cores'] }} cores.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="text-[10.5px] text-slate-500 font-mono border-t border-slate-800 pt-3 mt-4 text-right">
                    Telemetry nodes synced: {{ now()->toIso8601String() }}
                </div>
            </div>

            {{-- Right: Manual Override Action Buttons --}}
            <div class="lg:col-span-3 opscenter-card p-4 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center border-b border-slate-800 pb-2">
                        <span class="text-xs font-bold uppercase text-slate-400">Manual Override</span>
                        <span class="text-[10px] text-emerald-400 font-bold uppercase tracking-wider flex items-center gap-1">
                            <i data-lucide="unlock" class="w-3 h-3"></i> ACCESS UNLOCKED
                        </span>
                    </div>

                    <div class="mt-4 space-y-2">
                        <button type="button" onclick="postOps('{{ route('admin.setups.system-maintenance.cloud-mirror') }}')" class="override-btn w-full p-2.5 rounded-lg text-left flex items-start gap-2.5">
                            <i data-lucide="cloud" class="w-4 h-4 text-sky-400 shrink-0 mt-0.5"></i>
                            <div>
                                <strong class="text-xs font-bold text-slate-200 block">Cloud Mirror (Moodledata)</strong>
                                <small class="text-[9.5px] text-slate-400 block font-mono">rclone status check</small>
                            </div>
                        </button>

                        <form method="POST" action="{{ route('admin.setups.system-maintenance.backup.create') }}">
                            @csrf
                            <button type="submit" class="override-btn w-full p-2.5 rounded-lg text-left flex items-start gap-2.5">
                                <i data-lucide="hard-drive" class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5"></i>
                                <div>
                                    <strong class="text-xs font-bold text-slate-200 block">Dump Database</strong>
                                    <small class="text-[9.5px] text-slate-400 block font-mono">pg_dump -> /backups/</small>
                                </div>
                            </button>
                        </form>

                        <button type="button" onclick="postOps('{{ route('admin.setups.system-maintenance.codebase.sync') }}')" class="override-btn w-full p-2.5 rounded-lg text-left flex items-start gap-2.5">
                            <i data-lucide="git-branch" class="w-4 h-4 text-amber-400 shrink-0 mt-0.5"></i>
                            <div>
                                <strong class="text-xs font-bold text-slate-200 block">Sync Codebase</strong>
                                <small class="text-[9.5px] text-slate-400 block font-mono">git status / HEAD</small>
                            </div>
                        </button>

                        <button onclick="runCronJob()" class="override-btn w-full p-2.5 rounded-lg text-left flex items-start gap-2.5">
                            <i data-lucide="play" class="w-4 h-4 text-purple-400 shrink-0 mt-0.5"></i>
                            <div>
                                <strong class="text-xs font-bold text-slate-200 block">Force Cron Run</strong>
                                <small class="text-[9.5px] text-slate-400 block font-mono">php admin/cli/cron.php</small>
                            </div>
                        </button>

                        <button onclick="clearCache('all')" class="override-btn w-full p-2.5 rounded-lg text-left flex items-start gap-2.5">
                            <i data-lucide="trash-2" class="w-4 h-4 text-rose-400 shrink-0 mt-0.5"></i>
                            <div>
                                <strong class="text-xs font-bold text-slate-200 block">Purge Caches</strong>
                                <small class="text-[9.5px] text-slate-400 block font-mono">CLI purge_caches.php</small>
                            </div>
                        </button>
                    </div>
                </div>

                <div class="text-[10px] text-slate-500 font-mono text-center mt-4">
                    Authorized actions are cryptographically signed.
                </div>
            </div>

        </div>

        {{-- Bottom Section (Console Upload Stream + Recent Activity Logs) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
            
            {{-- Console Stream --}}
            <div class="lg:col-span-7 opscenter-card p-4">
                <div class="flex justify-between items-center border-b border-slate-800 pb-2.5 mb-3">
                    <span class="text-xs font-bold uppercase text-slate-400">Ops Console</span>
                    <span class="px-2 py-0.5 bg-sky-950 text-sky-400 text-[9px] rounded font-bold uppercase border border-sky-800 flex items-center gap-1">
                        LIVE
                    </span>
                </div>
                
                <div class="text-xs text-sky-400 font-bold mb-2">Command output</div>
                <pre class="console-block p-4 rounded-lg overflow-y-auto max-h-56 leading-relaxed shadow-inner" id="ops-console">{{ $consoleLog }}</pre>
            </div>

            {{-- Recent Activity Logs --}}
            <div class="lg:col-span-5 opscenter-card p-4">
                <div class="flex justify-between items-center border-b border-slate-800 pb-2.5 mb-3">
                    <span class="text-xs font-bold uppercase text-slate-400">Recent Activity Logs</span>
                    <span class="text-[10px] text-slate-500 font-mono uppercase tracking-wider">Live Feed</span>
                </div>

                <div class="border border-slate-800 rounded-lg overflow-hidden max-h-64 overflow-y-auto">
                    <table class="w-full text-left text-[11px] font-mono border-collapse">
                        <thead>
                            <tr class="bg-slate-900 text-slate-400 border-b border-slate-800">
                                <th class="py-2 px-3 font-bold">Time</th>
                                <th class="py-2 px-3 font-bold">Status</th>
                                <th class="py-2 px-3 font-bold">Event</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @forelse($recentLogs as $log)
                                <tr class="hover:bg-slate-900/30 transition-colors">
                                    <td class="py-2 px-3 text-slate-400">{{ \Carbon\Carbon::parse($log->occurred_at)->format('H:i:s') }}</td>
                                    <td class="py-2 px-3">
                                        <span class="inline-block px-1.5 py-0.2 bg-sky-950 text-sky-400 border border-sky-900 rounded text-[9px] font-bold uppercase">EVENT</span>
                                    </td>
                                    <td class="py-2 px-3 text-slate-300 font-mono text-[10.5px] truncate max-w-[200px]" title="{{ $log->action }}">{{ str_replace('system.maintenance.', '', $log->action) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-8 text-center text-slate-500">No audit events yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>

    {{-- ------------------- PANEL 2: SYSTEM LOCKDOWN ------------------- --}}
    <div id="panel-lockdown" class="tab-panel hidden space-y-4">
        
        <div class="opscenter-card p-6">
            <h3 class="text-sm font-bold text-white border-b border-slate-800 pb-3 flex items-center gap-1.5">
                <i data-lucide="shield-alert" class="w-4.5 h-4.5 text-orange-500"></i> Portal Lockdown Mode Configuration
            </h3>
            
            <form method="POST" action="{{ route('admin.setups.system-maintenance.lockdown.update') }}" class="mt-5 space-y-5">
                @csrf
                
                {{-- Master Switch --}}
                <div class="flex items-center justify-between p-4 bg-[#05090f] border border-[#142334] rounded-lg">
                    <div>
                        <label class="font-bold text-xs text-white block">Active Infrastructure Lockdown Mode</label>
                        <span class="text-[11px] text-slate-400 mt-0.5 block">Restrict the entire portal to offline downtime screens or read-only states.</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_lockdown" value="1" {{ $config->is_lockdown ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-10 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-slate-400 after:border-slate-300 after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-orange-500 peer-checked:after:bg-white"></div>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Lockdown Scope / Severity</label>
                        <select name="lockdown_type" class="w-full border border-[#142334] bg-[#05090f] text-slate-200 rounded-lg p-2.5 text-xs focus:outline-none focus:border-[#00f2fe]">
                            <option value="read_only" {{ $config->lockdown_type === 'read_only' ? 'selected' : '' }}>Read Only (GET Requests Enabled)</option>
                            <option value="offline" {{ $config->lockdown_type === 'offline' ? 'selected' : '' }}>Offline (Full Downtime Landing Page)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Bypass Whitelist IPs (Comma-separated)</label>
                        <input type="text" name="ip_whitelist" value="{{ $config->ip_whitelist }}" placeholder="e.g. 127.0.0.1, 10.0.0.50" class="w-full border border-[#142334] bg-[#05090f] text-slate-200 rounded-lg p-2.5 text-xs focus:outline-none focus:border-[#00f2fe]">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Maintenance Banner Announcement Message</label>
                    <textarea name="maintenance_message" rows="3" class="w-full border border-[#142334] bg-[#05090f] text-slate-200 rounded-lg p-2.5 text-xs focus:outline-none focus:border-[#00f2fe]">{{ $config->maintenance_message }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Scheduled Downtime Start</label>
                        <input type="datetime-local" name="scheduled_start" value="{{ $config->scheduled_start ? $config->scheduled_start->format('Y-m-d\TH:i') : '' }}" class="w-full border border-[#142334] bg-[#05090f] text-slate-200 rounded-lg p-2.5 text-xs focus:outline-none focus:border-[#00f2fe]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Scheduled Downtime End</label>
                        <input type="datetime-local" name="scheduled_end" value="{{ $config->scheduled_end ? $config->scheduled_end->format('Y-m-d\TH:i') : '' }}" class="w-full border border-[#142334] bg-[#05090f] text-slate-200 rounded-lg p-2.5 text-xs focus:outline-none focus:border-[#00f2fe]">
                    </div>
                </div>

                {{-- Granular Module Lockdown --}}
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-2">Granular Module Locking (Select specific modules to suspend)</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 bg-[#05090f] border border-[#142334] p-4 rounded-lg max-h-48 overflow-y-auto">
                        @foreach($modules as $key => $name)
                            <label class="flex items-center gap-2.5 text-[11px] font-semibold text-slate-300 hover:text-white cursor-pointer">
                                <input type="checkbox" name="locked_modules[]" value="{{ $key }}" {{ is_array($config->locked_modules) && in_array($key, $config->locked_modules, true) ? 'checked' : '' }} class="rounded border-slate-800 bg-slate-900 text-orange-500 focus:ring-0">
                                <span>{{ $name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end pt-2 border-t border-slate-800">
                    <button type="submit" class="px-5 py-2.5 rounded-lg bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs transition-colors flex items-center gap-1.5" style="background:#E67E22 !important; color:#ffffff !important;">
                        <i data-lucide="save" class="w-4 h-4"></i> Save System Lockdown Scope
                    </button>
                </div>
            </form>

            <form id="broadcast-form" class="mt-6 space-y-3 border-t border-slate-800 pt-5">
                <label class="block text-xs font-bold text-slate-300">Broadcast to signed-in sessions</label>
                <div class="flex gap-2">
                    <input type="text" name="message" minlength="5" maxlength="255" required placeholder="Maintenance window starts at 18:00..." class="flex-1 border border-[#142334] bg-[#05090f] text-slate-200 rounded-lg p-2.5 text-xs">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-sky-700 text-white text-xs font-bold">Send</button>
                </div>
            </form>
        </div>

    </div>

    {{-- ------------------- PANEL 3: BACKUPS ------------------- --}}
    <div id="panel-backups" class="tab-panel hidden space-y-4">
        
        <div class="opscenter-card p-6">
            <div class="flex justify-between items-center border-b border-slate-800 pb-3 mb-4">
                <h3 class="text-sm font-bold text-white flex items-center gap-1.5">
                    <i data-lucide="archive" class="w-4.5 h-4.5 text-[#00f2fe]"></i> Relational Database Backup Logs
                </h3>
                <form method="POST" action="{{ route('admin.setups.system-maintenance.backup.create') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg transition-colors flex items-center gap-1.5" style="background:#10b981 !important; color:#ffffff !important;">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i> Trigger New DB Dump
                    </button>
                </form>
            </div>

            <div class="border border-slate-800 rounded-lg overflow-hidden">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-900/60 text-slate-400 border-b border-slate-800">
                            <th class="py-2.5 px-4 font-bold">Filename</th>
                            <th class="py-2.5 px-4 font-bold">Target size</th>
                            <th class="py-2.5 px-4 font-bold">Generated At</th>
                            <th class="py-2.5 px-4 text-center font-bold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse($backups as $backup)
                            <tr class="hover:bg-slate-900/20 transition-colors">
                                <td class="py-3 px-4 font-bold text-slate-200">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="file-database" class="w-4 h-4 text-slate-500"></i>
                                        {{ $backup->filename }}
                                    </div>
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-400">{{ number_format($backup->file_size / 1024 / 1024, 2) }} MB</td>
                                <td class="py-3 px-4 text-slate-400 font-mono">{{ $backup->created_at->format('d M Y, h:i A') }}</td>
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.setups.system-maintenance.backup.download', $backup) }}" target="_blank" class="px-2 py-1 border border-slate-700 rounded bg-[#05090f] hover:bg-slate-900 text-slate-300">
                                            Download
                                        </a>
                                        <button type="button" onclick="confirmRestoreBackup('{{ $backup->id }}', '{{ $backup->filename }}')" class="px-2.5 py-1 bg-emerald-950 text-emerald-400 border border-emerald-800 rounded text-xs font-bold">
                                            Restore
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-12 text-center text-slate-500">
                                    <i data-lucide="folder" class="w-8 h-8 mx-auto mb-2 text-slate-600"></i>
                                    <div>No SQL backups cataloged in this repository.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($backups->hasPages())
                <div class="mt-4">
                    {{ $backups->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- ------------------- PANEL: UPGRADES & VERSIONS ------------------- --}}
    <div id="panel-upgrades" class="tab-panel hidden space-y-4">
        <div class="opscenter-card p-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-slate-800 pb-4 mb-5 gap-3">
                <div>
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <i data-lucide="git-merge" class="w-4.5 h-4.5 text-[#00f2fe]"></i> System Upgrades &amp; Release Management
                    </h3>
                    <p class="text-xs text-slate-400 mt-1">Execute live database migrations (`php artisan migrate --force`), flush cache tiers, and register version release manifests.</p>
                </div>
                <button type="button" onclick="executeUpgrade()" id="btn-upgrade" class="px-4 py-2 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-bold text-xs rounded-lg transition-all shadow-md flex items-center gap-1.5" style="background: linear-gradient(to right, #E67E22, #d35400) !important; color:#ffffff !important;">
                    <i data-lucide="zap" class="w-4 h-4"></i> Run Live Platform Upgrade
                </button>
            </div>

            {{-- Upgrade Console / Output Stream --}}
            <div id="upgrade-output-box" class="hidden mb-5 p-4 rounded-lg bg-[#05090f] border border-cyan-500/40">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-cyan-400 font-mono flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-cyan-400 animate-ping"></span> Upgrade Execution Log
                    </span>
                    <span class="text-[10px] text-slate-400 font-mono" id="upgrade-status-text">Migrating...</span>
                </div>
                <pre class="text-[11px] font-mono text-slate-200 overflow-x-auto whitespace-pre-wrap max-h-40" id="upgrade-output-content"></pre>
            </div>

            {{-- Version History Table --}}
            <div class="border border-slate-800 rounded-lg overflow-hidden">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-900/60 text-slate-400 border-b border-slate-800">
                            <th class="py-2.5 px-4 font-bold">Release Version</th>
                            <th class="py-2.5 px-4 font-bold">Release Type</th>
                            <th class="py-2.5 px-4 font-bold">Changelog / Migration Details</th>
                            <th class="py-2.5 px-4 font-bold">Installed At</th>
                            <th class="py-2.5 px-4 text-center font-bold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60" id="versions-tbody">
                        @forelse($versions as $ver)
                            <tr class="hover:bg-slate-900/20 transition-colors">
                                <td class="py-3 px-4 font-bold text-slate-200">
                                    <span class="font-mono text-cyan-400">{{ $ver->version }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-800 text-slate-300 border border-slate-700">
                                        {{ $ver->type ?? 'RELEASE' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-300 text-[11px] max-w-md">
                                    {{ $ver->changelog ?? 'Standard continuous release deployment.' }}
                                </td>
                                <td class="py-3 px-4 text-slate-400 font-mono text-[11px]">
                                    {{ $ver->installed_at ? $ver->installed_at->format('d M Y, h:i A') : 'Initial' }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($ver->rolled_back_at)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-950 text-rose-400 border border-rose-800">ROLLED BACK</span>
                                    @else
                                        <div class="flex flex-col items-center gap-1">
                                            @if($ver->is_current)
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-950 text-emerald-400 border border-emerald-800">CURRENT</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-300 border border-slate-700">RECORDED</span>
                                            @endif
                                            <form method="POST" action="{{ route('admin.setups.system-maintenance.version.rollback', $ver) }}">
                                                @csrf
                                                <button type="submit" class="text-[10px] text-amber-400 hover:text-amber-200">Rollback pointer</button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-500">
                                    No recorded version releases. Click "Run Live Platform Upgrade" to execute migrations and register a release.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ------------------- PANEL 4: SPECS ------------------- --}}
    <div id="panel-specs" class="tab-panel hidden space-y-4">
        
        <div class="opscenter-card p-6">
            <h3 class="text-sm font-bold text-white border-b border-slate-800 pb-3 mb-4 flex items-center gap-1.5">
                <i data-lucide="info" class="w-4.5 h-4.5 text-[#00f2fe]"></i> Technical Core Infrastructure Specifications
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                
                <div class="bg-[#05090f] border border-[#142334] p-4 rounded-lg">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block">PHP Runtime Environment</span>
                    <strong class="text-sm text-slate-200 mt-1 block font-mono">{{ $specs['php_version'] }}</strong>
                </div>

                <div class="bg-[#05090f] border border-[#142334] p-4 rounded-lg">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block">Laravel Framework Version</span>
                    <strong class="text-sm text-slate-200 mt-1 block font-mono">{{ $specs['laravel_version'] }}</strong>
                </div>

                <div class="bg-[#05090f] border border-[#142334] p-4 rounded-lg">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block">Operating System Platform</span>
                    <strong class="text-sm text-slate-200 mt-1 block font-mono">{{ $specs['os_version'] }}</strong>
                </div>

                <div class="bg-[#05090f] border border-[#142334] p-4 rounded-lg">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block">Database SQL Driver</span>
                    <strong class="text-sm text-slate-200 mt-1 block font-mono">{{ strtoupper($specs['database_type']) }}</strong>
                </div>

                <div class="bg-[#05090f] border border-[#142334] p-4 rounded-lg">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block">Server Software Gateway</span>
                    <strong class="text-sm text-slate-200 mt-1 block font-mono">{{ $specs['server_software'] }}</strong>
                </div>

                <div class="bg-[#05090f] border border-[#142334] p-4 rounded-lg">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block">PHP Memory Limit</span>
                    <strong class="text-sm text-slate-200 mt-1 block font-mono">{{ $specs['memory_limit'] }}</strong>
                </div>

            </div>
        </div>

    </div>

    {{-- ------------------- PANEL 5: ACTIVITY LOGS ------------------- --}}
    <div id="panel-logs" class="tab-panel hidden space-y-4">
        
        <div class="opscenter-card p-6">
            <h3 class="text-sm font-bold text-white border-b border-slate-800 pb-3 mb-4 flex items-center gap-1.5">
                <i data-lucide="file-text" class="w-4.5 h-4.5 text-[#00f2fe]"></i> System Activity Log & Audit Trail
            </h3>

            <div class="border border-slate-800 rounded-lg overflow-hidden">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-900/60 text-slate-400 border-b border-slate-800">
                            <th class="py-2.5 px-4 font-bold">Occurred At</th>
                            <th class="py-2.5 px-4 font-bold">Action Event</th>
                            <th class="py-2.5 px-4 font-bold">Actor User ID</th>
                            <th class="py-2.5 px-4 font-bold">Role context</th>
                            <th class="py-2.5 px-4 font-bold">Subject Record</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-mono text-[11px]">
                        @forelse($recentLogs as $log)
                            <tr class="hover:bg-slate-900/20 transition-colors">
                                <td class="py-2.5 px-4 text-slate-400">{{ $log->occurred_at }}</td>
                                <td class="py-2.5 px-4 text-emerald-400 font-bold">{{ $log->action }}</td>
                                <td class="py-2.5 px-4 text-slate-300">User ID: {{ $log->actor_user_id }}</td>
                                <td class="py-2.5 px-4 text-sky-400 font-bold">{{ $log->actor_role }}</td>
                                <td class="py-2.5 px-4 text-slate-400 truncate max-w-[200px]" title="{{ $log->subject_type }}">{{ basename(str_replace('\\', '/', $log->subject_type)) }} #{{ $log->subject_id }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-slate-500 font-sans">
                                    <i data-lucide="info" class="w-8 h-8 mx-auto mb-2 text-slate-600"></i>
                                    <div>No audit logs found.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>

{{-- MODAL 1: RESTORE BACKUP WARNING --}}
<div class="modal" id="restore-backup-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(460px, 94vw); background:#0c131c; border:1px solid #dc2626; color:#f1f5f9;">
        <div class="panel-head" style="background:#dc2626;color:#fff;padding:14px 20px;border-radius:10px 10px 0 0;">
            <div>
                <h2 class="text-sm font-bold text-white">Restore Database State</h2>
                <small style="color:rgba(255,255,255,0.85);">Warning: Overwrites existing active schema structures.</small>
            </div>
            <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
        </div>
        <div class="panel-body p-5">
            <p class="text-xs text-slate-300 mb-2">
                Are you sure you want to restore the system database from the backup file <strong id="restore-filename-display" class="text-white"></strong>?
            </p>
            <p class="text-[11px] text-red-400 bg-red-950/60 p-2.5 rounded-lg border border-red-800/80">
                <i data-lucide="alert-triangle" class="w-3.5 h-3.5 inline mr-1 text-red-500"></i> This operation will take the ERP offline and overwrite all current database tables with the backup snapshots.
            </p>
            
            <div class="mt-4">
                <div class="w-full bg-slate-900 rounded-full h-2.5 hidden" id="restore-progress-container">
                    <div class="bg-emerald-500 h-2.5 rounded-full transition-all duration-300" id="restore-progress-bar" style="width: 0%"></div>
                </div>
                <div class="text-[10px] font-mono font-bold text-emerald-400 mt-1.5 hidden text-center" id="restore-progress-text">Restoring table rows...</div>
            </div>

            <div class="flex justify-end gap-2 mt-5 pt-3 border-t border-slate-800" id="restore-modal-actions">
                <button type="button" class="px-3 py-1.5 rounded bg-slate-850 hover:bg-slate-800 text-slate-300 text-xs font-semibold" data-modal-close>Cancel</button>
                <button type="button" onclick="executeRestore()" class="px-3.5 py-1.5 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-lg transition-colors flex items-center gap-1.5">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i> Yes, Restore Backup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const cacheClearUrl = "{{ route('admin.setups.system-maintenance.cache.clear') }}";
    const cronUrl = "{{ route('admin.setups.system-maintenance.cron') }}";
    const upgradeUrl = "{{ route('admin.setups.system-maintenance.upgrade') }}";
    const restoreUrlTemplate = @json(url('/admin-setups/system-maintenance/backup/__ID__/restore'));
    const broadcastUrl = "{{ route('admin.setups.system-maintenance.broadcast') }}";
    let restoreBackupId = null;

    function jsonHeaders() {
        return {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        };
    }

    function postOps(url, payload = {}) {
        fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: jsonHeaders(),
            body: JSON.stringify(payload),
        })
        .then(async (r) => {
            const data = await r.json();
            alert(data.message || (r.ok ? 'Done' : 'Request failed'));
            const consoleEl = document.getElementById('ops-console');
            if (consoleEl && data.message) {
                consoleEl.textContent = new Date().toISOString() + '  ' + data.message + '\n' + consoleEl.textContent;
            }
        })
        .catch((err) => alert(err.message));
    }

    function switchOpsTab(tabId) {
        document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.add('hidden'));
        const activePanel = document.getElementById(`panel-${tabId}`);
        if (activePanel) activePanel.classList.remove('hidden');

        document.querySelectorAll('.opscenter-tab-btn').forEach(btn => btn.classList.remove('active'));
        const activeBtn = document.getElementById(`tab-btn-${tabId}`);
        if (activeBtn) activeBtn.classList.add('active');
    }

    setInterval(() => {
        const clock = document.getElementById('live-clock');
        if (clock) {
            clock.textContent = new Date().toTimeString().split(' ')[0];
        }
    }, 1000);

    function runCronJob() {
        postOps(cronUrl);
    }

    function clearCache(target = 'all') {
        postOps(cacheClearUrl, { target });
    }

    function executeUpgrade() {
        const btn = document.getElementById('btn-upgrade');
        const box = document.getElementById('upgrade-output-box');
        const content = document.getElementById('upgrade-output-content');
        const statusText = document.getElementById('upgrade-status-text');

        if (btn) btn.disabled = true;
        if (box) box.classList.remove('hidden');
        if (statusText) statusText.textContent = 'Executing migrations & cache rebuild...';
        if (content) content.textContent = 'Starting php artisan migrate --force...\n';

        fetch(upgradeUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: jsonHeaders(),
            body: JSON.stringify({ type: 'patch' }),
        })
        .then(r => r.json())
        .then(data => {
            if (btn) btn.disabled = false;
            if (data.success) {
                if (statusText) statusText.textContent = 'SUCCESS: ' + data.version;
                if (content) content.textContent += (data.output || '') + '\n\n' + data.message;
                setTimeout(() => window.location.reload(), 1200);
            } else {
                if (statusText) statusText.textContent = 'FAILED';
                if (content) content.textContent += 'Error: ' + data.message;
                alert('Upgrade failed: ' + data.message);
            }
        })
        .catch(err => {
            if (btn) btn.disabled = false;
            if (statusText) statusText.textContent = 'ERROR';
            if (content) content.textContent += 'Network Error: ' + err.message;
        });
    }

    function confirmRestoreBackup(id, filename) {
        restoreBackupId = id;
        document.getElementById('restore-filename-display').textContent = filename;
        document.getElementById('restore-progress-container').classList.add('hidden');
        document.getElementById('restore-progress-bar').style.width = '0%';
        document.getElementById('restore-progress-text').classList.add('hidden');
        document.getElementById('restore-modal-actions').classList.remove('hidden');
        document.getElementById('restore-backup-modal').classList.add('open');
    }

    function executeRestore() {
        if (!restoreBackupId) {
            alert('No backup selected.');
            return;
        }
        const progressContainer = document.getElementById('restore-progress-container');
        const progressBar = document.getElementById('restore-progress-bar');
        const progressText = document.getElementById('restore-progress-text');
        const modalActions = document.getElementById('restore-modal-actions');

        modalActions.classList.add('hidden');
        progressContainer.classList.remove('hidden');
        progressText.classList.remove('hidden');
        progressText.textContent = 'Submitting restore...';
        progressBar.style.width = '40%';

        fetch(restoreUrlTemplate.replace('__ID__', restoreBackupId), {
            method: 'POST',
            credentials: 'same-origin',
            headers: jsonHeaders(),
            body: JSON.stringify({ confirm: true }),
        })
        .then(r => r.json())
        .then(data => {
            progressBar.style.width = '100%';
            progressText.textContent = data.message || 'Restore finished.';
            setTimeout(() => {
                document.getElementById('restore-backup-modal').classList.remove('open');
                alert(data.message || 'Restore finished.');
            }, 600);
        })
        .catch(err => {
            modalActions.classList.remove('hidden');
            progressText.textContent = err.message;
            alert('Restore failed: ' + err.message);
        });
    }

    const broadcastForm = document.getElementById('broadcast-form');
    if (broadcastForm) {
        broadcastForm.addEventListener('submit', (event) => {
            event.preventDefault();
            const message = broadcastForm.querySelector('[name="message"]').value;
            postOps(broadcastUrl, { message });
        });
    }
</script>
@endsection

@extends('layouts.app')

@section('title', 'System Load Balancer & Queuing Strategy - Admin Setups')
@section('section', 'Admin Setups')

@section('content')
<div class="mema-dashboard-container py-2">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.setups.index') }}" class="text-xs font-semibold text-[#0A3E50] hover:underline">&larr; Admin Setup Hub</a>
                <span class="text-slate-300">/</span>
                <span class="text-xs font-bold text-slate-700">Load Balancer &amp; Queuing</span>
            </div>
            <div class="flex items-center gap-3 mt-1">
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">System Load Balancer &amp; Traffic Scheduler</h1>
                <span class="px-2.5 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                    ACTIVE: {{ $config->active_algorithm }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Enterprise multi-strategy ingress dispatcher, FIFO/LIFO burst management, node cluster health, and circuit breakers</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" onclick="runClusterHealthCheck()" id="btnHealthCheck" class="px-3.5 py-2 rounded-lg bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs transition-colors shadow-2xs inline-flex items-center gap-1.5 cursor-pointer">
                <i data-lucide="activity" class="w-3.5 h-3.5 text-blue-600"></i>
                <span>Ping Node Cluster</span>
            </button>
            <button type="button" onclick="document.getElementById('addNodeModal').classList.remove('hidden')" class="px-3.5 py-2 rounded-lg bg-[#0A3E50] hover:bg-[#08303e] font-bold text-xs transition-colors shadow-2xs inline-flex items-center gap-1.5 text-white cursor-pointer" style="color:#ffffff !important;">
                <i data-lucide="plus-circle" class="w-3.5 h-3.5 text-white"></i>
                <span style="color:#ffffff !important;">Add Cluster Node</span>
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold flex items-center gap-2 shadow-2xs">
            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Cluster Metrics Row --}}
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-3.5 mb-7">
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Cluster Nodes</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ $clusterMetrics['totalNodes'] }} <span class="text-xs font-semibold text-emerald-700">({{ $clusterMetrics['healthyNodes'] }} Healthy)</span></div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Active Conns</div>
            <div class="text-2xl font-black text-[#0A3E50] mt-1">{{ $clusterMetrics['activeConnections'] }}</div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Avg Latency</div>
            <div class="text-2xl font-black text-blue-700 mt-1">{{ $clusterMetrics['avgLatency'] }}ms</div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Avg CPU Load</div>
            <div class="text-2xl font-black text-purple-700 mt-1">{{ $clusterMetrics['avgCpu'] }}%</div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Served Lifetime</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format($clusterMetrics['totalServed']) }}</div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Circuit Breaker</div>
            <div class="text-2xl font-black {{ $config->circuit_breaker_enabled ? 'text-[#1E8449]' : 'text-rose-600' }} mt-1">
                {{ $config->circuit_breaker_enabled ? 'ENGAGED' : 'DISABLED' }}
            </div>
        </div>
    </div>

    {{-- Strategy Selection Grid --}}
    <div class="mb-8">
        <div class="flex items-center justify-between mb-3.5">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Load Balancing &amp; Queuing Strategy</h2>
                <p class="text-xs text-slate-500 font-medium">Select active dispatch algorithm to control how incoming HTTP traffic and background jobs are scheduled.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-3.5">
            @foreach($algorithmDescriptions as $key => $strat)
                @php($isActive = $config->active_algorithm === $key)
                <div class="rounded-xl border p-4 transition-all relative flex flex-col justify-between @if($isActive) bg-emerald-50/70 border-[#1E8449] ring-2 ring-[#1E8449]/30 shadow-sm @else bg-white border-slate-200 hover:border-slate-300 shadow-2xs @endif">
                    <div>
                        <div class="flex justify-between items-start mb-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold @if($isActive) bg-[#1E8449] text-white @else bg-slate-100 text-slate-700 @endif">
                                {{ $strat['badge'] }}
                            </span>
                            @if($isActive)
                                <span class="flex h-2.5 w-2.5 relative">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-600"></span>
                                </span>
                            @endif
                        </div>
                        <h3 class="text-xs font-black text-slate-900 tracking-tight">{{ $strat['name'] }}</h3>
                        <div class="text-[11px] font-bold text-[#0A3E50] mt-0.5 mb-1.5">{{ $strat['tagline'] }}</div>
                        <p class="text-[11px] text-slate-600 leading-relaxed">{{ $strat['description'] }}</p>
                    </div>

                    <div class="mt-4 pt-3 border-t @if($isActive) border-emerald-200 @else border-slate-100 @endif">
                        @if($isActive)
                            <div class="text-center py-1.5 rounded-lg bg-emerald-700 text-white font-extrabold text-[11px] shadow-xs">
                                &check; Active Ingress Strategy
                            </div>
                        @else
                            <form method="POST" action="{{ route('admin.setups.load-balancer.strategy') }}">
                                @csrf
                                <input type="hidden" name="active_algorithm" value="{{ $key }}">
                                <button type="submit" class="w-full py-1.5 rounded-lg bg-slate-100 hover:bg-[#0A3E50] text-slate-700 hover:text-white font-bold text-[11px] transition-colors cursor-pointer text-center">
                                    Activate {{ $key }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Interactive Traffic Simulator & Benchmark Console --}}
    <div class="bg-white border border-slate-200 rounded-xl shadow-xs p-5 mb-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-4 border-b border-slate-200 mb-4">
            <div>
                <div class="flex items-center gap-2">
                    <i data-lucide="zap" class="w-4 h-4 text-amber-500"></i>
                    <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Interactive Traffic Simulation &amp; Benchmark Playground</h2>
                </div>
                <p class="text-xs text-slate-500 font-medium mt-0.5">Test and compare response times, queue latency, and node dispatching across FIFO, LIFO, WRR, and PFQ.</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <select id="simAlgorithm" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-bold text-slate-800 bg-white">
                    <option value="FIFO" {{ $config->active_algorithm === 'FIFO' ? 'selected' : '' }}>Algorithm: FIFO (First-In, First-Out)</option>
                    <option value="LIFO" {{ $config->active_algorithm === 'LIFO' ? 'selected' : '' }}>Algorithm: LIFO (Last-In, First-Out)</option>
                    <option value="WEIGHTED_ROUND_ROBIN" {{ $config->active_algorithm === 'WEIGHTED_ROUND_ROBIN' ? 'selected' : '' }}>Algorithm: Weighted Round Robin</option>
                    <option value="LEAST_CONNECTIONS" {{ $config->active_algorithm === 'LEAST_CONNECTIONS' ? 'selected' : '' }}>Algorithm: Least Connections</option>
                    <option value="PRIORITY_QUEUE" {{ $config->active_algorithm === 'PRIORITY_QUEUE' ? 'selected' : '' }}>Algorithm: Priority Fair Queue</option>
                </select>
                <select id="simCount" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-bold text-slate-800 bg-white">
                    <option value="25">25 Concurrent Requests</option>
                    <option value="50" selected>50 Concurrent Requests</option>
                    <option value="100">100 Concurrent Requests</option>
                </select>
                <button type="button" onclick="executeSimulationBenchmark()" id="btnRunSim" class="px-4 py-1.5 rounded-lg bg-[#E67E22] hover:bg-[#cf6d17] font-extrabold text-xs text-white shadow-2xs inline-flex items-center gap-1.5 cursor-pointer" style="color:#ffffff !important;">
                    <i data-lucide="play" class="w-3.5 h-3.5 text-white"></i>
                    <span style="color:#ffffff !important;">Run Benchmark</span>
                </button>
            </div>
        </div>

        {{-- Simulation Results Display Box --}}
        <div id="simResultsBox" class="hidden space-y-4">
            {{-- Benchmark Scorecards --}}
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                <div>
                    <span class="text-slate-500 font-bold block">Simulated Algorithm</span>
                    <span id="resAlgo" class="font-black text-[#0A3E50] text-sm">-</span>
                </div>
                <div>
                    <span class="text-slate-500 font-bold block">Avg Turnaround</span>
                    <span id="resAvg" class="font-mono font-bold text-slate-900 text-sm">-</span>
                </div>
                <div>
                    <span class="text-slate-500 font-bold block">P50 / P95 Latency</span>
                    <span id="resP50P95" class="font-mono font-bold text-blue-900 text-sm">-</span>
                </div>
                <div>
                    <span class="text-slate-500 font-bold block">P99 Latency (Tail)</span>
                    <span id="resP99" class="font-mono font-bold text-rose-700 text-sm">-</span>
                </div>
                <div>
                    <span class="text-slate-500 font-bold block">Est. Throughput</span>
                    <span id="resRps" class="font-mono font-black text-[#1E8449] text-sm">-</span>
                </div>
            </div>

            {{-- Node Distribution Progress Bars --}}
            <div id="resNodeDistribution" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                {{-- Injected dynamically --}}
            </div>

            {{-- Sample Execution Stream Table --}}
            <div class="overflow-x-auto border border-slate-200 rounded-lg">
                <table class="w-full text-left text-[11px] border-collapse">
                    <thead>
                        <tr class="bg-slate-100 text-slate-800 font-bold border-b border-slate-200">
                            <th class="p-2">Request ID</th>
                            <th class="p-2 text-center">Arrival Order</th>
                            <th class="p-2 text-center">Execution Order</th>
                            <th class="p-2">Priority Tier</th>
                            <th class="p-2">Target Endpoint</th>
                            <th class="p-2">Assigned Node</th>
                            <th class="p-2 text-right">Queue Wait</th>
                            <th class="p-2 text-right">Processing</th>
                            <th class="p-2 text-right">Total Turnaround</th>
                        </tr>
                    </thead>
                    <tbody id="resExecutionTableBody" class="divide-y divide-slate-100 font-mono">
                        {{-- Injected dynamically --}}
                    </tbody>
                </table>
            </div>
        </div>

        <div id="simEmptyPrompt" class="text-center py-6 text-slate-400 text-xs">
            <i data-lucide="bar-chart-2" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
            Click <strong>"Run Benchmark"</strong> to execute synthetic traffic burst testing and evaluate latency distributions.
        </div>
    </div>

    {{-- Cluster Topology & Fleet Grid --}}
    <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden mb-8">
        <div class="p-4 border-b border-slate-200 flex justify-between items-center">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Cluster Backend Node Fleet</h2>
                <p class="text-xs text-slate-500">Live operational topology and hardware utilization across application and worker nodes</p>
            </div>
            <span class="text-xs font-bold text-slate-600 font-mono">{{ $nodes->count() }} Registered Nodes</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-800 font-bold border-b border-slate-200">
                        <th class="py-3 px-4">Node Particulars</th>
                        <th class="py-3 px-4">Cluster Role</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Weight</th>
                        <th class="py-3 px-4">Active Conns</th>
                        <th class="py-3 px-4">CPU &amp; Memory</th>
                        <th class="py-3 px-4 text-center">Latency</th>
                        <th class="py-3 px-4 text-right">Served Requests</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($nodes as $node)
                        <tr class="hover:bg-slate-50/80 transition-colors" id="node-row-{{ $node->id }}">
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $node->name }}</div>
                                <div class="font-mono text-[10.5px] text-slate-500">{{ $node->host }}:{{ $node->port }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10.5px] font-bold @if($node->role === 'APPLICATION') bg-blue-100 text-blue-800 @elseif($node->role === 'BACKGROUND_WORKER') bg-purple-100 text-purple-800 @else bg-amber-100 text-amber-800 @endif">
                                    {{ str_replace('_', ' ', $node->role) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10.5px] font-black @if($node->status === 'HEALTHY') bg-emerald-100 text-emerald-800 @elseif($node->status === 'DRAINING') bg-amber-100 text-amber-800 @else bg-rose-100 text-rose-800 @endif">
                                    {{ $node->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center font-mono font-bold text-slate-800">{{ $node->weight }}</td>
                            <td class="py-3 px-4 font-mono font-bold text-[#0A3E50]">{{ $node->active_connections }}</td>
                            <td class="py-3 px-4 w-40">
                                <div class="space-y-1">
                                    <div class="flex justify-between text-[10px] font-semibold text-slate-600">
                                        <span>CPU: {{ $node->cpu_usage }}%</span>
                                        <span>RAM: {{ $node->memory_usage }}%</span>
                                    </div>
                                    <div class="w-full h-1.5 rounded-full bg-slate-100 overflow-hidden flex">
                                        <div class="bg-blue-600 h-full" style="width: {{ $node->cpu_usage }}%;"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-center font-mono font-bold text-blue-900">{{ $node->latency_ms }}ms</td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">{{ number_format($node->total_served_requests) }}</td>
                            <td class="py-3 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <form method="POST" action="{{ route('admin.setups.load-balancer.toggle-node', $node->id) }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="action" value="drain">
                                        <button type="submit" class="px-2 py-1 rounded text-[10.5px] font-bold border border-slate-300 hover:bg-slate-100 text-slate-700 cursor-pointer">
                                            {{ $node->status === 'DRAINING' ? 'Undrain' : 'Drain' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.setups.load-balancer.destroy-node', $node->id) }}" onsubmit="return confirm('Remove node {{ $node->name }} from cluster?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 rounded text-slate-400 hover:text-rose-600 cursor-pointer">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Advanced Operational Parameters & Circuit Breaker Config Form --}}
    <div class="bg-white border border-slate-200 rounded-xl shadow-xs p-5">
        <h2 class="text-base font-extrabold text-slate-900 tracking-tight mb-1">Operational Limits &amp; Circuit Breaker Parameters</h2>
        <p class="text-xs text-slate-500 mb-4">Fine-tune cluster concurrency backpressure, timeout thresholds, and automated health recovery intervals.</p>

        <form method="POST" action="{{ route('admin.setups.load-balancer.config') }}" class="space-y-4 text-xs">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Max Concurrency Per Node</label>
                    <input type="number" name="max_concurrency_per_node" value="{{ $config->max_concurrency_per_node }}" required min="10" max="5000" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <p class="text-[10px] text-slate-400 mt-1">Maximum simultaneous HTTP requests per worker.</p>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Queue Timeout (Seconds)</label>
                    <input type="number" name="queue_timeout_seconds" value="{{ $config->queue_timeout_seconds }}" required min="1" max="300" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <p class="text-[10px] text-slate-400 mt-1">LIFO/FIFO queue drop threshold before shedding.</p>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Rate Limit (Requests / Min)</label>
                    <input type="number" name="rate_limit_rpm" value="{{ $config->rate_limit_rpm }}" required min="60" max="60000" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <p class="text-[10px] text-slate-400 mt-1">Global ingress firewall throttling ceiling.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Failure Threshold (Errors)</label>
                    <input type="number" name="failure_threshold" value="{{ $config->failure_threshold }}" required min="1" max="50" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Recovery Timeout (Seconds)</label>
                    <input type="number" name="recovery_timeout_seconds" value="{{ $config->recovery_timeout_seconds }}" required min="1" max="120" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Surge Fallback Action</label>
                    <select name="fallback_action" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-bold text-slate-800 bg-white">
                        <option value="DEGRADE_GRACEFULLY" {{ $config->fallback_action === 'DEGRADE_GRACEFULLY' ? 'selected' : '' }}>Degrade Gracefully (Cache-First)</option>
                        <option value="QUEUE_WITH_BACKPRESSURE" {{ $config->fallback_action === 'QUEUE_WITH_BACKPRESSURE' ? 'selected' : '' }}>Queue With Backpressure</option>
                        <option value="REJECT_503" {{ $config->fallback_action === 'REJECT_503' ? 'selected' : '' }}>Reject with 503 Overload</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="circuit_breaker_enabled" id="cbCircuit" value="1" {{ $config->circuit_breaker_enabled ? 'checked' : '' }} class="rounded text-[#0A3E50] focus:ring-[#0A3E50]">
                <label for="cbCircuit" class="font-bold text-slate-800">Enable Automated Circuit Breaker &amp; Health Isolator</label>
            </div>

            <div class="flex justify-end pt-3 border-t border-slate-100">
                <button type="submit" class="px-4 py-2 rounded-lg bg-[#0A3E50] hover:bg-[#08303e] font-bold text-xs text-white transition-colors" style="color:#ffffff !important;">
                    Save Load Balancer Configuration
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Add Node Modal --}}
<div id="addNodeModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl border border-slate-200 max-w-md w-full p-6 relative">
        <div class="flex justify-between items-center pb-3 border-b border-slate-100 mb-4">
            <h3 class="text-base font-bold text-slate-900">Register Backend Node</h3>
            <button type="button" onclick="document.getElementById('addNodeModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.setups.load-balancer.store-node') }}" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 mb-1">Node Identifier Name</label>
                <input type="text" name="name" required placeholder="e.g. Web-Node-03 (East Wing)" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Host / IP</label>
                    <input type="text" name="host" required placeholder="10.0.1.12" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Port</label>
                    <input type="number" name="port" value="8000" required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Cluster Role</label>
                    <select name="role" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-bold text-slate-800 bg-white">
                        <option value="APPLICATION">Application Server</option>
                        <option value="BACKGROUND_WORKER">Background Worker</option>
                        <option value="DATABASE_READ_REPLICA">DB Read Replica</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Capacity Weight</label>
                    <input type="number" name="weight" value="100" min="1" max="1000" required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('addNodeModal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 font-semibold text-xs hover:bg-slate-50">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-[#0A3E50] hover:bg-[#08303e] text-white font-bold text-xs" style="color:#ffffff !important;">Register Node</button>
            </div>
        </form>
    </div>
</div>

<script>
    async function executeSimulationBenchmark() {
        const algo = document.getElementById('simAlgorithm').value;
        const count = document.getElementById('simCount').value;
        const btn = document.getElementById('btnRunSim');

        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader" class="w-3.5 h-3.5 animate-spin"></i> Running...';

        try {
            const res = await fetch("{{ route('admin.setups.load-balancer.simulate') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ algorithm: algo, request_count: count })
            });

            const data = await res.json();
            if (data.success) {
                document.getElementById('simEmptyPrompt').classList.add('hidden');
                document.getElementById('simResultsBox').classList.remove('hidden');

                document.getElementById('resAlgo').textContent = data.algorithm;
                document.getElementById('resAvg').textContent = data.avg_latency_ms + 'ms';
                document.getElementById('resP50P95').textContent = data.p50_latency_ms + 'ms / ' + data.p95_latency_ms + 'ms';
                document.getElementById('resP99').textContent = data.p99_latency_ms + 'ms';
                document.getElementById('resRps').textContent = data.throughput_rps + ' RPS';

                // Render Node Distribution
                const distContainer = document.getElementById('resNodeDistribution');
                distContainer.innerHTML = '';
                data.node_distribution.forEach(n => {
                    const pct = Math.round((n.dispatched_count / data.total_requests) * 100);
                    distContainer.innerHTML += `
                        <div class="p-3 rounded-lg bg-white border border-slate-200 text-xs">
                            <div class="flex justify-between font-bold text-slate-800">
                                <span>${n.name}</span>
                                <span class="font-mono text-[#0A3E50]">${n.dispatched_count} reqs (${pct}%)</span>
                            </div>
                            <div class="w-full h-1.5 rounded-full bg-slate-100 mt-2 overflow-hidden">
                                <div class="bg-[#1E8449] h-full" style="width: ${pct}%"></div>
                            </div>
                        </div>
                    `;
                });

                // Render Execution sample
                const tbody = document.getElementById('resExecutionTableBody');
                tbody.innerHTML = '';
                data.execution_sample.forEach(r => {
                    const priorityColor = r.priority === 'URGENT' ? 'text-rose-700 bg-rose-50' : (r.priority === 'HIGH' ? 'text-amber-800 bg-amber-50' : 'text-slate-700 bg-slate-50');
                    tbody.innerHTML += `
                        <tr class="hover:bg-slate-50">
                            <td class="p-2 font-bold text-slate-900">${r.req_id}</td>
                            <td class="p-2 text-center text-slate-500">${r.arrival_seq}</td>
                            <td class="p-2 text-center font-bold text-blue-900">${r.execution_seq}</td>
                            <td class="p-2"><span class="px-1.5 py-0.5 rounded text-[10px] font-bold ${priorityColor}">${r.priority}</span></td>
                            <td class="p-2 text-slate-700 font-sans text-[10.5px]">${r.path}</td>
                            <td class="p-2 font-sans font-semibold text-slate-800">${r.node_name}</td>
                            <td class="p-2 text-right text-slate-500">${r.wait_time_ms}ms</td>
                            <td class="p-2 text-right text-slate-800">${r.execution_time_ms}ms</td>
                            <td class="p-2 text-right font-bold text-emerald-800">${r.total_turnaround_ms}ms</td>
                        </tr>
                    `;
                });
            }
        } catch (e) {
            alert('Simulation failed: ' + e.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i data-lucide="play" class="w-3.5 h-3.5 text-white"></i> <span>Run Benchmark</span>';
            if (window.lucide) window.lucide.createIcons();
        }
    }

    async function runClusterHealthCheck() {
        const btn = document.getElementById('btnHealthCheck');
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader" class="w-3.5 h-3.5 animate-spin"></i> Pinging...';

        try {
            const res = await fetch("{{ route('admin.setups.load-balancer.health-check') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            const data = await res.json();
            if (data.success) {
                alert(data.message);
                window.location.reload();
            }
        } catch (e) {
            alert('Health check failed: ' + e.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i data-lucide="activity" class="w-3.5 h-3.5 text-blue-600"></i> <span>Ping Node Cluster</span>';
        }
    }
</script>
@endsection

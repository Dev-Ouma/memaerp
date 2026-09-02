@extends('layouts.app')

@section('title', 'Reminder')

@section('content')
<div class="mema-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Automated Notification & Reminder Broadcast Engine</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Broadcast automated multi-channel SMS and email reminders for fee payment deadlines, unit registration cutoffs, and orientation schedules</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                New Reminder Campaign
            </button>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Active Campaigns</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['activeAutomatedCampaigns'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Scheduled trigger rules.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">Scheduled Campaigns</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">SMS Broadcasts</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ number_format($stats['smsBroadcastDelivered']) }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Delivered via Telco Gateway.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">SMS Direct</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Email Notifications</div>
            <div class="text-3xl font-extrabold text-blue-900 mt-2 mb-1.5 leading-none">{{ number_format($stats['emailsDelivered']) }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Dispatched to university inboxes.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">Email Dispatched</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Delivery Success</div>
            <div class="text-3xl font-extrabold text-purple-900 mt-2 mb-1.5 leading-none">{{ $stats['deliverySuccessRate'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Low bounce threshold.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-purple-800 bg-purple-50 border border-purple-200">High Reliability</span></div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Campaign Code & Title</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Target Audience Group</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Dispatch Channels & Schedule</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Total Recipients</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Status</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($campaigns as $camp)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $camp['campaign_code'] }}</span>
                                <div class="font-bold text-slate-900 text-xs mt-1">{{ $camp['title'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800 text-xs">{{ $camp['target_audience'] }}</td>
                            <td class="py-3.5 px-4 text-xs">
                                <div class="font-bold text-purple-900">{{ $camp['dispatch_channels'] }}</div>
                                <div class="text-slate-500 text-[10.5px] mt-0.5">{{ $camp['trigger_schedule'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-emerald-800 text-xs">{{ number_format($camp['total_recipients']) }} Recipients</td>
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">{{ $camp['status'] }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <button type="button" class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">Dispatch</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

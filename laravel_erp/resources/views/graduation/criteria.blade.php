@extends('layouts.app')

@section('title', 'Graduation Criteria')

@section('content')
<div class="mema-dashboard-container py-2">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Programme Graduation Criteria & Credit Policies</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Configure degree qualification benchmarks, minimum credit ceilings, required CGPA boundaries, and department clearance nodes</p>
        </div>
        <button class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">Add Criteria Rule</button>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-[#0A3E50] text-white">
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Accredited Programme</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Min Credits</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Min CGPA</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Thesis Required</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Required Clearance Nodes</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Status</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @foreach($criteria as $c)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3 px-4 font-bold text-slate-900 text-xs">{{ $c['programme'] }}</td>
                        <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ $c['min_credits'] }}</td>
                        <td class="py-3 px-4 font-mono font-bold text-purple-900">{{ $c['min_cgpa'] }}</td>
                        <td class="py-3 px-4 text-[#0A3E50] font-semibold">{{ $c['thesis_required'] }}</td>
                        <td class="py-3 px-4 text-slate-600 font-semibold">{{ $c['clearance_nodes'] }}</td>
                        <td class="py-3 px-4">
                            <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">{{ $c['status'] }}</span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <button class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">Edit</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

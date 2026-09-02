@extends('layouts.app')

@section('title', 'Exam Center Configuration')

@section('content')
<div class="mema-dashboard-container py-2">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Exam Centers & Capacity Configuration</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Configure physical exam halls, room capacities, invigilator/proctor allocations, and virtual exam centers</p>
        </div>
        @can('admin')<button type="button" data-modal-open="exam-center-modal" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">Add Exam Center</button>@endcan
    </div>

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-[#0A3E50] text-white">
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Center Code & Name</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Location</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Capacity</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Proctors Allocated</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Access Support</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Status</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @foreach($centers as $c)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3 px-4">
                            <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $c['center_code'] }}</span>
                            <div class="font-bold text-slate-900 mt-1">{{ $c['name'] }}</div>
                        </td>
                        <td class="py-3 px-4 text-slate-700 font-semibold">{{ $c['location'] }}</td>
                        <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ number_format($c['capacity']) }} seats</td>
                        <td class="py-3 px-4 font-semibold text-[#0A3E50]">{{ $c['proctors_allocated'] }} proctors</td>
                        <td class="py-3 px-4 text-slate-600">{{ $c['special_needs_access'] }}</td>
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
@can('admin')<div class="modal" id="exam-center-modal"><div class="modal-card"><div class="panel-head"><h2>Add Exam Center</h2><button type="button" class="btn btn-secondary" data-modal-close>Close</button></div><form class="panel-body" method="post" action="{{ route('examination.exam-center.store') }}">@csrf<div class="form-grid"><div class="field"><label>Code</label><input name="center_code" required></div><div class="field"><label>Name</label><input name="name" required></div><div class="field full"><label>Location</label><input name="location" required></div><div class="field"><label>Capacity</label><input type="number" name="capacity" min="1" required></div><div class="field"><label>Proctors</label><input type="number" name="proctors_allocated" min="0" value="0" required></div><div class="field full"><label>Access support</label><input name="special_needs_access"></div><div class="field"><label>Status</label><select name="status"><option>OPERATIONAL</option><option>MAINTENANCE</option><option>INACTIVE</option></select></div></div><button class="btn" style="margin-top:16px">Save center</button></form></div></div>@endcan
@endsection

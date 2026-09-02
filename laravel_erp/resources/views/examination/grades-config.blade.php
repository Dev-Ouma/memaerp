@extends('layouts.app')

@section('title', 'Grades & Scale Config')

@section('content')
<div class="mema-dashboard-container py-2">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">University Grading Policy & Scale Config</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Configure trimesters grading letter scales, minimum/maximum thresholds, GPA point allocations, and classification honours tags</p>
        </div>
        @can('admin')<button type="button" data-modal-open="grade-scale-modal" class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">Add Grade Letter</button>@endcan
    </div>

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-[#0A3E50] text-white">
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Grade Letter</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Minimum Marks (%)</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Maximum Marks (%)</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">GPA Point Value</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Honours Performance Descriptor</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Status</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @foreach($scales as $s)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3 px-4 font-extrabold text-[#0A3E50] text-sm">{{ $s['grade_letter'] }}</td>
                        <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ $s['min_marks'] }}%</td>
                        <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ $s['max_marks'] }}%</td>
                        <td class="py-3 px-4 font-mono text-purple-900 font-bold">{{ $s['gpa_points'] }}</td>
                        <td class="py-3 px-4 font-semibold text-slate-700">{{ $s['performance_descriptor'] }}</td>
                        <td class="py-3 px-4">
                            <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">{{ $s['status'] }}</span>
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
@can('admin')<div class="modal" id="grade-scale-modal"><div class="modal-card"><div class="panel-head"><h2>Add Grade Letter</h2><button type="button" class="btn btn-secondary" data-modal-close>Close</button></div><form class="panel-body" method="post" action="{{ route('examination.grades-config.store') }}">@csrf<div class="form-grid"><div class="field"><label>Letter</label><input name="grade_letter" maxlength="5" required></div><div class="field"><label>GPA points</label><input type="number" name="gpa_points" min="0" max="5" step="0.01" required></div><div class="field"><label>Minimum marks</label><input type="number" name="min_marks" min="0" max="100" step="0.01" required></div><div class="field"><label>Maximum marks</label><input type="number" name="max_marks" min="0" max="100" step="0.01" required></div><div class="field full"><label>Descriptor</label><input name="performance_descriptor" required></div></div><button class="btn" style="margin-top:16px">Save grade scale</button></form></div></div>@endcan
@endsection

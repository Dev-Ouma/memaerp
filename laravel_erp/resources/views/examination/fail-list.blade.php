@extends('layouts.app')

@section('title', 'Fail List')

@section('content')
<div class="mema-dashboard-container py-2">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Academic Fail, Supplementary & Repeat Register</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Verify students with trailing or failed units, schedule supplementary examinations, and log academic board repeat orders</p>
        </div>
        <button class="px-4 py-1.5 rounded-md border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">Schedule Supplementary</button>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-[#0A3E50] text-white">
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Reg No & Student Name</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Programme</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Failed Course Unit</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Raw Marks Obtained</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Supplementary Ref</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Scheduled Exam Date</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Academic Board Verdict</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @foreach($fails as $f)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3 px-4">
                            <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $f['reg_no'] }}</span>
                            <div class="font-bold text-slate-900 mt-1">{{ $f['student_name'] }}</div>
                        </td>
                        <td class="py-3 px-4 font-semibold text-slate-800">{{ $f['programme'] }}</td>
                        <td class="py-3 px-4 font-semibold text-[#0A3E50]">{{ $f['failed_unit'] }}</td>
                        <td class="py-3 px-4 font-mono font-bold text-red-700">{{ $f['raw_marks'] }}</td>
                        <td class="py-3 px-4 font-mono text-purple-900 font-bold">{{ $f['supplementary_ref'] }}</td>
                        <td class="py-3 px-4 font-mono text-slate-600 font-semibold">{{ $f['scheduled_date'] }}</td>
                        <td class="py-3 px-4">
                            @if(str_contains($f['verdict'], 'Repeat') || str_contains($f['verdict'], 'Disqualification'))
                                <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-red-100 text-red-800">{{ $f['verdict'] }}</span>
                            @else
                                <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-amber-100 text-amber-800">{{ $f['verdict'] }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            <button class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">Resolve</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

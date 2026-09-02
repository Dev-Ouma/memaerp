@extends('layouts.app')

@section('title', 'Student & Short Course Search')

@section('content')
<div class="mema-dashboard-container py-2">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Student, Payment Source & Transaction Search Desk</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Query individual student records for short courses enrollments, payment sources, and direct bank/M-Pesa transaction reference checks</p>
        </div>
    </div>

    {{-- Search Bar --}}
    <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs mb-6">
        <form action="#" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input type="text" name="query" value="MEMA/BCS/2024/0912" placeholder="Search by Reg No, Payment Source code, or Transaction ID Ref..." class="w-full px-4 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-[#0A3E50] focus:border-[#0A3E50]" />
            </div>
            <button type="submit" class="px-5 py-2 bg-[#0A3E50] text-white hover:bg-[#082f3e] rounded-lg font-bold text-xs transition-all shadow-xs">
                Query Registry Ledger
            </button>
        </form>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-[#0A3E50] text-white">
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Search Query Reference</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Student Full Name</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Registered Course Units</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Short Courses & Badges</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Payment Allocations</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Query Status</th>
                    <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @foreach($results as $res)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3.5 px-4 font-mono font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $res['search_query'] }}</td>
                        <td class="py-3.5 px-4 font-bold text-slate-900 text-xs">{{ $res['student_name'] }}</td>
                        <td class="py-3.5 px-4 font-mono text-slate-600 font-semibold">{{ $res['registered_courses'] }}</td>
                        <td class="py-3.5 px-4 font-semibold text-purple-900 text-xs">{{ $res['short_courses'] }}</td>
                        <td class="py-3.5 px-4 font-semibold text-emerald-800 text-xs">{{ $res['payments'] }}</td>
                        <td class="py-3.5 px-4">
                            <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">{{ $res['status'] }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <button class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">Dossier</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

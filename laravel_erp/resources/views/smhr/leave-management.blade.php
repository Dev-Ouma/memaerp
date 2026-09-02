@extends('layouts.app')

@section('title', 'Leave Management & Approvals - SMHR')
@section('section', 'SMHR')

@section('content')
<div class="mema-dashboard-container py-2">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('smhr.dashboard') }}" class="text-xs font-semibold text-[#0A3E50] hover:underline">&larr; SMHR Dashboard</a>
                <span class="text-slate-300">/</span>
                <span class="text-xs font-bold text-slate-700">Leave Management</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight mt-1">Leave Management &amp; Approvals</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Faculty and administrative staff statutory leave application, reliever routing, and HOD approval tracking</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="document.getElementById('applyLeaveModal').classList.remove('hidden')" class="px-3.5 py-2 rounded-lg bg-[#1E8449] hover:bg-[#166534] font-bold text-xs transition-colors shadow-2xs inline-flex items-center gap-1.5 text-white cursor-pointer" style="color:#ffffff !important;">
                <i data-lucide="plus-circle" class="w-3.5 h-3.5 text-white"></i>
                <span style="color:#ffffff !important;">Apply for Leave</span>
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-semibold flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- 4 Metric Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-7">
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-600 uppercase tracking-wide">Currently on Leave</div>
            <div class="text-2xl font-extrabold text-[#0A3E50] mt-1.5">{{ $leaveStats['totalOnLeave'] }} Staff</div>
            <p class="text-[11px] text-slate-500 mt-0.5">Active approved leaves</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-600 uppercase tracking-wide">Pending HR Approvals</div>
            <div class="text-2xl font-extrabold text-amber-600 mt-1.5">{{ $leaveStats['pendingApproval'] }} Requests</div>
            <p class="text-[11px] text-slate-500 mt-0.5">Awaiting HOD/Dean endorsement</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-600 uppercase tracking-wide">Approved This Month</div>
            <div class="text-2xl font-extrabold text-[#1E8449] mt-1.5">{{ $leaveStats['approvedThisMonth'] }} Approved</div>
            <p class="text-[11px] text-slate-500 mt-0.5">August/September cycle</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-600 uppercase tracking-wide">Average Duration</div>
            <div class="text-2xl font-extrabold text-purple-700 mt-1.5">{{ $leaveStats['averageLeaveDays'] }}</div>
            <p class="text-[11px] text-slate-500 mt-0.5">Per staff member</p>
        </div>
    </div>

    {{-- Leave Requests Ledger --}}
    <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-sm font-bold text-slate-900">Leave Requests &amp; Reliever Approvals</h2>
            <span class="text-xs text-slate-500 font-medium">Statutory Entitlement: 30 Days/Year</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-800 font-bold border-b border-slate-200">
                        <th class="py-3 px-4">Request Ref</th>
                        <th class="py-3 px-4">Staff Member</th>
                        <th class="py-3 px-4">Leave Category</th>
                        <th class="py-3 px-4 text-center">Days</th>
                        <th class="py-3 px-4">Start &amp; End Date</th>
                        <th class="py-3 px-4">Designated Reliever</th>
                        <th class="py-3 px-4 text-center">Remaining Balance</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($leaveRequests as $req)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-4 font-mono font-bold text-[#0A3E50]">{{ $req['id'] }}</td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $req['name'] }}</div>
                                <div class="text-[11px] text-slate-500">{{ $req['dept'] }} &middot; {{ $req['staff_id'] }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-semibold text-slate-800">{{ $req['type'] }}</span>
                                <div class="text-[10.5px] text-slate-500 truncate max-w-xs">{{ $req['reason'] }}</div>
                            </td>
                            <td class="py-3 px-4 text-center font-mono font-extrabold text-blue-800">{{ $req['days'] }}</td>
                            <td class="py-3 px-4 text-slate-700">
                                <div><strong>{{ $req['start_date'] }}</strong> to <strong>{{ $req['end_date'] }}</strong></div>
                            </td>
                            <td class="py-3 px-4 font-medium text-slate-800">{{ $req['reliever'] }}</td>
                            <td class="py-3 px-4 text-center font-mono font-bold text-[#1E8449]">{{ $req['balance_remaining'] }} Days</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[10.5px] font-bold @if($req['status'] === 'APPROVED') bg-emerald-100 text-emerald-800 @else bg-amber-100 text-amber-800 @endif">
                                    {{ $req['status'] }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($req['status'] === 'PENDING')
                                    <div class="flex items-center justify-center gap-1.5">
                                        <form method="POST" action="{{ route('smhr.leave-management.approve', $req['id']) }}">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 rounded bg-[#1E8449] hover:bg-[#166534] font-bold text-[11px] text-white transition-colors" style="color:#ffffff !important;">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('smhr.leave-management.reject', $req['id']) }}">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 rounded border border-rose-300 hover:bg-rose-50 font-bold text-[11px] text-rose-700 transition-colors">Reject</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-slate-400 font-semibold text-[11px]">&mdash; Processed &mdash;</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Apply Leave Modal --}}
<div id="applyLeaveModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl border border-slate-200 max-w-lg w-full p-6 relative">
        <div class="flex justify-between items-center pb-3 border-b border-slate-100 mb-4">
            <h3 class="text-base font-bold text-slate-900">Submit Staff Leave Application</h3>
            <button type="button" onclick="document.getElementById('applyLeaveModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('smhr.leave-management.store') }}" class="space-y-3.5 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 mb-1">Staff Member</label>
                <input type="text" name="staff_name" required placeholder="e.g. Dr. Emmanuel Mutua" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Leave Type</label>
                    <select name="leave_type" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs bg-white">
                        <option value="Annual Leave">Annual Statutory Leave</option>
                        <option value="Study / Sabbatical">Study / Sabbatical Research</option>
                        <option value="Sick Leave">Sick Leave</option>
                        <option value="Maternity / Paternity">Maternity / Paternity Leave</option>
                        <option value="Compassionate Leave">Compassionate Leave</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Designated Reliever</label>
                    <input type="text" name="reliever" required placeholder="Colleague name" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Start Date</label>
                    <input type="date" name="start_date" required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">End Date</label>
                    <input type="date" name="end_date" required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Reason / Purpose of Leave</label>
                <textarea name="reason" rows="3" required placeholder="Detailed reason..." class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('applyLeaveModal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 font-semibold text-xs hover:bg-slate-50">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-[#1E8449] hover:bg-[#166534] text-white font-bold text-xs transition-colors" style="color:#ffffff !important;">Submit Application</button>
            </div>
        </form>
    </div>
</div>
@endsection

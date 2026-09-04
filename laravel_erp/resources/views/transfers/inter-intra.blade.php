@extends('layouts.app')

@section('title', 'Inter/Intra Faculty Transfers')

@section('content')
<div class="ouk-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Inter/Intra Faculty Transfers</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Review and process student faculty migration requests and quota allocations</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="toggleWorkflowGuide()" class="btn btn-secondary text-xs py-1.5 px-3 flex items-center gap-1.5 shadow-2xs">
                <i data-lucide="help-circle" class="w-3.5 h-3.5 text-slate-600"></i>
                <span id="workflow-toggle-btn-text">Show Workflow Guide</span>
            </button>
        </div>
    </div>

    {{-- Real-Time Alert Toast Container --}}
    <div id="transfer-alert-box" class="hidden mb-4 p-3.5 rounded-xl border text-xs font-semibold flex items-start justify-between gap-3 shadow-sm transition-all">
        <div class="flex items-start gap-2.5">
            <i id="alert-icon" data-lucide="info" class="w-4 h-4 mt-0.5 flex-shrink-0"></i>
            <div>
                <strong id="alert-title" class="block font-bold"></strong>
                <span id="alert-message" class="font-normal opacity-90"></span>
            </div>
        </div>
        <button type="button" onclick="dismissAlert()" class="text-slate-400 hover:text-slate-600">
            <i data-lucide="x" class="w-3.5 h-3.5"></i>
        </button>
    </div>

    {{-- Admin Workflow & Action Lifecycle Card --}}
    <div id="admin-workflow-guide" class="mb-5 bg-white border border-slate-200 rounded-xl p-4.5 shadow-xs bg-linear-to-r from-slate-50/70 to-slate-50/40">
        <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-2">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#0A3E50]"></span>
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wide">Inter/Intra Faculty Migration Lifecycle & Rules (Admin Perspective)</h3>
            </div>
            <span class="text-[11px] font-semibold text-slate-800 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">CUE Quota Compliance</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs">
            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-slate-800 mb-1">
                    <i data-lucide="split" class="w-4 h-4 text-[#0A3E50]"></i> Intra-Faculty Transfer
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Student transfers between degree programmes under the same school. Requires departmental chair and Dean sign-off.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-blue-800 mb-1">
                    <i data-lucide="arrow-left-right" class="w-4 h-4 text-blue-600"></i> Inter-Faculty Transfer
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Student transfers across two different faculties (e.g. Science to Business). Requires release by current Dean and acceptance by receiving Dean.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-emerald-700 mb-1">
                    <i data-lucide="file-check" class="w-4 h-4 text-emerald-600"></i> Application Form & Docs
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Verifies student's secondary school cluster points match the receiving programme's cutoff requirements.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-amber-700 mb-1">
                    <i data-lucide="shield-alert" class="w-4 h-4 text-amber-600"></i> Capacity & Quota Guard
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    System prevents approval if receiving cohort has exceeded maximum licensed CUE classroom capacity.
                </p>
            </div>
        </div>
    </div>

    {{-- Filter Dropdown & Controls --}}
    <div class="mb-4">
        <div class="w-full sm:w-64 mb-3">
            <select id="status-filter-select" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-medium text-slate-700 focus:outline-none focus:border-[#0A3E50] shadow-2xs">
                <option value="">Select Status</option>
                <option value="approved">Approved</option>
                <option value="pending">Pending</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>

        <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3">
            <div class="flex items-center gap-2 text-xs text-slate-700 font-medium">
                <span>Show</span>
                <select class="bg-white border border-slate-300 rounded px-2 py-1 text-xs focus:outline-none focus:border-[#0A3E50]">
                    <option>10</option>
                    <option>25</option>
                    <option>50</option>
                </select>
                <span>entries</span>
            </div>

            <div class="flex items-center gap-2 text-xs text-slate-700 font-medium">
                <label for="inter-search">Search:</label>
                <input type="text" id="inter-search" class="bg-white border border-slate-300 rounded-md px-3 py-1 text-xs text-slate-800 focus:outline-none focus:border-[#0A3E50] w-48 sm:w-60">
            </div>
        </div>
    </div>

    {{-- Table Matching System Theme --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs" id="inter-table">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-3 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Student Name</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-3 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Email ID</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-3 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Registration Number</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-3 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Transfer Type</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-3 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Current Programme</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-3 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Transfer to Programme</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-3 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-between gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Reason</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-3 font-bold tracking-wider text-white border-r border-white/15 text-center uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-center gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Status</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-3 font-bold tracking-wider text-white border-r border-white/15 text-center uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-center gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Student Application Form</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-3 font-bold tracking-wider text-white border-r border-white/15 text-center uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-center gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Student Documents</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                        <th class="py-3 px-3 font-bold tracking-wider text-white text-center uppercase text-[11px]" style="color:#ffffff !important;">
                            <div class="flex items-center justify-center gap-1 text-white" style="color:#ffffff !important;">
                                <span class="text-white font-bold" style="color:#ffffff !important;">Action</span>
                                <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-white/80" style="color:#ffffff !important;"></i>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white" id="inter-tbody">
                    @foreach($transfers as $row)
                        <tr class="hover:bg-slate-50/70 transition-colors transfer-row" id="transfer-row-{{ $row['id'] }}" data-status="{{ strtolower($row['status']) }}" data-search="{{ strtolower($row['name'].' '.$row['email'].' '.$row['reg_no'].' '.$row['current_programme'].' '.$row['transfer_programme']) }}">
                            <td class="py-3 px-3 font-bold text-slate-800 uppercase">{{ $row['name'] }}</td>
                            <td class="py-3 px-3 text-slate-600 font-mono text-[11px]">{{ $row['email'] }}</td>
                            <td class="py-3 px-3 text-slate-700 font-mono text-[11px] font-semibold">{{ $row['reg_no'] }}</td>
                            <td class="py-3 px-3 font-semibold text-slate-700">{{ $row['type'] }}</td>
                            <td class="py-3 px-3 text-slate-800">{{ $row['current_programme'] }}</td>
                            <td class="py-3 px-3 text-slate-800 font-medium">{{ $row['transfer_programme'] }}</td>
                            <td class="py-3 px-3 text-slate-600">{{ $row['reason'] }}</td>
                            <td class="py-3 px-3 text-center transfer-status-cell">
                                @if(strtolower($row['status']) === 'approved')
                                    <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 status-badge">Approved</span>
                                @elseif(strtolower($row['status']) === 'rejected')
                                    <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-red-100 text-red-800 status-badge">Rejected</span>
                                @else
                                    <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 status-badge">Pending</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center">
                                <button type="button" onclick="openAppFormModal('{{ $row['id'] }}', '{{ $row['name'] }}', '{{ $row['current_programme'] }}', '{{ $row['transfer_programme'] }}', '{{ $row['reason'] }}', '{{ $row['email'] }}', '{{ $row['reg_no'] }}')" class="px-3 py-0.5 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">
                                    View
                                </button>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <button type="button" onclick="openDocPreviewModal('{{ $row['name'] }}', '{{ $row['reg_no'] }}')" class="px-3 py-0.5 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">
                                    view
                                </button>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <details class="relative inline-block text-left inter-actions-menu">
                                    <summary class="inline-flex items-center gap-1.5 px-3 py-1 bg-white border border-slate-200 rounded text-xs font-semibold text-slate-700 hover:bg-slate-50 cursor-pointer shadow-2xs list-none">
                                        <i data-lucide="user" class="w-3.5 h-3.5 text-blue-600"></i>
                                        Actions <i data-lucide="chevron-down" class="w-3 h-3 text-slate-500"></i>
                                    </summary>
                                    <div class="absolute right-0 mt-1 w-48 bg-white rounded-lg shadow-lg border border-slate-200 py-1 z-30">
                                        <button type="button" class="w-full text-left px-3 py-1.5 text-xs text-emerald-700 hover:bg-emerald-50 flex items-center gap-2 font-semibold" onclick="updateTransferStatus({{ $row['id'] }}, 'approved', '{{ $row['name'] }}')">
                                            <i data-lucide="check" class="w-3.5 h-3.5"></i>Approve Transfer
                                        </button>
                                        <button type="button" class="w-full text-left px-3 py-1.5 text-xs text-amber-700 hover:bg-amber-50 flex items-center gap-2" onclick="updateTransferStatus({{ $row['id'] }}, 'pending', '{{ $row['name'] }}')">
                                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>Send Back
                                        </button>
                                        <button type="button" class="w-full text-left px-3 py-1.5 text-xs text-red-700 hover:bg-red-50 flex items-center gap-2" onclick="updateTransferStatus({{ $row['id'] }}, 'rejected', '{{ $row['name'] }}')">
                                            <i data-lucide="x" class="w-3.5 h-3.5"></i>Reject Transfer
                                        </button>
                                        <div class="border-t border-slate-100 my-1"></div>
                                        <button type="button" class="w-full text-left px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50 flex items-center gap-2" onclick="openAppFormModal('{{ $row['id'] }}', '{{ $row['name'] }}', '{{ $row['current_programme'] }}', '{{ $row['transfer_programme'] }}', '{{ $row['reason'] }}', '{{ $row['email'] }}', '{{ $row['reg_no'] }}')">
                                            <i data-lucide="file-check-2" class="w-3.5 h-3.5 text-slate-400"></i>View Application
                                        </button>
                                        <button type="button" class="w-full text-left px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50 flex items-center gap-2" onclick="openDocPreviewModal('{{ $row['name'] }}', '{{ $row['reg_no'] }}')">
                                            <i data-lucide="file-text" class="w-3.5 h-3.5 text-slate-400"></i>Documents
                                        </button>
                                        <button type="button" class="w-full text-left px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50 flex items-center gap-2" onclick="openInterLogsModal('{{ $row['name'] }}', '{{ $row['reg_no'] }}')">
                                            <i data-lucide="history" class="w-3.5 h-3.5 text-slate-400"></i>View Logs
                                        </button>
                                    </div>
                                </details>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Table Footer Pagination Matching Screenshot 2 --}}
        <div class="flex flex-col sm:flex-row justify-between items-center px-4 py-3 bg-white border-t border-slate-100 text-xs text-slate-600 gap-3">
            <div>
                Showing 1 to 10 of 439 entries
            </div>

            <div class="flex items-center gap-1">
                <button type="button" class="px-2 py-1 text-slate-400 hover:text-slate-600 text-xs">Previous</button>
                <button type="button" class="px-2.5 py-1 rounded bg-orange-500 text-white font-bold text-xs">1</button>
                <button type="button" class="px-2.5 py-1 rounded hover:bg-slate-100 text-slate-700 text-xs">2</button>
                <button type="button" class="px-2.5 py-1 rounded hover:bg-slate-100 text-slate-700 text-xs">3</button>
                <button type="button" class="px-2.5 py-1 rounded hover:bg-slate-100 text-slate-700 text-xs">4</button>
                <button type="button" class="px-2.5 py-1 rounded hover:bg-slate-100 text-slate-700 text-xs">5</button>
                <span class="px-1 text-slate-400">..</span>
                <button type="button" class="px-2.5 py-1 rounded hover:bg-slate-100 text-slate-700 text-xs">44</button>
                <button type="button" class="px-2 py-1 text-slate-700 hover:text-slate-900 text-xs">Next</button>
            </div>
        </div>
    </div>

</div>

{{-- MODAL: STUDENT APPLICATION FORM VIEWER --}}
<div class="modal" id="app-form-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(680px, 94vw);">
        <div class="panel-head" style="background:var(--primary);color:#fff;padding:12px 18px;border-radius:7px 7px 0 0;">
            <div>
                <h2 class="text-sm font-bold text-white">Student Transfer Application Form</h2>
                <small style="color:rgba(255,255,255,0.85);" id="app-modal-sub">Official lodged transfer request.</small>
            </div>
            <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
        </div>
        <div class="panel-body p-4 text-xs space-y-4" style="max-height: 70vh; overflow-y: auto;" id="printable-app-form">
            <!-- Printable content wrapper -->
            <div class="border border-slate-200 rounded-lg p-4 bg-white shadow-2xs">
                <div class="flex justify-between items-start gap-4 flex-wrap">
                    <div class="flex items-center gap-3">
                        <div class="w-14 h-14 bg-orange-500 rounded-full flex items-center justify-center font-bold text-white text-lg">MEMA</div>
                        <div>
                            <h2 class="text-sm font-extrabold text-slate-800 uppercase tracking-tight">Mema College</h2>
                            <small class="text-[10px] text-slate-500 block">P.O. BOX 2440-00606 NAIROBI, KENYA</small>
                            <small class="text-[10px] text-slate-500 block">TELEPHONE: 0202000211/0202000212 • EMAIL: admissions@mema.ac.ke</small>
                        </div>
                    </div>
                    <!-- Photo placeholder -->
                    <div class="w-16 h-16 bg-slate-100 border border-slate-200 rounded overflow-hidden flex items-center justify-center">
                        <span class="text-slate-400 font-semibold text-[10px]">Photo</span>
                    </div>
                </div>
                
                <div class="text-center mt-3 pt-3 border-t border-slate-100">
                    <div class="font-extrabold text-slate-700 text-xs uppercase tracking-wider">Mema Admissions Office</div>
                    <div class="text-orange-500 font-extrabold text-[11px] uppercase tracking-widest mt-0.5">Student Application Online Form</div>
                </div>
            </div>

            <!-- (A) PERSONAL DETAILS -->
            <div class="border border-slate-200 rounded-lg bg-white shadow-2xs">
                <div class="bg-slate-50 px-3 py-2 font-bold text-slate-700 border-b border-slate-200 uppercase tracking-tight text-[11px]">(A) PERSONAL DETAILS</div>
                <div class="p-3 grid grid-cols-2 gap-3 text-[11px]">
                    <div><span class="font-semibold text-slate-500 block">First Name</span><strong class="text-slate-800" id="form-first-name">ABDIHAKIM</strong></div>
                    <div><span class="font-semibold text-slate-500 block">Middle Name</span><strong class="text-slate-800">—</strong></div>
                    <div><span class="font-semibold text-slate-500 block">Surname</span><strong class="text-slate-800" id="form-last-name">OMAR</strong></div>
                    <div><span class="font-semibold text-slate-500 block">Country of Residence</span><strong class="text-slate-800">Kenya</strong></div>
                    <div><span class="font-semibold text-slate-500 block">County</span><strong class="text-slate-800">Garissa</strong></div>
                    <div><span class="font-semibold text-slate-500 block">Sub County</span><strong class="text-slate-800">Ijara</strong></div>
                    <div><span class="font-semibold text-slate-500 block">Constituency</span><strong class="text-slate-800">IJARA</strong></div>
                    <div><span class="font-semibold text-slate-500 block">Mobile Number 1</span><strong class="text-slate-800">+254 724668806</strong></div>
                    <div><span class="font-semibold text-slate-500 block">Current Email</span><strong class="text-slate-800" id="form-email">ST63801212024@students.mema.ac.ke</strong></div>
                </div>
            </div>

            <!-- (B) BIOGRAPHICAL INFORMATION -->
            <div class="border border-slate-200 rounded-lg bg-white shadow-2xs">
                <div class="bg-slate-50 px-3 py-2 font-bold text-slate-700 border-b border-slate-200 uppercase tracking-tight text-[11px]">(B) BIOGRAPHICAL INFORMATION</div>
                <div class="p-3 grid grid-cols-2 gap-3 text-[11px]">
                    <div><span class="font-semibold text-slate-500 block">Gender</span><strong class="text-slate-800">Male</strong></div>
                    <div><span class="font-semibold text-slate-500 block">Date of Birth</span><strong class="text-slate-800">1997-02-01</strong></div>
                    <div><span class="font-semibold text-slate-500 block">Marital Status</span><strong class="text-slate-800">Single</strong></div>
                    <div><span class="font-semibold text-slate-500 block">Birth Country</span><strong class="text-slate-800">Kenya</strong></div>
                    <div><span class="font-semibold text-slate-500 block">Orphan Status</span><strong class="text-slate-800">Do Not Wish To Disclose</strong></div>
                    <div><span class="font-semibold text-slate-500 block">Disability Status</span><strong class="text-slate-800">None</strong></div>
                </div>
            </div>

            <!-- (D) PREVIOUS ACADEMIC DETAILS -->
            <div class="border border-slate-200 rounded-lg bg-white shadow-2xs">
                <div class="bg-slate-50 px-3 py-2 font-bold text-slate-700 border-b border-slate-200 uppercase tracking-tight text-[11px]">(D) PREVIOUS ACADEMIC DETAILS</div>
                <div class="p-3 grid grid-cols-2 gap-3 text-[11px]">
                    <div><span class="font-semibold text-slate-500 block">Current Enrolled Programme</span><strong class="text-slate-800" id="form-curr-prog">ST61 - Master of Data Science</strong></div>
                    <div><span class="font-semibold text-slate-500 block">Requested Transfer Destination</span><strong class="text-slate-800 text-orange-600" id="form-dest-prog">ST63 - Master of Science in Cybersecurity</strong></div>
                </div>
            </div>

            <!-- (E) NEXT OF KIN / EMERGENCY CONTACT -->
            <div class="border border-slate-200 rounded-lg bg-white shadow-2xs">
                <div class="bg-slate-50 px-3 py-2 font-bold text-slate-700 border-b border-slate-200 uppercase tracking-tight text-[11px]">(E) NEXT OF KIN / EMERGENCY CONTACT</div>
                <div class="p-3 grid grid-cols-2 gap-3 text-[11px]">
                    <div><span class="font-semibold text-slate-500 block">Full Name</span><strong class="text-slate-800">nimo mohamed</strong></div>
                    <div><span class="font-semibold text-slate-500 block">Relationship</span><strong class="text-slate-800">Spouse</strong></div>
                    <div><span class="font-semibold text-slate-500 block">Telephone</span><strong class="text-slate-800">+254 724505885</strong></div>
                    <div><span class="font-semibold text-slate-500 block">County / Country</span><strong class="text-slate-800">Nairobi / Kenya</strong></div>
                </div>
            </div>

            <!-- (F) REFERRAL -->
            <div class="border border-slate-200 rounded-lg bg-white shadow-2xs">
                <div class="bg-slate-50 px-3 py-2 font-bold text-slate-700 border-b border-slate-200 uppercase tracking-tight text-[11px]">(F) REFERRAL & TRANSFER REASON</div>
                <div class="p-3 text-[11px]">
                    <div><span class="font-semibold text-slate-500 block">Stated Reason for Transfer</span><strong class="text-slate-800" id="form-reason">Change of Preference</strong></div>
                    <div class="mt-2"><span class="font-semibold text-slate-500 block">Referred By</span><strong class="text-slate-800">Friend / MEMA Staff or Student</strong></div>
                </div>
            </div>
        </div>
        <div class="panel-foot flex justify-between items-center p-3 border-t border-slate-200 bg-slate-50 rounded-b-lg">
            <div class="flex gap-2">
                <button type="button" class="btn btn-secondary text-xs" data-modal-close>Close</button>
                <button type="button" class="px-3 py-1.5 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-bold text-xs" onclick="printApplicationForm()">Download</button>
            </div>
            <div class="flex gap-2">
                <button type="button" class="px-3 py-1.5 rounded bg-red-600 text-white font-bold text-xs" onclick="updateTransferStatus(activeTransferId, 'rejected', activeTransferName)">Reject</button>
                <button type="button" class="px-3 py-1.5 rounded bg-emerald-600 text-white font-bold text-xs" onclick="updateTransferStatus(activeTransferId, 'approved', activeTransferName)">Endorse Transfer</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL: DOCUMENTS VIEWER --}}
<div class="modal" id="doc-preview-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(520px, 94vw);">
        <div class="panel-head" style="background:var(--primary);color:#fff;padding:12px 18px;border-radius:7px 7px 0 0;">
            <div>
                <h2 class="text-sm font-bold text-white">Student Credentials & Cluster Points</h2>
                <small style="color:rgba(255,255,255,0.85);" id="doc-modal-sub">Certified KCSE / Degree Transcripts.</small>
            </div>
            <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
        </div>
        <div class="panel-body p-5 text-xs space-y-3">
            <div class="p-3 border border-slate-200 rounded-lg flex items-center justify-between bg-slate-50">
                <div>
                    <div class="font-bold text-slate-800">KCSE_Certificate_Verified.pdf</div>
                    <small class="text-slate-400">Mean Grade: A- (78 Points) • Cluster Qualified</small>
                </div>
                <button type="button" class="px-2.5 py-1 bg-white border border-slate-200 rounded font-semibold text-slate-700 hover:bg-slate-50" onclick="triggerActionAlert('info', 'Document Downloaded', 'KCSE_Certificate_Verified.pdf downloaded.')">Download</button>
            </div>
            <div class="flex justify-end pt-3 border-t border-slate-100">
                <button type="button" class="btn btn-secondary text-xs" data-modal-close>Close</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL: AUDIT TRAIL --}}
<div class="modal" id="inter-logs-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(580px, 94vw);">
        <div class="panel-head" style="background:var(--primary);color:#fff;padding:12px 18px;border-radius:7px 7px 0 0;">
            <div>
                <h2 class="text-sm font-bold text-white">Inter/Intra Transfer Audit Trail</h2>
                <small style="color:rgba(255,255,255,0.85);" id="inter-logs-sub">Review history & authenticated user timeline.</small>
            </div>
            <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
        </div>
        <div class="panel-body p-5 text-xs">
            <div class="relative pl-6 space-y-4 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                
                <div class="relative">
                    <span class="absolute -left-[27px] top-1 w-3 h-3 rounded-full bg-emerald-500 ring-4 ring-white"></span>
                    <div class="flex items-center justify-between flex-wrap gap-1">
                        <span class="font-bold text-slate-900">Transfer Lodged</span>
                        <span class="text-[11px] text-slate-400 font-mono">15 Jul 2026, 11:05 AM</span>
                    </div>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="px-1.5 py-0.5 rounded text-[10.5px] font-bold bg-blue-100 text-blue-800">Student User</span>
                        <strong class="text-slate-800" id="inter-actor-student">Abdelhakim Omar (ST63/80121/2024)</strong>
                    </div>
                    <p class="text-slate-500 mt-1">Submitted inter-school transfer request from Data Science to Cybersecurity.</p>
                </div>

                <div class="relative">
                    <span class="absolute -left-[27px] top-1 w-3 h-3 rounded-full bg-[#0A3E50] ring-4 ring-white"></span>
                    <div class="flex items-center justify-between flex-wrap gap-1">
                        <span class="font-bold text-slate-900">Releasing Dean Endorsement</span>
                        <span class="text-[11px] text-slate-400 font-mono">18 Jul 2026, 02:14 PM</span>
                    </div>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="px-1.5 py-0.5 rounded text-[10.5px] font-bold bg-slate-100 text-[#0A3E50]">Dean (School of Origin)</span>
                        <strong class="text-slate-800">Receiving dean (from case record)</strong>
                    </div>
                    <p class="text-slate-500 mt-1">Dean approved release; no academic or fee arrears noted.</p>
                </div>

                <div class="relative">
                    <span class="absolute -left-[27px] top-1 w-3 h-3 rounded-full bg-emerald-500 ring-4 ring-white"></span>
                    <div class="flex items-center justify-between flex-wrap gap-1">
                        <span class="font-bold text-emerald-800">Receiving Dean & Registrar Admission</span>
                        <span class="text-[11px] text-slate-400 font-mono">20 Jul 2026, 09:30 AM</span>
                    </div>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="px-1.5 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">Academic Registrar</span>
                        <strong class="text-slate-800">Academic Registrar (from case record)</strong>
                    </div>
                    <p class="text-slate-500 mt-1">Capacity verified (Quota Available: 48 seats). New registration number allocated.</p>
                </div>

            </div>
            <div class="flex justify-end pt-4 border-t border-slate-100 mt-4">
                <button type="button" class="btn btn-secondary text-xs" data-modal-close>Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleWorkflowGuide() {
        const guide = document.getElementById('admin-workflow-guide');
        const btnText = document.getElementById('workflow-toggle-btn-text');
        if (guide) {
            const isHidden = guide.classList.contains('hidden');
            guide.classList.toggle('hidden', !isHidden);
            btnText.textContent = isHidden ? 'Hide Workflow Guide' : 'Show Workflow Guide';
        }
    }

    function triggerActionAlert(type, title, message) {
        const box = document.getElementById('transfer-alert-box');
        const icon = document.getElementById('alert-icon');
        const titleEl = document.getElementById('alert-title');
        const msgEl = document.getElementById('alert-message');

        titleEl.textContent = title;
        msgEl.textContent = message;

        box.className = 'mb-4 p-3.5 rounded-xl border text-xs font-semibold flex items-start justify-between gap-3 shadow-sm transition-all';

        if (type === 'success') {
            box.classList.add('bg-emerald-50', 'text-emerald-900', 'border-emerald-200');
            icon.setAttribute('data-lucide', 'check-circle-2');
            icon.className = 'w-4 h-4 text-emerald-600 mt-0.5 flex-shrink-0';
        } else if (type === 'warning') {
            box.classList.add('bg-amber-50', 'text-amber-900', 'border-amber-200');
            icon.setAttribute('data-lucide', 'alert-triangle');
            icon.className = 'w-4 h-4 text-amber-600 mt-0.5 flex-shrink-0';
        } else if (type === 'error') {
            box.classList.add('bg-red-50', 'text-red-900', 'border-red-200');
            icon.setAttribute('data-lucide', 'alert-circle');
            icon.className = 'w-4 h-4 text-red-600 mt-0.5 flex-shrink-0';
        } else {
            box.classList.add('bg-blue-50', 'text-blue-900', 'border-blue-200');
            icon.setAttribute('data-lucide', 'info');
            icon.className = 'w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0';
        }

        box.classList.remove('hidden');
        lucide.createIcons();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function dismissAlert() {
        document.getElementById('transfer-alert-box').classList.add('hidden');
    }

    let activeTransferId = null;
    let activeTransferName = '';

    function openAppFormModal(id, name, curr, dest, reason, email, regNo) {
        activeTransferId = id;
        activeTransferName = name;

        // Split name into first and last name
        const nameParts = name.trim().split(/\s+/);
        const firstName = nameParts[0] || '';
        const lastName = nameParts.slice(1).join(' ') || '—';

        document.getElementById('form-first-name').textContent = firstName;
        document.getElementById('form-last-name').textContent = lastName;
        document.getElementById('form-email').textContent = email;
        document.getElementById('form-curr-prog').textContent = curr;
        document.getElementById('form-dest-prog').textContent = dest;
        document.getElementById('form-reason').textContent = reason;

        document.getElementById('app-modal-sub').textContent = name + ' • ' + regNo;
        document.getElementById('app-form-modal').classList.add('open');
    }

    function updateTransferStatus(id, newStatus, name) {
        if (!id) {
            triggerActionAlert('error', 'Missing record', 'Save the transfer to the database before changing status.');
            return;
        }
        const statusLabel = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = @json(url('/transfers/inter-intra')) + '/' + id + '/status';
        form.innerHTML = `
            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
            <input type="hidden" name="_method" value="PATCH">
            <input type="hidden" name="status" value="${statusLabel}">
            <input type="hidden" name="status_type" value="${newStatus}">
        `;
        document.body.appendChild(form);
        form.submit();
    }

    function printApplicationForm() {
        const printWindow = window.open('', '_blank', 'width=800,height=900');
        const content = document.getElementById('printable-app-form').innerHTML;
        const html = `
            <html>
            <head>
                <title>Student Application Form - ${activeTransferName}</title>
                <style>
                    body { font-family: sans-serif; padding: 40px; color: #1e293b; font-size: 13px; line-height: 1.5; }
                    .border { border: 1px solid #cbd5e1; }
                    .rounded-lg { border-radius: 8px; }
                    .p-4 { padding: 16px; }
                    .p-3 { padding: 12px; }
                    .px-3 { padding-left: 12px; padding-right: 12px; }
                    .py-2 { padding-top: 8px; padding-bottom: 8px; }
                    .bg-white { background-color: #fff; }
                    .bg-slate-50 { background-color: #f8fafc; }
                    .border-b { border-bottom: 1px solid #e2e8f0; }
                    .border-t { border-top: 1px solid #e2e8f0; }
                    .mt-3 { margin-top: 12px; }
                    .pt-3 { padding-top: 12px; }
                    .mt-2 { margin-top: 8px; }
                    .font-extrabold { font-weight: 800; }
                    .font-bold { font-weight: 700; }
                    .font-semibold { font-weight: 600; }
                    .text-slate-800 { color: #1e293b; }
                    .text-slate-700 { color: #334155; }
                    .text-slate-500 { color: #64748b; }
                    .text-orange-500 { color: #f97316; }
                    .text-orange-600 { color: #ea580c; }
                    .text-xs { font-size: 12px; }
                    .text-sm { font-size: 14px; }
                    .uppercase { text-transform: uppercase; }
                    .flex { display: flex; }
                    .justify-between { justify-content: space-between; }
                    .items-start { align-items: flex-start; }
                    .items-center { align-items: center; }
                    .gap-3 { gap: 12px; }
                    .gap-4 { gap: 16px; }
                    .w-14 { width: 56px; }
                    .h-14 { height: 56px; }
                    .w-16 { width: 64px; }
                    .h-16 { height: 64px; }
                    .rounded-full { border-radius: 9999px; }
                    .flex-wrap { flex-wrap: wrap; }
                    .text-center { text-align: center; }
                    .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
                    .block { display: block; }
                    .text-\\[10px\\] { font-size: 10px; }
                    .text-\\[11px\\] { font-size: 11px; }
                    .shadow-2xs { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); }
                    @media print {
                        body { padding: 0; }
                        button { display: none !important; }
                    }
                </style>
            </head>
            <body>
                ${content}
                <script>
                    window.onload = function() {
                        window.print();
                        window.close();
                    };
                <\/script>
            </body>
            </html>
        `;
        printWindow.document.write(html);
        printWindow.document.close();
    }

    function openDocPreviewModal(name, regNo) {
        document.getElementById('doc-modal-sub').textContent = `${name} • ${regNo}`;
        document.getElementById('doc-preview-modal').classList.add('open');
    }

    function openInterLogsModal(name, regNo) {
        document.getElementById('inter-logs-sub').textContent = `${name} • ${regNo}`;
        document.getElementById('inter-actor-student').textContent = `${name} (${regNo})`;
        document.getElementById('inter-logs-modal').classList.add('open');
    }

    const statusFilter = document.getElementById('status-filter-select');
    const searchInput = document.getElementById('inter-search');
    const rows = document.querySelectorAll('.transfer-row');

    function filterRows() {
        const q = searchInput?.value.toLowerCase().trim() || '';
        const status = statusFilter?.value.toLowerCase() || '';

        rows.forEach(row => {
            const text = row.dataset.search || '';
            const rowStatus = row.dataset.status || '';

            const matchesSearch = !q || text.includes(q);
            const matchesStatus = !status || rowStatus === status;

            row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        searchInput?.addEventListener('input', filterRows);
        statusFilter?.addEventListener('change', filterRows);

        document.addEventListener('click', (e) => {
            document.querySelectorAll('.inter-actions-menu[open]').forEach(menu => {
                if (!menu.contains(e.target)) menu.removeAttribute('open');
            });
        });
    });
</script>
@endsection

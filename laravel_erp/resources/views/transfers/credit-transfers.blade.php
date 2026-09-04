@extends('layouts.app')

@section('title', 'Credit Transfers')

@section('content')
<div class="ouk-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Credit Transfers</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">View and manage student credit transfer requests</p>
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
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wide">Credit Articulation & Course Exemption Rules (Admin Perspective)</h3>
            </div>
            <span class="text-[11px] font-semibold text-slate-800 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">KNEC / Prior University Articulation</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs">
            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-emerald-700 mb-1">
                    <i data-lucide="award" class="w-4 h-4 text-emerald-600"></i> Course Equivalence
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Prior course units must demonstrate minimum 80% content coverage match against MEMA ERP syllabus descriptors.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-amber-700 mb-1">
                    <i data-lucide="user-x" class="w-4 h-4 text-amber-600"></i> Unassigned Queue
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Applications awaiting allocation to subject-matter academic reviewers. Admin must allocate reviewers to clear backlog.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-blue-700 mb-1">
                    <i data-lucide="receipt" class="w-4 h-4 text-blue-600"></i> Fee Credit Offset
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Upon approval, the finance ledger automatically discounts the credit unit cost from the student's semester invoice.
                </p>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200/80">
                <div class="flex items-center gap-1.5 font-bold text-slate-800 mb-1">
                    <i data-lucide="scroll-text" class="w-4 h-4 text-[#0A3E50]"></i> Senate Board Sanction
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Approved transfer credits are compiled into the official graduation clearance register for Senate ratification.
                </p>
            </div>
        </div>
    </div>

    {{-- Top 4 KPI Cards Matching Screenshot 4 --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        
        {{-- Card 1: Total Requests --}}
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Total Requests</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ number_format($stats['totalRequests']) }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Submitted credit transfer applications.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">{{ $stats['totalRate'] }}</span>
            </div>
        </div>

        {{-- Card 2: Pending Approvals --}}
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Pending Approvals</div>
            <div class="flex items-baseline gap-2 mt-2 mb-1.5 leading-none">
                <span class="text-3xl font-extrabold text-slate-900">{{ number_format($stats['pendingApprovals']) }}</span>
                <span class="px-2 py-0.5 rounded text-[11px] font-semibold text-slate-600 bg-slate-100 border border-slate-200/70">{{ $stats['unassignedPending'] }} unassigned</span>
            </div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Awaiting review or verification.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">{{ $stats['pendingRate'] }}</span>
            </div>
        </div>

        {{-- Card 3: Approved Transfers --}}
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Approved Transfers</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ number_format($stats['approvedTransfers']) }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Successfully validated and accepted.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">{{ $stats['approvedRate'] }}</span>
            </div>
        </div>

        {{-- Card 4: Rejected Requests --}}
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Rejected Requests</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ number_format($stats['rejectedRequests']) }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Current assigned courses.</p>
            <div>
                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">{{ $stats['rejectedRate'] }}</span>
            </div>
        </div>

    </div>

    {{-- Filter Dropdown & Controls --}}
    <div class="mb-4">
        <div class="w-full sm:w-64 mb-3">
            <select id="status-filter-select" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-medium text-slate-700 focus:outline-none focus:border-[#0A3E50] shadow-2xs">
                <option value="">Select Status</option>
                <option value="approved">Approved</option>
                <option value="sentback">Instructor sentback</option>
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
                <label for="credit-search">Search:</label>
                <input type="text" id="credit-search" class="bg-white border border-slate-300 rounded-md px-3 py-1 text-xs text-slate-800 focus:outline-none focus:border-[#0A3E50] w-48 sm:w-60">
            </div>
        </div>
    </div>

    {{-- Data Table Matching Screenshot 4 --}}
    <div class="bg-white border border-slate-200/90 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="credit-table">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80">
                        <th class="py-3 px-4 text-[11px] font-bold text-slate-600 uppercase tracking-wider">Full Name</th>
                        <th class="py-3 px-4 text-[11px] font-bold text-slate-600 uppercase tracking-wider">Course</th>
                        <th class="py-3 px-4 text-[11px] font-bold text-slate-600 uppercase tracking-wider">Registration Status</th>
                        <th class="py-3 px-4 text-[11px] font-bold text-slate-600 uppercase tracking-wider text-right w-32">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="credit-tbody">
                    @foreach($creditEntries as $row)
                        <tr class="hover:bg-slate-50/60 transition-colors credit-row" data-status="{{ strtolower($row['status']) }}" data-search="{{ strtolower($row['name'].' '.$row['admission_number'].' '.$row['course_code'].' '.$row['course_name']) }}">
                            
                            {{-- Full Name & Admission Number --}}
                            <td class="py-3.5 px-4">
                                <div class="text-xs font-bold text-slate-900 tracking-tight">{{ $row['name'] }}</div>
                                <div class="mt-1">
                                    <span class="inline-block px-2 py-0.5 rounded text-[11px] font-medium text-slate-600 bg-slate-100 border border-slate-200/60 font-mono">
                                        {{ $row['admission_number'] }}
                                    </span>
                                </div>
                            </td>

                            {{-- Course & Programme --}}
                            <td class="py-3.5 px-4">
                                <div class="text-xs font-bold text-slate-800">
                                    {{ $row['course_code'] }} - {{ $row['course_name'] }}
                                </div>
                                <div class="mt-1">
                                    <span class="inline-block px-2 py-0.5 rounded text-[11px] font-medium text-slate-600 bg-slate-100 border border-slate-200/60">
                                        {{ $row['programme_code'] }} - {{ $row['programme_name'] }}
                                    </span>
                                </div>
                            </td>

                            {{-- Registration Status --}}
                            <td class="py-3.5 px-4">
                                @php
                                    $badgeClass = match($row['status_type']) {
                                        'approved' => 'bg-[#dcfce7] text-[#15803d] border-[#bbf7d0]',
                                        'sentback' => 'bg-transparent text-slate-700 border-none font-semibold',
                                        default => 'bg-[#fef3c7] text-[#b45309] border-[#fde68a]',
                                    };
                                @endphp
                                <span class="inline-block {{ $badgeClass }} text-xs">
                                    {{ $row['status'] }}
                                </span>
                            </td>

                            {{-- Actions Dropdown --}}
                            <td class="py-3.5 px-4 text-right">
                                <details class="relative inline-block text-left credit-actions-menu">
                                    <summary class="inline-flex items-center gap-1.5 px-3 py-1 bg-white border border-slate-200 rounded text-xs font-semibold text-slate-700 hover:bg-slate-50 cursor-pointer shadow-2xs list-none">
                                        <i data-lucide="user" class="w-3.5 h-3.5 text-blue-600"></i>
                                        Actions <i data-lucide="chevron-down" class="w-3 h-3 text-slate-500"></i>
                                    </summary>
                                    <div class="absolute right-0 mt-1 w-44 bg-white rounded-lg shadow-lg border border-slate-200 py-1 z-30">
                                        <button type="button" class="w-full text-left px-3 py-1.5 text-xs text-emerald-700 hover:bg-emerald-50 flex items-center gap-2 font-semibold" onclick="persistRecordStatus({{ $row['id'] ?? 'null' }}, 'Approved')">
                                            <i data-lucide="check" class="w-3.5 h-3.5"></i>Approve Credit
                                        </button>
                                        <button type="button" class="w-full text-left px-3 py-1.5 text-xs text-amber-700 hover:bg-amber-50 flex items-center gap-2" onclick="persistRecordStatus({{ $row['id'] ?? 'null' }}, 'Pending')">
                                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>Send Back
                                        </button>
                                        <button type="button" class="w-full text-left px-3 py-1.5 text-xs text-red-700 hover:bg-red-50 flex items-center gap-2" onclick="persistRecordStatus({{ $row['id'] ?? 'null' }}, 'Rejected')">
                                            <i data-lucide="x" class="w-3.5 h-3.5"></i>Reject Transfer
                                        </button>
                                        <div class="border-t border-slate-100 my-1"></div>
                                        <button type="button" class="w-full text-left px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50 flex items-center gap-2" onclick="openCreditAppModal('{{ $row['name'] }}', '{{ $row['admission_number'] }}', '{{ $row['course_code'] }}', '{{ $row['course_name'] }}', '{{ $row['programme_name'] }}')">
                                            <i data-lucide="file-check-2" class="w-3.5 h-3.5 text-slate-400"></i>View Application
                                        </button>
                                        <button type="button" class="w-full text-left px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50 flex items-center gap-2" onclick="openCreditDocsModal('{{ $row['name'] }}', '{{ $row['course_code'] }}')">
                                            <i data-lucide="file-text" class="w-3.5 h-3.5 text-slate-400"></i>Documents
                                        </button>
                                        <button type="button" class="w-full text-left px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50 flex items-center gap-2" onclick="openCreditLogsModal('{{ $row['name'] }}', '{{ $row['course_code'] }}')">
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

        {{-- Table Footer Pagination Matching Screenshot 4 --}}
        <div class="flex flex-col sm:flex-row justify-between items-center px-4 py-3 bg-white border-t border-slate-100 text-xs text-slate-600 gap-3">
            <div>
                Showing 1 to 10 of 206 entries
            </div>

            <div class="flex items-center gap-1">
                <button type="button" class="px-2 py-1 text-slate-400 hover:text-slate-600 text-xs">Previous</button>
                <button type="button" class="px-2.5 py-1 rounded bg-orange-500 text-white font-bold text-xs">1</button>
                <button type="button" class="px-2.5 py-1 rounded hover:bg-slate-100 text-slate-700 text-xs">2</button>
                <button type="button" class="px-2.5 py-1 rounded hover:bg-slate-100 text-slate-700 text-xs">3</button>
                <button type="button" class="px-2.5 py-1 rounded hover:bg-slate-100 text-slate-700 text-xs">4</button>
                <button type="button" class="px-2.5 py-1 rounded hover:bg-slate-100 text-slate-700 text-xs">5</button>
                <span class="px-1 text-slate-400">..</span>
                <button type="button" class="px-2.5 py-1 rounded hover:bg-slate-100 text-slate-700 text-xs">21</button>
                <button type="button" class="px-2 py-1 text-slate-700 hover:text-slate-900 text-xs">Next</button>
            </div>
        </div>
    </div>

</div>

{{-- MODAL 1: VIEW APPLICATION --}}
<div class="modal" id="credit-app-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(580px, 94vw);">
        <div class="panel-head" style="background:var(--primary);color:#fff;padding:12px 18px;border-radius:7px 7px 0 0;">
            <div>
                <h2 class="text-sm font-bold text-white">Student Credit Exemption Application</h2>
                <small style="color:rgba(255,255,255,0.85);" id="credit-app-sub">Course articulation and transcript verification details.</small>
            </div>
            <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
        </div>
        <div class="panel-body p-5 text-xs space-y-3.5">
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg flex justify-between items-center">
                <div>
                    <div class="text-[11px] text-slate-500 font-semibold">Student Name & Admission</div>
                    <div class="font-bold text-slate-900 text-xs" id="credit-app-student"></div>
                </div>
                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-[#0A3E50]" id="credit-app-prog"></span>
            </div>

            <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg">
                <div class="text-[11px] text-slate-600 font-semibold">Course Unit Requested for Exemption</div>
                <div class="font-bold text-slate-900 text-xs mt-0.5" id="credit-app-course"></div>
                <div class="text-slate-600 text-[11px] mt-1">Equivalence Score: <strong class="text-emerald-700">86.4% Syllabus Overlap (Pass >= 80%)</strong></div>
            </div>

            <div class="p-3 border border-slate-200 rounded-lg bg-slate-50 space-y-1.5">
                <div class="text-[11px] text-slate-500 font-semibold">Prior Institution & Credential</div>
                <div class="text-slate-800 font-medium">KNEC Diploma in Information Technology • Certified Grade: <strong>Credit</strong></div>
                <div class="text-slate-500 text-[11px]">Academic Credit Hours Transferred: <strong>3.0 Credit Units</strong></div>
            </div>

            <div class="flex justify-between items-center pt-3 border-t border-slate-100">
                <button type="button" class="btn btn-secondary text-xs" data-modal-close>Close</button>
                <div class="flex gap-2">
                    <button type="button" class="px-3 py-1.5 rounded bg-red-600 text-white font-bold text-xs" onclick="document.getElementById('credit-app-modal').classList.remove('open'); triggerActionAlert('error', 'Credit Transfer Rejected', 'Application formally rejected due to unit mismatch.');">Reject</button>
                    <button type="button" class="px-3 py-1.5 rounded bg-emerald-600 text-white font-bold text-xs" onclick="document.getElementById('credit-app-modal').classList.remove('open'); triggerActionAlert('success', 'Credit Transfer Validated', 'Application approved. Credit units registered in student record.');">Approve Exemption</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL 2: DOCUMENTS VIEWER --}}
<div class="modal" id="credit-docs-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(540px, 94vw);">
        <div class="panel-head" style="background:var(--primary);color:#fff;padding:12px 18px;border-radius:7px 7px 0 0;">
            <div>
                <h2 class="text-sm font-bold text-white">Credit Exemption Dossier</h2>
                <small style="color:rgba(255,255,255,0.85);" id="credit-docs-sub">Certified transcripts and institutional course outlines.</small>
            </div>
            <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
        </div>
        <div class="panel-body p-5 text-xs space-y-3">
            <div class="p-3 border border-slate-200 rounded-lg flex items-center justify-between bg-slate-50">
                <div class="flex items-center gap-3">
                    <i data-lucide="file-check" class="w-5 h-5 text-[#0A3E50]"></i>
                    <div>
                        <div class="text-xs font-bold text-slate-800">Certified_Academic_Transcript.pdf</div>
                        <small class="text-slate-400">Prior College Official Seal (2.1 MB)</small>
                    </div>
                </div>
                <button type="button" class="px-2.5 py-1 bg-white border border-slate-200 rounded font-semibold text-slate-700 hover:bg-slate-50" onclick="triggerActionAlert('info', 'Document Downloaded', 'Certified_Academic_Transcript.pdf downloaded.')">Download</button>
            </div>

            <div class="p-3 border border-slate-200 rounded-lg flex items-center justify-between bg-slate-50">
                <div class="flex items-center gap-3">
                    <i data-lucide="file-text" class="w-5 h-5 text-blue-700"></i>
                    <div>
                        <div class="text-xs font-bold text-slate-800">Prior_Course_Syllabus_Descriptor.pdf</div>
                        <small class="text-slate-400">Certified Unit Content & Lab Modules (1.3 MB)</small>
                    </div>
                </div>
                <button type="button" class="px-2.5 py-1 bg-white border border-slate-200 rounded font-semibold text-slate-700 hover:bg-slate-50" onclick="triggerActionAlert('info', 'Document Downloaded', 'Prior_Course_Syllabus_Descriptor.pdf downloaded.')">Download</button>
            </div>

            <div class="flex justify-end pt-3 border-t border-slate-100">
                <button type="button" class="btn btn-secondary text-xs" data-modal-close>Close</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL 3: VIEW LOGS --}}
<div class="modal" id="credit-logs-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(580px, 94vw);">
        <div class="panel-head" style="background:var(--primary);color:#fff;padding:12px 18px;border-radius:7px 7px 0 0;">
            <div>
                <h2 class="text-sm font-bold text-white">Credit Exemption Audit Trail</h2>
                <small style="color:rgba(255,255,255,0.85);" id="credit-logs-sub">Review history & authenticated actor progression.</small>
            </div>
            <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
        </div>
        <div class="panel-body p-5">
            <div class="relative pl-6 space-y-4 text-xs before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                
                {{-- Step 1 --}}
                <div class="relative">
                    <span class="absolute -left-[27px] top-1 w-3 h-3 rounded-full bg-emerald-500 ring-4 ring-white"></span>
                    <div class="flex items-center justify-between flex-wrap gap-1">
                        <span class="font-bold text-slate-900">Application Submitted</span>
                        <span class="text-[11px] text-slate-400 font-mono">18 Aug 2026, 10:20 AM</span>
                    </div>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="px-1.5 py-0.5 rounded text-[10.5px] font-bold bg-blue-100 text-blue-800">Student User</span>
                        <strong class="text-slate-800">Jared Orwa (BE01/56068/2023)</strong>
                    </div>
                    <p class="text-slate-500 mt-1 leading-relaxed">Lodged credit transfer application with KNEC diploma transcript & syllabus outline.</p>
                </div>

                {{-- Step 2 --}}
                <div class="relative">
                    <span class="absolute -left-[27px] top-1 w-3 h-3 rounded-full bg-[#0A3E50] ring-4 ring-white"></span>
                    <div class="flex items-center justify-between flex-wrap gap-1">
                        <span class="font-bold text-slate-900">Instructor Review Assigned</span>
                        <span class="text-[11px] text-slate-400 font-mono">20 Aug 2026, 03:15 PM</span>
                    </div>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="px-1.5 py-0.5 rounded text-[10.5px] font-bold bg-slate-100 text-[#0A3E50]">Admissions Desk</span>
                        <strong class="text-slate-800">Esther Ndung'u (Senior Admissions Officer)</strong>
                    </div>
                    <p class="text-slate-500 mt-1 leading-relaxed">Assigned to Department of Economics & Statistics for curriculum equivalence audit.</p>
                </div>

                {{-- Step 3 --}}
                <div class="relative">
                    <span class="absolute -left-[27px] top-1 w-3 h-3 rounded-full bg-amber-500 ring-4 ring-white"></span>
                    <div class="flex items-center justify-between flex-wrap gap-1">
                        <span class="font-bold text-amber-800">Instructor Sent Back</span>
                        <span class="text-[11px] text-slate-400 font-mono">22 Aug 2026, 01:40 PM</span>
                    </div>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="px-1.5 py-0.5 rounded text-[10.5px] font-bold bg-amber-100 text-amber-800">Department Evaluator</span>
                        <strong class="text-slate-800">Course examiner (from case record)</strong>
                    </div>
                    <p class="text-slate-600 mt-1 p-2 bg-amber-50/60 border border-amber-200/60 rounded italic text-[11px]">
                        "Please attach certified course syllabus breakdown for Level 100 equivalence match."
                    </p>
                </div>

                {{-- Step 4 --}}
                <div class="relative">
                    <span class="absolute -left-[27px] top-1 w-3 h-3 rounded-full bg-emerald-500 ring-4 ring-white"></span>
                    <div class="flex items-center justify-between flex-wrap gap-1">
                        <span class="font-bold text-emerald-800">Dean Endorsement</span>
                        <span class="text-[11px] text-slate-400 font-mono">24 Aug 2026, 04:10 PM</span>
                    </div>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="px-1.5 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">Dean of Faculty</span>
                        <strong class="text-slate-800">School board chair (from case record)</strong>
                    </div>
                    <p class="text-slate-500 mt-1 leading-relaxed">Dean validated 86.4% syllabus match. Forwarded for Senate Registry ratification.</p>
                </div>

            </div>
            <div class="flex justify-end mt-5 pt-3 border-t border-slate-100">
                <button type="button" class="btn btn-secondary text-xs" data-modal-close>Close</button>
            </div>
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

    function persistRecordStatus(id, status) {
        if (!id) {
            triggerActionAlert('error', 'Missing record', 'Save the credit transfer to the database before changing status.');
            return;
        }
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = @json(url('/transfers/credit-transfers')) + '/' + id + '/status';
        form.innerHTML = `
            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
            <input type="hidden" name="_method" value="PATCH">
            <input type="hidden" name="status" value="${status}">
            <input type="hidden" name="status_type" value="${String(status).toLowerCase()}">
        `;
        document.body.appendChild(form);
        form.submit();
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

    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('credit-search');
        const statusSelect = document.getElementById('status-filter-select');
        const rows = document.querySelectorAll('.credit-row');

        function filterRows() {
            const query = (searchInput?.value || '').toLowerCase().trim();
            const status = (statusSelect?.value || '').toLowerCase().trim();

            rows.forEach(row => {
                const rowSearch = row.dataset.search || '';
                const rowStatus = row.dataset.status || '';

                const matchesQuery = !query || rowSearch.includes(query);
                const matchesStatus = !status || rowStatus.includes(status);

                row.style.display = (matchesQuery && matchesStatus) ? '' : 'none';
            });
        }

        searchInput?.addEventListener('input', filterRows);
        statusSelect?.addEventListener('change', filterRows);

        document.addEventListener('click', (e) => {
            document.querySelectorAll('.credit-actions-menu[open]').forEach(menu => {
                if (!menu.contains(e.target)) menu.removeAttribute('open');
            });
        });
    });

    function openCreditAppModal(name, adm, code, courseName, prog) {
        document.getElementById('credit-app-student').textContent = `${name} (${adm})`;
        document.getElementById('credit-app-prog').textContent = prog;
        document.getElementById('credit-app-course').textContent = `${code} - ${courseName}`;
        document.getElementById('credit-app-modal').classList.add('open');
    }

    function openCreditDocsModal(name, code) {
        document.getElementById('credit-docs-sub').textContent = `${name} • ${code}`;
        document.getElementById('credit-docs-modal').classList.add('open');
    }

    function openCreditLogsModal(name, code) {
        document.getElementById('credit-logs-sub').textContent = `${name} • ${code}`;
        document.getElementById('credit-logs-modal').classList.add('open');
    }
</script>
@endsection

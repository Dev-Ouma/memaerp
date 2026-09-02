@extends('layouts.app')

@section('title', 'Exemptions')

@section('content')
<div class="mema-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Exemptions</h1>
            <p class="text-xs text-slate-500 mt-1 font-medium">View and manage student exemptions requests</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="toggleWorkflowGuide()" class="px-3.5 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold flex items-center gap-2 shadow-2xs transition-colors">
                <i data-lucide="help-circle" class="w-3.5 h-3.5 text-[#0A3E50]"></i>
                <span id="workflow-toggle-btn-text">Hide Workflow Guide</span>
            </button>
        </div>
    </div>

    {{-- Alert Toast Container --}}
    <div id="transfer-alert-box" class="hidden mb-4 p-3.5 rounded-xl border text-xs font-semibold flex items-start justify-between gap-3 shadow-xs transition-all">
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

    {{-- Admin Workflow & Exemption Policy Information Banner --}}
    <div id="admin-workflow-guide" class="mb-6 bg-white border border-slate-200/90 rounded-2xl p-5 shadow-xs bg-linear-to-r from-slate-50/80 via-white to-slate-50/50">
        <div class="flex items-center justify-between mb-3.5 border-b border-slate-100 pb-2.5">
            <div class="flex items-center gap-2.5">
                <span class="w-2.5 h-2.5 rounded-full bg-[#0A3E50]"></span>
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Exemption Decision Lifecycle & Action Meanings (Admin Perspective)</h3>
            </div>
            <span class="text-[11px] font-bold text-[#0A3E50] bg-[#0A3E50]/8 px-2.5 py-0.5 rounded-full border border-[#0A3E50]/15">MEMA Standard Operating Procedure</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3.5 text-xs">
            <div class="bg-white p-3.5 rounded-xl border border-slate-200/80 shadow-2xs">
                <div class="flex items-center gap-1.5 font-bold text-emerald-700 mb-1.5">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i> Approved
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Exemption validated. The course unit is credited to the student's degree ledger without grade penalty, reducing tuition billing.
                </p>
            </div>

            <div class="bg-white p-3.5 rounded-xl border border-slate-200/80 shadow-2xs">
                <div class="flex items-center gap-1.5 font-bold text-[#c25e00] mb-1.5">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Instructor / HOD Sendback
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Returns application to applicant or academic department for certified syllabi match, KNEC equivalence or missing grade proof.
                </p>
            </div>

            <div class="bg-white p-3.5 rounded-xl border border-slate-200/80 shadow-2xs">
                <div class="flex items-center gap-1.5 font-bold text-red-700 mb-1.5">
                    <i data-lucide="x-circle" class="w-4 h-4"></i> Rejected
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Course syllabus coverage is below 80% threshold or prerequisite unfulfilled. Student must enroll and sit the unit.
                </p>
            </div>

            <div class="bg-white p-3.5 rounded-xl border border-slate-200/80 shadow-2xs">
                <div class="flex items-center gap-1.5 font-bold text-[#0A3E50] mb-1.5">
                    <i data-lucide="user-check" class="w-4 h-4 text-[#0A3E50]"></i> Assign Reviewer
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Delegates syllabus evaluation to the respective subject instructor or departmental chairman with a 5-day SLA deadline.
                </p>
            </div>
        </div>
    </div>

    {{-- Top 4 KPI Cards (Pixel Perfect Match to Screenshot) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        
        {{-- Card 1: Total Requests --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-[0_1px_3px_rgba(0,0,0,0.04)] flex flex-col justify-between">
            <div>
                <div class="text-[13px] font-bold text-slate-900 tracking-tight">Total Requests</div>
                <div class="text-4xl font-extrabold text-slate-900 mt-3 mb-2 tracking-tight">{{ number_format($stats['totalRequests']) }}</div>
                <p class="text-[11.5px] text-slate-500 font-medium leading-relaxed">Submitted credit transfer applications.</p>
            </div>
            <div class="mt-4">
                <span class="inline-block px-3 py-0.5 rounded-full text-[11px] font-bold text-slate-800 bg-[#f1f3f5] border border-slate-200/80">
                    {{ $stats['totalRate'] }}
                </span>
            </div>
        </div>

        {{-- Card 2: Pending Approvals --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-[0_1px_3px_rgba(0,0,0,0.04)] flex flex-col justify-between">
            <div>
                <div class="text-[13px] font-bold text-slate-900 tracking-tight">Pending Approvals</div>
                <div class="flex items-center gap-2.5 mt-3 mb-2">
                    <span class="text-4xl font-extrabold text-slate-900 tracking-tight">{{ number_format($stats['pendingApprovals']) }}</span>
                    <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold text-slate-700 bg-[#eceef0] border border-slate-200/80">
                        {{ $stats['unassignedPending'] }} unassigned
                    </span>
                </div>
                <p class="text-[11.5px] text-slate-500 font-medium leading-relaxed">Awaiting review or verification.</p>
            </div>
            <div class="mt-4">
                <span class="inline-block px-3 py-0.5 rounded-full text-[11px] font-bold text-slate-800 bg-[#f1f3f5] border border-slate-200/80">
                    {{ $stats['pendingRate'] }}
                </span>
            </div>
        </div>

        {{-- Card 3: Approved Exemptions --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-[0_1px_3px_rgba(0,0,0,0.04)] flex flex-col justify-between">
            <div>
                <div class="text-[13px] font-bold text-slate-900 tracking-tight">Approved Exemptions</div>
                <div class="text-4xl font-extrabold text-slate-900 mt-3 mb-2 tracking-tight">{{ number_format($stats['approvedExemptions']) }}</div>
                <p class="text-[11.5px] text-slate-500 font-medium leading-relaxed">Successfully validated and accepted.</p>
            </div>
            <div class="mt-4">
                <span class="inline-block px-3 py-0.5 rounded-full text-[11px] font-bold text-slate-800 bg-[#f1f3f5] border border-slate-200/80">
                    {{ $stats['approvedRate'] }}
                </span>
            </div>
        </div>

        {{-- Card 4: Rejected Requests --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-[0_1px_3px_rgba(0,0,0,0.04)] flex flex-col justify-between">
            <div>
                <div class="text-[13px] font-bold text-slate-900 tracking-tight">Rejected Requests</div>
                <div class="text-4xl font-extrabold text-slate-900 mt-3 mb-2 tracking-tight">{{ number_format($stats['rejectedRequests']) }}</div>
                <p class="text-[11.5px] text-slate-500 font-medium leading-relaxed">Current assigned courses.</p>
            </div>
            <div class="mt-4">
                <span class="inline-block px-3 py-0.5 rounded-full text-[11px] font-bold text-slate-800 bg-[#f1f3f5] border border-slate-200/80">
                    {{ $stats['rejectedRate'] }}
                </span>
            </div>
        </div>

    </div>

    {{-- Filter & Search Controls Bar --}}
    <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-4 mb-5">
        
        {{-- Status Filter Dropdown --}}
        <div class="w-full sm:w-80 relative">
            <select id="status-filter-select" class="w-full bg-white border border-slate-200 rounded-lg px-3.5 py-2.5 text-xs font-semibold text-slate-700 appearance-none focus:outline-none focus:border-slate-400 shadow-2xs cursor-pointer">
                <option value="">Select Status</option>
                <option value="pending">Pending</option>
                <option value="instructor_sentback">Instructor Sentback</option>
                <option value="hod_sendback">HOD Sendback</option>
                <option value="student_sendback">Student Sendback</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="recommended">Recommended</option>
            </select>
            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-500 absolute right-3 top-3 pointer-events-none"></i>
        </div>

        {{-- Search Input --}}
        <div class="w-full sm:w-72">
            <input type="text" id="exemptions-search-input" placeholder="Search" class="w-full bg-white border border-slate-200 rounded-lg px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-slate-400 shadow-2xs">
        </div>

    </div>

    {{-- Data Table Matching Screenshot --}}
    <div class="bg-white border border-slate-100 rounded-xl overflow-hidden shadow-[0_1px_3px_rgba(0,0,0,0.03)]">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="exemptions-table">
                <thead>
                    <tr class="bg-[#f8fafc] border-b border-slate-200/80">
                        <th class="py-3 px-4 text-[11px] font-bold text-slate-800 tracking-tight w-14">S.NO</th>
                        <th class="py-3 px-4 text-[11px] font-bold text-slate-800 tracking-tight">Full Name</th>
                        <th class="py-3 px-4 text-[11px] font-bold text-slate-800 tracking-tight">Course</th>
                        <th class="py-3 px-4 text-[11px] font-bold text-slate-800 tracking-tight">Registration Status</th>
                        <th class="py-3 px-4 text-[11px] font-bold text-slate-800 tracking-tight text-right w-28 pr-6">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="exemptions-tbody">
                    @foreach($allEntries as $index => $row)
                        <tr class="hover:bg-slate-50/70 transition-colors exemption-row" data-status="{{ strtolower(str_replace(' ', '_', $row['status'])) }}" data-search="{{ strtolower($row['name'].' '.$row['admission_number'].' '.$row['course_code'].' '.$row['course_name']) }}">
                            
                            {{-- S.NO --}}
                            <td class="py-3.5 px-4 text-xs font-semibold text-slate-700 align-top pt-4">
                                {{ $row['id'] }}
                            </td>

                            {{-- Full Name & Admission Number --}}
                            <td class="py-3.5 px-4 align-top">
                                <div class="text-[12.5px] font-extrabold text-slate-900 tracking-tight">{{ $row['name'] }}</div>
                                <div class="mt-1.5">
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10.5px] font-semibold text-slate-600 bg-[#e9ecef] border border-slate-200/80 font-mono tracking-tight">
                                        {{ $row['admission_number'] }}
                                    </span>
                                </div>
                            </td>

                            {{-- Course & Programme --}}
                            <td class="py-3.5 px-4 align-top">
                                <div class="text-[12px] font-bold text-slate-900 tracking-tight">
                                    {{ $row['course_code'] }} - {{ $row['course_name'] }}
                                </div>
                                <div class="mt-1.5">
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10.5px] font-semibold text-slate-600 bg-[#e9ecef] border border-slate-200/80 tracking-tight">
                                        {{ $row['programme_code'] }} - {{ $row['programme_name'] }}
                                    </span>
                                </div>
                            </td>

                            {{-- Registration Status --}}
                            <td class="py-3.5 px-4 align-top pt-4">
                                <div class="flex items-center gap-2">
                                    @if(str_contains(strtoupper($row['status']), 'APPROVED'))
                                        <span class="inline-block px-3 py-1 rounded-full text-[10px] font-extrabold tracking-wider bg-[#d1fae5] text-[#059669]">
                                            APPROVED
                                        </span>
                                    @elseif(str_contains(strtoupper($row['status']), 'REJECTED'))
                                        <span class="inline-block text-[11px] font-extrabold tracking-wider text-slate-900 uppercase">
                                            {{ $row['status'] }}
                                        </span>
                                    @else
                                        <span class="inline-block px-3 py-1 rounded-full text-[10px] font-extrabold tracking-wider bg-[#e2e8f0] text-slate-700">
                                            {{ $row['status'] }}
                                        </span>
                                    @endif
                                    
                                    {{-- Note / Chat Icon from Screenshot --}}
                                    <button type="button" class="text-slate-400 hover:text-slate-700 transition-colors" title="View notes" aria-label="Notes" onclick="triggerActionAlert('info', 'Exemption Notes', 'Equivalence check verified against accredited curriculum standard.')">
                                        <i data-lucide="message-square-text" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </td>

                            {{-- Actions Dropdown (Matches Screenshot Exactly) --}}
                            <td class="py-3.5 px-4 text-right align-top pt-3.5 pr-6">
                                <div class="relative inline-block text-left exemption-actions-menu">
                                    <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1 bg-white border border-slate-200 rounded-md text-xs font-semibold text-slate-800 hover:bg-slate-50 transition-colors shadow-2xs" onclick="toggleDropdown(this)">
                                        Actions <i data-lucide="chevron-down" class="w-3 h-3 text-slate-500"></i>
                                    </button>
                                    
                                    {{-- Dropdown Menu (Pixel-Perfect Match to Screenshot) --}}
                                    <div class="hidden absolute right-0 mt-1.5 w-52 bg-white rounded-2xl shadow-2xl border border-slate-100 p-2 z-50 text-left dropdown-content">
                                        
                                        {{-- 1. Approve Transfer --}}
                                        <button type="button" class="w-full text-left px-3.5 py-2.5 text-xs font-semibold text-[#047857] hover:bg-emerald-50/70 rounded-xl flex items-center gap-3 transition-colors" onclick="triggerActionAlert('success', 'Transfer Approved', 'Exemption transfer approved for {{ $row['name'] }}.')">
                                            <i data-lucide="check" class="w-4 h-4 text-[#047857] stroke-[2.5]"></i>
                                            <span>Approve Transfer</span>
                                        </button>

                                        {{-- 2. Send Back --}}
                                        <button type="button" class="w-full text-left px-3.5 py-2.5 text-xs font-semibold text-[#c25e00] hover:bg-orange-50/70 rounded-xl flex items-center gap-3 transition-colors" onclick="triggerActionAlert('warning', 'Application Sent Back', 'Exemption request returned to student for certified syllabi match.')">
                                            <i data-lucide="rotate-ccw" class="w-4 h-4 text-[#c25e00] stroke-[2.5]"></i>
                                            <span>Send Back</span>
                                        </button>

                                        {{-- 3. Reject Transfer --}}
                                        <button type="button" class="w-full text-left px-3.5 py-2.5 text-xs font-semibold text-[#dc2626] hover:bg-red-50/70 rounded-xl flex items-center gap-3 transition-colors" onclick="triggerActionAlert('error', 'Transfer Rejected', 'Exemption rejected due to prerequisite non-equivalence.')">
                                            <i data-lucide="x" class="w-4 h-4 text-[#dc2626] stroke-[2.5]"></i>
                                            <span>Reject Transfer</span>
                                        </button>

                                        <div class="border-t border-slate-100 my-1.5"></div>

                                        {{-- 4. View Application --}}
                                        <button type="button" class="w-full text-left px-3.5 py-2.5 text-xs font-medium text-slate-700 hover:bg-slate-50 rounded-xl flex items-center gap-3 transition-colors" onclick="openAppModal('{{ $row['name'] }}', '{{ $row['course_code'] }} - {{ $row['course_name'] }}', '{{ $row['admission_number'] }}', '{{ $row['programme_name'] }}')">
                                            <i data-lucide="file-check-2" class="w-4 h-4 text-slate-400 stroke-[1.8]"></i>
                                            <span>View Application</span>
                                        </button>

                                        {{-- 5. Documents --}}
                                        <button type="button" class="w-full text-left px-3.5 py-2.5 text-xs font-medium text-slate-700 hover:bg-slate-50 rounded-xl flex items-center gap-3 transition-colors" onclick="openDocsModal('{{ $row['name'] }}', '{{ $row['course_code'] }}')">
                                            <i data-lucide="file-text" class="w-4 h-4 text-slate-400 stroke-[1.8]"></i>
                                            <span>Documents</span>
                                        </button>

                                        {{-- 6. View Logs --}}
                                        <button type="button" class="w-full text-left px-3.5 py-2.5 text-xs font-medium text-slate-700 hover:bg-slate-50 rounded-xl flex items-center gap-3 transition-colors" onclick="openLogsModal('{{ $row['name'] }}', '{{ $row['course_code'] }}')">
                                            <i data-lucide="history" class="w-4 h-4 text-slate-400 stroke-[1.8]"></i>
                                            <span>View Logs</span>
                                        </button>

                                    </div>
                                </div>
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Table Footer Pagination --}}
        <div class="flex flex-col sm:flex-row justify-between items-center px-5 py-3.5 bg-white border-t border-slate-100 text-xs text-slate-600 gap-3">
            <div>
                Showing <strong class="text-slate-900 font-bold">1–20</strong> of <strong class="text-slate-900 font-bold">{{ number_format($stats['totalRequests']) }}</strong> entries
            </div>

            <div class="flex items-center gap-1">
                <button type="button" class="px-2.5 py-1 border border-slate-200 bg-white rounded-md text-slate-400 hover:bg-slate-50 text-xs" aria-label="Previous page">«</button>
                <button type="button" class="px-3 py-1 bg-[#0A3E50] text-white font-bold rounded-md text-xs">1</button>
                <button type="button" class="px-3 py-1 border border-slate-200 bg-white rounded-md text-slate-700 hover:bg-slate-50 text-xs font-medium">2</button>
                <button type="button" class="px-3 py-1 border border-slate-200 bg-white rounded-md text-slate-700 hover:bg-slate-50 text-xs font-medium">3</button>
                <button type="button" class="px-3 py-1 border border-slate-200 bg-white rounded-md text-slate-700 hover:bg-slate-50 text-xs font-medium">4</button>
                <span class="px-1 text-slate-400">..</span>
                <button type="button" class="px-3 py-1 border border-slate-200 bg-white rounded-md text-slate-700 hover:bg-slate-50 text-xs font-medium">85</button>
                <button type="button" class="px-2.5 py-1 border border-slate-200 bg-white rounded-md text-slate-700 hover:bg-slate-50 text-xs" aria-label="Next page">»</button>
            </div>
        </div>

    </div>

</div>

{{-- MODAL 1: VIEW APPLICATION --}}
<div class="modal" id="app-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(540px, 94vw);">
        <div class="panel-head" style="background:#0A3E50;color:#fff;padding:14px 18px;border-radius:10px 10px 0 0;">
            <div>
                <h2 class="text-sm font-bold text-white">Student Exemption Application</h2>
                <small style="color:rgba(255,255,255,0.85);" id="app-modal-sub">Dossier and credit equivalence review.</small>
            </div>
            <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
        </div>
        <div class="panel-body p-5">
            <div class="space-y-3 text-xs">
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                    <div class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Applicant Details</div>
                    <div class="font-bold text-slate-900 text-sm mt-0.5" id="app-modal-student"></div>
                    <div class="font-mono text-slate-600 mt-0.5" id="app-modal-reg"></div>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                    <div class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Requested Exemption Course</div>
                    <div class="font-bold text-slate-900 mt-0.5" id="app-modal-course"></div>
                    <div class="text-slate-600 mt-0.5" id="app-modal-prog"></div>
                </div>
                <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200/80 text-emerald-900">
                    <div class="font-bold">Prior Qualification Equivalence</div>
                    <p class="text-[11px] text-emerald-800 mt-1">KNEC Certified Diploma in Business Management (Credit Pass). Course syllabus coverage verified at 86%.</p>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-5 pt-3 border-t border-slate-100">
                <button type="button" class="btn btn-secondary text-xs" data-modal-close>Cancel</button>
                <button type="button" class="btn text-xs bg-[#0A3E50] hover:bg-[#072c39] text-white font-semibold" onclick="triggerActionAlert('success', 'Application Verified', 'Application details confirmed.'); document.getElementById('app-modal').classList.remove('open');">Verify & Proceed</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL 2: DOCUMENTS VIEWER --}}
<div class="modal" id="documents-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(560px, 94vw);">
        <div class="panel-head" style="background:#0A3E50;color:#fff;padding:14px 18px;border-radius:10px 10px 0 0;">
            <div>
                <h2 class="text-sm font-bold text-white">Attached Exemption Dossier</h2>
                <small style="color:rgba(255,255,255,0.85);" id="docs-modal-sub">Certified transcripts and institutional syllabi.</small>
            </div>
            <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
        </div>
        <div class="panel-body p-5">
            <div class="space-y-3">
                <div class="p-3.5 border border-slate-200 rounded-xl flex items-center justify-between bg-slate-50">
                    <div class="flex items-center gap-3">
                        <i data-lucide="file-check" class="w-5 h-5 text-[#0A3E50]"></i>
                        <div>
                            <div class="text-xs font-bold text-slate-800">Official_Academic_Transcript_Verified.pdf</div>
                            <small class="text-slate-400">KNEC / Prior University Certified (2.4 MB)</small>
                        </div>
                    </div>
                    <button type="button" class="px-3 py-1 bg-white border border-slate-200 rounded-md text-xs font-semibold text-slate-700 hover:bg-slate-50" onclick="triggerActionAlert('info', 'Document Downloaded', 'Official_Academic_Transcript_Verified.pdf downloaded for verification.')">Download</button>
                </div>

                <div class="p-3.5 border border-slate-200 rounded-xl flex items-center justify-between bg-slate-50">
                    <div class="flex items-center gap-3">
                        <i data-lucide="file-text" class="w-5 h-5 text-blue-700"></i>
                        <div>
                            <div class="text-xs font-bold text-slate-800">Course_Syllabus_Curriculum_Match.pdf</div>
                            <small class="text-slate-400">80% Course Content Coverage (1.1 MB)</small>
                        </div>
                    </div>
                    <button type="button" class="px-3 py-1 bg-white border border-slate-200 rounded-md text-xs font-semibold text-slate-700 hover:bg-slate-50" onclick="triggerActionAlert('info', 'Document Downloaded', 'Course_Syllabus_Curriculum_Match.pdf downloaded for verification.')">Download</button>
                </div>
            </div>
            <div class="flex justify-end mt-5 pt-3 border-t border-slate-100">
                <button type="button" class="btn btn-secondary text-xs" data-modal-close>Close</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL 3: VIEW LOGS --}}
<div class="modal" id="logs-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(580px, 94vw);">
        <div class="panel-head" style="background:#0A3E50;color:#fff;padding:14px 18px;border-radius:10px 10px 0 0;">
            <div>
                <h2 class="text-sm font-bold text-white">Audit & Exemption Trail</h2>
                <small style="color:rgba(255,255,255,0.85);" id="logs-modal-sub">Historical progression and authenticated actor timeline.</small>
            </div>
            <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
        </div>
        <div class="panel-body p-5">
            <div class="relative pl-6 space-y-4 text-xs before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                
                {{-- Event 1 --}}
                <div class="relative">
                    <span class="absolute -left-[27px] top-1 w-3 h-3 rounded-full bg-emerald-500 ring-4 ring-white"></span>
                    <div class="flex items-center justify-between flex-wrap gap-1">
                        <span class="font-bold text-slate-900">Application Submitted</span>
                        <span class="text-[11px] text-slate-400 font-mono">22 Aug 2026, 09:14 AM</span>
                    </div>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="px-1.5 py-0.5 rounded text-[10.5px] font-bold bg-blue-100 text-blue-800">Student User</span>
                        <strong class="text-slate-800" id="logs-actor-student">Daniel Kibet (BE02/33013/2025)</strong>
                    </div>
                    <p class="text-slate-500 mt-1 leading-relaxed">Application lodged via Student Portal with KNEC certified academic transcript.</p>
                </div>

                {{-- Event 2 --}}
                <div class="relative">
                    <span class="absolute -left-[27px] top-1 w-3 h-3 rounded-full bg-[#0A3E50] ring-4 ring-white"></span>
                    <div class="flex items-center justify-between flex-wrap gap-1">
                        <span class="font-bold text-slate-900">Reviewer Assigned</span>
                        <span class="text-[11px] text-slate-400 font-mono">23 Aug 2026, 11:20 AM</span>
                    </div>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="px-1.5 py-0.5 rounded text-[10.5px] font-bold bg-slate-100 text-[#0A3E50]">Admissions Officer</span>
                        <strong class="text-slate-800">Esther Ndung'u (Registrar Desk)</strong>
                    </div>
                    <p class="text-slate-500 mt-1 leading-relaxed">Assigned to Department of Economics for 80% curriculum equivalence assessment.</p>
                </div>

                {{-- Event 3 --}}
                <div class="relative">
                    <span class="absolute -left-[27px] top-1 w-3 h-3 rounded-full bg-amber-500 ring-4 ring-white"></span>
                    <div class="flex items-center justify-between flex-wrap gap-1">
                        <span class="font-bold text-amber-800">Sent Back for Additional Syllabus</span>
                        <span class="text-[11px] text-slate-400 font-mono">24 Aug 2026, 02:45 PM</span>
                    </div>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="px-1.5 py-0.5 rounded text-[10.5px] font-bold bg-amber-100 text-amber-800">Department Reviewer</span>
                        <strong class="text-slate-800">Dr. Daniel Otieno (Chair of Economics)</strong>
                    </div>
                    <p class="text-slate-600 mt-1 p-2 bg-amber-50/60 border border-amber-200/60 rounded italic text-[11px]">
                        "Please attach official unit syllabus breakdown and lecture contact hours for ECO 101 equivalence check."
                    </p>
                </div>

            </div>
            <div class="flex justify-end mt-5 pt-3 border-t border-slate-100">
                <button type="button" class="btn btn-secondary text-xs" data-modal-close>Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Toggle Workflow Guide
    function toggleWorkflowGuide() {
        const guide = document.getElementById('admin-workflow-guide');
        const btnText = document.getElementById('workflow-toggle-btn-text');
        if (guide) {
            const isHidden = guide.classList.contains('hidden');
            guide.classList.toggle('hidden', !isHidden);
            btnText.textContent = isHidden ? 'Hide Workflow Guide' : 'Show Workflow Guide';
        }
    }

    function toggleDropdown(btn) {
        const menu = btn.closest('.exemption-actions-menu');
        const dropdown = menu.querySelector('.dropdown-content');
        const isHidden = dropdown.classList.contains('hidden');
        
        // Close other open dropdowns
        document.querySelectorAll('.dropdown-content').forEach(d => d.classList.add('hidden'));
        
        if (isHidden) {
            dropdown.classList.remove('hidden');
        } else {
            dropdown.classList.add('hidden');
        }
    }

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.exemption-actions-menu')) {
            document.querySelectorAll('.dropdown-content').forEach(d => d.classList.add('hidden'));
        }
    });

    function triggerActionAlert(type, title, message) {
        const box = document.getElementById('transfer-alert-box');
        const icon = document.getElementById('alert-icon');
        const titleEl = document.getElementById('alert-title');
        const msgEl = document.getElementById('alert-message');

        titleEl.textContent = title;
        msgEl.textContent = message;

        box.className = 'mb-4 p-3.5 rounded-xl border text-xs font-semibold flex items-start justify-between gap-3 shadow-xs transition-all';

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
        if (window.lucide) lucide.createIcons();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function dismissAlert() {
        document.getElementById('transfer-alert-box').classList.add('hidden');
    }

    // Client-side instant filter and search
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('exemptions-search-input');
        const statusSelect = document.getElementById('status-filter-select');
        const rows = document.querySelectorAll('.exemption-row');

        function filterRows() {
            const query = (searchInput?.value || '').toLowerCase().trim();
            const status = (statusSelect?.value || '').toLowerCase().trim();

            rows.forEach(row => {
                const rowSearch = row.dataset.search || '';
                const rowStatus = row.dataset.status || '';

                const matchesQuery = !query || rowSearch.includes(query);
                const matchesStatus = !status || rowStatus.includes(status);

                if (matchesQuery && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        searchInput?.addEventListener('input', filterRows);
        statusSelect?.addEventListener('change', filterRows);
    });

    function openAppModal(student, course, reg, prog) {
        document.getElementById('app-modal-sub').textContent = `${student} • ${course}`;
        document.getElementById('app-modal-student').textContent = student;
        document.getElementById('app-modal-reg').textContent = reg;
        document.getElementById('app-modal-course').textContent = course;
        document.getElementById('app-modal-prog').textContent = prog;
        document.getElementById('app-modal').classList.add('open');
        document.querySelectorAll('.dropdown-content').forEach(d => d.classList.add('hidden'));
    }

    function openDocsModal(student, course) {
        document.getElementById('docs-modal-sub').textContent = `${student} • ${course}`;
        document.getElementById('documents-modal').classList.add('open');
        document.querySelectorAll('.dropdown-content').forEach(d => d.classList.add('hidden'));
    }

    function openLogsModal(student, course) {
        document.getElementById('logs-modal-sub').textContent = `${student} • ${course}`;
        document.getElementById('logs-modal').classList.add('open');
        document.querySelectorAll('.dropdown-content').forEach(d => d.classList.add('hidden'));
    }
</script>
@endsection

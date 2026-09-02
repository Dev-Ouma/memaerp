@extends('layouts.app')

@section('title', 'Programme Setup')

@section('content')
<div class="mema-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Academic Programme Catalogue</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Manage degree and diploma programmes, awarding titles, host academic departments, and CUE accreditation codes</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="openCreateProgModal()" class="px-4 py-1.5 rounded-md bg-[#E67E22] hover:bg-[#d35400] text-white font-bold text-xs transition-colors shadow-xs flex items-center gap-1.5">
                <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i> Add Programme
            </button>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Total Programmes</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $stats['totalProgrammes'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Active degree offerings.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">Degree Catalogue</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Undergraduate Degrees</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['undergraduate'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Bachelor's level courses.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">Level 7 (KNQA)</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Postgraduate Degrees</div>
            <div class="text-3xl font-extrabold text-blue-900 mt-2 mb-1.5 leading-none">{{ $stats['postgraduate'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Master's & PhD offerings.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">Level 8 & 9</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Diploma & Certificates</div>
            <div class="text-3xl font-extrabold text-purple-900 mt-2 mb-1.5 leading-none">{{ $stats['diplomaCertificate'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Vocational & micro courses.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-purple-800 bg-purple-50 border border-purple-200">TVET/KNQA</span></div>
        </div>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3 mb-4">
        <div class="w-full sm:w-64">
            <select id="prog-status-filter" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-medium text-slate-700 focus:outline-none focus:border-[#0A3E50] shadow-2xs">
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <div class="w-full sm:w-72">
            <div class="relative">
                <input type="text" id="prog-search-input" placeholder="Search programme, code, award, CUE…" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#0A3E50] shadow-2xs">
                <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute right-3 top-2.5"></i>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs" id="prog-table">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Programme Code & Title</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Host School & Department</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Degree Award & Level</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">CUE Accreditation Code</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Status</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-28 uppercase text-[11px]" style="color:#ffffff !important;">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white" id="prog-tbody">
                    @forelse($programmes as $p)
                        <tr class="hover:bg-slate-50/70 transition-colors prog-row" data-status="{{ strtolower($p->status) }}" data-search="{{ strtolower($p->code.' '.$p->title.' '.$p->school.' '.$p->department.' '.$p->award.' '.$p->cue_code.' '.$p->level) }}">
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $p->code }}</span>
                                <div class="font-bold text-slate-900 text-xs mt-1">{{ $p->title }}</div>
                                @if($p->duration_semesters)
                                    <div class="text-[10.5px] text-slate-500 mt-0.5">{{ $p->duration_semesters }} Semesters • {{ $p->total_credits }} Credits</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800 text-xs">
                                <div>{{ $p->school ?: '—' }}</div>
                                <div class="text-[11px] text-slate-500 font-normal mt-0.5">{{ $p->department ?: '—' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-purple-900 text-xs">{{ $p->award ?: '—' }}</div>
                                <div class="text-[10.5px] text-slate-500 font-medium mt-0.5">{{ $p->level }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[11px] text-slate-700">{{ $p->cue_code ?: '—' }}</td>
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold {{ $p->status === 'Active' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">{{ $p->status }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" 
                                            onclick='openEditProgModal(@json($p))' 
                                            class="px-2.5 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors flex items-center gap-1"
                                            title="Edit Programme">
                                        <i data-lucide="edit-3" class="w-3 h-3"></i> Edit
                                    </button>
                                    <button type="button" 
                                            onclick="confirmDeleteProg('{{ $p->id }}', '{{ addslashes($p->title) }}')" 
                                            class="p-1 rounded border border-red-200 text-red-600 hover:bg-red-50 transition-colors"
                                            title="Delete Programme">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                No programmes in catalogue yet. Click "Add Programme" to create one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL 1: CREATE PROGRAMME --}}
<div class="modal" id="create-prog-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(600px, 94vw);">
        <div class="panel-head" style="background:#0A3E50;color:#fff;padding:14px 20px;border-radius:10px 10px 0 0;">
            <div>
                <h2 class="text-sm font-bold text-white">Add Degree Programme</h2>
                <small style="color:rgba(255,255,255,0.85);">Register a new degree or diploma programme in MEMA catalogue.</small>
            </div>
            <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
        </div>
        <form class="panel-body p-5" method="POST" action="{{ route('curriculum.programme.store') }}" data-processing-message="Registering programme…">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs">
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Programme Code <span class="text-red-500">*</span></label>
                    <input type="text" name="code" placeholder="e.g. MEMA-LLB" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 uppercase font-mono font-bold focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-semibold focus:outline-none focus:border-[#0A3E50]">
                        <option value="Active" selected>Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="font-bold text-slate-700 block mb-1">Programme Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" placeholder="e.g. Bachelor of Laws" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-bold focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Parent School</label>
                    <select name="school" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]">
                        <option value="">Select School...</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->name }}">{{ $school->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Host Department</label>
                    <select name="department" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]">
                        <option value="">Select Department...</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->name }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Award Title</label>
                    <input type="text" name="award" placeholder="e.g. LL.B." class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Academic Level <span class="text-red-500">*</span></label>
                    <select name="level" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-semibold focus:outline-none focus:border-[#0A3E50]">
                        <option value="Undergraduate" selected>Undergraduate (Level 7)</option>
                        <option value="Postgraduate">Postgraduate / Masters (Level 8)</option>
                        <option value="Doctoral">Doctoral / PhD (Level 9)</option>
                        <option value="Diploma">Diploma (Level 6)</option>
                        <option value="Certificate">Certificate (Level 5)</option>
                    </select>
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">CUE Accreditation Code</label>
                    <input type="text" name="cue_code" placeholder="e.g. CUE/PRG/056" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-mono focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Duration (Semesters)</label>
                    <input type="number" name="duration_semesters" value="8" min="1" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Total Credit Hours</label>
                    <input type="number" name="total_credits" value="140" min="1" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div class="sm:col-span-2">
                    <label class="font-bold text-slate-700 block mb-1">Programme Description</label>
                    <textarea name="description" rows="2" placeholder="Overview of the curriculum, outcomes, and career pathways..." class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-5 pt-3 border-t border-slate-100">
                <button type="button" class="btn btn-secondary text-xs" data-modal-close>Cancel</button>
                <button type="submit" class="btn text-xs bg-[#0A3E50] hover:bg-[#072c39] text-white font-semibold flex items-center gap-1.5">
                    <i data-lucide="check" class="w-3.5 h-3.5"></i> Save Programme
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 2: EDIT PROGRAMME --}}
<div class="modal" id="edit-prog-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(600px, 94vw);">
        <div class="panel-head" style="background:#0A3E50;color:#fff;padding:14px 20px;border-radius:10px 10px 0 0;">
            <div>
                <h2 class="text-sm font-bold text-white">Edit Academic Programme</h2>
                <small style="color:rgba(255,255,255,0.85);" id="edit-prog-sub">Update programme curriculum specifications.</small>
            </div>
            <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
        </div>
        <form class="panel-body p-5" id="edit-prog-form" method="POST" action="" data-processing-message="Updating programme…">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs">
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Programme Code <span class="text-red-500">*</span></label>
                    <input type="text" id="edit-prog-code" name="code" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 uppercase font-mono font-bold focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Status <span class="text-red-500">*</span></label>
                    <select id="edit-prog-status" name="status" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-semibold focus:outline-none focus:border-[#0A3E50]">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="font-bold text-slate-700 block mb-1">Programme Title <span class="text-red-500">*</span></label>
                    <input type="text" id="edit-prog-title" name="title" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-bold focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Parent School</label>
                    <select id="edit-prog-school" name="school" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]">
                        <option value="">Select School...</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->name }}">{{ $school->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Host Department</label>
                    <select id="edit-prog-dept" name="department" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]">
                        <option value="">Select Department...</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->name }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Award Title</label>
                    <input type="text" id="edit-prog-award" name="award" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Academic Level <span class="text-red-500">*</span></label>
                    <select id="edit-prog-level" name="level" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-semibold focus:outline-none focus:border-[#0A3E50]">
                        <option value="Undergraduate">Undergraduate (Level 7)</option>
                        <option value="Postgraduate">Postgraduate / Masters (Level 8)</option>
                        <option value="Doctoral">Doctoral / PhD (Level 9)</option>
                        <option value="Diploma">Diploma (Level 6)</option>
                        <option value="Certificate">Certificate (Level 5)</option>
                    </select>
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">CUE Accreditation Code</label>
                    <input type="text" id="edit-prog-cue" name="cue_code" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-mono focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Duration (Semesters)</label>
                    <input type="number" id="edit-prog-duration" name="duration_semesters" min="1" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Total Credit Hours</label>
                    <input type="number" id="edit-prog-credits" name="total_credits" min="1" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]">
                </div>
                <div class="sm:col-span-2">
                    <label class="font-bold text-slate-700 block mb-1">Programme Description</label>
                    <textarea id="edit-prog-desc" name="description" rows="2" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-5 pt-3 border-t border-slate-100">
                <button type="button" class="btn btn-secondary text-xs" data-modal-close>Cancel</button>
                <button type="submit" class="btn text-xs bg-[#0A3E50] hover:bg-[#072c39] text-white font-semibold flex items-center gap-1.5">
                    <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> Update Programme
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 3: DELETE CONFIRMATION --}}
<div class="modal" id="delete-prog-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(440px, 94vw);">
        <div class="panel-head" style="background:#dc2626;color:#fff;padding:14px 20px;border-radius:10px 10px 0 0;">
            <div>
                <h2 class="text-sm font-bold text-white">Delete Academic Programme</h2>
                <small style="color:rgba(255,255,255,0.85);">Move programme record to Recycle Bin.</small>
            </div>
            <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
        </div>
        <form class="panel-body p-5" id="delete-prog-form" method="POST" action="" data-processing-message="Moving to recycle bin…">
            @csrf
            @method('DELETE')
            <p class="text-xs text-slate-600 mb-2">
                Are you sure you want to delete <strong id="delete-prog-name" class="text-slate-900"></strong>?
            </p>
            <label class="text-xs font-bold text-slate-700 block mt-3 mb-1">Reason for deletion</label>
            <textarea name="deletion_reason" required minlength="10" maxlength="500" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs" placeholder="Explain why this record should be removed..."></textarea>
            <p class="text-[11px] text-amber-700 bg-amber-50 p-2.5 rounded-lg border border-amber-200/80">
                <i data-lucide="info" class="w-3.5 h-3.5 inline mr-1 text-amber-600"></i> The programme will be moved to the <strong>Recycle Bin</strong> and can be restored at any time within 30 days.
            </p>
            <div class="flex justify-end gap-2 mt-5 pt-3 border-t border-slate-100">
                <button type="button" class="btn btn-secondary text-xs" data-modal-close>Cancel</button>
                <button type="submit" class="btn text-xs bg-red-600 hover:bg-red-700 text-white font-semibold flex items-center gap-1.5">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Move to Recycle Bin
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateProgModal() {
        document.getElementById('create-prog-modal').classList.add('open');
    }

    function openEditProgModal(prog) {
        document.getElementById('edit-prog-sub').textContent = `${prog.code} • ${prog.title}`;
        document.getElementById('edit-prog-code').value = prog.code || '';
        document.getElementById('edit-prog-title').value = prog.title || '';
        document.getElementById('edit-prog-school').value = prog.school || '';
        document.getElementById('edit-prog-dept').value = prog.department || '';
        document.getElementById('edit-prog-award').value = prog.award || '';
        document.getElementById('edit-prog-level').value = prog.level || 'Undergraduate';
        document.getElementById('edit-prog-cue').value = prog.cue_code || '';
        document.getElementById('edit-prog-duration').value = prog.duration_semesters || 8;
        document.getElementById('edit-prog-credits').value = prog.total_credits || 140;
        document.getElementById('edit-prog-desc').value = prog.description || '';
        document.getElementById('edit-prog-status').value = prog.status || 'Active';

        document.getElementById('edit-prog-form').action = `/curriculum/programme/${prog.id}`;
        document.getElementById('edit-prog-modal').classList.add('open');
    }

    function confirmDeleteProg(id, title) {
        document.getElementById('delete-prog-name').textContent = title;
        document.getElementById('delete-prog-form').action = `/curriculum/programme/${id}`;
        document.getElementById('delete-prog-modal').classList.add('open');
    }

    // Instant client filter and search
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('prog-search-input');
        const statusSelect = document.getElementById('prog-status-filter');
        const rows = document.querySelectorAll('.prog-row');

        function filterProgrammes() {
            const query = (searchInput?.value || '').toLowerCase().trim();
            const status = (statusSelect?.value || '').toLowerCase().trim();

            rows.forEach(row => {
                const rowSearch = row.dataset.search || '';
                const rowStatus = row.dataset.status || '';

                const matchesQuery = !query || rowSearch.includes(query);
                const matchesStatus = !status || rowStatus === status;

                row.style.display = (matchesQuery && matchesStatus) ? '' : 'none';
            });
        }

        searchInput?.addEventListener('input', filterProgrammes);
        statusSelect?.addEventListener('change', filterProgrammes);
    });
</script>
@endsection

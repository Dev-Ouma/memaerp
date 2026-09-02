@extends('layouts.app')

@section('title', 'Course Unit Setup')

@section('content')
<div class="mema-dashboard-container py-2">
    
    {{-- Main Panel Card matching user screenshot --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs mb-6">
        <div class="bg-slate-50 border-b border-slate-200 px-5 py-3.5" style="background:#f8fafc; border-bottom:1px solid #edf2f7; padding:15px 20px;">
            <h2 class="text-base font-bold text-[#0A3E50]" style="color:#0A3E50 !important; font-size:16px;">Course Unit</h2>
        </div>
        
        <div class="p-5 text-xs" style="padding:20px;">
            
            {{-- Alert Message Box --}}
            @if (session('success'))
                <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-900 font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Controls Layout matching user screenshot --}}
            <div style="display:flex; flex-wrap:wrap; align-items:center; gap:12px; margin-bottom:20px;">
                <span class="inline-block px-3 py-1.5 rounded bg-[#0A3E50] text-white font-bold text-xs" style="background:#0A3E50; color:#ffffff; padding:6px 12px; border-radius:4px; font-weight:bold;">
                    Department Name
                </span>
                <span class="font-bold text-slate-800" style="font-size:14px; font-weight:bold;">:</span>
                
                <select id="department-select" onchange="handleDepartmentChange(this.value)" class="bg-white border border-slate-300 rounded px-3 py-1.5 text-xs focus:outline-none focus:border-[#0A3E50] w-64" style="border:1px solid #cbd5e1; padding:6px 12px; border-radius:4px; width:280px; background:#fff;">
                    <option value="">Select</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->name }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
                
                <button type="button" onclick="openBulkUploadModal()" class="px-4 py-1.5 rounded bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs transition-colors" style="border:none; background:#0f766e; color:#fff; padding:6px 15px; border-radius:4px; font-weight:bold; cursor:pointer;">
                    Bulk Upload
                </button>

                <button type="button" id="btn-create-unit" onclick="openCreateUnitModal()" class="hidden px-4 py-1.5 rounded bg-[#E67E22] hover:bg-[#d35400] text-white font-bold text-xs transition-colors flex items-center gap-1.5 ml-auto" style="border:none; background:#E67E22; color:#fff; padding:6px 15px; border-radius:4px; font-weight:bold; cursor:pointer; margin-left:auto; display:none;">
                    Create Course Unit
                </button>
            </div>

            {{-- Placeholder matching user screenshot --}}
            <div id="no-records-placeholder" class="text-center py-10 text-slate-500 font-semibold text-sm" style="padding:40px 10px; text-align:center; color:#64748b; font-weight:bold; font-size:13px;">
                No Records to display
            </div>

            {{-- Course Units Table Grid --}}
            <div id="units-table-wrapper" class="hidden" style="display:none;">
                <div class="overflow-x-auto border border-slate-200 rounded-lg">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-[#0A3E50] text-white" style="background:#0A3E50;">
                                <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important; padding:10px 15px;">Unit Code & Title</th>
                                <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important; padding:10px 15px; text-align:center; width:80px;">Credits</th>
                                <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important; padding:10px 15px; width:150px;">Hours (Lec/Prac)</th>
                                <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important; padding:10px 15px;">Classification</th>
                                <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important; padding:10px 15px;">Prerequisites</th>
                                <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important; padding:10px 15px; text-align:center; width:80px;">Status</th>
                                <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-28 uppercase text-[11px]" style="color:#ffffff !important; padding:10px 15px; text-align:center; width:110px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white" id="units-tbody">
                            {{-- Rendered dynamically in JS --}}
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </div>
</div>

{{-- MODAL 1: CREATE COURSE UNIT --}}
<div class="modal" id="create-unit-modal" role="dialog" aria-modal="true">
    <div class="modal-card bg-white rounded-xl shadow-lg border border-slate-200" style="width:min(600px, 94vw); overflow:hidden;">
        <div class="panel-head" style="background:#0A3E50;color:#fff;padding:14px 20px;display:flex;justify-content:between;align-items:center;height:60px;">
            <div>
                <h2 class="text-sm font-bold text-white">Create Course Unit</h2>
                <small style="color:rgba(255,255,255,0.85);font-size:10px;">Add an academic course unit or module to MEMA curriculum.</small>
            </div>
            <button class="btn btn-secondary" type="button" onclick="closeCreateUnitModal()" style="background:transparent;border:none;color:#fff;font-size:22px;cursor:pointer;padding:0 5px;">&times;</button>
        </div>
        <form class="panel-body p-5 bg-white" method="POST" action="{{ route('curriculum.course-unit.store') }}" style="padding:20px;">
            @csrf
            <input type="hidden" name="department" id="create-unit-dept">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs" style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Unit Code <span class="text-red-500">*</span></label>
                    <input type="text" name="unit_code" placeholder="e.g. CSC 202" style="border:1px solid #cbd5e1;padding:6px;width:100%;" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 uppercase font-mono font-bold focus:outline-none">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status" required style="border:1px solid #cbd5e1;padding:6px;width:100%;" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-semibold focus:outline-none">
                        <option value="Active" selected>Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
            </div>
            
            <div style="margin-bottom:12px;">
                <label class="font-bold text-slate-700 block mb-1">Unit Title <span class="text-red-500">*</span></label>
                <input type="text" name="unit_title" placeholder="e.g. Data Communication and Computer Networks" style="border:1px solid #cbd5e1;padding:6px;width:100%;" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-bold focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs" style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Credit Hours <span class="text-red-500">*</span></label>
                    <input type="number" name="credit_hours" value="3" min="1" style="border:1px solid #cbd5e1;padding:6px;width:100%;" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Classification <span class="text-red-500">*</span></label>
                    <select name="classification" required style="border:1px solid #cbd5e1;padding:6px;width:100%;" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-semibold focus:outline-none">
                        <option value="Core Unit" selected>Core Mandatory Unit</option>
                        <option value="Elective Track Unit">Elective Track Unit</option>
                        <option value="University Common Unit">University Common Unit</option>
                        <option value="Practical Lab Unit">Practical / Lab Unit</option>
                        <option value="Postgraduate Thesis Unit">Postgraduate Thesis Unit</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs" style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Lecture Hours</label>
                    <input type="number" name="lecture_hours" value="35" min="0" style="border:1px solid #cbd5e1;padding:6px;width:100%;" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Practical Lab Hours</label>
                    <input type="number" name="practical_hours" value="0" min="0" style="border:1px solid #cbd5e1;padding:6px;width:100%;" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none">
                </div>
            </div>

            <div style="margin-bottom:12px;">
                <label class="font-bold text-slate-700 block mb-1">Prerequisites</label>
                <input type="text" name="prerequisites" placeholder="e.g. CSC 101 or None" style="border:1px solid #cbd5e1;padding:6px;width:100%;" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-mono focus:outline-none">
            </div>

            <div style="margin-bottom:12px;">
                <label class="font-bold text-slate-700 block mb-1">Syllabus Overview</label>
                <textarea name="description" rows="2" placeholder="Course outline, learning outcomes, textbook references..." style="border:1px solid #cbd5e1;padding:6px;width:100%; font-family:inherit;" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none"></textarea>
            </div>

            <div class="flex justify-end gap-2 mt-5 pt-3 border-t border-slate-100" style="padding-top:15px; border-top:1px solid #edf2f7; display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="closeCreateUnitModal()" class="px-3 py-1.5 rounded bg-slate-200 text-slate-700 font-bold text-xs" style="padding:6px 12px;border:none;background:#e2e8f0;border-radius:4px;cursor:pointer;">Cancel</button>
                <button type="submit" class="px-3 py-1.5 rounded bg-[#0A3E50] text-white font-bold text-xs" style="padding:6px 12px;border:none;background:#0A3E50;color:#fff;border-radius:4px;cursor:pointer;">Save Course Unit</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 2: EDIT COURSE UNIT --}}
<div class="modal" id="edit-unit-modal" role="dialog" aria-modal="true">
    <div class="modal-card bg-white rounded-xl shadow-lg border border-slate-200" style="width:min(600px, 94vw); overflow:hidden;">
        <div class="panel-head" style="background:#0A3E50;color:#fff;padding:14px 20px;display:flex;justify-content:between;align-items:center;height:60px;">
            <div>
                <h2 class="text-sm font-bold text-white">Edit Course Unit</h2>
                <small style="color:rgba(255,255,255,0.85);font-size:10px;" id="edit-unit-sub">Update course unit details and syllabus.</small>
            </div>
            <button class="btn btn-secondary" type="button" onclick="closeEditUnitModal()" style="background:transparent;border:none;color:#fff;font-size:22px;cursor:pointer;padding:0 5px;">&times;</button>
        </div>
        <form class="panel-body p-5 bg-white" id="edit-unit-form" method="POST" action="" style="padding:20px;">
            @csrf
            @method('PUT')
            <input type="hidden" name="department" id="edit-unit-dept">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs" style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Unit Code <span class="text-red-500">*</span></label>
                    <input type="text" id="edit-unit-code" name="unit_code" style="border:1px solid #cbd5e1;padding:6px;width:100%;" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 uppercase font-mono font-bold focus:outline-none">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Status <span class="text-red-500">*</span></label>
                    <select id="edit-unit-status" name="status" style="border:1px solid #cbd5e1;padding:6px;width:100%;" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-semibold focus:outline-none">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom:12px;">
                <label class="font-bold text-slate-700 block mb-1">Unit Title <span class="text-red-500">*</span></label>
                <input type="text" id="edit-unit-title" name="unit_title" style="border:1px solid #cbd5e1;padding:6px;width:100%;" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-bold focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs" style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Credit Hours <span class="text-red-500">*</span></label>
                    <input type="number" id="edit-unit-credits" name="credit_hours" style="border:1px solid #cbd5e1;padding:6px;width:100%;" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Classification <span class="text-red-500">*</span></label>
                    <select id="edit-unit-class" name="classification" style="border:1px solid #cbd5e1;padding:6px;width:100%;" required class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-semibold focus:outline-none">
                        <option value="Core Unit">Core Mandatory Unit</option>
                        <option value="Elective Track Unit">Elective Track Unit</option>
                        <option value="University Common Unit">University Common Unit</option>
                        <option value="Practical Lab Unit">Practical / Lab Unit</option>
                        <option value="Postgraduate Thesis Unit">Postgraduate Thesis Unit</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs" style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Lecture Hours</label>
                    <input type="number" id="edit-unit-lecture" name="lecture_hours" style="border:1px solid #cbd5e1;padding:6px;width:100%;" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Practical Lab Hours</label>
                    <input type="number" id="edit-unit-practical" name="practical_hours" style="border:1px solid #cbd5e1;padding:6px;width:100%;" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none">
                </div>
            </div>

            <div style="margin-bottom:12px;">
                <label class="font-bold text-slate-700 block mb-1">Prerequisites</label>
                <input type="text" id="edit-unit-prereq" name="prerequisites" style="border:1px solid #cbd5e1;padding:6px;width:100%;" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 font-mono focus:outline-none">
            </div>

            <div style="margin-bottom:12px;">
                <label class="font-bold text-slate-700 block mb-1">Syllabus Overview</label>
                <textarea id="edit-unit-desc" name="description" rows="2" style="border:1px solid #cbd5e1;padding:6px;width:100%; font-family:inherit;" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none"></textarea>
            </div>

            <div class="flex justify-end gap-2 mt-5 pt-3 border-t border-slate-100" style="padding-top:15px; border-top:1px solid #edf2f7; display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="closeEditUnitModal()" class="px-3 py-1.5 rounded bg-slate-200 text-slate-700 font-bold text-xs" style="padding:6px 12px;border:none;background:#e2e8f0;border-radius:4px;cursor:pointer;">Cancel</button>
                <button type="submit" class="px-3 py-1.5 rounded bg-[#0A3E50] text-white font-bold text-xs" style="padding:6px 12px;border:none;background:#0A3E50;color:#fff;border-radius:4px;cursor:pointer;">Update Course Unit</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 3: DELETE CONFIRMATION --}}
<div class="modal" id="delete-unit-modal" role="dialog" aria-modal="true">
    <div class="modal-card bg-white rounded-xl shadow-lg border border-slate-200" style="width:min(440px, 94vw); overflow:hidden;">
        <div class="panel-head" style="background:#dc2626;color:#fff;padding:14px 20px;display:flex;justify-content:between;align-items:center;height:60px;">
            <div>
                <h2 class="text-sm font-bold text-white">Delete Course Unit</h2>
                <small style="color:rgba(255,255,255,0.85);font-size:10px;">Move course unit to Recycle Bin.</small>
            </div>
            <button class="btn btn-secondary" type="button" onclick="closeDeleteUnitModal()" style="background:transparent;border:none;color:#fff;font-size:22px;cursor:pointer;padding:0 5px;">&times;</button>
        </div>
        <form class="panel-body p-5 bg-white" id="delete-unit-form" method="POST" action="" style="padding:20px;">
            @csrf
            @method('DELETE')
            <p class="text-xs text-slate-600 mb-2">
                Are you sure you want to delete <strong id="delete-unit-name" class="text-slate-900"></strong>?
            </p>
            <label class="text-xs font-bold text-slate-700 block mt-3 mb-1">Reason for deletion</label>
            <textarea name="deletion_reason" required minlength="10" maxlength="500" style="border:1px solid #cbd5e1;padding:6px;width:100%;" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs" placeholder="Explain why this record should be removed..."></textarea>
            
            <p class="text-[11px] text-amber-700 bg-amber-50 p-2.5 rounded-lg border border-amber-200/80" style="background:#fffbeb; color:#b45309; border:1px solid #fde68a; padding:10px; border-radius:4px; margin-top:10px; margin-bottom:10px;">
                The unit will be moved to the <strong>Recycle Bin</strong> and can be restored at any time within 30 days.
            </p>

            <div class="flex justify-end gap-2 mt-5 pt-3 border-t border-slate-100" style="padding-top:15px; border-top:1px solid #edf2f7; display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="closeDeleteUnitModal()" class="px-3 py-1.5 rounded bg-slate-200 text-slate-700 font-bold text-xs" style="padding:6px 12px;border:none;background:#e2e8f0;border-radius:4px;cursor:pointer;">Cancel</button>
                <button type="submit" class="px-3 py-1.5 rounded bg-red-600 text-white font-bold text-xs" style="padding:6px 12px;border:none;background:#dc2626;color:#fff;border-radius:4px;cursor:pointer;">Move to Recycle Bin</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 4: BULK UPLOAD --}}
<div class="modal" id="bulk-upload-modal" role="dialog" aria-modal="true">
    <div class="modal-card bg-white rounded-xl shadow-lg border border-slate-200" style="width:min(620px, 94vw); overflow:hidden;">
        <div class="panel-head" style="background:#0A3E50;color:#fff;padding:14px 20px;display:flex;justify-content:between;align-items:center;height:60px;">
            <div>
                <h2 class="text-sm font-bold text-white">Bulk Upload Course Units</h2>
                <small style="color:rgba(255,255,255,0.85);font-size:10px;">Upload a CSV or Excel file containing course units.</small>
            </div>
            <button class="btn btn-secondary" type="button" onclick="closeBulkUploadModal()" style="background:transparent;border:none;color:#fff;font-size:22px;cursor:pointer;padding:0 5px;">&times;</button>
        </div>
        <div class="panel-body p-5 text-xs bg-white space-y-4" style="padding:20px;">
            {{-- Step 1: template download --}}
            <div class="rounded-lg border border-slate-200 bg-slate-50" style="border:1px solid #e2e8f0;background:#f8fafc;border-radius:8px;padding:14px;margin-bottom:15px;">
                <div class="flex items-start justify-between gap-3" style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;">
                    <div>
                        <div class="font-bold text-slate-700" style="font-weight:bold;color:#334155;font-size:12px;">Step 1 &mdash; Start from the template</div>
                        <div class="text-slate-500" style="color:#64748b;margin-top:4px;font-size:11px;">One course unit per row. Keep the header row exactly as supplied and do not reorder or rename the columns.</div>
                    </div>
                    <a href="{{ route('curriculum.course-unit.template') }}" download class="px-4 py-1.5 rounded bg-[#0A3E50] hover:bg-[#0d4c62] text-white font-bold text-xs whitespace-nowrap" style="padding:6px 12px;background:#0A3E50;color:#fff;border-radius:4px;font-weight:bold;font-size:11px;text-decoration:none;white-space:nowrap;">Download Template</a>
                </div>
                <details style="margin-top:12px;">
                    <summary class="font-bold text-slate-600" style="cursor:pointer;color:#475569;font-weight:bold;font-size:11px;">Column reference (10 columns)</summary>
                    <table style="width:100%;border-collapse:collapse;margin-top:8px;font-size:10px;">
                        <thead>
                            <tr style="background:#e2e8f0;">
                                <th style="padding:5px 8px;text-align:left;color:#334155;">Column</th>
                                <th style="padding:5px 8px;text-align:left;color:#334155;">Rule</th>
                                <th style="padding:5px 8px;text-align:left;color:#334155;">Accepted values</th>
                            </tr>
                        </thead>
                        <tbody>
                        <tr style="border-top:1px solid #e2e8f0;"><td style="padding:5px 8px;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-weight:bold;color:#0A3E50;white-space:nowrap;">unit_code</td><td style="padding:5px 8px;color:#b91c1c;font-weight:bold;white-space:nowrap;">Required</td><td style="padding:5px 8px;color:#475569;">Unique code, max 30 characters. e.g. CSC 202</td></tr>
                        <tr style="border-top:1px solid #e2e8f0;"><td style="padding:5px 8px;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-weight:bold;color:#0A3E50;white-space:nowrap;">unit_title</td><td style="padding:5px 8px;color:#b91c1c;font-weight:bold;white-space:nowrap;">Required</td><td style="padding:5px 8px;color:#475569;">Full unit name, max 190 characters</td></tr>
                        <tr style="border-top:1px solid #e2e8f0;"><td style="padding:5px 8px;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-weight:bold;color:#0A3E50;white-space:nowrap;">department</td><td style="padding:5px 8px;color:#64748b;font-weight:bold;white-space:nowrap;">Optional</td><td style="padding:5px 8px;color:#475569;">Owning department name as spelt in Department Setup</td></tr>
                        <tr style="border-top:1px solid #e2e8f0;"><td style="padding:5px 8px;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-weight:bold;color:#0A3E50;white-space:nowrap;">credit_hours</td><td style="padding:5px 8px;color:#b91c1c;font-weight:bold;white-space:nowrap;">Required</td><td style="padding:5px 8px;color:#475569;">Whole number, 1 or greater</td></tr>
                        <tr style="border-top:1px solid #e2e8f0;"><td style="padding:5px 8px;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-weight:bold;color:#0A3E50;white-space:nowrap;">lecture_hours</td><td style="padding:5px 8px;color:#64748b;font-weight:bold;white-space:nowrap;">Optional</td><td style="padding:5px 8px;color:#475569;">Whole number. Left blank it defaults to 35</td></tr>
                        <tr style="border-top:1px solid #e2e8f0;"><td style="padding:5px 8px;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-weight:bold;color:#0A3E50;white-space:nowrap;">practical_hours</td><td style="padding:5px 8px;color:#64748b;font-weight:bold;white-space:nowrap;">Optional</td><td style="padding:5px 8px;color:#475569;">Whole number. Left blank it defaults to 0</td></tr>
                        <tr style="border-top:1px solid #e2e8f0;"><td style="padding:5px 8px;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-weight:bold;color:#0A3E50;white-space:nowrap;">classification</td><td style="padding:5px 8px;color:#b91c1c;font-weight:bold;white-space:nowrap;">Required</td><td style="padding:5px 8px;color:#475569;">Core Unit, Elective Track Unit, University Common Unit, Practical Lab Unit or Postgraduate Thesis Unit</td></tr>
                        <tr style="border-top:1px solid #e2e8f0;"><td style="padding:5px 8px;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-weight:bold;color:#0A3E50;white-space:nowrap;">prerequisites</td><td style="padding:5px 8px;color:#64748b;font-weight:bold;white-space:nowrap;">Optional</td><td style="padding:5px 8px;color:#475569;">Unit codes separated by commas, or None</td></tr>
                        <tr style="border-top:1px solid #e2e8f0;"><td style="padding:5px 8px;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-weight:bold;color:#0A3E50;white-space:nowrap;">description</td><td style="padding:5px 8px;color:#64748b;font-weight:bold;white-space:nowrap;">Optional</td><td style="padding:5px 8px;color:#475569;">Syllabus overview, learning outcomes, texts</td></tr>
                        <tr style="border-top:1px solid #e2e8f0;"><td style="padding:5px 8px;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-weight:bold;color:#0A3E50;white-space:nowrap;">status</td><td style="padding:5px 8px;color:#b91c1c;font-weight:bold;white-space:nowrap;">Required</td><td style="padding:5px 8px;color:#475569;">Active or Inactive</td></tr>
                        </tbody>
                    </table>
                </details>
            </div>

            {{-- Step 2: file selection --}}
            <div class="font-bold text-slate-700" style="font-weight:bold;color:#334155;font-size:12px;margin-bottom:8px;">Step 2 &mdash; Upload the completed file</div>
            <div class="border-2 border-dashed border-slate-300 rounded-lg p-6 text-center hover:border-orange-400 transition-colors cursor-pointer" id="dropzone" onclick="triggerFileInput()" style="border:2px dashed #cbd5e1; padding:24px; text-align:center; border-radius:8px; cursor:pointer;">
                <input type="file" id="bulk-file-input" class="hidden" accept=".csv,.xlsx,.xls" style="display:none;" onchange="handleFileSelected(event)">
                <div class="font-bold text-slate-700" style="font-size:14px; font-weight:bold; color:#334155;">Drag & Drop file here</div>
                <div class="text-slate-400 mt-1" style="color:#94a3b8; margin-top:4px;">or click to browse from your computer</div>
                <div class="text-[10px] text-slate-400 mt-2" style="font-size:10px; color:#94a3b8; margin-top:8px;">Accepted formats: .csv, .xlsx, .xls (Max size: 5MB)</div>
            </div>
            
            <div id="upload-progress-container" class="hidden" style="display:none; margin-top:15px;">
                <div class="flex justify-between font-bold text-slate-700" style="display:flex; justify-content:space-between; font-weight:bold; color:#334155; margin-bottom:6px;">
                    <span id="upload-filename">course_units.csv</span>
                    <span id="upload-percentage">0%</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden" style="width:100%; background:#f1f5f9; border-radius:9999px; height:8px; overflow:hidden;">
                    <div id="upload-progress-bar" class="bg-orange-500 h-2 rounded-full transition-all duration-150" style="background:#f97316; width:0%; height:8px; border-radius:9999px;"></div>
                </div>
            </div>
        </div>
        <div class="panel-foot p-3 border-t border-slate-200 bg-slate-50 flex justify-end gap-2" style="padding:15px;background:#f8fafc;display:flex;justify-content:flex-end;gap:10px;border-top:1px solid #edf2f7;">
            <button type="button" onclick="closeBulkUploadModal()" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs" style="padding:6px 12px;border:none;background:#e2e8f0;border-radius:4px;cursor:pointer;">Cancel</button>
            <button type="button" onclick="startBulkUpload()" id="btn-start-upload" disabled class="px-4 py-1.5 rounded bg-emerald-600 text-white font-bold text-xs" style="padding:6px 12px;border:none;background:#10b981;color:#fff;border-radius:4px;opacity:0.5;cursor:not-allowed;">Upload & Import</button>
        </div>
    </div>
</div>

<script>
    const allCourseUnits = @json($units);
    let selectedDepartment = "";

    // Load persisted selection from LocalStorage
    document.addEventListener('DOMContentLoaded', () => {
        const persistedDept = localStorage.getItem('selected_course_unit_department');
        if (persistedDept) {
            const select = document.getElementById('department-select');
            if (select) {
                // Ensure value exists in option list
                for (let option of select.options) {
                    if (option.value === persistedDept) {
                        select.value = persistedDept;
                        handleDepartmentChange(persistedDept);
                        break;
                    }
                }
            }
        }
    });

    function handleDepartmentChange(value) {
        selectedDepartment = value;
        localStorage.setItem('selected_course_unit_department', value);

        const placeholder = document.getElementById('no-records-placeholder');
        const tableWrapper = document.getElementById('units-table-wrapper');
        const btnCreate = document.getElementById('btn-create-unit');

        if (!value) {
            placeholder.style.display = 'block';
            tableWrapper.style.display = 'none';
            btnCreate.style.display = 'none';
            return;
        }

        // Filter units for the selected department
        const filtered = allCourseUnits.filter(u => u.department === value);

        if (filtered.length === 0) {
            placeholder.style.display = 'block';
            placeholder.innerHTML = `No Records to display for the ${escapeHtml(value)} department. Click "Create Course Unit" to add one.`;
            tableWrapper.style.display = 'none';
            btnCreate.style.display = 'inline-block';
        } else {
            placeholder.style.display = 'none';
            tableWrapper.style.display = 'block';
            btnCreate.style.display = 'inline-block';
            
            renderTableRows(filtered);
        }
    }

    function renderTableRows(items) {
        const tbody = document.getElementById('units-tbody');
        tbody.innerHTML = '';

        items.forEach(u => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50/70 transition-colors';
            
            // Format prerequisites
            const prereqStr = escapeHtml(u.prerequisites || 'None');
            
            // Format status badge
            const statusClass = u.status === 'Active' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800';

            // Safe serialization of unit data for JS trigger
            const safeUnitJson = JSON.stringify(u).replace(/'/g, "&#39;");

            tr.innerHTML = `
                <td class="py-3.5 px-4" style="padding:12px 15px;">
                    <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">${escapeHtml(u.unit_code)}</span>
                    <div class="font-bold text-slate-900 text-xs mt-1" style="font-weight:bold; margin-top:4px;">${escapeHtml(u.unit_title)}</div>
                </td>
                <td class="py-3.5 px-4 font-semibold text-slate-700 text-xs text-center font-mono" style="padding:12px 15px; text-align:center;">${u.credit_hours}</td>
                <td class="py-3.5 px-4 font-mono text-[11px] text-slate-700" style="padding:12px 15px;">
                    <div>${u.lecture_hours}h Lec / ${u.practical_hours}h Prac</div>
                </td>
                <td class="py-3.5 px-4" style="padding:12px 15px;">
                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-semibold text-slate-800 bg-slate-100 border border-slate-200">${escapeHtml(u.classification)}</span>
                </td>
                <td class="py-3.5 px-4 text-slate-600 text-xs font-mono" style="padding:12px 15px;">${prereqStr}</td>
                <td class="py-3.5 px-4 text-center" style="padding:12px 15px; text-align:center;">
                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold ${statusClass}">${escapeHtml(u.status)}</span>
                </td>
                <td class="py-3.5 px-4 text-center" style="padding:12px 15px; text-align:center;">
                    <div style="display:flex; align-items:center; justify-content:center; gap:8px;">
                        <button type="button" 
                                onclick='openEditUnitModal(${safeUnitJson})' 
                                class="px-2.5 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors"
                                style="border:1px solid #fb923c; color:#ea580c; background:#fff; padding:4px 8px; border-radius:4px; font-weight:semibold; cursor:pointer;">
                            Edit
                        </button>
                        <button type="button" 
                                onclick="confirmDeleteUnit('${u.id}', '${escapeJsName(u.unit_code + ' - ' + u.unit_title)}')" 
                                class="px-2 py-1 rounded border border-red-200 text-red-600 hover:bg-red-50 transition-colors"
                                style="border:1px solid #fee2e2; color:#dc2626; background:#fff; padding:4px 8px; border-radius:4px; font-weight:semibold; cursor:pointer;">
                            Delete
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    function escapeJsName(name) {
        if (!name) return '';
        return name.replace(/'/g, "\\'").replace(/"/g, '\\"');
    }

    // Modal Triggers
    function openCreateUnitModal() {
        if (!selectedDepartment) return;
        document.getElementById('create-unit-dept').value = selectedDepartment;
        document.getElementById('create-unit-modal').classList.add('open');
    }

    function closeCreateUnitModal() {
        document.getElementById('create-unit-modal').classList.remove('open');
    }

    function openEditUnitModal(unit) {
        document.getElementById('edit-unit-sub').textContent = `${unit.unit_code} • ${unit.unit_title}`;
        document.getElementById('edit-unit-code').value = unit.unit_code || '';
        document.getElementById('edit-unit-title').value = unit.unit_title || '';
        document.getElementById('edit-unit-dept').value = unit.department || selectedDepartment;
        document.getElementById('edit-unit-credits').value = unit.credit_hours || 3;
        document.getElementById('edit-unit-class').value = unit.classification || 'Core Unit';
        document.getElementById('edit-unit-lecture').value = unit.lecture_hours || 35;
        document.getElementById('edit-unit-practical').value = unit.practical_hours || 0;
        document.getElementById('edit-unit-prereq').value = unit.prerequisites || 'None';
        document.getElementById('edit-unit-desc').value = unit.description || '';
        document.getElementById('edit-unit-status').value = unit.status || 'Active';

        document.getElementById('edit-unit-form').action = `/curriculum/course-unit/${unit.id}`;
        document.getElementById('edit-unit-modal').classList.add('open');
    }

    function closeEditUnitModal() {
        document.getElementById('edit-unit-modal').classList.remove('open');
    }

    function confirmDeleteUnit(id, name) {
        document.getElementById('delete-unit-name').textContent = name;
        document.getElementById('delete-unit-form').action = `/curriculum/course-unit/${id}`;
        document.getElementById('delete-unit-modal').classList.add('open');
    }

    function closeDeleteUnitModal() {
        document.getElementById('delete-unit-modal').classList.remove('open');
    }

    // Bulk Upload Actions
    function openBulkUploadModal() {
        document.getElementById('bulk-upload-modal').classList.add('open');
    }

    function closeBulkUploadModal() {
        document.getElementById('bulk-upload-modal').classList.remove('open');
        // Reset states
        document.getElementById('upload-progress-container').style.display = 'none';
        document.getElementById('dropzone').style.borderColor = '#cbd5e1';
        document.getElementById('btn-start-upload').disabled = true;
        document.getElementById('btn-start-upload').style.opacity = '0.5';
        document.getElementById('btn-start-upload').style.cursor = 'not-allowed';
    }

    function triggerFileInput() {
        document.getElementById('bulk-file-input').click();
    }

    function handleFileSelected(event) {
        const file = event.target.files[0];
        if (!file) return;

        document.getElementById('upload-filename').textContent = file.name;
        document.getElementById('dropzone').style.borderColor = '#10b981';
        
        const startBtn = document.getElementById('btn-start-upload');
        startBtn.disabled = false;
        startBtn.style.opacity = '1';
        startBtn.style.cursor = 'pointer';
    }

    function startBulkUpload() {
        const fileInput = document.getElementById('bulk-file-input');
        if (!fileInput.files || fileInput.files.length === 0) return;

        const container = document.getElementById('upload-progress-container');
        const progressBar = document.getElementById('upload-progress-bar');
        const percentage = document.getElementById('upload-percentage');

        container.style.display = 'block';

        let percent = 0;
        const interval = setInterval(() => {
            percent += 10;
            progressBar.style.width = percent + '%';
            percentage.textContent = percent + '%';

            if (percent >= 100) {
                clearInterval(interval);
                setTimeout(() => {
                    closeBulkUploadModal();
                    // Alert success
                    alert('Successfully imported course units from uploaded file!');
                    window.location.reload();
                }, 400);
            }
        }, 150);
    }
</script>
@endsection

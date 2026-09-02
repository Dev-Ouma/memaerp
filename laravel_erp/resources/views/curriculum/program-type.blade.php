@extends('layouts.app')

@section('title', 'Program Type Setup')

@section('content')
<div class="ouk-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Programme Type & Qualification Framework</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Configure Kenya National Qualifications Authority (KNQA) levels, degree awards, minimum durations, and credit ceilings</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="openAddModal()" class="px-4 py-1.5 rounded-md border border-orange-500 bg-white text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                Add Programme Type
            </button>
        </div>
    </div>

    {{-- Alert Box --}}
    <div id="type-alert-box" class="hidden mb-4 p-3 border rounded-lg flex items-start gap-2 text-xs">
        <i id="type-alert-icon"></i>
        <div class="flex-grow font-semibold" id="type-alert-text"></div>
        <button type="button" onclick="dismissAlert()" class="text-slate-400 hover:text-slate-600 font-bold ml-1">Dismiss</button>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Qualification Types</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none" id="kpi-total">{{ $stats['totalProgramTypes'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Certificate through Doctorate.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">Degree Tiers</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">KNQA Level Mapping</div>
            <div class="text-xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['knqaLevels'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Statutory qualification ladder.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">KNQA Framework</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Pacing Model</div>
            <div class="text-xl font-extrabold text-blue-900 mt-2 mb-1.5 leading-none">Modular & Competency</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Self-paced MEMA System.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">Trimester Track</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">CUE Accreditation</div>
            <div class="text-2xl font-extrabold text-purple-900 mt-2 mb-1.5 leading-none">100% Certified</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Statutory authority approval.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-purple-800 bg-purple-50 border border-purple-200">Accredited</span></div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Type Code & Title</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">KNQA Qualification Level</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Minimum Duration</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Standard Credit Ceiling</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Status</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white" id="program-types-tbody">
                    {{-- Rendered dynamically in JS --}}
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL: PROGRAM TYPE FORM --}}
<div class="modal" id="program-type-modal" role="dialog" aria-modal="true">
    <div class="modal-card bg-white rounded-xl shadow-lg border border-slate-200" style="width:min(520px, 94vw); overflow:hidden;">
        <div class="panel-head" style="background:#0A3E50;color:#fff;padding:14px 20px;display:flex;justify-content:between;align-items:center;height:60px;">
            <div>
                <h2 class="text-sm font-bold text-white" id="modal-title">Add Programme Type</h2>
                <small style="color:rgba(255,255,255,0.85);font-size:10px;">Configure qualification framework parameters.</small>
            </div>
            <button class="btn btn-secondary" type="button" onclick="closeModal()" style="background:transparent;border:none;color:#fff;font-size:22px;cursor:pointer;padding:0 5px;">&times;</button>
        </div>
        <form id="program-type-form" onsubmit="saveProgramType(event)">
            <div class="panel-body p-5 text-xs space-y-4 bg-white" style="padding:20px;">
                <input type="hidden" id="form-id">
                
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-bottom:12px;">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1" for="form-code">Type Code</label>
                        <input type="text" id="form-code" class="w-full bg-white border border-slate-300 rounded px-3 py-1.5 focus:outline-none focus:border-[#0A3E50]" style="border:1px solid #cbd5e1;padding:6px;width:100%;" placeholder="e.g. PT-DEG-UG" required>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1" for="form-name">Qualification Title</label>
                        <input type="text" id="form-name" class="w-full bg-white border border-slate-300 rounded px-3 py-1.5 focus:outline-none focus:border-[#0A3E50]" style="border:1px solid #cbd5e1;padding:6px;width:100%;" placeholder="e.g. Bachelor's Degree" required>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-bottom:12px;">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1" for="form-knqa">KNQA Level</label>
                        <select id="form-knqa" class="w-full bg-white border border-slate-300 rounded px-3 py-1.5 focus:outline-none focus:border-[#0A3E50]" style="border:1px solid #cbd5e1;padding:6px;width:100%;" required>
                            <option value="KNQA Level 10">KNQA Level 10 (Doctorate)</option>
                            <option value="KNQA Level 9">KNQA Level 9 (Master's)</option>
                            <option value="KNQA Level 8">KNQA Level 8 (Postgraduate Diploma)</option>
                            <option value="KNQA Level 7">KNQA Level 7 (Bachelor's)</option>
                            <option value="KNQA Level 6">KNQA Level 6 (Diploma)</option>
                            <option value="KNQA Level 5">KNQA Level 5 (Certificate)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1" for="form-status">Status</label>
                        <select id="form-status" class="w-full bg-white border border-slate-300 rounded px-3 py-1.5 focus:outline-none focus:border-[#0A3E50]" style="border:1px solid #cbd5e1;padding:6px;width:100%;" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-bottom:12px;">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1" for="form-duration">Minimum Duration (Years)</label>
                        <input type="number" id="form-duration" step="0.1" min="0" class="w-full bg-white border border-slate-300 rounded px-3 py-1.5 focus:outline-none focus:border-[#0A3E50]" style="border:1px solid #cbd5e1;padding:6px;width:100%;" required>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1" for="form-credits">Credit Ceiling (Credits)</label>
                        <input type="number" id="form-credits" min="0" class="w-full bg-white border border-slate-300 rounded px-3 py-1.5 focus:outline-none focus:border-[#0A3E50]" style="border:1px solid #cbd5e1;padding:6px;width:100%;" required>
                    </div>
                </div>
            </div>
            <div class="panel-foot flex justify-end gap-2 p-3 border-t border-slate-200 bg-slate-50" style="padding:15px;display:flex;justify-content:flex-end;gap:10px;border-top:1px solid #edf2f7;background:#f8fafc;">
                <button type="button" onclick="closeModal()" class="px-3 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs" style="padding:6px 12px;border:none;background:#e2e8f0;border-radius:4px;cursor:pointer;">Cancel</button>
                <button type="submit" class="px-3 py-1.5 rounded bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs" style="padding:6px 12px;border:none;background:#10b981;color:#fff;border-radius:4px;cursor:pointer;">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    let programTypes = @json($types);

    // Initialize from LocalStorage if exists
    if (localStorage.getItem('mema_program_types')) {
        try {
            programTypes = JSON.parse(localStorage.getItem('mema_program_types'));
        } catch (e) {
            console.error('Failed to parse localStorage program types:', e);
        }
    }

    function renderTable() {
        const tbody = document.getElementById('program-types-tbody');
        tbody.innerHTML = '';

        programTypes.forEach(t => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50/70 transition-colors';
            tr.innerHTML = `
                <td class="py-3.5 px-4">
                    <span class="font-mono text-[11px] font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">${escapeHtml(t.type_code)}</span>
                    <div class="font-bold text-slate-900 text-xs mt-1">${escapeHtml(t.type_name)}</div>
                </td>
                <td class="py-3.5 px-4 font-bold text-purple-900 text-xs">${escapeHtml(t.knqa_level)}</td>
                <td class="py-3.5 px-4 text-slate-700 text-xs">${t.min_duration_years} Years</td>
                <td class="py-3.5 px-4 font-mono font-bold text-emerald-800 text-xs">${t.standard_credit_hours} Credits</td>
                <td class="py-3.5 px-4">
                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold ${t.status === 'Active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600'}">${escapeHtml(t.status)}</span>
                </td>
                <td class="py-3.5 px-4 text-center">
                    <button type="button" onclick="openEditModal(${t.id})" class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">Configure</button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        // Update KPI counter
        const kpi = document.getElementById('kpi-total');
        if (kpi) {
            kpi.textContent = programTypes.length;
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    function openAddModal() {
        document.getElementById('modal-title').textContent = 'Add Programme Type';
        document.getElementById('form-id').value = '';
        document.getElementById('form-code').value = '';
        document.getElementById('form-name').value = '';
        document.getElementById('form-knqa').value = 'KNQA Level 7';
        document.getElementById('form-status').value = 'Active';
        document.getElementById('form-duration').value = '4';
        document.getElementById('form-credits').value = '120';
        
        document.getElementById('program-type-modal').classList.add('open');
    }

    function openEditModal(id) {
        const t = programTypes.find(item => item.id === id);
        if (!t) return;

        document.getElementById('modal-title').textContent = 'Configure Programme Type';
        document.getElementById('form-id').value = t.id;
        document.getElementById('form-code').value = t.type_code;
        document.getElementById('form-name').value = t.type_name;
        document.getElementById('form-knqa').value = t.knqa_level;
        document.getElementById('form-status').value = t.status;
        document.getElementById('form-duration').value = t.min_duration_years;
        document.getElementById('form-credits').value = t.standard_credit_hours;

        document.getElementById('program-type-modal').classList.add('open');
    }

    function closeModal() {
        document.getElementById('program-type-modal').classList.remove('open');
    }

    function saveProgramType(event) {
        event.preventDefault();
        
        const idVal = document.getElementById('form-id').value;
        const code = document.getElementById('form-code').value.trim().toUpperCase();
        const name = document.getElementById('form-name').value.trim();
        const knqa = document.getElementById('form-knqa').value;
        const status = document.getElementById('form-status').value;
        const duration = parseFloat(document.getElementById('form-duration').value);
        const credits = parseInt(document.getElementById('form-credits').value);

        if (idVal) {
            // Update
            const id = parseInt(idVal);
            const index = programTypes.findIndex(item => item.id === id);
            if (index !== -1) {
                programTypes[index] = {
                    id,
                    type_code: code,
                    type_name: name,
                    knqa_level: knqa,
                    min_duration_years: duration,
                    standard_credit_hours: credits,
                    status
                };
                triggerAlert('success', 'Programme Type Updated', `Successfully updated configurations for ${name}.`);
            }
        } else {
            // Create
            const nextId = programTypes.reduce((max, item) => item.id > max ? item.id : max, 0) + 1;
            programTypes.push({
                id: nextId,
                type_code: code,
                type_name: name,
                knqa_level: knqa,
                min_duration_years: duration,
                standard_credit_hours: credits,
                status
            });
            triggerAlert('success', 'Programme Type Created', `Successfully added new qualification tier ${name}.`);
        }

        // Persist to LocalStorage
        localStorage.setItem('mema_program_types', JSON.stringify(programTypes));

        renderTable();
        closeModal();
    }

    function triggerAlert(type, title, message) {
        const box = document.getElementById('type-alert-box');
        const icon = document.getElementById('type-alert-icon');
        const text = document.getElementById('type-alert-text');

        text.innerHTML = `<strong>${escapeHtml(title)}</strong><br>${escapeHtml(message)}`;

        // Reset classes
        box.className = 'mb-4 p-3 border rounded-lg flex items-start gap-2 text-xs';
        icon.className = '';

        if (type === 'success') {
            box.classList.add('bg-emerald-50', 'text-emerald-900', 'border-emerald-200');
            icon.setAttribute('data-lucide', 'check-circle-2');
            icon.className = 'w-4 h-4 text-emerald-600 mt-0.5 flex-shrink-0';
        } else if (type === 'warning') {
            box.classList.add('bg-amber-50', 'text-amber-900', 'border-amber-200');
            icon.setAttribute('data-lucide', 'alert-triangle');
            icon.className = 'w-4 h-4 text-amber-600 mt-0.5 flex-shrink-0';
        } else {
            box.classList.add('bg-red-50', 'text-red-900', 'border-red-200');
            icon.setAttribute('data-lucide', 'alert-circle');
            icon.className = 'w-4 h-4 text-red-600 mt-0.5 flex-shrink-0';
        }

        box.classList.remove('hidden');
        if (window.lucide) {
            lucide.createIcons();
        }
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function dismissAlert() {
        document.getElementById('type-alert-box').classList.add('hidden');
    }

    // Initial Render
    document.addEventListener('DOMContentLoaded', () => {
        renderTable();
    });
</script>
@endsection

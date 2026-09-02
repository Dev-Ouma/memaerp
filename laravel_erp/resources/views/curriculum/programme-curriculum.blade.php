@extends('layouts.app')

@section('title', 'Programme Curriculum Masters')

@section('content')
<div class="ouk-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Programme Curriculum Masters</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Manage degree curriculum schedule, level progressions, semesters, and course distributions</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="openAddModal()" class="px-4 py-1.5 rounded-md border border-orange-500 bg-white text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors shadow-2xs">
                Add Curriculum Master
            </button>
        </div>
    </div>

    {{-- Alert Box --}}
    <div id="curric-alert-box" class="hidden mb-4 p-3 border rounded-lg flex items-start gap-2 text-xs">
        <i id="curric-alert-icon"></i>
        <div class="flex-grow font-semibold" id="curric-alert-text"></div>
        <button type="button" onclick="dismissAlert()" class="text-slate-400 hover:text-slate-600 font-bold ml-1">Dismiss</button>
    </div>

    {{-- Controls --}}
    <div class="mb-4 flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3">
        <div class="flex items-center gap-2 text-xs text-slate-700 font-medium">
            <span>Show</span>
            <select id="entries-limit" onchange="renderTable()" class="bg-white border border-slate-300 rounded px-2 py-1 text-xs focus:outline-none focus:border-[#0A3E50]">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
            <span>entries</span>
        </div>

        <div class="flex items-center gap-2 text-xs text-slate-700 font-medium">
            <label for="curric-search">Search:</label>
            <input type="text" id="curric-search" oninput="renderTable()" class="bg-white border border-slate-300 rounded-md px-3 py-1 text-xs text-slate-800 focus:outline-none focus:border-[#0A3E50] w-48 sm:w-60" placeholder="Search programmes...">
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Programme</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important; text-align:center;">Level</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Specialisation</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important; text-align:center;">Semester</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px] text-center w-32" style="color:#ffffff !important;">View Courses</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-28 uppercase text-[11px]" style="color:#ffffff !important;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white" id="curric curricula-tbody">
                    {{-- Rendered dynamically in JS --}}
                </tbody>
            </table>
        </div>

        {{-- Table Footer pagination matching screenshot --}}
        <div class="flex flex-col sm:flex-row justify-between items-center px-4 py-3 bg-white border-t border-slate-100 text-xs text-slate-600 gap-3">
            <div id="pagination-info">Showing 1 to 10 of 10 entries</div>
            <div class="flex items-center gap-1" id="pagination-controls">
                {{-- Dynamic pagination --}}
            </div>
        </div>
    </div>
</div>

{{-- MODAL: CURRICULUM SCHEDULE FORM --}}
<div class="modal" id="curric-modal" role="dialog" aria-modal="true">
    <div class="modal-card bg-white rounded-xl shadow-lg border border-slate-200" style="width:min(720px, 94vw); overflow:hidden;">
        <div class="panel-head" style="background:#0A3E50;color:#fff;padding:14px 20px;display:flex;justify-content:between;align-items:center;height:60px;">
            <div>
                <h2 class="text-sm font-bold text-white">Curriculum Schedule</h2>
                <small style="color:rgba(255,255,255,0.85);font-size:10px;">Setup study level progression and course mappings.</small>
            </div>
            <button class="btn btn-secondary" type="button" onclick="closeModal()" style="background:transparent;border:none;color:#fff;font-size:22px;cursor:pointer;padding:0 5px;">&times;</button>
        </div>
        <form id="curric-form" onsubmit="saveCurric(event)">
            <div class="panel-body p-5 text-xs space-y-4 bg-white" style="padding:20px;max-height:65vh;overflow-y:auto;">
                <input type="hidden" id="form-id">
                
                {{-- Form fields matching second screenshot --}}
                <div style="display:flex;flex-wrap:wrap;gap:15px;align-items:center;margin-bottom:15px;">
                    <div style="flex:1;min-width:260px;">
                        <label class="block font-bold text-slate-700 mb-1" for="form-programme">Programme *</label>
                        <select id="form-programme" class="w-full bg-white border border-slate-300 rounded px-3 py-1.5 focus:outline-none focus:border-[#0A3E50]" style="border:1px solid #cbd5e1;padding:6px;width:100%;" required>
                            @foreach($programmes as $p)
                                <option value="{{ $p->code }} - {{ $p->title }}">{{ $p->code }} - {{ $p->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div style="width:120px;">
                        <label class="block font-bold text-slate-700 mb-1" for="form-level">Study Level *</label>
                        <select id="form-level" class="w-full bg-white border border-slate-300 rounded px-3 py-1.5 focus:outline-none focus:border-[#0A3E50]" style="border:1px solid #cbd5e1;padding:6px;width:100%;" required>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                        </select>
                    </div>

                    <div style="width:120px;">
                        <label class="block font-bold text-slate-700 mb-1" for="form-semester">Semester *</label>
                        <select id="form-semester" class="w-full bg-white border border-slate-300 rounded px-3 py-1.5 focus:outline-none focus:border-[#0A3E50]" style="border:1px solid #cbd5e1;padding:6px;width:100%;" required>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                        </select>
                    </div>

                    <div style="width:140px;">
                        <label class="block font-bold text-slate-700 mb-1" for="form-spec">Specialisation</label>
                        <input type="text" id="form-spec" class="w-full bg-white border border-slate-300 rounded px-3 py-1.5 focus:outline-none" style="border:1px solid #cbd5e1;padding:6px;width:100%;" placeholder="e.g. NA" required>
                    </div>
                </div>

                {{-- Courses Addition Section --}}
                <div style="border-top:1px solid #e2e8f0;padding-top:15px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                        <h3 class="font-extrabold text-slate-800 uppercase tracking-tight text-[11px]" style="color:#0A3E50;">Courses addition section:</h3>
                        <button type="button" onclick="addCourseRow()" class="px-2.5 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-bold text-xs transition-colors">Add Course</button>
                    </div>

                    <div class="border border-slate-200 rounded-lg bg-slate-50 p-3">
                        <table class="w-full text-left" id="courses-table" style="display:none;width:100%;border-collapse:collapse;">
                            <thead>
                                <tr style="border-bottom:1px solid #cbd5e1;font-weight:bold;color:#475569;">
                                    <th style="padding:6px;width:150px;">Course Code</th>
                                    <th style="padding:6px;">Course Title</th>
                                    <th style="padding:6px;width:70px;text-align:center;">Credits</th>
                                    <th style="padding:6px;width:50px;text-align:center;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="courses-tbody">
                                {{-- Course Rows Added dynamically --}}
                            </tbody>
                        </table>
                        
                        <div id="no-courses-placeholder" class="text-center py-6 text-slate-500 font-semibold">
                            No Records to display
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel-foot flex justify-between items-center p-3 border-t border-slate-200 bg-slate-50" style="padding:15px;border-top:1px solid #edf2f7;background:#f8fafc;">
                <div>
                    <button type="submit" class="px-4 py-1.5 rounded border border-orange-500 text-orange-600 hover:bg-orange-50 font-bold text-xs" style="padding:6px 15px;background:#fff;border-radius:4px;cursor:pointer;">Submit</button>
                </div>
                <div>
                    <button type="button" onclick="closeModal()" class="px-4 py-1.5 rounded bg-red-600 text-white font-bold text-xs" style="padding:6px 15px;border:none;background:#c53030;color:#fff;border-radius:4px;cursor:pointer;">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- MODAL: PREVIEW COURSES LIST --}}
<div class="modal" id="courses-preview-modal" role="dialog" aria-modal="true">
    <div class="modal-card bg-white rounded-xl shadow-lg border border-slate-200" style="width:min(520px, 94vw); overflow:hidden;">
        <div class="panel-head" style="background:#0A3E50;color:#fff;padding:14px 20px;display:flex;justify-content:between;align-items:center;height:60px;">
            <div>
                <h2 class="text-sm font-bold text-white">Syllabus Courses</h2>
                <small style="color:rgba(255,255,255,0.85);font-size:10px;" id="preview-modal-subtitle">Curriculum schedule details.</small>
            </div>
            <button class="btn btn-secondary" type="button" onclick="closePreviewModal()" style="background:transparent;border:none;color:#fff;font-size:22px;cursor:pointer;padding:0 5px;">&times;</button>
        </div>
        <div class="panel-body p-5 text-xs bg-white" style="padding:20px;max-height:60vh;overflow-y:auto;">
            <table class="w-full text-left" style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid #cbd5e1;font-weight:bold;color:#475569;">
                        <th style="padding:6px;width:120px;">Code</th>
                        <th style="padding:6px;">Title</th>
                        <th style="padding:6px;width:80px;text-align:center;">Credits</th>
                    </tr>
                </thead>
                <tbody id="preview-courses-tbody">
                    {{-- Loaded dynamically --}}
                </tbody>
            </table>
        </div>
        <div class="panel-foot flex justify-end p-3 border-t border-slate-200 bg-slate-50" style="padding:15px;background:#f8fafc;display:flex;justify-content:flex-end;">
            <button type="button" onclick="closePreviewModal()" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs" style="padding:6px 12px;border:none;background:#e2e8f0;border-radius:4px;cursor:pointer;">Close</button>
        </div>
    </div>
</div>

<script>
    // Initial starting datasets mimicking user screenshot
    let initialCurricula = [
        {
            id: 1,
            programme: 'MEMA-BCS - Bachelor of Science in Computer Science',
            level: '1',
            specialisation: 'NA',
            semester: '1',
            courses: [
                { code: 'CSC 101', title: 'Introduction to Computing Systems', credits: 3 },
                { code: 'MAT 101', title: 'Foundations of Mathematics', credits: 3 },
                { code: 'CSC 102', title: 'Structured Programming with C', credits: 4 }
            ]
        },
        {
            id: 2,
            programme: 'MEMA-BCS - Bachelor of Science in Computer Science',
            level: '1',
            specialisation: 'NA',
            semester: '2',
            courses: [
                { code: 'CSC 104', title: 'Database Systems Design', credits: 3 },
                { code: 'MAT 102', title: 'Discrete Mathematics', credits: 3 }
            ]
        },
        {
            id: 3,
            programme: 'MEMA-BCS - Bachelor of Science in Computer Science',
            level: '2',
            specialisation: 'NA',
            semester: '1',
            courses: [
                { code: 'CSC 201', title: 'Data Structures and Algorithms', credits: 4 }
            ]
        },
        {
            id: 4,
            programme: 'MEMA-BCS - Bachelor of Science in Computer Science',
            level: '2',
            specialisation: 'NA',
            semester: '2',
            courses: [
                { code: 'CSC 202', title: 'Object Oriented Programming', credits: 4 }
            ]
        },
        {
            id: 5,
            programme: 'MEMA-BCS - Bachelor of Science in Computer Science',
            level: '3',
            specialisation: 'NA',
            semester: '1',
            courses: [
                { code: 'CSC 311', title: 'Design & Analysis of Algorithms', credits: 3 }
            ]
        },
        {
            id: 6,
            programme: 'MEMA-BBA - Bachelor of Business Administration',
            level: '2',
            specialisation: 'NA',
            semester: '1',
            courses: [
                { code: 'BBA 201', title: 'Business Mathematics', credits: 3 }
            ]
        },
        {
            id: 7,
            programme: 'MEMA-BBA - Bachelor of Business Administration',
            level: '2',
            specialisation: 'NA',
            semester: '2',
            courses: [
                { code: 'BBA 202', title: 'Principles of Marketing', credits: 3 }
            ]
        },
        {
            id: 8,
            programme: 'MEMA-BBA - Bachelor of Business Administration',
            level: '3',
            specialisation: 'NA',
            semester: '1',
            courses: [
                { code: 'BBA 301', title: 'Financial Management', credits: 3 }
            ]
        },
        {
            id: 9,
            programme: 'MEMA-BBA - Bachelor of Business Administration',
            level: '3',
            specialisation: 'NA',
            semester: '2',
            courses: [
                { code: 'BBA 302', title: 'Human Resource Management', credits: 3 }
            ]
        },
        {
            id: 10,
            programme: 'MEMA-BBA - Bachelor of Business Administration',
            level: '3',
            specialisation: 'NA',
            semester: '2',
            courses: [
                { code: 'BBA 303', title: 'Strategic Planning', credits: 3 }
            ]
        }
    ];

    let curricula = initialCurricula;
    let currentPage = 1;

    // Load from LocalStorage if exists
    if (localStorage.getItem('mema_curriculum_masters')) {
        try {
            curricula = JSON.parse(localStorage.getItem('mema_curriculum_masters'));
        } catch (e) {
            console.error('Failed to parse localStorage curriculum masters:', e);
        }
    }

    function renderTable() {
        const tbody = document.getElementById('curricula-tbody');
        const searchInput = document.getElementById('curric-search');
        const limitSelect = document.getElementById('entries-limit');

        const q = searchInput?.value.toLowerCase().trim() || '';
        const limit = parseInt(limitSelect?.value || '10');

        // Filtering
        const filtered = curricula.filter(c => {
            return !q || c.programme.toLowerCase().includes(q) || c.specialisation.toLowerCase().includes(q);
        });

        // Pagination
        const totalItems = filtered.length;
        const totalPages = Math.ceil(totalItems / limit) || 1;
        if (currentPage > totalPages) currentPage = totalPages;

        const startIdx = (currentPage - 1) * limit;
        const endIdx = Math.min(startIdx + limit, totalItems);
        const paginated = filtered.slice(startIdx, endIdx);

        tbody.innerHTML = '';

        if (paginated.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="py-4 text-center text-slate-500 font-semibold">No matching curriculum masters found.</td></tr>`;
        } else {
            paginated.forEach(c => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50/70 transition-colors';
                tr.innerHTML = `
                    <td class="py-3.5 px-4 font-bold text-slate-900">${escapeHtml(c.programme)}</td>
                    <td class="py-3.5 px-4 text-center text-slate-700 font-bold">${c.level}</td>
                    <td class="py-3.5 px-4 text-slate-700">${escapeHtml(c.specialisation)}</td>
                    <td class="py-3.5 px-4 text-center text-slate-700 font-bold">${c.semester}</td>
                    <td class="py-3.5 px-4 text-center">
                        <button type="button" onclick="previewCourses(${c.id})" class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">Preview</button>
                    </td>
                    <td class="py-3.5 px-4 text-center">
                        <button type="button" onclick="openEditModal(${c.id})" class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">Edit</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        // Render Pagination Info & Controls
        const info = document.getElementById('pagination-info');
        if (info) {
            info.textContent = totalItems === 0 ? 'Showing 0 to 0 of 0 entries' : `Showing ${startIdx + 1} to ${endIdx} of ${totalItems} entries`;
        }

        renderPaginationControls(totalPages);
    }

    function renderPaginationControls(totalPages) {
        const wrapper = document.getElementById('pagination-controls');
        wrapper.innerHTML = '';

        // Previous button
        const prevBtn = document.createElement('button');
        prevBtn.type = 'button';
        prevBtn.className = `px-2 py-1 text-xs font-semibold ${currentPage === 1 ? 'text-slate-400 cursor-not-allowed' : 'text-slate-700 hover:text-slate-900'}`;
        prevBtn.textContent = 'Previous';
        if (currentPage > 1) {
            prevBtn.onclick = () => { currentPage--; renderTable(); };
        }
        wrapper.appendChild(prevBtn);

        // Page buttons
        for (let i = 1; i <= totalPages; i++) {
            const pageBtn = document.createElement('button');
            pageBtn.type = 'button';
            pageBtn.className = `px-2.5 py-1 rounded font-bold text-xs ${currentPage === i ? 'bg-orange-500 text-white' : 'hover:bg-slate-100 text-slate-700'}`;
            pageBtn.textContent = i;
            pageBtn.onclick = () => { currentPage = i; renderTable(); };
            wrapper.appendChild(pageBtn);
        }

        // Next button
        const nextBtn = document.createElement('button');
        nextBtn.type = 'button';
        nextBtn.className = `px-2 py-1 text-xs font-semibold ${currentPage === totalPages ? 'text-slate-400 cursor-not-allowed' : 'text-slate-700 hover:text-slate-900'}`;
        nextBtn.textContent = 'Next';
        if (currentPage < totalPages) {
            nextBtn.onclick = () => { currentPage++; renderTable(); };
        }
        wrapper.appendChild(nextBtn);
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    // Modal Helpers
    function openAddModal() {
        const modal = document.getElementById('curric-modal');
        if (!modal) return;

        document.getElementById('form-id').value = '';
        document.getElementById('form-level').value = '1';
        document.getElementById('form-semester').value = '1';
        document.getElementById('form-spec').value = 'NA';

        // Select first programme by default
        const progSelect = document.getElementById('form-programme');
        if (progSelect && progSelect.options.length > 0) {
            progSelect.selectedIndex = 0;
        }

        // Clear course rows
        const coursesTbody = document.getElementById('courses-tbody');
        coursesTbody.innerHTML = '';
        toggleCoursesPlaceholder();

        modal.classList.add('open');
    }

    function openEditModal(id) {
        const modal = document.getElementById('curric-modal');
        const c = curricula.find(item => item.id === id);
        if (!c || !modal) return;

        document.getElementById('form-id').value = c.id;
        document.getElementById('form-programme').value = c.programme;
        document.getElementById('form-level').value = c.level;
        document.getElementById('form-semester').value = c.semester;
        document.getElementById('form-spec').value = c.specialisation;

        // Render existing courses in the modal edit table
        const coursesTbody = document.getElementById('courses-tbody');
        coursesTbody.innerHTML = '';

        if (c.courses && c.courses.length > 0) {
            c.courses.forEach(course => {
                appendCourseRowHtml(course.code, course.title, course.credits);
            });
        }
        toggleCoursesPlaceholder();

        modal.classList.add('open');
    }

    function closeModal() {
        const modal = document.getElementById('curric-modal');
        if (modal) {
            modal.classList.remove('open');
        }
    }

    // Dynamic Course Rows Adder
    function addCourseRow() {
        appendCourseRowHtml('', '', '3');
        toggleCoursesPlaceholder();
    }

    function appendCourseRowHtml(code, title, credits) {
        const tbody = document.getElementById('courses-tbody');
        const tr = document.createElement('tr');
        tr.style.borderBottom = '1px solid #e2e8f0';
        tr.className = 'course-row-item';
        tr.innerHTML = `
            <td style="padding:6px;"><input type="text" class="course-code-input font-mono" style="border:1px solid #cbd5e1;padding:4px;width:95%;text-transform:uppercase;" placeholder="CSC 101" value="${escapeHtml(code)}" required></td>
            <td style="padding:6px;"><input type="text" class="course-title-input" style="border:1px solid #cbd5e1;padding:4px;width:98%;" placeholder="Course Name" value="${escapeHtml(title)}" required></td>
            <td style="padding:6px;text-align:center;"><input type="number" class="course-credits-input font-mono" style="border:1px solid #cbd5e1;padding:4px;width:55px;text-align:center;" min="1" value="${credits}" required></td>
            <td style="padding:6px;text-align:center;"><button type="button" onclick="removeCourseRow(this)" style="background:transparent;border:none;color:#e53e3e;font-size:16px;cursor:pointer;">&times;</button></td>
        `;
        tbody.appendChild(tr);
    }

    function removeCourseRow(btn) {
        btn.closest('tr').remove();
        toggleCoursesPlaceholder();
    }

    function toggleCoursesPlaceholder() {
        const tbody = document.getElementById('courses-tbody');
        const tbl = document.getElementById('courses-table');
        const placeholder = document.getElementById('no-courses-placeholder');

        if (tbody.children.length === 0) {
            tbl.style.display = 'none';
            placeholder.style.display = 'block';
        } else {
            tbl.style.display = 'table';
            placeholder.style.display = 'none';
        }
    }

    // Save Action
    function saveCurric(event) {
        event.preventDefault();
        
        const idVal = document.getElementById('form-id').value;
        const programme = document.getElementById('form-programme').value;
        const level = document.getElementById('form-level').value;
        const semester = document.getElementById('form-semester').value;
        const specialisation = document.getElementById('form-spec').value.trim();

        // Read all course rows
        const courseRows = document.querySelectorAll('.course-row-item');
        const courses = [];
        courseRows.forEach(row => {
            const code = row.querySelector('.course-code-input').value.trim().toUpperCase();
            const title = row.querySelector('.course-title-input').value.trim();
            const credits = parseInt(row.querySelector('.course-credits-input').value || '3');
            courses.push({ code, title, credits });
        });

        if (idVal) {
            // Update
            const id = parseInt(idVal);
            const index = curricula.findIndex(item => item.id === id);
            if (index !== -1) {
                curricula[index] = {
                    id,
                    programme,
                    level,
                    specialisation,
                    semester,
                    courses
                };
                triggerAlert('success', 'Curriculum Master Updated', `Successfully updated curriculum schedule for Level ${level} Semester ${semester}.`);
            }
        } else {
            // Create
            const nextId = curricula.reduce((max, item) => item.id > max ? item.id : max, 0) + 1;
            curricula.push({
                id: nextId,
                programme,
                level,
                specialisation,
                semester,
                courses
            });
            triggerAlert('success', 'Curriculum Master Created', `Successfully scheduled new curriculum master entry.`);
        }

        // Persist to LocalStorage
        localStorage.setItem('mema_curriculum_masters', JSON.stringify(curricula));

        renderTable();
        closeModal();
    }

    // Preview Modal Helpers
    function previewCourses(id) {
        const modal = document.getElementById('courses-preview-modal');
        const c = curricula.find(item => item.id === id);
        if (!c || !modal) return;

        document.getElementById('preview-modal-subtitle').textContent = `${c.programme} • Level ${c.level} Semester ${c.semester}`;
        
        const tbody = document.getElementById('preview-courses-tbody');
        tbody.innerHTML = '';

        if (c.courses && c.courses.length > 0) {
            c.courses.forEach(course => {
                const tr = document.createElement('tr');
                tr.style.borderBottom = '1px solid #edf2f7';
                tr.innerHTML = `
                    <td style="padding:8px;" class="font-mono">${escapeHtml(course.code)}</td>
                    <td style="padding:8px;">${escapeHtml(course.title)}</td>
                    <td style="padding:8px;text-align:center;" class="font-mono font-bold text-emerald-800">${course.credits}</td>
                `;
                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = `<tr><td colspan="3" class="py-4 text-center text-slate-500 font-semibold">No scheduled courses for this semester.</td></tr>`;
        }

        modal.classList.add('open');
    }

    function closePreviewModal() {
        const modal = document.getElementById('courses-preview-modal');
        if (modal) {
            modal.classList.remove('open');
        }
    }

    function triggerAlert(type, title, message) {
        const box = document.getElementById('curric-alert-box');
        const icon = document.getElementById('curric-alert-icon');
        const text = document.getElementById('curric-alert-text');

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
        document.getElementById('curric-alert-box').classList.add('hidden');
    }

    // Initial Render
    document.addEventListener('DOMContentLoaded', () => {
        renderTable();
    });
</script>
@endsection

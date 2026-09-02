@extends('layouts.app')

@section('title', 'Staff Directory & Profiles - SMHR')
@section('section', 'SMHR')

@section('content')
<div class="mema-dashboard-container py-2">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('smhr.dashboard') }}" class="text-xs font-semibold text-[#0A3E50] hover:underline">&larr; SMHR Dashboard</a>
                <span class="text-slate-300">/</span>
                <span class="text-xs font-bold text-slate-700">Staff Directory</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight mt-1">Staff Directory &amp; Profiles</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Master personnel register for academic faculty, administrative officers, and support staff</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="document.getElementById('addStaffModal').classList.remove('hidden')" class="px-3.5 py-2 rounded-lg bg-[#0A3E50] hover:bg-[#08303e] font-bold text-xs transition-colors shadow-2xs inline-flex items-center gap-1.5 text-white cursor-pointer" style="color:#ffffff !important;">
                <i data-lucide="user-plus" class="w-3.5 h-3.5 text-white"></i>
                <span style="color:#ffffff !important;">Add New Staff Member</span>
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Filter & Search Card --}}
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <div class="sm:col-span-4 relative">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                <input type="text" id="staffSearchInput" onkeyup="filterStaffTable()" placeholder="Search staff name, EMP ID, email, designation..." class="w-full pl-9 pr-3 py-1.5 rounded-lg border border-slate-300 text-xs text-slate-900 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div class="sm:col-span-3">
                <select id="schoolFilter" onchange="filterStaffTable()" class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs text-slate-800 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">All Schools / Faculties</option>
                    <option value="School of Computing & Informatics">School of Computing & Informatics</option>
                    <option value="School of Business & Economics">School of Business & Economics</option>
                    <option value="School of Engineering">School of Engineering</option>
                    <option value="School of Health Sciences">School of Health Sciences</option>
                    <option value="Central Administration">Central Administration</option>
                </select>
            </div>

            <div class="sm:col-span-3">
                <select id="typeFilter" onchange="filterStaffTable()" class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs text-slate-800 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">All Contract Types</option>
                    <option value="Permanent">Permanent</option>
                    <option value="Contract">3-Year Contract</option>
                    <option value="Adjunct">Adjunct / Part-Time</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <button type="button" onclick="resetStaffFilters()" class="w-full py-1.5 rounded-lg border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-colors text-center">
                    Reset Filters
                </button>
            </div>
        </div>
    </div>

    {{-- Staff Table --}}
    <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs" id="staffTable">
                <thead>
                    <tr class="bg-slate-50 text-slate-800 font-bold border-b border-slate-200">
                        <th class="py-3 px-4">Staff Member &amp; ID</th>
                        <th class="py-3 px-4">Designation &amp; Rank</th>
                        <th class="py-3 px-4">School &amp; Department</th>
                        <th class="py-3 px-4">Employment Type</th>
                        <th class="py-3 px-4">Qualification</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($staffMembers as $staff)
                        <tr class="hover:bg-slate-50/70 transition-colors staff-row" data-school="{{ $staff['school'] }}" data-type="{{ $staff['type'] }}">
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $staff['name'] }}</div>
                                <div class="font-mono text-[11px] text-[#0A3E50] font-semibold">{{ $staff['id'] }}</div>
                                <div class="text-[10.5px] text-slate-500">{{ $staff['email'] }} &middot; {{ $staff['phone'] }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-800">{{ $staff['designation'] }}</div>
                                <div class="text-[11px] text-slate-500 font-medium">Rank: <strong>{{ $staff['rank'] }}</strong></div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-semibold text-slate-900">{{ $staff['department'] }}</div>
                                <div class="text-[10.5px] text-slate-500">{{ $staff['school'] }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10.5px] font-bold bg-blue-50 text-blue-800 border border-blue-200">
                                    {{ $staff['type'] }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-700 font-medium">
                                {{ $staff['qualification'] }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[10.5px] font-bold @if($staff['status'] === 'ACTIVE') bg-emerald-100 text-emerald-800 @else bg-amber-100 text-amber-800 @endif">
                                    {{ $staff['status'] }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <button type="button" onclick="alert('Opening personnel file for {{ $staff['name'] }} ({{ $staff['id'] }})')" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 font-semibold text-[11px] text-slate-700 transition-colors">
                                    View Dossier
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Staff Modal --}}
<div id="addStaffModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl border border-slate-200 max-w-lg w-full p-6 relative">
        <div class="flex justify-between items-center pb-3 border-b border-slate-100 mb-4">
            <h3 class="text-base font-bold text-slate-900">Register New Staff Member</h3>
            <button type="button" onclick="document.getElementById('addStaffModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('smhr.staff-directory.store') }}" class="space-y-3.5 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 mb-1">Full Official Name</label>
                <input type="text" name="name" required placeholder="e.g. Dr. Jane Mwangi" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Work Email</label>
                    <input type="email" name="email" required placeholder="j.mwangi@mema.ac.ke" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Mobile Phone</label>
                    <input type="text" name="phone" required placeholder="+254 7..." class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Department</label>
                    <input type="text" name="department" required placeholder="Computer Science" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Designation</label>
                    <input type="text" name="designation" required placeholder="Lecturer" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Rank</label>
                    <select name="rank" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs bg-white">
                        <option value="Professor">Professor</option>
                        <option value="Associate Professor">Associate Professor</option>
                        <option value="Senior Lecturer" selected>Senior Lecturer</option>
                        <option value="Lecturer">Lecturer</option>
                        <option value="Assistant Lecturer">Assistant Lecturer</option>
                        <option value="Administrative Officer">Administrative Officer</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Employment Type</label>
                    <select name="employment_type" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs bg-white">
                        <option value="Permanent / Tenured">Permanent / Tenured</option>
                        <option value="3-Year Contract">3-Year Contract</option>
                        <option value="Adjunct / Part-Time">Adjunct / Part-Time</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Highest Academic Qualification</label>
                <input type="text" name="qualification" required placeholder="PhD in Computer Science (UoN)" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('addStaffModal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 font-semibold text-xs hover:bg-slate-50">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-[#0A3E50] hover:bg-[#08303e] text-white font-bold text-xs transition-colors" style="color:#ffffff !important;">Save Staff Member</button>
            </div>
        </form>
    </div>
</div>

<script>
    function filterStaffTable() {
        const query = (document.getElementById('staffSearchInput')?.value || '').toLowerCase().trim();
        const schoolVal = document.getElementById('schoolFilter')?.value || '';
        const typeVal = document.getElementById('typeFilter')?.value || '';
        const rows = document.querySelectorAll('#staffTable tbody tr.staff-row');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const rowSchool = row.dataset.school || '';
            const rowType = row.dataset.type || '';

            const matchesQuery = !query || text.includes(query);
            const matchesSchool = !schoolVal || rowSchool.includes(schoolVal);
            const matchesType = !typeVal || rowType.includes(typeVal);

            if (matchesQuery && matchesSchool && matchesType) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function resetStaffFilters() {
        document.getElementById('staffSearchInput').value = '';
        document.getElementById('schoolFilter').value = '';
        document.getElementById('typeFilter').value = '';
        filterStaffTable();
    }
</script>
@endsection

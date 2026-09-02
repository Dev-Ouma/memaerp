@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="mema-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">{{ $title }}</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">{{ $description }}</p>
        </div>
        
        {{-- Download Export Menu --}}
        <div class="relative inline-block text-left" id="export-dropdown-wrapper">
            <button type="button" onclick="document.getElementById('export-menu').classList.toggle('hidden')" class="px-4 py-1.5 rounded-md bg-[#0A3E50] text-white hover:bg-[#082f3e] font-bold text-xs transition-all shadow-xs flex items-center gap-1.5">
                <i data-lucide="download" class="w-3.5 h-3.5"></i> Export Report
            </button>
            <div id="export-menu" class="hidden absolute right-0 mt-2 w-40 rounded-md shadow-lg bg-white border border-slate-200 ring-1 ring-black/5 z-55">
                <div class="py-1" role="none">
                    <a href="#" onclick="alert('Exporting as PDF...'); document.getElementById('export-menu').classList.add('hidden'); return false;" class="text-slate-700 block px-4 py-2 text-xs hover:bg-slate-100 font-semibold flex items-center gap-2">
                        <i data-lucide="file-text" class="w-3.5 h-3.5 text-red-600"></i> Export PDF Document
                    </a>
                    <a href="#" onclick="alert('Exporting as Excel...'); document.getElementById('export-menu').classList.add('hidden'); return false;" class="text-slate-700 block px-4 py-2 text-xs hover:bg-slate-100 font-semibold flex items-center gap-2">
                        <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 text-emerald-600"></i> Export Excel Sheet
                    </a>
                    <a href="#" onclick="alert('Exporting as CSV...'); document.getElementById('export-menu').classList.add('hidden'); return false;" class="text-slate-700 block px-4 py-2 text-xs hover:bg-slate-100 font-semibold flex items-center gap-2">
                        <i data-lucide="file-code" class="w-3.5 h-3.5 text-blue-600"></i> Export CSV Dataset
                    </a>
                    <a href="#" onclick="alert('Exporting as JSON...'); document.getElementById('export-menu').classList.add('hidden'); return false;" class="text-slate-700 block px-4 py-2 text-xs hover:bg-slate-100 font-semibold flex items-center gap-2">
                        <i data-lucide="braces" class="w-3.5 h-3.5 text-purple-600"></i> Export JSON Dataset
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Close dropdown if clicking outside --}}
    <script>
        window.addEventListener('click', function(e){
            const wrapper = document.getElementById('export-dropdown-wrapper');
            const menu = document.getElementById('export-menu');
            if (wrapper && !wrapper.contains(e.target) && menu) {
                menu.classList.add('hidden');
            }
        });
    </script>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach($stats as $st)
            <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
                <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">{{ $st['label'] }}</div>
                <div class="text-2xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $st['val'] }}</div>
                <p class="text-xs text-slate-500 mb-3 leading-snug">Latest updated status.</p>
                <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">Verified Log</span></div>
            </div>
        @endforeach
    </div>

    {{-- Search Filters --}}
    <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input type="text" id="reportSearch" onkeyup="filterReportTable()" placeholder="Quick filter table rows by typing any keyword..." class="w-full px-4 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-[#0A3E50] focus:border-[#0A3E50]" />
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="document.getElementById('reportSearch').value=''; filterReportTable();" class="px-4 py-2 border border-slate-300 text-slate-700 hover:bg-slate-50 rounded-lg font-bold text-xs transition-colors">
                    Reset Filter
                </button>
            </div>
        </div>
    </div>

    {{-- Main Report Table --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs" id="reportTable">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        @foreach($headers as $head)
                            <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">{{ $head }}</th>
                        @endforeach
                        <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-24 uppercase text-[11px]" style="color:#ffffff !important;">Audit Trace</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($rows as $row)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            @foreach($row as $cell)
                                <td class="py-3.5 px-4 text-slate-900 text-xs font-medium">{{ $cell }}</td>
                            @endforeach
                            <td class="py-3.5 px-4 text-center">
                                <button type="button" onclick="alert('Viewing audit trace details for this record line.');" class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">Trace</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Simple Client-Side Table Filter --}}
<script>
    function filterReportTable() {
        const input = document.getElementById("reportSearch");
        const filter = input.value.toLowerCase();
        const table = document.getElementById("reportTable");
        const tr = table.getElementsByTagName("tr");

        for (let i = 1; i < tr.length; i++) {
            let found = false;
            const tds = tr[i].getElementsByTagName("td");
            for (let j = 0; j < tds.length - 1; j++) {
                if (tds[j]) {
                    const txtValue = tds[j].textContent || tds[j].innerText;
                    if (txtValue.toLowerCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
            }
            if (found) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
</script>
@endsection

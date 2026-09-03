@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="mema-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admissions.reports') }}" class="text-xs font-semibold text-[#0A3E50] hover:underline flex items-center gap-1">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Reports Center
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ $reportKey }}</span>
            </div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight mt-1">{{ $title }}</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">{{ $description }}</p>
        </div>
        
        {{-- Actions Menu --}}
        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()" class="px-3 py-1.5 rounded-md border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 font-bold text-xs transition-all shadow-xs flex items-center gap-1.5">
                <i data-lucide="printer" class="w-3.5 h-3.5 text-slate-600"></i> Print View
            </button>

            {{-- Download Export Menu --}}
            <div class="relative inline-block text-left" id="export-dropdown-wrapper">
                <button type="button" onclick="document.getElementById('export-menu').classList.toggle('hidden')" class="px-4 py-1.5 rounded-md bg-[#0A3E50] text-white hover:bg-[#082f3e] font-bold text-xs transition-all shadow-xs flex items-center gap-1.5">
                    <i data-lucide="download" class="w-3.5 h-3.5"></i> Export Report
                </button>
                <div id="export-menu" class="hidden absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white border border-slate-200 ring-1 ring-black/5 z-55">
                    <div class="py-1" role="none">
                        <a href="{{ route('reports.export', ['report' => $reportKey, 'format' => 'pdf', 'q' => request('q'), 'status' => request('status')]) }}" target="_blank" class="text-slate-700 block px-4 py-2 text-xs hover:bg-slate-100 font-semibold flex items-center gap-2">
                            <i data-lucide="file-text" class="w-3.5 h-3.5 text-red-600"></i> Export PDF Document
                        </a>
                        <a href="{{ route('reports.export', ['report' => $reportKey, 'format' => 'xlsx', 'q' => request('q'), 'status' => request('status')]) }}" class="text-slate-700 block px-4 py-2 text-xs hover:bg-slate-100 font-semibold flex items-center gap-2">
                            <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 text-emerald-600"></i> Export Excel (.xlsx)
                        </a>
                        <a href="{{ route('reports.export', ['report' => $reportKey, 'format' => 'csv', 'q' => request('q'), 'status' => request('status')]) }}" class="text-slate-700 block px-4 py-2 text-xs hover:bg-slate-100 font-semibold flex items-center gap-2">
                            <i data-lucide="file-code" class="w-3.5 h-3.5 text-blue-600"></i> Export CSV Dataset
                        </a>
                    </div>
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
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 print:grid-cols-4">
        @foreach($stats as $st)
            <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
                <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">{{ $st['label'] }}</div>
                <div class="text-2xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none">{{ $st['val'] }}</div>
                <p class="text-xs text-slate-500 mb-3 leading-snug">Institutional live metric.</p>
                <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-[#1E8449] bg-emerald-50 border border-emerald-200/70">Live Database</span></div>
            </div>
        @endforeach
    </div>

    {{-- Search & Filter Controls --}}
    <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs mb-6 print:hidden">
        <form method="GET" action="{{ url()->current() }}" class="flex flex-col sm:flex-row gap-3 items-center">
            <div class="flex-1 w-full">
                <input type="text" name="q" value="{{ request('q') }}" id="reportSearch" onkeyup="filterReportTable()" placeholder="Quick filter table rows by typing any keyword..." class="w-full px-4 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-[#0A3E50] focus:border-[#0A3E50]" />
            </div>
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-[#0A3E50] text-white hover:bg-[#082f3e] rounded-lg font-bold text-xs transition-colors">
                    Filter
                </button>
                <a href="{{ url()->current() }}" class="px-4 py-2 border border-slate-300 text-slate-700 hover:bg-slate-50 rounded-lg font-bold text-xs transition-colors">
                    Reset
                </a>
            </div>
        </form>
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
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($rows as $row)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            @foreach($row as $cell)
                                <td class="py-3.5 px-4 text-slate-900 text-xs font-medium">{{ $cell }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($headers) }}" class="py-12 text-center text-slate-500">
                                <i data-lucide="inbox" class="w-8 h-8 text-slate-300 mx-auto mb-2"></i>
                                <p class="text-xs font-semibold">No records match the selected criteria.</p>
                                <p class="text-[11px] text-slate-400 mt-1">Try resetting the filter search.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(count($rows) > 0)
            <div class="p-3 bg-slate-50 border-t border-slate-100 text-xs text-slate-500 flex justify-between items-center">
                <span>Showing {{ count($rows) }} verified records from PostgreSQL</span>
                <span class="font-bold text-slate-700">Tamper-evident system log</span>
            </div>
        @endif
    </div>
</div>

<script>
    function filterReportTable() {
        const input = document.getElementById("reportSearch");
        const filter = input.value.toUpperCase();
        const table = document.getElementById("reportTable");
        const tr = table.getElementsByTagName("tr");

        for (let i = 1; i < tr.length; i++) {
            let rowText = tr[i].textContent || tr[i].innerText;
            if (rowText.toUpperCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
</script>
@endsection

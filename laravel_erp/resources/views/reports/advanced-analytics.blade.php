@extends('layouts.app')

@section('title', 'Advanced Analytics & Insights')

@section('content')
<div class="mema-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Advanced Analytics & Academic Intelligence</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Real-time predictive insights, cash flow collections, cohort progress trends, and senate reporting statistics</p>
        </div>
        
        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print();" class="px-4 py-1.5 rounded-md border border-[#0A3E50] text-[#0A3E50] hover:bg-slate-50 font-bold text-xs transition-colors shadow-2xs flex items-center gap-1.5">
                <i data-lucide="printer" class="w-3.5 h-3.5"></i> Print Dashboard
            </button>
            <div class="relative inline-block text-left" id="export-analytics-wrapper">
                <button type="button" onclick="document.getElementById('export-analytics-menu').classList.toggle('hidden')" class="px-4 py-1.5 rounded-md bg-[#0A3E50] text-white hover:bg-[#082f3e] font-bold text-xs transition-all shadow-xs flex items-center gap-1.5">
                    <i data-lucide="download" class="w-3.5 h-3.5"></i> Export Report
                </button>
                <div id="export-analytics-menu" class="hidden absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white border border-slate-200 ring-1 ring-black/5 z-55">
                    <div class="py-1" role="none">
                        <a href="#" onclick="alert('Exporting Analytics Booklet as PDF...'); document.getElementById('export-analytics-menu').classList.add('hidden'); return false;" class="text-slate-700 block px-4 py-2 text-xs hover:bg-slate-100 font-semibold flex items-center gap-2">
                            <i data-lucide="file-text" class="w-3.5 h-3.5 text-red-600"></i> Export PDF Booklet
                        </a>
                        <a href="#" onclick="alert('Exporting Charts Summary as Excel...'); document.getElementById('export-analytics-menu').classList.add('hidden'); return false;" class="text-slate-700 block px-4 py-2 text-xs hover:bg-slate-100 font-semibold flex items-center gap-2">
                            <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 text-emerald-600"></i> Export Excel Data
                        </a>
                        <a href="#" onclick="alert('Exporting Raw Values as CSV...'); document.getElementById('export-analytics-menu').classList.add('hidden'); return false;" class="text-slate-700 block px-4 py-2 text-xs hover:bg-slate-100 font-semibold flex items-center gap-2">
                            <i data-lucide="file-code" class="w-3.5 h-3.5 text-blue-600"></i> Export CSV Dataset
                        </a>
                        <a href="#" onclick="downloadAllChartsAsImages(); document.getElementById('export-analytics-menu').classList.add('hidden'); return false;" class="text-slate-700 block px-4 py-2 text-xs hover:bg-slate-100 font-semibold flex items-center gap-2">
                            <i data-lucide="image" class="w-3.5 h-3.5 text-purple-600"></i> Export Charts (PNG Images)
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Close dropdown if clicking outside --}}
    <script>
        window.addEventListener('click', function(e){
            const wrapper = document.getElementById('export-analytics-wrapper');
            const menu = document.getElementById('export-analytics-menu');
            if (wrapper && !wrapper.contains(e.target) && menu) {
                menu.classList.add('hidden');
            }
        });
    </script>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Total Student Enrollment</div>
            <div class="text-3xl font-extrabold text-[#0A3E50] mt-2 mb-1.5 leading-none">{{ number_format($stats['totalEnrollment']) }} Scholars</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Active campus checking status.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200/70">Total Scholars</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Cash Flow Recovery Ratio</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['clearedRatio'] }} Cleared</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Invoiced vs net cash received.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">Revenue Recovered</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Academic Retention Rate</div>
            <div class="text-3xl font-extrabold text-blue-700 mt-2 mb-1.5 leading-none">{{ $stats['retentionRate'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Trimester cohort survival rate.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">Retention Rate</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Graduation Cleared Roster</div>
            <div class="text-lg font-extrabold text-purple-900 mt-3 mb-2 leading-none">{{ $stats['graduationAccuracy'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Accurate Senate-cleared count.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-purple-800 bg-purple-50 border border-purple-200">Graduation Audit</span></div>
        </div>
    </div>

    {{-- Main Visualizations Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        
        {{-- Chart 1: Enrollment Growth --}}
        <div class="bg-white p-5 border border-slate-200 rounded-xl shadow-xs">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3 mb-4">
                <div>
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide">5-Year Enrollment Trend Analysis</h3>
                    <p class="text-[10px] text-slate-400">Total enrolled students from 2022 to 2026</p>
                </div>
                <button onclick="downloadChartImage('enrollmentChart', 'enrollment_trend.png')" class="text-slate-400 hover:text-orange-500 transition-colors" title="Download Chart Image">
                    <i data-lucide="image-down" class="w-4 h-4"></i>
                </button>
            </div>
            <div class="h-64 relative">
                <canvas id="enrollmentChart"></canvas>
            </div>
        </div>

        {{-- Chart 2: Fee Collections vs Deficits --}}
        <div class="bg-white p-5 border border-slate-200 rounded-xl shadow-xs">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3 mb-4">
                <div>
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Trimester Fee Revenue Collection Analysis</h3>
                    <p class="text-[10px] text-slate-400">Invoiced Billings vs Recovered Cash vs Arrears</p>
                </div>
                <button onclick="downloadChartImage('revenueChart', 'revenue_vs_deficits.png')" class="text-slate-400 hover:text-orange-500 transition-colors" title="Download Chart Image">
                    <i data-lucide="image-down" class="w-4 h-4"></i>
                </button>
            </div>
            <div class="h-64 relative">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        {{-- Chart 3: School Wise Students Share --}}
        <div class="bg-white p-5 border border-slate-200 rounded-xl shadow-xs">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3 mb-4">
                <div>
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide">School-Wise Candidate Distribution</h3>
                    <p class="text-[10px] text-slate-400">Ratio share split by academic faculty</p>
                </div>
                <button onclick="downloadChartImage('schoolChart', 'school_share.png')" class="text-slate-400 hover:text-orange-500 transition-colors" title="Download Chart Image">
                    <i data-lucide="image-down" class="w-4 h-4"></i>
                </button>
            </div>
            <div class="h-64 relative flex justify-center">
                <canvas id="schoolChart"></canvas>
            </div>
        </div>

        {{-- Chart 4: Student Performance Radar --}}
        <div class="bg-white p-5 border border-slate-200 rounded-xl shadow-xs">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3 mb-4">
                <div>
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Student Performance Distribution Metrics</h3>
                    <p class="text-[10px] text-slate-400">Cumulative grade scores mapping analysis</p>
                </div>
                <button onclick="downloadChartImage('performanceChart', 'performance_distribution.png')" class="text-slate-400 hover:text-orange-500 transition-colors" title="Download Chart Image">
                    <i data-lucide="image-down" class="w-4 h-4"></i>
                </button>
            </div>
            <div class="h-64 relative flex justify-center">
                <canvas id="performanceChart"></canvas>
            </div>
        </div>

        {{-- Chart 5: Operational Status Polar Area --}}
        <div class="bg-white p-5 border border-slate-200 rounded-xl shadow-xs lg:col-span-2">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3 mb-4">
                <div>
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide">System Operations SLA compliance metrics</h3>
                    <p class="text-[10px] text-slate-400">LMS engagement ratios, library turnarounds, registration clearance speed, and grade approvals SLA status</p>
                </div>
                <button onclick="downloadChartImage('operationsChart', 'operations_compliance.png')" class="text-slate-400 hover:text-orange-500 transition-colors" title="Download Chart Image">
                    <i data-lucide="image-down" class="w-4 h-4"></i>
                </button>
            </div>
            <div class="h-80 relative flex justify-center">
                <canvas id="operationsChart"></canvas>
            </div>
        </div>

    </div>
</div>

{{-- Chart.js via CDN for premium visual rendering --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        
        // 1. Enrollment Chart
        const ctx1 = document.getElementById('enrollmentChart').getContext('2d');
        window.chart1 = new Chart(ctx1, {
            type: 'line',
            data: {
                labels: ['2022', '2023', '2024', '2025', '2026'],
                datasets: [{
                    label: 'Enrolled Students',
                    data: [8200, 9500, 11400, 13100, 14850],
                    borderColor: '#E67E22',
                    backgroundColor: 'rgba(230, 126, 34, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#0A3E50',
                    pointBorderColor: '#fff',
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { font: { size: 10 } } },
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                }
            }
        });

        // 2. Revenue Chart
        const ctx2 = document.getElementById('revenueChart').getContext('2d');
        window.chart2 = new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: ['Tuition Billing', 'Administrative', 'Library Access', 'Graduation Gowns'],
                datasets: [
                    {
                        label: 'Invoiced Amount',
                        data: [748.5, 45.2, 12.8, 8.4],
                        backgroundColor: '#0A3E50',
                        borderRadius: 4
                    },
                    {
                        label: 'Collected Cash',
                        data: [682.4, 45.2, 12.0, 7.8],
                        backgroundColor: '#E67E22',
                        borderRadius: 4
                    },
                    {
                        label: 'Arrears Balance',
                        data: [66.1, 0, 0.8, 0.6],
                        backgroundColor: '#E74C3C',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } } },
                scales: {
                    y: { 
                        title: { display: true, text: 'Value (Millions KES)', font: { size: 10 } },
                        grid: { color: 'rgba(0,0,0,0.05)' }, 
                        ticks: { font: { size: 10 } } 
                    },
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                }
            }
        });

        // 3. School Wise Students Share
        const ctx3 = document.getElementById('schoolChart').getContext('2d');
        window.chart3 = new Chart(ctx3, {
            type: 'doughnut',
            data: {
                labels: ['Science & Tech (SST)', 'Business & Management', 'Humanities & Arts', 'Graduate School Studies'],
                datasets: [{
                    data: [45, 30, 15, 10],
                    backgroundColor: ['#0A3E50', '#E67E22', '#2980B9', '#9B59B6'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'right', labels: { boxWidth: 12, font: { size: 10 } } } }
            }
        });

        // 4. Student Performance Radar
        const ctx4 = document.getElementById('performanceChart').getContext('2d');
        window.chart4 = new Chart(ctx4, {
            type: 'radar',
            data: {
                labels: ['First Class Honours', 'Second Class (Upper)', 'Second Class (Lower)', 'Pass Division', 'Supplementary / Retakes'],
                datasets: [{
                    label: 'SST Faculty Distribution',
                    data: [15, 55, 20, 6, 4],
                    borderColor: '#E67E22',
                    backgroundColor: 'rgba(230, 126, 34, 0.2)',
                    borderWidth: 2,
                    pointBackgroundColor: '#0A3E50'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    r: { ticks: { font: { size: 9 } }, pointLabels: { font: { size: 9 } } }
                }
            }
        });

        // 5. Operations Polar Area
        const ctx5 = document.getElementById('operationsChart').getContext('2d');
        window.chart5 = new Chart(ctx5, {
            type: 'polarArea',
            data: {
                labels: ['LMS Activity Ratios', 'Library Circulation Speed', 'Check-In Clearance Times', 'Marks approval Speed SLA', 'Graduation audit Cleared'],
                datasets: [{
                    data: [85, 72, 91, 78, 98],
                    backgroundColor: [
                        'rgba(10, 62, 80, 0.7)',
                        'rgba(230, 126, 34, 0.7)',
                        'rgba(41, 128, 185, 0.7)',
                        'rgba(155, 89, 182, 0.7)',
                        'rgba(46, 204, 113, 0.7)'
                    ],
                    borderColor: '#fff',
                    borderWidth: 1.5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'right', labels: { boxWidth: 12, font: { size: 10 } } } }
            }
        });
    });

    /**
     * Download individual chart as PNG image
     */
    function downloadChartImage(canvasId, filename) {
        const canvas = document.getElementById(canvasId);
        if (canvas) {
            const link = document.createElement('a');
            link.download = filename;
            link.href = canvas.toDataURL("image/png");
            link.click();
        }
    }

    /**
     * Batch download all charts
     */
    function downloadAllChartsAsImages() {
        downloadChartImage('enrollmentChart', 'enrollment_trend.png');
        setTimeout(() => downloadChartImage('revenueChart', 'revenue_vs_deficits.png'), 200);
        setTimeout(() => downloadChartImage('schoolChart', 'school_share.png'), 400);
        setTimeout(() => downloadChartImage('performanceChart', 'performance_distribution.png'), 600);
        setTimeout(() => downloadChartImage('operationsChart', 'operations_compliance.png'), 800);
    }
</script>
@endsection

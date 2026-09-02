@extends('layouts.app')

@section('title', 'Recycle Bin & Trash Recovery')

@section('content')
<div class="mema-dashboard-container py-2">
    
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-600 shadow-2xs">
                    <i data-lucide="trash-2" class="w-4.5 h-4.5"></i>
                </div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight" style="color: #0f172a !important;">System Recycle Bin & Data Recovery</h1>
            </div>
            <p class="text-xs text-slate-600 mt-1 font-medium" style="color: #475569 !important;">Safeguard and restore soft-deleted academic entities, departments, programmes, syllabi and cohorts across MEMA ERP</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-900 border border-emerald-200 shadow-2xs">
                <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600"></i>
                <span>Audit-Governed Recovery & SLA Protection</span>
            </span>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide" style="color: #1e293b !important;">Trashed Records</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-2 mb-1.5 leading-none" style="color: #0f172a !important;">{{ $stats['totalDeleted'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug" style="color: #64748b !important;">Soft-deleted items across modules.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-800 bg-slate-100 border border-slate-200/70" style="color: #1e293b !important; background: #f1f5f9 !important;">Database Protected</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide" style="color: #1e293b !important;">Storage Reclaimed</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none" style="color: #047857 !important;">{{ $stats['storageReclaimed'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug" style="color: #64748b !important;">Relational storage recoverable.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-900 bg-emerald-50 border border-emerald-200" style="color: #065f46 !important; background: #ecfdf5 !important;">Optimized</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide" style="color: #1e293b !important;">Retention Policy</div>
            <div class="text-2xl font-extrabold text-blue-900 mt-2 mb-1.5 leading-none" style="color: #1e3a8a !important;">{{ $stats['retentionPolicy'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug" style="color: #64748b !important;">Automatic permanent purge cycle.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-900 bg-blue-50 border border-blue-200" style="color: #1e3a8a !important; background: #eff6ff !important;">Compliance Standard</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide" style="color: #1e293b !important;">Expiring Soon</div>
            <div class="text-3xl font-extrabold text-amber-700 mt-2 mb-1.5 leading-none" style="color: #b45309 !important;">{{ $stats['expiringSoon'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug" style="color: #64748b !important;">Purging within 7 days.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-amber-900 bg-amber-50 border border-amber-200" style="color: #78350f !important; background: #fffbeb !important;">Notice Period</span></div>
        </div>
    </div>

    {{-- Filter Tabs & Search Bar --}}
    <div class="flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4 mb-4">
        
        {{-- Entity Filter Tabs --}}
        <div class="flex flex-wrap items-center gap-1.5">
            <a href="{{ route('admin.setups.recycle-bin.index') }}" 
               style="{{ empty($selectedType) ? 'background-color:#0A3E50 !important; color:#ffffff !important; border:1px solid #0A3E50 !important;' : 'background-color:#ffffff !important; color:#1e293b !important; border:1px solid #cbd5e1 !important;' }}"
               class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all shadow-2xs hover:bg-slate-50 flex items-center gap-1.5">
                <span>All Items</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px]" style="{{ empty($selectedType) ? 'background:rgba(255,255,255,0.25) !important; color:#ffffff !important; font-weight:700;' : 'background:#f1f5f9 !important; color:#334155 !important; font-weight:700;' }}">{{ $stats['totalDeleted'] }}</span>
            </a>
            @foreach($typeBreakdown as $typeKey => $info)
                <a href="{{ route('admin.setups.recycle-bin.index', ['type' => $typeKey]) }}" 
                   style="{{ $selectedType === $typeKey ? 'background-color:#0A3E50 !important; color:#ffffff !important; border:1px solid #0A3E50 !important;' : 'background-color:#ffffff !important; color:#1e293b !important; border:1px solid #cbd5e1 !important;' }}"
                   class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 shadow-2xs hover:bg-slate-50">
                    <i data-lucide="{{ $info['icon'] }}" class="w-3.5 h-3.5" style="{{ $selectedType === $typeKey ? 'color:#ffffff !important;' : 'color:#475569 !important;' }}"></i>
                    <span style="{{ $selectedType === $typeKey ? 'color:#ffffff !important; font-weight:700;' : 'color:#1e293b !important;' }}">{{ $info['label'] }}</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px]" style="{{ $selectedType === $typeKey ? 'background:rgba(255,255,255,0.25) !important; color:#ffffff !important; font-weight:700;' : 'background:#f1f5f9 !important; color:#334155 !important; font-weight:700;' }}">{{ $info['count'] }}</span>
                </a>
            @endforeach
        </div>

        {{-- Live Search Input --}}
        <div class="w-full md:w-72">
            <div class="relative">
                <input type="text" id="recycle-search-input" placeholder="Search by title, code, module…" value="{{ $search }}" class="w-full bg-white border border-slate-300 rounded-lg px-3.5 py-2 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-[#0A3E50] shadow-2xs" style="color: #0f172a !important;">
                <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute right-3 top-2.5"></i>
            </div>
        </div>
    </div>

    {{-- Trashed Items Table --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs" id="recycle-table">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important; background-color:#0A3E50 !important;">Entity Suite</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important; background-color:#0A3E50 !important;">Record Title & Code</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important; background-color:#0A3E50 !important;">Deleted At</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important; background-color:#0A3E50 !important;">Auto-Purge SLA</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-white text-center w-36 uppercase text-[11px]" style="color:#ffffff !important; background-color:#0A3E50 !important;">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white" id="recycle-tbody">
                    @forelse($items as $item)
                        <tr class="hover:bg-slate-50/70 transition-colors recycle-row" data-search="{{ strtolower($item['title'].' '.$item['code'].' '.$item['type_label']) }}">
                            
                            {{-- Entity Suite Badge --}}
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $item['type_badge'] }}">
                                    <i data-lucide="{{ $item['type_icon'] }}" class="w-3.5 h-3.5"></i>
                                    <span>{{ $item['type_label'] }}</span>
                                </span>
                            </td>

                            {{-- Title & Code --}}
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-xs" style="color: #0f172a !important;">{{ $item['title'] }}</div>
                                <div class="font-mono text-[10.5px] mt-0.5" style="color: #475569 !important;">Code: <strong style="color: #0f172a !important;">{{ $item['code'] }}</strong></div>
                            </td>

                            {{-- Deleted At --}}
                            <td class="py-3.5 px-4 font-mono text-[11px]" style="color: #334155 !important;">
                                {{ $item['deleted_at'] }}
                            </td>

                            {{-- SLA Countdown --}}
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2.5 py-0.5 rounded text-[10.5px] font-bold {{ $item['days_left'] <= 7 ? 'bg-rose-100 text-rose-900 border border-rose-200' : 'bg-slate-100 text-slate-800 border border-slate-200' }}" style="{{ $item['days_left'] <= 7 ? 'color:#881337 !important; background:#ffe4e6 !important;' : 'color:#1e293b !important; background:#f1f5f9 !important;' }}">
                                    {{ $item['days_left'] }} days remaining
                                </span>
                            </td>

                            {{-- Action Buttons --}}
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    
                                    {{-- Restore Button --}}
                                    <form method="POST" action="{{ route('admin.setups.recycle-bin.restore', ['deletion' => $item['id']]) }}" data-processing-message="Restoring item…">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded border border-emerald-500 font-semibold text-xs transition-colors flex items-center gap-1 shadow-2xs hover:bg-emerald-50" style="color: #065f46 !important; background: #ffffff !important;" title="Restore item">
                                            <i data-lucide="rotate-ccw" class="w-3 h-3" style="color: #065f46 !important;"></i> Restore
                                        </button>
                                    </form>

                                    {{-- Inspect Snapshot Modal Button --}}
                                    <button type="button" 
                                            onclick='inspectSnapshot(@json($item))' 
                                            class="p-1 rounded border border-slate-200 hover:bg-slate-50 transition-colors"
                                            style="color: #334155 !important; background: #ffffff !important;"
                                            title="View Snapshot">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    </button>

                                    {{-- Permanent Purge Button --}}
                                    <button type="button" 
                                            onclick="confirmPurge('{{ $item['id'] }}', '{{ addslashes($item['title']) }}')" 
                                            class="p-1 rounded border border-red-200 hover:bg-red-50 transition-colors"
                                            style="color: #dc2626 !important; background: #ffffff !important;"
                                            title="Request Permanent Purge">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5" style="color: #dc2626 !important;"></i>
                                    </button>

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <i data-lucide="check-circle" class="w-8 h-8 text-emerald-500/60"></i>
                                    <div class="font-bold text-sm" style="color: #334155 !important;">Recycle Bin is Empty</div>
                                    <p class="text-xs max-w-sm" style="color: #64748b !important;">No soft-deleted records found. When academic schools, departments, courses or cohorts are deleted, they will appear here for safe recovery.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($items, 'hasPages') && $items->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 bg-slate-50/50">
                {{ $items->links() }}
            </div>
        @endif
    </div>

</div>

{{-- MODAL 1: INSPECT SNAPSHOT --}}
<div class="modal" id="snapshot-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(620px, 94vw);">
        <div class="panel-head" style="background:#0A3E50;color:#fff;padding:14px 20px;border-radius:10px 10px 0 0;">
            <div>
                <h2 class="text-sm font-bold text-white" id="snapshot-modal-title" style="color:#ffffff !important;">Record Snapshot</h2>
                <small style="color:rgba(255,255,255,0.85);" id="snapshot-modal-sub">Historical data state at time of deletion.</small>
            </div>
            <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
        </div>
        <div class="panel-body p-5">
            <div class="space-y-3">
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs">
                    <div class="text-slate-500 text-[11px] font-semibold uppercase">Entity Information</div>
                    <div class="font-bold text-slate-900 text-sm mt-0.5" id="snapshot-entity-name" style="color: #0f172a !important;"></div>
                    <div class="font-mono text-slate-600 mt-0.5" id="snapshot-entity-code"></div>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Payload JSON Attributes</label>
                    <pre class="bg-slate-900 text-slate-100 p-3.5 rounded-xl text-[11px] font-mono overflow-x-auto max-h-64" id="snapshot-json-display"></pre>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-5 pt-3 border-t border-slate-100">
                <button type="button" class="btn btn-secondary text-xs" data-modal-close>Close</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL 2: PURGE REQUEST CONFIRMATION --}}
<div class="modal" id="purge-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min(480px, 94vw);">
        <div class="panel-head" style="background:#dc2626;color:#fff;padding:14px 20px;border-radius:10px 10px 0 0;">
            <div>
                <h2 class="text-sm font-bold text-white" style="color:#ffffff !important;">Request Permanent Purge</h2>
                <small style="color:rgba(255,255,255,0.85);">Maker-checker governed audit compliance.</small>
            </div>
            <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;"><i data-lucide="x"></i></button>
        </div>
        <form class="panel-body p-5" id="purge-form" method="POST" action="" data-processing-message="Submitting purge request…">
            @csrf
            <p class="text-xs text-slate-700 mb-2">
                Initiate a permanent purge request for <strong id="purge-record-title" class="text-slate-900" style="color: #0f172a !important;"></strong>.
            </p>
            <div class="mb-3">
                <label class="block text-xs font-bold text-slate-700 mb-1">Purge Justification / Compliance Reason <span class="text-red-500">*</span></label>
                <textarea name="reason" rows="3" required placeholder="State regulatory, audit or duplication justification (minimum 10 characters)..." class="w-full border border-slate-300 rounded-lg p-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]"></textarea>
            </div>
            <p class="text-[11px] text-amber-800 bg-amber-50 p-2.5 rounded-lg border border-amber-200/80">
                <i data-lucide="shield-alert" class="w-3.5 h-3.5 inline mr-1 text-amber-600"></i> Two-person rule enforced: An independent checker must approve before permanent database deletion.
            </p>
            <div class="flex justify-end gap-2 mt-5 pt-3 border-t border-slate-100">
                <button type="button" class="btn btn-secondary text-xs" data-modal-close>Cancel</button>
                <button type="submit" class="btn text-xs bg-red-600 hover:bg-red-700 text-white font-semibold flex items-center gap-1.5">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Submit Purge Request
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function inspectSnapshot(item) {
        document.getElementById('snapshot-modal-title').textContent = `${item.type_label} Snapshot`;
        document.getElementById('snapshot-modal-sub').textContent = `Deleted on ${item.deleted_at}`;
        document.getElementById('snapshot-entity-name').textContent = item.title;
        document.getElementById('snapshot-entity-code').textContent = `Identifier: ${item.code}`;
        document.getElementById('snapshot-json-display').textContent = JSON.stringify(item.snapshot, null, 2);
        document.getElementById('snapshot-modal').classList.add('open');
    }

    function confirmPurge(id, title) {
        document.getElementById('purge-record-title').textContent = title;
        document.getElementById('purge-form').action = `/admin-setups/recycle-bin/purge/${id}/request`;
        document.getElementById('purge-modal').classList.add('open');
    }

    // Client-side search filtering
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('recycle-search-input');
        const rows = document.querySelectorAll('.recycle-row');

        searchInput?.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            rows.forEach(row => {
                const text = row.dataset.search || '';
                row.style.display = (!query || text.includes(query)) ? '' : 'none';
            });
        });
    });
</script>
@endsection

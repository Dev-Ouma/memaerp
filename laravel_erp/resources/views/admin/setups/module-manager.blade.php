@extends('layouts.app')

@section('title', 'Module Manager')

@section('content')
<div class="mema-dashboard-container py-2">

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">MEMA ERP Module Manager</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Enable or disable any ERP module suite. State is persisted and used by routes, the sidebar, and related module actions.</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" id="enable-all-btn" class="px-4 py-1.5 rounded-md border border-emerald-500 text-emerald-700 hover:bg-emerald-50 font-bold text-xs transition-colors shadow-2xs flex items-center gap-1.5">
                <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> Enable All
            </button>
            <button type="button" id="integrity-btn" class="px-4 py-1.5 rounded-md bg-[#0A3E50] text-white hover:bg-[#082f3e] font-bold text-xs transition-colors shadow-xs flex items-center gap-1.5">
                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i> Audit Integrity
            </button>
        </div>
    </div>

    <div class="mb-6 p-4 bg-slate-900 border-l-4 border-orange-500 rounded-r-xl flex items-center justify-between shadow-xs">
        <div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-orange-400">ERP System Integrity State</h3>
            <p class="text-xs text-slate-300 mt-0.5 font-medium" id="global-status-text">
                All <strong id="active-count">{{ count(array_filter($modules, fn($m) => $m['is_active'])) }}</strong>
                of <strong>{{ count($modules) }}</strong> module suites are currently active.
            </p>
        </div>
        <span id="global-status-badge" class="inline-block px-3 py-1 rounded-md text-xs font-bold bg-emerald-500/25 text-emerald-400 border border-emerald-500/30">Stable Release</span>
    </div>

    <div id="integrity-panel" class="hidden mb-5 p-4 bg-white border border-slate-200 rounded-xl text-xs"></div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5" id="modules-grid">
        @foreach($modules as $mod)
            @php($isActive = $mod['is_active'])
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs flex flex-col transition-all hover:shadow-sm {{ $isActive ? '' : 'opacity-50 grayscale' }}"
                 id="card-{{ $mod['key'] }}">

                <div class="flex items-start justify-between gap-3 p-4 pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-[#0A3E50]/8 flex items-center justify-center text-[#0A3E50] shrink-0">
                            <i data-lucide="{{ $mod['icon'] }}" class="w-4.5 h-4.5"></i>
                        </div>
                        <div>
                            <h3 class="text-[12.5px] font-bold text-slate-900 leading-snug">{{ $mod['name'] }}</h3>
                            <code class="text-[10px] font-mono text-slate-400">{{ $mod['key'] }}</code>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer mt-1 shrink-0" title="Toggle module state">
                        <input type="checkbox"
                               id="toggle-{{ $mod['key'] }}"
                               data-key="{{ $mod['key'] }}"
                               data-name="{{ $mod['name'] }}"
                               class="sr-only peer module-toggle"
                               {{ $isActive ? 'checked' : '' }}>
                        <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer
                                    peer-checked:after:translate-x-full peer-checked:after:border-white
                                    after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                    after:bg-white after:border-slate-300 after:border after:rounded-full
                                    after:h-4 after:w-4 after:transition-all
                                    peer-checked:bg-orange-500"></div>
                    </label>
                </div>

                <div class="px-4 py-3 flex-1 flex flex-col gap-2">
                    <p class="text-[11px] text-slate-500 leading-snug">{{ $mod['description'] }}</p>
                    <div class="flex flex-wrap gap-1 mt-1">
                        @foreach($mod['submodules'] as $sub)
                            @if(!empty($sub['url']))
                                <a href="{{ $sub['url'] }}" class="inline-block text-[10px] font-semibold bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded border border-slate-200/80 hover:bg-[#0A3E50] hover:text-white hover:border-[#0A3E50]">{{ $sub['name'] }}</a>
                            @else
                                <span class="inline-block text-[10px] font-semibold bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded border border-slate-200/80">{{ $sub['name'] }}</span>
                            @endif
                        @endforeach
                    </div>
                    @if(!empty($mod['dependents']))
                        <p class="text-[10px] text-slate-400">Used by: {{ implode(', ', $mod['dependents']) }}</p>
                    @endif
                </div>

                <div class="px-4 py-2.5 border-t border-slate-100 bg-slate-50/50 rounded-b-xl flex items-center justify-between">
                    <div class="text-[10.5px] text-slate-400">
                        Depends on: <span class="font-semibold text-slate-700">{{ $mod['dependencies'] }}</span>
                    </div>
                    <span id="badge-{{ $mod['key'] }}"
                          class="inline-block px-2 py-0.5 rounded text-[10px] font-bold border
                                 {{ $isActive
                                    ? 'bg-emerald-100 text-emerald-800 border-emerald-200/80'
                                    : 'bg-red-100 text-red-800 border-red-200/80' }}">
                        {{ $isActive ? 'ACTIVE' : 'DISABLED' }}
                    </span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6 flex flex-wrap items-center gap-4 text-xs text-slate-400 font-medium">
        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span> Active — Module serving all users normally</span>
        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-400 inline-block"></span> Disabled — Non-admin routes return 503 and the sidebar hides the suite</span>
    </div>
</div>

<div id="toast"
     class="fixed bottom-5 right-5 z-50 hidden items-center gap-3 px-5 py-3 rounded-xl shadow-xl text-sm font-semibold border text-white transition-all duration-300"
     role="alert">
    <i data-lucide="check-circle" id="toast-icon" class="w-4 h-4 shrink-0"></i>
    <span id="toast-msg"></span>
</div>

<script>
    const totalModules = {{ count($modules) }};
    const toggleUrl    = "{{ route('admin.setups.module-manager.toggle') }}";
    const enableAllUrl = "{{ route('admin.setups.module-manager.enable-all') }}";
    const integrityUrl = "{{ route('admin.setups.module-manager.integrity') }}";
    const csrfToken    = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function jsonHeaders() {
        return {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        };
    }

    function getActiveCount() {
        return document.querySelectorAll('.module-toggle:checked').length;
    }

    function paintCard(key, active) {
        const card  = document.getElementById('card-' + key);
        const badge = document.getElementById('badge-' + key);
        if (!card || !badge) return;
        if (active) {
            card.classList.remove('opacity-50', 'grayscale');
            badge.textContent = 'ACTIVE';
            badge.className = 'inline-block px-2 py-0.5 rounded text-[10px] font-bold border bg-emerald-100 text-emerald-800 border-emerald-200/80';
        } else {
            card.classList.add('opacity-50', 'grayscale');
            badge.textContent = 'DISABLED';
            badge.className = 'inline-block px-2 py-0.5 rounded text-[10px] font-bold border bg-red-100 text-red-800 border-red-200/80';
        }
    }

    function updateGlobalBanner() {
        const active  = getActiveCount();
        const countEl = document.getElementById('active-count');
        const badgeEl = document.getElementById('global-status-badge');
        const textEl  = document.getElementById('global-status-text');

        if (countEl) countEl.textContent = active;

        if (active === totalModules) {
            badgeEl.className = 'inline-block px-3 py-1 rounded-md text-xs font-bold bg-emerald-500/25 text-emerald-400 border border-emerald-500/30';
            badgeEl.textContent = 'Stable Release';
            textEl.innerHTML = 'All <strong>' + active + '</strong> of <strong>' + totalModules + '</strong> module suites are currently active.';
        } else if (active === 0) {
            badgeEl.className = 'inline-block px-3 py-1 rounded-md text-xs font-bold bg-red-500/25 text-red-400 border border-red-500/30';
            badgeEl.textContent = 'System Offline';
            textEl.innerHTML = '<strong class="text-red-400">All modules are disabled.</strong> Users will encounter access denial gates across all portals.';
        } else {
            badgeEl.className = 'inline-block px-3 py-1 rounded-md text-xs font-bold bg-amber-500/25 text-amber-400 border border-amber-500/30';
            badgeEl.textContent = 'Partial Mode';
            textEl.innerHTML = '<strong>' + active + '</strong> of <strong>' + totalModules + '</strong> module suites are active. Some modules are currently disabled.';
        }
    }

    function showToast(message, success = true) {
        const toast   = document.getElementById('toast');
        const msgEl   = document.getElementById('toast-msg');
        const iconEl  = document.getElementById('toast-icon');

        msgEl.textContent = message;
        toast.classList.remove('hidden');
        toast.className = 'fixed bottom-5 right-5 z-50 flex items-center gap-3 px-5 py-3 rounded-xl shadow-xl text-sm font-semibold border text-white transition-all duration-300 '
            + (success ? 'bg-emerald-600 border-emerald-700' : 'bg-red-600 border-red-700');
        iconEl.setAttribute('data-lucide', success ? 'check-circle' : 'x-circle');

        if (window.lucide) window.lucide.createIcons();

        clearTimeout(toast._timer);
        toast._timer = setTimeout(() => { toast.classList.add('hidden'); }, 3500);
    }

    async function persistToggle(checkbox) {
        const key = checkbox.dataset.key;
        const active = checkbox.checked;
        paintCard(key, active);
        updateGlobalBanner();

        try {
            const response = await fetch(toggleUrl, {
                method: 'PATCH',
                credentials: 'same-origin',
                headers: jsonHeaders(),
                body: JSON.stringify({ module_key: key, is_active: active }),
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                const message = data.message
                    || (data.errors && data.errors.module_key && data.errors.module_key[0])
                    || 'Unable to save module state.';
                throw new Error(message);
            }
            checkbox.checked = !!data.is_active;
            paintCard(key, !!data.is_active);
            updateGlobalBanner();
            showToast(data.message, data.is_active);
        } catch (err) {
            checkbox.checked = !active;
            paintCard(key, !active);
            updateGlobalBanner();
            showToast(err.message, false);
        }
    }

    document.querySelectorAll('.module-toggle').forEach((checkbox) => {
        checkbox.addEventListener('change', () => persistToggle(checkbox));
    });

    document.getElementById('enable-all-btn').addEventListener('click', async () => {
        try {
            const response = await fetch(enableAllUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: jsonHeaders(),
                body: JSON.stringify({}),
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Enable all failed.');
            }
            document.querySelectorAll('.module-toggle').forEach((cb) => {
                cb.checked = true;
                paintCard(cb.dataset.key, true);
            });
            updateGlobalBanner();
            showToast(data.message, true);
        } catch (err) {
            showToast(err.message, false);
        }
    });

    document.getElementById('integrity-btn').addEventListener('click', async () => {
        const panel = document.getElementById('integrity-panel');
        try {
            const response = await fetch(integrityUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: jsonHeaders(),
                body: JSON.stringify({}),
            });
            const data = await response.json();
            panel.classList.remove('hidden');
            panel.innerHTML = '<strong class="block mb-2">' + (data.message || 'Integrity report') + '</strong>' +
                (data.checks || []).map((check) => {
                    const color = check.ok ? 'text-emerald-700' : 'text-red-700';
                    return '<div class="' + color + '"><span class="font-bold">' + check.name + ':</span> ' + check.detail + '</div>';
                }).join('');
            showToast(data.message, !!data.success);
        } catch (err) {
            showToast(err.message, false);
        }
    });

    updateGlobalBanner();
</script>
@endsection

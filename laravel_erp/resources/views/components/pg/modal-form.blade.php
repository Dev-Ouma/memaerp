@props([
    'id',
    'title',
    'subtitle' => null,
    'action',
    'method' => 'POST',
    'submitLabel' => 'Save',
    'width' => '560px',
    'multipart' => false,
])

{{-- A modal whose submit button posts a real request. No client-only "success" state. --}}
<div class="modal" id="{{ $id }}" role="dialog" aria-modal="true">
    <div class="modal-card" style="width:min({{ $width }}, 94vw);">
        <form method="POST" action="{{ $action }}" @if($multipart) enctype="multipart/form-data" @endif>
            @csrf
            @if($method !== 'POST')
                @method($method)
            @endif

            <div class="panel-head" style="background:var(--primary);color:#fff;padding:12px 18px;border-radius:7px 7px 0 0;">
                <div>
                    <h2 class="text-sm font-bold text-white">{{ $title }}</h2>
                    @if($subtitle)
                        <small style="color:rgba(255,255,255,0.85);">{{ $subtitle }}</small>
                    @endif
                </div>
                <button class="btn btn-secondary" type="button" data-modal-close style="background:transparent;border:none;color:#fff;">
                    <i data-lucide="x"></i>
                </button>
            </div>

            <div class="panel-body p-5 text-xs space-y-3.5">
                {{ $slot }}

                <div class="flex justify-between items-center pt-3 border-t border-slate-100">
                    <button type="button" class="btn btn-secondary text-xs" data-modal-close>Cancel</button>
                    <button type="submit" class="px-3.5 py-1.5 rounded bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-colors">
                        {{ $submitLabel }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

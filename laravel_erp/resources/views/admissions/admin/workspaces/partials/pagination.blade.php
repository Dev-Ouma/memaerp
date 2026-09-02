{{-- Page links, rendered only when the result set actually spans pages. --}}
@if($rows->hasPages())
    <div class="px-4 py-3 border-t border-slate-100 bg-slate-50/60">
        {{ $rows->withQueryString()->links() }}
    </div>
@endif

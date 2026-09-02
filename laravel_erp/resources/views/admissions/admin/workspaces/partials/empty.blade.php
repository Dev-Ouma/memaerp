{{-- Empty state for a workspace table. $colspan and $message are required. --}}
<tr>
    <td colspan="{{ $colspan }}" class="py-10 px-4 text-center">
        <div class="text-sm font-semibold text-slate-600">{{ $message }}</div>
        <p class="text-xs text-slate-400 mt-1">{{ $hint ?? 'Nothing matches the current filters.' }}</p>
    </td>
</tr>

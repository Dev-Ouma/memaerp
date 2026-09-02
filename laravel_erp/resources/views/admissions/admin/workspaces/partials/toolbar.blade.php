{{--
    Filter toolbar for a workspace table.

    $selects  array of ['name' => ..., 'label' => ..., 'options' => [value => label]]
    $search   placeholder for the free-text box, or null to omit it
    $rows     the paginator, so the count reflects the filtered result set
    $noun     what is being counted, e.g. "active priority queue items"
    $filters  the applied filters, used to keep the controls sticky
--}}
<form method="get" class="bg-white border border-slate-200 rounded-xl p-4 mb-5 shadow-xs flex flex-wrap gap-3 items-center justify-between">
    <div class="flex flex-wrap gap-2 items-center">
        @foreach($selects ?? [] as $select)
            <select name="{{ $select['name'] }}" onchange="this.form.submit()"
                    class="px-3 py-1.5 border border-slate-300 rounded-md text-xs font-medium text-slate-700 focus:outline-none focus:ring-1 focus:ring-[#0A3E50]">
                <option value="">{{ $select['label'] }}</option>
                @foreach($select['options'] as $value => $label)
                    {{-- Helpers return either a value=>label map or a plain list. --}}
                    @php($value = is_int($value) ? $label : $value)
                    <option value="{{ $value }}" @selected(($filters[$select['name']] ?? '') === (string) $value)>{{ $label }}</option>
                @endforeach
            </select>
        @endforeach

        @isset($search)
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ $search }}"
                   class="px-3 py-1.5 border border-slate-300 rounded-md text-xs w-64 focus:outline-none focus:ring-1 focus:ring-[#0A3E50]">
            <button type="submit" class="px-3 py-1.5 rounded-md bg-[#0A3E50] text-white font-bold text-xs hover:bg-[#0A3E50]/90 transition-colors">Filter</button>
        @endisset

        @if(array_filter($filters ?? []))
            <a href="{{ url()->current() }}" class="px-3 py-1.5 rounded-md border border-slate-300 text-slate-600 hover:bg-slate-50 font-semibold text-xs transition-colors">Clear</a>
        @endif
    </div>
    <div class="text-xs text-slate-500 font-medium">Showing <strong>{{ number_format($rows->total()) }}</strong> {{ $noun }}</div>
</form>

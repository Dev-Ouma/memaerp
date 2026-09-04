@isset($operationalCreate)
<section class="bg-white border border-slate-200 rounded-xl shadow-xs mb-5 overflow-hidden" data-operational-create>
    <details class="group">
        <summary class="cursor-pointer list-none flex items-center justify-between gap-3 px-4 py-3 bg-slate-50 border-b border-slate-200">
            <div>
                <div class="text-xs font-bold uppercase tracking-wide text-slate-700">{{ $operationalCreate['title'] ?? 'Add database record' }}</div>
                <p class="text-[11px] text-slate-500 mt-0.5">{{ $operationalCreate['hint'] ?? 'Persists to the domain table for this desk.' }}</p>
            </div>
            <span class="px-3 py-1 rounded-md border border-orange-500 text-orange-600 font-bold text-xs group-open:bg-orange-50">Add record</span>
        </summary>
        <form method="POST" action="{{ $operationalCreate['action'] }}" class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3" data-processing-message="Saving database record…">
            @csrf
            @foreach(($operationalCreate['fields'] ?? []) as $field)
                @php
                    $name = $field['name'];
                    $label = $field['label'] ?? ucfirst(str_replace('_', ' ', $name));
                    $type = $field['type'] ?? 'text';
                    $required = ! empty($field['required']);
                    $options = $field['options'] ?? null;
                @endphp
                <label class="block text-xs font-semibold text-slate-700">
                    <span class="mb-1 inline-block">{{ $label }}@if($required) <span class="text-red-600">*</span>@endif</span>
                    @if($type === 'textarea')
                        <textarea name="{{ $name }}" rows="2" @required($required) class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs bg-white"></textarea>
                    @elseif($type === 'number')
                        <input type="number" step="any" name="{{ $name }}" @required($required) class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs bg-white">
                    @elseif($type === 'date')
                        <input type="date" name="{{ $name }}" @required($required) class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs bg-white">
                    @elseif($type === 'select' && is_array($options))
                        <select name="{{ $name }}" @required($required) class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs bg-white">
                            <option value="">Select…</option>
                            @foreach($options as $value => $optionLabel)
                                <option value="{{ $value }}">{{ $optionLabel }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="text" name="{{ $name }}" @required($required) class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs bg-white">
                    @endif
                </label>
            @endforeach
            <div class="sm:col-span-2 lg:col-span-3 flex justify-end gap-2 pt-1">
                <button type="submit" class="px-4 py-2 rounded-md bg-[#0A3E50] text-white font-bold text-xs hover:bg-[#083241]">Save to database</button>
            </div>
        </form>
    </details>
</section>
@endisset

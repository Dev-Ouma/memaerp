@props(['label', 'name', 'required' => false, 'hint' => null])

<label class="block">
    <span class="block text-[11px] font-bold text-slate-700 mb-1">
        {{ $label }}@if($required)<span class="text-red-600"> *</span>@endif
    </span>
    {{ $slot }}
    @if($hint)
        <span class="block text-[10.5px] text-slate-500 mt-0.5">{{ $hint }}</span>
    @endif
    @error($name)
        <span class="block text-[10.5px] text-red-600 font-semibold mt-0.5">{{ $message }}</span>
    @enderror
</label>

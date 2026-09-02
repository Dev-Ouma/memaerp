@props([
    'action',
    'label',
    'fields' => [],
    'confirm' => null,
    'method' => 'POST',
    'variant' => 'neutral',
    'disabled' => false,
    'title' => null,
])

@php
    $palette = [
        'approve' => 'border-emerald-500 text-emerald-700 hover:bg-emerald-50',
        'reject' => 'border-red-400 text-red-700 hover:bg-red-50',
        'primary' => 'border-orange-400 text-orange-600 hover:bg-orange-50',
        'neutral' => 'border-slate-300 text-slate-700 hover:bg-slate-50',
    ];
    $classes = $palette[$variant] ?? $palette['neutral'];
@endphp

<form method="POST" action="{{ $action }}" class="inline-block">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif
    @foreach($fields as $name => $value)
        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
    @endforeach
    <button type="submit"
            @disabled($disabled)
            @if($title) title="{{ $title }}" @endif
            @if($confirm) onclick="return confirm(@js($confirm))" @endif
            class="px-3 py-1 rounded border font-semibold text-xs transition-colors disabled:opacity-40 disabled:cursor-not-allowed {{ $classes }}">
        {{ $label }}
    </button>
</form>

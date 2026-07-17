@props(['label', 'value'])

<div class="space-y-1">
    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">{{ $label }}</p>
    <p class="text-sm font-bold text-slate-700">{{ $value ?? '-' }}</p>
</div>

@props([
    'text' => 'PANITIA LT III 2026 • KWARCAB PADANGSIDIMPUAN',
    'theme' => 'boys',
    'bg' => null,
])

@php
    $bgClasses = [
        'boys' => 'bg-[#0f172a] border-t border-[#334155]',
        'girls' => 'bg-[#0f172a] border-t border-[#334155]',
        'chaperone-male' => 'bg-[#022c22] border-t border-[#064e3b]',
        'chaperone-female' => 'bg-[#19053b] border-t border-[#3b0764]',
        'staff' => 'bg-[#0f172a] border-t border-[#334155]',
        'judge' => 'bg-[#0f172a] border-t border-[#334155]',
    ];

    $resolvedBgClass = $bgClasses[$theme] ?? 'bg-[#0f172a] border-t border-[#334155]';
@endphp

<div {{ $attributes->merge(['class' => "h-[6mm] text-white text-[5.2pt] font-bold uppercase tracking-[0.4px] flex items-center justify-center absolute bottom-0 inset-x-0 z-[5] $resolvedBgClass"]) }}
    @if ($bg) style="background: {{ $bg }};" @endif>
    {{ $slot->isNotEmpty() ? $slot : $text }}
</div>

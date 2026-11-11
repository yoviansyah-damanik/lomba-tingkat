@props([
    'text' => 'PANITIA LT III 2026 • KWARCAB PADANGSIDIMPUAN',
    'theme' => 'boys',
    'bg' => null,
])

@php
    $bgClasses = [
        'boys' => 'bg-[#091326] border-t-[1.2px] border-[#f59e0b]',
        'girls' => 'bg-[#1f041b] border-t-[1.2px] border-[#f59e0b]',
        'chaperone-male' => 'bg-[#022c22] border-t-[1.2px] border-[#f59e0b]',
        'chaperone-female' => 'bg-[#19053b] border-t-[1.2px] border-[#f59e0b]',
        'staff' => 'bg-[#2b0303] border-t-[1.2px] border-[#f59e0b]',
        'judge' => 'bg-[#270f02] border-t-[1.2px] border-[#f59e0b]',
    ];

    $resolvedBgClass = $bgClasses[$theme] ?? $bgClasses['boys'];
@endphp

<div {{ $attributes->merge(['class' => "h-[5.4mm] w-full shrink-0 text-[#fbbf24] text-[4.9pt] font-extrabold uppercase tracking-[0.5px] flex items-center justify-center gap-1.5 relative z-[5] $resolvedBgClass"]) }}>
    <span class="text-[4.2pt]">★</span>
    <span class="text-white">{{ $slot->isNotEmpty() ? $slot : $text }}</span>
    <span class="text-[4.2pt]">★</span>
</div>

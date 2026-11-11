@props([
    'theme' => 'boys',
    'label' => null,
])

@php
    $themeClasses = [
        'boys' => 'bg-[#091326] text-[#fbbf24] border-y-[1.2px] border-[#f59e0b]/80',
        'girls' => 'bg-[#1f041b] text-[#fbbf24] border-y-[1.2px] border-[#f59e0b]/80',
        'chaperone-male' => 'bg-[#022c22] text-[#fcd34d] border-y-[1.2px] border-[#f59e0b]/80',
        'chaperone-female' => 'bg-[#19053b] text-[#fcd34d] border-y-[1.2px] border-[#f59e0b]/80',
        'staff' => 'bg-[#2b0303] text-[#fcd34d] border-y-[1.2px] border-[#f59e0b]/80',
        'judge' => 'bg-[#270f02] text-[#fcd34d] border-y-[1.2px] border-[#f59e0b]/80',
    ];

    $classes = $themeClasses[$theme] ?? $themeClasses['boys'];
@endphp

<div
    {{ $attributes->merge(['class' => "relative z-[5] shrink-0 px-[2.5mm] py-[1mm] text-[6.5pt] font-black text-center uppercase tracking-[0.8px] flex items-center justify-center gap-1.5 shadow-2xs $classes"]) }}>
    <span class="text-[#f59e0b] text-[5pt] leading-none">◆</span>
    <span>{{ $label ?? $slot }}</span>
    <span class="text-[#f59e0b] text-[5pt] leading-none">◆</span>
</div>

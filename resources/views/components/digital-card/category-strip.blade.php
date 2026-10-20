@props([
    'theme' => 'boys',
    'label' => null,
])

@php
    $themeClasses = [
        'boys' => 'bg-[#0f172a] text-[#38bdf8] border-b border-[#1e293b]',
        'girls' => 'bg-[#20040e] text-[#f472b6] border-b border-[#4c0519]',
        'chaperone-male' => 'bg-[#062f24] text-[#34d399] border-b border-[#064e3b]',
        'chaperone-female' => 'bg-[#19053b] text-[#d8b4fe] border-b border-[#3b0764]',
        'staff' => 'bg-[#2b0303] text-[#fca5a5] border-b border-[#450a0a]',
        'judge' => 'bg-[#270f02] text-[#fcd34d] border-b border-[#451a03]',
    ];

    $classes = $themeClasses[$theme] ?? $themeClasses['boys'];
@endphp

<div
    {{ $attributes->merge(['class' => "relative z-[5] px-[3mm] py-[1.5mm] text-[7.2pt] font-black text-center uppercase tracking-[0.8px] flex items-center justify-center gap-1.5 $classes"]) }}>
    {{ $label ?? $slot }}
</div>

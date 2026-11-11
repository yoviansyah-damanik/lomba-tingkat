@props([
    'title' => 'LOMBA TINGKAT TIGA (LT-III) 2026',
    'subtitle' => 'KWARCAB KOTA PADANGSIDIMPUAN',
    'kwarcab' => '14 — 17 November 2026 • Pulo Baok',
    'logo' => null,
    'theme' => 'boys',
    'bg' => null,
])

@php
    $gradients = [
        'boys' => 'bg-gradient-to-br from-[#091326] via-[#13284c] to-[#1e40af]',
        'girls' => 'bg-gradient-to-br from-[#1f041b] via-[#4a093b] to-[#9d174d]',
        'chaperone-male' => 'bg-gradient-to-br from-[#022c22] via-[#064e3b] to-[#059669]',
        'chaperone-female' => 'bg-gradient-to-br from-[#1e0b45] via-[#4c1d95] to-[#7e22ce]',
        'staff' => 'bg-gradient-to-br from-[#350606] via-[#7f1d1d] to-[#b91c1c]',
        'judge' => 'bg-gradient-to-br from-[#361302] via-[#78350f] to-[#b45309]',
    ];

    $gradientClass = $gradients[$theme] ?? $gradients['boys'];
@endphp

<div {{ $attributes->merge(['class' => "relative z-[5] shrink-0 overflow-hidden text-white shadow-xs $gradientClass"]) }}>
    {{-- Pita Aksen Emas Paling Atas (Senada dengan BIB) --}}
    <div class="h-[1mm] w-full bg-gradient-to-r from-[#b45309] via-[#fbbf24] to-[#b45309]"></div>

    {{-- Ornamen Geometris Gorga/Mandailing Halus di Sudut Kanan Header --}}
    <svg class="pointer-events-none absolute -right-[2mm] -top-[1mm] h-[16mm] w-[20mm] text-[#fbbf24]/15" viewBox="0 0 80 60" fill="none">
        <path d="M40 0L80 30L40 60L0 30L40 0Z" stroke="currentColor" stroke-width="2"/>
        <path d="M40 8L70 30L40 52L10 30L40 8Z" stroke="currentColor" stroke-width="1.5"/>
        <path d="M40 16L60 30L40 44L20 30L40 16Z" fill="currentColor" fill-opacity="0.3"/>
    </svg>

    <div class="px-[2.8mm] py-[1.4mm] flex items-center gap-[2.4mm] relative">
        <div class="relative shrink-0 flex items-center justify-center rounded-full p-[0.4mm] bg-gradient-to-b from-[#fbbf24]/40 to-transparent">
            <img src="{{ $logo ?? asset('logo.png') }}" alt="Logo LT III"
                class="w-[11.5mm] h-[11.5mm] object-contain drop-shadow-md shrink-0">
        </div>
        <div class="flex-1 flex flex-col justify-center drop-shadow-xs text-left gap-[0.3mm] min-w-0">
            <h2 class="text-[5.2pt] font-extrabold tracking-[0.5px] text-[#fbbf24] uppercase leading-tight truncate">
                {{ $subtitle }}
            </h2>
            <h1 class="text-[7.2pt] font-black tracking-[0.4px] uppercase leading-tight text-white">
                {{ $title }}
            </h1>
            @if ($kwarcab)
                <div class="text-[5pt] font-semibold text-amber-100/90 leading-tight truncate">{{ $kwarcab }}</div>
            @endif
        </div>
    </div>
</div>

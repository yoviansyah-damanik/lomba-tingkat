@props([
    'title' => 'LOMBA TINGKAT III (LT III)',
    'subtitle' => 'KWARCAB KOTA PADANGSIDIMPUAN',
    'kwarcab' => 'KWARDA SUMATERA UTARA',
    'logo' => null,
    'theme' => 'boys',
    'bg' => null,
])

@php
    $gradients = [
        'boys' => 'bg-gradient-to-br from-[#091e42] via-[#1e3a8a] to-[#2563eb]',
        'girls' => 'bg-gradient-to-br from-[#500724] via-[#831843] to-[#be185d]',
        'chaperone-male' => 'bg-gradient-to-br from-[#022c22] via-[#065f46] to-[#059669]',
        'chaperone-female' => 'bg-gradient-to-br from-[#2e1065] via-[#581c87] to-[#7e22ce]',
        'staff' => 'bg-gradient-to-br from-[#450a0a] via-[#7f1d1d] to-[#b91c1c]',
        'judge' => 'bg-gradient-to-br from-[#451a03] via-[#78350f] to-[#b45309]',
    ];

    $gradientClass = $gradients[$theme] ?? $gradients['boys'];
@endphp

<div {{ $attributes->merge(['class' => "px-[3mm] py-[2mm] flex items-center gap-[3mm] relative z-[5] shadow-sm text-white $gradientClass"]) }}
    @if ($bg) style="background: {{ $bg }};" @endif>
    <img src="{{ $logo ?? asset('logo.png') }}" alt="Logo LT III"
        class="w-[13.5mm] h-[13.5mm] object-contain drop-shadow-md shrink-0">
    <div class="flex-1 flex flex-col justify-center drop-shadow-sm text-left gap-[0.5mm]">
        <h1 class="text-[8pt] font-black tracking-[0.6px] uppercase leading-tight text-white">{{ $title }}</h1>
        <h2 class="text-[6pt] font-extrabold tracking-[0.4px] text-amber-400 leading-tight">
            {{ $subtitle }}
        </h2>
        @if ($kwarcab)
            <div class="text-[5.7pt] text-white/90 leading-tight">{{ $kwarcab }}</div>
        @endif
    </div>
</div>

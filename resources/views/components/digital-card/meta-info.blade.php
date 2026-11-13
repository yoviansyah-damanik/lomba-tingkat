@props([
    'name',
    'idNumber',
    'scoutId' => null,
    'theme' => 'boys',
    'idColor' => null,
])

@php
    $idBadgeClasses = [
        'boys' => 'bg-[#091326] text-[#fbbf24] border-[#f59e0b]/60',
        'girls' => 'bg-[#1f041b] text-[#fbbf24] border-[#f59e0b]/60',
        'chaperone-male' => 'bg-[#022c22] text-[#fcd34d] border-[#f59e0b]/60',
        'chaperone-female' => 'bg-[#19053b] text-[#fcd34d] border-[#f59e0b]/60',
        'staff' => 'bg-[#2b0303] text-[#fcd34d] border-[#f59e0b]/60',
        'judge' => 'bg-[#270f02] text-[#fcd34d] border-[#f59e0b]/60',
    ];

    $resolvedIdBadge = $idBadgeClasses[$theme] ?? $idBadgeClasses['boys'];
@endphp

<div class="w-full text-center shrink-0">
    {{-- Nama Lengkap (Tepat di bawah Pas Foto) --}}
    <div class="text-[11pt] font-black text-slate-900 leading-tight uppercase max-w-full overflow-hidden text-ellipsis whitespace-nowrap px-1" title="{{ $name }}">
        {{ $name }}
    </div>

    {{-- Garis Aksen Ornamen Emas di Bawah Nama --}}
    <div class="mx-auto mt-[0.7mm] mb-[0.8mm] flex items-center justify-center gap-1.5">
        <div class="h-[0.8px] w-[18mm] bg-gradient-to-r from-transparent to-[#d97706]/70"></div>
        <span class="text-[5pt] text-[#d97706] leading-none">◆</span>
        <div class="h-[0.8px] w-[18mm] bg-gradient-to-l from-transparent to-[#d97706]/70"></div>
    </div>

    {{-- NTA dan Pill ID Resmi --}}
    <div class="flex items-center justify-center gap-[1.8mm] font-mono text-[7.4pt] leading-none">
        <span class="text-slate-700 font-bold tracking-[0.25px]">NTA: {{ $scoutId ?: '-' }}</span>
        <span class="text-[#d97706] text-[7pt]">•</span>
        <span class="px-[1.8mm] py-[0.5mm] rounded-[1.2mm] border font-extrabold tracking-[0.45px] {{ $resolvedIdBadge }}">
            {{ $idNumber }}
        </span>
    </div>
</div>

@props([
    'name',
    'idNumber',
    'scoutId' => null,
    'theme' => 'boys',
    'idColor' => null,
])

@php
    $idTextClasses = [
        'boys' => 'text-[#b45309]',
        'girls' => 'text-[#b45309]',
        'chaperone-male' => 'text-[#047857]',
        'chaperone-female' => 'text-[#7e22ce]',
        'staff' => 'text-[#b91c1c]',
        'judge' => 'text-[#b45309]',
    ];

    $resolvedIdClass = $idTextClasses[$theme] ?? 'text-[#b45309]';
@endphp

<div class="w-full text-center shrink-0 my-0.5">
    {{-- Nama (tepat di bawah Pas Foto) --}}
    <div class="text-[9.5pt] font-black text-slate-900 leading-tight uppercase max-w-full overflow-hidden text-ellipsis whitespace-nowrap px-1" title="{{ $name }}">
        {{ $name }}
    </div>

    {{-- NTA dan ID (tepat di bawah Nama) --}}
    <div class="flex items-center justify-center gap-[1.8mm] mt-[1mm] mb-[1.5mm] font-mono text-[6.8pt] leading-none">
        <span class="text-slate-600 font-bold tracking-[0.3px]">NTA: {{ $scoutId ?: '-' }}</span>
        <span class="text-slate-400 text-[7.5pt]">•</span>
        <span class="font-extrabold tracking-[0.5px] {{ $resolvedIdClass }}" @if($idColor) style="color: {{ $idColor }};" @endif>{{ $idNumber }}</span>
    </div>
</div>

@props([
    'qrDataUri',
    'token',
    'title' => 'KREDENSIAL VERIFIKASI QR',
    'description' => null,
    'theme' => 'boys',
    'titleColor' => null,
    'tokenColor' => null,
    'appUrl' => null,
])

@php
    $themeBoxes = [
        'boys' => 'bg-gradient-to-r from-[#091326] via-[#102142] to-[#091326] border-[#f59e0b]/70',
        'girls' => 'bg-gradient-to-r from-[#1f041b] via-[#380831] to-[#1f041b] border-[#f59e0b]/70',
        'chaperone-male' => 'bg-gradient-to-r from-[#022c22] via-[#064e3b] to-[#022c22] border-[#f59e0b]/70',
        'chaperone-female' => 'bg-gradient-to-r from-[#19053b] via-[#3b0764] to-[#19053b] border-[#f59e0b]/70',
        'staff' => 'bg-gradient-to-r from-[#2b0303] via-[#450a0a] to-[#2b0303] border-[#f59e0b]/70',
        'judge' => 'bg-gradient-to-r from-[#270f02] via-[#451a03] to-[#270f02] border-[#f59e0b]/70',
    ];

    $resolvedBox = $themeBoxes[$theme] ?? $themeBoxes['boys'];
    $defaultDescription = 'Kartu tanda pengenal resmi ' . config('app.name') . ' Kwarcab Kota Padangsidimpuan.';
    $resolvedAppUrl = $appUrl ?? config('app.url');
@endphp

<div
    class="w-full flex items-center rounded-[2.2mm] border-[1.2px] p-[1.4mm] relative z-[5] shadow-xs shrink-0 text-left {{ $resolvedBox }}">
    <img src="{{ $qrDataUri }}" alt="QR Code"
        class="w-[18.5mm] h-[18.5mm] block rounded-[1.5mm] bg-white p-[0.5mm] border border-[#fbbf24] shrink-0">
    <div class="flex-1 pl-[2.2mm] text-[5.1pt] text-slate-200 leading-tight min-w-0">
        <span class="text-[5.7pt] font-black block mb-[0.3mm] tracking-[0.4px] text-[#fbbf24] uppercase">
            ◆ {{ $title }}
        </span>
        <div class="text-[5pt] text-slate-200/90 leading-snug">{{ $description ?? $defaultDescription }}</div>
        <span
            class="font-mono font-bold block mt-[0.4mm] text-[4.9pt] tracking-[0.2px] break-all text-amber-300">
            {{ $token }}
        </span>
        @if ($resolvedAppUrl)
            <span
                class="block text-[4.4pt] text-slate-400 font-mono tracking-tight mt-[0.2mm] truncate">{{ $resolvedAppUrl }}</span>
        @endif
    </div>
</div>

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
    class="w-full flex items-center rounded-[2.6mm] border-[1.2px] p-[1.8mm] relative z-[5] shadow-xs shrink-0 text-left {{ $resolvedBox }}">
    <img src="{{ $qrDataUri }}" alt="QR Code"
        class="w-[22mm] h-[22mm] block rounded-[1.8mm] bg-white p-[0.6mm] border border-[#fbbf24] shrink-0">
    <div class="flex-1 pl-[2.8mm] text-[6.2pt] text-slate-200 leading-tight min-w-0">
        <span class="text-[7pt] font-black block mb-[0.4mm] tracking-[0.45px] text-[#fbbf24] uppercase">
            ◆ {{ $title }}
        </span>
        <div class="text-[6pt] text-slate-200/90 leading-snug">{{ $description ?? $defaultDescription }}</div>
        <span
            class="font-mono font-bold block mt-[0.5mm] text-[5.8pt] tracking-[0.25px] break-all text-amber-300">
            {{ $token }}
        </span>
        @if ($resolvedAppUrl)
            <span
                class="block text-[5.2pt] text-slate-400 font-mono tracking-tight mt-[0.3mm] truncate">{{ $resolvedAppUrl }}</span>
        @endif
    </div>
</div>

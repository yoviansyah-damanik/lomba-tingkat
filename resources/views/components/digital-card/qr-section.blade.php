@props([
    'qrDataUri',
    'token',
    'title' => 'PINDAI VERIFIKASI QR',
    'description' => null,
    'theme' => 'boys',
    'titleColor' => null,
    'tokenColor' => null,
    'appUrl' => null,
])

@php
    $themeClasses = [
        'boys' => ['title' => 'text-[#0f172a]', 'token' => 'text-[#b45309]'],
        'girls' => ['title' => 'text-[#0f172a]', 'token' => 'text-[#b45309]'],
        'chaperone-male' => ['title' => 'text-[#065f46]', 'token' => 'text-[#047857]'],
        'chaperone-female' => ['title' => 'text-[#581c87]', 'token' => 'text-[#7e22ce]'],
        'staff' => ['title' => 'text-[#7f1d1d]', 'token' => 'text-[#b91c1c]'],
        'judge' => ['title' => 'text-[#78350f]', 'token' => 'text-[#b45309]'],
    ];

    $resolvedTitleClass = $titleColor ? '' : $themeClasses[$theme]['title'] ?? 'text-[#0f172a]';
    $resolvedTokenClass = $tokenColor ? '' : $themeClasses[$theme]['token'] ?? 'text-[#b45309]';
    $defaultDescription = 'Kartu digital resmi ' . config('app.name') . ' ' . config('app.description') . '.';
    $resolvedAppUrl = $appUrl ?? config('app.url');
@endphp

<div
    class="w-full mt-[2.5mm] flex items-center bg-slate-50/95 border border-slate-200 rounded-[2.8mm] p-[1.8mm] relative z-[5] shadow-xs shrink-0 text-left">
    <img src="{{ $qrDataUri }}" alt="QR Code"
        class="w-[22mm] h-[22mm] block rounded-[2mm] bg-white p-[0.6mm] border-[1.5px] border-slate-300 shrink-0">
    <div class="flex-1 pl-[2.5mm] text-[5.6pt] text-slate-700 leading-tight">
        <span class="text-[6.2pt] font-extrabold block mb-px tracking-[0.2px] {{ $resolvedTitleClass }}"
            @if ($titleColor) style="color: {{ $titleColor }};" @endif>{{ $title }}</span>
        <div class="text-[5.6pt] text-slate-600 leading-tight">{{ $description ?? $defaultDescription }}</div>
        <span
            class="font-mono font-bold block mt-[1.5px] text-[5.5pt] tracking-[0.3px] break-all {{ $resolvedTokenClass }}"
            @if ($tokenColor) style="color: {{ $tokenColor }};" @endif>{{ $token }}</span>
        @if ($resolvedAppUrl)
            <span
                class="block text-[4.8pt] text-slate-400 font-mono tracking-tight mt-[1px] truncate">{{ $resolvedAppUrl }}</span>
        @endif
    </div>
</div>

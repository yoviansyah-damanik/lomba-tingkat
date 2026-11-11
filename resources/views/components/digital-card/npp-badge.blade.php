@props([
    'npp' => null,
    'digits' => [],
    'bg' => 'bg-[#1e3a8a]',
    'theme' => 'boys',
])

@php
    $rawNpp = $npp !== null ? trim((string) $npp) : implode('', $digits);
    $prefixTop = 'NPP';
    $prefixSub = null;
    $seqDigits = $digits;

    if (preg_match('/^(LT)\s*-\s*(PA|PI)\s*-\s*(\d+)$/i', $rawNpp, $matches)) {
        $prefixTop = strtoupper($matches[1]);
        $prefixSub = strtoupper($matches[2]);
        $seqDigits = mb_str_split($matches[3]);
    } elseif ($rawNpp !== '' && empty($seqDigits)) {
        $seqDigits = mb_str_split($rawNpp);
    }

    $gradientBg = $theme === 'girls'
        ? 'bg-gradient-to-b from-[#1f041b] via-[#500724] to-[#831843]'
        : 'bg-gradient-to-b from-[#091326] via-[#13284c] to-[#1e3a8a]';
@endphp

{{-- Lencana NPP di sisi kiri foto, sepasang dengan lencana PINRU/WAPINRU di sisi kanan:
     mendukung format LT-PA-01 / LT-PI-01 maupun 2 digit dengan ornamen emas BIB --}}
<div class="npp-badge w-[6.5mm] h-full shrink-0 flex flex-col items-center justify-between py-[1mm] px-[0.3mm] {{ $gradientBg }} border-r border-[#f59e0b]/60 text-white font-black leading-none select-none" title="Nomor Pendaftaran Peserta (NPP)">
    {{-- Kepala Lencana: Kode Kategori LT-PA / LT-PI --}}
    <div class="flex flex-col items-center justify-center text-[#fbbf24] font-black tracking-wider leading-[1.08]">
        <span class="text-[4.5pt]">{{ $prefixTop }}</span>
        @if($prefixSub)
            <span class="text-[4.8pt] text-white bg-[#f59e0b]/25 px-[0.7mm] py-[0.15mm] rounded-[0.5mm] border border-[#f59e0b]/50 mt-[0.2mm]">{{ $prefixSub }}</span>
        @endif
    </div>

    <div class="w-[4.2mm] h-[0.6px] bg-[#f59e0b]/60 my-[0.3mm] shrink-0"></div>

    {{-- Nomor Urut Regu Berukuran Besar --}}
    <div class="flex flex-col items-center justify-center flex-1 gap-[0.6mm] text-[12pt] tracking-normal text-white drop-shadow-xs">
        @foreach($seqDigits as $digit)
            <span>{{ $digit }}</span>
        @endforeach
    </div>

    <div class="w-[4.2mm] h-[0.6px] bg-[#f59e0b]/60 my-[0.3mm] shrink-0"></div>

    {{-- Kaki Lencana --}}
    <div class="text-[3.8pt] font-extrabold text-[#fbbf24] tracking-widest uppercase">
        {{ $prefixSub ? 'NPP' : '★' }}
    </div>
</div>

@props([
    'name' => '',
    'theme' => 'boys',
])

@php
    $cleanName = strtoupper(trim($name));
    // Hapus awalan "REGU " jika ada agar hanya menampilkan nama inti regu (misal: "MELATI")
    $cleanName = preg_replace('/^REGU\s+/i', '', $cleanName);
    $chars = mb_str_split($cleanName);
    $charCount = count($chars);

    // Sesuaikan ukuran font berdasarkan panjang nama regu
    $fontSizeClass = match (true) {
        $charCount >= 12 => 'text-[4.2pt] leading-[1.0]',
        $charCount >= 10 => 'text-[4.8pt] leading-[1.05]',
        $charCount >= 8  => 'text-[5.4pt] leading-[1.12]',
        $charCount >= 6  => 'text-[6.2pt] leading-[1.18]',
        default          => 'text-[6.8pt] leading-[1.22]',
    };

    $themeGradients = [
        'boys' => 'bg-gradient-to-b from-[#091326] via-[#13284c] to-[#1e40af] border-[#f59e0b]/80 shadow-xs',
        'girls' => 'bg-gradient-to-b from-[#1f041b] via-[#500724] to-[#9d174d] border-[#f59e0b]/80 shadow-xs',
    ];

    $resolvedGradient = $themeGradients[$theme] ?? $themeGradients['boys'];
@endphp

@if($cleanName)
    <div
        class="absolute right-[3.5mm] top-[2.8mm] z-[6] w-[8mm] h-[44mm] rounded-[3.2mm] border-[1.2px] {{ $resolvedGradient }} flex flex-col items-center justify-between py-[1.6mm] px-[0.5mm] text-white select-none pointer-events-none"
        title="Regu {{ $name }}">

        {{-- Nama Asli untuk Aksesibilitas & Screen Reader --}}
        <span class="sr-only">{{ $name }}</span>

        {{-- Ikon Tenda Berkemah Pramuka Berwarna Emas --}}
        <div class="shrink-0 flex items-center justify-center">
            <svg class="w-[4.6mm] h-[4.6mm] text-[#fbbf24] drop-shadow-xs" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 20 12 5 5 20h14Z" fill="currentColor" fill-opacity="0.2" />
                <path d="m12 5 4 15" />
                <path d="m12 5-4 15" />
                <path d="M2 20h20" stroke-width="2" />
                <path d="m5 20-3-4" />
                <path d="m19 20 3-4" />
            </svg>
        </div>

        {{-- Garis Pembatas Emas --}}
        <div class="w-[4mm] h-[0.6px] bg-[#f59e0b]/70 my-[0.5mm] shrink-0"></div>

        {{-- Susunan Huruf Vertikal Nama Regu --}}
        <div class="flex-1 w-full flex flex-col items-center justify-around font-black tracking-normal uppercase text-white {{ $fontSizeClass }}">
            @foreach($chars as $char)
                @if($char === ' ')
                    <span class="h-[1mm]"></span>
                @else
                    <span class="block text-center drop-shadow-xs">{{ $char }}</span>
                @endif
            @endforeach
        </div>
    </div>
@endif

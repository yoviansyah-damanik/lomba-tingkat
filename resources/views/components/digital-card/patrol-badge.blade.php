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
        $charCount >= 10 => 'text-[4pt] leading-[1.1]',
        $charCount >= 8  => 'text-[4.5pt] leading-[1.15]',
        $charCount >= 6  => 'text-[5.2pt] leading-[1.2]',
        default          => 'text-[5.8pt] leading-[1.25]',
    };

    $themeGradients = [
        'boys' => 'bg-gradient-to-b from-[#091e42] via-[#1e3a8a] to-[#2563eb] border-[#38bdf8]/60 shadow-[0_0_4px_rgba(56,189,248,0.25)]',
        'girls' => 'bg-gradient-to-b from-[#500724] via-[#831843] to-[#be185d] border-[#f472b6]/60 shadow-[0_0_4px_rgba(244,114,182,0.25)]',
    ];

    $resolvedGradient = $themeGradients[$theme] ?? $themeGradients['boys'];
@endphp

@if($cleanName)
    <div
        class="absolute right-[2.2mm] top-[2.2mm] z-[6] w-[7mm] h-[42mm] rounded-[3.2mm] border-[1.2px] {{ $resolvedGradient }} flex flex-col items-center justify-between py-[1.8mm] px-[0.5mm] text-white select-none pointer-events-none"
        title="Regu {{ $name }}">
        
        {{-- Nama Asli untuk Aksesibilitas & Screen Reader --}}
        <span class="sr-only">{{ $name }}</span>

        {{-- Ikon Tenda Berkemah Pramuka --}}
        <div class="shrink-0 flex items-center justify-center">
            <svg class="w-[4.4mm] h-[4.4mm] text-white drop-shadow-xs" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <!-- Tenda Pramuka / Camp Tent Icon -->
                <path d="M19 20 12 5 5 20h14Z" fill="currentColor" fill-opacity="0.15" />
                <path d="m12 5 4 15" />
                <path d="m12 5-4 15" />
                <path d="M2 20h20" stroke-width="2" />
                <path d="m5 20-3-4" />
                <path d="m19 20 3-4" />
            </svg>
        </div>

        {{-- Garis Pembatas Halus --}}
        <div class="w-[4mm] h-[0.6px] bg-white/50 my-[0.8mm] shrink-0"></div>

        {{-- Susunan Huruf Vertikal Nama Regu --}}
        <div class="flex-1 w-full flex flex-col items-center justify-around font-black tracking-normal uppercase text-white {{ $fontSizeClass }}">
            @foreach($chars as $char)
                @if($char === ' ')
                    <span class="h-[1.5mm]"></span>
                @else
                    <span class="block text-center drop-shadow-xs">{{ $char }}</span>
                @endif
            @endforeach
        </div>
    </div>
@endif

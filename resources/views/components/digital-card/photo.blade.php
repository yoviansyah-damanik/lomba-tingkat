@props([
    'photoUrl' => null,
    'name' => '',
    'role' => 'member', // 'member', 'leader', 'assistant_leader'
    'placeholderText' => 'PAS FOTO<br>3 x 4',
    'leaderColor' => 'red',
    'assistantColor' => 'blue', // blue, amber, emerald, indigo
    'npp' => null, // Nomor Pendaftaran Peserta: lencana di sisi kiri foto (cermin lencana PINRU/WAPINRU)
    'theme' => 'boys',
])

@php
    $assistantClasses = match ($assistantColor) {
        'amber' => ['border' => 'border-amber-500', 'bg' => 'bg-amber-500'],
        'emerald' => ['border' => 'border-emerald-600', 'bg' => 'bg-emerald-600'],
        'indigo' => ['border' => 'border-indigo-600', 'bg' => 'bg-indigo-600'],
        default => ['border' => 'border-blue-600', 'bg' => 'bg-blue-600'],
    };

    $nppBg = $theme === 'girls' ? 'bg-[#831843]' : 'bg-[#1e3a8a]';
    $nppDigits = $npp ? mb_str_split((string) $npp) : [];

    // Lebar total proporsional: foto 23mm + lencana kanan 6mm (PINRU/WAPINRU) + lencana NPP kiri 6.5mm
    $leaderWidth = $npp ? 'w-[35.5mm]' : 'w-[29mm]';
    $memberWidth = $npp ? 'w-[29.5mm]' : 'w-[23mm]';
@endphp

<div class="flex items-center justify-center shrink-0 mb-[0.6mm]">
    @if($role === 'leader')
        {{-- PINRU: Pita & Border Merah Tegas dengan Aksen Emas --}}
        <div class="photo-pinru badge-pinru {{ $leaderWidth }} h-[30.5mm] rounded-[3mm] overflow-hidden flex relative shrink-0 shadow-xs border-[1.5px] border-red-600 ring-1 ring-[#f59e0b]/50 bg-red-600" title="Pimpinan Regu (PINRU)">
            @if($npp)
                <x-digital-card.npp-badge :npp="$npp" :digits="$nppDigits" :bg="$nppBg" :theme="$theme" />
            @endif
            <div class="w-[23mm] h-full overflow-hidden bg-slate-50 flex items-center justify-center">
                @if($photoUrl)
                    <img src="{{ $photoUrl }}" alt="{{ $name }}" class="w-full h-full object-cover block">
                @else
                    <div class="text-[6.8pt] text-slate-500 text-center font-bold leading-tight">{!! $placeholderText !!}</div>
                @endif
            </div>
            <div class="w-[6mm] h-full flex flex-col items-center justify-between py-[1mm] bg-gradient-to-b from-red-700 via-red-600 to-red-700 border-l border-[#fbbf24]/50 text-white font-black text-[4.8pt] leading-none">
                <img src="{{ asset('logo.png') }}" alt="Logo LT III" class="w-[4.4mm] h-[4.4mm] object-contain drop-shadow-xs">
                <div class="w-[3.2mm] h-px bg-[#fbbf24]/60 my-0.5"></div>
                <div class="flex flex-col items-center justify-around flex-1 text-white font-black text-[4.8pt] tracking-normal leading-none">
                    <span>P</span>
                    <span>I</span>
                    <span>N</span>
                    <span>R</span>
                    <span>U</span>
                </div>
            </div>
        </div>
    @elseif($role === 'assistant_leader')
        {{-- WAPINRU: Pita & Border Biru Membedakan dari PINRU --}}
        <div class="photo-wapinru badge-wapinru {{ $leaderWidth }} h-[30.5mm] rounded-[3mm] overflow-hidden flex relative shrink-0 shadow-xs border-[1.5px] {{ $assistantClasses['border'] }} ring-1 ring-[#f59e0b]/50 {{ $assistantClasses['bg'] }}" title="Wakil Pimpinan Regu (WAPINRU)">
            @if($npp)
                <x-digital-card.npp-badge :npp="$npp" :digits="$nppDigits" :bg="$nppBg" :theme="$theme" />
            @endif
            <div class="w-[23mm] h-full overflow-hidden bg-slate-50 flex items-center justify-center">
                @if($photoUrl)
                    <img src="{{ $photoUrl }}" alt="{{ $name }}" class="w-full h-full object-cover block">
                @else
                    <div class="text-[6.8pt] text-slate-500 text-center font-bold leading-tight">{!! $placeholderText !!}</div>
                @endif
            </div>
            <div class="w-[6mm] h-full flex flex-col items-center justify-between py-[1mm] {{ $assistantClasses['bg'] }} border-l border-[#fbbf24]/50 text-white font-black text-[4.8pt] leading-none">
                <img src="{{ asset('logo.png') }}" alt="Logo LT III" class="w-[4.4mm] h-[4.4mm] object-contain drop-shadow-xs">
                <div class="w-[3.2mm] h-px bg-[#fbbf24]/60 my-0.5"></div>
                <div class="flex flex-col items-center justify-around flex-1 text-white font-black text-[4.8pt] tracking-normal leading-none">
                    <span>W</span>
                    <span>A</span>
                    <span>P</span>
                    <span>I</span>
                    <span>N</span>
                    <span>R</span>
                    <span>U</span>
                </div>
            </div>
        </div>
    @else
        {{-- ANGGOTA / BINDAMPING / APARATUR: Border Emas & Tema BIB --}}
        <div class="photo-member {{ $memberWidth }} h-[30.5mm] rounded-[3mm] overflow-hidden bg-slate-50 flex relative shrink-0 border-[1.5px] border-[#d97706]/80 shadow-xs">
            @if($npp)
                <x-digital-card.npp-badge :npp="$npp" :digits="$nppDigits" :bg="$nppBg" :theme="$theme" />
            @endif
            <div class="w-[23mm] h-full overflow-hidden flex items-center justify-center">
                @if($photoUrl)
                    <img src="{{ $photoUrl }}" alt="{{ $name }}" class="w-full h-full object-cover block">
                @else
                    <div class="text-[6.8pt] text-slate-500 text-center font-bold leading-tight">{!! $placeholderText !!}</div>
                @endif
            </div>
        </div>
    @endif
</div>

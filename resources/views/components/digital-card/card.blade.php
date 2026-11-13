<div data-digital-card {{ $attributes->merge(['class' => 'w-[105mm] h-[148mm] bg-gradient-to-b from-[#fffdf9] via-white to-[#fef9ec] rounded-[5.5mm] overflow-hidden shadow-2xl relative flex flex-col border-[1.5px] border-[#d97706]/70 break-inside-avoid print:shadow-none print:m-0 print:border-[#d97706] select-none']) }}>
    {{-- Bingkai Ornamen Emas Dalam (Berhenti tepat di atas Footer) --}}
    <div class="pointer-events-none absolute top-[1.6mm] inset-x-[1.6mm] bottom-[7.6mm] rounded-t-[4.2mm] rounded-b-[2.4mm] border border-[#f59e0b]/35 z-[1]"></div>

    {{-- Ornamen Sudut Geometris Emas Kiri & Kanan Atas Badan Kartu --}}
    <svg class="pointer-events-none absolute left-[2.5mm] top-[25mm] w-[8.5mm] h-[8.5mm] text-[#d97706]/25 z-[1]" viewBox="0 0 32 32" fill="none">
        <path d="M1 31V1H31" stroke="currentColor" stroke-width="2"/>
        <path d="M5 25V5H25" stroke="currentColor" stroke-width="1.2"/>
        <rect x="3" y="3" width="4" height="4" fill="currentColor"/>
    </svg>
    <svg class="pointer-events-none absolute right-[2.5mm] top-[25mm] w-[8.5mm] h-[8.5mm] text-[#d97706]/25 z-[1]" viewBox="0 0 32 32" fill="none">
        <path d="M31 31V1H1" stroke="currentColor" stroke-width="2"/>
        <path d="M27 25V5H7" stroke="currentColor" stroke-width="1.2"/>
        <rect x="25" y="3" width="4" height="4" fill="currentColor"/>
    </svg>

    {{-- Watermark Logo Background --}}
    <img src="{{ asset('logo.png') }}" alt="Watermark" class="absolute inset-0 m-auto w-[72mm] opacity-[0.045] pointer-events-none select-none z-0">

    {{ $slot }}
</div>

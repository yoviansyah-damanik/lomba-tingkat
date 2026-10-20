<div data-digital-card {{ $attributes->merge(['class' => 'w-[79mm] h-[126mm] bg-white rounded-[5mm] overflow-hidden shadow-2xl relative flex flex-col border border-slate-300 break-inside-avoid print:shadow-none print:m-0 print:border-slate-400 select-none']) }}>
    {{-- Watermark Logo Background --}}
    <img src="{{ asset('logo.png') }}" alt="Watermark" class="absolute inset-0 m-auto w-[65mm] opacity-[0.045] pointer-events-none select-none z-0">
    
    {{ $slot }}
</div>

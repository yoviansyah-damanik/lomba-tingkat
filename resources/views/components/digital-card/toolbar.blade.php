@props([
    'title' => 'ID Card B2 (79 x 126 mm)',
    'subtitle' => null,
    'backUrl' => 'javascript:window.history.back()',
    'backLabel' => 'Kembali',
    'batchUrl' => null,
    'batchLabel' => 'Lihat & Simpan Seluruh Kartu (ZIP)',
    'downloadFilename' => 'KARTU_DIGITAL',
])

<div
    class="w-full max-w-3xl mb-5 flex flex-wrap justify-between items-center gap-3 bg-slate-800 p-[12px_18px] rounded-[14px] shadow-md border border-slate-700 text-slate-50">
    <div>
        <strong class="text-[13px] font-bold text-slate-100 block">{{ $title }}</strong>
        @if ($subtitle)
            <div class="text-[11px] text-slate-400 mt-0.5">{{ $subtitle }}</div>
        @endif
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        @if ($batchUrl)
            <flux:button :href="$batchUrl" icon="squares-2x2" size="sm">{{ $batchLabel }}</flux:button>
        @endif
        <flux:button :href="$backUrl" icon="arrow-left" size="sm">{{ $backLabel }}</flux:button>
        <flux:button
            id="btn-save-png"
            variant="primary"
            icon="arrow-down-tray"
            size="sm"
            onclick="window.saveCardAsPng('{{ $downloadFilename }}')"
        >Simpan .PNG</flux:button>
    </div>
</div>

<div data-digital-card {{ $attributes->merge(['class' => 'w-[79mm] h-[126mm] bg-white rounded-[5mm] overflow-hidden shadow-2xl relative flex flex-col border border-slate-300 break-inside-avoid select-none']) }}>
    {{-- Watermark Logo Background --}}
    <img src="{{ asset('logo.png') }}" alt="Watermark" class="absolute inset-0 m-auto w-[65mm] opacity-[0.045] pointer-events-none select-none z-0">

    {{ $slot }}
</div>

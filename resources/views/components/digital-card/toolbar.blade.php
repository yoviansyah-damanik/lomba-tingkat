@props([
    'title' => 'ID Card B2 (79 x 126 mm)',
    'subtitle' => null,
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
        <flux:button
            id="btn-save-png"
            variant="primary"
            icon="arrow-down-tray"
            size="sm"
            onclick="window.saveCardAsPng('{{ $downloadFilename }}')"
        >Simpan .PNG</flux:button>
    </div>
</div>

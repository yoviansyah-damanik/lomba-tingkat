@props([
    'title' => 'ID Card B2 (79 x 126 mm)',
    'subtitle' => null,
    'backUrl' => 'javascript:window.history.back()',
    'backLabel' => '← Kembali',
    'printLabel' => '🖨 Cetak Kartu B2',
    'batchUrl' => null,
    'batchLabel' => 'Cetak Lembar A4 (Batch)',
    'downloadFilename' => 'KARTU_DIGITAL',
])

<div
    class="w-full max-w-3xl mb-5 flex flex-wrap justify-between items-center gap-3 bg-slate-800 p-[12px_18px] rounded-[14px] shadow-md border border-slate-700 text-slate-50 print:hidden">
    <div>
        <strong class="text-[13px] font-bold text-slate-100 block">{{ $title }}</strong>
        @if ($subtitle)
            <div class="text-[11px] text-slate-400 mt-0.5">{{ $subtitle }}</div>
        @endif
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        @if ($batchUrl)
            <a href="{{ $batchUrl }}"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-[13px] font-semibold rounded-lg bg-amber-500/15 border border-amber-500/40 text-amber-400 hover:bg-amber-500/25 transition">
                {{ $batchLabel }}
            </a>
        @endif
        <a href="{{ $backUrl }}"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-[13px] font-semibold rounded-lg bg-slate-700 border border-slate-600 text-slate-100 hover:bg-slate-600 transition">
            {{ $backLabel }}
        </a>
        <button type="button"
            id="btn-save-png"
            onclick="window.saveCardAsPng ? window.saveCardAsPng('{{ $downloadFilename }}') : alert('Menyiapkan generator PNG...')"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-[13px] font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white transition cursor-pointer shadow-sm">
            💾 Simpan .PNG
        </button>
        <button onclick="window.print()"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-[13px] font-semibold rounded-lg bg-amber-600 hover:bg-amber-500 text-white transition cursor-pointer">
            {{ $printLabel }}
        </button>
    </div>
</div>


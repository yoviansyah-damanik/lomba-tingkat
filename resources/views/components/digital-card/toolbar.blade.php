@props([
    'title' => 'ID Card B2 (79 x 126 mm)',
    'subtitle' => null,
    'batchUrl' => null,
    'batchLabel' => 'Lihat & Simpan Seluruh Kartu (ZIP)',
    'downloadFilename' => 'KARTU_DIGITAL',
])

{{-- Header aksi kartu: ikon, judul ukuran kartu, nama pemilik, dan tombol simpan/batch --}}
<div
    class="relative w-full max-w-3xl mb-6 flex flex-wrap justify-between items-center gap-4 overflow-hidden rounded-2xl border border-slate-700/70 bg-gradient-to-br from-slate-800 via-slate-800 to-slate-900 p-[14px_18px] text-slate-50 shadow-xl shadow-black/30">
    <div class="pointer-events-none absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-amber-400 via-rose-500 to-indigo-500"></div>
    <div class="pointer-events-none absolute -right-10 -top-10 size-32 rounded-full bg-amber-400/10 blur-2xl"></div>

    <div class="relative flex min-w-0 items-center gap-3.5">
        <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-amber-400/15 ring-1 ring-amber-400/30">
            <flux:icon name="identification" class="size-6 text-amber-400" />
        </div>
        <div class="min-w-0">
            <span class="block text-[10px] font-bold uppercase tracking-[0.18em] text-amber-400">{{ $title }}</span>
            @if ($subtitle)
                <strong class="mt-0.5 block truncate text-[15px] font-bold text-slate-50">{{ $subtitle }}</strong>
            @endif
        </div>
    </div>
    <div class="relative flex items-center gap-2 flex-wrap">
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

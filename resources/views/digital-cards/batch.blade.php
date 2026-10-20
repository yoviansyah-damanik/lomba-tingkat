@php
    $sections = $cards->groupBy('group');
@endphp
<x-digital-card.layout :title="$title">
    {{-- Bilah aksi: hanya simpan .ZIP (berisi .PNG seluruh kartu) --}}
    <div class="sticky top-3 z-40 w-full max-w-7xl mb-6 flex flex-wrap justify-between items-center gap-3 bg-slate-800/95 backdrop-blur-sm p-3.5 rounded-xl border border-slate-700 text-slate-50 shadow-md">
        <div>
            <strong class="text-[13px] font-bold text-slate-100 block">{{ $title }}</strong>
            <div class="text-[11px] text-amber-400 mt-0.5">{{ $subtitle }} &bull; Total {{ $cards->count() }} kartu</div>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <flux:button :href="$backUrl" icon="arrow-left" size="sm">Kembali</flux:button>
            <flux:button
                variant="primary"
                icon="arrow-down-tray"
                size="sm"
                onclick="window.saveCardsAsZip('{{ $zipName }}')"
            >Simpan Semua (.ZIP)</flux:button>
        </div>
    </div>

    @forelse ($sections as $groupName => $items)
        <section class="w-full max-w-7xl mb-8">
            <h2 class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-amber-400">
                <span>{{ $groupName }}</span>
                <span class="rounded-full bg-slate-700 px-2 py-0.5 text-[10px] font-semibold text-slate-300">{{ $items->count() }} kartu</span>
            </h2>

            <div class="grid grid-cols-[repeat(auto-fill,minmax(79mm,1fr))] justify-items-center gap-6">
                @foreach ($items as $item)
                    <div class="flex flex-col items-center gap-2">
                        <x-digital-card.item :item="$item" :scout-group="$scoutGroup" />
                        <flux:button
                            size="xs"
                            icon="arrow-down-tray"
                            onclick="window.saveCardAsPng('{{ Str::slug($item['name']) }}_{{ $item['id_number'] }}', this.previousElementSibling)"
                        >Simpan .PNG</flux:button>
                    </div>
                @endforeach
            </div>
        </section>
    @empty
        <div class="w-full max-w-3xl rounded-xl bg-slate-800 p-8 text-center text-sm text-slate-400">
            {{ $emptyMessage }}
        </div>
    @endforelse
</x-digital-card.layout>

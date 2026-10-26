<div class="flex w-full flex-col items-center">
    @php
        $sections = $cards->groupBy('group');
    @endphp

    {{-- Layar pemuatan: menunggu foto, logo, dan QR seluruh kartu selesai dimuat --}}
    <div id="cards-loading" class="fixed inset-0 z-50 flex flex-col items-center justify-center gap-4 bg-[#0f172a] transition-opacity duration-300" role="status" aria-live="polite">
        <div class="size-12 animate-spin rounded-full border-4 border-slate-700 border-t-amber-400"></div>
        <div class="text-center">
            <strong class="block text-sm font-bold text-slate-100">Memuat {{ $cards->count() }} ID Card...</strong>
            <span id="cards-loading-text" class="mt-0.5 block text-[11px] text-amber-400">Menyiapkan kartu</span>
        </div>
        <progress id="cards-loading-bar" value="0" max="100" class="h-2 w-64 max-w-[80vw] overflow-hidden rounded-full [&::-moz-progress-bar]:bg-amber-400 [&::-webkit-progress-bar]:bg-slate-700 [&::-webkit-progress-value]:bg-amber-400 [&::-webkit-progress-value]:transition-all"></progress>
    </div>

    <script>
        (() => {
            const overlay = document.getElementById('cards-loading');
            const bar = document.getElementById('cards-loading-bar');
            const text = document.getElementById('cards-loading-text');

            const finish = () => {
                bar.value = 100;
                text.textContent = 'Selesai';
                overlay.classList.add('opacity-0', 'pointer-events-none');
                setTimeout(() => overlay.remove(), 350);
            };

            const run = () => {
                const images = [...document.querySelectorAll('section img')];
                let done = 0;

                if (!images.length) {
                    return finish();
                }

                const step = () => {
                    done++;
                    bar.value = Math.round((done / images.length) * 100);
                    text.textContent = `${done} dari ${images.length} gambar`;

                    if (done >= images.length) {
                        setTimeout(finish, 150);
                    }
                };

                images.forEach((img) => {
                    img.complete ? step() : ['load', 'error'].forEach((evt) => img.addEventListener(evt, step, { once: true }));
                });

                // Pengaman: jangan menahan layar bila ada gambar yang tidak kunjung selesai
                setTimeout(() => overlay.isConnected && finish(), 20000);
            };

            document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', run) : run();
        })();
    </script>

    {{-- Bilah aksi: hanya simpan .ZIP (berisi .PNG seluruh kartu) --}}
    <div class="sticky top-3 z-40 w-full max-w-7xl mb-6 flex flex-wrap justify-between items-center gap-3 bg-slate-800/95 backdrop-blur-sm p-3.5 rounded-xl border border-slate-700 text-slate-50 shadow-md">
        <div>
            <strong class="text-[13px] font-bold text-slate-100 block">{{ $title }}</strong>
            <div class="text-[11px] text-amber-400 mt-0.5">{{ $subtitle }} &bull; Total {{ $cards->count() }} kartu</div>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
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
</div>

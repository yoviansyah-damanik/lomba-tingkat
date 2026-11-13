<div class="flex w-full flex-col items-center print:block">
    @php
        $a4Pages = $cards->chunk(4);
    @endphp

    {{-- Layar pemuatan: menunggu foto, logo, dan QR seluruh kartu selesai dimuat --}}
    <div id="cards-loading" class="print:hidden fixed inset-0 z-50 flex flex-col items-center justify-center gap-4 bg-[#0f172a] transition-opacity duration-300" role="status" aria-live="polite">
        <div class="size-12 animate-spin rounded-full border-4 border-slate-700 border-t-amber-400"></div>
        <div class="text-center">
            <strong class="block text-sm font-bold text-slate-100">Memuat {{ $cards->count() }} ID Card ({{ $a4Pages->count() }} Lembar A4)...</strong>
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

    {{-- Bilah aksi: Unduh PDF (A4) (4 kartu A6 per halaman) & Simpan .ZIP --}}
    <div data-pdf-filename="{{ $zipName }}" class="print:hidden sticky top-3 z-40 w-full max-w-5xl mb-6 flex flex-wrap justify-between items-center gap-3 bg-slate-800/95 backdrop-blur-sm p-3.5 rounded-xl border border-slate-700 text-slate-50 shadow-md">
        <div>
            <strong class="text-[13px] font-bold text-slate-100 block">{{ $title }}</strong>
            <div class="text-[11px] text-amber-400 mt-0.5">
                {{ $subtitle }} &bull; Total {{ $cards->count() }} kartu A6 (105 &times; 148 mm) &bull; {{ $a4Pages->count() }} Lembar A4
            </div>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <flux:button
                variant="primary"
                icon="document-arrow-down"
                size="sm"
                onclick="window.saveSheetsAsPdf('{{ $zipName }}')"
            >Unduh PDF (A4)</flux:button>
            <flux:button
                icon="arrow-down-tray"
                size="sm"
                onclick="window.saveCardsAsZip('{{ $zipName }}')"
            >Simpan Semua (.ZIP)</flux:button>
        </div>
    </div>

    @forelse ($a4Pages as $pageIndex => $pageItems)
        @php
            $sheetGroups = $pageItems->pluck('group')->filter()->unique()->values();
            $firstCardNumber = ($pageIndex * 4) + 1;
            $lastCardNumber = $firstCardNumber + $pageItems->count() - 1;
        @endphp
        <section class="w-full max-w-5xl mb-8 flex flex-col items-center print:m-0 print:max-w-none print:block">
            <div class="print:hidden w-[210mm] max-w-full mb-2.5 flex flex-wrap items-center justify-between gap-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-lg bg-amber-500/20 border border-amber-500/40 px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider text-amber-300">
                        Lembar A4 #{{ $pageIndex + 1 }}
                    </span>
                    <span class="text-xs font-semibold text-slate-300">
                        Kartu {{ $firstCardNumber }}&ndash;{{ $lastCardNumber }} dari {{ $cards->count() }}
                    </span>
                    @foreach ($sheetGroups as $groupLabel)
                        <span class="rounded-full bg-slate-700/90 px-2.5 py-0.5 text-[10px] font-semibold text-slate-200">
                            {{ $groupLabel }}
                        </span>
                    @endforeach
                </div>
                <div class="flex items-center gap-1.5">
                    <flux:button
                        size="xs"
                        variant="primary"
                        icon="document-arrow-down"
                        onclick="window.saveSheetsAsPdf('{{ $zipName }}_A4_LEMBAR_{{ str_pad((string) ($pageIndex + 1), 2, '0', STR_PAD_LEFT) }}', this.closest('section').querySelector('[data-a4-sheet]'))"
                    >Unduh PDF (A4)</flux:button>
                    <flux:button
                        size="xs"
                        icon="photo"
                        onclick="window.saveCardAsPng('{{ $zipName }}_A4_LEMBAR_{{ str_pad((string) ($pageIndex + 1), 2, '0', STR_PAD_LEFT) }}', this.closest('section').querySelector('[data-a4-sheet]'))"
                    >Simpan Lembar A4 (.PNG)</flux:button>
                </div>
            </div>

            <div class="w-full overflow-x-auto flex justify-center pb-2 print:overflow-visible print:pb-0 print:block">
                {{-- Lembar Cetak A4 Portrait (210mm x 297mm) memuat 4 kartu A6 (2x2 @ 105mm x 148mm) --}}
                <div
                    data-a4-sheet
                    class="w-[210mm] h-[297mm] shrink-0 bg-white shadow-2xl rounded-xs grid grid-cols-2 grid-rows-2 items-center justify-items-center mx-auto relative overflow-hidden print:shadow-none print:rounded-none print:m-0 break-after-page last:break-after-auto"
                >
                    {{-- Garis bantu potong tengah vertikal & horizontal untuk 4 kartu A6 pada kertas A4 --}}
                    <div class="pointer-events-none absolute inset-y-0 left-1/2 -translate-x-1/2 w-0 border-l border-dashed border-slate-300 z-20"></div>
                    <div class="pointer-events-none absolute inset-x-0 top-1/2 -translate-y-1/2 h-0 border-t border-dashed border-slate-300 z-20"></div>

                    @foreach ($pageItems as $item)
                        <div class="w-[105mm] h-[148mm] flex items-center justify-center relative group">
                            <x-digital-card.item :item="$item" :scout-group="$scoutGroup" />
                            <div data-html2canvas-ignore="true" class="print:hidden absolute bottom-2 right-2 z-30 opacity-0 group-hover:opacity-100 focus-within:opacity-100 transition-opacity">
                                <flux:button
                                    size="xs"
                                    variant="primary"
                                    icon="arrow-down-tray"
                                    onclick="window.saveCardAsPng('{{ Str::slug($item['name']) }}_{{ $item['id_number'] }}', this.parentElement.previousElementSibling)"
                                >Simpan .PNG</flux:button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @empty
        <div class="print:hidden w-full max-w-3xl rounded-xl bg-slate-800 p-8 text-center text-sm text-slate-400">
            {{ $emptyMessage }}
        </div>
    @endforelse
</div>

@php
    $indexedCards = $cards->values()->map(function (array $item, int $idx) {
        $item['_idx'] = $idx;
        return $item;
    });
    $a4Pages = $indexedCards->chunk(4);
    $groupsSummary = $indexedCards->groupBy('group')->map(fn ($items, $name) => [
        'name' => (string) $name,
        'indices' => $items->pluck('_idx')->values()->all(),
        'count' => $items->count(),
    ])->values();
    $cardsMeta = $indexedCards->map(fn ($item) => [
        'idx' => $item['_idx'],
        'name' => (string) ($item['name'] ?? ''),
        'id_number' => (string) ($item['id_number'] ?? ''),
        'scout_id' => (string) ($item['scout_id'] ?? ''),
        'group' => (string) ($item['group'] ?? ''),
        'category' => (string) ($item['category_label'] ?? ''),
    ])->values();
@endphp

<div
    x-data="{
        cardsMeta: {{ \Illuminate\Support\Js::from($cardsMeta) }},
        groupsSummary: {{ \Illuminate\Support\Js::from($groupsSummary) }},
        selected: {{ \Illuminate\Support\Js::from($indexedCards->pluck('_idx')->values()) }},
        selectorOpen: true,
        search: '',
        groupFilter: 'all',
        slotMap: {},
        init() {
            this.$nextTick(() => {
                document.querySelectorAll('[data-card-slot]').forEach((el) => {
                    this.slotMap[Number(el.getAttribute('data-card-slot'))] = el;
                });
            });
            this.$watch('selected', () => this.reflowSheets());
        },
        get totalCount() {
            return this.cardsMeta.length;
        },
        get selectedCount() {
            return this.selected.length;
        },
        get activeSheetCount() {
            return Math.ceil(this.selected.length / 4);
        },
        get filteredMeta() {
            const q = this.search.trim().toLowerCase();
            return this.cardsMeta.filter((c) => {
                if (this.groupFilter !== 'all' && c.group !== this.groupFilter) return false;
                if (!q) return true;
                return c.name.toLowerCase().includes(q)
                    || c.id_number.toLowerCase().includes(q)
                    || c.scout_id.toLowerCase().includes(q)
                    || c.group.toLowerCase().includes(q);
            });
        },
        isSelected(idx) {
            return this.selected.includes(Number(idx));
        },
        toggleCard(idx) {
            const n = Number(idx);
            if (this.selected.includes(n)) {
                this.selected = this.selected.filter((i) => i !== n);
            } else {
                this.selected = [...this.selected, n].sort((a, b) => a - b);
            }
        },
        selectAll() {
            this.selected = this.cardsMeta.map((c) => c.idx);
        },
        deselectAll() {
            this.selected = [];
        },
        invertSelection() {
            const current = new Set(this.selected);
            this.selected = this.cardsMeta.filter((c) => !current.has(c.idx)).map((c) => c.idx);
        },
        selectFiltered() {
            const next = new Set(this.selected);
            this.filteredMeta.forEach((c) => next.add(c.idx));
            this.selected = [...next].sort((a, b) => a - b);
        },
        deselectFiltered() {
            const remove = new Set(this.filteredMeta.map((c) => c.idx));
            this.selected = this.selected.filter((i) => !remove.has(i));
        },
        isGroupAllSelected(indices) {
            return indices.length > 0 && indices.every((i) => this.selected.includes(i));
        },
        groupSelectedCount(indices) {
            return indices.filter((i) => this.selected.includes(i)).length;
        },
        toggleGroup(indices) {
            if (this.isGroupAllSelected(indices)) {
                const remove = new Set(indices);
                this.selected = this.selected.filter((i) => !remove.has(i));
            } else {
                const next = new Set([...this.selected, ...indices]);
                this.selected = [...next].sort((a, b) => a - b);
            }
        },
        sheetCardRangeLabel(pageIndex) {
            const start = pageIndex * 4 + 1;
            const end = Math.min((pageIndex + 1) * 4, this.selected.length);
            return `Kartu ${start}\u2013${end} dari ${this.selected.length} dipilih`;
        },
        reflowSheets() {
            const pool = this.$refs.unselectedPool;
            if (!pool) return;

            const sortedSelected = [...this.selected].map(Number).sort((a, b) => a - b);
            const selectedSet = new Set(sortedSelected);

            Object.entries(this.slotMap).forEach(([idx, el]) => {
                if (!selectedSet.has(Number(idx))) {
                    pool.appendChild(el);
                }
            });

            const sections = [...document.querySelectorAll('[data-sheet-section]')];
            sections.forEach((section, pageIndex) => {
                const sheet = section.querySelector('[data-a4-sheet]');
                const chunk = sortedSelected.slice(pageIndex * 4, (pageIndex + 1) * 4);

                if (chunk.length > 0) {
                    section.classList.remove('hidden', 'print:hidden');
                    sheet.removeAttribute('data-sheet-empty');
                    chunk.forEach((idx) => {
                        const slot = this.slotMap[idx];
                        if (slot) sheet.appendChild(slot);
                    });
                } else {
                    section.classList.add('hidden', 'print:hidden');
                    sheet.setAttribute('data-sheet-empty', 'true');
                }
            });
        },
    }"
    class="flex w-full flex-col items-center print:block"
>
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

    {{-- Bilah aksi: Pilih Kartu, Unduh PDF (A4) (4 kartu A6 per halaman), & Simpan .ZIP --}}
    <div data-pdf-filename="{{ $zipName }}" class="print:hidden sticky top-3 z-40 w-full max-w-5xl mb-4 flex flex-wrap justify-between items-center gap-3 bg-slate-800/95 backdrop-blur-sm p-3.5 rounded-xl border border-slate-700 text-slate-50 shadow-md">
        <div>
            <strong class="text-[13px] font-bold text-slate-100 block">{{ $title }}</strong>
            <div class="text-[11px] text-amber-400 mt-0.5">
                {{ $subtitle }} &bull;
                <span x-text="selectedCount">{{ $cards->count() }}</span> dari {{ $cards->count() }} kartu dipilih &bull;
                <span x-text="activeSheetCount">{{ $a4Pages->count() }}</span> Lembar A4 (105 &times; 148 mm)
            </div>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @if ($cards->isNotEmpty())
                <flux:button
                    size="sm"
                    icon="check-circle"
                    x-on:click="selectorOpen = !selectorOpen"
                >
                    <span x-text="selectorOpen ? 'Sembunyikan Pilihan' : 'Pilih Kartu (' + selectedCount + '/' + totalCount + ')'">Pilih Kartu</span>
                </flux:button>
            @endif
            <flux:button
                variant="primary"
                icon="document-arrow-down"
                size="sm"
                x-bind:disabled="selectedCount === 0"
                onclick="window.saveSheetsAsPdf('{{ $zipName }}')"
            >Unduh PDF (A4)</flux:button>
            <flux:button
                icon="photo"
                size="sm"
                x-bind:disabled="selectedCount === 0"
                onclick="window.saveSheetsAsPng('{{ $zipName }}')"
            >Unduh PNG (A4)</flux:button>
            <flux:button
                icon="arrow-down-tray"
                size="sm"
                x-bind:disabled="selectedCount === 0"
                onclick="window.saveCardsAsZip('{{ $zipName }}')"
            >Simpan Semua (.ZIP)</flux:button>
        </div>
    </div>

    {{-- Panel Interaktif Pemilihan Kartu yang Akan Diunduh / Dicetak --}}
    @if ($cards->isNotEmpty())
        <div x-show="selectorOpen" x-transition class="print:hidden w-full max-w-5xl mb-6 rounded-xl border border-slate-700 bg-slate-800/90 p-4 text-slate-100 shadow-lg space-y-3.5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5 border-b border-slate-700/80 pb-3">
                <div>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-amber-400">
                        Pilih Kartu yang Akan Diunduh / Dicetak
                    </h2>
                    <p class="text-[11px] text-slate-400 mt-0.5">
                        Centang kartu yang diinginkan. Kartu yang dipilih akan otomatis disusun rapat (4 kartu per lembar A4) tanpa ruang kosong yang terbuang.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-1.5 shrink-0">
                    <flux:button size="xs" icon="check" x-on:click="selectAll()">
                        Pilih Semua ({{ $cards->count() }})
                    </flux:button>
                    <flux:button size="xs" icon="x-mark" x-on:click="deselectAll()">
                        Kosongkan
                    </flux:button>
                    <flux:button size="xs" icon="arrows-right-left" x-on:click="invertSelection()">
                        Balik Pilihan
                    </flux:button>
                </div>
            </div>

            {{-- Pencarian & Filter Kelompok --}}
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                <div class="relative flex-1">
                    <input
                        type="search"
                        x-model="search"
                        placeholder="Cari nama peserta / panitia / bindamping, nomor ID, NTA, atau regu..."
                        class="w-full rounded-lg border border-slate-600 bg-slate-900/90 px-3 py-1.5 text-xs text-slate-100 placeholder-slate-400 focus:border-amber-400 focus:outline-hidden"
                    >
                </div>
                @if ($groupsSummary->count() > 1)
                    <select
                        x-model="groupFilter"
                        class="rounded-lg border border-slate-600 bg-slate-900/90 px-3 py-1.5 text-xs text-slate-100 focus:border-amber-400 focus:outline-hidden"
                    >
                        <option value="all">Semua Kelompok ({{ $groupsSummary->count() }})</option>
                        @foreach ($groupsSummary as $grp)
                            <option value="{{ $grp['name'] }}">{{ $grp['name'] }} ({{ $grp['count'] }})</option>
                        @endforeach
                    </select>
                @endif
                <template x-if="search.trim() !== '' || groupFilter !== 'all'">
                    <div class="flex items-center gap-1.5 shrink-0">
                        <flux:button size="xs" icon="plus" x-on:click="selectFiltered()">
                            Pilih Hasil Filter
                        </flux:button>
                        <flux:button size="xs" icon="minus" x-on:click="deselectFiltered()">
                            Hapus Hasil Filter
                        </flux:button>
                    </div>
                </template>
            </div>

            {{-- Pintasan Pilih per Kelompok / Regu --}}
            @if ($groupsSummary->count() > 1)
                <div class="flex flex-wrap items-center gap-1.5 pt-0.5">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mr-1">Kelompok:</span>
                    @foreach ($groupsSummary as $grp)
                        <button
                            type="button"
                            x-on:click="toggleGroup({{ \Illuminate\Support\Js::from($grp['indices']) }})"
                            class="cursor-pointer inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-semibold transition"
                            :class="isGroupAllSelected({{ \Illuminate\Support\Js::from($grp['indices']) }})
                                ? 'border-amber-400/70 bg-amber-500/20 text-amber-200'
                                : 'border-slate-700 bg-slate-900/70 text-slate-400 hover:border-slate-500 hover:text-slate-200'"
                        >
                            <span x-text="isGroupAllSelected({{ \Illuminate\Support\Js::from($grp['indices']) }}) ? '✓' : '+'"></span>
                            <span>{{ $grp['name'] }}</span>
                            <span class="rounded-full bg-slate-800 px-1.5 py-0.2 text-[10px] font-mono text-slate-300">
                                <span x-text="groupSelectedCount({{ \Illuminate\Support\Js::from($grp['indices']) }})"></span>/{{ $grp['count'] }}
                            </span>
                        </button>
                    @endforeach
                </div>
            @endif

            {{-- Daftar Centang Per Kartu --}}
            <div class="max-h-56 overflow-y-auto pr-1 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                <template x-for="card in filteredMeta" :key="card.idx">
                    <label
                        class="flex items-start gap-2.5 rounded-lg border p-2 text-left text-xs cursor-pointer transition select-none"
                        :class="isSelected(card.idx)
                            ? 'border-amber-400/60 bg-amber-500/10 text-slate-100'
                            : 'border-slate-700/80 bg-slate-900/50 text-slate-400 hover:border-slate-600'"
                    >
                        <input
                            type="checkbox"
                            class="mt-0.5 size-4 rounded border-slate-600 text-amber-500 focus:ring-amber-400"
                            :checked="isSelected(card.idx)"
                            x-on:change="toggleCard(card.idx)"
                        >
                        <span class="min-w-0 flex-1 leading-tight">
                            <strong class="block truncate font-bold text-slate-100" x-text="card.name"></strong>
                            <span class="mt-0.5 block truncate font-mono text-[10px] text-amber-300/90" x-text="card.id_number + (card.scout_id ? ' • NTA ' + card.scout_id : '')"></span>
                            <span class="mt-0.5 block truncate text-[10px] text-slate-400" x-text="card.group"></span>
                        </span>
                    </label>
                </template>
            </div>
        </div>
    @endif

    {{-- Pool tersembunyi untuk menampung elemen kartu yang sedang tidak dicentang --}}
    <div x-ref="unselectedPool" class="hidden print:hidden" aria-hidden="true"></div>

    {{-- Pesan bila pengguna mengosongkan seluruh pilihan kartu --}}
    <div x-show="totalCount > 0 && selectedCount === 0" x-cloak class="print:hidden w-full max-w-3xl rounded-xl border border-amber-500/40 bg-slate-800/90 p-8 text-center text-sm text-amber-200 space-y-3">
        <p class="font-semibold">Tidak ada kartu yang dipilih untuk diunduh atau dicetak.</p>
        <p class="text-xs text-slate-400">Silakan centang minimal satu kartu pada panel pilihan di atas atau klik tombol di bawah.</p>
        <div>
            <flux:button size="sm" variant="primary" icon="check" x-on:click="selectAll()">
                Pilih Semua Kartu ({{ $cards->count() }})
            </flux:button>
        </div>
    </div>

    @forelse ($a4Pages as $pageIndex => $pageItems)
        @php
            $sheetGroups = $pageItems->pluck('group')->filter()->unique()->values();
            $firstCardNumber = ($pageIndex * 4) + 1;
            $lastCardNumber = $firstCardNumber + $pageItems->count() - 1;
        @endphp
        <section data-sheet-section="{{ $pageIndex }}" class="w-full max-w-5xl mb-8 flex flex-col items-center print:m-0 print:max-w-none print:block">
            <div class="print:hidden w-[210mm] max-w-full mb-2.5 flex flex-wrap items-center justify-between gap-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-lg bg-amber-500/20 border border-amber-500/40 px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider text-amber-300">
                        Lembar A4 #{{ $pageIndex + 1 }}
                    </span>
                    <span class="text-xs font-semibold text-slate-300" x-text="sheetCardRangeLabel({{ $pageIndex }})">
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
                        onclick="window.saveSheetsAsPng('{{ $zipName }}_A4_LEMBAR_{{ str_pad((string) ($pageIndex + 1), 2, '0', STR_PAD_LEFT) }}', this.closest('section').querySelector('[data-a4-sheet]'))"
                    >Unduh PNG (A4)</flux:button>
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
                        <div data-card-slot="{{ $item['_idx'] }}" class="w-[105mm] h-[148mm] flex items-center justify-center relative group">
                            <x-digital-card.item :item="$item" :scout-group="$scoutGroup" />
                            {{-- Tombol centang/hapus kartu langsung dari atas pratinjau lembar A4 --}}
                            <div data-html2canvas-ignore="true" class="print:hidden absolute top-2 left-2 z-30 opacity-0 group-hover:opacity-100 focus-within:opacity-100 transition-opacity">
                                <flux:button
                                    size="xs"
                                    icon="x-mark"
                                    x-on:click="toggleCard({{ $item['_idx'] }})"
                                    title="Keluarkan kartu ini dari lembar cetak/unduhan"
                                >Keluarkan</flux:button>
                            </div>
                            <div data-html2canvas-ignore="true" class="print:hidden absolute bottom-2 right-2 z-30 opacity-0 group-hover:opacity-100 focus-within:opacity-100 transition-opacity">
                                <flux:button
                                    size="xs"
                                    variant="primary"
                                    icon="arrow-down-tray"
                                    onclick="window.saveCardAsPng('{{ Str::slug($item['name']) }}_{{ $item['id_number'] }}', this.closest('[data-card-slot]').querySelector('[data-digital-card]'))"
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

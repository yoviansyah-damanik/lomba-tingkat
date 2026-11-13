{{-- Modal pratinjau berkas (gambar dengan zoom / PDF) seragam di seluruh aplikasi; dibuka lewat <x-file-viewer-trigger>
     atau event window "open-file-viewer" ({ url, title }). Memakai <dialog>.showModal() agar berada di top layer
     browser dan selalu tampil di atas modal lain (mis. flux:modal). --}}
<div x-data="{
        open: false,
        url: '',
        title: '',
        scale: 1,
        pdfViewer: null,
        pdfLoading: false,
        pdfPages: 0,
        pdfCurrentPage: 1,
        get isMobile() {
            return window.innerWidth < 768 || /android|iphone|ipad|ipod/i.test(navigator.userAgent);
        },
        get kind() {
            const ext = this.url.split('?')[0].split('#')[0].split('.').pop().toLowerCase();
            if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'].includes(ext)) return 'image';
            if (ext === 'pdf') return 'pdf';
            return 'other';
        },
        dialog() {
            return document.getElementById('file-viewer-dialog');
        },
        show(detail) {
            this.url = detail.url;
            this.title = detail.title || 'Pratinjau Berkas';
            this.scale = 1;
            this.open = true;
            this.pdfPages = 0;
            this.pdfCurrentPage = 1;
            const dialog = this.dialog();
            if (dialog && !dialog.open) dialog.showModal();

            if (this.kind === 'pdf') {
                this.$nextTick(() => {
                    this.initPdf();
                });
            }
        },
        async initPdf() {
            const container = document.getElementById('pdf-canvas-container');
            if (!container || !window.loadPdfViewer) return;
            this.pdfLoading = true;
            try {
                if (!this.pdfViewer) {
                    this.pdfViewer = await window.loadPdfViewer(container);
                }
                await this.pdfViewer.load(this.url, (info) => {
                    this.pdfPages = info.numPages;
                    this.pdfCurrentPage = info.currentPage;
                    this.pdfLoading = false;
                });
            } catch (e) {
                this.pdfLoading = false;
            }
        },
        pdfPrev() {
            if (this.pdfViewer) {
                this.pdfViewer.prevPage();
                this.pdfCurrentPage = this.pdfViewer.pageNum;
            }
        },
        pdfNext() {
            if (this.pdfViewer) {
                this.pdfViewer.nextPage();
                this.pdfCurrentPage = this.pdfViewer.pageNum;
            }
        },
        pdfZoom(step) {
            if (this.pdfViewer) {
                this.pdfViewer.zoom(step);
            }
        },
        pdfReset() {
            if (this.pdfViewer) {
                this.pdfViewer.resetZoom();
            }
        },
        close() {
            const dialog = this.dialog();
            if (dialog && dialog.open) dialog.close();
            this.closed();
        },
        closed() {
            this.open = false;
            this.url = '';
            this.scale = 1;
            if (this.pdfViewer) {
                this.pdfViewer.destroy();
                this.pdfViewer = null;
            }
        },
        zoom(step) {
            this.scale = Math.min(3.5, Math.max(0.5, +(this.scale + step).toFixed(2)));
        },
    }"
    x-on:open-file-viewer.window="show($event.detail)">
    <template x-teleport="body">
        <dialog id="file-viewer-dialog"
            x-on:close="closed()"
            x-on:click.self="close()"
            class="m-0 h-dvh max-h-none w-screen max-w-none overflow-hidden bg-black/85 p-0 text-white backdrop-blur-xs backdrop:bg-black/80"
            :aria-label="title">
            <div class="flex h-full flex-col items-center justify-between gap-3 p-3 sm:p-6" x-on:click.self="close()">
                {{-- Bilah judul & kontrol --}}
                <div class="flex w-full max-w-5xl shrink-0 flex-wrap items-center justify-between gap-2.5 rounded-2xl border border-zinc-700/60 bg-zinc-900/95 px-3 py-2.5 sm:px-4 sm:py-3 shadow-xl">
                    <div class="flex min-w-0 items-center gap-2">
                        <flux:icon x-show="kind === 'image'" name="photo" class="size-5 shrink-0 text-indigo-400" />
                        <flux:icon x-show="kind === 'pdf'" name="document-text" class="size-5 shrink-0 text-amber-400" />
                        <flux:icon x-show="kind === 'other'" name="document-text" class="size-5 shrink-0 text-amber-400" />
                        <div class="min-w-0">
                            <div class="truncate text-xs font-bold text-zinc-100 sm:text-sm" x-text="title"></div>
                            <div class="text-[10px] sm:text-[11px] text-zinc-400" x-text="kind === 'image' ? 'Kontrol zoom in / out' : (kind === 'pdf' ? (pdfPages > 1 ? `Halaman ${pdfCurrentPage} dari ${pdfPages}` : 'Dokumen PDF') : 'Pratinjau Dokumen Resmi')"></div>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-1.5 sm:gap-2">
                        <template x-if="kind === 'image'">
                            <div class="flex items-center gap-1 sm:gap-2">
                                <span class="rounded-lg border border-zinc-700/50 bg-zinc-800 px-2 py-1 font-mono text-xs font-semibold text-amber-300" x-text="Math.round(scale * 100) + '%'"></span>
                                <flux:button size="xs" variant="subtle" icon="magnifying-glass-plus" x-on:click="zoom(0.25)">Zoom In</flux:button>
                                <flux:button size="xs" variant="subtle" icon="magnifying-glass-minus" x-on:click="zoom(-0.25)">Zoom Out</flux:button>
                                <flux:button size="xs" variant="subtle" icon="arrow-path" x-on:click="scale = 1">Reset</flux:button>
                            </div>
                        </template>

                        <template x-if="kind === 'pdf'">
                            <div class="flex items-center gap-1 sm:gap-1.5">
                                <template x-if="pdfPages > 1">
                                    <div class="flex items-center gap-1 mr-1">
                                        <flux:button size="xs" variant="subtle" icon="chevron-left" x-bind:disabled="pdfCurrentPage <= 1" x-on:click="pdfPrev()">Hal</flux:button>
                                        <span class="rounded-md border border-zinc-700/50 bg-zinc-800 px-1.5 py-0.5 font-mono text-[11px] text-amber-300" x-text="`${pdfCurrentPage}/${pdfPages}`"></span>
                                        <flux:button size="xs" variant="subtle" icon="chevron-right" x-bind:disabled="pdfCurrentPage >= pdfPages" x-on:click="pdfNext()">Hal</flux:button>
                                    </div>
                                </template>
                                <flux:button size="xs" variant="subtle" icon="magnifying-glass-plus" x-on:click="pdfZoom(0.25)"></flux:button>
                                <flux:button size="xs" variant="subtle" icon="magnifying-glass-minus" x-on:click="pdfZoom(-0.25)"></flux:button>
                                <flux:button size="xs" variant="subtle" icon="arrow-path" x-on:click="pdfReset()"></flux:button>
                            </div>
                        </template>

                        <flux:button size="xs" variant="subtle" icon="arrow-top-right-on-square" x-bind:href="url" target="_blank" rel="noopener">
                            Tab Baru
                        </flux:button>
                        <flux:button size="xs" variant="danger" icon="x-mark" x-on:click="close()">
                            Tutup
                        </flux:button>
                    </div>
                </div>

                {{-- Isi pratinjau --}}
                <div class="relative min-h-0 w-full max-w-5xl flex-1 overflow-hidden rounded-2xl border border-zinc-700/60 bg-zinc-900 shadow-2xl">
                    <template x-if="open && kind === 'image'">
                        <div class="flex h-full w-full items-center justify-center overflow-auto p-4"
                            x-on:wheel.prevent="zoom($event.deltaY < 0 ? 0.25 : -0.25)">
                            <img :src="url" :alt="title" class="max-h-full max-w-full cursor-zoom-in rounded-lg object-contain shadow-2xl transition-transform duration-150"
                                :style="`transform: scale(${scale})`"
                                x-on:click="scale = scale === 1 ? 2 : 1">
                        </div>
                    </template>

                    {{-- Penampil PDF Canvas (langsung tampil di mobile maupun desktop) --}}
                    <div x-show="open && kind === 'pdf'" class="relative h-full w-full overflow-auto bg-zinc-950 p-2 sm:p-4 text-center">
                        <div x-show="pdfLoading" class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-zinc-900/80 backdrop-blur-xs">
                            <flux:icon name="arrow-path" class="size-8 animate-spin text-amber-500 mb-2" />
                            <span class="text-xs text-zinc-300 font-medium">Memuat dokumen PDF...</span>
                        </div>
                        <div id="pdf-canvas-container" class="flex min-h-full items-center justify-center"></div>
                    </div>

                    <template x-if="open && kind === 'other'">
                        <div class="flex h-full flex-col items-center justify-center gap-3 p-6 text-center">
                            <flux:icon name="document-arrow-down" class="size-12 text-amber-400" />
                            <p class="text-sm text-zinc-300">Pratinjau tidak tersedia untuk jenis berkas ini.</p>
                            <flux:button variant="primary" icon="arrow-down-tray" x-bind:href="url" target="_blank" rel="noopener">
                                Unduh Berkas
                            </flux:button>
                        </div>
                    </template>
                </div>

                <p class="shrink-0 text-[10px] sm:text-[11px] text-zinc-400">Tekan tombol ESC atau klik di luar area untuk menutup</p>
            </div>
        </dialog>
    </template>
</div>

        {{-- Modal Viewer Berkas / Dokumen yang Diunggah (menggunakan <dialog>.showModal() agar berada di top-layer) --}}
        <template x-teleport="body">
            <dialog x-ref="docModalDialog"
                    @close="docModalOpen = false; docModalSrc = ''; imageZoomScale = 1; pdfScale = 1; window.pdfViewerManager?.destroy('doc-modal-viewer');"
                    @keydown.escape.stop.prevent="closeDocModal()"
                    @click.self="closeDocModal()"
                    class="m-0 h-dvh max-h-none w-screen max-w-none overflow-hidden bg-black/85 p-0 text-white backdrop-blur-xs backdrop:bg-black/80"
                    :aria-label="docModalTitle"
                    x-data="{
                        pdfLoading: false,
                        pdfPages: 0,
                        pdfCurrentPage: 1,
                        pdfScale: 1,
                        viewerId: 'doc-modal-viewer',
                        async initPdf() {
                            const container = this.$refs.docModalPdfContainer;
                            if (!container || !window.pdfViewerManager) return;
                            this.pdfLoading = true;
                            this.pdfScale = 1;
                            try {
                                const viewer = await window.pdfViewerManager.getOrCreate(this.viewerId, container);
                                await viewer.load(docModalSrc, (info) => {
                                    this.pdfPages = info.numPages;
                                    this.pdfCurrentPage = info.currentPage;
                                    this.pdfScale = 1;
                                    this.pdfLoading = false;
                                });
                            } catch (e) {
                                console.error(e);
                                this.pdfLoading = false;
                            }
                        },
                        pdfPrev() {
                            const viewer = window.pdfViewerManager?.get(this.viewerId);
                            if (viewer) {
                                this.pdfCurrentPage = viewer.prevPage();
                            }
                        },
                        pdfNext() {
                            const viewer = window.pdfViewerManager?.get(this.viewerId);
                            if (viewer) {
                                this.pdfCurrentPage = viewer.nextPage();
                            }
                        },
                        pdfZoom(step) {
                            const viewer = window.pdfViewerManager?.get(this.viewerId);
                            if (viewer) {
                                this.pdfScale = viewer.zoom(step);
                            }
                        },
                        pdfReset() {
                            const viewer = window.pdfViewerManager?.get(this.viewerId);
                            if (viewer) {
                                this.pdfScale = viewer.resetZoom();
                            }
                        }
                    }"
                    x-effect="if (docModalOpen && docModalType === 'pdf') { $nextTick(() => initPdf()); }">
                <div class="flex h-full w-full flex-col items-center justify-between p-3 sm:p-6"
                     @click.self="closeDocModal()">

                    {{-- Modal Header & Controls --}}
                    <div class="flex w-full max-w-5xl items-center justify-between gap-2.5 rounded-2xl bg-zinc-900/95 px-3 py-2.5 sm:px-4 sm:py-3 text-white border border-zinc-700/60 shadow-xl shrink-0 flex-wrap">
                        <div class="flex items-center gap-2 min-w-0">
                            <flux:icon name="document-text" class="size-5 text-amber-400 shrink-0" />
                            <div class="truncate">
                                <div class="text-xs sm:text-sm font-bold truncate text-zinc-100" x-text="docModalTitle"></div>
                                <div class="text-[10px] sm:text-[11px] text-zinc-400" x-text="docModalType === 'image' ? 'Kontrol zoom in / out' : (docModalType === 'pdf' ? (pdfPages > 1 ? `Halaman ${pdfCurrentPage} dari ${pdfPages}` : 'Dokumen PDF') : 'Pratinjau Dokumen Resmi')"></div>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                            <template x-if="docModalType === 'image'">
                                <div class="flex items-center gap-1 sm:gap-2">
                                    <span class="rounded-lg bg-zinc-800 px-2 py-1 font-mono text-xs font-semibold text-amber-300 border border-zinc-700/50"
                                          x-text="Math.round(imageZoomScale * 100) + '%'">
                                    </span>
                                    <flux:button size="xs" variant="subtle" icon="magnifying-glass-plus" @click="zoomIn()">
                                        Zoom In
                                    </flux:button>
                                    <flux:button size="xs" variant="subtle" icon="magnifying-glass-minus" @click="zoomOut()">
                                        Zoom Out
                                    </flux:button>
                                    <flux:button size="xs" variant="subtle" icon="arrow-path" @click="resetZoom()">
                                        Reset
                                    </flux:button>
                                </div>
                            </template>

                            <template x-if="docModalType === 'pdf'">
                                <div class="flex items-center gap-1 sm:gap-1.5">
                                    <span class="rounded-lg bg-zinc-800 px-2 py-1 font-mono text-xs font-semibold text-amber-300 border border-zinc-700/50"
                                          x-text="Math.round(pdfScale * 100) + '%'">
                                    </span>
                                    <template x-if="pdfPages > 1">
                                        <div class="flex items-center gap-1 mr-1">
                                            <flux:button size="xs" variant="subtle" icon="chevron-left" x-bind:disabled="pdfCurrentPage <= 1" @click="pdfPrev()">Hal</flux:button>
                                            <span class="rounded-md border border-zinc-700/50 bg-zinc-800 px-1.5 py-0.5 font-mono text-[11px] text-amber-300" x-text="`${pdfCurrentPage}/${pdfPages}`"></span>
                                            <flux:button size="xs" variant="subtle" icon="chevron-right" x-bind:disabled="pdfCurrentPage >= pdfPages" @click="pdfNext()">Hal</flux:button>
                                        </div>
                                    </template>
                                    <flux:button size="xs" variant="subtle" icon="magnifying-glass-plus" @click="pdfZoom(0.25)" title="Perbesar (Zoom In)"></flux:button>
                                    <flux:button size="xs" variant="subtle" icon="magnifying-glass-minus" @click="pdfZoom(-0.25)" title="Perkecil (Zoom Out)"></flux:button>
                                    <flux:button size="xs" variant="subtle" icon="arrow-path" @click="pdfReset()" title="Reset Zoom"></flux:button>
                                </div>
                            </template>

                            <flux:button size="xs" variant="subtle" icon="arrow-top-right-on-square" as="a" x-bind:href="docModalSrc" target="_blank">
                                Tab Baru
                            </flux:button>

                            <flux:button size="xs" variant="danger" icon="x-mark" @click="closeDocModal()">
                                Tutup
                            </flux:button>
                        </div>
                    </div>

                    {{-- Modal Content: Gambar dengan Zoom atau PDF Canvas Viewer --}}
                    <div class="flex flex-1 w-full max-w-5xl items-center justify-center overflow-auto p-2 sm:p-4 my-2 select-none"
                         @click.self="closeDocModal()">
                        {{-- Jika file gambar --}}
                        <template x-if="docModalOpen && docModalType === 'image'">
                            <div class="flex size-full items-center justify-center overflow-auto"
                                 @click.self="closeDocModal()"
                                 @wheel.prevent="if ($event.deltaY < 0) zoomIn(); else zoomOut();"
                                 @dblclick="toggleZoom()">
                                 <img :src="docModalSrc"
                                      :alt="docModalTitle"
                                      class="max-h-[75vh] max-w-full rounded-xl object-contain shadow-2xl transition-transform duration-150 ease-out cursor-pointer"
                                      :style="'transform: scale(' + imageZoomScale + ');'"
                                      title="Klik ganda untuk toggle zoom 2x / 1x, scroll untuk zoom in/out">
                            </div>
                        </template>

                        {{-- Jika file PDF (HTML5 Canvas via PDF.js) --}}
                        <div x-show="docModalOpen && docModalType === 'pdf'"
                             @wheel.ctrl.prevent="pdfZoom($event.deltaY < 0 ? 0.25 : -0.25)"
                             class="relative size-full max-h-[75vh] rounded-xl overflow-auto bg-zinc-950 p-2 sm:p-4 shadow-2xl text-center">
                            <div x-show="pdfLoading" class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-zinc-900/80 backdrop-blur-xs">
                                <flux:icon name="arrow-path" class="size-8 animate-spin text-amber-500 mb-2" />
                                <span class="text-xs text-zinc-300 font-medium">Memuat dokumen PDF...</span>
                            </div>
                            <div x-ref="docModalPdfContainer" class="flex min-h-full min-w-full"></div>
                        </div>

                        {{-- Jika tipe berkas lainnya --}}
                        <template x-if="docModalOpen && docModalType === 'other'">
                            <div class="rounded-2xl bg-zinc-900 border border-zinc-700 p-8 text-center text-white max-w-md">
                                <flux:icon name="document-arrow-down" class="size-12 mx-auto text-amber-400 mb-3" />
                                <h4 class="text-sm font-bold mb-1" x-text="docModalTitle"></h4>
                                <p class="text-xs text-zinc-400 mb-4">Pratinjau langsung tidak didukung untuk jenis file ini. Silakan unduh atau buka di tab baru.</p>
                                <flux:button as="a" x-bind:href="docModalSrc" target="_blank" variant="primary" icon="arrow-top-right-on-square">
                                    Buka / Unduh Berkas
                                </flux:button>
                            </div>
                        </template>
                    </div>

                    {{-- Modal Footer Tip --}}
                    <div class="text-[10px] sm:text-[11px] text-zinc-400 text-center shrink-0">
                        Tekan tombol ESC atau klik di luar area untuk menutup modal pratinjau
                    </div>
                </div>
            </dialog>
        </template>

        {{-- Modal Viewer Berkas / Dokumen yang Diunggah --}}
        <div x-show="docModalOpen"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex flex-col items-center justify-between bg-black/85 backdrop-blur-xs p-4 sm:p-6"
             @click.self="closeDocModal()">

            {{-- Modal Header & Controls --}}
            <div class="flex w-full max-w-5xl items-center justify-between gap-3 rounded-2xl bg-zinc-900/95 px-4 py-3 text-white border border-zinc-700/60 shadow-xl shrink-0">
                <div class="flex items-center gap-2.5 min-w-0">
                    <flux:icon name="photo" class="size-5 text-indigo-400 shrink-0" />
                    <div class="truncate">
                        <div class="text-xs sm:text-sm font-bold truncate text-zinc-100" x-text="docModalTitle"></div>
                        <div class="text-[11px] text-zinc-400" x-text="docModalType === 'image' ? 'Gunakan tombol kontrol atau scroll mouse untuk zoom in/out' : 'Pratinjau Berkas Resmi'"></div>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <template x-if="docModalType === 'image'">
                        <div class="flex items-center gap-2">
                            <span class="rounded-lg bg-zinc-800 px-2 py-1 font-mono text-xs font-semibold text-indigo-300 border border-zinc-700/50"
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

                    <flux:button size="xs" variant="subtle" icon="arrow-top-right-on-square" as="a" x-bind:href="docModalSrc" target="_blank">
                        Buka Tab Baru
                    </flux:button>

                    <flux:button size="xs" variant="danger" icon="x-mark" @click="closeDocModal()">
                        Tutup
                    </flux:button>
                </div>
            </div>

            {{-- Modal Content: Gambar dengan Zoom atau Dokumen Viewer --}}
            <div class="flex flex-1 w-full max-w-5xl items-center justify-center overflow-auto p-4 my-2 select-none">
                {{-- Jika file gambar --}}
                <template x-if="docModalType === 'image'">
                    <div class="flex size-full items-center justify-center overflow-auto"
                         @wheel.prevent="if ($event.deltaY < 0) zoomIn(); else zoomOut();"
                         @dblclick="toggleZoom()">
                        <img :src="docModalSrc"
                             :alt="docModalTitle"
                             class="max-h-[75vh] max-w-full rounded-xl object-contain shadow-2xl transition-transform duration-150 ease-out cursor-pointer"
                             :style="'transform: scale(' + imageZoomScale + ');'"
                             title="Klik ganda untuk toggle zoom 2x / 1x, scroll untuk zoom in/out">
                    </div>
                </template>

                {{-- Jika file PDF --}}
                <template x-if="docModalType === 'pdf'">
                    <div class="size-full max-h-[75vh] rounded-xl overflow-hidden bg-white shadow-2xl">
                        <iframe :src="docModalSrc" class="size-full border-0"></iframe>
                    </div>
                </template>

                {{-- Jika tipe berkas lainnya --}}
                <template x-if="docModalType === 'other'">
                    <div class="rounded-2xl bg-zinc-900 border border-zinc-700 p-8 text-center text-white max-w-md">
                        <flux:icon name="document-arrow-down" class="size-12 mx-auto text-indigo-400 mb-3" />
                        <h4 class="text-sm font-bold mb-1" x-text="docModalTitle"></h4>
                        <p class="text-xs text-zinc-400 mb-4">Pratinjau langsung tidak didukung untuk jenis file ini. Silakan unduh atau buka di tab baru.</p>
                        <flux:button as="a" x-bind:href="docModalSrc" target="_blank" variant="primary" icon="arrow-top-right-on-square">
                            Buka / Unduh Berkas
                        </flux:button>
                    </div>
                </template>
            </div>

            {{-- Modal Footer Tip --}}
            <div class="text-[11px] text-zinc-400 text-center shrink-0">
                Tekan tombol ESC atau klik di luar area untuk menutup modal pratinjau
            </div>
        </div>

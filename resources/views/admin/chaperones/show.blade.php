<x-layouts::app title="Detail Pembina Pendamping — {{ $chaperone->full_name }}">
    <div class="flex h-full w-full flex-1 flex-col gap-6"
         x-data="{
             // State Document Preview Modal
             docModalOpen: false,
             docModalSrc: '',
             docModalTitle: '',
             docModalType: '', // 'image' or 'pdf' or 'other'
             imageZoomScale: 1,

             openDocModal(src, title, ext) {
                 this.docModalSrc = src;
                 this.docModalTitle = title || 'Pratinjau Berkas';
                 const extension = (ext || '').toLowerCase();
                 if (['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(extension)) {
                     this.docModalType = 'image';
                 } else if (extension === 'pdf') {
                     this.docModalType = 'pdf';
                 } else {
                     this.docModalType = 'other';
                 }
                 this.imageZoomScale = 1;
                 this.docModalOpen = true;
             },
             closeDocModal() {
                 this.docModalOpen = false;
                 this.docModalSrc = '';
                 this.imageZoomScale = 1;
             },
             zoomIn() {
                 if (this.imageZoomScale < 3.5) {
                     this.imageZoomScale = +(this.imageZoomScale + 0.25).toFixed(2);
                 }
             },
             zoomOut() {
                 if (this.imageZoomScale > 0.5) {
                     this.imageZoomScale = +(this.imageZoomScale - 0.25).toFixed(2);
                 }
             },
             resetZoom() {
                 this.imageZoomScale = 1;
             },
             toggleZoom() {
                 this.imageZoomScale = this.imageZoomScale === 1 ? 2 : 1;
             }
         }"
         @keydown.escape.window="if (docModalOpen) closeDocModal()">

        <x-official-header />

        {{-- Top Navigation & Action Bar --}}
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div class="flex items-center gap-3">
                <flux:button href="{{ $scoutGroup ? route('admin.scout-groups.show', $scoutGroup->id) : route('admin.chaperones.index') }}" variant="subtle" icon="arrow-left" size="sm">
                </flux:button>
                <div>
                    <div class="flex items-center gap-2">
                        <x-id-badge :value="$chaperone->id_number" />
                        <span class="inline-flex items-center rounded-lg border px-2.5 py-0.5 text-xs font-semibold {{ $chaperone->status?->badgeClasses() }}">
                            {{ $chaperone->status?->label() ?? 'Status Belum Diverifikasi' }}
                        </span>
                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold {{ $chaperone->gender === 'male' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300' }}">
                            {{ $chaperone->gender === 'male' ? 'Bindamping Putra' : 'Bindamping Putri' }}
                        </span>
                    </div>
                    <h1 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100 sm:text-2xl">
                        {{ $chaperone->full_name }}
                    </h1>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                @if($scoutGroup)
                    <flux:button href="{{ route('admin.scout-groups.show', $scoutGroup->id) }}" variant="subtle" size="sm" icon="building-library">
                        Gudep: {{ $scoutGroup->school_name }}
                    </flux:button>
                @endif

                @if($scoutGroup && $scoutGroup->canDownloadDocuments())
                    <flux:button href="{{ route('admin.scout-groups.chaperones.print', [$scoutGroup->id, $chaperone->id]) }}" target="_blank" variant="subtle" size="sm" icon="printer">
                        Cetak Form Biodata
                    </flux:button>
                    <flux:button href="{{ route('digital-cards.chaperone', $chaperone->id) }}" target="_blank" variant="subtle" size="sm" icon="qr-code">
                        Kartu Digital
                    </flux:button>
                @endif
            </div>
        </div>

        {{-- Profil Card Bindamping --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    @if($chaperone->photo_path)
                        <button type="button"
                                @click="openDocModal('{{ asset('storage/' . $chaperone->photo_path) }}', '{{ addslashes($chaperone->full_name) }} (Pas Foto 3x4)', '{{ pathinfo($chaperone->photo_path, PATHINFO_EXTENSION) }}')"
                                class="group relative size-20 rounded-2xl overflow-hidden border-2 border-indigo-500 shadow-sm shrink-0 cursor-zoom-in focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                title="Klik untuk memperbesar foto">
                            <img src="{{ asset('storage/' . $chaperone->photo_path) }}" alt="{{ $chaperone->full_name }}" class="size-full object-cover group-hover:scale-110 transition-transform duration-200">
                            <span class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity">
                                <flux:icon name="magnifying-glass-plus" class="size-6 text-white" />
                            </span>
                        </button>
                    @else
                        <div class="size-20 rounded-2xl bg-zinc-100 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 flex items-center justify-center text-zinc-400 font-bold text-2xl shrink-0">
                            {{ strtoupper(substr($chaperone->full_name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-md bg-zinc-100 px-2 py-0.5 font-mono text-xs font-bold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                {{ $chaperone->gender === 'male' ? 'Pembina Pendamping Putra' : 'Pembina Pendamping Putri' }}
                            </span>
                        </div>
                        <h2 class="mt-1 text-lg sm:text-xl font-bold text-zinc-900 dark:text-zinc-100">
                            {{ $chaperone->full_name }}
                        </h2>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                            Pangkalan: <strong class="text-zinc-700 dark:text-zinc-300">{{ $scoutGroup?->school_name ?? '-' }}</strong> &bull; Kwarran: {{ $scoutGroup?->kwarran_name ?? '-' }}
                        </p>
                    </div>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-3 text-right text-xs dark:border-zinc-800 dark:bg-zinc-800/50">
                    <span class="text-zinc-400 block text-[11px]">Status Pas Foto:</span>
                    @if($chaperone->photo_path)
                        <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">
                            Foto Resmi Terunggah
                        </span>
                    @else
                        <span class="text-sm font-bold text-rose-600 dark:text-rose-400">
                            Belum Ada Pas Foto
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Section 1: Berkas Yang Diunggah (Dapat Ditampilkan dalam Modal) --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                <div>
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Berkas Unggahan Pembina Pendamping</h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Klik tombol pratinjau untuk menampilkan berkas di dalam modal dengan fitur zoom in / out</p>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="flex items-center justify-between p-4 rounded-xl border {{ $chaperone->photo_path ? 'border-emerald-200 bg-emerald-50/20 dark:border-emerald-800/40 dark:bg-emerald-950/10' : 'border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-800/30' }}">
                    <div class="flex items-center gap-3">
                        @if($chaperone->photo_path)
                            <button type="button"
                                    @click="openDocModal('{{ asset('storage/' . $chaperone->photo_path) }}', '{{ addslashes($chaperone->full_name) }} (Pas Foto)', '{{ pathinfo($chaperone->photo_path, PATHINFO_EXTENSION) }}')"
                                    class="group relative size-14 rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700 shrink-0 cursor-zoom-in focus:outline-none"
                                    title="Klik untuk memperbesar">
                                <img src="{{ asset('storage/' . $chaperone->photo_path) }}" alt="{{ $chaperone->full_name }}" class="size-full object-cover group-hover:scale-110 transition-transform duration-200">
                                <span class="absolute inset-0 flex items-center justify-center bg-black/35 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <flux:icon name="magnifying-glass-plus" class="size-4 text-white" />
                                </span>
                            </button>
                            <div>
                                <div class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Pas Foto 3x4 Resmi Pembina</div>
                                <div class="text-[11px] text-zinc-400 font-mono">{{ basename($chaperone->photo_path) }}</div>
                            </div>
                        @else
                            <div class="text-xs text-zinc-500">Pas foto belum diunggah oleh pangkalan.</div>
                        @endif
                    </div>

                    @if($chaperone->photo_path)
                        <div class="flex items-center gap-1.5">
                            <flux:button size="xs" variant="primary" icon="eye"
                                         @click="openDocModal('{{ asset('storage/' . $chaperone->photo_path) }}', '{{ addslashes($chaperone->full_name) }} — Pas Foto 3x4', '{{ pathinfo($chaperone->photo_path, PATHINFO_EXTENSION) }}')">
                                Pratinjau File
                            </flux:button>
                            <flux:button href="{{ asset('storage/' . $chaperone->photo_path) }}" target="_blank" size="xs" variant="subtle" icon="arrow-top-right-on-square">
                            </flux:button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Section 2: Biodata Pribadi & Kontak --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 mb-3">Biodata Pribadi &amp; Kontak</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs bg-zinc-50 dark:bg-zinc-800/40 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800">
                <div>
                    <span class="text-zinc-400 block text-[11px]">Nomor Induk Kependudukan (NIK):</span>
                    <span class="font-semibold text-zinc-800 dark:text-zinc-200 font-mono">{{ $chaperone->national_identity_number ?: '-' }}</span>
                </div>
                <div>
                    <span class="text-zinc-400 block text-[11px]">NTA Pramuka:</span>
                    <span class="font-semibold text-zinc-800 dark:text-zinc-200 font-mono">{{ $chaperone->scout_id ?: '-' }}</span>
                </div>
                <div>
                    <span class="text-zinc-400 block text-[11px]">Tempat, Tanggal Lahir:</span>
                    <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $chaperone->birth_place ?: '-' }}, {{ $chaperone->birth_date?->translatedFormat('d F Y') }}</span>
                </div>
                <div>
                    <span class="text-zinc-400 block text-[11px]">Agama &amp; Gol. Darah:</span>
                    <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $chaperone->religion ?: '-' }} ({{ $chaperone->blood_type ?: 'Gol. -' }})</span>
                </div>
                <div>
                    <span class="text-zinc-400 block text-[11px]">No. Telepon / WhatsApp:</span>
                    <span class="font-semibold text-zinc-800 dark:text-zinc-200 font-mono">{{ $chaperone->phone_number ?: '-' }}</span>
                </div>
                <div>
                    <span class="text-zinc-400 block text-[11px]">Telepon Rumah:</span>
                    <span class="font-semibold text-zinc-800 dark:text-zinc-200 font-mono">{{ $chaperone->home_phone ?: '-' }}</span>
                </div>
                <div class="col-span-2">
                    <span class="text-zinc-400 block text-[11px]">Alamat Lengkap:</span>
                    <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $chaperone->home_address ?: '-' }}, {{ $chaperone->city ?: '-' }} {{ $chaperone->postal_code ?: '' }}</span>
                </div>
            </div>
        </div>

        {{-- Section 3: Riwayat Pendidikan Formal --}}
        @php
            $chaperoneEdu = $chaperone->formal_education ?: \App\Models\Chaperone::defaultFormalEducation();
            $chaperoneExp = $chaperone->scout_experiences ?: \App\Models\Chaperone::defaultScoutExperiences();
            $chaperoneTrain = $chaperone->scout_trainings ?: \App\Models\Chaperone::defaultScoutTrainings();
        @endphp

        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 mb-3">Riwayat Pendidikan Formal</h3>
            <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-800">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50 text-zinc-500 dark:bg-zinc-800/60 dark:text-zinc-400">
                        <tr>
                            <th class="px-3 py-2 font-semibold">Tingkat</th>
                            <th class="px-3 py-2 font-semibold">Nama Sekolah / Instansi</th>
                            <th class="px-3 py-2 font-semibold">Kota</th>
                            <th class="px-3 py-2 font-semibold">Tahun Lulus</th>
                            <th class="px-3 py-2 font-semibold">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @foreach($chaperoneEdu as $edu)
                            <tr>
                                <td class="px-3 py-1.5 font-bold">{{ $edu['level'] ?? '-' }}</td>
                                <td class="px-3 py-1.5">{{ $edu['school_name'] ?? '-' }}</td>
                                <td class="px-3 py-1.5">{{ $edu['city'] ?? '-' }}</td>
                                <td class="px-3 py-1.5 font-mono">{{ $edu['graduation_year'] ?? '-' }}</td>
                                <td class="px-3 py-1.5 text-zinc-500">{{ $edu['notes'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Section 4: Riwayat Golongan & Kursus Kepramukaan --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 mb-3">Riwayat Golongan Pramuka</h3>
                <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-800">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-zinc-50 text-zinc-500 dark:bg-zinc-800/60 dark:text-zinc-400">
                            <tr>
                                <th class="px-3 py-1.5 font-semibold">Golongan</th>
                                <th class="px-3 py-1.5 font-semibold">Tahun</th>
                                <th class="px-3 py-1.5 font-semibold">No. Gudep</th>
                                <th class="px-3 py-1.5 font-semibold">Pangkalan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @foreach($chaperoneExp as $exp)
                                <tr>
                                    <td class="px-3 py-1.5 font-bold">{{ $exp['level'] ?? '-' }}</td>
                                    <td class="px-3 py-1.5 font-mono">{{ $exp['year'] ?? '-' }}</td>
                                    <td class="px-3 py-1.5 font-mono">{{ $exp['group_number'] ?? '-' }}</td>
                                    <td class="px-3 py-1.5">{{ $exp['base'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 mb-3">Riwayat Kursus Kepramukaan</h3>
                <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-800">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-zinc-50 text-zinc-500 dark:bg-zinc-800/60 dark:text-zinc-400">
                            <tr>
                                <th class="px-3 py-1.5 font-semibold">Kursus</th>
                                <th class="px-3 py-1.5 font-semibold">Tahun</th>
                                <th class="px-3 py-1.5 font-semibold">Tempat</th>
                                <th class="px-3 py-1.5 font-semibold">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @foreach($chaperoneTrain as $tr)
                                <tr>
                                    <td class="px-3 py-1.5 font-bold">{{ $tr['course'] ?? '-' }}</td>
                                    <td class="px-3 py-1.5 font-mono">{{ $tr['year'] ?? '-' }}</td>
                                    <td class="px-3 py-1.5">{{ $tr['location'] ?? '-' }}</td>
                                    <td class="px-3 py-1.5 text-zinc-500">{{ $tr['notes'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

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

                    <flux:button size="xs" variant="filled" icon="x-mark" @click="closeDocModal()">
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
    </div>
</x-layouts::app>

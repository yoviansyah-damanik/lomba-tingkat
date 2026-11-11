<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1
                class="text-2xl font-black tracking-tight text-zinc-900 dark:text-zinc-100 sm:text-3xl flex items-center gap-2.5">
                <flux:icon name="academic-cap" class="size-8 text-amber-500" />
                <span>Pengaturan Template Sertifikat</span>
            </h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Atur penempatan, ukuran, dan warna teks Nama Lengkap, NTA,
                Gugusdepan/Pangkalan, dan Peran secara visual pada desain vektor Piagam Penghargaan LT-III (A4 Landscape).
            </p>
        </div>

        <div class="flex items-center gap-2">
            <flux:button href="{{ route('admin.certificates.preview', ['role' => $activeRole]) }}" target="_blank"
                icon="printer" variant="outline">
                Lihat Contoh PDF
            </flux:button>
        </div>
    </div>

    {{-- Master Toggle Penerbitan Sertifikat (ON / OFF) --}}
    <div
        class="rounded-2xl border p-5 shadow-sm transition-all duration-200 {{ $is_certificate_published ? 'border-emerald-500/40 bg-emerald-50/50 dark:border-emerald-500/30 dark:bg-emerald-950/20' : 'border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900' }}">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-3.5">
                <div
                    class="flex size-11 shrink-0 items-center justify-center rounded-xl {{ $is_certificate_published ? 'bg-emerald-500 text-white' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400' }}">
                    <flux:icon :name="$is_certificate_published ? 'check-badge' : 'lock-closed'" class="size-6" />
                </div>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Status Penerbitan E-Sertifikat
                            Kegiatan</h2>
                        <span
                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold {{ $is_certificate_published ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' }}">
                            {{ $is_certificate_published ? 'DIBUKA (ON)' : 'DITUTUP (OFF)' }}
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-400 max-w-3xl leading-relaxed">
                        @if ($is_certificate_published)
                            Penerbitan sertifikat sedang <span
                                class="font-bold text-emerald-700 dark:text-emerald-400">AKTIF</span>. Pemberitahuan
                            resmi ditampilkan di halaman depan, dan seluruh akun (Peserta, Bindamping, Panitia, Dewan
                            Juri, & Gugus Depan) dapat mengunduh e-sertifikat melalui dasbor masing-masing.
                        @else
                            Penerbitan sertifikat sedang <span
                                class="font-bold text-zinc-700 dark:text-zinc-300">NONAKTIF</span>. Pengguna belum dapat
                            mengunduh e-sertifikat resmi dan pemberitahuan di halaman depan disembunyikan.
                        @endif
                    </p>
                </div>
            </div>

            <div class="shrink-0">
                <flux:button wire:click="togglePublish" :variant="$is_certificate_published ? 'danger' : 'primary'"
                    :icon="$is_certificate_published ? 'lock-closed' : 'lock-open'">
                    {{ $is_certificate_published ? 'Tutup Penerbitan' : 'Buka Penerbitan (ON)' }}
                </flux:button>
            </div>
        </div>
    </div>

    {{-- Tabs Pemilihan Peran --}}
    <div class="flex flex-wrap items-center gap-2 border-b border-zinc-200 pb-3 dark:border-zinc-800">
        @foreach ($roles as $key => $label)
            <button type="button" wire:click="setRole('{{ $key }}')"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-bold transition-all duration-150 {{ $activeRole === $key ? 'bg-amber-500 text-white shadow-sm' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700' }}">
                <span>{{ $label }}</span>
                @if ($activeRole === $key)
                    <span class="size-1.5 rounded-full bg-white"></span>
                @endif
            </button>
        @endforeach
    </div>

    {{-- Form Konfigurasi Peran Aktif --}}
    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- Kolom Kiri: Interactive Visual Canvas (Aspect Ratio 297:210) --}}
            <div class="lg:col-span-7 xl:col-span-8 space-y-4">
                <div
                    class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-5">
                    <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                        <div class="flex items-center gap-2">
                            <span
                                class="inline-flex items-center rounded-lg bg-amber-500/10 px-2 py-0.5 text-xs font-bold text-amber-700 dark:bg-amber-400/10 dark:text-amber-400">Live
                                Canvas</span>
                            <span class="text-xs text-zinc-500 dark:text-zinc-400">A4 Landscape (297 x 210 mm)</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                            <span class="size-2 rounded-full bg-emerald-500"></span>
                            <span>Elemen teks dapat digeser langsung di kanvas</span>
                        </div>
                    </div>

                    {{-- Canvas dengan Alpine.js Drag Logic --}}
                    <div x-data="{
                        nameX: @entangle('name_x'),
                        nameY: @entangle('name_y'),
                        nameSize: @entangle('name_size'),
                        nameColor: @entangle('name_color'),
                        nameAlign: @entangle('name_align'),
                        nameBold: @entangle('name_bold'),
                    
                        scoutIdX: @entangle('scout_id_x'),
                        scoutIdY: @entangle('scout_id_y'),
                        scoutIdSize: @entangle('scout_id_size'),
                        scoutIdColor: @entangle('scout_id_color'),
                        scoutIdAlign: @entangle('scout_id_align'),
                        scoutIdBold: @entangle('scout_id_bold'),
                    
                        instX: @entangle('inst_x'),
                        instY: @entangle('inst_y'),
                        instSize: @entangle('inst_size'),
                        instColor: @entangle('inst_color'),
                        instAlign: @entangle('inst_align'),
                        instBold: @entangle('inst_bold'),
                    
                        roleX: @entangle('role_x'),
                        roleY: @entangle('role_y'),
                        roleSize: @entangle('role_size'),
                        roleColor: @entangle('role_color'),
                        roleAlign: @entangle('role_align'),
                        roleBold: @entangle('role_bold'),
                    
                        certNoX: @entangle('cert_no_x'),
                        certNoY: @entangle('cert_no_y'),
                        certNoSize: @entangle('cert_no_size'),
                        certNoColor: @entangle('cert_no_color'),
                        certNoAlign: @entangle('cert_no_align'),
                        certNoBold: @entangle('cert_no_bold'),
                    
                        dragging: null,
                    
                        startDrag(target, event) {
                            this.dragging = target;
                            event.preventDefault();
                        },
                        onMouseMove(event) {
                            if (!this.dragging) return;
                            const rect = this.$refs.canvas.getBoundingClientRect();
                            let clientX = event.clientX || (event.touches && event.touches[0].clientX);
                            let clientY = event.clientY || (event.touches && event.touches[0].clientY);
                            if (!clientX || !clientY) return;
                    
                            let x = ((clientX - rect.left) / rect.width) * 100;
                            let y = ((clientY - rect.top) / rect.height) * 100;
                    
                            x = Math.max(0, Math.min(100, Math.round(x * 10) / 10));
                            y = Math.max(0, Math.min(100, Math.round(y * 10) / 10));
                    
                            if (this.dragging === 'name') {
                                this.nameX = x;
                                this.nameY = y;
                            } else if (this.dragging === 'scout_id') {
                                this.scoutIdX = x;
                                this.scoutIdY = y;
                            } else if (this.dragging === 'inst') {
                                this.instX = x;
                                this.instY = y;
                            } else if (this.dragging === 'role') {
                                this.roleX = x;
                                this.roleY = y;
                            } else if (this.dragging === 'cert_no') {
                                this.certNoX = x;
                                this.certNoY = y;
                            }
                        },
                        stopDrag() {
                            this.dragging = null;
                        }
                    }" @mousemove.window="onMouseMove($event)" @mouseup.window="stopDrag()"
                        @touchmove.window="onMouseMove($event)" @touchend.window="stopDrag()"
                        class="mt-3 relative select-none">
                        <div x-ref="canvas"
                            class="relative w-full rounded-xl overflow-hidden border-2 border-zinc-200 dark:border-zinc-700 shadow-md bg-white dark:bg-zinc-950"
                            style="aspect-ratio: 297 / 210;">
                            {{-- Desain Vektor Buatan Sendiri LT-III (Tanpa Background PNG/JPG) --}}
                            <div class="absolute inset-0 pointer-events-none [&>svg]:w-full [&>svg]:h-full">
                                @include('certificates.vector-artwork')
                            </div>

                            <img src="{{ asset('logo.png') }}" alt="Watermark LT-III"
                                class="absolute top-[29%] left-[38.5%] w-[23%] opacity-5 pointer-events-none" />
                            <img src="{{ asset('logo.png') }}" alt="Logo LT-III"
                                class="absolute top-[9.8%] left-[9.8%] w-[5.7%] pointer-events-none" />

                            <div class="absolute inset-0 pointer-events-none text-center">
                                <div class="absolute top-[7.8%] left-[18%] w-[64%] space-y-0.5">
                                    <p class="text-[8px] sm:text-[11px] font-bold uppercase tracking-widest text-amber-900">
                                        Gerakan Pramuka Kwartir Cabang Kota Padangsidimpuan
                                    </p>
                                    <p class="text-[7px] sm:text-[9.5px] font-bold uppercase tracking-wider text-amber-800">
                                        Lomba Tingkat Tiga (LT-III) Regu Penggalang Tahun
                                        {{ $event?->start_date ? $event->start_date->year : 2026 }}
                                    </p>
                                    <h3 class="text-xs sm:text-xl font-black uppercase tracking-widest text-[#260c36] pt-0.5">
                                        Piagam Penghargaan
                                    </h3>
                                </div>

                                <p class="absolute top-[35.8%] left-[15%] w-[70%] text-[8px] sm:text-[10.5px] italic text-zinc-600">
                                    Diberikan secara resmi dan sah kepada:
                                </p>

                                <div class="absolute top-[69.5%] left-[12%] w-[76%] text-[7px] sm:text-[8.8px] leading-snug text-zinc-700">
                                    <p>
                                        Atas partisipasi aktif, dedikasi, dan prestasi dalam kegiatan
                                        <span class="font-bold">{{ $event?->name ?: 'Lomba Tingkat Tiga (LT-III) Regu Pramuka Penggalang' }}</span>
                                        Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan.
                                    </p>
                                    <p class="font-bold text-amber-900 mt-0.5">
                                        {{ $event?->location ?: 'Lapangan Pulo Baok, Kota Padangsidimpuan' }}
                                    </p>
                                </div>

                                <div class="absolute top-[78.2%] left-[8%] w-[84%] flex justify-between items-start text-[7px] sm:text-[8.5px] text-zinc-700">
                                    <div class="w-[41%] text-center">
                                        <p class="text-zinc-500 text-[6.5px] sm:text-[7.5px]">Mengetahui,</p>
                                        <p class="font-bold">Ketua Panitia Pelaksana,</p>
                                        <p class="mt-4 sm:mt-6 font-bold underline text-zinc-900">
                                            {{ $event?->committee_chair_name ?: 'Kak Fahri Siregar, S.Pd' }}
                                        </p>
                                        <p class="text-[6.5px] sm:text-[7.5px] text-zinc-500">
                                            NTA: {{ $event?->committee_chair_nta ?: '-' }}
                                        </p>
                                    </div>
                                    <div class="w-[18%]"></div>
                                    <div class="w-[41%] text-center">
                                        <p class="text-zinc-500 text-[6.5px] sm:text-[7.5px]">
                                            Padangsidimpuan,
                                            {{ $event?->end_date ? $event->end_date->translatedFormat('d F Y') : '17 November 2026' }}
                                        </p>
                                        <p class="font-bold">Ketua Kwartir Cabang Kota Padangsidimpuan,</p>
                                        <p class="mt-4 sm:mt-6 font-bold underline text-zinc-900">
                                            {{ $event?->kwarcab_chair_name ?: 'Ketua Kwarcab Padangsidimpuan' }}
                                        </p>
                                        <p class="text-[6.5px] sm:text-[7.5px] text-zinc-500">
                                            NTA: {{ $event?->kwarcab_chair_nta ?: '-' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Draggable Sample: Nomor Sertifikat --}}
                            <div @mousedown="startDrag('cert_no', $event)" @touchstart="startDrag('cert_no', $event)"
                                class="absolute cursor-move px-2 py-0.5 rounded border border-dashed border-zinc-400 bg-white/70 backdrop-blur-xs text-center z-10"
                                :style="`top: ${certNoY}%; left: ${certNoX}%; transform: translate(-50%, -50%); color: ${certNoColor}; font-weight: ${certNoBold ? 'bold' : 'normal'}; font-size: ${certNoSize * 0.45}px;`">
                                <span>{{ $sample['certificate_number'] }}</span>
                            </div>

                            {{-- Draggable Sample: Nama Lengkap --}}
                            <div @mousedown="startDrag('name', $event)" @touchstart="startDrag('name', $event)"
                                class="absolute cursor-move px-2 py-0.5 rounded border border-dashed border-amber-500 bg-amber-50/70 backdrop-blur-xs text-center z-20"
                                :style="`top: ${nameY}%; left: ${nameX}%; transform: translate(-50%, -50%); color: ${nameColor}; font-weight: ${nameBold ? 'bold' : 'normal'}; font-size: ${nameSize * 0.45}px;`">
                                <span>{{ $sample['full_name'] }}</span>
                            </div>

                            {{-- Draggable Sample: NTA --}}
                            <div @mousedown="startDrag('scout_id', $event)" @touchstart="startDrag('scout_id', $event)"
                                class="absolute cursor-move px-2 py-0.5 rounded border border-dashed border-zinc-400 bg-white/70 backdrop-blur-xs text-center z-15"
                                :style="`top: ${scoutIdY}%; left: ${scoutIdX}%; transform: translate(-50%, -50%); color: ${scoutIdColor}; font-weight: ${scoutIdBold ? 'bold' : 'normal'}; font-size: ${scoutIdSize * 0.45}px;`">
                                <span>{{ $sample['scout_id'] }}</span>
                            </div>

                            {{-- Draggable Sample: Gugusdepan / Pangkalan --}}
                            <div @mousedown="startDrag('inst', $event)" @touchstart="startDrag('inst', $event)"
                                class="absolute cursor-move px-2 py-0.5 rounded border border-dashed border-zinc-400 bg-white/70 backdrop-blur-xs text-center z-15"
                                :style="`top: ${instY}%; left: ${instX}%; transform: translate(-50%, -50%); color: ${instColor}; font-weight: ${instBold ? 'bold' : 'normal'}; font-size: ${instSize * 0.45}px;`">
                                <span>{{ $sample['institution'] }}</span>
                            </div>

                            {{-- Draggable Sample: Sebagai (Peran) --}}
                            <div @mousedown="startDrag('role', $event)" @touchstart="startDrag('role', $event)"
                                class="absolute cursor-move px-2 py-0.5 rounded border border-dashed border-amber-600 bg-amber-100/70 backdrop-blur-xs text-center z-20"
                                :style="`top: ${roleY}%; left: ${roleX}%; transform: translate(-50%, -50%); color: ${roleColor}; font-weight: ${roleBold ? 'bold' : 'normal'}; font-size: ${roleSize * 0.45}px;`">
                                <span>{{ $sample['role_label'] }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Opsi Cepat Tata Letak --}}
                    <div class="mt-4 pt-4 border-t border-zinc-100 dark:border-zinc-800 flex flex-wrap items-center justify-between gap-2">
                        <flux:button type="button" wire:click="applyToAllRoles" variant="subtle" size="sm"
                            icon="document-duplicate">
                            Terapkan Tata Letak Ini ke Semua Peran
                        </flux:button>

                        <flux:button type="submit" variant="primary" icon="check">
                            Simpan Pengaturan {{ $roles[$activeRole] }}
                        </flux:button>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Fine-tuning Input Controls --}}
            <div class="lg:col-span-5 xl:col-span-4 space-y-4">

                {{-- 1. Nama Lengkap --}}
                <div
                    class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 space-y-3">
                    <div class="flex items-center justify-between border-b border-zinc-100 pb-2 dark:border-zinc-800">
                        <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-1.5">
                            <flux:icon name="user" class="size-4 text-amber-500" />
                            Nama Lengkap
                        </span>
                        <label
                            class="flex items-center gap-1.5 text-xs text-zinc-600 dark:text-zinc-400 cursor-pointer">
                            <input type="checkbox" wire:model.live="name_bold"
                                class="rounded border-zinc-300 text-amber-600 focus:ring-amber-500">
                            <span>Tebal</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5 text-xs">
                        <div>
                            <label class="text-[11px] text-zinc-500">Posisi X (%)</label>
                            <input type="number" step="0.5" min="0" max="100"
                                wire:model.live.debounce.150ms="name_x"
                                class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-xs dark:border-zinc-700 dark:bg-zinc-800">
                        </div>
                        <div>
                            <label class="text-[11px] text-zinc-500">Posisi Y (%)</label>
                            <input type="number" step="0.5" min="0" max="100"
                                wire:model.live.debounce.150ms="name_y"
                                class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-xs dark:border-zinc-700 dark:bg-zinc-800">
                        </div>
                        <div>
                            <label class="text-[11px] text-zinc-500">Ukuran Font (pt)</label>
                            <input type="number" step="1" min="10" max="60"
                                wire:model.live.debounce.150ms="name_size"
                                class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-xs dark:border-zinc-700 dark:bg-zinc-800">
                        </div>
                        <div>
                            <label class="text-[11px] text-zinc-500">Warna Font</label>
                            <div class="mt-1 flex items-center gap-1.5">
                                <input type="color" wire:model.live="name_color"
                                    class="size-7 rounded cursor-pointer border-0 p-0">
                                <input type="text" wire:model.live.debounce.200ms="name_color"
                                    class="w-full rounded-lg border border-zinc-300 bg-white px-2 py-1 text-xs font-mono dark:border-zinc-700 dark:bg-zinc-800">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. NTA (Nomor Tanda Anggota) --}}
                <div
                    class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 space-y-3">
                    <div class="flex items-center justify-between border-b border-zinc-100 pb-2 dark:border-zinc-800">
                        <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-1.5">
                            <flux:icon name="identification" class="size-4 text-blue-500" />
                            NTA (Nomor Tanda Anggota)
                        </span>
                        <label
                            class="flex items-center gap-1.5 text-xs text-zinc-600 dark:text-zinc-400 cursor-pointer">
                            <input type="checkbox" wire:model.live="scout_id_bold"
                                class="rounded border-zinc-300 text-amber-600 focus:ring-amber-500">
                            <span>Tebal</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5 text-xs">
                        <div>
                            <label class="text-[11px] text-zinc-500">Posisi X (%)</label>
                            <input type="number" step="0.5" min="0" max="100"
                                wire:model.live.debounce.150ms="scout_id_x"
                                class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-xs dark:border-zinc-700 dark:bg-zinc-800">
                        </div>
                        <div>
                            <label class="text-[11px] text-zinc-500">Posisi Y (%)</label>
                            <input type="number" step="0.5" min="0" max="100"
                                wire:model.live.debounce.150ms="scout_id_y"
                                class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-xs dark:border-zinc-700 dark:bg-zinc-800">
                        </div>
                        <div>
                            <label class="text-[11px] text-zinc-500">Ukuran Font (pt)</label>
                            <input type="number" step="1" min="8" max="40"
                                wire:model.live.debounce.150ms="scout_id_size"
                                class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-xs dark:border-zinc-700 dark:bg-zinc-800">
                        </div>
                        <div>
                            <label class="text-[11px] text-zinc-500">Warna Font</label>
                            <div class="mt-1 flex items-center gap-1.5">
                                <input type="color" wire:model.live="scout_id_color"
                                    class="size-7 rounded cursor-pointer border-0 p-0">
                                <input type="text" wire:model.live.debounce.200ms="scout_id_color"
                                    class="w-full rounded-lg border border-zinc-300 bg-white px-2 py-1 text-xs font-mono dark:border-zinc-700 dark:bg-zinc-800">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. Gugusdepan / Pangkalan --}}
                <div
                    class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 space-y-3">
                    <div class="flex items-center justify-between border-b border-zinc-100 pb-2 dark:border-zinc-800">
                        <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-1.5">
                            <flux:icon name="building-library" class="size-4 text-emerald-500" />
                            Gugusdepan / Pangkalan
                        </span>
                        <label
                            class="flex items-center gap-1.5 text-xs text-zinc-600 dark:text-zinc-400 cursor-pointer">
                            <input type="checkbox" wire:model.live="inst_bold"
                                class="rounded border-zinc-300 text-amber-600 focus:ring-amber-500">
                            <span>Tebal</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5 text-xs">
                        <div>
                            <label class="text-[11px] text-zinc-500">Posisi X (%)</label>
                            <input type="number" step="0.5" min="0" max="100"
                                wire:model.live.debounce.150ms="inst_x"
                                class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-xs dark:border-zinc-700 dark:bg-zinc-800">
                        </div>
                        <div>
                            <label class="text-[11px] text-zinc-500">Posisi Y (%)</label>
                            <input type="number" step="0.5" min="0" max="100"
                                wire:model.live.debounce.150ms="inst_y"
                                class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-xs dark:border-zinc-700 dark:bg-zinc-800">
                        </div>
                        <div>
                            <label class="text-[11px] text-zinc-500">Ukuran Font (pt)</label>
                            <input type="number" step="1" min="8" max="40"
                                wire:model.live.debounce.150ms="inst_size"
                                class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-xs dark:border-zinc-700 dark:bg-zinc-800">
                        </div>
                        <div>
                            <label class="text-[11px] text-zinc-500">Warna Font</label>
                            <div class="mt-1 flex items-center gap-1.5">
                                <input type="color" wire:model.live="inst_color"
                                    class="size-7 rounded cursor-pointer border-0 p-0">
                                <input type="text" wire:model.live.debounce.200ms="inst_color"
                                    class="w-full rounded-lg border border-zinc-300 bg-white px-2 py-1 text-xs font-mono dark:border-zinc-700 dark:bg-zinc-800">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 4. Sebagai / Peran --}}
                <div
                    class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 space-y-3">
                    <div class="flex items-center justify-between border-b border-zinc-100 pb-2 dark:border-zinc-800">
                        <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-1.5">
                            <flux:icon name="tag" class="size-4 text-purple-500" />
                            Sebagai (Peran / Predikat)
                        </span>
                        <label
                            class="flex items-center gap-1.5 text-xs text-zinc-600 dark:text-zinc-400 cursor-pointer">
                            <input type="checkbox" wire:model.live="role_bold"
                                class="rounded border-zinc-300 text-amber-600 focus:ring-amber-500">
                            <span>Tebal</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5 text-xs">
                        <div>
                            <label class="text-[11px] text-zinc-500">Posisi X (%)</label>
                            <input type="number" step="0.5" min="0" max="100"
                                wire:model.live.debounce.150ms="role_x"
                                class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-xs dark:border-zinc-700 dark:bg-zinc-800">
                        </div>
                        <div>
                            <label class="text-[11px] text-zinc-500">Posisi Y (%)</label>
                            <input type="number" step="0.5" min="0" max="100"
                                wire:model.live.debounce.150ms="role_y"
                                class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-xs dark:border-zinc-700 dark:bg-zinc-800">
                        </div>
                        <div>
                            <label class="text-[11px] text-zinc-500">Ukuran Font (pt)</label>
                            <input type="number" step="1" min="8" max="40"
                                wire:model.live.debounce.150ms="role_size"
                                class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-xs dark:border-zinc-700 dark:bg-zinc-800">
                        </div>
                        <div>
                            <label class="text-[11px] text-zinc-500">Warna Font</label>
                            <div class="mt-1 flex items-center gap-1.5">
                                <input type="color" wire:model.live="role_color"
                                    class="size-7 rounded cursor-pointer border-0 p-0">
                                <input type="text" wire:model.live.debounce.200ms="role_color"
                                    class="w-full rounded-lg border border-zinc-300 bg-white px-2 py-1 text-xs font-mono dark:border-zinc-700 dark:bg-zinc-800">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 5. Nomor Sertifikat --}}
                <div
                    class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 space-y-3">
                    <div class="flex items-center justify-between border-b border-zinc-100 pb-2 dark:border-zinc-800">
                        <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-1.5">
                            <flux:icon name="hashtag" class="size-4 text-zinc-500" />
                            Nomor Sertifikat
                        </span>
                        <label class="flex items-center gap-1.5 text-xs text-zinc-600 dark:text-zinc-400 cursor-pointer">
                            <input type="checkbox" wire:model.live="cert_no_bold"
                                class="rounded border-zinc-300 text-amber-600 focus:ring-amber-500">
                            <span>Tebal</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5 text-xs">
                        <div>
                            <label class="text-[11px] text-zinc-500">Posisi X (%)</label>
                            <input type="number" step="0.5" min="0" max="100"
                                wire:model.live.debounce.150ms="cert_no_x"
                                class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-xs dark:border-zinc-700 dark:bg-zinc-800">
                        </div>
                        <div>
                            <label class="text-[11px] text-zinc-500">Posisi Y (%)</label>
                            <input type="number" step="0.5" min="0" max="100"
                                wire:model.live.debounce.150ms="cert_no_y"
                                class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-xs dark:border-zinc-700 dark:bg-zinc-800">
                        </div>
                        <div>
                            <label class="text-[11px] text-zinc-500">Ukuran Font (pt)</label>
                            <input type="number" step="1" min="8" max="40"
                                wire:model.live.debounce.150ms="cert_no_size"
                                class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-xs dark:border-zinc-700 dark:bg-zinc-800">
                        </div>
                        <div>
                            <label class="text-[11px] text-zinc-500">Warna Font</label>
                            <div class="mt-1 flex items-center gap-1.5">
                                <input type="color" wire:model.live="cert_no_color"
                                    class="size-7 rounded cursor-pointer border-0 p-0">
                                <input type="text" wire:model.live.debounce.200ms="cert_no_color"
                                    class="w-full rounded-lg border border-zinc-300 bg-white px-2 py-1 text-xs font-mono dark:border-zinc-700 dark:bg-zinc-800">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Simpan Cepat --}}
                <div class="pt-2">
                    <flux:button type="submit" variant="primary" icon="check" class="w-full">
                        Simpan Pengaturan {{ $roles[$activeRole] }}
                    </flux:button>
                </div>
            </div>
        </div>
    </form>
</div>

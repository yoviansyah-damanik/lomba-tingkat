<div class="space-y-6">
    {{-- Header Section --}}
    <x-heading-section
        title="Konfigurasi Sistem & Master Kegiatan"
        description="Atur identitas resmi kegiatan, jadwal pelaksanaan, batas waktu pendaftaran kontingen, dan kanal narahubung resmi."
    >
        <x-slot:breadcrumbs>
            <a href="{{ route('dashboard') }}" wire:navigate class="hover:underline">Dashboard</a>
            <span class="mx-2">&rsaquo;</span>
            <span class="text-zinc-800 dark:text-zinc-200">Konfigurasi Sistem</span>
        </x-slot:breadcrumbs>
    </x-heading-section>

    {{-- Notification Alert & Toast Bridge --}}
    <x-alert />

    <form wire:submit="saveSettings" class="space-y-6">
        {{-- Card 1: Identitas Resmi Kegiatan --}}
        <div class="rounded-2xl border border-zinc-200/80 bg-white p-6 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 space-y-4">
            <div class="border-b border-zinc-100 dark:border-zinc-800 pb-3 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider">
                        Identitas Resmi Kegiatan
                    </h3>
                    <p class="text-xs text-zinc-400">
                        Informasi yang akan tampil pada kop dokumen resmi, halaman publik, dan kartu tanda peserta
                    </p>
                </div>
                <span class="rounded-md bg-amber-100 px-2.5 py-1 text-[11px] font-bold text-amber-900 dark:bg-amber-950/60 dark:text-amber-300">
                    Master Data
                </span>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                        Nama Resmi Kegiatan:
                    </label>
                    <input
                        type="text"
                        wire:model="title"
                        placeholder="Contoh: Lomba Tingkat III Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan"
                        class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 font-semibold"
                    />
                    @error('title') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                            Tema / Slogan Kegiatan:
                        </label>
                        <input
                            type="text"
                            wire:model="theme"
                            placeholder="Contoh: Tangguh, Cerdas, Berkarakter, dan Berprestasi"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                        />
                        @error('theme') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                            Lokasi Bumi Perkemahan:
                        </label>
                        <input
                            type="text"
                            wire:model="location"
                            placeholder="Contoh: Bumi Perkemahan Tor Simarsayang, Kota Padangsidimpuan"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                        />
                        @error('location') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                        Deskripsi Singkat / Sambutan:
                    </label>
                    <textarea
                        wire:model="description"
                        rows="3"
                        placeholder="Deskripsi pengantar kegiatan untuk halaman beranda publik..."
                        class="w-full rounded-xl border border-zinc-300 bg-white p-3 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                    ></textarea>
                </div>
            </div>
        </div>

        {{-- Card 2: Jadwal Pelaksanaan & Batas Waktu Registrasi --}}
        <div class="rounded-2xl border border-zinc-200/80 bg-white p-6 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 space-y-4">
            <div class="border-b border-zinc-100 dark:border-zinc-800 pb-3 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider">
                        Jadwal Kegiatan & Periode Pendaftaran
                    </h3>
                    <p class="text-xs text-zinc-400">
                        Pengaturan rentang waktu perkemahan dan pembatasan akses pendaftaran kontingen
                    </p>
                </div>
                <span class="rounded-md bg-blue-100 px-2.5 py-1 text-[11px] font-bold text-blue-900 dark:bg-blue-950/60 dark:text-blue-300">
                    WIB (Asia/Jakarta)
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                        Tanggal Mulai Kegiatan Perkemahan:
                    </label>
                    <input
                        type="date"
                        wire:model="start_date"
                        class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                    />
                    @error('start_date') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                        Tanggal Selesai Kegiatan:
                    </label>
                    <input
                        type="date"
                        wire:model="end_date"
                        class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                    />
                    @error('end_date') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                        Waktu Pembukaan Pendaftaran Online:
                    </label>
                    <input
                        type="datetime-local"
                        wire:model="registration_start_at"
                        class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                    />
                    @error('registration_start_at') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                        Batas Akhir (Deadline) Pendaftaran Kontingen:
                    </label>
                    <input
                        type="datetime-local"
                        wire:model="registration_end_at"
                        class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                    />
                    @error('registration_end_at') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="pt-2 flex items-center justify-between border-t border-zinc-100 dark:border-zinc-800">
                <div>
                    <strong class="text-xs text-zinc-900 dark:text-zinc-100 block">Status Gerbang Registrasi Online</strong>
                    <span class="text-[11px] text-zinc-400">Jika ditutup, formulir registrasi publik tidak akan menerima pendaftaran pangkalan baru.</span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" wire:model="is_registration_open" class="sr-only peer" />
                    <div class="w-11 h-6 bg-zinc-200 peer-focus:outline-hidden rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-zinc-600 peer-checked:bg-amber-600"></div>
                </label>
            </div>
        </div>

        {{-- Card 3: Kontak Narahubung & Status Sistem --}}
        <div class="rounded-2xl border border-zinc-200/80 bg-white p-6 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 space-y-4">
            <div class="border-b border-zinc-100 dark:border-zinc-800 pb-3">
                <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider">
                    Kontak Narahubung & Status Kegiatan
                </h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                        Email Resmi Sekretariat:
                    </label>
                    <input
                        type="email"
                        wire:model="contact_email"
                        placeholder="sekretariat@lt3padangsidimpuan.id"
                        class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                    />
                    @error('contact_email') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                        Nomor WhatsApp Narahubung:
                    </label>
                    <input
                        type="text"
                        wire:model="contact_phone"
                        placeholder="Contoh: 081234567890"
                        class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                    />
                    @error('contact_phone') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="pt-2 flex items-center justify-between border-t border-zinc-100 dark:border-zinc-800">
                <div>
                    <strong class="text-xs text-zinc-900 dark:text-zinc-100 block">Status Aktif Kegiatan</strong>
                    <span class="text-[11px] text-zinc-400">Menandakan kegiatan LT III aktif berjalan di sistem informasi.</span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" wire:model="is_active" class="sr-only peer" />
                    <div class="w-11 h-6 bg-zinc-200 peer-focus:outline-hidden rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-zinc-600 peer-checked:bg-emerald-600"></div>
                </label>
            </div>
        </div>

        {{-- Card 4: Privasi Identitas & Blind Mode --}}
        <div class="rounded-2xl border border-zinc-200/80 bg-white p-6 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 space-y-4">
            <div class="border-b border-zinc-100 dark:border-zinc-800 pb-3 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider">
                        Privasi Identitas & Blind Scoring / Klasemen
                    </h3>
                    <p class="text-xs text-zinc-400">
                        Pengaturan penyembunyian nama regu dan pangkalan gudep dari publik, panitia, dan dewan juri
                    </p>
                </div>
                <span class="rounded-md bg-purple-100 px-2.5 py-1 text-[11px] font-bold text-purple-900 dark:bg-purple-950/60 dark:text-purple-300">
                    Blind Mode
                </span>
            </div>

            <div class="flex items-center justify-between p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200/60 dark:border-zinc-700/60">
                <div class="space-y-0.5">
                    <strong class="text-xs text-zinc-900 dark:text-zinc-100 block font-bold">
                        Sembunyikan Nama Regu & Pangkalan (Mode Sensor / Blind)
                    </strong>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 max-w-xl">
                        Saat aktif, nama regu dan pangkalan sekolah akan disamarkan (contoh: <em>Nomor Undian #01</em> / <em>Pangkalan Dirahasiakan</em>) pada halaman publik dan akun panitia/juri. Hanya Administrator yang dapat melihat dan mengintip identitas asli.
                    </p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer ml-4">
                    <input type="checkbox" wire:model="hide_patrol_identities" class="sr-only peer" />
                    <div class="w-11 h-6 bg-zinc-200 peer-focus:outline-hidden rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-zinc-600 peer-checked:bg-purple-600"></div>
                </label>
            </div>
        </div>

        {{-- Action Bar --}}
        <div class="flex items-center justify-end gap-3 pt-2">
            <flux:button
                type="submit"
                variant="primary"
                icon="check"
            >
                Simpan Konfigurasi Kegiatan
            </flux:button>
        </div>
    </form>
</div>

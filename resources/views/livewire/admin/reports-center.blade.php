<div class="space-y-6">
    {{-- Header Section --}}
    <x-heading-section
        title="Pusat Laporan & Ekspor Data Resmi"
        description="Unduh berkas rekapitulasi lengkap, buku induk peserta, log presensi, rekap nilai juri, dan cetak Berita Acara Juara Umum LT III 2026."
    >
        <x-slot:breadcrumbs>
            <a href="{{ route('dashboard') }}" wire:navigate class="hover:underline">Dashboard</a>
            <span class="mx-2">&rsaquo;</span>
            <span class="text-zinc-800 dark:text-zinc-200">Pusat Laporan</span>
        </x-slot:breadcrumbs>
    </x-heading-section>

    {{-- Ringkasan Statistik Data Siap Ekspor --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card
            label="Gudep Terverifikasi"
            value="{{ $totalScoutGroups }}"
            description="Pangkalan Sekolah Siap Lomba"
            icon="building-office-2"
            color="amber"
        />

        <x-stat-card
            label="Total Peserta Terdaftar"
            value="{{ $totalParticipants }}"
            description="Anggota Regu & Pimpinan"
            icon="academic-cap"
            color="blue"
        />

        <x-stat-card
            label="Log Presensi Masuk"
            value="{{ $totalAttendances }}"
            description="Rekapitulasi Kehadiran Pos"
            icon="clipboard-document-check"
            color="emerald"
        />

        <x-stat-card
            label="Nilai Lomba Terinput"
            value="{{ $totalScoresEntered }}"
            description="Penilaian dari Dewan Juri"
            icon="trophy"
            color="purple"
        />
    </div>

    {{-- Section: Dokumen Berita Acara & Klasemen Resmi --}}
    <div class="rounded-2xl border border-amber-200/80 bg-gradient-to-br from-amber-500/10 via-amber-500/5 to-transparent p-6 shadow-xs dark:border-amber-900/40 dark:bg-amber-950/20 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-start gap-3.5">
                <span class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-amber-600 text-white shadow-xs">
                    <flux:icon name="document-text" class="size-6" />
                </span>
                <div>
                    <span class="rounded bg-amber-200/80 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-amber-900 dark:bg-amber-900/60 dark:text-amber-200">
                        Dokumen Yuridis Resmi
                    </span>
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 mt-1">
                        Berita Acara Penetapan Juara Umum & Peringkat Prestasi
                    </h3>
                    <p class="text-xs text-zinc-600 dark:text-zinc-400 mt-0.5 leading-relaxed max-w-2xl">
                        Format siap cetak (A4 PDF) mencantumkan peringkat regu putra dan regu putri (terpisah), perolehan prestasi, serta regu berprestasi tinggi putra & putri, serta ruang pengesahan tanda tangan Dewan Juri, Panitia, dan Pimpinan Kwarcab.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('admin.reports.print.berita-acara') }}" target="_blank">
                    <flux:button icon="document-text" variant="primary">
                        Cetak Berita Acara
                    </flux:button>
                </a>
            </div>
        </div>
    </div>

    {{-- Section: Katalog Unduhan Berkas Spreadsheet (CSV / Excel) --}}
    <div>
        <div class="mb-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300">
                Katalog Rekapitulasi Format CSV / Excel
            </h3>
            <p class="text-xs text-zinc-400">
                Semua berkas diunduh dengan encoding UTF-8 BOM sehingga langsung terbaca rapi dan kompatibel di Microsoft Excel
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Card 1: Buku Induk Peserta --}}
            <div class="rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 flex flex-col justify-between space-y-4">
                <div class="flex items-start gap-3">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300">
                        <flux:icon name="academic-cap" class="size-5" />
                    </span>
                    <div>
                        <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100">
                            Buku Induk Peserta & Anggota Regu
                        </h4>
                        <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5 leading-relaxed">
                            Daftar lengkap seluruh peserta (Pinru, Wapinru, Anggota) beserta NIK, NTA Pramuka, asal sekolah, nama regu, dan tanggal lahir.
                        </p>
                    </div>
                </div>

                <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                    <span class="text-[11px] font-mono text-zinc-400">{{ $totalParticipants }} Peserta</span>
                    <a href="{{ route('admin.reports.export.participants') }}">
                        <flux:button icon="arrow-down-tray" variant="subtle" size="sm">
                            Unduh CSV
                        </flux:button>
                    </a>
                </div>
            </div>

            {{-- Card 2: Rekapitulasi Kontingen & Gugus Depan --}}
            <div class="rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 flex flex-col justify-between space-y-4">
                <div class="flex items-start gap-3">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">
                        <flux:icon name="building-office-2" class="size-5" />
                    </span>
                    <div>
                        <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100">
                            Rekap Kontingen & Gugus Depan
                        </h4>
                        <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5 leading-relaxed">
                            Data master pangkalan sekolah, nomor gudep, nama Kamabigus, kontak pembina, jumlah regu, serta status verifikasi administrasi.
                        </p>
                    </div>
                </div>

                <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                    <span class="text-[11px] font-mono text-zinc-400">{{ $totalScoutGroups }} Kontingen</span>
                    <a href="{{ route('admin.reports.export.contingents') }}">
                        <flux:button icon="arrow-down-tray" variant="subtle" size="sm">
                            Unduh CSV
                        </flux:button>
                    </a>
                </div>
            </div>

            {{-- Card 3: Rekap Presensi & Kehadiran Pos --}}
            <div class="rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 flex flex-col justify-between space-y-4">
                <div class="flex items-start gap-3">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                        <flux:icon name="clipboard-document-check" class="size-5" />
                    </span>
                    <div>
                        <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100">
                            Rekapitulasi Presensi Seluruh Pos
                        </h4>
                        <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5 leading-relaxed">
                            Seluruh jejak pemindaian QR code kehadiran peserta, bindamping, dan aparat kegiatan lengkap dengan stempel waktu server (WIB) dan nama pos.
                        </p>
                    </div>
                </div>

                <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                    <span class="text-[11px] font-mono text-zinc-400">{{ $totalAttendances }} Pindai</span>
                    <a href="{{ route('admin.reports.export.attendance') }}">
                        <flux:button icon="arrow-down-tray" variant="subtle" size="sm">
                            Unduh CSV
                        </flux:button>
                    </a>
                </div>
            </div>

            {{-- Card 4: Rekap Nilai Seluruh Cabang Lomba --}}
            <div class="rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 flex flex-col justify-between space-y-4">
                <div class="flex items-start gap-3">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300">
                        <flux:icon name="trophy" class="size-5" />
                    </span>
                    <div>
                        <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100">
                            Rekap Nilai Seluruh Cabang Lomba
                        </h4>
                        <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5 leading-relaxed">
                            Rincian skor akhir setiap regu per mata lomba, nama juri penilai, predikat prestasi (Tinggi, Baik, Cukup), dan catatan dewan juri.
                        </p>
                    </div>
                </div>

                <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                    <span class="text-[11px] font-mono text-zinc-400">{{ $totalScoresEntered }} Skor Masuk</span>
                    <a href="{{ route('admin.reports.export.scores') }}">
                        <flux:button icon="arrow-down-tray" variant="subtle" size="sm">
                            Unduh CSV
                        </flux:button>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Section: Ekspor Klasemen Akhir Akumulasi Medali dengan Filter Gender --}}
    <div class="rounded-2xl border border-zinc-200/80 bg-white p-6 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-zinc-100 dark:border-zinc-800 pb-4">
            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-900 dark:text-zinc-100">
                    Ekspor Klasemen Akumulasi Prestasi
                </h4>
                <p class="text-xs text-zinc-400 mt-0.5">
                    Pilih kategori regu untuk mengunduh rekap ranking kejuaraan
                </p>
            </div>

            <div class="flex items-center gap-3">
                <select
                    wire:model.live="standingsGender"
                    class="rounded-xl border border-zinc-300 bg-white px-3.5 py-1.5 text-xs text-zinc-800 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                >
                    <option value="all">Semua Kategori (Putra & Putri Terpisah)</option>
                    <option value="boys">Khusus Kategori Putra</option>
                    <option value="girls">Khusus Kategori Putri</option>
                </select>

                <a href="{{ route('admin.reports.export.standings', ['gender' => $standingsGender]) }}">
                    <flux:button icon="arrow-down-tray" variant="primary" size="sm">
                        Unduh Klasemen CSV
                    </flux:button>
                </a>
            </div>
        </div>

        <div class="text-[11px] text-zinc-500 dark:text-zinc-400 flex items-center gap-2">
            <flux:icon name="information-circle" class="size-4 text-amber-500 shrink-0" />
            <span>Peringkat umum ditentukan jumlah medali secara berurutan: Prestasi Tinggi terbanyak, lalu Prestasi Baik terbanyak, lalu Prestasi Cukup terbanyak.</span>
        </div>
    </div>
</div>

<x-layouts::public title="Panduan Pendaftaran & Penggunaan Sistem">
    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        {{-- Breadcrumb & Title --}}
        <x-heading-section
            class="mb-6"
            title="Panduan Pendaftaran & Penggunaan Aplikasi LT III"
            description="Petunjuk teknis tata cara registrasi kontingen pangkalan Gugus Depan, verifikasi berkas, dan kartu digital."
        >
            <x-slot:breadcrumbs>
                <a href="{{ route('home') }}" wire:navigate class="hover:underline">Beranda</a>
                <span class="mx-2">&rsaquo;</span>
                <span class="text-zinc-800 dark:text-zinc-200">Panduan Pendaftaran</span>
            </x-slot:breadcrumbs>
        </x-heading-section>

        <div class="space-y-6">
            {{-- Step 1 --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center gap-3">
                    <span class="flex size-8 items-center justify-center rounded-xl bg-amber-600 text-sm font-bold text-white">1</span>
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                        Pendaftaran & Pengelolaan Akun Gugus Depan
                    </h2>
                </div>
                <div class="mt-3 text-xs sm:text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed space-y-2">
                    <p>
                        Setiap pangkalan sekolah (SMP/MTs sederajat) diwakili oleh satu akun penanggung jawab resmi Gugus Depan. Akun ini bertanggung jawab untuk mengisi identitas pangkalan, mengelola regu, mendaftarkan peserta, dan mendaftarkan pembina pendamping (bindamping).
                    </p>
                </div>
            </div>

            {{-- Step 2 --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center gap-3">
                    <span class="flex size-8 items-center justify-center rounded-xl bg-amber-600 text-sm font-bold text-white">2</span>
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                        Pembentukan Regu & Input Anggota Peserta
                    </h2>
                </div>
                <div class="mt-3 text-xs sm:text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed space-y-2">
                    <p>
                        Gugus Depan wajib membuat entitas <strong>Regu</strong> terlebih dahulu sebelum menginput peserta. Kategori regu terbagi menjadi <strong>Penggalang Putra</strong> dan <strong>Penggalang Putri</strong>.
                    </p>
                    <p>
                        Setiap regu terdiri dari Pimpinan Regu (Pinru), Wakil Pimpinan Regu (Wapinru), dan Anggota. Pastikan kelengkapan biodata, nomor NTA Pramuka, dan pas foto telah terisi dengan benar.
                    </p>
                </div>
            </div>

            {{-- Step 3 --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center gap-3">
                    <span class="flex size-8 items-center justify-center rounded-xl bg-amber-600 text-sm font-bold text-white">3</span>
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                        Pemeriksaan Ulang & Verifikasi oleh Administrator
                    </h2>
                </div>
                <div class="mt-3 text-xs sm:text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed space-y-2">
                    <p>
                        Sebelum pengajuan akhir, periksa kembali seluruh data kontingen pada halaman pratinjau. Setelah tombol <em>Ajukan Pendaftaran</em> ditekan, data akan masuk ke antrean verifikasi <strong>Administrator</strong>.
                    </p>
                    <p>
                        Administrator berwenang menyetujui, meminta perbaikan, atau menolak pendaftaran jika berkas tidak sesuai petunjuk teknis kegiatan.
                    </p>
                </div>
            </div>

            {{-- Step 4 --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center gap-3">
                    <span class="flex size-8 items-center justify-center rounded-xl bg-amber-600 text-sm font-bold text-white">4</span>
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                        Penerbitan Kartu Digital & QR Code Aman
                    </h2>
                </div>
                <div class="mt-3 text-xs sm:text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed space-y-2">
                    <p>
                        Setelah pendaftaran disetujui, sistem secara otomatis menerbitkan <strong>Kartu Digital</strong> yang memuat token acak aman dan QR Code resmi untuk masing-masing peserta dan bindamping.
                    </p>
                    <p>
                        Kartu digital dapat diunduh dalam format gambar/PDF untuk dicetak sebagai tanda pengenal fisik atau disimpan di ponsel untuk dipindai saat absensi lomba.
                    </p>
                </div>
            </div>

            {{-- Step 5 --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center gap-3">
                    <span class="flex size-8 items-center justify-center rounded-xl bg-amber-600 text-sm font-bold text-white">5</span>
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                        Presensi Pos Lomba & Penilaian Blind Scoring
                    </h2>
                </div>
                <div class="mt-3 text-xs sm:text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed space-y-2">
                    <p>
                        Peserta wajib melakukan presensi kehadiran di pos lomba menggunakan kamera scanner yang dioperasikan oleh petugas berwenang (Admin/Juri). Presensi dibuka paling cepat <strong>30 menit sebelum waktu lomba dimulai</strong> dan ditutup saat waktu sesi lomba berakhir berdasarkan jam server resmi Asia/Jakarta.
                    </p>
                    <p>
                        Penilaian lomba menerapkan <em>Blind Scoring</em>: Dewan Juri hanya menilai nomor peserta anonim tanpa mengetahui asal pangkalan/sekolah regu untuk menjamin keadilan penilaian.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-layouts::public>

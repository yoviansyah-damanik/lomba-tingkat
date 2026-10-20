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

    {{-- Interactive Step Navigator (Livewire 4) --}}
    <div class="mb-8 grid grid-cols-2 sm:grid-cols-5 gap-2">
        @php
            $steps = [
                1 => 'Akun Gudep',
                2 => 'Regu & Anggota',
                3 => 'Verifikasi',
                4 => 'Kartu Digital',
                5 => 'Presensi & Nilai',
            ];
        @endphp
        @foreach($steps as $sNum => $sTitle)
            <button
                type="button"
                wire:click="setStep({{ $sNum }})"
                class="flex flex-col items-center justify-center rounded-2xl border p-3 text-center transition-all cursor-pointer {{ $activeStep === $sNum ? 'border-amber-500 bg-amber-50 text-amber-900 shadow-sm dark:bg-amber-950/40 dark:text-amber-200 dark:border-amber-500/50' : 'border-zinc-200 bg-white text-zinc-600 hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400' }}">
                <span class="flex size-6 items-center justify-center rounded-full text-xs font-bold {{ $activeStep === $sNum ? 'bg-amber-600 text-white' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400' }}">
                    {{ $sNum }}
                </span>
                <span class="mt-1.5 text-[11px] font-semibold truncate w-full">{{ $sTitle }}</span>
            </button>
        @endforeach
    </div>

    <div class="space-y-6">
        {{-- Step 1 --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 {{ $activeStep === 1 ? 'ring-2 ring-amber-500/30' : '' }}">
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
                <div class="pt-2">
                    <a href="{{ route('register.scout-group') }}" wire:navigate class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-700 hover:underline dark:text-amber-400">
                        Daftar Akun Gugus Depan Baru &rarr;
                    </a>
                </div>
            </div>
        </div>

        {{-- Step 2 --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 {{ $activeStep === 2 ? 'ring-2 ring-amber-500/30' : '' }}">
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
        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 {{ $activeStep === 3 ? 'ring-2 ring-amber-500/30' : '' }}">
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
        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 {{ $activeStep === 4 ? 'ring-2 ring-amber-500/30' : '' }}">
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
        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 {{ $activeStep === 5 ? 'ring-2 ring-amber-500/30' : '' }}">
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
                    Penilaian dilakukan murni berdasarkan performa teknis peserta pada nomor urut acak tanpa menampilkan asal sekolah/gugus depan demi asas keadilan objektif.
                </p>
            </div>
        </div>
    </div>
</div>

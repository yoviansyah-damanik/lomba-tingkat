<x-layouts::app title="Tambah Aparatur Baru">
    <div class="flex h-full w-full flex-1 flex-col gap-6 max-w-4xl mx-auto">
        <div>
            <a href="{{ route('admin.officials.index') }}" class="text-xs font-semibold text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 inline-flex items-center gap-1 mb-2">
                &larr; Kembali ke Daftar Aparatur
            </a>
            <h1 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100 sm:text-2xl">
                Tambah Aparatur Panitia & Dewan Juri
            </h1>
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400 sm:text-sm">
                Daftarkan akun staf baru untuk operasional pos kegiatan dan dewan juri penilai
            </p>
        </div>

        @if($errors->any())
            <div class="rounded-xl border border-red-300 bg-red-50 p-4 text-xs text-red-900 dark:border-red-500/30 dark:bg-red-950/40 dark:text-red-200">
                <ul class="list-disc pl-4 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.officials.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 space-y-5">
                <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    Informasi Akun & Akses Masuk
                </h2>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                            Nama Lengkap & Gelar <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            placeholder="Contoh: Kak Ahmad Fauzi, S.Pd"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                            Alamat Email (Digunakan untuk Login) <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            placeholder="nama@kwarcab.org"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                            Kata Sandi (Minimal 8 karakter) <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="password"
                            name="password"
                            required
                            placeholder="••••••••"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                            Nomor WhatsApp / HP Aktif
                        </label>
                        <input
                            type="text"
                            name="phone_number"
                            value="{{ old('phone_number') }}"
                            placeholder="081234567890"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        >
                    </div>
                </div>

                {{-- Penugasan Peran --}}
                <div class="pt-2">
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-2">
                        Peran Penugasan (Bisa pilih keduanya jika merangkap) <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <label class="flex items-start gap-3 rounded-xl border border-zinc-200 p-3 cursor-pointer hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800/50">
                            <input
                                type="checkbox"
                                name="roles[]"
                                value="committee"
                                {{ in_array('committee', old('roles', ['committee'])) ? 'checked' : '' }}
                                class="mt-0.5 rounded text-amber-600 focus:ring-amber-500"
                            >
                            <div>
                                <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Panitia Pelaksana (Committee)</span>
                                <p class="text-[11px] text-zinc-500">Berwenang memonitor pos, presensi, dan logistik kegiatan</p>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 rounded-xl border border-zinc-200 p-3 cursor-pointer hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800/50">
                            <input
                                type="checkbox"
                                name="roles[]"
                                value="judge"
                                {{ in_array('judge', old('roles', [])) ? 'checked' : '' }}
                                class="mt-0.5 rounded text-amber-600 focus:ring-amber-500"
                            >
                            <div>
                                <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Dewan Juri (Judge)</span>
                                <p class="text-[11px] text-zinc-500">Berwenang memasukkan skor penilaian pada pos mata lomba</p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 space-y-4">
                <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    Biodata Resmi untuk Kartu Identitas Aparatur
                </h2>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                            Nomor Tanda Anggota (NTA)
                        </label>
                        <input
                            type="text"
                            name="scout_id"
                            value="{{ old('scout_id') }}"
                            placeholder="02.17.xxxxx"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                            Instansi / Asal Lembaga
                        </label>
                        <input
                            type="text"
                            name="institution"
                            value="{{ old('institution', 'Kwarcab Kota Padangsidimpuan') }}"
                            placeholder="Kwarcab / Instansi"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                            Jabatan / Penugasan Khusus
                        </label>
                        <input
                            type="text"
                            name="position"
                            value="{{ old('position') }}"
                            placeholder="Contoh: Koordinator Pos Halang Rintang"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        >
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                        Pas Foto Resmi (Opsional, PNG/JPG maks 2MB)
                    </label>
                    <input
                        type="file"
                        name="avatar"
                        accept="image/png, image/jpeg"
                        class="w-full text-xs text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 dark:file:bg-zinc-800 dark:file:text-amber-400"
                    >
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('admin.officials.index') }}" class="rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                    Batal
                </a>
                <button type="submit" class="rounded-xl bg-amber-600 px-5 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-amber-700">
                    Simpan & Daftarkan Aparatur
                </button>
            </div>
        </form>
    </div>
</x-layouts::app>

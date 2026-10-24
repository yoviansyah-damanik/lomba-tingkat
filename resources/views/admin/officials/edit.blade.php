<x-layouts::app title="Edit Data Aparatur">
    <div class="flex h-full w-full flex-1 flex-col gap-6 max-w-4xl mx-auto">
        <div>
            <a href="{{ route('admin.officials.index') }}" class="text-xs font-semibold text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 inline-flex items-center gap-1 mb-2">
                &larr; Kembali ke Daftar Aparatur
            </a>
            <h1 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100 sm:text-2xl">
                Edit Aparatur &bull; {{ $official->name }}
            </h1>
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400 sm:text-sm">
                Perbarui akun, hak akses peran panitia/juri, status aktif, dan foto tanda pengenal
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

        <form method="POST" action="{{ route('admin.officials.update', $official->id) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 space-y-5">
                <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                        Informasi Akun & Akses Masuk
                    </h2>
                    <a href="{{ route('digital-cards.staff', $official->id) }}" target="_blank"
                       class="inline-flex items-center gap-1 text-xs font-semibold text-amber-600 hover:text-amber-700 dark:text-amber-400">
                        <flux:icon name="qr-code" class="size-3.5" />
                        Pratinjau Kartu Digital &rarr;
                    </a>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                            Nama Lengkap & Gelar <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name', $official->name) }}"
                            required
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
                            value="{{ old('phone_number', $official->phone_number) }}"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        >
                    </div>
                </div>

                {{-- Penugasan Peran --}}
                @php
                    $currentRoles = $official->roles->pluck('name')->toArray();
                @endphp
                <div class="pt-2">
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-2">
                        Peran Penugasan <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <label class="flex items-start gap-3 rounded-xl border border-zinc-200 p-3 cursor-pointer hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800/50">
                            <input
                                type="checkbox"
                                name="roles[]"
                                value="committee"
                                {{ in_array('committee', old('roles', $currentRoles)) ? 'checked' : '' }}
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
                                {{ in_array('judge', old('roles', $currentRoles)) ? 'checked' : '' }}
                                class="mt-0.5 rounded text-amber-600 focus:ring-amber-500"
                            >
                            <div>
                                <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Dewan Juri (Judge)</span>
                                <p class="text-[11px] text-zinc-500">Berwenang memasukkan skor penilaian pada pos mata lomba</p>
                            </div>
                        </label>
                    </div>
                    @include('admin.officials.partials.administrator-toggle', ['checked' => in_array('admin', $currentRoles)])
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
                            value="{{ old('scout_id', $official->scout_id) }}"
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
                            value="{{ old('institution', $official->institution) }}"
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
                            value="{{ old('position', $official->position) }}"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        >
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    @if($official->avatar_path)
                        <img src="{{ asset('storage/' . $official->avatar_path) }}" alt="{{ $official->name }}" class="size-16 rounded-xl object-cover border border-zinc-200">
                    @endif
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                            Ganti Pas Foto Resmi (Opsional, PNG/JPG maks 2MB)
                        </label>
                        <input
                            type="file"
                            name="avatar"
                            accept="image/png, image/jpeg"
                            class="w-full text-xs text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 dark:file:bg-zinc-800 dark:file:text-amber-400"
                        >
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('admin.officials.index') }}" class="rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                    Batal
                </a>
                <button type="submit" class="rounded-xl bg-amber-600 px-5 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-amber-700">
                    Perbarui Data Aparatur
                </button>
            </div>
        </form>
    </div>
</x-layouts::app>

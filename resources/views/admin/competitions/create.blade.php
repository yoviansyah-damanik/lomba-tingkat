<x-layouts::app :title="__('Tambah Cabang Mata Lomba')">
    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Breadcrumb & Header --}}
        <div>
            <div class="flex items-center gap-2 text-xs text-zinc-500 mb-2">
                <a href="{{ route('admin.competitions.index') }}" class="hover:text-amber-700">Mata Lomba & Rubrik</a>
                <span>&rarr;</span>
                <span class="text-zinc-900 dark:text-zinc-100 font-semibold">Tambah Cabang Baru</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-100">
                Tambah Cabang Mata Lomba
            </h1>
            <p class="mt-1 text-xs sm:text-sm text-zinc-600 dark:text-zinc-400">
                Daftarkan cabang mata lomba resmi baru untuk penyelenggaraan Lomba Tingkat III. Setelah disimpan, Anda dapat mengonfigurasi rubrik kriteria penilaian.
            </p>
        </div>

        @if ($errors->any())
            <div class="rounded-2xl border border-red-300 bg-red-50 p-4 text-xs sm:text-sm text-red-900 dark:border-red-500/30 dark:bg-red-950/40 dark:text-red-200">
                <p class="font-bold mb-1">Periksa isian formulir:</p>
                <ul class="list-disc pl-5 space-y-0.5 text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form Container --}}
        <div class="rounded-3xl border border-zinc-200/90 bg-white p-6 sm:p-8 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
            <form method="POST" action="{{ route('admin.competitions.store') }}" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    {{-- Kode Lomba --}}
                    <div>
                        <flux:input
                            name="code"
                            label="Kode Cabang Lomba (Unik)"
                            :value="old('code')"
                            required
                            placeholder="Contoh: L-PION, L-SEMA, L-P3K..."
                        />
                        <p class="mt-1 text-[11px] text-zinc-400">Kode singkatan unik untuk pengarsipan dan jadwal.</p>
                    </div>

                    {{-- Nama Lomba --}}
                    <div>
                        <flux:input
                            name="title"
                            label="Nama Cabang Mata Lomba"
                            :value="old('title')"
                            required
                            placeholder="Contoh: Pionering Kreatif Menara Pandang"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
                    {{-- Kategori Golongan --}}
                    <div>
                        <flux:select name="category" label="Kategori Golongan Peserta" required>
                            <flux:select.option value="all" :selected="old('category') === 'all'">Putra & Putri (Terpisah)</flux:select.option>
                            <flux:select.option value="boys" :selected="old('category') === 'boys'">Khusus Putra Saja</flux:select.option>
                            <flux:select.option value="girls" :selected="old('category') === 'girls'">Khusus Putri Saja</flux:select.option>
                        </flux:select>
                    </div>

                    {{-- Tipe Format Kepesertaan --}}
                    <div>
                        <flux:select name="participant_type" label="Format Kepesertaan" required>
                            <flux:select.option value="patrol" :selected="old('participant_type', 'patrol') === 'patrol'">Regu Utuh (PINRU/WAPINRU Presensi)</flux:select.option>
                            <flux:select.option value="group" :selected="old('participant_type') === 'group'">Kelompok (Beberapa Anggota dalam Regu)</flux:select.option>
                            <flux:select.option value="individual" :selected="old('participant_type') === 'individual'">Individu (1 Perwakilan Regu)</flux:select.option>
                        </flux:select>
                    </div>

                    {{-- Formula Penilaian --}}
                    <div>
                        <flux:select name="scoring_formula" label="Metode Akumulasi Skor" required>
                            <flux:select.option value="average" :selected="old('scoring_formula', 'average') === 'average'">Rata-Rata Multi-Juri (Average)</flux:select.option>
                            <flux:select.option value="sum" :selected="old('scoring_formula') === 'sum'">Total Akumulasi Poin (Sum)</flux:select.option>
                        </flux:select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    {{-- Min Peserta --}}
                    <div>
                        <flux:input
                            name="min_participants"
                            label="Jumlah Minimal Peserta (Orang)"
                            type="number"
                            :value="old('min_participants', 1)"
                            min="1"
                            max="8"
                            required
                        />
                    </div>

                    {{-- Max Peserta --}}
                    <div>
                        <flux:input
                            name="max_participants"
                            label="Jumlah Maksimal Peserta (Orang)"
                            type="number"
                            :value="old('max_participants', 8)"
                            min="1"
                            max="8"
                            required
                        />
                    </div>
                </div>

                {{-- Deskripsi Ringkas --}}
                <div>
                    <flux:textarea
                        name="description"
                        label="Ringkasan Deskripsi Lomba"
                        rows="2"
                        placeholder="Uraian singkat tujuan dan konsep lomba..."
                    >{{ old('description') }}</flux:textarea>
                </div>

                {{-- Ketentuan Juknis Teknis --}}
                <div>
                    <flux:textarea
                        name="guidelines_text"
                        label="Ketentuan Petunjuk Teknis (Juknis Lengkap)"
                        rows="5"
                        placeholder="Tuliskan aturan teknis, durasi, peralatan yang wajib dibawa, larangan, serta pedoman pelaksanaan pos lomba..."
                    >{{ old('guidelines_text') }}</flux:textarea>
                </div>

                {{-- Status Publikasi --}}
                <div class="pt-2">
                    <flux:checkbox
                        name="is_published"
                        label="Publikasikan mata lomba ini langsung di portal informasi publik"
                        :checked="old('is_published', true)"
                    />
                </div>

                {{-- Actions --}}
                <div class="pt-4 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                    <a href="{{ route('admin.competitions.index') }}" class="text-xs font-semibold text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200">
                        &larr; Batal & Kembali
                    </a>

                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-amber-600 px-5 py-2.5 text-xs sm:text-sm font-bold text-white shadow-md shadow-amber-600/20 hover:bg-amber-700 transition cursor-pointer"
                    >
                        <flux:icon name="check" class="size-4" />
                        <span>Simpan & Atur Rubrik Kriteria &rarr;</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>

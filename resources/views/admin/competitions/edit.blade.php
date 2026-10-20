<x-layouts::app :title="__('Atur Mata Lomba & Rubrik Penilaian')">
    <div class="space-y-6">
        {{-- Breadcrumb & Header --}}
        <div>
            <div class="flex items-center gap-2 text-xs text-zinc-500 mb-2">
                <a href="{{ route('admin.competitions.index') }}" class="hover:text-amber-700">Mata Lomba & Rubrik</a>
                <span>&rarr;</span>
                <span class="text-zinc-900 dark:text-zinc-100 font-semibold">{{ $competition->title }}</span>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-100">
                        Atur Mata Lomba & Rubrik Penilaian
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm text-zinc-600 dark:text-zinc-400">
                        Sesuaikan detail mata lomba <span class="font-bold text-amber-700 dark:text-amber-400 font-mono">{{ $competition->code }}</span> dan kelola bobot kriteria penilaian juri.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.competitions.index') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-zinc-200 bg-white px-3 py-2 text-xs font-semibold text-zinc-700 shadow-xs hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                        <flux:icon name="arrow-left" class="size-3.5" />
                        <span>Daftar Lomba</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            {{-- KOLOM KIRI: FORM EDIT DATA LOMBA (lg:col-span-5) --}}
            <div class="lg:col-span-5 rounded-3xl border border-zinc-200/90 bg-white p-6 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 space-y-5">
                <div class="border-b border-zinc-100 pb-4 dark:border-zinc-800">
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                        Informasi Utama Cabang Lomba
                    </h2>
                    <p class="text-xs text-zinc-500">Konfigurasi parameter dasar dan metode scoring.</p>
                </div>

                <form method="POST" action="{{ route('admin.competitions.update', $competition) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <flux:input
                                name="code"
                                label="Kode Lomba"
                                :value="old('code', $competition->code)"
                                required
                            />
                        </div>
                        <div>
                            <flux:select name="scoring_formula" label="Formula Penilaian" required>
                                <flux:select.option value="average" :selected="old('scoring_formula', $competition->scoring_formula) === 'average'">Rata-Rata Multi-Juri</flux:select.option>
                                <flux:select.option value="sum" :selected="old('scoring_formula', $competition->scoring_formula) === 'sum'">Akumulasi Total</flux:select.option>
                            </flux:select>
                        </div>
                    </div>

                    <div>
                        <flux:input
                            name="title"
                            label="Nama Cabang Lomba"
                            :value="old('title', $competition->title)"
                            required
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <flux:select name="category" label="Golongan" required>
                                <flux:select.option value="all" :selected="old('category', $competition->category) === 'all'">Putra & Putri</flux:select.option>
                                <flux:select.option value="boys" :selected="old('category', $competition->category) === 'boys'">Putra</flux:select.option>
                                <flux:select.option value="girls" :selected="old('category', $competition->category) === 'girls'">Putri</flux:select.option>
                            </flux:select>
                        </div>
                        <div>
                            <flux:select name="participant_type" label="Format Kepesertaan" required>
                                <flux:select.option value="patrol" :selected="old('participant_type', $competition->participant_type) === 'patrol'">Regu Utuh (PINRU/WAPINRU Presensi)</flux:select.option>
                                <flux:select.option value="group" :selected="old('participant_type', $competition->participant_type) === 'group'">Kelompok (Beberapa Anggota dalam Regu)</flux:select.option>
                                <flux:select.option value="individual" :selected="old('participant_type', $competition->participant_type) === 'individual'">Individu (1 Perwakilan Regu)</flux:select.option>
                            </flux:select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <flux:input
                                name="min_participants"
                                label="Min Peserta"
                                type="number"
                                :value="old('min_participants', $competition->min_participants)"
                                min="1"
                                max="8"
                                required
                            />
                        </div>
                        <div>
                            <flux:input
                                name="max_participants"
                                label="Max Peserta"
                                type="number"
                                :value="old('max_participants', $competition->max_participants)"
                                min="1"
                                max="8"
                                required
                            />
                        </div>
                    </div>

                    <div>
                        <flux:textarea
                            name="description"
                            label="Deskripsi Ringkas"
                            rows="2"
                        >{{ old('description', $competition->description) }}</flux:textarea>
                    </div>

                    <div>
                        <flux:textarea
                            name="guidelines_text"
                            label="Petunjuk Teknis (Juknis Lengkap)"
                            rows="4"
                        >{{ old('guidelines_text', $competition->guidelines_text) }}</flux:textarea>
                    </div>

                    <div class="pt-1">
                        <flux:checkbox
                            name="is_published"
                            label="Publikasikan di portal web resmi"
                            :checked="old('is_published', $competition->is_published)"
                        />
                    </div>

                    <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800">
                        <button
                            type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-zinc-900 px-4 py-2.5 text-xs sm:text-sm font-bold text-white transition hover:bg-zinc-800 dark:bg-amber-600 dark:hover:bg-amber-500 cursor-pointer"
                        >
                            <flux:icon name="check" class="size-4" />
                            <span>Simpan Perubahan Lomba</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- KOLOM KANAN: RUBRIK KRITERIA PENILAIAN JURI (lg:col-span-7) --}}
            <div class="lg:col-span-7 space-y-6">
                {{-- Daftar Kriteria Yang Ada --}}
                <div class="rounded-3xl border border-zinc-200/90 bg-white p-6 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 space-y-4">
                    <div class="flex items-center justify-between border-b border-zinc-100 pb-4 dark:border-zinc-800">
                        <div>
                            <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                                Rubrik Kriteria Penilaian Dewan Juri
                            </h2>
                            <p class="text-xs text-zinc-500">
                                Kriteria penilaian objektif yang akan dinilai juri saat sesi lomba berlangsung.
                            </p>
                        </div>

                        @php
                            $totalWeight = $competition->criteria->sum('weight_percentage');
                        @endphp
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold {{ $totalWeight == 100 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' }}">
                            Total Bobot: {{ $totalWeight }}%
                        </span>
                    </div>

                    @if($competition->criteria->isEmpty())
                        <div class="rounded-2xl border border-dashed border-zinc-300 p-8 text-center dark:border-zinc-800">
                            <flux:icon name="scale" class="mx-auto size-8 text-zinc-400" />
                            <p class="mt-2 text-xs font-semibold text-zinc-600 dark:text-zinc-400">Belum Ada Kriteria Rubrik</p>
                            <p class="text-[11px] text-zinc-400">Tambahkan kriteria penilaian pertama melalui formulir di bawah ini.</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($competition->criteria as $criterion)
                                <div class="flex items-start justify-between gap-3 rounded-2xl border border-zinc-200/80 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-800/50">
                                    <div class="flex items-start gap-3">
                                        <div class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-amber-600 text-xs font-bold text-white shadow-xs">
                                            {{ $criterion->order_index }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100">
                                                    {{ $criterion->name }}
                                                </h4>
                                                @if($criterion->code)
                                                    <span class="rounded bg-zinc-200 px-1.5 py-0.2 text-[10px] font-mono text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">
                                                        {{ $criterion->code }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if($criterion->description)
                                                <p class="mt-0.5 text-[11px] text-zinc-500 dark:text-zinc-400 leading-snug">
                                                    {{ $criterion->description }}
                                                </p>
                                            @endif

                                            <div class="mt-2 flex items-center gap-3 text-[11px] text-zinc-500">
                                                <span>Rentang Skor: <strong class="text-zinc-800 dark:text-zinc-200">{{ (float) $criterion->min_score }} &ndash; {{ (float) $criterion->max_score }}</strong></span>
                                                <span>&bull;</span>
                                                <span>Bobot: <strong class="text-amber-700 dark:text-amber-400">{{ (float) $criterion->weight_percentage }}%</strong></span>
                                            </div>
                                        </div>
                                    </div>

                                    <form method="POST" action="{{ route('admin.competitions.criteria.destroy', $criterion) }}" onsubmit="return confirm('Hapus kriteria penilaian {{ $criterion->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex size-7 items-center justify-center rounded-lg border border-red-200 text-red-600 hover:bg-red-50 dark:border-red-900/40 dark:text-red-400 dark:hover:bg-red-950/40 cursor-pointer" title="Hapus Kriteria">
                                            <flux:icon name="trash" class="size-3.5" />
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Formulir Tambah Kriteria Baru --}}
                <div class="rounded-3xl border border-amber-200/80 bg-amber-50/40 p-6 shadow-xs dark:border-amber-500/20 dark:bg-amber-950/20 space-y-4">
                    <div class="border-b border-amber-200/50 pb-3 dark:border-amber-500/20">
                        <h3 class="text-sm font-bold text-amber-950 dark:text-amber-300 flex items-center gap-2">
                            <flux:icon name="plus-circle" class="size-4 text-amber-600 dark:text-amber-400" />
                            <span>Tambah Kriteria Rubrik Baru</span>
                        </h3>
                        <p class="text-[11px] text-amber-900/70 dark:text-amber-400/80">Masukkan indikator penilaian yang akan diisi oleh dewan juri.</p>
                    </div>

                    <form method="POST" action="{{ route('admin.competitions.criteria.store', $competition) }}" class="space-y-3.5">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="sm:col-span-2">
                                <flux:input
                                    name="name"
                                    label="Nama Aspek Kriteria Penilaian"
                                    required
                                    placeholder="Contoh: Kerapian Ikatan, Akurasi Simpul, Kecepatan Waktu..."
                                />
                            </div>
                            <div>
                                <flux:input
                                    name="code"
                                    label="Kode Rubrik"
                                    placeholder="K-01"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <flux:input
                                    name="min_score"
                                    label="Skor Min"
                                    type="number"
                                    value="0"
                                    min="0"
                                    required
                                />
                            </div>
                            <div>
                                <flux:input
                                    name="max_score"
                                    label="Skor Max"
                                    type="number"
                                    value="100"
                                    min="1"
                                    required
                                />
                            </div>
                            <div>
                                <flux:input
                                    name="weight_percentage"
                                    label="Bobot (%)"
                                    type="number"
                                    value="25"
                                    min="1"
                                    max="100"
                                    required
                                />
                            </div>
                        </div>

                        <div>
                            <flux:input
                                name="description"
                                label="Pedoman Penilaian Singkat Juri (Opsional)"
                                placeholder="Indikator detail saat juri memberikan nilai..."
                            />
                        </div>

                        <div class="pt-2 flex justify-end">
                            <button
                                type="submit"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-amber-600 px-4 py-2 text-xs font-bold text-white shadow-xs hover:bg-amber-700 transition cursor-pointer"
                            >
                                <flux:icon name="plus" class="size-3.5" />
                                <span>Tambahkan ke Rubrik Lomba</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-layouts::app>

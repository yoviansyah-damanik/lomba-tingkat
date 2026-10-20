<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Header --}}
    <x-heading-section
        title="Mata Lomba & Rubrik Penilaian"
        description="Kelola master cabang perlombaan LT III, format peserta (Regu Utuh, Kelompok, Individu), dan rubrik kriteria dewan juri."
        badge="Master Cabang Perlombaan"
        badge-icon="trophy"
        badge-color="amber"
    >
        <x-slot:actions>
            <flux:button wire:click="openCreateModal" variant="primary" icon="plus">
                Buat Lomba Baru
            </flux:button>
        </x-slot:actions>
    </x-heading-section>

    {{-- Stats Counter Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 sm:gap-4">
        <x-stat-card
            class="col-span-2 sm:col-span-1"
            label="Total Cabang"
            value="{{ $stats['total'] }}"
            unit="Lomba"
            color="neutral"
        />

        <x-stat-card
            label="Regu Utuh"
            value="{{ $stats['patrol'] }}"
            unit="Utuh"
            color="amber"
        />

        <x-stat-card
            label="Kelompok"
            value="{{ $stats['group'] ?? 0 }}"
            unit="Tim"
            color="indigo"
        />

        <x-stat-card
            label="Individu"
            value="{{ $stats['individual'] }}"
            unit="Tunggal"
            color="purple"
        />

        <x-stat-card
            label="Status Publik"
            value="{{ $stats['published'] }}"
            unit="Aktif"
            color="emerald"
        />
    </div>

    {{-- Filter & Search Bar --}}
    <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-5">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari nama lomba, kode cabang, atau kata kunci..."
                    icon="magnifying-glass"
                    clearable
                />
            </div>
            <div class="sm:col-span-3">
                <flux:select wire:model.live="categoryFilter" placeholder="Golongan">
                    <flux:select.option value="all_categories">Semua Kategori</flux:select.option>
                    <flux:select.option value="all">Putra & Putri (Terpisah)</flux:select.option>
                    <flux:select.option value="boys">Hanya Penggalang Putra</flux:select.option>
                    <flux:select.option value="girls">Hanya Penggalang Putri</flux:select.option>
                </flux:select>
            </div>
            <div class="sm:col-span-2">
                <flux:select wire:model.live="participantTypeFilter" placeholder="Format">
                    <flux:select.option value="">Semua Format</flux:select.option>
                    <flux:select.option value="patrol">Regu Utuh</flux:select.option>
                    <flux:select.option value="group">Kelompok</flux:select.option>
                    <flux:select.option value="individual">Individu</flux:select.option>
                </flux:select>
            </div>
            <div class="sm:col-span-2">
                <flux:select wire:model.live="publishedFilter" placeholder="Status">
                    <flux:select.option value="all">Semua Status</flux:select.option>
                    <flux:select.option value="published">Aktif Publik</flux:select.option>
                    <flux:select.option value="draft">Draft Saja</flux:select.option>
                </flux:select>
            </div>
        </div>
    </div>

    {{-- Cards Grid --}}
    @if($competitions->isEmpty())
        <div class="rounded-3xl border border-dashed border-zinc-300 p-12 text-center dark:border-zinc-800 bg-white dark:bg-zinc-900">
            <flux:icon name="trophy" class="mx-auto size-12 text-zinc-400" />
            <h3 class="mt-3 text-sm font-bold text-zinc-900 dark:text-zinc-100">Belum Ada Cabang Lomba Ditemukan</h3>
            <p class="mt-1 text-xs text-zinc-500 max-w-sm mx-auto">
                Tidak ada data cabang mata lomba yang cocok dengan filter saat ini.
            </p>
            <div class="mt-4">
                <flux:button wire:click="openCreateModal" variant="primary" icon="plus" class="text-xs font-bold">
                    Buat Lomba Pertama
                </flux:button>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-6">
            @foreach($competitions as $comp)
                <div class="group flex flex-col justify-between rounded-3xl border border-zinc-200/90 bg-white p-5 shadow-xs transition-all hover:border-amber-400 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900">
                    <div>
                        {{-- Top Badges --}}
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="inline-flex items-center rounded-lg bg-zinc-100 px-2.5 py-1 text-xs font-mono font-extrabold text-zinc-800 border border-zinc-200 dark:bg-zinc-800 dark:text-zinc-200 dark:border-zinc-700">
                                {{ $comp->code }}
                            </span>

                            <div class="flex items-center gap-1.5">
                                <button type="button" wire:click="togglePublish('{{ $comp->id }}')" class="cursor-pointer" title="Klik untuk ubah status publikasi">
                                    @if($comp->is_published)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 hover:ring-1 hover:ring-emerald-500">
                                            <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                            Publik
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-zinc-100 px-2 py-0.5 text-[10px] font-bold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 hover:ring-1 hover:ring-zinc-400">
                                            Draft
                                        </span>
                                    @endif
                                </button>

                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold 
                                    @if($comp->isGroup()) bg-indigo-100 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300
                                    @elseif($comp->isIndividual()) bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300
                                    @else bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300
                                    @endif">
                                    {{ $comp->formatTypeLabel() }}
                                </span>
                            </div>
                        </div>

                        <h3 class="text-base font-extrabold text-zinc-900 dark:text-zinc-100 group-hover:text-amber-700 dark:group-hover:text-amber-400 transition-colors">
                            {{ $comp->title }}
                        </h3>

                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400 line-clamp-2 leading-relaxed">
                            {{ $comp->description ?? 'Tidak ada ringkasan penjelasan teknis cabang lomba.' }}
                        </p>

                        {{-- Info Indicators --}}
                        <div class="mt-4 pt-3 border-t border-zinc-100 dark:border-zinc-800/80 grid grid-cols-2 gap-2 text-xs">
                            <button type="button" wire:click="openCriteriaModal('{{ $comp->id }}')" class="rounded-xl bg-zinc-50 p-2 dark:bg-zinc-800/50 text-left hover:bg-amber-50 dark:hover:bg-amber-950/30 transition cursor-pointer">
                                <span class="text-[10px] text-zinc-400 block font-medium">Rubrik Kriteria</span>
                                <span class="font-bold text-zinc-900 dark:text-zinc-200 flex items-center justify-between">
                                    <span>{{ $comp->criteria_count }} Kriteria</span>
                                    <flux:icon name="arrow-right" class="size-3 text-zinc-400" />
                                </span>
                            </button>
                            <div class="rounded-xl bg-zinc-50 p-2 dark:bg-zinc-800/50">
                                <span class="text-[10px] text-zinc-400 block font-medium">Sesi Jadwal</span>
                                <span class="font-bold text-zinc-900 dark:text-zinc-200">
                                    {{ $comp->schedules_count }} Sesi Lomba
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Footer Actions --}}
                    <div class="mt-5 pt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-zinc-400 font-medium truncate">
                            {{ $comp->participantRangeLabel() }}
                        </span>

                        <div class="flex items-center gap-1.5 shrink-0">
                            {{-- Atur Rubrik Kriteria Modal --}}
                            <button type="button" wire:click="openCriteriaModal('{{ $comp->id }}')"
                               class="cursor-pointer inline-flex items-center gap-1 rounded-xl border border-zinc-200 bg-white px-2.5 py-1.5 text-xs font-bold text-zinc-700 shadow-xs hover:border-amber-400 hover:bg-amber-50 hover:text-amber-800 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                                <flux:icon name="adjustments-horizontal" class="size-3.5" />
                                <span>Rubrik</span>
                            </button>

                            {{-- Edit Lomba --}}
                            <flux:button
                                size="xs"
                                variant="outline"
                                icon="pencil-square"
                                wire:click="openEditModal('{{ $comp->id }}')"
                                title="Ubah Cabang Lomba"
                            >
                                Ubah
                            </flux:button>

                            {{-- Hapus Lomba --}}
                            <button type="button" wire:click="deleteCompetition('{{ $comp->id }}')" wire:confirm="Apakah Anda yakin ingin menghapus cabang lomba {{ $comp->title }}?"
                               class="cursor-pointer inline-flex size-8 items-center justify-center rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 dark:border-rose-900/40 dark:text-rose-400 dark:hover:bg-rose-950/40" title="Hapus Lomba">
                                <flux:icon name="trash" class="size-3.5" />
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($competitions->hasPages())
            <div class="p-4 border-t border-zinc-200 dark:border-zinc-800">
                {{ $competitions->links() }}
            </div>
        @endif
    @endif

    {{-- Modal Tambah / Edit Mata Lomba --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="w-full max-w-xl bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-2xl p-6 overflow-y-auto max-h-[90vh]" @click.outside="$wire.showModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                    <div>
                        <h2 class="text-base font-extrabold text-zinc-900 dark:text-zinc-100">
                            {{ $editingId ? 'Edit Cabang Mata Lomba' : 'Buat Cabang Lomba Baru' }}
                        </h2>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                            Atur informasi cabang, formula penilaian, dan batas peserta
                        </p>
                    </div>
                    <button type="button" wire:click="$set('showModal', false)" class="size-8 rounded-full flex items-center justify-center text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200">
                        <flux:icon name="x-mark" class="size-4" />
                    </button>
                </div>

                <form wire:submit="save" class="mt-4 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-2">
                            <flux:input wire:model="title" label="Nama Cabang Lomba" placeholder="Contoh: Pionering Bangunan Darurat" required />
                        </div>
                        <div>
                            <flux:input wire:model="code" label="Kode Lomba" placeholder="PION-01" required />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <flux:select wire:model="category" label="Golongan" required>
                                <flux:select.option value="all">Putra & Putri</flux:select.option>
                                <flux:select.option value="boys">Hanya Putra</flux:select.option>
                                <flux:select.option value="girls">Hanya Putri</flux:select.option>
                            </flux:select>
                        </div>
                        <div>
                            <flux:select wire:model.live="participant_type" label="Format Lomba" required>
                                <flux:select.option value="patrol">Regu Utuh</flux:select.option>
                                <flux:select.option value="group">Kelompok</flux:select.option>
                                <flux:select.option value="individual">Individu</flux:select.option>
                            </flux:select>
                        </div>
                        <div>
                            <flux:select wire:model="scoring_formula" label="Metode Skor" required>
                                <flux:select.option value="average">Rata-Rata Multi-Juri</flux:select.option>
                                <flux:select.option value="sum">Total Akumulasi</flux:select.option>
                            </flux:select>
                        </div>
                    </div>

                    {{-- Interval Jumlah Peserta --}}
                    <div class="grid grid-cols-2 gap-3 bg-zinc-50 dark:bg-zinc-800/40 p-3.5 rounded-2xl border border-zinc-200 dark:border-zinc-800">
                        <div>
                            <flux:input wire:model="min_participants" type="number" min="1" max="8" label="Min Peserta (Orang)" required />
                        </div>
                        <div>
                            <flux:input wire:model="max_participants" type="number" min="1" max="8" label="Max Peserta (Orang)" required />
                        </div>
                    </div>

                    <div>
                        <flux:textarea wire:model="description" label="Deskripsi Cabang Lomba" placeholder="Penjelasan singkat tujuan dan materi lomba..." rows="2" />
                    </div>

                    <div>
                        <flux:textarea wire:model="guidelines_text" label="Ketentuan Teknis Lomba" placeholder="Durasi, perlengkapan yang dibawa, batasan materi..." rows="3" />
                    </div>

                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="is_published" class="rounded text-amber-600 focus:ring-amber-500">
                            <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">Publikasikan lomba ini di laman informasi publik portal LT III</span>
                        </label>
                    </div>

                    <div class="pt-4 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-end gap-2">
                        <flux:button type="button" wire:click="$set('showModal', false)" variant="ghost" class="text-xs">
                            Batal
                        </flux:button>
                        <flux:button type="submit" variant="primary" class="text-xs font-bold">
                            {{ $editingId ? 'Simpan Perubahan' : 'Buat Lomba' }}
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal / Drawer Manajemen Rubrik Kriteria Penilaian --}}
    @if($showCriteriaModal && $activeCompetition)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="w-full max-w-2xl bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-2xl p-6 overflow-y-auto max-h-[90vh]" @click.outside="$wire.showCriteriaModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 px-2 py-0.5 rounded-md">
                                {{ $activeCompetition->code }}
                            </span>
                            <h2 class="text-base font-extrabold text-zinc-900 dark:text-zinc-100">
                                Rubrik Penilaian: {{ $activeCompetition->title }}
                            </h2>
                        </div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                            Kriteria yang dinilai oleh dewan juri lapangan pada sesi lomba
                        </p>
                    </div>
                    <button type="button" wire:click="$set('showCriteriaModal', false)" class="size-8 rounded-full flex items-center justify-center text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200">
                        <flux:icon name="x-mark" class="size-4" />
                    </button>
                </div>

                {{-- Daftar Kriteria yang Sudah Ada --}}
                <div class="mt-4 space-y-2">
                    <h3 class="text-xs font-bold text-zinc-700 dark:text-zinc-300">Daftar Kriteria Aktif ({{ $activeCompetition->criteria->count() }})</h3>
                    @forelse($activeCompetition->criteria as $criterion)
                        <div class="flex items-center justify-between p-3 rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800/40">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100">{{ $criterion->name }}</span>
                                    @if($criterion->code)
                                        <span class="text-[10px] font-mono text-zinc-400">({{ $criterion->code }})</span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-zinc-500 mt-0.5">
                                    Rentang: <strong class="text-zinc-700 dark:text-zinc-300">{{ $criterion->min_score }} - {{ $criterion->max_score }}</strong> &bull; Bobot: <strong class="text-zinc-700 dark:text-zinc-300">{{ $criterion->weight_percentage }}%</strong>
                                </div>
                            </div>
                            <button type="button" wire:click="deleteCriterion('{{ $criterion->id }}')" wire:confirm="Hapus kriteria penilaian ini?" class="size-7 rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 flex items-center justify-center cursor-pointer" title="Hapus kriteria">
                                <flux:icon name="trash" class="size-3.5" />
                            </button>
                        </div>
                    @empty
                        <div class="text-center py-6 border border-dashed border-zinc-200 dark:border-zinc-800 rounded-2xl text-xs text-zinc-400">
                            Belum ada rubrik kriteria untuk lomba ini. Tambahkan kriteria baru di bawah.
                        </div>
                    @endforelse
                </div>

                {{-- Formulir Tambah Kriteria Baru --}}
                <div class="mt-6 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <h3 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 mb-3">+ Tambah Kriteria Baru</h3>
                    <form wire:submit="addCriterion" class="space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            <div class="sm:col-span-2">
                                <flux:input wire:model="criterionName" label="Nama Kriteria" placeholder="Contoh: Kerapian & Kekuatan Ikatan" required />
                            </div>
                            <div>
                                <flux:input wire:model="criterionCode" label="Kode Singkat" placeholder="IKAT" />
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-2.5">
                            <div>
                                <flux:input wire:model="minScore" type="number" step="0.5" label="Skor Min" required />
                            </div>
                            <div>
                                <flux:input wire:model="maxScore" type="number" step="0.5" label="Skor Max" required />
                            </div>
                            <div>
                                <flux:input wire:model="weightPercentage" type="number" step="1" label="Bobot (%)" required />
                            </div>
                        </div>

                        <div>
                            <flux:input wire:model="criterionDescription" label="Panduan Penilaian Juri" placeholder="Penjelasan standar poin nilai bagi juri..." />
                        </div>

                        <div class="flex justify-end pt-2">
                            <flux:button type="submit" variant="primary" class="text-xs font-bold">
                                Simpan Kriteria
                            </flux:button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

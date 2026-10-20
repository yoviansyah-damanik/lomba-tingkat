<x-layouts::app :title="__('Manajemen Mata Lomba & Rubrik Penilaian')">
    <div class="space-y-6">
        {{-- Header & Statistik Cepat --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border border-amber-300 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800 dark:border-amber-500/20 dark:bg-amber-950/40 dark:text-amber-300 mb-2">
                    <span class="size-2 rounded-full bg-amber-600"></span>
                    <span>Modul 5 &bull; Administrasi Cabang Lomba</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-100">
                    Mata Lomba & Rubrik Penilaian
                </h1>
                <p class="mt-1 text-xs sm:text-sm text-zinc-600 dark:text-zinc-400">
                    Kelola master cabang mata lomba resmi LT III, interval jumlah peserta, juknis teknis, dan bobot rubrik kriteria dewan juri.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <a
                    href="{{ route('admin.competitions.create') }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-amber-600 to-amber-700 px-4 py-2.5 text-xs sm:text-sm font-bold text-white shadow-md shadow-amber-600/20 transition-all hover:brightness-105 active:scale-95"
                >
                    <flux:icon name="plus" class="size-4" />
                    <span>Tambah Cabang Lomba</span>
                </a>
            </div>
        </div>

        {{-- Notification Alerts --}}
        <x-alert />

        {{-- Statistik Counter Grid --}}
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 sm:gap-4">
            <x-stat-card
                label="Total Cabang"
                :value="$stats['total']"
                unit="Lomba"
                color="neutral"
                class="col-span-2 sm:col-span-1"
            />
            <x-stat-card
                label="Regu Utuh"
                :value="$stats['patrol']"
                unit="Utuh"
                color="amber"
            />
            <x-stat-card
                label="Kelompok"
                :value="$stats['group'] ?? 0"
                unit="Tim"
                color="indigo"
            />
            <x-stat-card
                label="Individu"
                :value="$stats['individual']"
                unit="Tunggal"
                color="purple"
            />
            <x-stat-card
                label="Status Publik"
                :value="$stats['published']"
                unit="Aktif"
                color="emerald"
            />
        </div>

        {{-- Filter & Search Bar --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
            <form method="GET" action="{{ route('admin.competitions.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                <div class="sm:col-span-5">
                    <flux:input
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari nama lomba, kode cabang, atau kata kunci..."
                        icon="magnifying-glass"
                        clearable
                    />
                </div>
                <div class="sm:col-span-3">
                    <flux:select name="category" placeholder="Semua Golongan">
                        <flux:select.option value="all_categories" :selected="request('category') === 'all_categories'">Semua Kategori</flux:select.option>
                        <flux:select.option value="all" :selected="request('category') === 'all'">Putra & Putri (Terpisah)</flux:select.option>
                        <flux:select.option value="boys" :selected="request('category') === 'boys'">Hanya Penggalang Putra</flux:select.option>
                        <flux:select.option value="girls" :selected="request('category') === 'girls'">Hanya Penggalang Putri</flux:select.option>
                    </flux:select>
                </div>
                <div class="sm:col-span-2">
                    <flux:select name="participant_type" placeholder="Format Lomba">
                        <flux:select.option value="" :selected="!request('participant_type')">Semua Format</flux:select.option>
                        <flux:select.option value="patrol" :selected="request('participant_type') === 'patrol'">Regu Utuh</flux:select.option>
                        <flux:select.option value="group" :selected="request('participant_type') === 'group'">Kelompok</flux:select.option>
                        <flux:select.option value="individual" :selected="request('participant_type') === 'individual'">Individu</flux:select.option>
                    </flux:select>
                </div>
                <div class="sm:col-span-2 flex items-center gap-2">
                    <flux:button type="submit" variant="primary" icon="funnel" class="w-full justify-center">
                        Saring
                    </flux:button>
                    @if(request()->anyFilled(['search', 'category', 'participant_type']))
                        <a href="{{ route('admin.competitions.index') }}" class="inline-flex size-9 shrink-0 items-center justify-center rounded-xl border border-zinc-200 text-zinc-600 hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-300" title="Atur Ulang Filter">
                            <flux:icon name="arrow-path" class="size-4" />
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Competition Cards Grid --}}
        @if($competitions->isEmpty())
            <div class="rounded-3xl border border-dashed border-zinc-300 p-12 text-center dark:border-zinc-800 bg-white dark:bg-zinc-900">
                <flux:icon name="trophy" class="mx-auto size-12 text-zinc-400" />
                <h3 class="mt-3 text-sm font-bold text-zinc-900 dark:text-zinc-100">Belum Ada Cabang Lomba Ditemukan</h3>
                <p class="mt-1 text-xs text-zinc-500 max-w-sm mx-auto">
                    Tidak ada data cabang mata lomba yang cocok dengan kriteria filter saat ini.
                </p>
                <div class="mt-4">
                    <a href="{{ route('admin.competitions.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-amber-600 px-4 py-2 text-xs font-bold text-white hover:bg-amber-700">
                        <flux:icon name="plus" class="size-4" />
                        <span>Buat Lomba Pertama</span>
                    </a>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-6">
                @foreach($competitions as $competition)
                    <div class="group flex flex-col justify-between rounded-3xl border border-zinc-200/90 bg-white p-5 shadow-xs transition-all hover:border-amber-400 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900">
                        <div>
                            {{-- Top Header Badges --}}
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <span class="inline-flex items-center rounded-lg bg-zinc-100 px-2.5 py-1 text-xs font-mono font-extrabold text-zinc-800 border border-zinc-200 dark:bg-zinc-800 dark:text-zinc-200 dark:border-zinc-700">
                                    {{ $competition->code }}
                                </span>

                                <div class="flex items-center gap-1.5">
                                    @if($competition->is_published)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                            <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                            Publik
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-zinc-100 px-2 py-0.5 text-[10px] font-bold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                            Draft
                                        </span>
                                    @endif

                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold 
                                        @if($competition->isGroup()) bg-indigo-100 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300
                                        @elseif($competition->isIndividual()) bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300
                                        @else bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300
                                        @endif">
                                        {{ $competition->formatTypeLabel() }}
                                    </span>
                                </div>
                            </div>

                            <h3 class="text-base font-extrabold text-zinc-900 dark:text-zinc-100 group-hover:text-amber-700 dark:group-hover:text-amber-400 transition-colors">
                                {{ $competition->title }}
                            </h3>

                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400 line-clamp-2 leading-relaxed">
                                {{ $competition->description ?? 'Tidak ada ringkasan penjelasan teknis cabang lomba.' }}
                            </p>

                            {{-- Info Indicators --}}
                            <div class="mt-4 pt-3 border-t border-zinc-100 dark:border-zinc-800/80 grid grid-cols-2 gap-2 text-xs">
                                <div class="rounded-xl bg-zinc-50 p-2 dark:bg-zinc-800/50">
                                    <span class="text-[10px] text-zinc-400 block font-medium">Rubrik Kriteria</span>
                                    <span class="font-bold text-zinc-900 dark:text-zinc-200">
                                        {{ $competition->criteria_count }} Kriteria
                                    </span>
                                </div>
                                <div class="rounded-xl bg-zinc-50 p-2 dark:bg-zinc-800/50">
                                    <span class="text-[10px] text-zinc-400 block font-medium">Sesi Jadwal</span>
                                    <span class="font-bold text-zinc-900 dark:text-zinc-200">
                                        {{ $competition->schedules_count }} Sesi Lomba
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Footer Actions --}}
                        <div class="mt-5 pt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between gap-2">
                            <span class="text-[11px] text-zinc-400 font-medium truncate">
                                Formula: <strong class="text-zinc-700 dark:text-zinc-300 capitalize">{{ $competition->scoring_formula === 'average' ? 'Rata-Rata' : 'Akumulasi Total' }}</strong>
                            </span>

                            <div class="flex items-center gap-1.5 shrink-0">
                                <a
                                    href="{{ route('admin.competitions.edit', $competition) }}"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-zinc-200 bg-white px-3 py-1.5 text-xs font-bold text-zinc-700 shadow-xs hover:border-amber-400 hover:bg-amber-50 hover:text-amber-800 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                                >
                                    <flux:icon name="pencil-square" class="size-3.5" />
                                    <span>Atur Rubrik</span>
                                </a>

                                <form method="POST" action="{{ route('admin.competitions.destroy', $competition) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus cabang mata lomba {{ $competition->title }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex size-8 items-center justify-center rounded-xl border border-red-200 text-red-600 hover:bg-red-50 dark:border-red-900/40 dark:text-red-400 dark:hover:bg-red-950/40 cursor-pointer" title="Hapus Lomba">
                                        <flux:icon name="trash" class="size-3.5" />
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $competitions->links() }}
            </div>
        @endif
    </div>
</x-layouts::app>

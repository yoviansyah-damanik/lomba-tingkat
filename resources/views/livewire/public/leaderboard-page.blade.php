<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 space-y-8" wire:poll.15s.visible="refreshStandings">
    {{-- Header Section --}}
    <x-heading-section
        title="Klasemen Perolehan Prestasi"
        description="Pantau perolehan Prestasi Tinggi, Prestasi Baik, dan Prestasi Cukup kontingen LT III Kwarcab Padangsidimpuan 2026 secara berkala."
    >
        <x-slot:breadcrumbs>
            <a href="{{ route('home') }}" wire:navigate class="hover:underline">Beranda</a>
            <span class="mx-2">&rsaquo;</span>
            <span class="text-zinc-800 dark:text-zinc-200">Klasemen Prestasi</span>
        </x-slot:breadcrumbs>

        <x-slot:actions>
            <div class="flex flex-wrap items-center gap-2">
                <flux:button
                    icon="arrow-path"
                    variant="subtle"
                    wire:click="refreshStandings"
                    wire:loading.attr="disabled"
                    title="Perbarui Data Klasemen"
                >
                    Segarkan
                </flux:button>

                @if($isEventMasked && $isAdmin)
                    <flux:button
                        icon="{{ $canUnmask ? 'eye-slash' : 'eye' }}"
                        variant="{{ $canUnmask ? 'primary' : 'subtle' }}"
                        wire:click="toggleUnmask"
                        title="{{ $canUnmask ? 'Sembunyikan kembali identitas asli' : 'Hanya Administrator yang dapat mengintip identitas asli' }}"
                    >
                        {{ $canUnmask ? 'Sensor Aktif' : 'Intip Identitas (Admin)' }}
                    </flux:button>
                @endif
            </div>
        </x-slot:actions>
    </x-heading-section>

    @if($isEventMasked)
        <div class="rounded-2xl border border-purple-200 bg-purple-50/70 p-4 text-xs text-purple-900 dark:border-purple-900/50 dark:bg-purple-950/30 dark:text-purple-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-2.5">
                <span class="inline-flex size-7 items-center justify-center rounded-xl bg-purple-600 text-white text-xs font-bold shrink-0">
                    🔒
                </span>
                <div>
                    <strong class="font-bold">Mode Sensor Identitas Aktif:</strong> Nama regu dan pangkalan disamarkan sesuai protokol Blind Scoring untuk menjaga netralitas dan objektivitas lomba.
                    @if($isAdmin)
                        <span class="block mt-0.5 text-[11px] text-purple-700 dark:text-purple-300 font-semibold">
                            (Anda login sebagai Administrator: Gunakan tombol 'Intip Identitas' di atas untuk melihat nama asli).
                        </span>
                    @endif
                </div>
            </div>
            @if($isAdmin)
                <flux:button
                    icon="{{ $canUnmask ? 'eye-slash' : 'eye' }}"
                    variant="{{ $canUnmask ? 'primary' : 'subtle' }}"
                    size="sm"
                    wire:click="toggleUnmask"
                >
                    {{ $canUnmask ? 'Tutup Sensor' : 'Intip Identitas' }}
                </flux:button>
            @endif
        </div>
    @endif

    {{-- Ringkasan Statistik Perolehan Prestasi & Lomba --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <x-stat-card
            label="Total Pangkalan"
            value="{{ $summary['total_scout_groups'] ?? 0 }}"
            description="Gugus Depan SMP/MTs"
            icon="building-library"
            color="indigo"
        />

        <x-stat-card
            label="Regu Berlaga"
            value="{{ $summary['total_patrols'] ?? 0 }}"
            description="Regu Putra & Putri"
            icon="user-group"
            color="blue"
        />

        <x-stat-card
            label="Prestasi Disahkan"
            value="{{ $summary['total_medals'] ?? 0 }}"
            description="Tinggi, Baik, Cukup (penentu bila total poin sama)"
            icon="trophy"
            color="amber"
        />

        <x-stat-card
            label="Mata Lomba Dinilai"
            value="{{ $summary['completed_competitions_count'] ?? 0 }} / {{ $competitions->count() }}"
            description="Sesi Lomba Terverifikasi"
            icon="clipboard-document-check"
            color="emerald"
        />
    </div>

    {{-- Podium Tiga Besar Regu Berprestasi (Putra & Putri Terpisah) --}}
    @php
        $topBoys = $summary['top_boys_patrols'] ?? collect();
        $topGirls = $summary['top_girls_patrols'] ?? collect();
        $showBoys = $topBoys->isNotEmpty();
        $showGirls = $topGirls->isNotEmpty();
    @endphp

    @if($showBoys || $showGirls)
        <div class="mx-auto w-full max-w-6xl space-y-6">
            <div class="text-center">
                <h3 class="text-2xl font-black tracking-tight text-zinc-900 dark:text-zinc-100 sm:text-3xl">Regu Berprestasi</h3>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400 sm:text-sm">
                    Peringkat dipisahkan untuk Regu Putra dan Regu Putri, ditetapkan berdasarkan hasil penilaian lomba.
                </p>

            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 lg:items-start">
                @if($showBoys)
                    <div class="{{ $showGirls ? '' : 'lg:col-span-2 lg:mx-auto lg:w-full lg:max-w-3xl' }}">
                        <x-podium :items="$topBoys" gender="boys" />
                    </div>
                @endif

                @if($showGirls)
                    <div class="{{ $showBoys ? '' : 'lg:col-span-2 lg:mx-auto lg:w-full lg:max-w-3xl' }}">
                        <x-podium :items="$topGirls" gender="girls" />
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Cara penentuan peringkat umum: terpisah dari tab agar selalu terlihat --}}
    <x-standings.rules />

    {{-- Navigasi Tab & Kontrol Filter Interaktif --}}
    <div class="rounded-2xl border border-zinc-200/80 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-zinc-100 dark:border-zinc-800 pb-3">
            {{-- Tab Switcher --}}
            <div class="flex flex-wrap items-center gap-1.5">
                <flux:button
                    icon="user-group"
                    variant="{{ $activeTab === 'patrols' ? 'primary' : 'subtle' }}"
                    wire:click="setTab('patrols')"
                >
                    Peringkat Regu Penggalang
                </flux:button>

                <flux:button
                    icon="trophy"
                    variant="{{ $activeTab === 'competitions' ? 'primary' : 'subtle' }}"
                    wire:click="setTab('competitions')"
                >
                    Hasil Per Mata Lomba
                </flux:button>
            </div>

            {{-- Filter Kategori Gender --}}
            <div class="flex items-center gap-1 bg-zinc-100 dark:bg-zinc-800 p-1 rounded-xl">
                <button
                    type="button"
                    wire:click="setGender('boys')"
                    class="rounded-lg px-2.5 py-1 text-xs font-semibold transition cursor-pointer {{ $genderFilter === 'boys' ? 'bg-blue-600 text-white shadow-xs' : 'text-zinc-600 dark:text-zinc-400 hover:text-blue-600' }}"
                >
                    Putra
                </button>
                <button
                    type="button"
                    wire:click="setGender('girls')"
                    class="rounded-lg px-2.5 py-1 text-xs font-semibold transition cursor-pointer {{ $genderFilter === 'girls' ? 'bg-rose-600 text-white shadow-xs' : 'text-zinc-600 dark:text-zinc-400 hover:text-rose-600' }}"
                >
                    Putri
                </button>
            </div>
        </div>

        {{-- Bar Pencarian & Pemilihan Lomba --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            @if($activeTab === 'competitions')
                <div class="flex-1 max-w-md">
                    <label class="sr-only">Pilih Cabang Mata Lomba</label>
                    <select
                        wire:model.live="selectedCompetitionId"
                        class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3.5 py-2 text-xs font-medium text-zinc-900 focus:border-amber-500 focus:bg-white focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                    >
                        @foreach($competitions as $c)
                            <option value="{{ $c->id }}">
                                [{{ $c->code }}] {{ $c->title }} ({{ $c->scores_count }} dinilai)
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                <div class="relative flex-1 max-w-md">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Cari nama pangkalan, gugus depan, atau regu..."
                        class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3.5 py-2 text-xs text-zinc-900 focus:border-amber-500 focus:bg-white focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                    />
                    @if($search)
                        <button
                            type="button"
                            wire:click="$set('search', '')"
                            class="absolute right-3 top-2 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 text-sm cursor-pointer"
                        >
                            &times;
                        </button>
                    @endif
                </div>
            @endif

            <div class="text-[11px] text-zinc-500 dark:text-zinc-400 flex flex-wrap items-center gap-2">
                <span>🥇 Prestasi Tinggi</span>
                <span>&bull;</span>
                <span>🥈 Prestasi Baik</span>
                <span>&bull;</span>
                <span>🥉 Prestasi Cukup</span>
            </div>
        </div>
    </div>

    {{-- KLASEMEN PERINGKAT UMUM REGU PENGGALANG (PUTRA & PUTRI) --}}
    @if($activeTab === 'patrols')
        @php
            $boysPatrols = $patrols->where('gender_category', 'boys');
            $girlsPatrols = $patrols->where('gender_category', 'girls');
            $showBoysTable = $genderFilter === 'boys';
            $showGirlsTable = $genderFilter === 'girls';
        @endphp

        <div class="space-y-6">
            {{-- Tabel Regu Putra --}}
            @if($showBoysTable)
                <div class="rounded-2xl border border-blue-200/80 bg-white overflow-hidden shadow-xs dark:border-blue-900/40 dark:bg-zinc-900">
                    <div class="border-b border-blue-100 bg-blue-50/50 px-4 py-3 dark:border-blue-900/30 dark:bg-blue-950/20 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="flex size-6 items-center justify-center rounded-lg bg-blue-600 text-white text-xs font-bold">
                                👦
                            </span>
                            <h4 class="font-extrabold text-xs uppercase tracking-wider text-blue-900 dark:text-blue-300">
                                Klasemen Peringkat Umum Regu Penggalang Putra
                            </h4>
                        </div>
                        <span class="text-[11px] font-mono text-blue-600 dark:text-blue-400 font-bold">
                            {{ $boysPatrols->count() }} Regu
                        </span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="border-b border-zinc-200 bg-zinc-50/80 font-bold uppercase tracking-wider text-zinc-500 dark:border-zinc-800 dark:bg-zinc-800/40">
                                <tr>
                                    <th class="py-3 px-4 text-center w-14">Rank</th>
                                    <th class="py-3 px-3 text-center">NPP</th>
                                    <th class="py-3 px-4">Nama Regu</th>
                                    <th class="py-3 px-4">Pangkalan Sekolah</th>
                                    <th class="py-3 px-3 text-center">Total Poin</th>
                                    <th class="py-3 px-3 text-center">Prestasi Tinggi</th>
                                    <th class="py-3 px-3 text-center">Prestasi Baik</th>
                                    <th class="py-3 px-3 text-center">Prestasi Cukup</th>
                                    <th class="py-3 px-3 text-center">Total Prestasi</th>
                                    <th class="py-3 px-4 text-center">Predikat Umum</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/80">
                                @forelse($boysPatrols as $item)
                                    @php
                                        $isTop1 = $item['rank'] === 1;
                                    @endphp
                                    <tr class="transition hover:bg-zinc-50/60 dark:hover:bg-zinc-800/40 {{ $isTop1 ? 'bg-blue-50/30 dark:bg-blue-950/10' : '' }}">
                                        <td class="py-3 px-4 text-center">
                                            <span class="font-mono {{ $item['rank'] <= 3 ? 'font-extrabold text-blue-600 dark:text-blue-400' : 'text-zinc-500' }}">
                                                #{{ $item['rank'] }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <x-npp-badge :npp="$item['npp'] ?? null" />
                                        </td>
                                        <td class="py-3 px-4 font-bold text-zinc-900 dark:text-zinc-100">
                                            {{ $item['patrol_name'] }}
                                        </td>
                                        <td class="py-3 px-4 text-zinc-600 dark:text-zinc-300">
                                            {{ $item['school_name'] }}
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <span class="inline-block rounded-md border border-emerald-200 bg-emerald-50 px-2 py-0.5 font-mono text-xs font-extrabold text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-300">{{ $item['total_points'] }}</span>
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <span class="inline-flex min-w-6 items-center justify-center rounded-md bg-amber-100 px-1.5 py-0.5 font-mono text-xs font-extrabold text-amber-900 dark:bg-amber-950/60 dark:text-amber-300">
                                                {{ $item['gold'] }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <span class="inline-flex min-w-6 items-center justify-center rounded-md bg-zinc-200 px-1.5 py-0.5 font-mono text-xs font-extrabold text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200">
                                                {{ $item['silver'] }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <span class="inline-flex min-w-6 items-center justify-center rounded-md bg-amber-900/20 px-1.5 py-0.5 font-mono text-xs font-extrabold text-amber-950 dark:bg-amber-900/40 dark:text-amber-200">
                                                {{ $item['bronze'] }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 text-center font-bold text-zinc-700 dark:text-zinc-300 font-mono">
                                            {{ $item['total_medals'] }}
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            @if($item['general_rank_title'])
                                                <span class="inline-flex items-center gap-1 rounded-full {{ $item['rank'] === 1 ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-200' : ($item['rank'] === 2 ? 'bg-zinc-200 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200' : 'bg-amber-900/20 text-amber-950 dark:bg-amber-900/40 dark:text-amber-200') }} px-2.5 py-0.5 text-[11px] font-extrabold">
                                                    @if($item['rank'] === 1)
                                                        <flux:icon name="trophy" class="size-3" />
                                                    @endif
                                                    {{ preg_replace('/\s+(Putra|Putri)$/u', '', $item['general_rank_title']) }}
                                                </span>
                                            @else
                                                <span class="font-mono text-zinc-400 text-xs">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="py-8 text-center text-zinc-500">
                                            Belum ada data regu penggalang putra.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Tabel Regu Putri --}}
            @if($showGirlsTable)
                <div class="rounded-2xl border border-rose-200/80 bg-white overflow-hidden shadow-xs dark:border-rose-900/40 dark:bg-zinc-900">
                    <div class="border-b border-rose-100 bg-rose-50/50 px-4 py-3 dark:border-rose-900/30 dark:bg-rose-950/20 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="flex size-6 items-center justify-center rounded-lg bg-rose-600 text-white text-xs font-bold">
                                👧
                            </span>
                            <h4 class="font-extrabold text-xs uppercase tracking-wider text-rose-900 dark:text-rose-300">
                                Klasemen Peringkat Umum Regu Penggalang Putri
                            </h4>
                        </div>
                        <span class="text-[11px] font-mono text-rose-600 dark:text-rose-400 font-bold">
                            {{ $girlsPatrols->count() }} Regu
                        </span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="border-b border-zinc-200 bg-zinc-50/80 font-bold uppercase tracking-wider text-zinc-500 dark:border-zinc-800 dark:bg-zinc-800/40">
                                <tr>
                                    <th class="py-3 px-4 text-center w-14">Rank</th>
                                    <th class="py-3 px-3 text-center">NPP</th>
                                    <th class="py-3 px-4">Nama Regu</th>
                                    <th class="py-3 px-4">Pangkalan Sekolah</th>
                                    <th class="py-3 px-3 text-center">Total Poin</th>
                                    <th class="py-3 px-3 text-center">Prestasi Tinggi</th>
                                    <th class="py-3 px-3 text-center">Prestasi Baik</th>
                                    <th class="py-3 px-3 text-center">Prestasi Cukup</th>
                                    <th class="py-3 px-3 text-center">Total Prestasi</th>
                                    <th class="py-3 px-4 text-center">Predikat Umum</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/80">
                                @forelse($girlsPatrols as $item)
                                    @php
                                        $isTop1 = $item['rank'] === 1;
                                    @endphp
                                    <tr class="transition hover:bg-zinc-50/60 dark:hover:bg-zinc-800/40 {{ $isTop1 ? 'bg-rose-50/30 dark:bg-rose-950/10' : '' }}">
                                        <td class="py-3 px-4 text-center">
                                            <span class="font-mono {{ $item['rank'] <= 3 ? 'font-extrabold text-rose-600 dark:text-rose-400' : 'text-zinc-500' }}">
                                                #{{ $item['rank'] }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <x-npp-badge :npp="$item['npp'] ?? null" />
                                        </td>
                                        <td class="py-3 px-4 font-bold text-zinc-900 dark:text-zinc-100">
                                            {{ $item['patrol_name'] }}
                                        </td>
                                        <td class="py-3 px-4 text-zinc-600 dark:text-zinc-300">
                                            {{ $item['school_name'] }}
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <span class="inline-block rounded-md border border-emerald-200 bg-emerald-50 px-2 py-0.5 font-mono text-xs font-extrabold text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-300">{{ $item['total_points'] }}</span>
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <span class="inline-flex min-w-6 items-center justify-center rounded-md bg-amber-100 px-1.5 py-0.5 font-mono text-xs font-extrabold text-amber-900 dark:bg-amber-950/60 dark:text-amber-300">
                                                {{ $item['gold'] }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <span class="inline-flex min-w-6 items-center justify-center rounded-md bg-zinc-200 px-1.5 py-0.5 font-mono text-xs font-extrabold text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200">
                                                {{ $item['silver'] }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <span class="inline-flex min-w-6 items-center justify-center rounded-md bg-amber-900/20 px-1.5 py-0.5 font-mono text-xs font-extrabold text-amber-950 dark:bg-amber-900/40 dark:text-amber-200">
                                                {{ $item['bronze'] }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 text-center font-bold text-zinc-700 dark:text-zinc-300 font-mono">
                                            {{ $item['total_medals'] }}
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            @if($item['general_rank_title'])
                                                <span class="inline-flex items-center gap-1 rounded-full {{ $item['rank'] === 1 ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-200' : ($item['rank'] === 2 ? 'bg-zinc-200 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200' : 'bg-amber-900/20 text-amber-950 dark:bg-amber-900/40 dark:text-amber-200') }} px-2.5 py-0.5 text-[11px] font-extrabold">
                                                    @if($item['rank'] === 1)
                                                        <flux:icon name="trophy" class="size-3" />
                                                    @endif
                                                    {{ preg_replace('/\s+(Putra|Putri)$/u', '', $item['general_rank_title']) }}
                                                </span>
                                            @else
                                                <span class="font-mono text-zinc-400 text-xs">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="py-8 text-center text-zinc-500">
                                            Belum ada data regu penggalang putri.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- KONTEN TAB 3: HASIL PER MATA LOMBA --}}
    @if($activeTab === 'competitions')
        <div class="space-y-4">
            @if($selectedComp)
                <div class="rounded-2xl border border-zinc-200/80 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="rounded bg-amber-100 px-2 py-0.5 font-mono text-xs font-bold text-amber-900 dark:bg-amber-950/80 dark:text-amber-200">
                                {{ $selectedComp->code }}
                            </span>
                            <h3 class="text-base font-extrabold text-zinc-900 dark:text-zinc-100">
                                {{ $selectedComp->title }}
                            </h3>
                        </div>
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                            Formula Penilaian: <strong class="text-zinc-700 dark:text-zinc-300">{{ ucfirst($selectedComp->scoring_formula) }}</strong> &bull; Total Kriteria Rubrik: <strong class="text-zinc-700 dark:text-zinc-300">{{ $selectedComp->criteria->count() }}</strong>
                        </p>
                    </div>

                    <div class="text-xs text-zinc-500">
                        Total Regu Dinilai: <strong class="text-zinc-900 dark:text-zinc-100 font-mono">{{ $recap->count() }}</strong>
                    </div>
                </div>

                <div class="rounded-2xl border border-zinc-200/80 bg-white overflow-hidden shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="border-b border-zinc-200 bg-zinc-50/80 font-bold uppercase tracking-wider text-zinc-500 dark:border-zinc-800 dark:bg-zinc-800/40">
                                <tr>
                                    <th class="py-3 px-4 text-center w-14">Rank</th>
                                    <th class="py-3 px-3 text-center">NPP</th>
                                    <th class="py-3 px-4">Nama Regu Peserta</th>
                                    <th class="py-3 px-4">Pangkalan Sekolah</th>
                                    <th class="py-3 px-4 text-center">Predikat Prestasi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/80">
                                @forelse($recap as $score)
                                    @php
                                        $badgeColor = match($score->predicate) {
                                            'prestasi_tinggi' => 'bg-amber-100 text-amber-900 dark:bg-amber-950/60 dark:text-amber-200',
                                            'prestasi_baik' => 'bg-zinc-200 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200',
                                            'prestasi_cukup' => 'bg-amber-900/20 text-amber-950 dark:bg-amber-900/40 dark:text-amber-200',
                                            default => 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400',
                                        };
                                        $predicateLabel = match($score->predicate) {
                                            'prestasi_tinggi' => 'Prestasi Tinggi',
                                            'prestasi_baik' => 'Prestasi Baik',
                                            'prestasi_cukup' => 'Prestasi Cukup',
                                            default => null,
                                        };
                                    @endphp
                                    <tr class="transition hover:bg-zinc-50/60 dark:hover:bg-zinc-800/40">
                                        <td class="py-3 px-4 text-center">
                                            <span class="font-mono font-extrabold {{ $score->rank <= 3 ? 'text-amber-600 dark:text-amber-400' : 'text-zinc-500' }}">
                                                {{ $score->rank ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <x-npp-badge :npp="$score->patrol?->npp" />
                                        </td>
                                        <td class="py-3 px-4 font-bold text-zinc-900 dark:text-zinc-100">
                                            {{ $score->patrol?->display_name ?? $score->patrol?->patrol_name ?? 'Regu' }}
                                        </td>
                                        <td class="py-3 px-4 text-zinc-600 dark:text-zinc-300">
                                            {{ $score->patrol?->display_school ?? $score->patrol?->scoutGroup?->school_name ?? '-' }}
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            @if($predicateLabel)
                                                <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 font-semibold text-[11px] {{ $badgeColor }}">
                                                    <flux:icon name="trophy" class="size-3" />
                                                    {{ $predicateLabel }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-12 text-center text-zinc-500">
                                            <div class="max-w-xs mx-auto">
                                                <flux:icon name="clipboard-document-list" class="size-8 text-zinc-400 mx-auto mb-2" />
                                                <p class="font-semibold text-zinc-700 dark:text-zinc-300">Belum ada penilaian terfinalisasi untuk mata lomba ini.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>

<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 space-y-8" wire:poll.15s.visible="refreshStandings">
    {{-- Header Section --}}
    <x-heading-section
        title="Klasemen Perolehan Prestasi"
        description="Pantau perolehan medali Prestasi Tinggi, Prestasi Baik, dan Prestasi Cukup kontingen LT III Kwarcab Padangsidimpuan 2026 secara berkala."
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

    {{-- Ringkasan Statistik Perolehan Medali & Lomba --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <x-stat-card
            label="Total Pangkalan"
            value="{{ $summary['total_contingents'] ?? 0 }}"
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
            label="Medali Disahkan"
            value="{{ $summary['total_medals'] ?? 0 }}"
            description="Emas, Perak, Perunggu"
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

    {{-- Grand Podium Top 3 Per Kategori (Putra & Putri Terpisah) --}}
    @php
        $topBoys = $summary['top_boys_patrols'] ?? collect();
        $topGirls = $summary['top_girls_patrols'] ?? collect();
        $showBoys = ($genderFilter === 'all' || $genderFilter === 'boys') && $topBoys->isNotEmpty();
        $showGirls = ($genderFilter === 'all' || $genderFilter === 'girls') && $topGirls->isNotEmpty();
    @endphp

    @if($showBoys || $showGirls)
        <div x-data="{ podiumTab: 'all' }" class="space-y-8 max-w-5xl mx-auto w-full">
            {{-- Header Podium --}}
            <div class="text-center max-w-2xl mx-auto">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-4 py-1.5 font-mono text-xs font-bold text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 ring-1 ring-amber-400/50 shadow-xs">
                    <flux:icon name="trophy" class="size-4 text-amber-600 dark:text-amber-400" />
                    PODIUM PERINGKAT UMUM SEMENTARA
                </span>
                <h3 class="mt-2.5 text-2xl font-black text-zinc-900 dark:text-zinc-100 sm:text-3xl tracking-tight">
                    Peringkat Prestasi Regu Putra & Putri
                </h3>
                <p class="mt-1.5 text-xs sm:text-sm text-zinc-500 dark:text-zinc-400">
                    Peringkat umum ditentukan murni berdasarkan perolehan medali dan dipisahkan untuk Regu Putra dan Regu Putri.
                </p>

                {{-- Interactive Switcher Tabs (jika kedua kategori aktif) --}}
                @if($showBoys && $showGirls)
                    <div class="mt-5 inline-flex items-center gap-1.5 p-1 rounded-2xl bg-zinc-100 dark:bg-zinc-800/80 border border-zinc-200/80 dark:border-zinc-700/60 shadow-xs">
                        <flux:button
                            size="sm"
                            icon="trophy"
                            variant="subtle"
                            @click="podiumTab = 'all'"
                            class="transition-all"
                            ::class="podiumTab === 'all' ? 'bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 shadow-xs font-bold' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100'"
                        >
                            Semua Kategori
                        </flux:button>
                        <flux:button
                            size="sm"
                            icon="user-group"
                            variant="subtle"
                            @click="podiumTab = 'boys'"
                            class="transition-all"
                            ::class="podiumTab === 'boys' ? 'bg-blue-600 text-white shadow-xs font-bold' : 'text-zinc-500 hover:text-blue-600 dark:hover:text-blue-300'"
                        >
                            Regu Putra
                        </flux:button>
                        <flux:button
                            size="sm"
                            icon="user-group"
                            variant="subtle"
                            @click="podiumTab = 'girls'"
                            class="transition-all"
                            ::class="podiumTab === 'girls' ? 'bg-rose-600 text-white shadow-xs font-bold' : 'text-zinc-500 hover:text-rose-600 dark:hover:text-rose-300'"
                        >
                            Regu Putri
                        </flux:button>
                    </div>
                @endif
            </div>

            {{-- Podium Regu Putra --}}
            @if($showBoys)
                <div x-show="podiumTab === 'all' || podiumTab === 'boys'" x-transition class="rounded-3xl border border-blue-200/80 bg-gradient-to-b from-blue-50/70 via-indigo-50/20 to-white p-6 sm:p-8 shadow-sm dark:border-blue-900/50 dark:from-blue-950/20 dark:via-zinc-900 dark:to-zinc-900">
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center gap-2 rounded-full bg-blue-100/90 dark:bg-blue-950/80 px-4 py-1.5 text-xs font-bold text-blue-800 dark:text-blue-300 ring-1 ring-blue-300/60 dark:ring-blue-700/50 shadow-xs">
                            <flux:icon name="trophy" class="size-4 text-blue-600 dark:text-blue-400" />
                            <span class="tracking-wider uppercase font-mono font-extrabold text-xs">PERINGKAT UMUM REGU PUTRA</span>
                        </div>
                        <h4 class="mt-2 text-xl sm:text-2xl font-black text-zinc-900 dark:text-zinc-100 tracking-tight">
                            Kontingen Regu Putra Terdepan
                        </h4>
                        <p class="mt-1 text-xs sm:text-sm text-zinc-500 dark:text-zinc-400">
                            Tiga regu putra dengan akumulasi medali prestasi tertinggi
                        </p>
                    </div>

                    {{-- 3D Stepped Olympic Podium Grid --}}
                    <div class="pt-12 sm:pt-14 grid grid-cols-1 gap-7 sm:grid-cols-3 sm:items-end sm:gap-4 w-full">
                        {{-- Juara 2: Prestasi Baik Putra (Kiri pada Desktop) --}}
                        @if(isset($topBoys[1]))
                            @php $second = $topBoys[1]; @endphp
                            <div class="order-2 sm:order-1 flex flex-col items-center">
                                <div class="w-full relative z-0 rounded-t-3xl border border-b-0 border-zinc-200 dark:border-zinc-700 bg-gradient-to-b from-zinc-50 via-white to-zinc-50/50 dark:from-zinc-800/80 dark:via-zinc-900 dark:to-zinc-900 p-5 text-center shadow-xs flex flex-col items-center">
                                    <div class="relative -mt-11 sm:-mt-13 mb-2.5 flex items-center justify-center">
                                        <div class="flex size-16 sm:size-18 items-center justify-center rounded-full bg-gradient-to-tr from-zinc-300 via-zinc-100 to-zinc-200 text-zinc-800 shadow-md ring-4 ring-white dark:ring-zinc-900 font-black text-2xl">
                                            <flux:icon name="sparkles" class="size-8 text-zinc-700 dark:text-zinc-300" />
                                        </div>
                                        <span class="absolute -bottom-1 -right-1 flex size-6 items-center justify-center rounded-full bg-zinc-600 text-white ring-2 ring-white dark:ring-zinc-900 shadow-xs font-black text-xs font-mono">
                                            2
                                        </span>
                                    </div>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-zinc-200/90 dark:bg-zinc-800 px-3 py-0.5 text-xs font-extrabold text-zinc-800 dark:text-zinc-200 whitespace-nowrap">
                                        Regu Prestasi Baik Putra
                                    </span>
                                    <h5 class="mt-2.5 font-black text-base sm:text-lg text-zinc-900 dark:text-zinc-100 line-clamp-1" title="{{ $second['patrol_name'] }}">
                                        {{ $second['patrol_name'] }}
                                    </h5>
                                    <span class="text-xs sm:text-sm font-semibold text-zinc-600 dark:text-zinc-300 line-clamp-1" title="{{ $second['school_name'] }}">
                                        {{ $second['school_name'] }}
                                    </span>
                                    <div class="mt-1 flex items-center gap-1 text-xs text-zinc-400">
                                        <span>Gudep {{ $second['group_number'] }}</span>
                                    </div>
                                    <div class="mt-3 flex items-center justify-center gap-1.5 font-mono text-xs font-bold">
                                        <span class="inline-flex items-center gap-1 rounded-md bg-amber-500 px-2 py-0.5 text-white shadow-xs">
                                            <span>🥇</span>{{ $second['gold'] }}
                                        </span>
                                        <span class="inline-flex items-center gap-1 rounded-md bg-zinc-200 dark:bg-zinc-700 px-2 py-0.5 text-zinc-800 dark:text-zinc-200 shadow-xs">
                                            <span>🥈</span>{{ $second['silver'] }}
                                        </span>
                                        <span class="inline-flex items-center gap-1 rounded-md bg-amber-800 px-2 py-0.5 text-white shadow-xs">
                                            <span>🥉</span>{{ $second['bronze'] }}
                                        </span>
                                    </div>
                                    <div class="mt-2.5 text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                                        Total: <strong class="font-mono text-sm text-zinc-800 dark:text-zinc-200">{{ $second['total_medals'] }}</strong> Prestasi
                                    </div>
                                </div>
                                <div class="hidden sm:flex w-full h-24 sm:h-28 rounded-b-3xl bg-gradient-to-t from-zinc-300 via-zinc-200 to-zinc-100 dark:from-zinc-700 dark:via-zinc-600 dark:to-zinc-500 flex-col items-center justify-center text-zinc-700 dark:text-zinc-200 shadow-xs border border-t-0 border-zinc-200 dark:border-zinc-600">
                                    <span class="text-3xl sm:text-4xl font-black tracking-wider font-mono">2</span>
                                    <span class="text-[11px] font-extrabold tracking-widest uppercase opacity-85">PRESTASI BAIK</span>
                                </div>
                            </div>
                        @else
                            <div class="order-2 sm:order-1 flex flex-col items-center">
                                <div class="w-full rounded-t-3xl border-2 border-b-0 border-dashed border-zinc-200 dark:border-zinc-800 bg-white/50 dark:bg-zinc-900/30 p-5 text-center flex flex-col items-center justify-center min-h-[200px]">
                                    <div class="flex size-14 items-center justify-center rounded-full border-2 border-dashed border-zinc-300 dark:border-zinc-700 text-zinc-400 dark:text-zinc-600 font-black text-2xl mb-2.5">
                                        2
                                    </div>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-zinc-100 dark:bg-zinc-800 px-3 py-1 text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                        Sedang Diperebutkan
                                    </span>
                                    <span class="mt-2 text-xs font-semibold text-zinc-400 dark:text-zinc-500">
                                        Menunggu Hasil Lomba
                                    </span>
                                </div>
                                <div class="hidden sm:flex w-full h-24 sm:h-28 rounded-b-3xl border-2 border-t-0 border-dashed border-zinc-200 dark:border-zinc-800 bg-zinc-100/60 dark:bg-zinc-900/20 flex-col items-center justify-center text-zinc-400 font-mono">
                                    <span class="text-3xl sm:text-4xl font-black">2</span>
                                    <span class="text-[10px] font-bold tracking-widest uppercase">PRESTASI BAIK</span>
                                </div>
                            </div>
                        @endif

                        {{-- Juara 1: Prestasi Tinggi Putra (Tengah - Tertinggi & Terakbar) --}}
                        @if(isset($topBoys[0]))
                            @php $first = $topBoys[0]; @endphp
                            <div class="order-1 sm:order-2 flex flex-col items-center">
                                <div class="w-full relative z-10 rounded-t-3xl border-2 border-b-0 border-amber-400 dark:border-amber-500 bg-gradient-to-b from-amber-50 via-white to-amber-50/30 dark:from-amber-950/40 dark:via-zinc-900 dark:to-zinc-900 p-6 text-center shadow-lg ring-4 ring-amber-400/20 flex flex-col items-center">
                                    <div class="relative -mt-13 sm:-mt-16 mb-3 flex items-center justify-center">
                                        <div class="flex size-20 sm:size-24 items-center justify-center rounded-full bg-gradient-to-tr from-amber-500 via-yellow-300 to-amber-400 text-amber-950 shadow-lg ring-4 ring-white dark:ring-zinc-900">
                                            <flux:icon name="trophy" class="size-10 sm:size-11 text-amber-950" />
                                        </div>
                                        <span class="absolute -bottom-1 -right-1 flex size-8 items-center justify-center rounded-full bg-amber-600 text-white font-black text-sm ring-2 ring-white dark:ring-zinc-900 shadow-md font-mono">
                                            1
                                        </span>
                                    </div>
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/15 border border-amber-500/30 px-3.5 py-1 text-xs font-black uppercase tracking-wider text-amber-900 dark:text-amber-300 whitespace-nowrap">
                                        <flux:icon name="trophy" class="size-3.5 text-amber-600 dark:text-amber-400" />
                                        Regu Prestasi Tinggi Putra
                                    </span>
                                    <h5 class="mt-2.5 font-black text-lg sm:text-xl text-zinc-900 dark:text-zinc-50 tracking-tight line-clamp-1" title="{{ $first['patrol_name'] }}">
                                        {{ $first['patrol_name'] }}
                                    </h5>
                                    <span class="text-xs sm:text-sm font-semibold text-zinc-600 dark:text-zinc-300 line-clamp-1" title="{{ $first['school_name'] }}">
                                        {{ $first['school_name'] }}
                                    </span>
                                    <div class="mt-1 flex items-center gap-1.5 text-xs text-zinc-400">
                                        <span>Gudep {{ $first['group_number'] }}</span>
                                        @if(!empty($first['kwarran_name']))
                                            <span>•</span>
                                            <span>{{ $first['kwarran_name'] }}</span>
                                        @endif
                                    </div>
                                    <div class="mt-3.5 flex items-center justify-center gap-2 font-mono text-xs font-black">
                                        <span class="inline-flex items-center gap-1 rounded-lg bg-amber-500 px-2.5 py-1 text-white shadow-xs" title="Prestasi Tinggi (Emas)">
                                            <span>🥇</span>
                                            <span>{{ $first['gold'] }} Tinggi</span>
                                        </span>
                                        <span class="inline-flex items-center gap-1 rounded-lg bg-zinc-200 dark:bg-zinc-700 px-2.5 py-1 text-zinc-900 dark:text-zinc-100 shadow-xs" title="Prestasi Baik (Perak)">
                                            <span>🥈</span>
                                            <span>{{ $first['silver'] }} Baik</span>
                                        </span>
                                        <span class="inline-flex items-center gap-1 rounded-lg bg-amber-800 px-2.5 py-1 text-white shadow-xs" title="Prestasi Cukup (Perunggu)">
                                            <span>🥉</span>
                                            <span>{{ $first['bronze'] }} Cukup</span>
                                        </span>
                                    </div>
                                    <div class="mt-2.5 text-xs font-semibold text-zinc-600 dark:text-zinc-400">
                                        Total: <strong class="font-mono text-base font-black text-amber-600 dark:text-amber-400">{{ $first['total_medals'] }}</strong> Prestasi Disahkan
                                    </div>
                                </div>
                                <div class="hidden sm:flex w-full h-32 sm:h-36 rounded-b-3xl bg-gradient-to-t from-amber-500 via-amber-400 to-amber-300 dark:from-amber-700 dark:via-amber-600 dark:to-amber-500 flex-col items-center justify-center text-amber-950 dark:text-amber-100 shadow-md border-2 border-t-0 border-amber-400 dark:border-amber-500">
                                    <span class="text-4xl sm:text-5xl font-black tracking-wider font-mono">1</span>
                                    <span class="text-xs font-extrabold tracking-widest uppercase opacity-90">PRESTASI TINGGI</span>
                                </div>
                            </div>
                        @endif

                        {{-- Juara 3: Prestasi Cukup Putra (Kanan pada Desktop) --}}
                        @if(isset($topBoys[2]))
                            @php $third = $topBoys[2]; @endphp
                            <div class="order-3 flex flex-col items-center">
                                <div class="w-full relative z-0 rounded-t-3xl border border-b-0 border-amber-900/20 dark:border-amber-900/40 bg-gradient-to-b from-amber-50/50 via-white to-amber-50/20 dark:from-amber-950/20 dark:via-zinc-900 dark:to-zinc-900 p-5 text-center shadow-xs flex flex-col items-center">
                                    <div class="relative -mt-11 sm:-mt-13 mb-2.5 flex items-center justify-center">
                                        <div class="flex size-16 sm:size-18 items-center justify-center rounded-full bg-gradient-to-tr from-amber-800 via-amber-700 to-amber-600 text-amber-100 shadow-md ring-4 ring-white dark:ring-zinc-900 font-black text-2xl">
                                            <flux:icon name="sparkles" class="size-8 text-amber-200" />
                                        </div>
                                        <span class="absolute -bottom-1 -right-1 flex size-6 items-center justify-center rounded-full bg-amber-800 text-white ring-2 ring-white dark:ring-zinc-900 shadow-xs font-black text-xs font-mono">
                                            3
                                        </span>
                                    </div>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-900/15 dark:bg-amber-900/40 px-3 py-0.5 text-xs font-extrabold text-amber-950 dark:text-amber-200 whitespace-nowrap">
                                        Regu Prestasi Cukup Putra
                                    </span>
                                    <h5 class="mt-2.5 font-black text-base sm:text-lg text-zinc-900 dark:text-zinc-100 line-clamp-1" title="{{ $third['patrol_name'] }}">
                                        {{ $third['patrol_name'] }}
                                    </h5>
                                    <span class="text-xs sm:text-sm font-semibold text-zinc-600 dark:text-zinc-300 line-clamp-1" title="{{ $third['school_name'] }}">
                                        {{ $third['school_name'] }}
                                    </span>
                                    <div class="mt-1 flex items-center gap-1 text-xs text-zinc-400">
                                        <span>Gudep {{ $third['group_number'] }}</span>
                                    </div>
                                    <div class="mt-3 flex items-center justify-center gap-1.5 font-mono text-xs font-bold">
                                        <span class="inline-flex items-center gap-1 rounded-md bg-amber-500 px-2 py-0.5 text-white shadow-xs">
                                            <span>🥇</span>{{ $third['gold'] }}
                                        </span>
                                        <span class="inline-flex items-center gap-1 rounded-md bg-zinc-200 dark:bg-zinc-700 px-2 py-0.5 text-zinc-800 dark:text-zinc-200 shadow-xs">
                                            <span>🥈</span>{{ $third['silver'] }}
                                        </span>
                                        <span class="inline-flex items-center gap-1 rounded-md bg-amber-800 px-2 py-0.5 text-white shadow-xs">
                                            <span>🥉</span>{{ $third['bronze'] }}
                                        </span>
                                    </div>
                                    <div class="mt-2.5 text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                                        Total: <strong class="font-mono text-sm text-zinc-800 dark:text-zinc-200">{{ $third['total_medals'] }}</strong> Prestasi
                                    </div>
                                </div>
                                <div class="hidden sm:flex w-full h-16 sm:h-20 rounded-b-3xl bg-gradient-to-t from-amber-800 via-amber-700 to-amber-600 dark:from-amber-950 dark:via-amber-900 dark:to-amber-800 flex-col items-center justify-center text-amber-100 shadow-xs border border-t-0 border-amber-800/40">
                                    <span class="text-2xl sm:text-3xl font-black tracking-wider font-mono">3</span>
                                    <span class="text-[10px] font-extrabold tracking-widest uppercase opacity-85">PRESTASI CUKUP</span>
                                </div>
                            </div>
                        @else
                            <div class="order-3 flex flex-col items-center">
                                <div class="w-full rounded-t-3xl border-2 border-b-0 border-dashed border-zinc-200 dark:border-zinc-800 bg-white/50 dark:bg-zinc-900/30 p-5 text-center flex flex-col items-center justify-center min-h-[200px]">
                                    <div class="flex size-14 items-center justify-center rounded-full border-2 border-dashed border-zinc-300 dark:border-zinc-700 text-zinc-400 dark:text-zinc-600 font-black text-2xl mb-2.5">
                                        3
                                    </div>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-zinc-100 dark:bg-zinc-800 px-3 py-1 text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                        Sedang Diperebutkan
                                    </span>
                                    <span class="mt-2 text-xs font-semibold text-zinc-400 dark:text-zinc-500">
                                        Menunggu Hasil Lomba
                                    </span>
                                </div>
                                <div class="hidden sm:flex w-full h-16 sm:h-20 rounded-b-3xl border-2 border-t-0 border-dashed border-zinc-200 dark:border-zinc-800 bg-zinc-100/60 dark:bg-zinc-900/20 flex-col items-center justify-center text-zinc-400 font-mono">
                                    <span class="text-2xl sm:text-3xl font-black">3</span>
                                    <span class="text-[10px] font-bold tracking-widest uppercase">PRESTASI CUKUP</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Podium Regu Putri --}}
            @if($showGirls)
                <div x-show="podiumTab === 'all' || podiumTab === 'girls'" x-transition class="rounded-3xl border border-rose-200/80 bg-gradient-to-b from-rose-50/70 via-pink-50/20 to-white p-6 sm:p-8 shadow-sm dark:border-rose-900/50 dark:from-rose-950/20 dark:via-zinc-900 dark:to-zinc-900">
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center gap-2 rounded-full bg-rose-100/90 dark:bg-rose-950/80 px-4 py-1.5 text-xs font-bold text-rose-800 dark:text-rose-300 ring-1 ring-rose-300/60 dark:ring-rose-700/50 shadow-xs">
                            <flux:icon name="trophy" class="size-4 text-rose-600 dark:text-rose-400" />
                            <span class="tracking-wider uppercase font-mono font-extrabold text-xs">PERINGKAT UMUM REGU PUTRI</span>
                        </div>
                        <h4 class="mt-2 text-xl sm:text-2xl font-black text-zinc-900 dark:text-zinc-100 tracking-tight">
                            Kontingen Regu Putri Terdepan
                        </h4>
                        <p class="mt-1 text-xs sm:text-sm text-zinc-500 dark:text-zinc-400">
                            Tiga regu putri dengan akumulasi medali prestasi tertinggi
                        </p>
                    </div>

                    {{-- 3D Stepped Olympic Podium Grid --}}
                    <div class="pt-12 sm:pt-14 grid grid-cols-1 gap-7 sm:grid-cols-3 sm:items-end sm:gap-4 w-full">
                        {{-- Juara 2: Prestasi Baik Putri (Kiri pada Desktop) --}}
                        @if(isset($topGirls[1]))
                            @php $secondG = $topGirls[1]; @endphp
                            <div class="order-2 sm:order-1 flex flex-col items-center">
                                <div class="w-full relative z-0 rounded-t-3xl border border-b-0 border-zinc-200 dark:border-zinc-700 bg-gradient-to-b from-zinc-50 via-white to-zinc-50/50 dark:from-zinc-800/80 dark:via-zinc-900 dark:to-zinc-900 p-5 text-center shadow-xs flex flex-col items-center">
                                    <div class="relative -mt-11 sm:-mt-13 mb-2.5 flex items-center justify-center">
                                        <div class="flex size-16 sm:size-18 items-center justify-center rounded-full bg-gradient-to-tr from-zinc-300 via-zinc-100 to-zinc-200 text-zinc-800 shadow-md ring-4 ring-white dark:ring-zinc-900 font-black text-2xl">
                                            <flux:icon name="sparkles" class="size-8 text-zinc-700 dark:text-zinc-300" />
                                        </div>
                                        <span class="absolute -bottom-1 -right-1 flex size-6 items-center justify-center rounded-full bg-zinc-600 text-white ring-2 ring-white dark:ring-zinc-900 shadow-xs font-black text-xs font-mono">
                                            2
                                        </span>
                                    </div>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-zinc-200/90 dark:bg-zinc-800 px-3 py-0.5 text-xs font-extrabold text-zinc-800 dark:text-zinc-200 whitespace-nowrap">
                                        Regu Prestasi Baik Putri
                                    </span>
                                    <h5 class="mt-2.5 font-black text-base sm:text-lg text-zinc-900 dark:text-zinc-100 line-clamp-1" title="{{ $secondG['patrol_name'] }}">
                                        {{ $secondG['patrol_name'] }}
                                    </h5>
                                    <span class="text-xs sm:text-sm font-semibold text-zinc-600 dark:text-zinc-300 line-clamp-1" title="{{ $secondG['school_name'] }}">
                                        {{ $secondG['school_name'] }}
                                    </span>
                                    <div class="mt-1 flex items-center gap-1 text-xs text-zinc-400">
                                        <span>Gudep {{ $secondG['group_number'] }}</span>
                                    </div>
                                    <div class="mt-3 flex items-center justify-center gap-1.5 font-mono text-xs font-bold">
                                        <span class="inline-flex items-center gap-1 rounded-md bg-amber-500 px-2 py-0.5 text-white shadow-xs">
                                            <span>🥇</span>{{ $secondG['gold'] }}
                                        </span>
                                        <span class="inline-flex items-center gap-1 rounded-md bg-zinc-200 dark:bg-zinc-700 px-2 py-0.5 text-zinc-800 dark:text-zinc-200 shadow-xs">
                                            <span>🥈</span>{{ $secondG['silver'] }}
                                        </span>
                                        <span class="inline-flex items-center gap-1 rounded-md bg-amber-800 px-2 py-0.5 text-white shadow-xs">
                                            <span>🥉</span>{{ $secondG['bronze'] }}
                                        </span>
                                    </div>
                                    <div class="mt-2.5 text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                                        Total: <strong class="font-mono text-sm text-zinc-800 dark:text-zinc-200">{{ $secondG['total_medals'] }}</strong> Prestasi
                                    </div>
                                </div>
                                <div class="hidden sm:flex w-full h-24 sm:h-28 rounded-b-3xl bg-gradient-to-t from-zinc-300 via-zinc-200 to-zinc-100 dark:from-zinc-700 dark:via-zinc-600 dark:to-zinc-500 flex-col items-center justify-center text-zinc-700 dark:text-zinc-200 shadow-xs border border-t-0 border-zinc-200 dark:border-zinc-600">
                                    <span class="text-3xl sm:text-4xl font-black tracking-wider font-mono">2</span>
                                    <span class="text-[11px] font-extrabold tracking-widest uppercase opacity-85">PRESTASI BAIK</span>
                                </div>
                            </div>
                        @else
                            <div class="order-2 sm:order-1 flex flex-col items-center">
                                <div class="w-full rounded-t-3xl border-2 border-b-0 border-dashed border-zinc-200 dark:border-zinc-800 bg-white/50 dark:bg-zinc-900/30 p-5 text-center flex flex-col items-center justify-center min-h-[200px]">
                                    <div class="flex size-14 items-center justify-center rounded-full border-2 border-dashed border-zinc-300 dark:border-zinc-700 text-zinc-400 dark:text-zinc-600 font-black text-2xl mb-2.5">
                                        2
                                    </div>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-zinc-100 dark:bg-zinc-800 px-3 py-1 text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                        Sedang Diperebutkan
                                    </span>
                                    <span class="mt-2 text-xs font-semibold text-zinc-400 dark:text-zinc-500">
                                        Menunggu Hasil Lomba
                                    </span>
                                </div>
                                <div class="hidden sm:flex w-full h-24 sm:h-28 rounded-b-3xl border-2 border-t-0 border-dashed border-zinc-200 dark:border-zinc-800 bg-zinc-100/60 dark:bg-zinc-900/20 flex-col items-center justify-center text-zinc-400 font-mono">
                                    <span class="text-3xl sm:text-4xl font-black">2</span>
                                    <span class="text-[10px] font-bold tracking-widest uppercase">PRESTASI BAIK</span>
                                </div>
                            </div>
                        @endif

                        {{-- Juara 1: Prestasi Tinggi Putri (Tengah - Tertinggi & Terakbar) --}}
                        @if(isset($topGirls[0]))
                            @php $firstG = $topGirls[0]; @endphp
                            <div class="order-1 sm:order-2 flex flex-col items-center">
                                <div class="w-full relative z-10 rounded-t-3xl border-2 border-b-0 border-rose-400 dark:border-rose-500 bg-gradient-to-b from-rose-50 via-white to-rose-50/30 dark:from-rose-950/40 dark:via-zinc-900 dark:to-zinc-900 p-6 text-center shadow-lg ring-4 ring-rose-400/20 flex flex-col items-center">
                                    <div class="relative -mt-13 sm:-mt-16 mb-3 flex items-center justify-center">
                                        <div class="flex size-20 sm:size-24 items-center justify-center rounded-full bg-gradient-to-tr from-rose-500 via-pink-400 to-amber-300 text-rose-950 shadow-lg ring-4 ring-white dark:ring-zinc-900">
                                            <flux:icon name="trophy" class="size-10 sm:size-11 text-rose-950" />
                                        </div>
                                        <span class="absolute -bottom-1 -right-1 flex size-8 items-center justify-center rounded-full bg-rose-600 text-white font-black text-sm ring-2 ring-white dark:ring-zinc-900 shadow-md font-mono">
                                            1
                                        </span>
                                    </div>
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-500/15 border border-rose-500/30 px-3.5 py-1 text-xs font-black uppercase tracking-wider text-rose-900 dark:text-rose-300 whitespace-nowrap">
                                        <flux:icon name="trophy" class="size-3.5 text-rose-600 dark:text-rose-400" />
                                        Regu Prestasi Tinggi Putri
                                    </span>
                                    <h5 class="mt-2.5 font-black text-lg sm:text-xl text-zinc-900 dark:text-zinc-50 tracking-tight line-clamp-1" title="{{ $firstG['patrol_name'] }}">
                                        {{ $firstG['patrol_name'] }}
                                    </h5>
                                    <span class="text-xs sm:text-sm font-semibold text-zinc-600 dark:text-zinc-300 line-clamp-1" title="{{ $firstG['school_name'] }}">
                                        {{ $firstG['school_name'] }}
                                    </span>
                                    <div class="mt-1 flex items-center gap-1.5 text-xs text-zinc-400">
                                        <span>Gudep {{ $firstG['group_number'] }}</span>
                                        @if(!empty($firstG['kwarran_name']))
                                            <span>•</span>
                                            <span>{{ $firstG['kwarran_name'] }}</span>
                                        @endif
                                    </div>
                                    <div class="mt-3.5 flex items-center justify-center gap-2 font-mono text-xs font-black">
                                        <span class="inline-flex items-center gap-1 rounded-lg bg-amber-500 px-2.5 py-1 text-white shadow-xs" title="Prestasi Tinggi (Emas)">
                                            <span>🥇</span>
                                            <span>{{ $firstG['gold'] }} Tinggi</span>
                                        </span>
                                        <span class="inline-flex items-center gap-1 rounded-lg bg-zinc-200 dark:bg-zinc-700 px-2.5 py-1 text-zinc-900 dark:text-zinc-100 shadow-xs" title="Prestasi Baik (Perak)">
                                            <span>🥈</span>
                                            <span>{{ $firstG['silver'] }} Baik</span>
                                        </span>
                                        <span class="inline-flex items-center gap-1 rounded-lg bg-amber-800 px-2.5 py-1 text-white shadow-xs" title="Prestasi Cukup (Perunggu)">
                                            <span>🥉</span>
                                            <span>{{ $firstG['bronze'] }} Cukup</span>
                                        </span>
                                    </div>
                                    <div class="mt-2.5 text-xs font-semibold text-zinc-600 dark:text-zinc-400">
                                        Total: <strong class="font-mono text-base font-black text-amber-600 dark:text-amber-400">{{ $firstG['total_medals'] }}</strong> Prestasi Disahkan
                                    </div>
                                </div>
                                <div class="hidden sm:flex w-full h-32 sm:h-36 rounded-b-3xl bg-gradient-to-t from-rose-500 via-rose-400 to-pink-300 dark:from-rose-700 dark:via-rose-600 dark:to-rose-500 flex-col items-center justify-center text-rose-950 dark:text-rose-100 shadow-md border-2 border-t-0 border-rose-400 dark:border-rose-500">
                                    <span class="text-4xl sm:text-5xl font-black tracking-wider font-mono">1</span>
                                    <span class="text-xs font-extrabold tracking-widest uppercase opacity-90">PRESTASI TINGGI</span>
                                </div>
                            </div>
                        @endif

                        {{-- Juara 3: Prestasi Cukup Putri (Kanan pada Desktop) --}}
                        @if(isset($topGirls[2]))
                            @php $thirdG = $topGirls[2]; @endphp
                            <div class="order-3 flex flex-col items-center">
                                <div class="w-full relative z-0 rounded-t-3xl border border-b-0 border-amber-900/20 dark:border-amber-900/40 bg-gradient-to-b from-amber-50/50 via-white to-amber-50/20 dark:from-amber-950/20 dark:via-zinc-900 dark:to-zinc-900 p-5 text-center shadow-xs flex flex-col items-center">
                                    <div class="relative -mt-11 sm:-mt-13 mb-2.5 flex items-center justify-center">
                                        <div class="flex size-16 sm:size-18 items-center justify-center rounded-full bg-gradient-to-tr from-amber-800 via-amber-700 to-amber-600 text-amber-100 shadow-md ring-4 ring-white dark:ring-zinc-900 font-black text-2xl">
                                            <flux:icon name="sparkles" class="size-8 text-amber-200" />
                                        </div>
                                        <span class="absolute -bottom-1 -right-1 flex size-6 items-center justify-center rounded-full bg-amber-800 text-white ring-2 ring-white dark:ring-zinc-900 shadow-xs font-black text-xs font-mono">
                                            3
                                        </span>
                                    </div>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-900/15 dark:bg-amber-900/40 px-3 py-0.5 text-xs font-extrabold text-amber-950 dark:text-amber-200 whitespace-nowrap">
                                        Regu Prestasi Cukup Putri
                                    </span>
                                    <h5 class="mt-2.5 font-black text-base sm:text-lg text-zinc-900 dark:text-zinc-100 line-clamp-1" title="{{ $thirdG['patrol_name'] }}">
                                        {{ $thirdG['patrol_name'] }}
                                    </h5>
                                    <span class="text-xs sm:text-sm font-semibold text-zinc-600 dark:text-zinc-300 line-clamp-1" title="{{ $thirdG['school_name'] }}">
                                        {{ $thirdG['school_name'] }}
                                    </span>
                                    <div class="mt-1 flex items-center gap-1 text-xs text-zinc-400">
                                        <span>Gudep {{ $thirdG['group_number'] }}</span>
                                    </div>
                                    <div class="mt-3 flex items-center justify-center gap-1.5 font-mono text-xs font-bold">
                                        <span class="inline-flex items-center gap-1 rounded-md bg-amber-500 px-2 py-0.5 text-white shadow-xs">
                                            <span>🥇</span>{{ $thirdG['gold'] }}
                                        </span>
                                        <span class="inline-flex items-center gap-1 rounded-md bg-zinc-200 dark:bg-zinc-700 px-2 py-0.5 text-zinc-800 dark:text-zinc-200 shadow-xs">
                                            <span>🥈</span>{{ $thirdG['silver'] }}
                                        </span>
                                        <span class="inline-flex items-center gap-1 rounded-md bg-amber-800 px-2 py-0.5 text-white shadow-xs">
                                            <span>🥉</span>{{ $thirdG['bronze'] }}
                                        </span>
                                    </div>
                                    <div class="mt-2.5 text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                                        Total: <strong class="font-mono text-sm text-zinc-800 dark:text-zinc-200">{{ $thirdG['total_medals'] }}</strong> Prestasi
                                    </div>
                                </div>
                                <div class="hidden sm:flex w-full h-16 sm:h-20 rounded-b-3xl bg-gradient-to-t from-amber-800 via-amber-700 to-amber-600 dark:from-amber-950 dark:via-amber-900 dark:to-amber-800 flex-col items-center justify-center text-amber-100 shadow-xs border border-t-0 border-amber-800/40">
                                    <span class="text-2xl sm:text-3xl font-black tracking-wider font-mono">3</span>
                                    <span class="text-[10px] font-extrabold tracking-widest uppercase opacity-85">PRESTASI CUKUP</span>
                                </div>
                            </div>
                        @else
                            <div class="order-3 flex flex-col items-center">
                                <div class="w-full rounded-t-3xl border-2 border-b-0 border-dashed border-zinc-200 dark:border-zinc-800 bg-white/50 dark:bg-zinc-900/30 p-5 text-center flex flex-col items-center justify-center min-h-[200px]">
                                    <div class="flex size-14 items-center justify-center rounded-full border-2 border-dashed border-zinc-300 dark:border-zinc-700 text-zinc-400 dark:text-zinc-600 font-black text-2xl mb-2.5">
                                        3
                                    </div>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-zinc-100 dark:bg-zinc-800 px-3 py-1 text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                        Sedang Diperebutkan
                                    </span>
                                    <span class="mt-2 text-xs font-semibold text-zinc-400 dark:text-zinc-500">
                                        Menunggu Hasil Lomba
                                    </span>
                                </div>
                                <div class="hidden sm:flex w-full h-16 sm:h-20 rounded-b-3xl border-2 border-t-0 border-dashed border-zinc-200 dark:border-zinc-800 bg-zinc-100/60 dark:bg-zinc-900/20 flex-col items-center justify-center text-zinc-400 font-mono">
                                    <span class="text-2xl sm:text-3xl font-black">3</span>
                                    <span class="text-[10px] font-bold tracking-widest uppercase">PRESTASI CUKUP</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @endif

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
                    wire:click="setGender('all')"
                    class="rounded-lg px-2.5 py-1 text-xs font-semibold transition cursor-pointer {{ $genderFilter === 'all' ? 'bg-white text-zinc-900 shadow-xs dark:bg-zinc-900 dark:text-zinc-100' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900' }}"
                >
                    Semua
                </button>
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
            $showBoysTable = $genderFilter === 'all' || $genderFilter === 'boys';
            $showGirlsTable = $genderFilter === 'all' || $genderFilter === 'girls';
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
                                    <th class="py-3 px-4">Nama Regu</th>
                                    <th class="py-3 px-4">Pangkalan Sekolah</th>
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
                                        <td class="py-3 px-4 font-bold text-zinc-900 dark:text-zinc-100">
                                            {{ $item['patrol_name'] }}
                                        </td>
                                        <td class="py-3 px-4 text-zinc-600 dark:text-zinc-300">
                                            {{ $item['school_name'] }}
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
                                                    {{ $item['general_rank_title'] }}
                                                </span>
                                            @else
                                                <span class="font-mono text-zinc-400 text-xs">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="py-8 text-center text-zinc-500">
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
                                    <th class="py-3 px-4">Nama Regu</th>
                                    <th class="py-3 px-4">Pangkalan Sekolah</th>
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
                                        <td class="py-3 px-4 font-bold text-zinc-900 dark:text-zinc-100">
                                            {{ $item['patrol_name'] }}
                                        </td>
                                        <td class="py-3 px-4 text-zinc-600 dark:text-zinc-300">
                                            {{ $item['school_name'] }}
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
                                                    {{ $item['general_rank_title'] }}
                                                </span>
                                            @else
                                                <span class="font-mono text-zinc-400 text-xs">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="py-8 text-center text-zinc-500">
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
                                            default => 'Peserta',
                                        };
                                    @endphp
                                    <tr class="transition hover:bg-zinc-50/60 dark:hover:bg-zinc-800/40">
                                        <td class="py-3 px-4 text-center">
                                            <span class="font-mono font-extrabold {{ $score->rank <= 3 ? 'text-amber-600 dark:text-amber-400' : 'text-zinc-500' }}">
                                                {{ $score->rank ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 font-bold text-zinc-900 dark:text-zinc-100">
                                            {{ $score->patrol?->display_name ?? $score->patrol?->patrol_name ?? 'Regu' }}
                                        </td>
                                        <td class="py-3 px-4 text-zinc-600 dark:text-zinc-300">
                                            {{ $score->patrol?->display_school ?? $score->patrol?->scoutGroup?->school_name ?? '-' }}
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 font-semibold text-[11px] {{ $badgeColor }}">
                                                @if($score->rank <= 3)
                                                    <flux:icon name="trophy" class="size-3" />
                                                @endif
                                                {{ $predicateLabel }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-12 text-center text-zinc-500">
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

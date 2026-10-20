<div class="space-y-6">
    {{-- Header Section Konsisten --}}
    <x-heading-section
        title="Cockpit Penjurian"
        subtitle="Portal penilaian resmi dewan juri dengan protokol Blind Scoring dan rubrik terstandarisasi."
    >
        <x-slot:actions>
            <flux:button
                icon="chart-bar"
                variant="subtle"
                href="{{ route('standings', ['tab' => 'competitions']) }}"
                wire:navigate
            >
                Lihat Rekap Nilai
            </flux:button>
        </x-slot:actions>
    </x-heading-section>

    {{-- Banner Protokol Blind Scoring --}}
    <div class="rounded-2xl border border-indigo-200/80 bg-gradient-to-r from-indigo-50/70 via-blue-50/40 to-white p-4.5 shadow-xs dark:border-indigo-900/40 dark:from-indigo-950/30 dark:via-zinc-900 dark:to-zinc-900">
        <div class="flex items-start gap-3">
            <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-xs">
                <flux:icon name="shield-check" class="size-5" />
            </div>
            <div class="space-y-1">
                <h4 class="text-xs font-bold text-indigo-950 dark:text-indigo-200 uppercase tracking-wider">
                    Protokol Blind Scoring Aktif
                </h4>
                <p class="text-xs text-indigo-900/80 dark:text-indigo-300/80 leading-relaxed">
                    Identitas pangkalan sekolah dan nama regu disamarkan sepenuhnya menjadi <strong>Nomor Undian Peserta</strong> pada lembar penilaian. Pastikan penilaian dilakukan secara objektif berdasarkan standar rubrik petunjuk teknis.
                </p>
            </div>
        </div>
    </div>

    {{-- Ringkasan Kartu Statistik (Menggunakan x-stat-card) --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <x-stat-card
            label="Sesi Ditugaskan"
            :value="$stats['total_assigned']"
            unit="Sesi"
            description="Jadwal pos penjurian"
            color="neutral"
            icon="calendar"
        />

        <x-stat-card
            label="Sedang Berlangsung"
            :value="$stats['in_progress']"
            unit="Aktif"
            description="Sesi perlombaan berjalan"
            color="amber"
            icon="clock"
        />

        <x-stat-card
            label="Penilaian Final"
            :value="$stats['finalized_scores']"
            unit="Regu"
            description="Terkunci resmi ke klasemen"
            color="emerald"
            icon="lock-closed"
        />

        <x-stat-card
            label="Draf Tersimpan"
            :value="$stats['draft_scores']"
            unit="Draf"
            description="Menunggu difinalisasi"
            color="indigo"
            icon="document-text"
        />
    </div>

    {{-- Filter & Search Bar --}}
    <div class="rounded-2xl border border-zinc-200/80 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-12">
            <div class="sm:col-span-8">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari mata lomba, arena lokasi, atau kode cabang..."
                    icon="magnifying-glass"
                    clearable
                />
            </div>
            <div class="sm:col-span-4">
                <flux:select wire:model.live="statusFilter">
                    <flux:select.option value="all">Semua Status Jadwal</flux:select.option>
                    <flux:select.option value="scheduled">Dijadwalkan</flux:select.option>
                    <flux:select.option value="in_progress">Sedang Berlangsung</flux:select.option>
                    <flux:select.option value="completed">Selesai</flux:select.option>
                </flux:select>
            </div>
        </div>
    </div>

    {{-- Katalog Jadwal Sesi Penjurian --}}
    <div class="space-y-4">
        @forelse($schedules as $sched)
            <div class="rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-xs transition hover:border-amber-400 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    {{-- Informasi Sesi Lomba --}}
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-md bg-amber-100 px-2 py-0.5 font-mono text-[11px] font-bold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                {{ $sched['competition_code'] }}
                            </span>
                            <span class="rounded-md {{ $sched['gender_category'] === 'boys' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300' }} px-2 py-0.5 text-[11px] font-bold">
                                Regu {{ $sched['gender_category'] === 'boys' ? 'Putra' : 'Putri' }}
                            </span>
                            <span class="rounded-md bg-zinc-100 px-2 py-0.5 text-[11px] font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                Peran: <strong class="text-zinc-900 dark:text-zinc-100">{{ $sched['my_role'] }}</strong>
                            </span>
                            @if($sched['scoring_state'] === 'open')
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300" title="Penilaian dibuka sejak jadwal dimulai hingga 30 menit setelah berakhir">
                                    <flux:icon name="pencil-square" class="size-3" />
                                    Penilaian Dibuka
                                </span>
                            @elseif($sched['scoring_state'] === 'upcoming')
                                <span class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-2 py-0.5 text-[11px] font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400" title="{{ $sched['scoring_message'] }}">
                                    <flux:icon name="clock" class="size-3" />
                                    Belum Dimulai
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2 py-0.5 text-[11px] font-semibold text-rose-700 dark:bg-rose-950/60 dark:text-rose-300" title="{{ $sched['scoring_message'] }}">
                                    <flux:icon name="lock-closed" class="size-3" />
                                    Penilaian Ditutup
                                </span>
                            @endif
                            @if($sched['status'] === 'in_progress')
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                    <span class="size-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Berlangsung
                                </span>
                            @elseif($sched['status'] === 'completed')
                                <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-[11px] font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                    Selesai
                                </span>
                            @else
                                <span class="rounded-full bg-sky-100 px-2 py-0.5 text-[11px] font-semibold text-sky-800 dark:bg-sky-950/60 dark:text-sky-300">
                                    Dijadwalkan
                                </span>
                            @endif
                        </div>

                        <h3 class="text-base sm:text-lg font-bold text-zinc-900 dark:text-zinc-100">
                            {{ $sched['competition_title'] }}
                        </h3>

                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-zinc-500 dark:text-zinc-400">
                            <span class="flex items-center gap-1">
                                <flux:icon name="map-pin" class="size-3.5 text-zinc-400" />
                                Arena: <strong class="text-zinc-700 dark:text-zinc-300">{{ $sched['arena_location'] }}</strong>
                            </span>
                            <span class="flex items-center gap-1">
                                <flux:icon name="clock" class="size-3.5 text-zinc-400" />
                                {{ $sched['start_time'] }} - {{ $sched['end_time'] }} WIB
                            </span>
                            <span class="flex items-center gap-1">
                                <flux:icon name="list-bullet" class="size-3.5 text-zinc-400" />
                                {{ $sched['criteria_count'] }} Kriteria Rubrik
                            </span>
                        </div>
                    </div>

                    {{-- Progres & Tombol Aksi --}}
                    <div class="flex flex-col sm:items-end gap-3 shrink-0">
                        <div class="w-full sm:w-48 text-left sm:text-right space-y-1">
                            <div class="flex justify-between text-xs font-semibold">
                                <span class="text-zinc-500">Progres Final:</span>
                                <span class="text-zinc-900 dark:text-zinc-100">
                                    {{ $sched['finalized_patrols'] }} / {{ $sched['total_patrols'] }} Regu
                                </span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-zinc-100 dark:bg-zinc-800 overflow-hidden">
                                <div
                                    class="h-full rounded-full bg-amber-500 transition-all duration-500"
                                    style="width: {{ $sched['progress_percentage'] }}%"
                                ></div>
                            </div>
                            <p class="text-[11px] text-zinc-400">
                                {{ $sched['scored_patrols'] - $sched['finalized_patrols'] }} draf belum difinalisasi
                            </p>
                        </div>

                        <flux:button
                            variant="primary"
                            icon="pencil-square"
                            href="{{ route('judge.scoring', $sched['id']) }}"
                            wire:navigate
                        >
                            Buka Lembar Penilaian
                        </flux:button>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-zinc-200/80 bg-white p-12 text-center shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                <flux:icon name="clipboard-document-list" class="mx-auto size-12 text-zinc-400" />
                <h3 class="mt-3 text-sm font-bold text-zinc-900 dark:text-zinc-100">
                    Tidak Ada Jadwal Penjurian Ditemukan
                </h3>
                <p class="mt-1 text-xs text-zinc-500">
                    Belum ada sesi penugasan juri yang sesuai dengan kriteria pencarian atau filter yang dipilih.
                </p>
            </div>
        @endforelse
    </div>
</div>

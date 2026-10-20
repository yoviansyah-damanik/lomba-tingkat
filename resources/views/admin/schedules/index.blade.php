<x-layouts::app :title="__('Jadwal Sesi Lomba & Penugasan Juri')">
    <div class="space-y-6">
        {{-- Header & Aksi Cepat --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border border-amber-300 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800 dark:border-amber-500/20 dark:bg-amber-950/40 dark:text-amber-300 mb-2">
                    <span class="size-2 rounded-full bg-amber-600"></span>
                    <span>Modul 5 &bull; Manajemen Jadwal & Penugasan Pos</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-100">
                    Jadwal Sesi Lomba & Penugasan Juri
                </h1>
                <p class="mt-1 text-xs sm:text-sm text-zinc-600 dark:text-zinc-400">
                    Atur jadwal waktu pelaksanaan sesi lomba, lokasi arena pos, penugasan dewan juri, serta sesi presensi kehadiran kontingen.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <a
                    href="{{ route('admin.schedules.create') }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-amber-600 to-amber-700 px-4 py-2.5 text-xs sm:text-sm font-bold text-white shadow-md shadow-amber-600/20 transition-all hover:brightness-105 active:scale-95"
                >
                    <flux:icon name="plus" class="size-4" />
                    <span>Jadwalkan Sesi Lomba</span>
                </a>
            </div>
        </div>

        {{-- Notification Alerts --}}
        <x-alert />

        {{-- Statistik Counter Grid --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
            <x-stat-card
                label="Total Sesi Lomba"
                :value="$stats['total']"
                unit="Sesi"
                color="neutral"
            />
            <x-stat-card
                label="Dijadwalkan"
                :value="$stats['scheduled']"
                unit="Sesi"
                color="blue"
            />
            <x-stat-card
                label="Sedang Berlangsung"
                :value="$stats['in_progress']"
                unit="Aktif Pos"
                color="emerald"
            />
            <x-stat-card
                label="Selesai Dinilai"
                :value="$stats['completed']"
                unit="Tuntas"
                color="neutral"
            />
        </div>

        {{-- Filter & Search Bar --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
            <form method="GET" action="{{ route('admin.schedules.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                <div class="sm:col-span-5">
                    <flux:select name="competition_id" placeholder="Semua Cabang Lomba">
                        <flux:select.option value="" :selected="!request('competition_id')">Semua Cabang Mata Lomba</flux:select.option>
                        @foreach($competitions as $comp)
                            <flux:select.option value="{{ $comp->id }}" :selected="request('competition_id') == $comp->id">
                                {{ $comp->code }} &ndash; {{ $comp->title }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
                <div class="sm:col-span-3">
                    <flux:select name="status" placeholder="Semua Status">
                        <flux:select.option value="all_status" :selected="request('status') === 'all_status'">Semua Status Sesi</flux:select.option>
                        <flux:select.option value="scheduled" :selected="request('status') === 'scheduled'">Dijadwalkan</flux:select.option>
                        <flux:select.option value="in_progress" :selected="request('status') === 'in_progress'">Sedang Berlangsung</flux:select.option>
                        <flux:select.option value="completed" :selected="request('status') === 'completed'">Selesai</flux:select.option>
                        <flux:select.option value="rescheduled" :selected="request('status') === 'rescheduled'">Ditunda</flux:select.option>
                    </flux:select>
                </div>
                <div class="sm:col-span-2">
                    <flux:input
                        name="arena"
                        value="{{ request('arena') }}"
                        placeholder="Cari arena / pos..."
                        icon="map-pin"
                        clearable
                    />
                </div>
                <div class="sm:col-span-2 flex items-center gap-2">
                    <flux:button type="submit" variant="primary" icon="funnel" class="w-full justify-center">
                        Saring
                    </flux:button>
                    @if(request()->anyFilled(['competition_id', 'status', 'arena']))
                        <a href="{{ route('admin.schedules.index') }}" class="inline-flex size-9 shrink-0 items-center justify-center rounded-xl border border-zinc-200 text-zinc-600 hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-300" title="Reset Filter">
                            <flux:icon name="arrow-path" class="size-4" />
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Schedule Cards List --}}
        @if($schedules->isEmpty())
            <div class="rounded-3xl border border-dashed border-zinc-300 p-12 text-center dark:border-zinc-800 bg-white dark:bg-zinc-900">
                <flux:icon name="calendar" class="mx-auto size-12 text-zinc-400" />
                <h3 class="mt-3 text-sm font-bold text-zinc-900 dark:text-zinc-100">Belum Ada Sesi Jadwal Lomba</h3>
                <p class="mt-1 text-xs text-zinc-500 max-w-sm mx-auto">
                    Mata lomba belum memiliki sesi jadwal waktu pelaksanaan atau tidak ada data yang cocok dengan filter.
                </p>
                <div class="mt-4">
                    <a href="{{ route('admin.schedules.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-amber-600 px-4 py-2 text-xs font-bold text-white hover:bg-amber-700">
                        <flux:icon name="plus" class="size-4" />
                        <span>Jadwalkan Sesi Pertama</span>
                    </a>
                </div>
            </div>
        @else
            <div class="space-y-4">
                @foreach($schedules as $schedule)
                    <div class="rounded-3xl border border-zinc-200/90 bg-white p-5 sm:p-6 shadow-xs transition-all hover:border-amber-400 hover:shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                            
                            {{-- Info Sesi & Waktu --}}
                            <div class="space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center rounded-lg bg-amber-100 px-2.5 py-1 text-xs font-mono font-extrabold text-amber-900 border border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800/40">
                                        {{ $schedule->competition->code }}
                                    </span>
                                    <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-[11px] font-bold capitalize {{ $schedule->statusBadge() }}">
                                        {{ str_replace('_', ' ', $schedule->status) }}
                                    </span>
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold {{ $schedule->participantTypeBadgeClasses() }}">
                                        {{ $schedule->participantTypeLabel() }}
                                    </span>
                                    @php
                                        $activeSession = $schedule->activeAttendanceSession();
                                    @endphp
                                    @if($activeSession)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-bold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 animate-pulse">
                                            <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                            Presensi Pos Terbuka
                                        </span>
                                    @endif
                                </div>

                                <div>
                                    <h3 class="text-base sm:text-lg font-extrabold text-zinc-900 dark:text-zinc-100">
                                        {{ $schedule->session_name }}
                                    </h3>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                        Mata Lomba: <strong class="text-zinc-700 dark:text-zinc-300">{{ $schedule->competition->title }}</strong>
                                    </p>
                                </div>

                                <div class="flex flex-wrap items-center gap-4 text-xs text-zinc-600 dark:text-zinc-300 pt-1">
                                    <span class="inline-flex items-center gap-1.5">
                                        <flux:icon name="map-pin" class="size-3.5 text-amber-600 dark:text-amber-400" />
                                        <strong>{{ $schedule->arena_location }}</strong>
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 font-mono">
                                        <flux:icon name="clock" class="size-3.5 text-amber-600 dark:text-amber-400" />
                                        {{ $schedule->start_time->translatedFormat('d M Y, H:i') }} &ndash; {{ $schedule->end_time->translatedFormat('H:i') }} WIB
                                    </span>
                                </div>
                            </div>

                            {{-- Tim Juri & Action Buttons --}}
                            <div class="flex flex-col sm:flex-row lg:flex-col items-start lg:items-end justify-between gap-3 pt-3 lg:pt-0 border-t lg:border-t-0 border-zinc-100 dark:border-zinc-800">
                                <div class="text-start lg:text-end">
                                    <span class="text-[10px] uppercase font-bold text-zinc-400 block tracking-wider">Dewan Juri Bertugas</span>
                                    @if($schedule->judges->isEmpty())
                                        <span class="text-xs text-amber-600 dark:text-amber-400 font-semibold italic">Belum Ada Juri Ditugaskan</span>
                                    @else
                                        <div class="flex flex-wrap items-center justify-start lg:justify-end gap-1.5 mt-1">
                                            @foreach($schedule->judges as $judge)
                                                <span class="inline-flex items-center gap-1 rounded-lg bg-zinc-100 px-2 py-0.5 text-[11px] font-semibold text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200">
                                                    <flux:icon name="user" class="size-3 text-amber-600" />
                                                    <span>{{ $judge->name }}</span>
                                                    <span class="text-[9px] text-zinc-400">({{ $judge->pivot->role === 'lead_judge' ? 'Ketua' : 'Juri' }})</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2">
                                    <a
                                        href="{{ route('admin.schedules.edit', $schedule) }}"
                                        class="inline-flex items-center gap-1.5 rounded-xl border border-zinc-200 bg-white px-3 py-1.5 text-xs font-bold text-zinc-700 shadow-xs hover:border-amber-400 hover:bg-amber-50 hover:text-amber-800 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                                    >
                                        <flux:icon name="user-plus" class="size-3.5" />
                                        <span>Atur Juri & Pos</span>
                                    </a>

                                    <form method="POST" action="{{ route('admin.schedules.destroy', $schedule) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal {{ $schedule->session_name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex size-8 items-center justify-center rounded-xl border border-red-200 text-red-600 hover:bg-red-50 dark:border-red-900/40 dark:text-red-400 dark:hover:bg-red-950/40 cursor-pointer" title="Hapus Jadwal">
                                            <flux:icon name="trash" class="size-3.5" />
                                        </button>
                                    </form>
                                </div>
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $schedules->links() }}
            </div>
        @endif
    </div>
</x-layouts::app>

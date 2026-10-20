@php
    $totalSessions = $groups->sum(fn ($items) => $items->count());
    $liveSessions = $groups->flatten()->where('status', 'in_progress')->count();
@endphp

<div class="flex h-full w-full flex-1 flex-col gap-6">
    <x-heading-section
        icon="calendar-days"
        title="Jadwal Kegiatan"
        description="Sesi lomba yang sedang berlangsung dan yang terdekat, berurutan dari waktu paling dekat. Waktu resmi WIB."
    >
        <x-slot:breadcrumbs>
            <a href="{{ route('dashboard') }}" wire:navigate class="hover:underline">Dasbor</a>
            <span class="mx-2">&rsaquo;</span>
            <span class="text-zinc-800 dark:text-zinc-200">Jadwal Kegiatan</span>
        </x-slot:breadcrumbs>

        <x-slot:actions>
            <div class="flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-3 py-1.5 text-xs text-zinc-600 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400">
                <span class="size-2 animate-pulse rounded-full bg-emerald-500"></span>
                <span>Waktu Resmi: <strong>WIB</strong></span>
            </div>
        </x-slot:actions>
    </x-heading-section>

    {{-- Ringkasan --}}
    @if ($totalSessions)
        <div class="grid grid-cols-3 gap-3">
            <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400">Total Sesi</p>
                <p class="mt-1 text-2xl font-extrabold text-zinc-900 dark:text-white">{{ $totalSessions }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400">Hari Kegiatan</p>
                <p class="mt-1 text-2xl font-extrabold text-zinc-900 dark:text-white">{{ $groups->count() }}</p>
            </div>
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 shadow-xs dark:border-emerald-900 dark:bg-emerald-950/30">
                <p class="flex items-center gap-1.5 text-[11px] font-medium text-emerald-700 dark:text-emerald-400">
                    @if ($liveSessions)
                        <span class="size-1.5 animate-pulse rounded-full bg-emerald-500"></span>
                    @endif
                    Berlangsung
                </p>
                <p class="mt-1 text-2xl font-extrabold text-emerald-700 dark:text-emerald-300">{{ $liveSessions }}</p>
            </div>
        </div>
    @endif

    @forelse ($groups as $date => $schedules)
        @php
            $day = \Illuminate\Support\Carbon::parse($date)->locale('id');
            $dayLabel = $day->isToday() ? 'Hari ini' : ($day->isTomorrow() ? 'Besok' : null);
        @endphp

        <section wire:key="day-{{ $date }}" class="space-y-3">
            {{-- Header tanggal --}}
            <div class="flex items-center gap-3">
                <div class="flex size-12 shrink-0 flex-col items-center justify-center rounded-xl {{ $day->isToday() ? 'bg-amber-600 text-white' : 'bg-amber-500/10 text-amber-700 dark:text-amber-400' }}">
                    <span class="text-lg font-extrabold leading-none">{{ $day->isoFormat('D') }}</span>
                    <span class="mt-0.5 text-[10px] font-semibold uppercase leading-none">{{ $day->isoFormat('MMM') }}</span>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ $day->isoFormat('dddd, D MMMM Y') }}</h2>
                        @if ($dayLabel)
                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">{{ $dayLabel }}</span>
                        @endif
                    </div>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400">{{ $schedules->count() }} sesi lomba</p>
                </div>
                <div class="hidden h-px flex-1 bg-zinc-200 dark:bg-zinc-800 sm:block"></div>
            </div>

            <div class="space-y-3">
                @foreach ($schedules as $schedule)
                    <x-schedule-card :schedule="$schedule" wire:key="schedule-{{ $schedule->id }}" />
                @endforeach
            </div>
        </section>
    @empty
        <div class="flex flex-col items-center gap-3 rounded-2xl border border-dashed border-zinc-300 p-12 text-center dark:border-zinc-800">
            <div class="flex size-12 items-center justify-center rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                <flux:icon name="calendar-days" class="size-6" />
            </div>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Tidak ada sesi yang akan datang.</p>
        </div>
    @endforelse
</div>

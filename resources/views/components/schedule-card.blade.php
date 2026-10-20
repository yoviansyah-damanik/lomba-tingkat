@props(['schedule'])

@php
    $start = $schedule->start_time;
    $end = $schedule->end_time;
    $accent = match ($schedule->status) {
        'in_progress' => 'bg-emerald-500',
        'completed' => 'bg-zinc-300 dark:bg-zinc-700',
        'cancelled' => 'bg-red-500',
        'rescheduled' => 'bg-amber-500',
        default => 'bg-blue-500',
    };
@endphp

{{-- Kartu jadwal sesi lomba: mobile-first (waktu di atas), kolom waktu di kiri pada layar sm ke atas --}}
<article class="relative overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-xs transition-colors hover:border-amber-400 dark:border-zinc-800 dark:bg-zinc-900">
    <span class="absolute inset-y-0 left-0 w-1 {{ $accent }}"></span>

    <div class="flex flex-col sm:flex-row">
        {{-- Waktu --}}
        <div class="flex items-center justify-between gap-3 border-b border-zinc-100 py-3 pl-5 pr-4 dark:border-zinc-800 sm:w-40 sm:shrink-0 sm:flex-col sm:items-start sm:justify-center sm:border-b-0 sm:border-r sm:py-5">
            <div>
                <p class="font-mono text-2xl font-extrabold leading-none text-zinc-900 dark:text-white">{{ $start->format('H:i') }}</p>
                <p class="mt-1 font-mono text-[11px] text-zinc-500 dark:text-zinc-400">s/d {{ $end->format('H:i') }} WIB</p>
            </div>
            <p class="text-right text-[11px] font-medium leading-tight text-zinc-500 dark:text-zinc-400 sm:text-left">
                {{ $start->copy()->locale('id')->isoFormat('dddd') }}<br>
                {{ $start->copy()->locale('id')->isoFormat('D MMM Y') }}
            </p>
        </div>

        {{-- Detail --}}
        <div class="min-w-0 flex-1 space-y-3 py-4 pl-5 pr-4 sm:pl-6 sm:pr-5">
            <div class="min-w-0">
                <p class="truncate text-[11px] font-semibold text-amber-700 dark:text-amber-400">
                    <span class="font-mono font-bold">{{ $schedule->competition?->code }}</span>
                    <span class="mx-1 text-zinc-300 dark:text-zinc-700">&bull;</span>
                    {{ $schedule->competition?->title }}
                </p>
                <h3 class="mt-0.5 text-base font-bold leading-snug text-zinc-900 dark:text-zinc-100">
                    {{ $schedule->session_name }}
                </h3>
            </div>

            <div class="flex flex-wrap items-center gap-1.5">
                <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-[10px] font-semibold {{ $schedule->statusBadge() }}">
                    {{ $schedule->statusLabel() }}
                </span>
                <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-[10px] font-semibold {{ $schedule->genderBadgeClasses() }}">
                    {{ $schedule->genderLabel() }}
                </span>
                <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-[10px] font-semibold {{ $schedule->participantTypeBadgeClasses() }}">
                    {{ $schedule->participantTypeLabel() }}
                </span>
            </div>

            <div class="flex flex-col gap-1.5 text-xs text-zinc-600 dark:text-zinc-400 sm:flex-row sm:flex-wrap sm:items-center sm:gap-x-5">
                <span class="flex items-start gap-1.5">
                    <flux:icon name="map-pin" class="mt-px size-4 shrink-0 text-amber-600" />
                    <span class="min-w-0 break-words">{{ $schedule->arena_location }}</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <flux:icon name="qr-code" class="size-4 shrink-0 text-amber-600" />
                    <span>Presensi dibuka
                        <strong class="font-mono font-semibold text-amber-700 dark:text-amber-400">{{ $start->copy()->subMinutes(30)->format('H:i') }} WIB</strong>
                    </span>
                </span>
            </div>
        </div>
    </div>
</article>

@props(['schedule'])

@php
    $start = $schedule->start_time;
    $end = $schedule->end_time;
    $isLive = $schedule->status === 'in_progress';
    $isDone = in_array($schedule->status, ['completed', 'cancelled'], true);

    $accent = match ($schedule->status) {
        'in_progress' => 'bg-emerald-500',
        'completed' => 'bg-zinc-300 dark:bg-zinc-700',
        'cancelled' => 'bg-red-500',
        'rescheduled' => 'bg-amber-500',
        default => 'bg-blue-500',
    };
    $timePanel = $isLive
        ? 'bg-emerald-50 dark:bg-emerald-950/30'
        : 'bg-zinc-50 dark:bg-zinc-800/40';
@endphp

{{-- Kartu jadwal sesi lomba: mobile-first (waktu di atas), panel waktu di kiri pada layar sm ke atas --}}
<article class="group relative overflow-hidden rounded-2xl border bg-white shadow-xs transition-all hover:-translate-y-0.5 hover:border-amber-400 hover:shadow-md dark:bg-zinc-900 {{ $isLive ? 'border-emerald-300 dark:border-emerald-800' : 'border-zinc-200 dark:border-zinc-800' }} {{ $isDone ? 'opacity-75' : '' }}">
    <span class="absolute inset-y-0 left-0 w-1 {{ $accent }}"></span>

    <div class="flex flex-col sm:flex-row">
        {{-- Panel waktu --}}
        <div class="flex items-center justify-between gap-3 border-b border-zinc-100 py-3 pl-5 pr-4 dark:border-zinc-800 sm:w-44 sm:shrink-0 sm:flex-col sm:items-start sm:justify-center sm:gap-1 sm:border-b-0 sm:border-r sm:py-5 {{ $timePanel }}">
            <div>
                <p class="font-mono text-2xl font-extrabold leading-none tracking-tight text-zinc-900 dark:text-white">{{ $start->format('H:i') }}</p>
                <p class="mt-1.5 flex items-center gap-1 font-mono text-[11px] text-zinc-500 dark:text-zinc-400">
                    <flux:icon name="arrow-right" class="size-3" />
                    {{ $end->format('H:i') }} WIB
                </p>
            </div>
            <p class="text-right text-[11px] font-medium leading-tight text-zinc-500 dark:text-zinc-400 sm:mt-2 sm:text-left">
                {{ $start->copy()->locale('id')->isoFormat('dddd') }}<br>
                {{ $start->copy()->locale('id')->isoFormat('D MMM Y') }}
            </p>
        </div>

        {{-- Detail --}}
        <div class="min-w-0 flex-1 space-y-3 py-4 pl-5 pr-4 sm:pl-6 sm:pr-5">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="truncate text-[11px] font-semibold text-amber-700 dark:text-amber-400">
                        <span class="rounded bg-amber-500/10 px-1.5 py-0.5 font-mono font-bold">{{ $schedule->competition?->code }}</span>
                        <span class="ml-1">{{ $schedule->competition?->title }}</span>
                    </p>
                    <h3 class="mt-1.5 text-base font-bold leading-snug text-zinc-900 dark:text-zinc-100">
                        {{ $schedule->session_name }}
                    </h3>
                </div>

                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full border px-2.5 py-1 text-[10px] font-semibold {{ $schedule->statusBadge() }}">
                    @if ($isLive)
                        <span class="size-1.5 animate-pulse rounded-full bg-emerald-500"></span>
                    @endif
                    {{ $schedule->statusLabel() }}
                </span>
            </div>

            <div class="flex flex-wrap items-center gap-1.5">
                <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-[10px] font-semibold {{ $schedule->genderBadgeClasses() }}">
                    {{ $schedule->genderLabel() }}
                </span>
                <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-[10px] font-semibold {{ $schedule->participantTypeBadgeClasses() }}">
                    {{ $schedule->participantTypeLabel() }}
                </span>
            </div>

            <div class="flex flex-col gap-2 border-t border-zinc-100 pt-3 text-xs text-zinc-600 dark:border-zinc-800 dark:text-zinc-400 sm:flex-row sm:flex-wrap sm:items-center sm:gap-x-5">
                <span class="flex items-start gap-2">
                    <span class="flex size-6 shrink-0 items-center justify-center rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400">
                        <flux:icon name="map-pin" class="size-3.5" />
                    </span>
                    <span class="min-w-0 break-words pt-0.5">{{ $schedule->arena_location }}</span>
                </span>
                <span class="flex items-center gap-2">
                    <span class="flex size-6 shrink-0 items-center justify-center rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400">
                        <flux:icon name="qr-code" class="size-3.5" />
                    </span>
                    <span>Presensi dibuka
                        <strong class="font-mono font-semibold text-amber-700 dark:text-amber-400">{{ $start->copy()->subMinutes(30)->format('H:i') }} WIB</strong>
                    </span>
                </span>
                @unless ($isDone)
                    <flux:dropdown position="bottom" align="end" class="sm:ml-auto">
                        <flux:button icon="calendar-days" icon:trailing="chevron-down" size="sm" variant="subtle">
                            Tambah ke Kalender
                        </flux:button>

                        <flux:menu>
                            <flux:menu.item icon="arrow-top-right-on-square" href="{{ app(\App\Services\CalendarLinkService::class)->googleUrl($schedule) }}" target="_blank" rel="noopener">
                                Google Calendar
                            </flux:menu.item>
                            <flux:menu.item icon="arrow-down-tray" href="{{ route('schedules.calendar.show', $schedule->id) }}">
                                Unduh .ics (Apple / Outlook)
                            </flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                @endunless
            </div>
        </div>
    </div>
</article>

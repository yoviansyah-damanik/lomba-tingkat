@php
    $sessions = $today->isNotEmpty() ? $today : $upcoming;
    $heading = $today->isNotEmpty() ? 'Agenda Hari Ini' : 'Agenda Terdekat';
@endphp

<section class="rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
    <div class="flex items-center justify-between gap-3 border-b border-zinc-100 pb-3 dark:border-zinc-800">
        <div class="flex items-center gap-2">
            <flux:icon name="calendar-days" class="size-5 text-amber-600 dark:text-amber-400" />
            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ $heading }}</h3>
            @if ($today->isNotEmpty())
                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">{{ now()->timezone('Asia/Jakarta')->locale('id')->isoFormat('dddd, D MMM') }}</span>
            @endif
        </div>
        <flux:button href="{{ route('agenda.index') }}" wire:navigate size="xs" variant="subtle" icon-trailing="arrow-right">Semua Jadwal</flux:button>
    </div>

    <ul class="mt-3 divide-y divide-zinc-100 dark:divide-zinc-800">
        @forelse ($sessions as $schedule)
            <li wire:key="agenda-{{ $schedule->id }}" class="flex items-start gap-3 py-3">
                <div class="w-20 shrink-0 text-center">
                    <p class="font-mono text-sm font-extrabold text-zinc-900 dark:text-zinc-100">{{ $schedule->start_time->timezone('Asia/Jakarta')->format('H:i') }}</p>
                    <p class="text-[10px] text-zinc-400">s.d. {{ $schedule->end_time->timezone('Asia/Jakarta')->format('H:i') }}</p>
                    @if ($today->isEmpty())
                        <p class="mt-0.5 text-[10px] font-semibold text-amber-700 dark:text-amber-400">{{ $schedule->start_time->timezone('Asia/Jakarta')->locale('id')->isoFormat('ddd, D MMM') }}</p>
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $schedule->session_name }}</p>
                    <p class="truncate text-[11px] text-zinc-500">{{ $schedule->arena_location ?: 'Lokasi belum ditentukan' }}</p>
                </div>
                <span class="shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-semibold {{ $schedule->statusBadge() }}">{{ $schedule->statusLabel() }}</span>
            </li>
        @empty
            <li class="py-8 text-center text-xs italic text-zinc-400">Belum ada agenda yang dijadwalkan.</li>
        @endforelse
    </ul>
</section>

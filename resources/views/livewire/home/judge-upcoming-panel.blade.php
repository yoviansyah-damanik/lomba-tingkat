<section class="rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
    <div class="flex items-center justify-between gap-3 border-b border-zinc-100 pb-3 dark:border-zinc-800">
        <div class="flex items-center gap-2">
            <flux:icon name="trophy" class="size-5 text-amber-600 dark:text-amber-400" />
            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Lomba yang Akan Datang</h3>
            <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-[10px] font-bold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">Penugasan Anda</span>
        </div>
        <flux:button href="{{ route('judge.index') }}" wire:navigate size="xs" variant="subtle" icon-trailing="arrow-right">Cockpit Penjurian</flux:button>
    </div>

    <ul class="mt-3 divide-y divide-zinc-100 dark:divide-zinc-800">
        @forelse ($sessions as $schedule)
            @php
                $start = $schedule->start_time->timezone('Asia/Jakarta')->locale('id');
                $isLive = $schedule->status === 'in_progress';
            @endphp
            <li wire:key="judge-upcoming-{{ $schedule->id }}" class="flex flex-wrap items-center gap-3 py-3">
                <div class="w-24 shrink-0 text-center">
                    <p class="text-[10px] font-semibold uppercase text-amber-700 dark:text-amber-400">{{ $start->isToday() ? 'Hari ini' : $start->isoFormat('ddd, D MMM') }}</p>
                    <p class="font-mono text-sm font-extrabold text-zinc-900 dark:text-zinc-100">{{ $start->format('H:i') }}</p>
                    <p class="text-[10px] text-zinc-400">s.d. {{ $schedule->end_time->timezone('Asia/Jakarta')->format('H:i') }}</p>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $schedule->competition?->title ?? $schedule->session_name }}</p>
                    <p class="truncate text-[11px] text-zinc-500">
                        {{ $schedule->gender_category === 'girls' ? 'Penggalang Putri' : 'Penggalang Putra' }}
                        &bull; {{ $schedule->arena_location ?: 'Lokasi belum ditentukan' }}
                    </p>
                </div>
                <span class="shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-semibold {{ $schedule->statusBadge() }}">{{ $schedule->statusLabel() }}</span>
                @if ($isLive || $schedule->isScoringOpen())
                    <flux:button href="{{ route('judge.scoring', $schedule->id) }}" wire:navigate size="xs" variant="primary" icon="pencil-square">Nilai</flux:button>
                @endif
            </li>
        @empty
            <li class="py-8 text-center text-xs italic text-zinc-400">Tidak ada lomba mendatang pada penugasan Anda.</li>
        @endforelse
    </ul>
</section>

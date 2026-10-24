<section class="rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-100 pb-3 dark:border-zinc-800">
        <div class="flex items-center gap-2">
            <flux:icon name="clipboard-document-check" class="size-5 text-amber-600 dark:text-amber-400" />
            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Kemajuan Pendaftaran</h3>
        </div>
        @if ($group)
            <span class="text-xs font-semibold text-zinc-500">{{ $done }} dari {{ $total }} langkah selesai</span>
        @endif
    </div>

    @if (! $group)
        <p class="py-8 text-center text-xs italic text-zinc-400">Data pangkalan belum tersedia.</p>
    @else
        <div class="mt-3 h-2 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800" role="progressbar" aria-valuenow="{{ $done }}" aria-valuemin="0" aria-valuemax="{{ $total }}">
            <div class="h-full rounded-full bg-emerald-500 transition-all" style="width: {{ $total ? round($done / $total * 100) : 0 }}%"></div>
        </div>

        <ul class="mt-3 divide-y divide-zinc-100 dark:divide-zinc-800">
            @foreach ($steps as $index => $step)
                <li wire:key="step-{{ $index }}" class="flex items-center gap-3 py-2.5">
                    <span class="flex size-6 shrink-0 items-center justify-center rounded-full {{ $step['done'] ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300' }}">
                        <flux:icon :name="$step['done'] ? 'check' : 'exclamation-triangle'" class="size-3.5" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $step['label'] }}</p>
                        <p class="text-[11px] text-zinc-500">{{ $step['detail'] }}</p>
                    </div>
                    @unless ($step['done'])
                        <flux:button href="{{ $step['url'] }}" wire:navigate size="xs" variant="primary">Lengkapi</flux:button>
                    @endunless
                </li>
            @endforeach
        </ul>
    @endif
</section>

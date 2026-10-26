@props([
    'items',
    'gender' => 'boys', // boys | girls
])

{{-- Papan peringkat tiga besar satu kategori regu: tiga kartu bersusun, tanpa rincian perolehan --}}
@php
    $isBoys = $gender === 'boys';
    $label = $isBoys ? 'Putra' : 'Putri';
    $accent = $isBoys
        ? ['panel' => 'border-blue-200/80 dark:border-blue-900/50', 'chip' => 'bg-blue-100 text-blue-800 ring-blue-300/60 dark:bg-blue-950/70 dark:text-blue-300 dark:ring-blue-700/50', 'icon' => 'text-blue-600 dark:text-blue-400']
        : ['panel' => 'border-rose-200/80 dark:border-rose-900/50', 'chip' => 'bg-rose-100 text-rose-800 ring-rose-300/60 dark:bg-rose-950/70 dark:text-rose-300 dark:ring-rose-700/50', 'icon' => 'text-rose-600 dark:text-rose-400'];

    $ranks = [
        1 => ['badge' => 'bg-gradient-to-br from-yellow-300 via-amber-400 to-amber-500 text-amber-950', 'card' => 'border-amber-300 bg-gradient-to-r from-amber-50 via-white to-white dark:border-amber-500/50 dark:from-amber-950/30 dark:via-zinc-900 dark:to-zinc-900 ring-2 ring-amber-300/40', 'pill' => 'bg-amber-500/15 text-amber-900 border-amber-500/30 dark:text-amber-300', 'name' => 'text-xl sm:text-2xl', 'pad' => 'p-5 sm:p-6', 'size' => 'size-16 sm:size-20 text-3xl sm:text-4xl'],
        2 => ['badge' => 'bg-gradient-to-br from-zinc-100 via-zinc-300 to-zinc-400 text-zinc-800', 'card' => 'border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900', 'pill' => 'bg-zinc-200/80 text-zinc-800 border-zinc-300/70 dark:bg-zinc-800 dark:text-zinc-200 dark:border-zinc-600', 'name' => 'text-lg', 'pad' => 'p-4 sm:p-5', 'size' => 'size-14 text-2xl'],
        3 => ['badge' => 'bg-gradient-to-br from-orange-300 via-amber-600 to-amber-800 text-white', 'card' => 'border-amber-200/70 bg-white dark:border-amber-900/50 dark:bg-zinc-900', 'pill' => 'bg-amber-800/10 text-amber-900 border-amber-800/25 dark:text-amber-400', 'name' => 'text-lg', 'pad' => 'p-4 sm:p-5', 'size' => 'size-14 text-2xl'],
    ];
@endphp

<section class="rounded-3xl border {{ $accent['panel'] }} bg-white p-5 shadow-xs dark:bg-zinc-900/60 sm:p-7">
    <header class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <span class="inline-flex size-10 items-center justify-center rounded-2xl ring-1 {{ $accent['chip'] }}">
                <flux:icon name="trophy" class="size-5 {{ $accent['icon'] }}" />
            </span>
            <div>
                <h4 class="text-lg font-black tracking-tight text-zinc-900 dark:text-zinc-100">Regu {{ $label }} Berprestasi</h4>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Tiga besar kategori Penggalang {{ $label }}</p>
            </div>
        </div>
        <span class="rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-wider ring-1 {{ $accent['chip'] }}">{{ $label }}</span>
    </header>

    <div class="space-y-3">
        @foreach([1, 2, 3] as $rank)
            @php
                $item = $items[$rank - 1] ?? null;
                $style = $ranks[$rank];
            @endphp

            @if($item)
                @php
                    $kwarran = (string) ($item['kwarran_name'] ?? '');
                    $kwarran = ($kwarran === '' || \Illuminate\Support\Str::startsWith($kwarran, 'Kwarran') || $kwarran === 'Dirahasiakan') ? $kwarran : 'Kwarran '.$kwarran;
                    $predicate = preg_replace('/\s+(Putra|Putri)$/u', '', (string) ($item['general_rank_title'] ?? ''));
                @endphp

                <article class="relative flex items-center gap-4 overflow-hidden rounded-2xl border {{ $style['card'] }} {{ $style['pad'] }} shadow-xs">
                    <div class="flex shrink-0 items-center justify-center rounded-full font-mono font-black shadow-md ring-4 ring-white dark:ring-zinc-900 {{ $style['badge'] }} {{ $style['size'] }}">
                        {{ $rank }}
                    </div>

                    <div class="min-w-0 flex-1">
                        @if($predicate)
                            <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider {{ $style['pill'] }}">
                                @if($rank === 1)
                                    <flux:icon name="trophy" class="size-3" />
                                @endif
                                {{ $predicate }}
                            </span>
                        @endif

                        <h5 class="mt-1.5 font-black leading-tight tracking-tight text-zinc-900 dark:text-zinc-50 {{ $style['name'] }}">{{ $item['patrol_name'] }}</h5>
                        <x-npp-badge :npp="$item['npp'] ?? null" :hide="$item['is_masked'] ?? false" />
                        <p class="text-sm font-semibold text-zinc-600 dark:text-zinc-300">{{ $item['school_name'] }}</p>
                        <p class="mt-0.5 flex flex-wrap items-center gap-x-1.5 text-xs text-zinc-500 dark:text-zinc-400">
                            <span class="font-mono font-semibold">{{ $item['group_number'] }}</span>
                            @if($kwarran !== '')
                                <span aria-hidden="true">&bull;</span>
                                <span>{{ $kwarran }}</span>
                            @endif
                        </p>
                    </div>

                    @if($rank === 1)
                        <flux:icon name="trophy" class="pointer-events-none absolute -right-3 -bottom-3 hidden size-24 rotate-12 text-amber-400/20 sm:block" />
                    @endif
                </article>
            @else
                <div class="flex items-center gap-4 rounded-2xl border-2 border-dashed border-zinc-200 p-4 text-zinc-400 dark:border-zinc-800 dark:text-zinc-600 sm:p-5">
                    <div class="flex size-14 shrink-0 items-center justify-center rounded-full border-2 border-dashed border-zinc-300 font-mono text-2xl font-black dark:border-zinc-700">{{ $rank }}</div>
                    <div>
                        <p class="text-sm font-semibold">Menunggu hasil lomba</p>
                        <p class="text-xs">Peringkat {{ $rank }} sedang diperebutkan</p>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</section>

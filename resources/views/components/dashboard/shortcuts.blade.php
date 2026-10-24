@props(['groups'])

{{-- Pintasan menu dasbor: kartu per grup fungsi, urutannya sama dengan sidebar (definisi di App\Support\Navigation) --}}
@php
    $groups = collect($groups)->reject(fn (array $group) => $group['heading'] === \App\Support\Navigation::MAIN_GROUP);
@endphp

@if ($groups->isNotEmpty())
    <div class="space-y-6">
        @foreach ($groups as $group)
            <section class="space-y-3" aria-label="{{ $group['heading'] }}">
                <h3 class="text-[11px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ $group['heading'] }}</h3>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($group['items'] as $item)
                        <a href="{{ $item['href'] }}" wire:navigate
                            class="flex items-center gap-3.5 rounded-2xl border border-zinc-200 bg-white p-4 transition hover:border-amber-400 dark:border-zinc-800 dark:bg-zinc-900">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300">
                                <flux:icon :name="$item['icon']" class="size-5" />
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100">{{ $item['label'] }}</h4>
                                <p class="text-[11px] text-zinc-500">{{ $item['description'] }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
@endif

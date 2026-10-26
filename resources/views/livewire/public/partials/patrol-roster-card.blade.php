@php
    $tones = [
        'boys' => [
            'label' => 'Putra',
            'card' => 'border-blue-200/90 dark:border-blue-900/50 dark:shadow-blue-950/20',
            'header' => 'border-blue-100 dark:border-blue-900/40',
            'icon' => 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300 dark:border dark:border-blue-800/60',
            'caption' => 'text-blue-700 dark:text-blue-400',
            'count' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/80 dark:text-blue-300 dark:border dark:border-blue-800/60',
            'row' => 'hover:bg-blue-50/40 dark:hover:bg-blue-950/30',
        ],
        'girls' => [
            'label' => 'Putri',
            'card' => 'border-rose-200/90 dark:border-rose-900/50 dark:shadow-rose-950/20',
            'header' => 'border-rose-100 dark:border-rose-900/40',
            'icon' => 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300 dark:border dark:border-rose-800/60',
            'caption' => 'text-rose-700 dark:text-rose-400',
            'count' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/80 dark:text-rose-300 dark:border dark:border-rose-800/60',
            'row' => 'hover:bg-rose-50/40 dark:hover:bg-rose-950/30',
        ],
    ];
    $tone = $tones[$category];
@endphp

<div class="flex flex-col justify-between rounded-2xl border bg-white p-4 shadow-sm dark:bg-zinc-900/90 dark:shadow-md sm:p-5 {{ $tone['card'] }}">
    <div>
        <div class="flex items-center justify-between border-b pb-3.5 {{ $tone['header'] }}">
            <div class="flex items-center gap-2.5">
                <span class="flex size-8 items-center justify-center rounded-xl sm:size-9 {{ $tone['icon'] }}">
                    <flux:icon name="user-group" class="size-4 sm:size-5" />
                </span>
                <div>
                    <span class="block text-[10px] font-bold uppercase tracking-wider {{ $tone['caption'] }}">
                        Regu Penggalang {{ $tone['label'] }}
                    </span>
                    <h5 class="text-sm font-extrabold text-zinc-900 dark:text-zinc-100 sm:text-base">
                        {{ $patrol ? 'Regu '.$patrol->patrol_name : 'Belum Terdaftar' }}
                    </h5>
                    @if($patrol?->chaperone)
                        <span class="block text-[11px] text-zinc-500 dark:text-zinc-400">Bindamping: {{ $patrol->chaperone->full_name }}</span>
                    @endif
                </div>
            </div>
            @if($patrol)
                <span class="inline-flex items-center rounded-lg px-2 py-0.5 text-xs font-bold {{ $tone['count'] }}">
                    {{ $patrol->participants->count() }} Anggota
                </span>
            @endif
        </div>

        @if($patrol && $patrol->participants->isNotEmpty())
            <div class="mt-3 divide-y divide-zinc-100 dark:divide-zinc-800/80">
                @foreach($patrol->orderedParticipants() as $index => $participant)
                    <div class="-mx-2 flex items-center justify-between gap-2.5 rounded-xl px-2 py-2.5 text-xs transition-colors {{ $tone['row'] }}">
                        <div class="flex min-w-0 flex-1 items-center gap-2.5">
                            <span class="flex size-6 shrink-0 items-center justify-center rounded-lg bg-zinc-100 font-mono text-[11px] font-bold text-zinc-600 dark:border dark:border-zinc-700/60 dark:bg-zinc-800 dark:text-zinc-300">
                                {{ $index + 1 }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <span class="block truncate text-xs font-bold text-zinc-900 dark:text-zinc-100 sm:text-sm">
                                    {{ $participant->full_name }}
                                </span>
                                <span class="block truncate text-[11px] text-zinc-500 dark:text-zinc-400">
                                    <span>NTA: <strong class="font-mono text-zinc-700 dark:text-zinc-200">{{ $participant->scout_id ?: ($participant->id_number ?: '-') }}</strong></span>
                                    <span class="text-zinc-300 dark:text-zinc-600">&bull;</span>
                                    <span class="font-medium text-zinc-600 dark:text-zinc-300">Penggalang {{ $participant->highestSkuLevel() }}</span>
                                </span>
                            </div>
                        </div>

                        <div class="shrink-0">
                            <span class="inline-flex items-center gap-1 rounded-md border px-2 py-0.5 text-[10px] font-bold {{ $participant->role_in_patrol?->badgeClasses() ?? 'border-zinc-200 bg-zinc-50 text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800/80 dark:text-zinc-400' }}" title="{{ $participant->role_in_patrol?->label() ?? 'Anggota Regu' }}">
                                @if($participant->role_in_patrol?->iconName())
                                    <flux:icon name="{{ $participant->role_in_patrol->iconName() }}" class="size-3 stroke-[2.5]" />
                                @endif
                                <span>{{ $participant->role_in_patrol?->shortLabel() ?? 'Anggota' }}</span>
                                <span class="sr-only">({{ $participant->role_in_patrol?->label() ?? 'Anggota Regu' }})</span>
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="py-8 text-center text-xs text-zinc-500 dark:text-zinc-400">
                Belum ada data anggota regu {{ strtolower($tone['label']) }} pada pangkalan ini.
            </div>
        @endif
    </div>
</div>

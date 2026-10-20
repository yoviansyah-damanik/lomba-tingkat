@props([
    'label',
    'value' => null,
    'description' => null,
    'unit' => null,
    'icon' => null,
    'color' => 'neutral',
    'href' => null,
    'actionText' => null,
    'active' => false,
    'asButton' => false,
])

@php
    $colorThemes = [
        'neutral' => [
            'card' => 'border-zinc-200/80 bg-white dark:border-zinc-800 dark:bg-zinc-900',
            'active' => 'ring-2 ring-zinc-500/20 border-zinc-500 bg-zinc-50/80 dark:bg-zinc-800',
            'label' => 'text-zinc-500 dark:text-zinc-400',
            'value' => 'text-zinc-900 dark:text-white',
            'unit' => 'text-zinc-400',
            'desc' => 'text-zinc-500 dark:text-zinc-400',
            'iconBg' => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300',
            'iconColor' => 'text-zinc-500 dark:text-zinc-400',
            'link' => 'text-zinc-600 dark:text-zinc-400',
        ],
        'amber' => [
            'card' => 'border-amber-200/70 bg-gradient-to-br from-amber-50/60 to-white dark:border-amber-900/40 dark:from-amber-950/20 dark:to-zinc-900',
            'active' => 'ring-2 ring-amber-500/20 border-amber-500 bg-amber-50/80 dark:bg-amber-950/40',
            'label' => 'text-amber-800 dark:text-amber-300',
            'value' => 'text-amber-900 dark:text-amber-100',
            'unit' => 'text-amber-600/70 dark:text-amber-400',
            'desc' => 'text-amber-700/80 dark:text-amber-400/70',
            'iconBg' => 'bg-amber-500/10 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400',
            'iconColor' => 'text-amber-600 dark:text-amber-400',
            'link' => 'text-amber-600 dark:text-amber-400',
        ],
        'blue' => [
            'card' => 'border-blue-200/70 bg-gradient-to-br from-blue-50/60 to-white dark:border-blue-900/40 dark:from-blue-950/20 dark:to-zinc-900',
            'active' => 'ring-2 ring-blue-500/20 border-blue-500 bg-blue-50/80 dark:bg-blue-950/40',
            'label' => 'text-blue-700 dark:text-blue-300',
            'value' => 'text-blue-900 dark:text-blue-100',
            'unit' => 'text-blue-600/70 dark:text-blue-400',
            'desc' => 'text-blue-600/80 dark:text-blue-400',
            'iconBg' => 'bg-blue-500/10 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400',
            'iconColor' => 'text-blue-600 dark:text-blue-400',
            'link' => 'text-blue-600 dark:text-blue-400',
        ],
        'emerald' => [
            'card' => 'border-emerald-200/70 bg-gradient-to-br from-emerald-50/60 to-white dark:border-emerald-900/40 dark:from-emerald-950/20 dark:to-zinc-900',
            'active' => 'ring-2 ring-emerald-500/20 border-emerald-500 bg-emerald-50/80 dark:bg-emerald-950/40',
            'label' => 'text-emerald-700 dark:text-emerald-300',
            'value' => 'text-emerald-900 dark:text-emerald-100',
            'unit' => 'text-emerald-600/70 dark:text-emerald-400',
            'desc' => 'text-emerald-600/80 dark:text-emerald-400',
            'iconBg' => 'bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400',
            'iconColor' => 'text-emerald-600 dark:text-emerald-400',
            'link' => 'text-emerald-600 dark:text-emerald-400',
        ],
        'indigo' => [
            'card' => 'border-indigo-200/70 bg-gradient-to-br from-indigo-50/60 to-white dark:border-indigo-900/40 dark:from-indigo-950/20 dark:to-zinc-900',
            'active' => 'ring-2 ring-indigo-500/20 border-indigo-500 bg-indigo-50/80 dark:bg-indigo-950/40',
            'label' => 'text-indigo-700 dark:text-indigo-300',
            'value' => 'text-indigo-900 dark:text-indigo-100',
            'unit' => 'text-indigo-600/70 dark:text-indigo-400',
            'desc' => 'text-indigo-600/80 dark:text-indigo-400',
            'iconBg' => 'bg-indigo-500/10 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400',
            'iconColor' => 'text-indigo-600 dark:text-indigo-400',
            'link' => 'text-indigo-600 dark:text-indigo-400',
        ],
        'purple' => [
            'card' => 'border-purple-200/70 bg-gradient-to-br from-purple-50/60 to-white dark:border-purple-900/40 dark:from-purple-950/20 dark:to-zinc-900',
            'active' => 'ring-2 ring-purple-500/20 border-purple-500 bg-purple-50/80 dark:bg-purple-950/40',
            'label' => 'text-purple-700 dark:text-purple-300',
            'value' => 'text-purple-900 dark:text-purple-100',
            'unit' => 'text-purple-600/70 dark:text-purple-400',
            'desc' => 'text-purple-600/80 dark:text-purple-400',
            'iconBg' => 'bg-purple-500/10 text-purple-600 dark:bg-purple-500/20 dark:text-purple-400',
            'iconColor' => 'text-purple-600 dark:text-purple-400',
            'link' => 'text-purple-600 dark:text-purple-400',
        ],
        'rose' => [
            'card' => 'border-rose-200/70 bg-gradient-to-br from-rose-50/60 to-white dark:border-rose-900/40 dark:from-rose-950/20 dark:to-zinc-900',
            'active' => 'ring-2 ring-rose-500/20 border-rose-500 bg-rose-50/80 dark:bg-rose-950/40',
            'label' => 'text-rose-700 dark:text-rose-300',
            'value' => 'text-rose-900 dark:text-rose-100',
            'unit' => 'text-rose-600/70 dark:text-rose-400',
            'desc' => 'text-rose-600/80 dark:text-rose-400',
            'iconBg' => 'bg-rose-500/10 text-rose-600 dark:bg-rose-500/20 dark:text-rose-400',
            'iconColor' => 'text-rose-600 dark:text-rose-400',
            'link' => 'text-rose-600 dark:text-rose-400',
        ],
        'orange' => [
            'card' => 'border-orange-200/70 bg-gradient-to-br from-orange-50/60 to-white dark:border-orange-900/40 dark:from-orange-950/20 dark:to-zinc-900',
            'active' => 'ring-2 ring-orange-500/20 border-orange-500 bg-orange-50/80 dark:bg-orange-950/40',
            'label' => 'text-orange-700 dark:text-orange-300',
            'value' => 'text-orange-900 dark:text-orange-100',
            'unit' => 'text-orange-600/70 dark:text-orange-400',
            'desc' => 'text-orange-600/80 dark:text-orange-400',
            'iconBg' => 'bg-orange-500/10 text-orange-600 dark:bg-orange-500/20 dark:text-orange-400',
            'iconColor' => 'text-orange-600 dark:text-orange-400',
            'link' => 'text-orange-600 dark:text-orange-400',
        ],
    ];

    $theme = $colorThemes[$color] ?? $colorThemes['neutral'];
    $tag = $href ? 'a' : ($asButton ? 'button' : 'div');
    $isClickable = $href || $asButton;
    $baseClasses = 'relative rounded-2xl border p-4 shadow-xs transition-all flex flex-col justify-between ' .
        ($active ? $theme['active'] : $theme['card']) .
        ($isClickable ? ' group cursor-pointer hover:shadow-md hover:border-amber-400 dark:hover:border-amber-500' : '');
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" wire:navigate @endif
    @if ($asButton) type="button" @endif
    {{ $attributes->merge(['class' => $baseClasses]) }}
>
    <div>
        {{-- Label dan Ikon Atas --}}
        <div class="flex items-center justify-between gap-2">
            <span class="text-xs font-semibold {{ $theme['label'] }} line-clamp-1">
                {{ $label }}
            </span>
            @if ($icon)
                <span class="flex size-7 shrink-0 items-center justify-center rounded-lg {{ $theme['iconBg'] }} transition group-hover:scale-110">
                    <flux:icon :name="$icon" class="size-4" />
                </span>
            @endif
        </div>

        {{-- Nilai Angka Utama & Satuan --}}
        @if ($value !== null && $value !== '')
            <div class="mt-2 flex items-baseline gap-1.5 flex-wrap">
                <span class="text-2xl sm:text-3xl font-extrabold tracking-tight {{ $theme['value'] }}">
                    {{ $value }}
                </span>
                @if ($unit)
                    <span class="text-xs font-medium {{ $theme['unit'] }}">
                        {{ $unit }}
                    </span>
                @endif
            </div>
        @endif
    </div>

    {{-- Deskripsi / Catatan Bawah / Tautan Aksi --}}
    @if ($description || $actionText)
        <div class="mt-2 text-[11px] {{ $theme['desc'] }}">
            @if ($description)
                <p class="line-clamp-1">{{ $description }}</p>
            @endif
            @if ($actionText)
                <span class="mt-1 font-semibold {{ $theme['link'] }} flex items-center gap-1">
                    {{ $actionText }} &rarr;
                </span>
            @endif
        </div>
    @endif

    {{ $slot }}
</{{ $tag }}>

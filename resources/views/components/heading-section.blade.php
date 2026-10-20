@props([
    'title',
    'description' => null,
    'badge' => null,
    'badgeIcon' => null,
    'badgeColor' => 'amber',
    'badgeExtra' => null,
    'icon' => null,
    'breadcrumbs' => null,
    'actions' => null,
])

@php
    $badgeColors = [
        'amber' => 'bg-amber-500/10 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 border-amber-300/40 dark:border-amber-700/40',
        'emerald' => 'bg-emerald-500/10 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 border-emerald-300/40 dark:border-emerald-700/40',
        'blue' => 'bg-blue-500/10 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 border-blue-300/40 dark:border-blue-700/40',
        'indigo' => 'bg-indigo-500/10 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300 border-indigo-300/40 dark:border-indigo-700/40',
        'purple' => 'bg-purple-500/10 text-purple-700 dark:bg-purple-500/20 dark:text-purple-300 border-purple-300/40 dark:border-purple-700/40',
        'rose' => 'bg-rose-500/10 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 border-rose-300/40 dark:border-rose-700/40',
        'zinc' => 'bg-zinc-500/10 text-zinc-700 dark:bg-zinc-500/20 dark:text-zinc-300 border-zinc-300/40 dark:border-zinc-700/40',
    ];
    $selectedBadgeColor = $badgeColors[$badgeColor] ?? $badgeColors['amber'];
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between']) }}>
    <div class="space-y-1">
        {{-- Navigasi Breadcrumbs jika ada --}}
        @if ($breadcrumbs ?? false)
            <nav class="flex items-center text-xs text-zinc-500 dark:text-zinc-400 mb-1.5 flex-wrap">
                {{ $breadcrumbs }}
            </nav>
        @endif

        {{-- Badge Pill jika tersedia --}}
        @if ($badge)
            <div class="flex items-center gap-2 mb-1.5">
                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold border {{ $selectedBadgeColor }}">
                    @if ($badgeIcon)
                        <flux:icon :name="$badgeIcon" class="size-3.5" />
                    @endif
                    <span>{{ $badge }}</span>
                </span>
                {{ $badgeExtra ?? '' }}
            </div>
        @endif

        {{-- Judul Halaman & Icon Utama --}}
        <div class="flex items-center gap-3">
            @if ($icon)
                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/15 text-amber-600 dark:text-amber-400">
                    <flux:icon :name="$icon" class="size-6" />
                </div>
            @endif
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">
                {{ $title ?? $slot }}
            </h1>
        </div>

        {{-- Deskripsi / Subtitle --}}
        @if ($description)
            <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 max-w-3xl leading-relaxed">
                {{ $description }}
            </p>
        @endif
    </div>

    {{-- Slot Tombol Aksi / Meta Info --}}
    @if ($actions ?? false)
        <div class="flex items-center gap-2 flex-wrap self-start sm:self-center shrink-0">
            {{ $actions }}
        </div>
    @endif
</div>

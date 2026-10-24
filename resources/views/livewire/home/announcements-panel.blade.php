<section class="rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
    <div class="flex items-center justify-between gap-3 border-b border-zinc-100 pb-3 dark:border-zinc-800">
        <div class="flex items-center gap-2">
            <flux:icon name="megaphone" class="size-5 text-amber-600 dark:text-amber-400" />
            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Pengumuman Terbaru</h3>
        </div>
        <flux:button href="{{ route('announcements') }}" wire:navigate size="xs" variant="subtle" icon-trailing="arrow-right">Semua</flux:button>
    </div>

    <ul class="mt-3 divide-y divide-zinc-100 dark:divide-zinc-800">
        @forelse ($announcements as $announcement)
            <li wire:key="announcement-{{ $announcement->id }}" class="py-3">
                <a href="{{ route('announcements.show', $announcement->slug) }}" wire:navigate class="group block">
                    <p class="text-sm font-semibold text-zinc-900 group-hover:text-amber-700 dark:text-zinc-100 dark:group-hover:text-amber-400">{{ $announcement->title }}</p>
                    @if ($announcement->summary)
                        <p class="mt-0.5 line-clamp-2 text-xs text-zinc-500 dark:text-zinc-400">{{ $announcement->summary }}</p>
                    @endif
                    <p class="mt-1 text-[10px] text-zinc-400">{{ $announcement->published_at?->timezone('Asia/Jakarta')->locale('id')->isoFormat('D MMM Y') }}</p>
                </a>
            </li>
        @empty
            <li class="py-8 text-center text-xs italic text-zinc-400">Belum ada pengumuman.</li>
        @endforelse
    </ul>
</section>

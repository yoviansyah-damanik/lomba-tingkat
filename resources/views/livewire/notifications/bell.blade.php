<div wire:poll.30s class="relative" x-data="{ open: false }" x-on:keydown.escape.window="open = false">
    <div class="relative">
        <flux:button icon="bell" variant="subtle" size="sm" x-on:click="open = !open" aria-label="Notifikasi" />

        @if ($this->unreadCount > 0)
            <span class="pointer-events-none absolute -right-1 -top-1 flex min-w-4.5 items-center justify-center rounded-full bg-rose-600 px-1 text-[10px] font-bold leading-4.5 text-white shadow-xs">
                {{ $this->unreadCount > 99 ? '99+' : $this->unreadCount }}
            </span>
        @endif
    </div>

    <div x-show="open" x-cloak x-on:click.away="open = false"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 z-50 mt-2 w-80 max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-xl dark:border-zinc-800 dark:bg-zinc-900 sm:w-96">

        <div class="flex items-center justify-between border-b border-zinc-100 px-4 py-3 dark:border-zinc-800">
            <div class="flex items-center gap-2">
                <h4 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Notifikasi</h4>
                @if ($this->unreadCount > 0)
                    <span class="rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-rose-700 dark:bg-rose-950/60 dark:text-rose-300">
                        {{ $this->unreadCount }} baru
                    </span>
                @endif
            </div>

            @if ($this->unreadCount > 0)
                <flux:button size="xs" variant="subtle" icon="check" wire:click="markAllAsRead">
                    Tandai semua dibaca
                </flux:button>
            @endif
        </div>

        <div class="max-h-96 divide-y divide-zinc-100 overflow-y-auto dark:divide-zinc-800">
            @forelse ($this->notifications as $notification)
                @php
                    $data = $notification->data;
                    $tag = (string) ($data['tag'] ?? '');
                    $icon = match (true) {
                        str_starts_with($tag, 'lt3-registration') => 'building-library',
                        str_starts_with($tag, 'lt3-verification') => 'shield-check',
                        str_starts_with($tag, 'lt3-reminder') => 'calendar',
                        default => 'bell',
                    };
                    $isUnread = $notification->read_at === null;
                @endphp

                <button type="button" wire:key="notif-{{ $notification->id }}" wire:click="open('{{ $notification->id }}')"
                    class="flex w-full cursor-pointer items-start gap-3 px-4 py-3 text-left transition hover:bg-zinc-50 dark:hover:bg-zinc-800/60 {{ $isUnread ? 'bg-amber-50/60 dark:bg-amber-950/20' : '' }}">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-xl {{ $isUnread ? 'bg-amber-500 text-white' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400' }}">
                        <flux:icon :name="$icon" class="size-4.5" />
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <span class="text-xs {{ $isUnread ? 'font-bold text-zinc-900 dark:text-zinc-100' : 'font-medium text-zinc-700 dark:text-zinc-300' }}">
                                {{ $data['title'] ?? 'Pemberitahuan' }}
                            </span>
                            @if ($isUnread)
                                <span class="mt-1 size-2 shrink-0 rounded-full bg-amber-500" title="Belum dibaca"></span>
                            @endif
                        </div>
                        <p class="mt-0.5 line-clamp-2 text-[11px] leading-relaxed text-zinc-600 dark:text-zinc-400">
                            {{ $data['body'] ?? '' }}
                        </p>
                        <span class="mt-1 block text-[10px] text-zinc-400">
                            {{ $notification->created_at->diffForHumans() }}
                        </span>
                    </div>
                </button>
            @empty
                <div class="px-4 py-10 text-center text-zinc-400">
                    <flux:icon name="bell-slash" class="mx-auto size-8 opacity-60" />
                    <p class="mt-2 text-xs">Belum ada pemberitahuan.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

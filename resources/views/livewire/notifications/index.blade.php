<div class="flex h-full w-full flex-1 flex-col gap-6">
    <x-heading-section
        title="Notifikasi"
        description="Seluruh pemberitahuan yang ditujukan kepada akun Anda."
    >
        <x-slot:breadcrumbs>
            <a href="{{ route('dashboard') }}" wire:navigate class="hover:underline">Dasbor</a>
            <span class="mx-2">&rsaquo;</span>
            <span class="text-zinc-800 dark:text-zinc-200">Notifikasi</span>
        </x-slot:breadcrumbs>

        <x-slot:actions>
            @if ($unreadCount > 0)
                <flux:button size="sm" variant="subtle" icon="check" wire:click="markAllAsRead">
                    Tandai semua dibaca
                </flux:button>
            @endif
        </x-slot:actions>
    </x-heading-section>

    {{-- Filter --}}
    <div class="flex items-center gap-2">
        <flux:button size="sm" variant="{{ $filter === 'all' ? 'primary' : 'subtle' }}" wire:click="$set('filter', 'all')">
            Semua
        </flux:button>
        <flux:button size="sm" variant="{{ $filter === 'unread' ? 'primary' : 'subtle' }}" wire:click="$set('filter', 'unread')">
            Belum dibaca
            @if ($unreadCount > 0)
                <span class="ml-1 rounded-full bg-rose-600 px-1.5 text-[10px] font-bold leading-4 text-white">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
            @endif
        </flux:button>
    </div>

    {{-- Daftar notifikasi --}}
    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
        <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
            @forelse ($notifications as $notification)
                @php
                    $data = $notification->data;
                    $icon = \App\Notifications\AppNotification::iconFor($data['tag'] ?? null);
                    $isUnread = $notification->read_at === null;
                @endphp

                <div wire:key="notif-{{ $notification->id }}"
                    class="flex items-start gap-3 px-4 py-4 sm:px-5 {{ $isUnread ? 'bg-amber-50/60 dark:bg-amber-950/20' : '' }}">
                    <button type="button" wire:click="open('{{ $notification->id }}')"
                        class="flex min-w-0 flex-1 cursor-pointer items-start gap-3 text-left">
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-xl {{ $isUnread ? 'bg-amber-500 text-white' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400' }}">
                            <flux:icon :name="$icon" class="size-5" />
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <span class="text-sm {{ $isUnread ? 'font-bold text-zinc-900 dark:text-zinc-100' : 'font-medium text-zinc-700 dark:text-zinc-300' }}">
                                    {{ $data['title'] ?? 'Pemberitahuan' }}
                                </span>
                                @if ($isUnread)
                                    <span class="mt-1.5 size-2 shrink-0 rounded-full bg-amber-500" title="Belum dibaca"></span>
                                @endif
                            </div>
                            <p class="mt-0.5 break-words text-xs leading-relaxed text-zinc-600 dark:text-zinc-400">
                                {{ $data['body'] ?? '' }}
                            </p>
                            <span class="mt-1.5 block text-[11px] text-zinc-400">
                                {{ $notification->created_at->diffForHumans() }}
                                &bull; {{ $notification->created_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                            </span>
                        </div>
                    </button>

                    @if ($isUnread)
                        <flux:button size="xs" variant="subtle" icon="check" wire:click="markAsRead('{{ $notification->id }}')" title="Tandai dibaca" aria-label="Tandai dibaca" />
                    @endif
                </div>
            @empty
                <div class="px-4 py-14 text-center text-zinc-400">
                    <flux:icon name="bell-slash" class="mx-auto size-10 opacity-60" />
                    <p class="mt-3 text-sm">
                        {{ $filter === 'unread' ? 'Tidak ada notifikasi yang belum dibaca.' : 'Belum ada pemberitahuan.' }}
                    </p>
                </div>
            @endforelse
        </div>
    </div>

    {{ $notifications->links() }}
</div>

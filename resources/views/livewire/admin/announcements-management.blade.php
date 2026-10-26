<div class="space-y-6">
    {{-- Header Section --}}
    <x-heading-section
        title="Manajemen Pengumuman Kegiatan"
        description="Terbitkan informasi resmi, petunjuk teknis darurat, jadwal kegiatan, dan sebarluaskan melalui portal dan notifikasi push."
    >
        <x-slot:breadcrumbs>
            <a href="{{ route('dashboard') }}" wire:navigate class="hover:underline">Dashboard</a>
            <span class="mx-2">&rsaquo;</span>
            <span class="text-zinc-800 dark:text-zinc-200">Pengumuman</span>
        </x-slot:breadcrumbs>

        <x-slot:actions>
            <flux:button
                icon="plus"
                variant="primary"
                href="{{ route('admin.announcements.create') }}"
                wire:navigate
            >
                Tulis Pengumuman Baru
            </flux:button>
        </x-slot:actions>
    </x-heading-section>

    {{-- Filter Bar --}}
    <div class="rounded-2xl border border-zinc-200/80 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between">
        <div class="flex-1">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari judul atau isi pengumuman..."
                class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
            />
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <select
                wire:model.live="categoryFilter"
                class="rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
            >
                <option value="">Semua Kategori</option>
                <option value="general">Umum</option>
                <option value="schedule">Jadwal</option>
                <option value="technical">Teknis</option>
                <option value="urgent">Penting / Darurat</option>
            </select>

            <select
                wire:model.live="statusFilter"
                class="rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
            >
                <option value="">Semua Status</option>
                <option value="published">Terpublikasi</option>
                <option value="draft">Draf</option>
            </select>
        </div>
    </div>

    {{-- Tabel Pengumuman --}}
    <div class="rounded-2xl border border-zinc-200/80 bg-white overflow-hidden shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="border-b border-zinc-200 bg-zinc-50/80 font-bold uppercase tracking-wider text-zinc-500 dark:border-zinc-800 dark:bg-zinc-800/40">
                    <tr>
                        <th class="py-3 px-4">Pengumuman</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4">Penulis & Tanggal</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/80">
                    @forelse($announcements as $item)
                        <tr class="transition hover:bg-zinc-50/60 dark:hover:bg-zinc-800/40">
                            <td class="py-3 px-4">
                                <strong class="font-bold text-zinc-900 dark:text-zinc-100 block">
                                    {{ $item->title }}
                                </strong>
                                <p class="text-zinc-500 dark:text-zinc-400 text-[11px] line-clamp-1 mt-0.5">
                                    {{ $item->summary ?: Str::limit(strip_tags($item->content), 80) }}
                                </p>
                                @if($item->attachmentFiles())
                                    <span class="mt-1 inline-flex items-center gap-1 text-[11px] font-semibold text-amber-700 dark:text-amber-400">
                                        <flux:icon name="paper-clip" class="size-3" />
                                        {{ count($item->attachmentFiles()) }} berkas unduhan
                                    </span>
                                @endif
                            </td>

                            <td class="py-3 px-4">
                                @php
                                    $catColor = match($item->category) {
                                        'urgent' => 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300',
                                        'technical' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300',
                                        'schedule' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300',
                                        default => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300',
                                    };
                                @endphp
                                <span class="rounded-md px-2 py-0.5 text-[10px] font-bold uppercase {{ $catColor }}">
                                    {{ ucfirst($item->category) }}
                                </span>
                            </td>

                            <td class="py-3 px-4 text-zinc-500">
                                <div class="font-bold text-zinc-800 dark:text-zinc-200">{{ $item->author?->name ?? 'Administrator' }}</div>
                                <div class="text-[11px] text-zinc-400 font-mono">
                                    {{ $item->published_at ? $item->published_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') : 'Belum Terbit' }}
                                </div>
                            </td>

                            <td class="py-3 px-4 text-center">
                                @if($item->is_published)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                        <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                        Terbit
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-2.5 py-0.5 text-[10px] font-bold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                                        <span class="size-1.5 rounded-full bg-zinc-400"></span>
                                        Draf
                                    </span>
                                @endif
                            </td>

                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('announcements.show', $item->slug) }}" target="_blank">
                                        <flux:button
                                            size="xs"
                                            variant="subtle"
                                            icon="arrow-top-right-on-square"
                                            title="Lihat di Beranda Publik"
                                        />
                                    </a>

                                    <flux:button
                                        size="xs"
                                        variant="subtle"
                                        icon="pencil-square"
                                        href="{{ route('admin.announcements.edit', $item->id) }}"
                                        wire:navigate
                                        title="Edit Pengumuman"
                                    />

                                    <flux:button
                                        size="xs"
                                        variant="subtle"
                                        icon="{{ $item->is_published ? 'eye-slash' : 'eye' }}"
                                        wire:click="togglePublish('{{ $item->id }}')"
                                        title="{{ $item->is_published ? 'Tarik Publikasi (Jadikan Draf)' : 'Terbitkan Sekarang' }}"
                                    />

                                    <flux:button
                                        size="xs"
                                        variant="subtle"
                                        icon="trash"
                                        wire:click="openDeleteModal('{{ $item->id }}')"
                                        title="Hapus Pengumuman"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-zinc-400 italic">
                                Belum ada pengumuman yang sesuai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($announcements->hasPages())
            <div class="p-4 border-t border-zinc-100 dark:border-zinc-800">
                {{ $announcements->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Hapus Pengumuman --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="w-full max-w-sm rounded-2xl border border-rose-200 bg-white p-5 shadow-xl dark:border-rose-900/60 dark:bg-zinc-900 space-y-3">
                <div class="flex items-center gap-2.5 text-rose-600">
                    <flux:icon name="exclamation-triangle" class="size-5" />
                    <h3 class="text-sm font-bold">Hapus Pengumuman?</h3>
                </div>
                <p class="text-xs text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    Apakah Anda yakin ingin menghapus pengumuman <strong>{{ $title }}</strong>?
                </p>

                <div class="pt-2 flex justify-end gap-2">
                    <flux:button type="button" variant="subtle" size="sm" wire:click="$set('showDeleteModal', false)">Batal</flux:button>
                    <flux:button type="button" variant="danger" size="sm" icon="trash" wire:click="deleteAnnouncement">Ya, Hapus</flux:button>
                </div>
            </div>
        </div>
    @endif
</div>

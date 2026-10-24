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
                wire:click="openCreateModal"
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
                                        wire:click="openEditModal('{{ $item->id }}')"
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

    {{-- Modal Tambah / Edit Pengumuman --}}
    @if($showFormModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="w-full max-w-2xl rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-800 dark:bg-zinc-900 space-y-4">
                <div class="border-b border-zinc-100 dark:border-zinc-800 pb-3 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider">
                        {{ $selectedAnnouncementId ? 'Edit Pengumuman Kegiatan' : 'Tulis Pengumuman Baru' }}
                    </h3>
                    <flux:button size="xs" variant="subtle" icon="x-mark" wire:click="$set('showFormModal', false)" />
                </div>

                <form wire:submit="saveAnnouncement" class="space-y-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Judul Pengumuman:</label>
                        <input type="text" wire:model="title" placeholder="Contoh: Perubahan Lokasi Upacara Pembukaan LT-III" class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 font-semibold" />
                        @error('title') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Kategori Informasi:</label>
                            <select wire:model="category" class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100">
                                <option value="general">Informasi Umum</option>
                                <option value="schedule">Perubahan / Pengingat Jadwal</option>
                                <option value="technical">Petunjuk Teknis Lomba</option>
                                <option value="urgent">Pengumuman Penting / Darurat</option>
                            </select>
                            @error('category') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Status Publikasi Langsung:</label>
                            <div class="pt-2 flex items-center gap-2">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="is_published" class="sr-only peer" />
                                    <div class="w-9 h-5 bg-zinc-200 peer-focus:outline-hidden rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-zinc-600 peer-checked:bg-amber-600"></div>
                                </label>
                                <span class="text-xs text-zinc-600 dark:text-zinc-400 font-medium">
                                    {{ $is_published ? 'Langsung Terbitkan' : 'Simpan Sebagai Draf' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Ringkasan / Excerpt (Opsional):</label>
                        <input type="text" wire:model="summary" placeholder="Ringkasan 1 kalimat yang tampil di kartu pengumuman..." class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Materi Isi Pengumuman Lengkap:</label>
                        <textarea wire:model="content" rows="6" placeholder="Tuliskan isi pengumuman lengkap secara jelas..." class="w-full rounded-xl border border-zinc-300 bg-white p-3 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"></textarea>
                        @error('content') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    {{-- Berkas unduhan --}}
                    <div class="rounded-xl border border-zinc-200 p-3.5 dark:border-zinc-800">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Berkas Unduhan (Opsional):</label>
                        <p class="mb-2 text-[11px] text-zinc-500">PDF, Word, Excel, PowerPoint, JPG/PNG, atau ZIP &bull; maks. 10 MB per berkas &bull; maks. 10 berkas.</p>

                        @if($keptAttachments || $newFiles)
                            <ul class="mb-2 space-y-1.5">
                                @foreach($keptAttachments as $index => $file)
                                    <li wire:key="kept-{{ $index }}-{{ $file['path'] }}" class="flex items-center justify-between gap-2 rounded-lg bg-zinc-50 px-3 py-1.5 text-xs dark:bg-zinc-800/60">
                                        <span class="flex min-w-0 items-center gap-2">
                                            <flux:icon name="paper-clip" class="size-4 shrink-0 text-zinc-400" />
                                            <span class="truncate font-medium text-zinc-800 dark:text-zinc-200">{{ $file['name'] }}</span>
                                            <span class="shrink-0 text-[11px] text-zinc-400">{{ \App\Models\Announcement::formatFileSize((int) $file['size']) }}</span>
                                        </span>
                                        <flux:button type="button" size="xs" variant="subtle" icon="x-mark" wire:click="removeAttachment({{ $index }})" title="Lepas berkas" />
                                    </li>
                                @endforeach
                                @foreach($newFiles as $index => $upload)
                                    <li wire:key="new-{{ $index }}" class="flex items-center justify-between gap-2 rounded-lg bg-amber-50 px-3 py-1.5 text-xs dark:bg-amber-950/30">
                                        <span class="flex min-w-0 items-center gap-2">
                                            <flux:icon name="arrow-up-tray" class="size-4 shrink-0 text-amber-600" />
                                            <span class="truncate font-medium text-zinc-800 dark:text-zinc-200">{{ $upload->getClientOriginalName() }}</span>
                                            <span class="shrink-0 text-[11px] text-amber-700 dark:text-amber-400">baru &bull; {{ \App\Models\Announcement::formatFileSize((int) $upload->getSize()) }}</span>
                                        </span>
                                        <flux:button type="button" size="xs" variant="subtle" icon="x-mark" wire:click="removeNewFile({{ $index }})" title="Batalkan berkas" />
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        <input type="file" wire:model="newFiles" multiple
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip"
                            class="block w-full text-xs text-zinc-500 file:mr-2 file:rounded-lg file:border-0 file:bg-amber-100 file:px-2.5 file:py-1 file:text-xs file:font-semibold file:text-amber-800 hover:file:bg-amber-200 dark:file:bg-amber-950/60 dark:file:text-amber-300">
                        <div wire:loading wire:target="newFiles" class="mt-1 text-[11px] text-amber-700">Mengunggah berkas...</div>
                        @error('newFiles') <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span> @enderror
                        @error('newFiles.*') <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>
                    @if(! $selectedAnnouncementId)
                        <div class="rounded-xl border border-amber-200 bg-amber-50/60 p-3 dark:border-amber-900/60 dark:bg-amber-950/20 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <flux:icon name="bell" class="size-4 text-amber-600 dark:text-amber-400 shrink-0" />
                                <div>
                                    <strong class="text-xs text-amber-950 dark:text-amber-200 block">Kirim Notifikasi PWA Otomatis</strong>
                                    <span class="text-[11px] text-amber-800 dark:text-amber-400">Notifikasi otomatis terkirim ke gawai seluruh pengguna yang memasang PWA saat pengumuman diterbitkan.</span>
                                </div>
                            </div>
                            <input type="checkbox" wire:model="broadcastPush" class="rounded text-amber-600 focus:ring-amber-500 size-4" />
                        </div>
                    @endif

                    <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex justify-end gap-2">
                        <flux:button type="button" variant="subtle" wire:click="$set('showFormModal', false)">Batal</flux:button>
                        <flux:button type="submit" variant="primary" icon="check">
                            {{ $selectedAnnouncementId ? 'Simpan Perubahan' : 'Terbitkan Pengumuman' }}
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    @endif

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

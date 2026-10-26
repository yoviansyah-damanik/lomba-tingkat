<div class="space-y-6">
    <x-heading-section
        :title="$announcementId ? 'Edit Pengumuman' : 'Tulis Pengumuman Baru'"
        description="Terbitkan informasi resmi untuk seluruh peserta, bindamping, dan publik. Pengumuman dapat dilengkapi berkas unduhan."
    >
        <x-slot:breadcrumbs>
            <a href="{{ route('dashboard') }}" wire:navigate class="hover:underline">Dashboard</a>
            <span class="mx-2">&rsaquo;</span>
            <a href="{{ route('admin.announcements.index') }}" wire:navigate class="hover:underline">Pengumuman</a>
            <span class="mx-2">&rsaquo;</span>
            <span class="text-zinc-800 dark:text-zinc-200">{{ $announcementId ? 'Edit' : 'Tulis Baru' }}</span>
        </x-slot:breadcrumbs>

        <x-slot:actions>
            <flux:button href="{{ route('admin.announcements.index') }}" wire:navigate variant="subtle" icon="arrow-left">
                Kembali ke Daftar
            </flux:button>
        </x-slot:actions>
    </x-heading-section>

    <form wire:submit="save" class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">
        {{-- Konten utama --}}
        <div class="space-y-5 rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 sm:p-6 lg:col-span-8">
            <flux:input wire:model="title" label="Judul Pengumuman" placeholder="Contoh: Perubahan Lokasi Upacara Pembukaan LT-III" required />

            <flux:input wire:model="summary" label="Ringkasan (opsional)" placeholder="Satu kalimat yang tampil di kartu pengumuman dan awal isi" />

            <div>
                <flux:textarea wire:model.live.debounce.400ms="content" label="Isi Pengumuman" rows="16" required
                    placeholder="Tuliskan isi pengumuman lengkap secara jelas..." />
                <div class="mt-1.5 flex flex-wrap items-center justify-between gap-2 text-[11px] text-zinc-500">
                    <span>
                        Tips format: pisahkan paragraf dengan baris kosong; awali baris dengan <code class="rounded bg-zinc-100 px-1 dark:bg-zinc-800">-</code> untuk daftar berbutir
                        atau <code class="rounded bg-zinc-100 px-1 dark:bg-zinc-800">1.</code> untuk daftar bernomor; baris pendek berakhiran <code class="rounded bg-zinc-100 px-1 dark:bg-zinc-800">:</code> menjadi subjudul.
                    </span>
                    <span class="font-mono">{{ number_format(mb_strlen($content)) }} karakter</span>
                </div>
            </div>
        </div>

        {{-- Panel samping --}}
        <div class="space-y-5 lg:col-span-4 lg:sticky lg:top-24">
            {{-- Publikasi --}}
            <div class="space-y-4 rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-500">Publikasi</h3>

                <flux:select wire:model="category" label="Kategori Informasi">
                    <flux:select.option value="general">Informasi Umum</flux:select.option>
                    <flux:select.option value="schedule">Perubahan / Pengingat Jadwal</flux:select.option>
                    <flux:select.option value="technical">Petunjuk Teknis Lomba</flux:select.option>
                    <flux:select.option value="urgent">Pengumuman Penting / Darurat</flux:select.option>
                </flux:select>

                <flux:switch wire:model.live="is_published" label="Langsung terbitkan" description="Nonaktifkan untuk menyimpan sebagai draf." />

                @if(! $announcementId)
                    <div class="rounded-xl border border-amber-200 bg-amber-50/60 p-3 dark:border-amber-900/60 dark:bg-amber-950/20">
                        <flux:checkbox wire:model="broadcastPush" label="Kirim notifikasi PWA" description="Terkirim ke gawai pengguna yang memasang PWA saat pengumuman diterbitkan." />
                    </div>
                @endif
            </div>

            {{-- Berkas unduhan --}}
            <div class="rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-500">Berkas Unduhan</h3>
                <p class="mb-3 mt-1 text-[11px] text-zinc-500">PDF, Word, Excel, PowerPoint, JPG/PNG, atau ZIP &bull; maks. 10 MB per berkas &bull; maks. 10 berkas.</p>

                @if($keptAttachments || $newFiles)
                    <ul class="mb-3 space-y-1.5">
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

                <label class="flex cursor-pointer flex-col items-center gap-1 rounded-xl border-2 border-dashed border-amber-300 bg-amber-50/50 px-3 py-5 text-center transition hover:border-amber-500 hover:bg-amber-50 dark:border-amber-500/30 dark:bg-amber-950/20">
                    <flux:icon name="arrow-up-tray" class="size-6 text-amber-600" />
                    <span class="text-xs font-semibold text-amber-800 dark:text-amber-300">Pilih berkas untuk diunggah</span>
                    <input type="file" wire:model="newFiles" multiple class="sr-only"
                        accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip">
                </label>
                <div wire:loading wire:target="newFiles" class="mt-1 text-[11px] text-amber-700">Mengunggah berkas...</div>
                @error('newFiles') <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span> @enderror
                @error('newFiles.*') <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span> @enderror
            </div>

            {{-- Aksi --}}
            <div class="flex flex-col gap-2">
                <flux:button type="submit" variant="primary" icon="check" class="w-full" wire:loading.attr="disabled" wire:target="save,newFiles">
                    {{ $announcementId ? 'Simpan Perubahan' : ($is_published ? 'Terbitkan Pengumuman' : 'Simpan sebagai Draf') }}
                </flux:button>
                <flux:button href="{{ route('admin.announcements.index') }}" wire:navigate variant="subtle" class="w-full">Batal</flux:button>
            </div>
        </div>
    </form>
</div>

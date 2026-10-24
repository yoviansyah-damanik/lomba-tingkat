<div class="flex flex-col gap-6">
    {{-- Statistik --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <x-stat-card label="Total Undangan" value="{{ $this->stats['total'] }}" description="Gugus depan diizinkan" color="neutral" />
        <x-stat-card label="Belum Dipakai" value="{{ $this->stats['available'] }}" description="Kode aktif (On)" color="blue" />
        <x-stat-card label="Sudah Dipakai" value="{{ $this->stats['used'] }}" description="Gudep telah mendaftar" color="amber" />
        <x-stat-card label="Dimatikan (Off)" value="{{ $this->stats['off'] }}" description="Tidak dapat dipakai" color="purple" />
    </div>

    {{-- Filter & Pencarian --}}
    <div class="flex flex-col gap-3 rounded-2xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap gap-2">
            @foreach (['all' => 'Semua', 'available' => 'Belum Dipakai', 'used' => 'Sudah Dipakai', 'off' => 'Off'] as $key => $label)
                <flux:button size="sm" wire:click="$set('statusFilter', '{{ $key }}')" :variant="$statusFilter === '{{ $key }}' ? 'primary' : 'subtle'">
                    {{ $label }}
                </flux:button>
            @endforeach
        </div>

        <div class="w-full sm:w-72">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Cari nama sekolah, nomor gudep, atau kode..." />
        </div>
    </div>

    {{-- Tabel Undangan --}}
    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="border-b border-zinc-200 bg-zinc-50/70 font-semibold text-zinc-500 dark:border-zinc-800 dark:bg-zinc-800/40">
                    <tr>
                        <th class="px-4 py-3">Gugus Depan</th>
                        <th class="px-4 py-3">Kwarran</th>
                        <th class="px-4 py-3">Kode Undangan</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-center">On / Off</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($this->invitations as $invitation)
                        <tr wire:key="invitation-{{ $invitation->id }}">
                            <td class="px-4 py-3">
                                <div class="font-bold text-zinc-900 dark:text-zinc-100">{{ $invitation->school_name }}</div>
                                <div class="font-mono text-[11px] text-zinc-500">{{ $invitation->group_number }}</div>
                            </td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ $invitation->kwarran_name }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2" x-data="{ copied: false }">
                                    <span class="font-mono text-sm font-bold tracking-widest text-amber-800 dark:text-amber-400 select-all">{{ $invitation->formattedCode() }}</span>
                                    <flux:button size="xs" variant="subtle" icon="clipboard-document"
                                        x-on:click="navigator.clipboard.writeText('{{ $invitation->code }}'); copied = true; setTimeout(() => copied = false, 1500)"
                                        title="Salin kode">
                                        <span x-text="copied ? 'Tersalin' : 'Salin'"></span>
                                    </flux:button>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if ($invitation->used_at)
                                    <flux:badge size="sm" color="emerald">Terpakai</flux:badge>
                                    <div class="mt-1 text-[10px] text-zinc-500">{{ $invitation->used_at->translatedFormat('d M Y, H:i') }}</div>
                                @elseif ($invitation->is_active)
                                    <flux:badge size="sm" color="blue">Belum dipakai</flux:badge>
                                @else
                                    <flux:badge size="sm" color="zinc">Off</flux:badge>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($invitation->used_at)
                                    <span class="text-zinc-400">&mdash;</span>
                                @else
                                    <flux:switch :checked="$invitation->is_active" wire:click="toggleActive('{{ $invitation->id }}')" />
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                @unless ($invitation->used_at)
                                    <flux:button size="xs" variant="danger" icon="trash"
                                        wire:click="delete('{{ $invitation->id }}')"
                                        wire:confirm="Hapus undangan untuk {{ $invitation->school_name }}?"
                                        title="Hapus undangan">
                                        Hapus
                                    </flux:button>
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-zinc-500">
                                <flux:icon name="key" class="mx-auto mb-2 size-10 text-zinc-400" />
                                <div class="text-sm font-bold text-zinc-800 dark:text-zinc-200">Belum ada kode undangan</div>
                                <div class="mt-1 text-xs text-zinc-400">Tambahkan gugus depan yang diizinkan mendaftar untuk membuat kode undangan.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->invitations->hasPages())
            <div class="border-t border-zinc-200 p-4 dark:border-zinc-800">
                {{ $this->invitations->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Tambah Gugus Depan --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
            <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-3xl border border-zinc-200 bg-white p-6 shadow-2xl dark:border-zinc-800 dark:bg-zinc-900" @click.outside="$wire.showModal = false">
                <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <div>
                        <h2 class="text-base font-extrabold text-zinc-900 dark:text-zinc-100">Tambah Gugus Depan yang Diizinkan</h2>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Kode undangan dibuat otomatis setelah disimpan.</p>
                    </div>
                    <flux:button size="sm" variant="subtle" icon="x-mark" wire:click="$set('showModal', false)" aria-label="Tutup" />
                </div>

                <form wire:submit="save" class="mt-4 space-y-4">
                    <div class="flex gap-2">
                        <flux:button size="sm" type="button" wire:click="$set('bulkMode', false)" :variant="$bulkMode ? 'subtle' : 'primary'">Satu per satu</flux:button>
                        <flux:button size="sm" type="button" wire:click="$set('bulkMode', true)" :variant="$bulkMode ? 'primary' : 'subtle'">Impor banyak</flux:button>
                    </div>

                    @if ($bulkMode)
                        <flux:textarea wire:model="bulkText" rows="8" label="Daftar Gugus Depan"
                            placeholder="01-093; SMP Negeri 1 Padangsidimpuan; Utara&#10;01-095; SMP Negeri 2 Padangsidimpuan; Selatan" />
                        <p class="text-[11px] text-zinc-500">Satu baris per gugus depan: <span class="font-mono">nomor gudep; nama sekolah; kwarran</span>. Kwarran cukup ditulis Utara, Selatan, Batunadua, Hutaimbaru, Tenggara, atau Angkola Julu.</p>
                        @error('bulkText')
                            <div class="whitespace-pre-line rounded-xl border border-rose-300 bg-rose-50 p-3 text-xs text-rose-800 dark:border-rose-800 dark:bg-rose-950/50 dark:text-rose-200">{{ $message }}</div>
                        @enderror
                    @else
                        <flux:input wire:model="group_number" label="Nomor Gudep Putra (ganjil)" placeholder="Contoh: 01-093" required />
                        <flux:input wire:model="school_name" label="Nama Sekolah / Pangkalan" placeholder="Contoh: SMP Negeri 2 Padangsidimpuan" required />
                        <flux:select wire:model="kwarran_name" label="Kwartir Ranting (Kwarran)" placeholder="-- Pilih Kwarran --" required>
                            @foreach ($kwarrans as $kwarran)
                                <flux:select.option value="{{ $kwarran }}">{{ $kwarran }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    @endif

                    <div class="flex justify-end gap-2 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                        <flux:button type="button" variant="subtle" wire:click="$set('showModal', false)">Batal</flux:button>
                        <flux:button type="submit" variant="primary" icon="check">Simpan &amp; Buat Kode</flux:button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

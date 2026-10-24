<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Header --}}
    <x-heading-section
        title="Aparatur Panitia & Dewan Juri"
        description="Pengelolaan aparatur pelaksana lapangan, dewan juri penilai, serta penerbitan Kartu Tanda Pengenal Digital (B2)."
        badge="Manajemen Aparatur & Juri"
        badge-icon="shield-check"
        badge-color="amber"
    >
        <x-slot:actions>
            <flux:button wire:click="openCreateModal" variant="primary" icon="user-plus">
                Tambah Aparatur Baru
            </flux:button>
        </x-slot:actions>
    </x-heading-section>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <x-stat-card
            label="Total Aparatur"
            value="{{ $stats['total'] }}"
            description="Panitia & Juri terdaftar"
            color="neutral"
        />

        <x-stat-card
            label="Panitia Pelaksana"
            value="{{ $stats['committee'] }}"
            description="Operasional Lapangan"
            color="blue"
        />

        <x-stat-card
            label="Dewan Juri"
            value="{{ $stats['judge'] }}"
            description="Penilaian Pos & Lomba"
            color="amber"
        />

        <x-stat-card
            label="Peran Ganda (Dual)"
            value="{{ $stats['dual'] }}"
            description="Panitia merangkap Juri"
            color="purple"
        />
    </div>

    {{-- Filter & Search Bar --}}
    <div class="flex flex-col gap-3 rounded-2xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap gap-2">
            <button type="button" wire:click="$set('roleFilter', 'all')"
               class="cursor-pointer rounded-xl px-3 py-1.5 text-xs font-semibold transition {{ $roleFilter === 'all' ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-400' }}">
                Semua ({{ $stats['total'] }})
            </button>
            <button type="button" wire:click="$set('roleFilter', 'committee')"
               class="cursor-pointer rounded-xl px-3 py-1.5 text-xs font-semibold transition {{ $roleFilter === 'committee' ? 'bg-blue-600 text-white shadow-xs' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-400' }}">
                Panitia ({{ $stats['committee'] }})
            </button>
            <button type="button" wire:click="$set('roleFilter', 'judge')"
               class="cursor-pointer rounded-xl px-3 py-1.5 text-xs font-semibold transition {{ $roleFilter === 'judge' ? 'bg-amber-600 text-white shadow-xs' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-400' }}">
                Dewan Juri ({{ $stats['judge'] }})
            </button>
            <button type="button" wire:click="$set('roleFilter', 'dual')"
               class="cursor-pointer rounded-xl px-3 py-1.5 text-xs font-semibold transition {{ $roleFilter === 'dual' ? 'bg-purple-600 text-white shadow-xs' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-400' }}">
                Peran Ganda ({{ $stats['dual'] }})
            </button>
        </div>

        <div class="flex items-center gap-2">
            <div class="w-full sm:w-64">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari nama, NTA, instansi..."
                    icon="magnifying-glass"
                    clearable
                />
            </div>
            <div class="w-32">
                <flux:select wire:model.live="statusFilter" placeholder="Status">
                    <flux:select.option value="all">Semua</flux:select.option>
                    <flux:select.option value="active">Aktif</flux:select.option>
                    <flux:select.option value="inactive">Nonaktif</flux:select.option>
                </flux:select>
            </div>
        </div>
    </div>

    {{-- Table List of Officials --}}
    <div class="rounded-2xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-800 dark:bg-zinc-900 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="border-b border-zinc-200 bg-zinc-50/70 text-zinc-500 font-semibold dark:border-zinc-800 dark:bg-zinc-800/40">
                    <tr>
                        <th class="py-3 px-4">Aparatur</th>
                        <th class="py-3 px-4">Peran & Penugasan</th>
                        <th class="py-3 px-4">Instansi & Jabatan</th>
                        <th class="py-3 px-4">Kontak & NTA</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @forelse($officials as $official)
                        <tr class="hover:bg-zinc-50/60 dark:hover:bg-zinc-800/40 transition-colors">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    @if($official->avatar_path)
                                        <img src="{{ asset('storage/' . $official->avatar_path) }}" alt="{{ $official->name }}" class="size-9 rounded-full object-cover border border-zinc-200 dark:border-zinc-700">
                                    @else
                                        <div class="flex size-9 items-center justify-center rounded-full bg-zinc-100 font-bold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">
                                            {{ strtoupper(substr($official->name, 0, 2)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-bold text-zinc-900 dark:text-zinc-100">{{ $official->name }}</div>
                                        <div class="text-[11px] text-zinc-400 font-mono">{{ $official->id_number }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($official->roles as $role)
                                        @if($role->name === 'committee')
                                            <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800">
                                                Panitia
                                            </span>
                                        @elseif($role->name === 'judge')
                                            <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800">
                                                Dewan Juri
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="text-zinc-800 dark:text-zinc-200 font-medium">{{ $official->institution ?? 'Kwarcab Padangsidimpuan' }}</div>
                                <div class="text-[11px] text-zinc-400">{{ $official->position ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="text-zinc-700 dark:text-zinc-300 font-mono">{{ $official->phone_number ?? '-' }}</div>
                                <div class="text-[11px] text-zinc-400 font-mono">NTA: {{ $official->scout_id ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <button type="button" wire:click="toggleStatus('{{ $official->id }}')" class="cursor-pointer group flex items-center gap-1.5" title="Klik untuk ubah status">
                                    <span class="size-2 rounded-full {{ $official->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                    <span class="text-[11px] font-semibold {{ $official->is_active ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }} group-hover:underline">
                                        {{ $official->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </button>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    {{-- Tombol Lihat ID Card B2 --}}
                                    <a href="{{ route('digital-cards.staff', $official->id) }}" target="_blank"
                                       class="inline-flex items-center gap-1 rounded-lg border border-zinc-200 bg-white px-2 py-1 text-[11px] font-bold text-zinc-700 hover:bg-zinc-50 hover:text-amber-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 shadow-xs"
                                       title="Lihat Kartu Aparatur (B2)">
                                        <flux:icon name="identification" class="size-3" />
                                        <span>ID Card</span>
                                    </a>

                                    {{-- Edit Modal --}}
                                    <flux:button
                                        size="xs"
                                        variant="subtle"
                                        icon="pencil-square"
                                        wire:click="openEditModal('{{ $official->id }}')"
                                        title="Ubah Data Aparatur"
                                    >
                                        Ubah
                                    </flux:button>

                                    {{-- Hapus --}}
                                    <button type="button" wire:click="deleteOfficial('{{ $official->id }}')" wire:confirm="Apakah Anda yakin ingin menghapus aparatur {{ $official->name }}?"
                                       class="cursor-pointer inline-flex items-center justify-center size-7 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 dark:border-rose-900/50 dark:text-rose-400 dark:hover:bg-rose-950/40"
                                       title="Hapus Akun">
                                        <flux:icon name="trash" class="size-3" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-zinc-500">
                                <flux:icon name="user-group" class="mx-auto size-10 text-zinc-400 mb-2" />
                                <div class="font-bold text-sm text-zinc-800 dark:text-zinc-200">Tidak ada aparatur ditemukan</div>
                                <div class="text-xs text-zinc-400 mt-1">Sesuaikan filter pencarian atau tambah aparatur baru.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($officials->hasPages())
            <div class="p-4 border-t border-zinc-200 dark:border-zinc-800">
                {{ $officials->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Form Tambah / Edit Aparatur (Livewire 4 + Flux Dialog) --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="w-full max-w-lg bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-2xl p-6 overflow-y-auto max-h-[90vh]" @click.outside="$wire.showModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                    <div>
                        <h2 class="text-base font-extrabold text-zinc-900 dark:text-zinc-100">
                            {{ $editingId ? 'Edit Data Aparatur' : 'Tambah Aparatur Baru' }}
                        </h2>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                            {{ $editingId ? 'Perbarui informasi profil dan hak akses penugasan' : 'Daftarkan panitia pelaksana atau dewan juri baru' }}
                        </p>
                    </div>
                    <button type="button" wire:click="$set('showModal', false)" class="size-8 rounded-full flex items-center justify-center text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200">
                        <flux:icon name="x-mark" class="size-4" />
                    </button>
                </div>

                <form wire:submit="save" class="mt-4 space-y-4">
                    {{-- Nama Lengkap --}}
                    <div>
                        <flux:input wire:model="name" label="Nama Lengkap & Gelar" placeholder="Contoh: Kak Ahmad Siregar, S.Pd" required />
                    </div>

                    {{-- NTA & Kontak --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <flux:input wire:model="scout_id" label="Nomor Tanda Anggota (NTA)" placeholder="Contoh: 02.19.001.0012" />
                        </div>
                        <div>
                            <flux:input wire:model="phone_number" label="Nomor WhatsApp / HP" placeholder="08xxxxxxxxxx" />
                        </div>
                    </div>

                    {{-- Instansi & Jabatan --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <flux:input wire:model="institution" label="Instansi / Utusan" placeholder="Contoh: Kwarcab Padangsidimpuan" />
                        </div>
                        <div>
                            <flux:input wire:model="position" label="Jabatan di Kegiatan" placeholder="Contoh: Koordinator Lapangan" />
                        </div>
                    </div>

                    {{-- Penugasan Peran (Roles) --}}
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Peran Penugasan (Bisa Pilih Keduanya)</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center gap-2 p-3 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800/40 cursor-pointer">
                                <input type="checkbox" wire:model="selectedRoles" value="committee" class="rounded text-amber-600 focus:ring-amber-500">
                                <div>
                                    <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100 block">Panitia Pelaksana</span>
                                    <span class="text-[10px] text-zinc-500 block">Operator pos & operasional</span>
                                </div>
                            </label>
                            <label class="flex items-center gap-2 p-3 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800/40 cursor-pointer">
                                <input type="checkbox" wire:model="selectedRoles" value="judge" class="rounded text-amber-600 focus:ring-amber-500">
                                <div>
                                    <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100 block">Dewan Juri</span>
                                    <span class="text-[10px] text-zinc-500 block">Penilai lomba & rubrik</span>
                                </div>
                            </label>
                        </div>
                        @error('selectedRoles')
                            <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span>
                        @enderror

                        @if(auth()->user()->isSuperadmin())
                            <label class="mt-3 flex items-center gap-2 p-3 rounded-xl border border-amber-200 dark:border-amber-900/60 bg-amber-50 dark:bg-amber-950/30 cursor-pointer">
                                <input type="checkbox" wire:model="is_administrator" class="rounded text-amber-600 focus:ring-amber-500">
                                <div>
                                    <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100 block">Jadikan Administrator</span>
                                    <span class="text-[10px] text-zinc-500 block">Hanya untuk akun Panitia; hak kelola penuh data kegiatan</span>
                                </div>
                            </label>
                            @error('is_administrator')
                                <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span>
                            @enderror
                        @endif
                    </div>

                    {{-- Foto Pas Foto --}}
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Unggah Pas Foto (Maks 2MB)</label>
                        <input type="file" wire:model="avatar" accept="image/*" class="w-full text-xs text-zinc-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200 dark:file:bg-zinc-800 dark:file:text-zinc-300">
                        @error('avatar')
                            <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Actions --}}
                    <div class="pt-4 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-end gap-2">
                        <flux:button type="button" wire:click="$set('showModal', false)" variant="ghost" class="text-xs">
                            Batal
                        </flux:button>
                        <flux:button type="submit" variant="primary" class="text-xs font-bold">
                            {{ $editingId ? 'Simpan Perubahan' : 'Daftarkan Aparatur' }}
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

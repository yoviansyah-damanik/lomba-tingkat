<div class="space-y-6">
    {{-- Header Section --}}
    <x-heading-section
        title="Tata Kelola Pengguna & Peran"
        description="Kelola akun pengguna, penugasan peran aparatur, status aktif, dan reset kredensial akses seluruh pemangku kepentingan LT III."
    >
        <x-slot:breadcrumbs>
            <a href="{{ route('dashboard') }}" wire:navigate class="hover:underline">Dashboard</a>
            <span class="mx-2">&rsaquo;</span>
            <span class="text-zinc-800 dark:text-zinc-200">Pengguna & Peran</span>
        </x-slot:breadcrumbs>

        <x-slot:actions>
            <flux:button
                icon="user-plus"
                variant="primary"
                wire:click="openCreateModal"
            >
                Tambah Pengguna Baru
            </flux:button>
        </x-slot:actions>
    </x-heading-section>

    {{-- Filter & Pencarian Bar --}}
    <div class="rounded-2xl border border-zinc-200/80 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between">
        <div class="flex-1 relative">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari nama, email, nomor HP, instansi, atau ID..."
                class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
            />
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <select
                wire:model.live="roleFilter"
                class="rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
            >
                <option value="">Semua Peran</option>
                <option value="admin">Administrator</option>
                <option value="committee">Panitia</option>
                <option value="judge">Dewan Juri</option>
                <option value="scout_group">Gugus Depan</option>
                <option value="participant">Peserta / Pinru</option>
            </select>

            <select
                wire:model.live="statusFilter"
                class="rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
            >
                <option value="">Semua Status</option>
                <option value="active">Aktif</option>
                <option value="inactive">Nonaktif</option>
            </select>
        </div>
    </div>

    {{-- Tabel Pengguna --}}
    <div class="rounded-2xl border border-zinc-200/80 bg-white overflow-hidden shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="border-b border-zinc-200 bg-zinc-50/80 font-bold uppercase tracking-wider text-zinc-500 dark:border-zinc-800 dark:bg-zinc-800/40">
                    <tr>
                        <th class="py-3 px-4">Pengguna</th>
                        <th class="py-3 px-4">Kontak & Instansi</th>
                        <th class="py-3 px-4">Peran Resmi</th>
                        <th class="py-3 px-4 text-center">Status Akun</th>
                        <th class="py-3 px-4 text-right">Aksi Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/80">
                    @forelse($users as $user)
                        <tr class="transition hover:bg-zinc-50/60 dark:hover:bg-zinc-800/40">
                            {{-- Pengguna --}}
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-white font-bold text-xs uppercase shadow-xs">
                                        {{ $user->initials() }}
                                    </div>
                                    <div class="min-w-0">
                                        <strong class="font-bold text-zinc-900 dark:text-zinc-100 block truncate">
                                            {{ $user->name }}
                                        </strong>
                                        <span class="text-zinc-500 dark:text-zinc-400 text-[11px] font-mono block">
                                            {{ $user->email }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            {{-- Kontak & Instansi --}}
                            <td class="py-3 px-4 text-zinc-600 dark:text-zinc-400">
                                <div>{{ $user->institution ?: 'Kwartir Cabang' }}</div>
                                <div class="text-[11px] text-zinc-400">{{ $user->phone_number ?: '-' }}</div>
                            </td>

                            {{-- Peran --}}
                            <td class="py-3 px-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($user->roles as $role)
                                        @php
                                            $color = match($role->name) {
                                                'admin' => 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300',
                                                'committee' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300',
                                                'judge' => 'bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300',
                                                'scout_group' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300',
                                                default => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-300',
                                            };
                                        @endphp
                                        <span class="rounded-md px-2 py-0.5 text-[10px] font-bold uppercase {{ $color }}">
                                            {{ $role->label ?: $role->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>

                            {{-- Status --}}
                            <td class="py-3 px-4 text-center">
                                @if($user->is_active)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                        <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-2.5 py-0.5 text-[10px] font-bold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                                        <span class="size-1.5 rounded-full bg-zinc-400"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <flux:button
                                        size="xs"
                                        variant="subtle"
                                        icon="pencil-square"
                                        wire:click="openEditModal('{{ $user->id }}')"
                                        title="Edit Pengguna & Peran"
                                    />

                                    <flux:button
                                        size="xs"
                                        variant="subtle"
                                        icon="key"
                                        wire:click="openResetPasswordModal('{{ $user->id }}')"
                                        title="Reset Kata Sandi"
                                    />

                                    @if($user->id !== auth()->id())
                                        <flux:button
                                            size="xs"
                                            variant="subtle"
                                            icon="{{ $user->is_active ? 'no-symbol' : 'check-circle' }}"
                                            wire:click="toggleActive('{{ $user->id }}')"
                                            title="{{ $user->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}"
                                        />

                                        <flux:button
                                            size="xs"
                                            variant="subtle"
                                            icon="trash"
                                            wire:click="openDeleteModal('{{ $user->id }}')"
                                            title="Hapus Akun"
                                        />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-zinc-400 italic">
                                Tidak ada data pengguna yang sesuai dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-zinc-100 dark:border-zinc-800">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Tambah Pengguna Baru --}}
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="w-full max-w-lg rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-800 dark:bg-zinc-900 space-y-4">
                <div class="border-b border-zinc-100 dark:border-zinc-800 pb-3 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider">
                        Tambah Pengguna Baru
                    </h3>
                    <flux:button size="xs" variant="subtle" icon="x-mark" wire:click="$set('showCreateModal', false)" />
                </div>

                <form wire:submit="createUser" class="space-y-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Nama Lengkap:</label>
                        <input type="text" wire:model="name" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100" />
                        @error('name') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Email:</label>
                            <input type="email" wire:model="email" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100" />
                            @error('email') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Nomor HP / WhatsApp:</label>
                            <input type="text" wire:model="phone_number" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100" />
                            @error('phone_number') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Instansi / Pangkalan:</label>
                            <input type="text" wire:model="institution" placeholder="Contoh: Kwarcab Padangsidimpuan" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100" />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Jabatan:</label>
                            <input type="text" wire:model="position" placeholder="Contoh: Koordinator Penjurian" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Kata Sandi Awal:</label>
                        <input type="text" wire:model="password" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 font-mono focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100" />
                        @error('password') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Penugasan Peran Resmi:</label>
                        <div class="grid grid-cols-2 gap-2 p-3 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/40">
                            @foreach($availableRoles as $role)
                                <label class="flex items-center gap-2 text-xs cursor-pointer">
                                    <input type="checkbox" wire:model="selectedRoles" value="{{ $role->name }}" class="rounded text-amber-600 focus:ring-amber-500" />
                                    <span class="text-zinc-700 dark:text-zinc-300 font-medium">{{ $role->label ?: $role->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('selectedRoles') <span class="text-xs text-rose-500 block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex justify-end gap-2">
                        <flux:button type="button" variant="subtle" wire:click="$set('showCreateModal', false)">Batal</flux:button>
                        <flux:button type="submit" variant="primary" icon="check">Simpan Pengguna</flux:button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal Edit Pengguna --}}
    @if($showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="w-full max-w-lg rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-800 dark:bg-zinc-900 space-y-4">
                <div class="border-b border-zinc-100 dark:border-zinc-800 pb-3 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider">
                        Edit Data Pengguna
                    </h3>
                    <flux:button size="xs" variant="subtle" icon="x-mark" wire:click="$set('showEditModal', false)" />
                </div>

                <form wire:submit="updateUser" class="space-y-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Nama Lengkap:</label>
                        <input type="text" wire:model="name" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100" />
                        @error('name') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Email:</label>
                            <input type="email" wire:model="email" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100" />
                            @error('email') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Nomor HP / WhatsApp:</label>
                            <input type="text" wire:model="phone_number" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Instansi / Pangkalan:</label>
                            <input type="text" wire:model="institution" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100" />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Jabatan:</label>
                            <input type="text" wire:model="position" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Ubah Kata Sandi (Kosongkan jika tidak diubah):</label>
                        <input type="password" wire:model="password" placeholder="Minimal 6 karakter..." class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100" />
                        @error('password') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Penugasan Peran Resmi:</label>
                        <div class="grid grid-cols-2 gap-2 p-3 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/40">
                            @foreach($availableRoles as $role)
                                <label class="flex items-center gap-2 text-xs cursor-pointer">
                                    <input type="checkbox" wire:model="selectedRoles" value="{{ $role->name }}" class="rounded text-amber-600 focus:ring-amber-500" />
                                    <span class="text-zinc-700 dark:text-zinc-300 font-medium">{{ $role->label ?: $role->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('selectedRoles') <span class="text-xs text-rose-500 block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex justify-end gap-2">
                        <flux:button type="button" variant="subtle" wire:click="$set('showEditModal', false)">Batal</flux:button>
                        <flux:button type="submit" variant="primary" icon="check">Simpan Perubahan</flux:button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal Reset Password --}}
    @if($showResetPasswordModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="w-full max-w-sm rounded-2xl border border-zinc-200 bg-white p-5 shadow-xl dark:border-zinc-800 dark:bg-zinc-900 space-y-3">
                <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                    Reset Kata Sandi
                </h3>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                    Masukkan kata sandi baru untuk pengguna <strong>{{ $name }}</strong>:
                </p>

                <div>
                    <input type="text" wire:model="newPassword" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 font-mono focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100" />
                    @error('newPassword') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <flux:button type="button" variant="subtle" size="sm" wire:click="$set('showResetPasswordModal', false)">Batal</flux:button>
                    <flux:button type="button" variant="primary" size="sm" icon="key" wire:click="resetUserPassword">Reset Sandi</flux:button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Konfirmasi Hapus Pengguna --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="w-full max-w-sm rounded-2xl border border-rose-200 bg-white p-5 shadow-xl dark:border-rose-900/60 dark:bg-zinc-900 space-y-3">
                <div class="flex items-center gap-2.5 text-rose-600">
                    <flux:icon name="exclamation-triangle" class="size-5" />
                    <h3 class="text-sm font-bold">Hapus Akun Pengguna?</h3>
                </div>
                <p class="text-xs text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    Apakah Anda yakin ingin menghapus akun <strong>{{ $name }}</strong>? Tindakan ini akan mencabut seluruh akses dan tidak dapat dibatalkan.
                </p>

                <div class="pt-2 flex justify-end gap-2">
                    <flux:button type="button" variant="subtle" size="sm" wire:click="$set('showDeleteModal', false)">Batal</flux:button>
                    <flux:button type="button" variant="danger" size="sm" icon="trash" wire:click="deleteUser">Ya, Hapus</flux:button>
                </div>
            </div>
        </div>
    @endif
</div>

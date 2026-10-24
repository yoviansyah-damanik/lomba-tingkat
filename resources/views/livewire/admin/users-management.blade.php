<div class="space-y-6">
    {{-- Header Section --}}
    <x-heading-section
        title="Tata Kelola Pengguna & Peran"
        description="Pantau seluruh akun pengguna LT III. Data pengguna tidak dapat diubah dari halaman ini; Superadmin hanya dapat mengaktifkan atau menonaktifkan akun dan mengganti kata sandi."
    >
        <x-slot:breadcrumbs>
            <a href="{{ route('dashboard') }}" wire:navigate class="hover:underline">Dashboard</a>
            <span class="mx-2">&rsaquo;</span>
            <span class="text-zinc-800 dark:text-zinc-200">Pengguna & Peran</span>
        </x-slot:breadcrumbs>

    </x-heading-section>

    {{-- Filter & Pencarian Bar --}}
    <div class="rounded-2xl border border-zinc-200/80 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between">
        <div class="flex-1 relative">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari nama, username, nomor HP, instansi, atau ID..."
                class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
            />
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <select
                wire:model.live="roleFilter"
                class="rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
            >
                <option value="">Semua Peran</option>
                <option value="superadmin">Superadmin</option>
                <option value="admin">Administrator</option>
                <option value="committee">Panitia</option>
                <option value="judge">Dewan Juri</option>
                <option value="scout_group">Gugus Depan</option>
                <option value="participant">Peserta</option>
                <option value="chaperone">Bindamping</option>
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
                                            {{ $user->username }}
                                        </span>
                                        @if($user->isManagedAccount() || $user->isScoutGroup())
                                            <span class="mt-0.5 inline-flex items-center gap-1 text-[10px] text-zinc-400">
                                                <flux:icon name="lock-closed" class="size-3" />
                                                Mengikuti data gudep
                                            </span>
                                        @endif
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
                                                'superadmin' => 'bg-fuchsia-100 text-fuchsia-800 dark:bg-fuchsia-950/60 dark:text-fuchsia-300',
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
                                        icon="clock"
                                        wire:click="openHistoryModal('{{ $user->id }}')"
                                        title="Riwayat Login & Audit Log"
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

    {{-- Modal Riwayat Login & Audit Log --}}
    @if($historyUserId)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/60 p-4 backdrop-blur-xs" wire:key="history-modal">
            <div class="relative w-full max-w-3xl overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-xl dark:border-zinc-800 dark:bg-zinc-900">
                <div class="absolute right-4 top-4 z-10">
                    <flux:button wire:click="closeHistoryModal" size="sm" variant="subtle" icon="x-mark" />
                </div>
                <livewire:admin.user-activity-history :user-id="$historyUserId" :key="'history-'.$historyUserId" lazy />
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
                    Masukkan kata sandi baru untuk pengguna <strong>{{ $name }}</strong>. Untuk Peserta dan Bindamping, bawaannya adalah tanggal lahir (Ymd):
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

</div>

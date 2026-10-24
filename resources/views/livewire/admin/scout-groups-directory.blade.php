<div class="space-y-6">
    <x-heading-section
        title="Pangkalan Gugusdepan"
        description="Daftar seluruh pangkalan gugus depan yang telah terverifikasi beserta informasi lengkapnya."
        badge="Data Resmi Kontingen"
        badge-icon="building-library"
        badge-color="amber"
    >
        <x-slot:actions>
            <flux:button href="{{ route('admin.participants.index') }}" wire:navigate variant="subtle" icon="academic-cap">
                Direktori Peserta
            </flux:button>
            <flux:button href="{{ route('admin.chaperones.index') }}" wire:navigate variant="subtle" icon="user-circle">
                Pembina Pendamping
            </flux:button>
        </x-slot:actions>
    </x-heading-section>

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <x-stat-card label="Pangkalan" :value="number_format($this->stats['groups'])" description="Terverifikasi" color="amber" icon="building-library" />
        <x-stat-card label="Regu" :value="number_format($this->stats['patrols'])" description="Putra & putri" color="blue" icon="user-group" />
        <x-stat-card label="Peserta" :value="number_format($this->stats['participants'])" description="Penggalang" color="emerald" icon="academic-cap" />
        <x-stat-card label="Bindamping" :value="number_format($this->stats['chaperones'])" description="Pembina pendamping" color="rose" icon="user-circle" />
    </div>

    {{-- Pencarian & Filter --}}
    <div class="rounded-2xl border border-zinc-200/80 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-12">
            <div class="md:col-span-6">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Cari pangkalan, nomor gudep, kode, kwarran, PIC..." clearable />
            </div>
            <div class="md:col-span-4">
                <flux:select wire:model.live="kwarranFilter">
                    <flux:select.option value="all">Semua Kwarran</flux:select.option>
                    @foreach($this->kwarrans as $kwarran)
                        <flux:select.option value="{{ $kwarran }}">{{ $kwarran }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            <div class="md:col-span-2">
                <flux:select wire:model.live="perPage">
                    <flux:select.option value="15">15 / halaman</flux:select.option>
                    <flux:select.option value="25">25 / halaman</flux:select.option>
                    <flux:select.option value="50">50 / halaman</flux:select.option>
                </flux:select>
            </div>
        </div>

        <div class="mt-3 flex items-center justify-between text-xs text-zinc-500">
            <span>Ditemukan: <strong class="text-zinc-800 dark:text-zinc-200">{{ $groups->total() }}</strong> pangkalan</span>
            @if(filled($search) || $kwarranFilter !== 'all')
                <flux:button wire:click="resetFilters" variant="subtle" size="xs" icon="x-mark">Reset Filter</flux:button>
            @endif
        </div>
    </div>

    {{-- Tabel Pangkalan --}}
    <div class="overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-zinc-200 bg-zinc-50/75 text-xs font-semibold text-zinc-600 dark:border-zinc-800 dark:bg-zinc-800/50 dark:text-zinc-400">
                    <tr>
                        <th class="py-3.5 pl-6 pr-3">Pangkalan</th>
                        <th class="px-3 py-3.5">Nomor Gudep & Kwarran</th>
                        <th class="px-3 py-3.5">PIC / Kontak</th>
                        <th class="px-3 py-3.5 text-center">Regu</th>
                        <th class="px-3 py-3.5 text-center">Peserta</th>
                        <th class="px-3 py-3.5 text-center">Bindamping</th>
                        <th class="py-3.5 pl-3 pr-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 text-zinc-700 dark:divide-zinc-800 dark:text-zinc-300">
                    @forelse($groups as $group)
                        <tr wire:key="group-{{ $group->id }}" class="transition-colors hover:bg-zinc-50/50 dark:hover:bg-zinc-800/50">
                            <td class="py-4 pl-6 pr-3 align-top">
                                <span class="font-mono text-xs font-bold text-amber-700 dark:text-amber-400">{{ $group->registration_code }}</span>
                                <div class="font-bold text-zinc-900 dark:text-white">{{ $group->school_name }}</div>
                            </td>
                            <td class="px-3 py-4 align-top">
                                <div class="font-mono text-xs font-semibold">{{ $group->group_number ?: '-' }}</div>
                                <div class="text-xs text-zinc-500">{{ $group->kwarran_name }}</div>
                            </td>
                            <td class="px-3 py-4 align-top">
                                <div class="text-xs font-medium text-zinc-800 dark:text-zinc-200">{{ $group->contact_person ?: '-' }}</div>
                                <div class="text-xs text-zinc-500">{{ $group->contact_phone ?: '-' }}</div>
                            </td>
                            <td class="px-3 py-4 text-center align-top font-semibold">{{ $group->patrols_count }}</td>
                            <td class="px-3 py-4 text-center align-top font-semibold">{{ $group->participants_count }}</td>
                            <td class="px-3 py-4 text-center align-top font-semibold">{{ $group->chaperones_count }}</td>
                            <td class="py-4 pl-3 pr-6 text-right align-top">
                                <flux:button wire:click="openDetail('{{ $group->id }}')" variant="subtle" size="sm" icon="eye">
                                    Lihat
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-zinc-500">
                                <flux:icon name="building-library" class="mx-auto size-10 text-zinc-400 opacity-60" />
                                <div class="mt-2 font-medium">Belum ada pangkalan terverifikasi</div>
                                <div class="text-xs">Ubah kata kunci atau filter kwarran Anda.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($groups->hasPages())
            <div class="border-t border-zinc-200 px-6 py-4 dark:border-zinc-800">
                {{ $groups->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Informasi Lengkap Pangkalan --}}
    @if($selectedGroupId)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-zinc-950/60 p-4 backdrop-blur-xs" wire:key="detail-modal">
            <div class="relative w-full max-w-4xl overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-2xl dark:border-zinc-800 dark:bg-zinc-900">
                <div class="absolute right-4 top-4 z-10">
                    <flux:button wire:click="closeDetail" variant="subtle" size="sm" icon="x-mark" />
                </div>
                <livewire:admin.scout-group-detail :scout-group-id="$selectedGroupId" :key="'detail-'.$selectedGroupId" lazy />
            </div>
        </div>
    @endif
</div>

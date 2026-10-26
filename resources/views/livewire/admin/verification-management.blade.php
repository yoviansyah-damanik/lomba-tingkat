<div class="space-y-6">
    {{-- Header Section --}}
    <x-heading-section
        title="Verifikasi Pendaftaran Kontingen"
        description="Pemeriksaan berkas pangkalan Gugus Depan, validasi regu putra/putri, bindamping, dan persetujuan akreditasi kontingen."
        badge="Verifikasi Administrasi"
        badge-icon="shield-check"
        badge-color="amber"
    />

    {{-- Stats Cards & Interactive Filter Tabs --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
        <x-stat-card
            as-button
            wire:click="$set('statusFilter', 'all')"
            :active="$statusFilter === 'all'"
            label="Semua Pangkalan"
            value="{{ $stats['all'] }}"
            description="Seluruh kontingen"
            color="amber"
        />

        <x-stat-card
            as-button
            wire:click="$set('statusFilter', 'submitted')"
            :active="$statusFilter === 'submitted'"
            label="Menunggu Verifikasi"
            value="{{ $stats['submitted'] }}"
            description="Perlu ditinjau"
            color="blue"
        />

        <x-stat-card
            as-button
            wire:click="$set('statusFilter', 'verified')"
            :active="$statusFilter === 'verified'"
            label="Telah Disetujui"
            value="{{ $stats['verified'] }}"
            description="Lolos berkas"
            color="emerald"
        />

        <x-stat-card
            as-button
            wire:click="$set('statusFilter', 'needs_revision')"
            :active="$statusFilter === 'needs_revision'"
            label="Perlu Perbaikan"
            value="{{ $stats['needs_revision'] }}"
            description="Menunggu revisi"
            color="orange"
        />

        <x-stat-card
            as-button
            wire:click="$set('statusFilter', 'rejected')"
            :active="$statusFilter === 'rejected'"
            label="Ditolak"
            value="{{ $stats['rejected'] }}"
            description="Tidak memenuhi syarat"
            color="rose"
        />
    </div>

    {{-- Filter & Search Toolbar --}}
    <div class="flex flex-col gap-3 rounded-2xl border border-zinc-200/80 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex flex-1 items-center gap-3">
            <div class="w-full sm:max-w-md">
                <flux:input 
                    wire:model.live.debounce.300ms="search" 
                    icon="magnifying-glass" 
                    placeholder="Cari sekolah, no. gudep, kode registrasi, kwarran..." 
                    clearable 
                />
            </div>

            @if($search || $statusFilter !== 'all')
                <button 
                    type="button" 
                    wire:click="$set('search', ''); $set('statusFilter', 'all');" 
                    class="text-xs font-semibold text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200"
                >
                    Reset Filter
                </button>
            @endif
        </div>

        <div class="text-xs text-zinc-500">
            Ditemukan: <strong class="text-zinc-800 dark:text-zinc-200">{{ $scoutGroups->total() }}</strong> kontingen
        </div>
    </div>

    {{-- Contingents Table --}}
    <div class="overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-zinc-200 bg-zinc-50/75 text-xs font-semibold text-zinc-600 dark:border-zinc-800 dark:bg-zinc-800/50 dark:text-zinc-400">
                    <tr>
                        <th class="py-3.5 pl-6 pr-3">Kode & Pangkalan</th>
                        <th class="px-3 py-3.5">Nomor Gudep & Kwarran</th>
                        <th class="px-3 py-3.5">PIC / Kontak</th>
                        <th class="px-3 py-3.5">Regu & Peserta</th>
                        <th class="px-3 py-3.5">Bindamping</th>
                        <th class="px-3 py-3.5">Status</th>
                        <th class="py-3.5 pl-3 pr-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 text-zinc-700 dark:text-zinc-300">
                    @forelse($scoutGroups as $group)
                        @php
                            $totalParticipants = $group->patrols->sum(fn($p) => $p->participants->count());
                        @endphp
                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/50 transition-colors">
                            {{-- Kode & Pangkalan --}}
                            <td class="py-4 pl-6 pr-3 align-top">
                                <span class="font-mono text-xs font-bold text-amber-700 dark:text-amber-400">
                                    {{ $group->registration_code }}
                                </span>
                                <div class="font-bold text-zinc-900 dark:text-white">
                                    {{ $group->school_name }}
                                </div>
                                <div class="text-xs text-zinc-500 line-clamp-1">
                                    {{ $group->address ?? 'Alamat belum diisi' }}
                                </div>
                            </td>

                            {{-- Gudep & Kwarran --}}
                            <td class="px-3 py-4 align-top">
                                <div class="font-semibold text-zinc-800 dark:text-zinc-200">
                                    {{ $group->group_number }}
                                </div>
                                <div class="text-xs text-zinc-500">
                                    Kwarran: <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $group->kwarran_name ?? '-' }}</span>
                                </div>
                            </td>

                            {{-- PIC / Kontak --}}
                            <td class="px-3 py-4 align-top">
                                <div class="font-medium text-zinc-900 dark:text-white">
                                    {{ $group->contact_person }}
                                </div>
                                <div class="text-xs text-zinc-500">
                                    {{ $group->contact_phone }}
                                </div>
                            </td>

                            {{-- Regu & Peserta --}}
                            <td class="px-3 py-4 align-top">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    @forelse($group->patrols as $patrol)
                                        <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-semibold {{ $patrol->gender === 'male' ? 'bg-sky-500/10 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300' : 'bg-rose-500/10 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300' }}">
                                            {{ $patrol->name }} ({{ $patrol->participants->count() }})
                                        </span>
                                    @empty
                                        <span class="text-xs italic text-zinc-400">Belum ada regu</span>
                                    @endforelse
                                </div>
                                <div class="mt-1 text-[11px] text-zinc-500">
                                    Total: <strong>{{ $totalParticipants }}</strong> peserta
                                </div>
                            </td>

                            {{-- Bindamping --}}
                            <td class="px-3 py-4 align-top">
                                <div class="space-y-0.5">
                                    @forelse($group->chaperones as $chap)
                                        <div class="text-xs">
                                            <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $chap->name }}</span>
                                            <span class="text-[10px] text-zinc-500">({{ $chap->gender === 'male' ? 'Pa' : 'Pi' }})</span>
                                        </div>
                                    @empty
                                        <span class="text-xs italic text-zinc-400">Belum ada</span>
                                    @endforelse
                                </div>
                            </td>

                            {{-- Status --}}
                            <td class="px-3 py-4 align-top">
                                @if($group->verification_status->value === 'verified')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-xs font-semibold text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400">
                                        <flux:icon name="check-circle" class="size-3.5" />
                                        Disetujui
                                    </span>
                                @elseif($group->verification_status->value === 'needs_revision')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-orange-500/10 px-2.5 py-0.5 text-xs font-semibold text-orange-600 dark:bg-orange-500/20 dark:text-orange-400">
                                        <flux:icon name="exclamation-circle" class="size-3.5" />
                                        Perlu Revisi
                                    </span>
                                @elseif($group->verification_status->value === 'rejected')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-500/10 px-2.5 py-0.5 text-xs font-semibold text-rose-600 dark:bg-rose-500/20 dark:text-rose-400">
                                        <flux:icon name="x-circle" class="size-3.5" />
                                        Ditolak
                                    </span>
                                @elseif($group->verification_status->value === 'submitted')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-500/10 px-2.5 py-0.5 text-xs font-semibold text-blue-600 dark:bg-blue-500/20 dark:text-blue-400">
                                        <flux:icon name="clock" class="size-3.5" />
                                        Menunggu
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                                        Draft
                                    </span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="py-4 pl-3 pr-6 align-top text-right">
                                <flux:button 
                                    as="a"
                                    href="{{ route('admin.scout-groups.show', $group->id) }}" 
                                    variant="subtle" 
                                    size="sm" 
                                    icon="eye"
                                    title="Tinjau Berkas & Pendaftaran Kontingen" wire:navigate>
                                    Tinjau
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-zinc-500">
                                <flux:icon name="user-group" class="mx-auto size-10 text-zinc-400 opacity-60" />
                                <div class="mt-2 font-medium">Tidak ada kontingen ditemukan</div>
                                <div class="text-xs">Ubah filter status atau kata kunci pencarian Anda.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($scoutGroups->hasPages())
            <div class="border-t border-zinc-200 px-6 py-4 dark:border-zinc-800">
                {{ $scoutGroups->links() }}
            </div>
        @endif
    </div>
</div>

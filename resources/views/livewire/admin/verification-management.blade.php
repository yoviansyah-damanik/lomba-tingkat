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
                                <div class="flex items-center justify-end gap-2">
                                    <flux:button 
                                        variant="subtle" 
                                        size="sm" 
                                        icon="eye" 
                                        wire:click="openReviewModal('{{ $group->id }}', 'view')"
                                        title="Quick Review / Verifikasi"
                                    >
                                        Tinjau
                                    </flux:button>

                                    <a 
                                        href="{{ route('admin.scout-groups.show', $group->id) }}" 
                                        class="inline-flex items-center gap-1 rounded-lg border border-zinc-200 bg-white px-2.5 py-1 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300"
                                        title="Buka Halaman Detail Penuh"
                                    >
                                        Detail
                                    </a>
                                </div>
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

    {{-- Quick Review & Verification Modal --}}
    <flux:modal wire:model="showReviewModal" class="max-w-4xl">
        <div class="space-y-6">
            {{-- Header Profil Pangkalan --}}
            <div class="border-b border-zinc-200 pb-4 dark:border-zinc-800">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-mono text-xs font-bold text-amber-600 dark:text-amber-400">
                            {{ $selectedGroup?->registration_code }}
                        </span>
                        <span class="rounded bg-zinc-100 px-2 py-0.5 text-xs text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                            Gudep: {{ $selectedGroup?->group_number }}
                        </span>
                        @if($selectedGroup?->verification_status)
                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold {{ $selectedGroup->verification_status->badgeClasses() }}">
                                {{ $selectedGroup->verification_status->label() }}
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                        <span class="rounded-full bg-zinc-100 px-2.5 py-0.5 font-medium dark:bg-zinc-800 dark:text-zinc-300">
                            {{ $selectedGroup?->patrols?->count() ?? 0 }} Regu
                        </span>
                        <span class="rounded-full bg-zinc-100 px-2.5 py-0.5 font-medium dark:bg-zinc-800 dark:text-zinc-300">
                            {{ $selectedGroup?->patrols?->sum(fn($p) => $p->participants->count()) ?? 0 }} Peserta
                        </span>
                        <span class="rounded-full bg-zinc-100 px-2.5 py-0.5 font-medium dark:bg-zinc-800 dark:text-zinc-300">
                            {{ $selectedGroup?->chaperones?->count() ?? 0 }} Bindamping
                        </span>
                    </div>
                </div>

                <flux:heading size="lg" class="mt-2 text-zinc-900 dark:text-zinc-100">
                    {{ $selectedGroup?->school_name }}
                </flux:heading>

                <flux:subheading class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-zinc-500 dark:text-zinc-400">
                    <span>Kwarran: <strong class="text-zinc-700 dark:text-zinc-300">{{ $selectedGroup?->kwarran_name ?? '-' }}</strong></span>
                    <span>&bull;</span>
                    <span>PIC: <strong class="text-zinc-700 dark:text-zinc-300">{{ $selectedGroup?->contact_person ?? '-' }}</strong> ({{ $selectedGroup?->contact_phone ?? '-' }})</span>
                    @if($selectedGroup?->address)
                        <span>&bull;</span>
                        <span class="truncate max-w-xs">{{ $selectedGroup->address }}</span>
                    @endif
                </flux:subheading>
            </div>

            {{-- Catatan Revisi Sebelumnya (jika ada) --}}
            @if($selectedGroup?->revision_notes && $actionType === 'view')
                <div class="rounded-xl border border-orange-200 bg-orange-50/80 p-3.5 text-xs text-orange-900 dark:border-orange-900/60 dark:bg-orange-950/30 dark:text-orange-200">
                    <div class="font-bold flex items-center gap-1.5 text-orange-800 dark:text-orange-300">
                        <flux:icon name="exclamation-triangle" class="size-4" />
                        <span>Catatan Riwayat Revisi / Catatan Verifikator:</span>
                    </div>
                    <p class="mt-1 leading-relaxed">{{ $selectedGroup->revision_notes }}</p>
                </div>
            @endif

            {{-- Contingent Content Overview --}}
            <div class="space-y-5">
                {{-- Regu & Anggota --}}
                <div class="rounded-xl border border-zinc-200/80 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-800/40">
                    <div class="flex items-center justify-between mb-3">
                        <div class="font-semibold text-xs uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                            Daftar Regu & Anggota Penggalang
                        </div>
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">
                            {{ $selectedGroup?->patrols?->count() ?? 0 }} Regu Terdaftar
                        </span>
                    </div>

                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                        @forelse($selectedGroup?->patrols ?? [] as $p)
                            <div class="rounded-lg border border-zinc-200 bg-white p-3.5 shadow-2xs dark:border-zinc-700 dark:bg-zinc-800">
                                {{-- Header Regu --}}
                                <div class="flex items-center justify-between border-b border-zinc-100 pb-2.5 dark:border-zinc-700/60">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-sm text-zinc-900 dark:text-white">Regu {{ $p->patrol_name }}</span>
                                        <span class="rounded px-2 py-0.5 text-[11px] font-semibold {{ $p->isBoys() ? 'bg-sky-100 text-sky-800 dark:bg-sky-950/60 dark:text-sky-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300' }}">
                                            {{ $p->isBoys() ? 'Putra' : 'Putri' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-xs">
                                        @if($p->group_number)
                                            <span class="font-mono text-[11px] text-zinc-400">{{ $p->group_number }}</span>
                                        @endif
                                        <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-[11px] font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                            {{ $p->participants->count() }} orang
                                        </span>
                                    </div>
                                </div>

                                {{-- Daftar Peserta dalam Regu --}}
                                <div class="mt-2.5 divide-y divide-zinc-100 text-xs dark:divide-zinc-700/60">
                                    @forelse($p->orderedParticipants() as $part)
                                        <div class="flex items-center justify-between py-2 first:pt-1 last:pb-0 gap-2">
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-1.5">
                                                    @if($part->isLeader())
                                                        <span class="shrink-0 rounded px-1.5 py-0.2 text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-700/50">PINRU</span>
                                                    @elseif($part->isAssistantLeader())
                                                        <span class="shrink-0 rounded px-1.5 py-0.2 text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-300 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-700/50">WAPINRU</span>
                                                    @else
                                                        <span class="shrink-0 rounded px-1.5 py-0.2 text-[10px] font-medium bg-zinc-100 text-zinc-600 border border-zinc-200 dark:bg-zinc-700/60 dark:text-zinc-300 dark:border-zinc-600">ANGGOTA</span>
                                                    @endif
                                                    <span class="font-semibold text-zinc-900 dark:text-zinc-100 truncate">
                                                        {{ $part->full_name }}
                                                    </span>
                                                </div>
                                                <div class="mt-0.5 flex flex-wrap items-center gap-x-2 text-[11px] text-zinc-500 dark:text-zinc-400">
                                                    <span>NTA: <span class="font-mono text-zinc-700 dark:text-zinc-300">{{ $part->scout_id ?: '-' }}</span></span>
                                                    @if($part->class_grade)
                                                        <span>&bull;</span>
                                                        <span>Kls {{ $part->class_grade }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="shrink-0 text-right">
                                                <span class="font-mono text-[10px] text-zinc-400 dark:text-zinc-500">
                                                    {{ $part->id_number }}
                                                </span>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="py-2 text-center text-xs text-zinc-400 italic">
                                            Belum ada data peserta
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        @empty
                            <div class="col-span-2 rounded-lg border border-dashed border-zinc-300 p-6 text-center text-xs text-zinc-400 dark:border-zinc-700">
                                Belum ada data regu yang didaftarkan
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Bindamping --}}
                <div class="rounded-xl border border-zinc-200/80 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-800/40">
                    <div class="flex items-center justify-between mb-3">
                        <div class="font-semibold text-xs uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                            Pembina Pendamping (Bindamping)
                        </div>
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">
                            {{ $selectedGroup?->chaperones?->count() ?? 0 }} Orang Terdaftar
                        </span>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @forelse($selectedGroup?->chaperones ?? [] as $chap)
                            <div class="rounded-lg border border-zinc-200 bg-white p-3.5 shadow-2xs dark:border-zinc-700 dark:bg-zinc-800">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="font-bold text-sm text-zinc-900 dark:text-white truncate">
                                            {{ $chap->full_name }}
                                        </div>
                                        <div class="mt-1">
                                            <span class="inline-flex items-center rounded px-2 py-0.5 text-[11px] font-semibold {{ $chap->isMale() ? 'bg-sky-100 text-sky-800 dark:bg-sky-950/60 dark:text-sky-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300' }}">
                                                {{ $chap->isMale() ? 'Bindamping Putra' : 'Bindamping Putri' }}
                                            </span>
                                        </div>
                                    </div>
                                    <span class="shrink-0 rounded bg-zinc-100 px-1.5 py-0.5 font-mono text-[10px] text-zinc-500 dark:bg-zinc-700 dark:text-zinc-300">
                                        {{ $chap->id_number }}
                                    </span>
                                </div>

                                <div class="mt-3 space-y-1.5 border-t border-zinc-100 pt-2.5 text-xs text-zinc-600 dark:border-zinc-700/60 dark:text-zinc-400">
                                    <div class="flex justify-between items-center">
                                        <span class="text-zinc-400">NTA Pramuka:</span>
                                        <span class="font-mono font-medium text-zinc-800 dark:text-zinc-200">{{ $chap->scout_id ?: '-' }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-zinc-400">NIK:</span>
                                        <span class="font-mono font-medium text-zinc-800 dark:text-zinc-200">{{ $chap->national_identity_number ?: '-' }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-zinc-400">No. HP / WA:</span>
                                        @if($chap->phone_number)
                                            <span class="font-mono font-semibold text-emerald-600 dark:text-emerald-400">
                                                {{ $chap->phone_number }}
                                            </span>
                                        @else
                                            <span class="font-mono text-zinc-400">-</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-2 rounded-lg border border-dashed border-zinc-300 p-4 text-center text-xs text-zinc-400 dark:border-zinc-700">
                                Belum ada data pembina pendamping
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Action Panel Form --}}
                @if($actionType === 'revision')
                    <div class="rounded-xl border border-orange-200 bg-orange-50/70 p-4 space-y-3 dark:border-orange-900 dark:bg-orange-950/20">
                        <div class="font-semibold text-sm text-orange-900 dark:text-orange-200">
                            Catatan Instruksi Perbaikan Berkas
                        </div>
                        <flux:textarea 
                            wire:model="revisionNotes" 
                            placeholder="Jelaskan secara rinci data atau berkas yang perlu diperbaiki oleh pangkalan..." 
                            rows="3" 
                        />
                        <div class="flex justify-end gap-2">
                            <flux:button type="button" variant="subtle" size="sm" wire:click="$set('actionType', 'view')">
                                Batal
                            </flux:button>
                            <flux:button type="button" variant="primary" size="sm" class="bg-orange-600 hover:bg-orange-700" wire:click="requestRevision">
                                Kirim Permintaan Revisi
                            </flux:button>
                        </div>
                    </div>
                @elseif($actionType === 'reject')
                    <div class="rounded-xl border border-rose-200 bg-rose-50/70 p-4 space-y-3 dark:border-rose-900 dark:bg-rose-950/20">
                        <div class="font-semibold text-sm text-rose-900 dark:text-rose-200">
                            Alasan Penolakan Pendaftaran
                        </div>
                        <flux:textarea 
                            wire:model="rejectReason" 
                            placeholder="Alasan kontingen tidak dapat diterima pada ajang LT III..." 
                            rows="3" 
                        />
                        <div class="flex justify-end gap-2">
                            <flux:button type="button" variant="subtle" size="sm" wire:click="$set('actionType', 'view')">
                                Batal
                            </flux:button>
                            <flux:button type="button" variant="danger" size="sm" wire:click="rejectContingent">
                                Tolak Pendaftaran
                            </flux:button>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Footer Decision Buttons --}}
            <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <div class="flex items-center gap-2">
                    @if($selectedGroup)
                        <a 
                            href="{{ route('admin.scout-groups.show', $selectedGroup->id) }}" 
                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200 underline"
                        >
                            <flux:icon name="arrow-top-right-on-square" class="size-3.5" />
                            Buka Berkas Lengkap & Cetak Dokumen
                        </a>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    <flux:button type="button" variant="subtle" wire:click="$set('showReviewModal', false)">
                        Tutup
                    </flux:button>

                    @if(auth()->user()?->isAdmin() && $actionType === 'view')
                        <flux:button type="button" variant="subtle" class="text-orange-600 hover:bg-orange-50 dark:hover:bg-orange-950/30" wire:click="$set('actionType', 'revision')">
                            Minta Revisi
                        </flux:button>
                        <flux:button type="button" variant="subtle" class="text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30" wire:click="$set('actionType', 'reject')">
                            Tolak
                        </flux:button>
                        <flux:button type="button" variant="primary" class="bg-emerald-600 hover:bg-emerald-700 text-white" wire:click="verifyContingent('{{ $selectedGroup?->id }}')">
                            Setujui & Verifikasi
                        </flux:button>
                    @endif
                </div>
            </div>
        </div>
    </flux:modal>
</div>

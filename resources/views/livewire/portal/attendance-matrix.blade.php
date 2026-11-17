<div class="space-y-6">
    {{-- Header Section --}}
    <x-heading-section
        title="Matriks Penyesuaian Presensi"
        description="Pusat rekonsiliasi dan penyesuaian data kehadiran yang tertinggal dalam format matriks lintas sesi pos dan perkemahan."
        badge="Penyesuaian Kehadiran Tertinggal"
        badge-icon="table-cells"
        badge-color="emerald"
    >
        <x-slot:breadcrumbs>
            <a href="{{ route('dashboard') }}" wire:navigate class="hover:underline">Dashboard</a>
            <span class="mx-2">&rsaquo;</span>
            <a href="{{ route('attendance.index') }}" wire:navigate class="hover:underline">Portal Presensi</a>
            <span class="mx-2">&rsaquo;</span>
            <span class="text-zinc-800 dark:text-zinc-200">Matriks Penyesuaian</span>
        </x-slot:breadcrumbs>

        <x-slot:actions>
            <flux:button href="{{ route('attendance.recap') }}" variant="subtle" icon="clipboard-document-list" wire:navigate>
                Rekapitulasi Presensi
            </flux:button>
            <flux:button href="{{ route('attendance.index') }}" variant="subtle" icon="qr-code" wire:navigate>
                Portal Presensi
            </flux:button>
        </x-slot:actions>
    </x-heading-section>

    {{-- Tabs Scope & Kontrol Filter --}}
    <div class="rounded-2xl border border-zinc-200/80 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 space-y-4">
        {{-- Baris 1: Pilihan Scope (Regu / Peserta / Bindamping) & Kategori --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-zinc-100 dark:border-zinc-800 pb-3">
            <div class="flex items-center gap-1.5">
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 mr-1">Cakupan Data:</span>
                <flux:button
                    size="sm"
                    icon="user-group"
                    :variant="$scope === 'patrol' ? 'primary' : 'subtle'"
                    wire:click="$set('scope', 'patrol')"
                >
                    Per Regu
                </flux:button>
                <flux:button
                    size="sm"
                    icon="academic-cap"
                    :variant="$scope === 'participant' ? 'primary' : 'subtle'"
                    wire:click="$set('scope', 'participant')"
                >
                    Per Peserta
                </flux:button>
                <flux:button
                    size="sm"
                    icon="user-circle"
                    :variant="$scope === 'chaperone' ? 'primary' : 'subtle'"
                    wire:click="$set('scope', 'chaperone')"
                >
                    Pembina Pendamping
                </flux:button>
            </div>

            <div class="flex items-center gap-1.5">
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 mr-1">Golongan:</span>
                <flux:button
                    size="xs"
                    :variant="$genderFilter === 'all' ? 'primary' : 'subtle'"
                    wire:click="$set('genderFilter', 'all')"
                >
                    Semua
                </flux:button>
                <flux:button
                    size="xs"
                    :variant="$genderFilter === 'boys' ? 'primary' : 'subtle'"
                    wire:click="$set('genderFilter', 'boys')"
                >
                    Putra
                </flux:button>
                <flux:button
                    size="xs"
                    :variant="$genderFilter === 'girls' ? 'primary' : 'subtle'"
                    wire:click="$set('genderFilter', 'girls')"
                >
                    Putri
                </flux:button>
            </div>
        </div>

        {{-- Baris 2: Filter Sesi & Pencarian --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            {{-- Pencarian --}}
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-zinc-500 mb-1">Cari Entitas:</label>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Nama regu, NPP, sekolah..."
                    class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs font-semibold text-zinc-900 focus:border-emerald-500 focus:bg-white focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                >
            </div>

            {{-- Tipe Sesi --}}
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-zinc-500 mb-1">Tipe Sesi:</label>
                <select
                    wire:model.live="sessionTypeFilter"
                    class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs font-semibold text-zinc-900 focus:border-emerald-500 focus:bg-white focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                >
                    <option value="all">Semua Sesi (Lomba &amp; Umum)</option>
                    <option value="competition">Sesi Pos Mata Lomba</option>
                    <option value="general">Sesi Kehadiran Umum</option>
                </select>
            </div>

            {{-- Tanggal Sesi --}}
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-zinc-500 mb-1">Tanggal Sesi:</label>
                <select
                    wire:model.live="sessionDateFilter"
                    class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs font-semibold text-zinc-900 focus:border-emerald-500 focus:bg-white focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                >
                    <option value="all">Seluruh Tanggal Kegiatan</option>
                    @foreach($sessionDates as $sd)
                        <option value="{{ $sd['value'] }}">{{ $sd['label'] }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Baris Per Halaman --}}
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-zinc-500 mb-1">Tampilkan Baris:</label>
                <select
                    wire:model.live="perPage"
                    class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs font-semibold text-zinc-900 focus:border-emerald-500 focus:bg-white focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                >
                    <option value="15">15 Baris</option>
                    <option value="25">25 Baris</option>
                    <option value="50">50 Baris</option>
                    <option value="100">100 Baris</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Keterangan Legenda Status & Toolbar Tindakan Massal --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs bg-zinc-50/80 dark:bg-zinc-900/60 p-3 rounded-2xl border border-zinc-200/80 dark:border-zinc-800">
        <div class="flex flex-wrap items-center gap-3">
            <span class="font-bold text-zinc-500 uppercase tracking-wider text-[10px]">Legenda:</span>
            <span class="inline-flex items-center gap-1 font-semibold text-emerald-700 dark:text-emerald-400">
                <span class="flex size-5 items-center justify-center rounded-md bg-emerald-100 dark:bg-emerald-950/70 border border-emerald-300 dark:border-emerald-800 text-[10px] font-bold">H</span>
                Hadir
            </span>
            <span class="inline-flex items-center gap-1 font-semibold text-amber-700 dark:text-amber-400">
                <span class="flex size-5 items-center justify-center rounded-md bg-amber-100 dark:bg-amber-950/70 border border-amber-300 dark:border-amber-800 text-[10px] font-bold">T</span>
                Terlambat
            </span>
            <span class="inline-flex items-center gap-1 font-semibold text-blue-700 dark:text-blue-400">
                <span class="flex size-5 items-center justify-center rounded-md bg-blue-100 dark:bg-blue-950/70 border border-blue-300 dark:border-blue-800 text-[10px] font-bold">I</span>
                Izin
            </span>
            <span class="inline-flex items-center gap-1 font-semibold text-rose-700 dark:text-rose-400">
                <span class="flex size-5 items-center justify-center rounded-md bg-rose-100 dark:bg-rose-950/70 border border-rose-300 dark:border-rose-800 text-[10px] font-bold">A</span>
                Alpa
            </span>
            <span class="inline-flex items-center gap-1 font-semibold text-zinc-500 dark:text-zinc-400">
                <span class="flex size-5 items-center justify-center rounded-md bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-[10px] font-bold">-</span>
                Belum Presensi
            </span>
        </div>

        @if(count($selectedEntityIds) > 0)
            <div class="flex items-center gap-2">
                <span class="font-bold text-emerald-600 dark:text-emerald-400">
                    {{ count($selectedEntityIds) }} baris terpilih
                </span>
                <flux:button size="xs" variant="primary" icon="pencil-square" wire:click="openBatchModal">
                    Penyesuaian Massal
                </flux:button>
                <flux:button size="xs" variant="subtle" wire:click="deselectAllRows">
                    Batal Pilih
                </flux:button>
            </div>
        @else
            <div class="flex items-center gap-2">
                <flux:button size="xs" variant="subtle" wire:click="selectAllPageRows">
                    Pilih Seluruh Halaman
                </flux:button>
            </div>
        @endif
    </div>

    {{-- Tabel Matriks Presensi --}}
    <div class="rounded-2xl border border-zinc-200/80 bg-white dark:border-zinc-800 dark:bg-zinc-900 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-zinc-50 dark:bg-zinc-800/70 border-b border-zinc-200 dark:border-zinc-800 text-zinc-500 dark:text-zinc-400 text-[10px] uppercase font-bold tracking-wider">
                        {{-- Kolom Sticky: Checkbox --}}
                        <th class="py-3 px-3 text-center w-10 sticky left-0 z-20 bg-zinc-50 dark:bg-zinc-800">
                            <input
                                type="checkbox"
                                wire:click="{{ count($selectedEntityIds) === $entities->count() ? 'deselectAllRows' : 'selectAllPageRows' }}"
                                {{ count($selectedEntityIds) > 0 && count($selectedEntityIds) === $entities->count() ? 'checked' : '' }}
                                class="rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500 dark:border-zinc-700 dark:bg-zinc-900"
                            >
                        </th>

                        {{-- Kolom Sticky: NPP / ID --}}
                        <th class="py-3 px-3 text-center w-20 sticky left-10 z-20 bg-zinc-50 dark:bg-zinc-800">
                            {{ $scope === 'patrol' ? 'NPP' : 'ID / No' }}
                        </th>

                        {{-- Kolom Sticky: Nama Entitas & Pangkalan --}}
                        <th class="py-3 px-4 min-w-[200px] sticky left-30 z-20 bg-zinc-50 dark:bg-zinc-800 border-r border-zinc-200 dark:border-zinc-800 shadow-xs">
                            {{ $scope === 'patrol' ? 'Regu & Pangkalan' : ($scope === 'participant' ? 'Peserta & Regu' : 'Pembina & Sekolah') }}
                        </th>

                        {{-- Kolom Sesi Presensi --}}
                        @forelse($sessions as $session)
                            <th class="py-2.5 px-2 text-center min-w-[100px] max-w-[130px] border-r border-zinc-100 dark:border-zinc-800/60">
                                <div class="flex flex-col items-center justify-between h-full gap-1">
                                    <div class="font-extrabold text-zinc-800 dark:text-zinc-200 line-clamp-1 leading-tight" title="{{ $session->title }}">
                                        {{ $session->title }}
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <span class="inline-flex rounded px-1.5 py-0.2 text-[9px] font-bold {{ $session->is_competition ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' }}">
                                            {{ $session->is_competition ? 'Lomba' : 'Umum' }}
                                        </span>
                                        <flux:button
                                            size="xs"
                                            variant="subtle"
                                            icon="ellipsis-horizontal"
                                            wire:click="openSessionBulkModal('{{ $session->id }}')"
                                            title="Tindakan Massal untuk Sesi Ini"
                                        />
                                    </div>
                                </div>
                            </th>
                        @empty
                            <th class="py-3 px-4 text-center text-zinc-400">
                                Tidak ada sesi presensi yang sesuai filter.
                            </th>
                        @endforelse

                        {{-- Kolom Rekap Ringkas --}}
                        <th class="py-3 px-3 text-center w-24 sticky right-0 z-20 bg-zinc-50 dark:bg-zinc-800 border-l border-zinc-200 dark:border-zinc-800 shadow-xs">
                            Kehadiran
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 font-medium">
                    @forelse($entities as $entity)
                        @php
                            $entityId = (string) $entity->id;
                            $isSelected = in_array($entityId, $selectedEntityIds, true);
                            $attendedCount = 0;
                        @endphp
                        <tr wire:key="row-{{ $entityId }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition {{ $isSelected ? 'bg-emerald-50/40 dark:bg-emerald-950/20' : '' }}">
                            {{-- Checkbox --}}
                            <td class="py-2.5 px-3 text-center sticky left-0 z-10 bg-white dark:bg-zinc-900">
                                <input
                                    type="checkbox"
                                    value="{{ $entityId }}"
                                    wire:model.live="selectedEntityIds"
                                    class="rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500 dark:border-zinc-700 dark:bg-zinc-900"
                                >
                            </td>

                            {{-- NPP / ID Badge --}}
                            <td class="py-2.5 px-3 text-center sticky left-10 z-10 bg-white dark:bg-zinc-900">
                                @if($scope === 'patrol')
                                    <x-npp-badge :npp="$entity->npp" />
                                @elseif($scope === 'participant')
                                    <span class="font-mono text-[10px] font-bold text-zinc-600 dark:text-zinc-400">
                                        {{ $entity->id_number ?: $entity->scout_id ?: '-' }}
                                    </span>
                                @else
                                    <span class="font-mono text-[10px] font-bold text-zinc-600 dark:text-zinc-400">
                                        {{ $entity->id_number ?: '-' }}
                                    </span>
                                @endif
                            </td>

                            {{-- Nama Entitas & Pangkalan --}}
                            <td class="py-2.5 px-4 sticky left-30 z-10 bg-white dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-800 shadow-xs">
                                @if($scope === 'patrol')
                                    <div class="font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-1.5">
                                        <span>{{ $entity->patrol_name }}</span>
                                        <span class="text-[10px] font-semibold px-1.5 py-0.2 rounded {{ $entity->isBoys() ? 'bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' }}">
                                            {{ $entity->isBoys() ? 'PA' : 'PI' }}
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-zinc-500 mt-0.5 truncate max-w-[200px]" title="{{ $entity->scoutGroup?->school_name }}">
                                        {{ $entity->scoutGroup?->school_name }} ({{ $entity->group_number }})
                                    </div>
                                @elseif($scope === 'participant')
                                    <div class="font-bold text-zinc-900 dark:text-zinc-100">
                                        {{ $entity->full_name }}
                                    </div>
                                    <div class="text-[11px] text-zinc-500 mt-0.5 truncate max-w-[200px]" title="{{ $entity->patrol?->patrol_name }}">
                                        {{ $entity->patrol?->patrol_name }} • {{ $entity->patrol?->scoutGroup?->school_name }}
                                    </div>
                                @else
                                    <div class="font-bold text-zinc-900 dark:text-zinc-100">
                                        {{ $entity->full_name }}
                                    </div>
                                    <div class="text-[11px] text-zinc-500 mt-0.5 truncate max-w-[200px]" title="{{ $entity->scoutGroup?->school_name }}">
                                        {{ $entity->scoutGroup?->school_name }}
                                    </div>
                                @endif
                            </td>

                            {{-- Sel Matriks Presensi Per Sesi --}}
                            @foreach($sessions as $session)
                                @php
                                    $sessionId = (string) $session->id;
                                    $key = "{$entityId}_{$sessionId}";
                                    $record = $matrixMap[$key] ?? null;
                                    $status = $record?->status;

                                    if (in_array($status, ['present', 'late'], true)) {
                                        $attendedCount++;
                                    }
                                @endphp
                                <td wire:key="cell-{{ $key }}" class="py-2 px-1 text-center border-r border-zinc-100 dark:border-zinc-800/60">
                                    <div class="inline-flex items-center justify-center group relative">
                                        @if($status === 'present')
                                            <button
                                                type="button"
                                                wire:click="openCellModal('{{ $entityId }}', '{{ $sessionId }}')"
                                                title="Hadir • Klik untuk detail/ubah"
                                                class="flex size-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-800 border border-emerald-300 font-black text-xs transition hover:scale-110 hover:shadow-xs dark:bg-emerald-950/80 dark:text-emerald-300 dark:border-emerald-700 cursor-pointer"
                                            >
                                                H
                                            </button>
                                        @elseif($status === 'late')
                                            <button
                                                type="button"
                                                wire:click="openCellModal('{{ $entityId }}', '{{ $sessionId }}')"
                                                title="Terlambat • Klik untuk detail/ubah"
                                                class="flex size-7 items-center justify-center rounded-lg bg-amber-100 text-amber-800 border border-amber-300 font-black text-xs transition hover:scale-110 hover:shadow-xs dark:bg-amber-950/80 dark:text-amber-300 dark:border-amber-700 cursor-pointer"
                                            >
                                                T
                                            </button>
                                        @elseif($status === 'excused')
                                            <button
                                                type="button"
                                                wire:click="openCellModal('{{ $entityId }}', '{{ $sessionId }}')"
                                                title="Izin / Dispensasi • Klik untuk detail/ubah"
                                                class="flex size-7 items-center justify-center rounded-lg bg-blue-100 text-blue-800 border border-blue-300 font-black text-xs transition hover:scale-110 hover:shadow-xs dark:bg-blue-950/80 dark:text-blue-300 dark:border-blue-700 cursor-pointer"
                                            >
                                                I
                                            </button>
                                        @elseif($status === 'absent')
                                            <button
                                                type="button"
                                                wire:click="openCellModal('{{ $entityId }}', '{{ $sessionId }}')"
                                                title="Alpa / Tidak Hadir • Klik untuk detail/ubah"
                                                class="flex size-7 items-center justify-center rounded-lg bg-rose-100 text-rose-800 border border-rose-300 font-black text-xs transition hover:scale-110 hover:shadow-xs dark:bg-rose-950/80 dark:text-rose-300 dark:border-rose-700 cursor-pointer"
                                            >
                                                A
                                            </button>
                                        @else
                                            <button
                                                type="button"
                                                wire:click="quickSetStatus('{{ $entityId }}', '{{ $sessionId }}', 'present')"
                                                title="Belum Presensi • Klik untuk langsung tandai Hadir"
                                                class="flex size-7 items-center justify-center rounded-lg bg-zinc-100 text-zinc-400 border border-zinc-200 font-bold text-xs transition hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300 hover:scale-110 dark:bg-zinc-800 dark:text-zinc-500 dark:border-zinc-700 dark:hover:bg-emerald-950/40 dark:hover:text-emerald-300 cursor-pointer"
                                            >
                                                -
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            @endforeach

                            {{-- Rekap Ringkas Kehadiran --}}
                            <td class="py-2.5 px-3 text-center sticky right-0 z-10 bg-white dark:bg-zinc-900 border-l border-zinc-200 dark:border-zinc-800 shadow-xs">
                                <span class="font-extrabold text-xs text-zinc-800 dark:text-zinc-200">
                                    {{ $attendedCount }} / {{ $sessions->count() }}
                                </span>
                                <div class="text-[10px] text-zinc-500">
                                    {{ $sessions->count() > 0 ? round(($attendedCount / $sessions->count()) * 100) : 0 }}%
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $sessions->count() + 4 }}" class="py-8 text-center text-zinc-500">
                                Tidak ditemukan data sesuai kriteria pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Navigasi Paginasi --}}
        @if($entities->hasPages())
            <div class="p-3 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50">
                {{ $entities->links() }}
            </div>
        @endif
    </div>

    {{-- ================= MODAL PENYESUAIAN DETAIL SATUAN ================= --}}
    @if($showCellModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
            <div class="w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-800 dark:bg-zinc-900 space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400">
                            <flux:icon name="pencil-square" class="size-5" />
                        </span>
                        <div>
                            <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                                Penyesuaian Presensi
                            </h3>
                            <p class="mt-0.5 text-xs text-zinc-500">
                                {{ $cellEntityName }}
                            </p>
                        </div>
                    </div>
                    <flux:button
                        size="xs"
                        variant="subtle"
                        icon="x-mark"
                        wire:click="$set('showCellModal', false)"
                    />
                </div>

                <div class="rounded-xl border border-zinc-100 bg-zinc-50/70 p-3 text-xs dark:border-zinc-800 dark:bg-zinc-800/40 space-y-1">
                    <div class="text-[11px] font-bold text-zinc-500 uppercase tracking-wider">Sesi Presensi:</div>
                    <div class="font-bold text-zinc-900 dark:text-zinc-100">{{ $cellSessionTitle }}</div>
                    <div class="text-[11px] text-zinc-500">{{ $cellEntityInfo }}</div>
                </div>

                {{-- Status Pilihan --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300">
                        Status Kehadiran:
                    </label>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border border-zinc-200 dark:border-zinc-700 cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800 {{ $cellStatus === 'present' ? 'bg-emerald-50/70 border-emerald-300 dark:bg-emerald-950/30' : '' }}">
                            <input type="radio" wire:model.live="cellStatus" value="present" class="text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <div class="font-bold text-emerald-800 dark:text-emerald-300">Hadir (H)</div>
                                <div class="text-[10px] text-zinc-500">Tepat waktu</div>
                            </div>
                        </label>
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border border-zinc-200 dark:border-zinc-700 cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800 {{ $cellStatus === 'late' ? 'bg-amber-50/70 border-amber-300 dark:bg-amber-950/30' : '' }}">
                            <input type="radio" wire:model.live="cellStatus" value="late" class="text-amber-600 focus:ring-amber-500">
                            <div>
                                <div class="font-bold text-amber-800 dark:text-amber-300">Terlambat (T)</div>
                                <div class="text-[10px] text-zinc-500">Melewati batas</div>
                            </div>
                        </label>
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border border-zinc-200 dark:border-zinc-700 cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800 {{ $cellStatus === 'excused' ? 'bg-blue-50/70 border-blue-300 dark:bg-blue-950/30' : '' }}">
                            <input type="radio" wire:model.live="cellStatus" value="excused" class="text-blue-600 focus:ring-blue-500">
                            <div>
                                <div class="font-bold text-blue-800 dark:text-blue-300">Izin / Sakit (I)</div>
                                <div class="text-[10px] text-zinc-500">Dispensasi resmi</div>
                            </div>
                        </label>
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border border-zinc-200 dark:border-zinc-700 cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800 {{ $cellStatus === 'absent' ? 'bg-rose-50/70 border-rose-300 dark:bg-rose-950/30' : '' }}">
                            <input type="radio" wire:model.live="cellStatus" value="absent" class="text-rose-600 focus:ring-rose-500">
                            <div>
                                <div class="font-bold text-rose-800 dark:text-rose-300">Alpa (A)</div>
                                <div class="text-[10px] text-zinc-500">Tidak hadir</div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Waktu Presensi --}}
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                        Waktu Presensi Tercatat:
                    </label>
                    <input
                        type="datetime-local"
                        wire:model="cellScannedAt"
                        class="w-full rounded-xl border border-zinc-300 bg-white p-2.5 text-xs text-zinc-900 focus:border-emerald-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                    >
                </div>

                {{-- Catatan Penyesuaian --}}
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                        Catatan Penyesuaian:
                    </label>
                    <input
                        type="text"
                        wire:model="cellNotes"
                        placeholder="Contoh: Penyesuaian susulan kehadiran tertinggal..."
                        class="w-full rounded-xl border border-zinc-300 bg-white p-2.5 text-xs text-zinc-900 focus:border-emerald-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                    >
                </div>

                {{-- Tombol Aksi --}}
                <div class="flex items-center justify-between pt-3 border-t border-zinc-100 dark:border-zinc-800">
                    <div>
                        @if($cellHasRecord)
                            <flux:button
                                variant="danger"
                                size="sm"
                                icon="trash"
                                wire:click="deleteCellAttendance"
                                wire:confirm="Yakin ingin menghapus catatan presensi ini dan mengembalikannya ke status Belum Presensi?"
                            >
                                Hapus
                            </flux:button>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <flux:button
                            variant="subtle"
                            wire:click="$set('showCellModal', false)"
                        >
                            Batal
                        </flux:button>
                        <flux:button
                            variant="primary"
                            icon="check"
                            wire:click="saveCellAdjustment"
                        >
                            Simpan
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ================= MODAL PENYESUAIAN MASSAL (BATCH ROWS) ================= --}}
    @if($showBatchModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
            <div class="w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-800 dark:bg-zinc-900 space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400">
                            <flux:icon name="pencil-square" class="size-5" />
                        </span>
                        <div>
                            <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                                Penyesuaian Massal
                            </h3>
                            <p class="mt-0.5 text-xs text-zinc-500">
                                Menyesuaikan {{ count($selectedEntityIds) }} entitas terpilih sekaligus.
                            </p>
                        </div>
                    </div>
                    <flux:button
                        size="xs"
                        variant="subtle"
                        icon="x-mark"
                        wire:click="$set('showBatchModal', false)"
                    />
                </div>

                {{-- Pilih Sesi Target --}}
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                        Pilih Sesi Presensi:
                    </label>
                    <select
                        wire:model="batchTargetSessionId"
                        class="w-full rounded-xl border border-zinc-300 bg-white p-2.5 text-xs text-zinc-900 focus:border-emerald-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                    >
                        @foreach($sessions as $s)
                            <option value="{{ $s->id }}">[{{ $s->is_competition ? 'Lomba' : 'Umum' }}] {{ $s->title }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status Kehadiran --}}
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                        Tetapkan Status:
                    </label>
                    <select
                        wire:model="batchStatus"
                        class="w-full rounded-xl border border-zinc-300 bg-white p-2.5 text-xs text-zinc-900 focus:border-emerald-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                    >
                        <option value="present">Hadir (Present)</option>
                        <option value="late">Terlambat (Late)</option>
                        <option value="excused">Izin / Sakit (Excused)</option>
                        <option value="absent">Alpa (Absent)</option>
                    </select>
                </div>

                {{-- Catatan --}}
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                        Catatan:
                    </label>
                    <input
                        type="text"
                        wire:model="batchNotes"
                        class="w-full rounded-xl border border-zinc-300 bg-white p-2.5 text-xs text-zinc-900 focus:border-emerald-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                    >
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-zinc-100 dark:border-zinc-800">
                    <flux:button
                        variant="subtle"
                        wire:click="$set('showBatchModal', false)"
                    >
                        Batal
                    </flux:button>
                    <flux:button
                        variant="primary"
                        icon="check"
                        wire:click="executeBatchAdjustment"
                    >
                        Terapkan Penyesuaian
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    {{-- ================= MODAL PENYESUAIAN SELURUH SESI (KOLOM) ================= --}}
    @if($showSessionBulkModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
            <div class="w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-800 dark:bg-zinc-900 space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400">
                            <flux:icon name="arrows-pointing-out" class="size-5" />
                        </span>
                        <div>
                            <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                                Tindakan Seluruh Sesi
                            </h3>
                            <p class="mt-0.5 text-xs text-zinc-500">
                                Menyesuaikan kehadiran untuk seluruh entitas pada sesi ini.
                            </p>
                        </div>
                    </div>
                    <flux:button
                        size="xs"
                        variant="subtle"
                        icon="x-mark"
                        wire:click="$set('showSessionBulkModal', false)"
                    />
                </div>

                {{-- Pilihan Status --}}
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                        Status yang Ditetapkan:
                    </label>
                    <select
                        wire:model="sessionBulkStatus"
                        class="w-full rounded-xl border border-zinc-300 bg-white p-2.5 text-xs text-zinc-900 focus:border-emerald-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                    >
                        <option value="present">Tandai Hadir (Present)</option>
                        <option value="late">Tandai Terlambat (Late)</option>
                        <option value="excused">Tandai Izin (Excused)</option>
                        <option value="absent">Tandai Alpa (Absent)</option>
                    </select>
                </div>

                {{-- Target Entitas: Hanya yang belum presensi vs Semua --}}
                <div class="space-y-1">
                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-zinc-200 dark:border-zinc-700 cursor-pointer">
                        <input type="checkbox" wire:model="sessionBulkOnlyUnrecorded" class="rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500 dark:border-zinc-700 dark:bg-zinc-800">
                        <span class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">
                            Hanya terapkan pada entitas yang belum memiliki catatan presensi
                        </span>
                    </label>
                </div>

                {{-- Catatan --}}
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                        Catatan:
                    </label>
                    <input
                        type="text"
                        wire:model="sessionBulkNotes"
                        class="w-full rounded-xl border border-zinc-300 bg-white p-2.5 text-xs text-zinc-900 focus:border-emerald-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                    >
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-zinc-100 dark:border-zinc-800">
                    <flux:button
                        variant="subtle"
                        wire:click="$set('showSessionBulkModal', false)"
                    >
                        Batal
                    </flux:button>
                    <flux:button
                        variant="primary"
                        icon="check"
                        wire:click="executeSessionBulkAction"
                        wire:confirm="Yakin ingin menerapkan penyesuaian untuk seluruh entitas pada sesi ini?"
                    >
                        Terapkan ke Sesi Ini
                    </flux:button>
                </div>
            </div>
        </div>
    @endif
</div>

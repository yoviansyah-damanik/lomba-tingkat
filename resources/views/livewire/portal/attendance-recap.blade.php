<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Official Header --}}
    <x-official-header />

    {{-- Breadcrumb & Title Section --}}
    @php
        $printParams = array_filter([
            'session_id' => $sessionId !== 'all' ? $sessionId : null,
            'status' => $statusFilter !== 'all' ? $statusFilter : null,
            'entity_type' => $entityType !== 'all' ? $entityType : null,
            'date' => !empty($dateFilter) ? $dateFilter : null,
            'search' => !empty($search) ? $search : null,
        ]);
    @endphp

    <x-heading-section
        title="Rekapitulasi Presensi & Kehadiran Pos"
        description="Pantau, kelola, edit status kehadiran, dan cetak berita acara presensi resmi per pos kegiatan dan lomba."
        badge="Manajemen & Rekapitulasi Presensi"
        badge-icon="chart-bar"
        badge-color="amber"
    >
        <x-slot:badge-extra>
            <span class="text-xs text-zinc-400">LT-III Tahun 2026</span>
        </x-slot:badge-extra>

        <x-slot:actions>
            <flux:button href="{{ route('attendance.index') }}" variant="subtle" icon="qr-code">
                Buka Scanner Kamera
            </flux:button>

            <flux:button href="{{ route('attendance.recap.print', $printParams) }}" target="_blank" variant="primary" icon="printer">
                Cetak Lembar Presensi
            </flux:button>
        </x-slot:actions>
    </x-heading-section>

    {{-- Stats Cards KPI --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
        <x-stat-card
            label="Total Presensi"
            value="{{ number_format($stats['total']) }}"
            unit="rekaman"
            icon="user-group"
            color="neutral"
        />

        <x-stat-card
            label="Hadir Tepat Waktu"
            value="{{ number_format($stats['present']) }}"
            unit="orang"
            icon="check-circle"
            color="emerald"
        />

        <x-stat-card
            label="Terlambat"
            value="{{ number_format($stats['late']) }}"
            unit="orang"
            icon="clock"
            color="amber"
        />

        <x-stat-card
            label="Izin / Dispensasi"
            value="{{ number_format($stats['excused']) }}"
            unit="orang"
            icon="document-text"
            color="blue"
        />

        <x-stat-card
            class="col-span-2 sm:col-span-1"
            label="Alpa / Tidak Hadir"
            value="{{ number_format($stats['absent']) }}"
            unit="orang"
            icon="x-circle"
            color="rose"
        />
    </div>

    {{-- Filter Bar --}}
    <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-2xs dark:border-zinc-800 dark:bg-zinc-900 space-y-3">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-6">
            {{-- Pencarian --}}
            <div class="lg:col-span-2">
                <flux:input 
                    wire:model.live.debounce.350ms="search" 
                    placeholder="Cari nama, ID-XXXX, NTA, regu, atau sekolah..." 
                    icon="magnifying-glass"
                    size="sm"
                    clearable
                />
            </div>

            {{-- Filter Sesi Presensi --}}
            <div>
                <select wire:model.live="sessionId" class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs font-semibold text-zinc-800 focus:border-amber-600 focus:bg-white focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                    <option value="all">Semua Sesi Presensi</option>
                    @foreach($sessions as $sess)
                        <option value="{{ $sess->id }}">
                            {{ $sess->title }} ({{ $sess->isCompetition() ? 'Lomba' : 'Umum' }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Status --}}
            <div>
                <select wire:model.live="statusFilter" class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs font-semibold text-zinc-800 focus:border-amber-600 focus:bg-white focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                    <option value="all">Semua Status</option>
                    <option value="present">Hadir (Present)</option>
                    <option value="late">Terlambat (Late)</option>
                    <option value="excused">Izin / Dispensasi (Excused)</option>
                    <option value="absent">Alpa / Tidak Hadir (Absent)</option>
                </select>
            </div>

            {{-- Filter Tipe Entitas --}}
            <div>
                <select wire:model.live="entityType" class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs font-semibold text-zinc-800 focus:border-amber-600 focus:bg-white focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                    <option value="all">Semua Entitas</option>
                    <option value="participant">Peserta Regu</option>
                    <option value="chaperone">Pembina Pendamping</option>
                    <option value="user">Aparatur (Panitia / Juri)</option>
                </select>
            </div>

            {{-- Filter Tanggal --}}
            <div>
                <input 
                    type="date" 
                    wire:model.live="dateFilter" 
                    class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-1.5 text-xs font-semibold text-zinc-800 focus:border-amber-600 focus:bg-white focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                />
            </div>
        </div>

        {{-- Active Filters Indicator & Reset --}}
        @if(!empty($search) || $sessionId !== 'all' || $statusFilter !== 'all' || $entityType !== 'all' || !empty($dateFilter))
            <div class="flex items-center justify-between pt-2 border-t border-zinc-100 dark:border-zinc-800 text-xs">
                <div class="flex items-center gap-1.5 text-zinc-500 dark:text-zinc-400">
                    <flux:icon name="funnel" class="size-3.5 text-amber-600" />
                    <span>Filter aktif diterapkan</span>
                </div>
                <button 
                    type="button" 
                    wire:click="resetFilters" 
                    class="font-semibold text-amber-600 hover:text-amber-700 dark:text-amber-400 hover:underline"
                >
                    Reset Semua Filter
                </button>
            </div>
        @endif
    </div>

    {{-- Data Table --}}
    <div class="rounded-2xl border border-zinc-200 bg-white shadow-2xs dark:border-zinc-800 dark:bg-zinc-900 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="border-b border-zinc-200 bg-zinc-50/70 text-zinc-500 dark:border-zinc-800 dark:bg-zinc-800/50 dark:text-zinc-400">
                    <tr>
                        <th class="px-4 py-3 font-semibold w-10">No</th>
                        <th class="px-4 py-3 font-semibold">Identitas & Entitas</th>
                        <th class="px-4 py-3 font-semibold">Peran & Kontingen</th>
                        <th class="px-4 py-3 font-semibold">Sesi Kegiatan & Pos</th>
                        <th class="px-4 py-3 font-semibold">Status Presensi</th>
                        <th class="px-4 py-3 font-semibold">Waktu Presensi</th>
                        <th class="px-4 py-3 font-semibold">Catatan / Pos</th>
                        <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @forelse($attendances as $index => $att)
                        <tr class="hover:bg-zinc-50/60 dark:hover:bg-zinc-800/40 transition-colors">
                            {{-- Number --}}
                            <td class="px-4 py-3 font-mono text-zinc-400">
                                {{ $attendances->firstItem() + $index }}
                            </td>

                            {{-- Entity & Name --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="size-9 rounded-xl overflow-hidden bg-zinc-100 dark:bg-zinc-800 shrink-0 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center font-bold text-zinc-400 text-[9px]">
                                        @if($att->entity_photo_url)
                                            <img src="{{ $att->entity_photo_url }}" alt="{{ $att->entity_name }}" class="w-full h-full object-cover">
                                        @else
                                            <span>3x4</span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-zinc-900 dark:text-zinc-100 truncate">
                                            {{ $att->entity_name }}
                                        </div>
                                        <div class="flex items-center gap-1.5 text-[11px] text-zinc-500 dark:text-zinc-400 font-mono">
                                            <span class="rounded bg-zinc-100 px-1 py-0.2 dark:bg-zinc-800">{{ $att->entity_id_number }}</span>
                                            @if($att->entity_type === 'participant' && $att->participant?->scout_id)
                                                <span>• NTA: {{ $att->participant->scout_id }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Role & Contingent --}}
                            <td class="px-4 py-3">
                                <div class="font-semibold text-zinc-800 dark:text-zinc-200">
                                    {{ $att->entity_role }}
                                </div>
                                <div class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate max-w-xs">
                                    {{ $att->entity_group }}
                                </div>
                            </td>

                            {{-- Session & Pos --}}
                            <td class="px-4 py-3">
                                <div class="font-semibold text-zinc-900 dark:text-zinc-100 truncate max-w-xs">
                                    {{ $att->session?->title ?: 'Sesi Kegiatan' }}
                                </div>
                                <div class="flex items-center gap-1 text-[11px] text-zinc-500 dark:text-zinc-400">
                                    @if($att->session?->isCompetition())
                                        <span class="inline-flex items-center rounded px-1.5 py-0.2 font-medium bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/40 text-[10px]">
                                            Pos Lomba
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded px-1.5 py-0.2 font-medium bg-zinc-100 text-zinc-600 border border-zinc-200 dark:bg-zinc-800 dark:text-zinc-400 dark:border-zinc-700 text-[10px]">
                                            Kehadiran Umum
                                        </span>
                                    @endif
                                    @if($att->session?->schedule?->arena_location)
                                        <span>• {{ $att->session->schedule->arena_location }}</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Status Badge --}}
                            <td class="px-4 py-3">
                                @if($att->status === 'present')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-xs font-bold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-800">
                                        <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                        Hadir
                                    </span>
                                @elseif($att->status === 'late')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-500/10 px-2.5 py-0.5 text-xs font-bold text-amber-700 dark:bg-amber-500/20 dark:text-amber-400 border border-amber-300 dark:border-amber-800">
                                        <span class="size-1.5 rounded-full bg-amber-500"></span>
                                        Terlambat
                                    </span>
                                @elseif($att->status === 'excused')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-500/10 px-2.5 py-0.5 text-xs font-bold text-blue-700 dark:bg-blue-500/20 dark:text-blue-400 border border-blue-300 dark:border-blue-800">
                                        <span class="size-1.5 rounded-full bg-blue-500"></span>
                                        Izin
                                    </span>
                                @elseif($att->status === 'absent')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-500/10 px-2.5 py-0.5 text-xs font-bold text-rose-700 dark:bg-rose-500/20 dark:text-rose-400 border border-rose-300 dark:border-rose-800">
                                        <span class="size-1.5 rounded-full bg-rose-500"></span>
                                        Alpa
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                        {{ $att->status ?: 'Tercatat' }}
                                    </span>
                                @endif
                            </td>

                            {{-- Scanned Time --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="font-mono font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $att->scanned_at?->format('H:i:s') }} WIB
                                </div>
                                <div class="text-[10px] text-zinc-400">
                                    {{ $att->scanned_at?->translatedFormat('d M Y') }}
                                </div>
                            </td>

                            {{-- Notes / Scanned By --}}
                            <td class="px-4 py-3">
                                <div class="text-zinc-700 dark:text-zinc-300 truncate max-w-xs font-medium">
                                    {{ $att->notes ?: '-' }}
                                </div>
                                <div class="text-[10px] text-zinc-400">
                                    Pencatat: {{ $att->scannedBy?->name ?: 'Scanner Pos' }}
                                </div>
                            </td>

                            {{-- Actions (Edit & Delete) --}}
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    {{-- Edit Button --}}
                                    <flux:button 
                                        size="xs"
                                        variant="outline"
                                        icon="pencil-square"
                                        wire:click="openEditModal('{{ $att->id }}')" 
                                        title="Ubah Data Presensi"
                                    >
                                        Ubah
                                    </flux:button>

                                    {{-- Delete Button with confirm --}}
                                    <button 
                                        type="button" 
                                        wire:click="deleteAttendance('{{ $att->id }}')" 
                                        wire:confirm="Yakin ingin membatalkan/menghapus catatan presensi {{ $att->entity_name }}?" 
                                        class="inline-flex items-center justify-center rounded-lg border border-rose-200 bg-white p-1 text-xs font-semibold text-rose-600 hover:bg-rose-50 dark:border-rose-900/60 dark:bg-zinc-800 dark:text-rose-400 dark:hover:bg-rose-950/30"
                                        title="Hapus / Batalkan Presensi"
                                    >
                                        <flux:icon name="trash" class="size-3.5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-zinc-400">
                                <flux:icon name="clipboard-document-list" class="mx-auto size-12 text-zinc-300 dark:text-zinc-700 mb-2" />
                                <div class="font-bold text-sm text-zinc-600 dark:text-zinc-400">Tidak ada catatan presensi ditemukan</div>
                                <div class="text-xs text-zinc-400 mt-0.5">Ubah kata kunci pencarian atau sesuaikan filter status dan tanggal.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($attendances->hasPages())
            <div class="border-t border-zinc-200 px-6 py-4 dark:border-zinc-800">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Edit Data Presensi --}}
    <flux:modal wire:model="showEditModal" class="max-w-lg">
        <div class="space-y-5">
            <div>
                <div class="flex items-center gap-2">
                    <span class="rounded bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                        Edit Presensi
                    </span>
                    <span class="text-xs text-zinc-500">ID: {{ $editingAttendanceId }}</span>
                </div>
                <flux:heading size="lg" class="mt-1">
                    {{ $editEntityName }}
                </flux:heading>
                <flux:subheading class="text-xs">
                    {{ $editEntityRole }} • Sesi: {{ $editSessionTitle }}
                </flux:subheading>
            </div>

            <form wire:submit.prevent="saveEdit" class="space-y-4 text-xs">
                {{-- Status Kehadiran --}}
                <div class="space-y-1">
                    <label class="font-bold text-zinc-700 dark:text-zinc-300">Status Kehadiran *</label>
                    <select wire:model="editStatus" class="w-full rounded-xl border border-zinc-200 bg-white p-2.5 text-xs font-semibold text-zinc-900 focus:border-amber-600 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                        <option value="present">Hadir Tepat Waktu (Present)</option>
                        <option value="late">Terlambat (Late)</option>
                        <option value="excused">Izin / Dispensasi (Excused)</option>
                        <option value="absent">Alpa / Tidak Hadir (Absent)</option>
                    </select>
                    @error('editStatus') <span class="text-[11px] text-rose-600">{{ $message }}</span> @enderror
                </div>

                {{-- Waktu Presensi --}}
                <div class="space-y-1">
                    <label class="font-bold text-zinc-700 dark:text-zinc-300">Waktu Presensi Tercatat</label>
                    <input 
                        type="datetime-local" 
                        wire:model="editScannedAt" 
                        class="w-full rounded-xl border border-zinc-200 bg-white p-2.5 text-xs font-semibold text-zinc-900 focus:border-amber-600 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                    />
                    @error('editScannedAt') <span class="text-[11px] text-rose-600">{{ $message }}</span> @enderror
                </div>

                {{-- Catatan Presensi --}}
                <div class="space-y-1">
                    <label class="font-bold text-zinc-700 dark:text-zinc-300">Catatan Khusus Petugas Pos</label>
                    <textarea 
                        wire:model="editNotes" 
                        rows="3" 
                        placeholder="Contoh: Datang terlambat 10 menit karena izin ke pos kesehatan..."
                        class="w-full rounded-xl border border-zinc-200 bg-white p-2.5 text-xs text-zinc-900 focus:border-amber-600 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                    ></textarea>
                    @error('editNotes') <span class="text-[11px] text-rose-600">{{ $message }}</span> @enderror
                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                    <flux:button type="button" variant="subtle" size="sm" wire:click="$set('showEditModal', false)">
                        Batal
                    </flux:button>
                    <flux:button type="submit" variant="primary" size="sm" class="bg-amber-600 hover:bg-amber-700 text-white font-bold">
                        Simpan Perubahan
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>

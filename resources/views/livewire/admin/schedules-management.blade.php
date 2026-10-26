<div class="space-y-6">
    {{-- Header Section --}}
    <x-heading-section
        title="Jadwal Lomba & Penugasan Juri"
        description="Atur jadwal pelaksanaan sesi pos arena, alokasi dewan juri, serta aktivasi sesi presensi QR."
        badge="Manajemen Jadwal & Juri"
        badge-icon="calendar"
        badge-color="amber"
    >
        <x-slot:actions>
            <flux:button icon="sparkles" wire:click="$set('showAutoAssignModal', true)">
                Tentukan Juri Otomatis
            </flux:button>
            <flux:button variant="primary" icon="plus" wire:click="openCreateModal">
                Tambah Jadwal Sesi
            </flux:button>
        </x-slot:actions>
    </x-heading-section>

    {{-- Stats Cards Overview --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <x-stat-card
            label="Total Sesi Pelaksanaan"
            value="{{ $stats['total'] }}"
            description="Terdaftar di sistem"
            color="neutral"
        />

        <x-stat-card
            label="Dijadwalkan"
            value="{{ $stats['scheduled'] }}"
            description="Menunggu jam mulai"
            color="blue"
        />

        <x-stat-card
            label="Sedang Berlangsung"
            value="{{ $stats['in_progress'] }}"
            description="Pos arena aktif"
            color="emerald"
        />

        <x-stat-card
            label="Selesai"
            value="{{ $stats['completed'] }}"
            description="Sesi rampung dinilai"
            color="neutral"
        />
    </div>

    {{-- Filters Bar --}}
    <div class="flex flex-col gap-3 rounded-2xl border border-zinc-200/80 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center">
            <div class="w-full sm:max-w-xs">
                <flux:input 
                    wire:model.live.debounce.300ms="search" 
                    icon="magnifying-glass" 
                    placeholder="Cari sesi, arena, lomba..." 
                    clearable 
                />
            </div>

            <div class="w-full sm:w-56">
                <flux:select wire:model.live="competitionFilter" placeholder="Filter Cabang Lomba">
                    <flux:select.option value="all">Semua Cabang Lomba</flux:select.option>
                    @foreach($competitions as $c)
                        <flux:select.option value="{{ $c->id }}">{{ $c->title }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="w-full sm:w-44">
                <flux:select wire:model.live="genderFilter" placeholder="Kategori Regu">
                    <flux:select.option value="all">Semua Kategori</flux:select.option>
                    <flux:select.option value="boys">Khusus Putra</flux:select.option>
                    <flux:select.option value="girls">Khusus Putri</flux:select.option>
                </flux:select>
            </div>

            <div class="w-full sm:w-44">
                <flux:select wire:model.live="statusFilter" placeholder="Status Sesi">
                    <flux:select.option value="all_status">Semua Status</flux:select.option>
                    <flux:select.option value="scheduled">Dijadwalkan</flux:select.option>
                    <flux:select.option value="in_progress">Berlangsung</flux:select.option>
                    <flux:select.option value="completed">Selesai</flux:select.option>
                    <flux:select.option value="rescheduled">Ditunda</flux:select.option>
                    <flux:select.option value="cancelled">Dibatalkan</flux:select.option>
                </flux:select>
            </div>
        </div>

        <div class="flex items-center text-xs text-zinc-500">
            <span>Ditemukan: <strong class="text-zinc-800 dark:text-zinc-200">{{ $schedules->total() }}</strong> jadwal</span>
        </div>
    </div>

    {{-- Schedules Table --}}
    <div class="overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-zinc-200 bg-zinc-50/75 text-xs font-semibold text-zinc-600 dark:border-zinc-800 dark:bg-zinc-800/50 dark:text-zinc-400">
                    <tr>
                        <th class="py-3.5 pl-6 pr-3">Cabang Lomba & Sesi</th>
                        <th class="px-3 py-3.5">Arena Lokasi & Waktu</th>
                        <th class="px-3 py-3.5">Format Lomba</th>
                        <th class="px-3 py-3.5">Dewan Juri Bertugas</th>
                        <th class="px-3 py-3.5">Sesi Presensi Pos</th>
                        <th class="py-3.5 pl-3 pr-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 text-zinc-700 dark:text-zinc-300">
                    @forelse($schedules as $schedule)
                        @php
                            $attendanceSession = $schedule->attendanceSessions->first();
                            $isSessionActive = $attendanceSession && $attendanceSession->is_active;
                        @endphp
                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/50 transition-colors">
                            {{-- Cabang Lomba & Sesi --}}
                            <td class="py-4 pl-6 pr-3 align-top">
                                <div class="font-bold text-zinc-900 dark:text-white">
                                    {{ $schedule->session_name }}
                                </div>
                                <div class="mt-0.5 flex items-center gap-1.5 flex-wrap">
                                    <span class="rounded bg-zinc-100 px-1.5 py-0.5 text-xs font-mono font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                        {{ $schedule->competition?->code ?? '-' }}
                                    </span>
                                    <span class="text-xs text-zinc-600 dark:text-zinc-400">
                                        {{ $schedule->competition?->title ?? 'Lomba Belum Ditentukan' }}
                                    </span>
                                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $schedule->genderBadgeClasses() }}">
                                        @if($schedule->effectiveGenderCategory() === 'boys')
                                            <flux:icon name="user" class="size-2.5 text-blue-600 dark:text-blue-400" />
                                        @elseif($schedule->effectiveGenderCategory() === 'girls')
                                            <flux:icon name="user" class="size-2.5 text-pink-600 dark:text-pink-400" />
                                        @else
                                            <flux:icon name="users" class="size-2.5 text-zinc-500" />
                                        @endif
                                        {{ $schedule->genderLabel() }}
                                    </span>
                                </div>
                                <div class="mt-1">
                                    @if($schedule->status === 'in_progress')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2 py-0.5 text-[11px] font-semibold text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400">
                                            <span class="size-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Sedang Berlangsung
                                        </span>
                                    @elseif($schedule->status === 'completed')
                                        <span class="inline-flex items-center rounded-full bg-zinc-100 px-2 py-0.5 text-[11px] font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                            Selesai
                                        </span>
                                    @elseif($schedule->status === 'cancelled')
                                        <span class="inline-flex items-center rounded-full bg-rose-500/10 px-2 py-0.5 text-[11px] font-medium text-rose-600 dark:bg-rose-500/20 dark:text-rose-400">
                                            Dibatalkan
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-blue-500/10 px-2 py-0.5 text-[11px] font-semibold text-blue-600 dark:bg-blue-500/20 dark:text-blue-400">
                                            Dijadwalkan
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Arena Lokasi & Waktu --}}
                            <td class="px-3 py-4 align-top">
                                <div class="flex items-center gap-1.5 font-medium text-zinc-900 dark:text-white">
                                    <flux:icon name="map-pin" class="size-4 text-zinc-400" />
                                    {{ $schedule->arena_location }}
                                </div>
                                <div class="mt-1.5 text-xs text-zinc-500 dark:text-zinc-400">
                                    <div>Mulai: <strong class="text-zinc-700 dark:text-zinc-300">{{ \Carbon\Carbon::parse($schedule->start_time)->isoFormat('D MMM Y, HH:mm') }}</strong></div>
                                    <div>Selesai: <strong class="text-zinc-700 dark:text-zinc-300">{{ \Carbon\Carbon::parse($schedule->end_time)->isoFormat('HH:mm') }}</strong> WIB</div>
                                </div>
                            </td>

                            {{-- Format Lomba --}}
                            <td class="px-3 py-4 align-top">
                                @php
                                    $fmt = $schedule->participant_type ?? $schedule->competition?->participant_type ?? 'patrol';
                                @endphp
                                @if($fmt === 'individual')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-purple-500/10 px-2.5 py-0.5 text-xs font-semibold text-purple-700 dark:bg-purple-500/20 dark:text-purple-300">
                                        <flux:icon name="user" class="size-3" />
                                        Individu
                                    </span>
                                    <div class="mt-1 text-[11px] text-zinc-500">Presensi perwakilan regu</div>
                                @elseif($fmt === 'group')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-cyan-500/10 px-2.5 py-0.5 text-xs font-semibold text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300">
                                        <flux:icon name="users" class="size-3" />
                                        Kelompok
                                    </span>
                                    <div class="mt-1 text-[11px] text-zinc-500">Beberapa anggota dalam regu</div>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-500/10 px-2.5 py-0.5 text-xs font-semibold text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">
                                        <flux:icon name="user-group" class="size-3" />
                                        Regu Utuh
                                    </span>
                                    <div class="mt-1 text-[11px] text-zinc-500">Wajib PINRU/WAPINRU</div>
                                @endif
                            </td>

                            {{-- Dewan Juri Bertugas --}}
                            <td class="px-3 py-4 align-top">
                                <div class="space-y-1">
                                    @forelse($schedule->judgeAssignments as $assignment)
                                        <div class="flex items-center justify-between gap-1.5 rounded-lg border border-zinc-200/70 bg-zinc-50/70 px-2 py-1 text-xs dark:border-zinc-800 dark:bg-zinc-800/70">
                                            <div class="flex items-center gap-1 truncate">
                                                <span class="font-medium text-zinc-800 dark:text-zinc-200 truncate max-w-[120px]" title="{{ $assignment->judge?->name }}">
                                                    {{ $assignment->judge?->name }}
                                                </span>
                                                <span class="rounded bg-amber-500/15 px-1 text-[10px] font-semibold text-amber-700 dark:text-amber-300">
                                                    {{ $assignment->role === 'lead_judge' ? 'Ketua' : ($assignment->role === 'scoring_judge' ? 'Penilai' : 'Pendamping') }}
                                                </span>
                                            </div>
                                        </div>
                                    @empty
                                        <span class="text-xs italic text-zinc-400">Belum ada juri ditugaskan</span>
                                    @endforelse
                                </div>
                                <div class="mt-2">
                                    <button 
                                        type="button" 
                                        wire:click="openJudgesModal('{{ $schedule->id }}')" 
                                        class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400"
                                    >
                                        <flux:icon name="user-plus" class="size-3.5" />
                                        Atur Juri ({{ $schedule->judgeAssignments->count() }})
                                    </button>
                                </div>
                            </td>

                            {{-- Sesi Presensi Pos --}}
                            <td class="px-3 py-4 align-top">
                                @php
                                    $isWithinWindow = $schedule->isAttendanceOpen();
                                    $isNotOpenYet = $schedule->isAttendanceNotOpenYet();
                                    $isClosed = $schedule->isAttendanceClosed();
                                    $isManuallyActive = $attendanceSession ? $attendanceSession->is_active : true;
                                    $isReadyToScan = $isWithinWindow && $isManuallyActive;
                                @endphp

                                {{-- Badge Status Presensi Otomatis Berbasis Jadwal --}}
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    @if($isReadyToScan)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-xs font-semibold text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400 border border-emerald-300/50 dark:border-emerald-700/50">
                                            <span class="size-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Aktif Scan (Otomatis Terbuka)
                                        </span>
                                    @elseif($isWithinWindow && ! $isManuallyActive)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-500/10 px-2.5 py-0.5 text-xs font-semibold text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 border border-rose-300/50 dark:border-rose-700/50">
                                            <flux:icon name="lock-closed" class="size-3 text-rose-600 dark:text-rose-400" />
                                            Ditutup Manual Saat Lomba
                                        </span>
                                    @elseif($isNotOpenYet)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/10 px-2.5 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 border border-amber-300/50 dark:border-amber-700/50">
                                            <flux:icon name="clock" class="size-3 text-amber-600 dark:text-amber-400" />
                                            Otomatis Buka H-30m
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                                            <flux:icon name="check-circle" class="size-3 text-zinc-400" />
                                            Presensi Ditutup (Selesai)
                                        </span>
                                    @endif
                                </div>

                                {{-- Rincian Jadwal Waktu Presensi --}}
                                <div class="mt-1 text-[11px] text-zinc-500 dark:text-zinc-400">
                                    @if($isNotOpenYet)
                                        Buka otomatis: <strong class="text-zinc-700 dark:text-zinc-300">{{ $schedule->attendance_open_time?->isoFormat('D MMM, HH:mm') }}</strong> WIB
                                    @elseif($isWithinWindow)
                                        Tutup saat lomba berakhir (<strong class="text-zinc-700 dark:text-zinc-300">{{ $schedule->end_time?->isoFormat('HH:mm') }}</strong> WIB)
                                    @else
                                        Selesai pada {{ $schedule->end_time?->isoFormat('D MMM, HH:mm') }} WIB
                                    @endif
                                </div>

                                {{-- Tombol Aksi Kontrol & Akses Scanner --}}
                                <div class="mt-2.5 flex items-center gap-2 flex-wrap">
                                    @if($isWithinWindow)
                                        {{-- Saat waktu lomba: admin dapat menutup atau membuka kembali sesi --}}
                                        <button 
                                            type="button"
                                            wire:click="toggleAttendanceSession('{{ $schedule->id }}')" 
                                            class="inline-flex items-center gap-1 rounded-lg border px-2.5 py-1 text-xs font-semibold transition-all cursor-pointer {{ $isManuallyActive ? 'border-rose-300 bg-rose-50 text-rose-700 hover:bg-rose-100 dark:border-rose-800 dark:bg-rose-950/40 dark:text-rose-300' : 'border-emerald-300 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300' }}"
                                            title="{{ $isManuallyActive ? 'Tutup sesi presensi pos ini saat waktu lomba' : 'Buka kembali sesi presensi pos ini' }}"
                                        >
                                            <flux:icon name="{{ $isManuallyActive ? 'stop' : 'play' }}" class="size-3" />
                                            {{ $isManuallyActive ? 'Tutup Sesi' : 'Buka Kembali Sesi' }}
                                        </button>
                                    @elseif($isNotOpenYet)
                                        {{-- Saat belum waktu lomba: admin dapat mengunci/membatalkan auto-buka --}}
                                        <button 
                                            type="button"
                                            wire:click="toggleAttendanceSession('{{ $schedule->id }}')" 
                                            class="inline-flex items-center gap-1 rounded-lg border px-2.5 py-1 text-xs font-semibold transition-all cursor-pointer {{ $isManuallyActive ? 'border-zinc-200 bg-zinc-50 text-zinc-600 hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' : 'border-emerald-300 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300' }}"
                                            title="{{ $isManuallyActive ? 'Kunci sesi agar tidak otomatis terbuka' : 'Aktifkan auto-buka jadwal' }}"
                                        >
                                            <flux:icon name="{{ $isManuallyActive ? 'lock-closed' : 'lock-open' }}" class="size-3" />
                                            {{ $isManuallyActive ? 'Kunci Sesi' : 'Aktifkan Auto' }}
                                        </button>
                                    @else
                                        {{-- Saat sudah selesai: admin dapat membuka sesi susulan jika perlu --}}
                                        <button 
                                            type="button"
                                            wire:click="toggleAttendanceSession('{{ $schedule->id }}')" 
                                            class="inline-flex items-center gap-1 rounded-lg border border-zinc-200 bg-zinc-50 px-2.5 py-1 text-xs font-semibold text-zinc-600 hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 transition-all cursor-pointer"
                                            title="Buka sesi presensi susulan"
                                        >
                                            <flux:icon name="arrow-path" class="size-3" />
                                            Buka Susulan
                                        </button>
                                    @endif

                                    @if($attendanceSession)
                                        <a 
                                            href="{{ route('attendance.scanner', $attendanceSession->id) }}" 
                                            class="inline-flex items-center gap-1 rounded-lg border border-zinc-200 bg-white px-2.5 py-1 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 shadow-xs"
                                            title="Buka Kamera Scanner QR Pos" wire:navigate>
                                            <flux:icon name="qr-code" class="size-3.5 text-amber-600 dark:text-amber-400" />
                                            Scanner
                                        </a>
                                    @endif
                                </div>
                            </td>

                            {{-- Actions --}}
                            <td class="py-4 pl-3 pr-6 align-top text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($schedule->is_published && ! in_array($schedule->status, ['cancelled', 'completed'], true))
                                        <flux:button
                                            variant="subtle"
                                            size="sm"
                                            icon="bell-alert"
                                            class="text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-950/40"
                                            wire:click="sendReminder('{{ $schedule->id }}')"
                                            wire:confirm="Kirim notifikasi pengingat jadwal ini ke seluruh perangkat terdaftar?"
                                            wire:loading.attr="disabled"
                                            wire:target="sendReminder('{{ $schedule->id }}')"
                                        >
                                            Kirim Notifikasi
                                        </flux:button>
                                    @endif

                                    <flux:button 
                                        variant="subtle" 
                                        size="sm" 
                                        icon="pencil-square" 
                                        wire:click="openEditModal('{{ $schedule->id }}')"
                                        title="Edit Jadwal"
                                    />

                                    <flux:button 
                                        variant="subtle" 
                                        size="sm" 
                                        icon="trash" 
                                        class="text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/40"
                                        wire:click="deleteSchedule('{{ $schedule->id }}')" 
                                        wire:confirm="Yakin ingin menghapus jadwal sesi ini?"
                                        title="Hapus Jadwal"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-zinc-500">
                                <flux:icon name="calendar" class="mx-auto size-10 text-zinc-400 opacity-60" />
                                <div class="mt-2 font-medium">Belum ada jadwal sesi lomba</div>
                                <div class="text-xs">Klik tombol "Tambah Jadwal Sesi" untuk membuat jadwal pelaksanaan pertama.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($schedules->hasPages())
            <div class="border-t border-zinc-200 px-6 py-4 dark:border-zinc-800">
                {{ $schedules->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Form Jadwal (Create / Edit) --}}
    <flux:modal wire:model="showModal" class="max-w-2xl">
        <form wire:submit="saveSchedule" class="space-y-5">
            <div>
                <flux:heading size="lg">
                    {{ $editingId ? 'Edit Jadwal Sesi Lomba' : 'Tambah Jadwal Sesi Lomba Baru' }}
                </flux:heading>
                <flux:subheading>
                    Tentukan waktu pelaksanaan, lokasi pos arena, dan konfigurasi sesi presensi QR.
                </flux:subheading>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <flux:select wire:model.live="competition_id" label="Cabang Mata Lomba *" placeholder="Pilih Cabang Lomba">
                        @foreach($competitions as $c)
                            <flux:select.option value="{{ $c->id }}">
                                {{ $c->code }} — {{ $c->title }} ({{ ucfirst($c->participant_type) }})
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <div class="sm:col-span-2">
                    <flux:input wire:model="session_name" label="Nama Sesi Pelaksanaan *" placeholder="Contoh: Babak Penyisihan Regu Putra" />
                </div>

                <div class="sm:col-span-2">
                    <flux:input wire:model="arena_location" label="Lokasi Arena / Pos *" placeholder="Contoh: Pos 1 — Lapangan Utama Buper" />
                </div>

                <div>
                    <flux:input type="datetime-local" wire:model="start_time" label="Waktu Mulai *" />
                </div>

                <div>
                    <flux:input type="datetime-local" wire:model="end_time" label="Waktu Selesai *" />
                </div>

                <div class="sm:col-span-2 rounded-xl bg-blue-50/80 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/60 p-3.5">
                    <div class="flex items-start gap-2.5">
                        <flux:icon name="clock" class="size-4 text-blue-600 dark:text-blue-400 mt-0.5 shrink-0" />
                        <div class="text-xs text-blue-900 dark:text-blue-200 space-y-0.5">
                            <p class="font-bold text-blue-950 dark:text-blue-100">Jadwal Presensi Terotomatisasi & Pembatasan Kategori Regu</p>
                            <p>Presensi pos dibuka otomatis H-30 menit sebelum lomba dimulai. Sistem akan memvalidasi agar regu Putra/Putri hanya bisa melakukan absensi pada sesi yang sesuai.</p>
                        </div>
                    </div>
                </div>

                <div>
                    <flux:select wire:model="gender_category" label="Kategori Peserta / Regu *">
                        <flux:select.option value="all">Putra & Putri (Terbuka)</flux:select.option>
                        <flux:select.option value="boys">Khusus Putra</flux:select.option>
                        <flux:select.option value="girls">Khusus Putri</flux:select.option>
                    </flux:select>
                </div>

                <div>
                    <flux:select wire:model="participant_type" label="Tipe Format Lomba">
                        <flux:select.option value="patrol">Regu Utuh (Wajib PINRU/WAPINRU)</flux:select.option>
                        <flux:select.option value="group">Kelompok (Beberapa Anggota dalam Regu)</flux:select.option>
                        <flux:select.option value="individual">Individu (Perwakilan)</flux:select.option>
                    </flux:select>
                </div>

                <div class="sm:col-span-2">
                    <flux:select wire:model="status" label="Status Sesi">
                        <flux:select.option value="scheduled">Dijadwalkan</flux:select.option>
                        <flux:select.option value="in_progress">Sedang Berlangsung</flux:select.option>
                        <flux:select.option value="completed">Selesai</flux:select.option>
                        <flux:select.option value="rescheduled">Ditunda</flux:select.option>
                        <flux:select.option value="cancelled">Dibatalkan</flux:select.option>
                    </flux:select>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:button type="button" variant="subtle" wire:click="$set('showModal', false)">
                    Batal
                </flux:button>
                <flux:button type="submit" variant="primary">
                    {{ $editingId ? 'Simpan Perubahan' : 'Buat Jadwal Sesi' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Modal Manajemen Penugasan Juri --}}
    <flux:modal wire:model="showJudgesModal" class="max-w-2xl">
        <div class="space-y-5">
            <div>
                <flux:heading size="lg">
                    Penugasan Dewan Juri Sesi
                </flux:heading>
                <flux:subheading>
                    {{ $selectedSchedule?->session_name }} — {{ $selectedSchedule?->arena_location }}
                </flux:subheading>
            </div>

            {{-- Form Tambah Penugasan Juri --}}
            <form wire:submit="assignJudge" class="rounded-xl border border-zinc-200/80 bg-zinc-50 p-4 space-y-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                <div class="text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                    Tugaskan Juri Baru
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <flux:select wire:model="selectedJudgeId" placeholder="Pilih Juri / Aparatur">
                            @foreach($availableJudges as $judge)
                                <flux:select.option value="{{ $judge->id }}">
                                    {{ $judge->name }} ({{ $judge->username }})
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>

                    <div>
                        <flux:select wire:model="judgeRole" label="Peran Juri">
                            <flux:select.option value="scoring_judge">Juri Penilai</flux:select.option>
                            <flux:select.option value="lead_judge">Ketua Dewan Juri</flux:select.option>
                            <flux:select.option value="companion_judge">Juri Pendamping</flux:select.option>
                        </flux:select>
                    </div>

                    <div>
                        <flux:input wire:model="judgeNotes" label="Catatan / Posisi Khusus" placeholder="Misal: Penilai Pos A" />
                    </div>
                </div>

                <div class="flex justify-end pt-1">
                    <flux:button type="submit" variant="primary" size="sm" icon="plus">
                        Tugaskan ke Sesi Ini
                    </flux:button>
                </div>
            </form>

            {{-- Daftar Juri Terpasang --}}
            <div class="space-y-2">
                <div class="text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                    Daftar Juri Bertugas ({{ $selectedSchedule?->judgeAssignments->count() ?? 0 }})
                </div>

                <div class="divide-y divide-zinc-200 rounded-xl border border-zinc-200/80 bg-white dark:divide-zinc-800 dark:border-zinc-800 dark:bg-zinc-900">
                    @forelse($selectedSchedule?->judgeAssignments ?? [] as $assignment)
                        <div class="flex items-center justify-between p-3 text-sm">
                            <div>
                                <div class="font-bold text-zinc-900 dark:text-white">
                                    {{ $assignment->judge?->name }}
                                </div>
                                <div class="flex items-center gap-2 text-xs text-zinc-500">
                                    <span class="rounded bg-amber-500/10 px-1.5 py-0.5 font-semibold text-amber-700 dark:bg-amber-500/20 dark:text-amber-300">
                                        {{ $assignment->role === 'lead_judge' ? 'Ketua Juri' : ($assignment->role === 'scoring_judge' ? 'Juri Penilai' : 'Pendamping') }}
                                    </span>
                                    @if($assignment->notes)
                                        <span>• {{ $assignment->notes }}</span>
                                    @endif
                                </div>
                            </div>

                            <flux:button 
                                variant="subtle" 
                                size="sm" 
                                icon="x-mark" 
                                class="text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/40"
                                wire:click="removeJudge('{{ $assignment->id }}')"
                                title="Batalkan Penugasan"
                            />
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs text-zinc-400">
                            Belum ada juri yang ditugaskan pada sesi ini.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="flex justify-end pt-3 border-t border-zinc-200 dark:border-zinc-800">
                <flux:button type="button" variant="subtle" wire:click="$set('showJudgesModal', false)">
                    Tutup
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Modal Konfirmasi Penentuan Juri Otomatis --}}
    <flux:modal wire:model="showAutoAssignModal" class="max-w-md">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">Tentukan Juri Otomatis</flux:heading>
                <flux:text class="mt-2">
                    Setiap sesi lomba dilengkapi hingga 3 juri (1 Ketua Juri dan 2 Juri Penilai). Beban penugasan dibagi rata dan juri
                    diusahakan tidak bertugas pada dua sesi yang waktunya bertumpuk. Penugasan yang sudah ada tidak diubah.
                </flux:text>
            </div>

            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="subtle" wire:click="$set('showAutoAssignModal', false)">
                    Batal
                </flux:button>
                <flux:button type="button" variant="primary" icon="sparkles" wire:click="autoAssignJudges" wire:loading.attr="disabled" wire:target="autoAssignJudges">
                    Tentukan Sekarang
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>

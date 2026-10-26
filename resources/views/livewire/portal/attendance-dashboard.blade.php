<div class="space-y-6">
    {{-- Header Section --}}
    <x-heading-section
        title="Portal Presensi Pos Arena"
        description="Pemantauan sesi presensi, aktivasi scanner QR barcode ID Card peserta, PINRU/WAPINRU, dan bindamping."
        badge="Portal Presensi & Scanner QR"
        badge-icon="qr-code"
        badge-color="emerald"
    >
        <x-slot:actions>
            <flux:button href="{{ route('attendance.recap') }}" variant="subtle" icon="clipboard-document-list" wire:navigate>
                Rekapitulasi Presensi
            </flux:button>

            @if (auth()->user()->isAdmin())
                <flux:button type="button" variant="primary" icon="plus" class="bg-emerald-600 hover:bg-emerald-700"
                    wire:click="$set('showCreateModal', true)">
                    Tambah Sesi Kehadiran
                </flux:button>
            @endif
        </x-slot:actions>
    </x-heading-section>

    {{-- Context Banner Peran Pengguna --}}
    @if (auth()->user()->isCommittee() && !auth()->user()->isAdmin() && !auth()->user()->isJudge())
        <div
            class="rounded-2xl border border-blue-200 bg-blue-50/80 p-4 text-xs text-blue-900 dark:border-blue-900/50 dark:bg-blue-950/30 dark:text-blue-200 flex items-center gap-3">
            <div class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white font-bold">
                <flux:icon name="information-circle" class="size-4" />
            </div>
            <div>
                <strong class="font-bold">Akses Panitia:</strong>
                <p class="mt-0.5 text-blue-800/80 dark:text-blue-300/80">Scanner presensi Anda dikhususkan untuk
                    <strong>kehadiran umum</strong> (apel, registrasi, upacara, bindamping, panitia) dan bukan untuk pos
                    mata lomba.
                </p>
            </div>
        </div>
    @elseif(auth()->user()->isJudge() && !auth()->user()->isAdmin() && !auth()->user()->isCommittee())
        <div
            class="rounded-2xl border border-amber-200 bg-amber-50/80 p-4 text-xs text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-200 flex items-center gap-3">
            <div class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-amber-600 text-white font-bold">
                <flux:icon name="trophy" class="size-4" />
            </div>
            <div>
                <strong class="font-bold">Akses Dewan Juri:</strong>
                <p class="mt-0.5 text-amber-800/80 dark:text-amber-300/80">Scanner presensi Anda disesuaikan khusus
                    untuk <strong>pos mata lomba yang ditugaskan</strong> kepada Anda.</p>
            </div>
        </div>
    @elseif(auth()->user()->isCommittee() && auth()->user()->isJudge() && !auth()->user()->isAdmin())
        <div
            class="rounded-2xl border border-purple-200 bg-purple-50/80 p-4 text-xs text-purple-900 dark:border-purple-900/50 dark:bg-purple-950/30 dark:text-purple-200 flex items-center gap-3">
            <div class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-purple-600 text-white font-bold">
                <flux:icon name="arrows-pointing-out" class="size-4" />
            </div>
            <div>
                <strong class="font-bold">Akses Rangkap (Panitia & Juri):</strong>
                <p class="mt-0.5 text-purple-800/80 dark:text-purple-300/80">Anda dapat memindai sesi <strong>kehadiran
                        umum</strong> (selaku Panitia) dan sesi <strong>pos mata lomba yang ditugaskan</strong> (selaku
                    Juri).</p>
            </div>
        </div>
    @endif

    {{-- Stats Cards Overview --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card
            label="Total Sesi Presensi"
            value="{{ $stats['total_sessions'] }}"
            description="Semua pos arena terdaftar"
            color="neutral"
        />

        <x-stat-card
            label="Sesi Aktif Siap Scan"
            value="{{ $stats['active_sessions'] }}"
            description="Peserta dapat memindai kehadiran"
            color="emerald"
        />

        <x-stat-card
            label="Total Presensi Hari Ini"
            value="{{ $stats['total_attendances_today'] }}"
            description="Peserta tercatat masuk pos"
            color="blue"
        />
    </div>

    {{-- Filter & Search Toolbar --}}
    <div
        class="flex flex-col gap-3 rounded-2xl border border-zinc-200/80 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center">
            <div class="w-full sm:max-w-md">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
                    placeholder="Cari sesi presensi, pos arena, atau cabang lomba..." clearable />
            </div>

            <div class="flex items-center gap-2">
                <button type="button" wire:click="$set('statusFilter', 'all')"
                    class="rounded-xl px-3 py-1.5 text-xs font-semibold transition-all {{ $statusFilter === 'all' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300' }}">
                    Semua
                </button>
                <button type="button" wire:click="$set('statusFilter', 'active')"
                    class="rounded-xl px-3 py-1.5 text-xs font-semibold transition-all {{ $statusFilter === 'active' ? 'bg-emerald-600 text-white' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300' }}">
                    Sesi Aktif
                </button>
                <button type="button" wire:click="$set('statusFilter', 'inactive')"
                    class="rounded-xl px-3 py-1.5 text-xs font-semibold transition-all {{ $statusFilter === 'inactive' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300' }}">
                    Sesi Nonaktif
                </button>
            </div>
        </div>

        <div class="text-xs text-zinc-500">
            Ditemukan: <strong class="text-zinc-800 dark:text-zinc-200">{{ $sessions->total() }}</strong> sesi pos
        </div>
    </div>

    {{-- Sessions Grid Cards --}}
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
        @forelse($sessions as $session)
            @php
                $competition = $session->schedule?->competition;
                $format = $session->schedule?->participant_type ?? ($competition?->participant_type ?? 'patrol');
                $checkinCount = $session->attendances->count();

                if (!$session->is_active) {
                    $cardBorderClasses =
                        'border-rose-200/80 bg-rose-50/20 dark:border-rose-950/40 dark:bg-zinc-900/40 opacity-80';
                } elseif ($session->isScheduleClosed()) {
                    $cardBorderClasses =
                        'border-zinc-200/80 bg-zinc-50/60 dark:border-zinc-800 dark:bg-zinc-900/60 opacity-90';
                } elseif (!$session->isScheduleOpen()) {
                    $cardBorderClasses = 'border-amber-200/80 bg-amber-50/15 dark:border-amber-900/40 dark:bg-zinc-900';
                } else {
                    $cardBorderClasses =
                        'border-emerald-300/80 bg-white dark:border-emerald-900/60 dark:bg-zinc-900 shadow-emerald-500/5';
                }
            @endphp
            <div
                class="flex flex-col justify-between rounded-2xl border transition-all duration-200 hover:shadow-md {{ $cardBorderClasses }}">
                <div class="p-5">
                    {{-- Session Status Pill & Format Badge --}}
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            @if (!$session->is_active)
                                <span
                                    class="inline-flex items-center rounded-full bg-rose-500/10 px-2.5 py-1 text-xs font-bold text-rose-600 dark:bg-rose-500/20 dark:text-rose-400">
                                    NONAKTIF
                                </span>
                            @elseif($session->isScheduleClosed())
                                <span
                                    class="inline-flex items-center rounded-full bg-zinc-200 px-2.5 py-1 text-xs font-bold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                    DITUTUP
                                </span>
                            @elseif(!$session->isScheduleOpen())
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/10 px-2.5 py-1 text-xs font-bold text-amber-700 dark:bg-amber-500/20 dark:text-amber-400">
                                    <span class="size-2 rounded-full bg-amber-500"></span>
                                    BELUM BUKA
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-bold text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400">
                                    <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    AKTIF SCAN
                                </span>
                            @endif

                            <span
                                class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold border {{ $session->scheduleStatusBadgeClasses() }}">
                                {{ $session->scheduleStatusLabel() }}
                            </span>
                        </div>

                        <div class="flex items-center gap-1 flex-wrap">
                            @if ($session->schedule)
                                <span
                                    class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[11px] font-semibold {{ $session->schedule->genderBadgeClasses() }}">
                                    @if ($session->schedule->effectiveGenderCategory() === 'boys')
                                        <flux:icon name="user" class="size-3 text-blue-600 dark:text-blue-400" />
                                    @elseif($session->schedule->effectiveGenderCategory() === 'girls')
                                        <flux:icon name="user" class="size-3 text-pink-600 dark:text-pink-400" />
                                    @else
                                        <flux:icon name="users" class="size-3 text-zinc-500" />
                                    @endif
                                    {{ $session->schedule->genderLabel() }}
                                </span>
                            @endif

                            @if ($session->isCompetition())
                                <span
                                    class="inline-flex items-center gap-1 rounded-md bg-amber-500/10 px-2 py-0.5 text-[11px] font-semibold text-amber-700 dark:bg-amber-500/20 dark:text-amber-300"
                                    title="Sesi Pos Mata Lomba">
                                    <flux:icon name="trophy" class="size-3" />
                                    Lomba
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center gap-1 rounded-md bg-emerald-500/10 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300"
                                    title="Sesi Presensi Kehadiran Umum">
                                    <flux:icon name="check-badge" class="size-3" />
                                    Kehadiran
                                </span>
                            @endif

                            @if ($session->isCompetition())
                                @if ($format === 'individual')
                                    <span
                                        class="inline-flex items-center gap-1 rounded-md bg-purple-500/10 px-2 py-0.5 text-[11px] font-semibold text-purple-700 dark:bg-purple-500/20 dark:text-purple-300">
                                        <flux:icon name="user" class="size-3" />
                                        Individu
                                    </span>
                                @elseif($format === 'group')
                                    <span
                                        class="inline-flex items-center gap-1 rounded-md bg-cyan-500/10 px-2 py-0.5 text-[11px] font-semibold text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300">
                                        <flux:icon name="users" class="size-3" />
                                        Kelompok
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1 rounded-md bg-blue-500/10 px-2 py-0.5 text-[11px] font-semibold text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">
                                        <flux:icon name="user-group" class="size-3" />
                                        Regu Utuh
                                    </span>
                                @endif
                            @else
                                <span
                                    class="inline-flex items-center gap-1 rounded-md bg-zinc-100 px-2 py-0.5 text-[11px] font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                    {{ $session->sessionTypeLabel() }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Title & Details --}}
                    <div class="mt-3">
                        <h3 class="text-base font-bold text-zinc-900 dark:text-white line-clamp-1"
                            title="{{ $session->title }}">
                            {{ $session->title }}
                        </h3>

                        @if ($session->schedule)
                            <div class="mt-1 flex items-center gap-1.5 text-xs text-zinc-600 dark:text-zinc-400">
                                <flux:icon name="map-pin" class="size-3.5 text-zinc-400 shrink-0" />
                                <span class="truncate">{{ $session->schedule->arena_location }}</span>
                            </div>
                            <div class="mt-1 flex items-center gap-1.5 text-xs text-zinc-500 dark:text-zinc-400">
                                <flux:icon name="clock" class="size-3.5 text-zinc-400 shrink-0" />
                                <span>{{ \Carbon\Carbon::parse($session->schedule->start_time)->isoFormat('D MMM, HH:mm') }}
                                    - {{ \Carbon\Carbon::parse($session->schedule->end_time)->isoFormat('HH:mm') }}
                                    WIB</span>
                            </div>
                        @elseif($session->opened_at || $session->closed_at)
                            <div class="mt-1 flex items-center gap-1.5 text-xs text-zinc-500 dark:text-zinc-400">
                                <flux:icon name="clock" class="size-3.5 text-zinc-400 shrink-0" />
                                <span>
                                    @if ($session->opened_at && $session->closed_at)
                                        {{ \Carbon\Carbon::parse($session->opened_at)->setTimezone('Asia/Jakarta')->isoFormat('D MMM, HH:mm') }}
                                        -
                                        {{ \Carbon\Carbon::parse($session->closed_at)->setTimezone('Asia/Jakarta')->isoFormat('HH:mm') }}
                                        WIB
                                    @elseif($session->opened_at)
                                        Mulai:
                                        {{ \Carbon\Carbon::parse($session->opened_at)->setTimezone('Asia/Jakarta')->isoFormat('D MMM, HH:mm') }}
                                        WIB
                                    @else
                                        Batas:
                                        {{ \Carbon\Carbon::parse($session->closed_at)->setTimezone('Asia/Jakarta')->isoFormat('D MMM, HH:mm') }}
                                        WIB
                                    @endif
                                </span>
                            </div>
                        @endif

                        @if ($competition)
                            <div class="mt-1 flex items-center gap-1.5 text-xs text-zinc-500 dark:text-zinc-400">
                                <span
                                    class="font-mono font-semibold text-amber-600 dark:text-amber-400">{{ $competition->code }}</span>
                                <span>• {{ $competition->title }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Checkin Count Indicator --}}
                    <div
                        class="mt-4 rounded-xl border border-zinc-100 bg-zinc-50/80 p-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-zinc-500">
                                @if ($session->isCompetition())
                                    @if ($format === 'patrol')
                                        Regu Hadir Terdata:
                                    @else
                                        Peserta Hadir Terdata:
                                    @endif
                                @else
                                    @if ($session->session_type === 'chaperone')
                                        Pendamping Hadir Terdata:
                                    @elseif($session->session_type === 'committee')
                                        Panitia Hadir Terdata:
                                    @elseif($session->session_type === 'judge')
                                        Juri Hadir Terdata:
                                    @else
                                        Peserta Hadir Terdata:
                                    @endif
                                @endif
                            </span>
                            <span
                                class="font-mono font-bold text-sm text-zinc-900 dark:text-white">{{ $checkinCount }}</span>
                        </div>
                        <div class="mt-1 text-[11px] text-zinc-500 dark:text-zinc-400">
                            @if ($session->isCompetition())
                                @if ($format === 'patrol')
                                    Wajib dipindai PINRU/WAPINRU
                                @elseif($format === 'group')
                                    Wajib dipindai anggota kelompok regu
                                @else
                                    1 perwakilan peserta per regu
                                @endif
                            @else
                                @if ($session->session_type === 'chaperone')
                                    Pemindaian kartu identitas pembina pendamping
                                @elseif($session->session_type === 'committee')
                                    Pemindaian kartu aparatur panitia pelaksana
                                @elseif($session->session_type === 'judge')
                                    Pemindaian kartu aparatur dewan juri
                                @else
                                    Pemindaian kartu identitas peserta (B2) perorangan
                                @endif
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Card Actions --}}
                <div
                    class="flex items-center justify-between gap-2 border-t border-zinc-100 p-4 dark:border-zinc-800/80 bg-zinc-50/50 dark:bg-zinc-900/50 rounded-b-2xl">
                    <flux:button type="button" size="xs" variant="subtle" wire:click="toggleSession('{{ $session->id }}')"
                        class="{{ $session->is_active ? 'text-amber-700 hover:bg-amber-100 dark:text-amber-400 dark:hover:bg-amber-950/40' : 'text-emerald-700 hover:bg-emerald-100 dark:text-emerald-400 dark:hover:bg-emerald-950/40' }}">
                        {{ $session->is_active ? 'Tutup Sesi' : 'Aktifkan Sesi' }}
                    </flux:button>

                    <div class="flex items-center gap-1.5">
                        @if (auth()->user()?->isAdmin())
                            <flux:button type="button" size="xs" variant="subtle" icon="pencil-square" wire:click="openEditModal('{{ $session->id }}')"
                                title="Edit Sesi Presensi & Jadwal">
                                Edit
                            </flux:button>
                        @endif

                        <flux:button type="button" size="xs" variant="subtle" icon="clock" wire:click="openLogsModal('{{ $session->id }}')"
                            title="Lihat Visual Log & Riwayat Presensi">
                            Log
                        </flux:button>

                        <flux:button href="{{ route('attendance.scanner', $session) }}" size="xs" variant="primary" icon="qr-code"
                            class="bg-emerald-600 hover:bg-emerald-500 text-white shadow-xs"
                            title="Buka Kamera Scanner" wire:navigate>
                            Scanner
                        </flux:button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center text-zinc-500">
                <flux:icon name="qr-code" class="mx-auto size-12 text-zinc-400 opacity-60" />
                <div class="mt-3 font-semibold text-base text-zinc-800 dark:text-zinc-200">Belum ada sesi presensi pos
                </div>
                <div class="text-xs">Sesi presensi otomatis dibuat ketika Anda menambahkan jadwal cabang lomba di menu
                    Jadwal.</div>
            </div>
        @endforelse
    </div>

    @if ($sessions->hasPages())
        <div class="border-t border-zinc-200 px-6 py-4 dark:border-zinc-800">
            {{ $sessions->links() }}
        </div>
    @endif

    {{-- Modal Tambah Sesi Presensi Kehadiran (Administrator Only) --}}
    <flux:modal wire:model="showCreateModal" class="w-full max-w-xl sm:max-w-2xl max-h-[92vh] overflow-y-auto">
        <form wire:submit.prevent="createAttendanceSession" class="space-y-4">
            {{-- Modal Header --}}
            <div class="flex items-start gap-3.5 pb-3 border-b border-zinc-200/80 dark:border-zinc-800">
                <div
                    class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-700 text-white shadow-sm shadow-emerald-500/20 mt-0.5">
                    <flux:icon name="qr-code" class="size-5" />
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-0.5">
                        <span
                            class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800">
                            Presensi Kegiatan Umum
                        </span>
                        <span class="text-[11px] text-zinc-400 font-medium">Khusus Administrator</span>
                    </div>
                    <h2 class="text-base sm:text-lg font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                        Tambah Sesi Presensi Kehadiran
                    </h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5 leading-normal">
                        Konfigurasi sesi pemindaian QR code untuk kegiatan apel, registrasi pangkalan, upacara, atau pos
                        kehadiran umum.
                    </p>
                </div>
            </div>

            {{-- 1. Nama / Judul Sesi --}}
            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-bold text-zinc-800 dark:text-zinc-200 uppercase tracking-wider">
                        Nama / Judul Sesi Presensi <span class="text-rose-500">*</span>
                    </label>
                    <span class="text-[10px] text-zinc-400 font-medium">Wajib diisi</span>
                </div>
                <flux:input wire:model="newTitle"
                    placeholder="Contoh: Presensi Apel Pembukaan & Upacara Adat Bhinneka" required />
                @error('newTitle')
                    <span class="text-xs text-rose-500 font-medium">{{ $message }}</span>
                @enderror

                {{-- Rekomendasi Cepat / Presets --}}
                <div class="flex flex-wrap items-center gap-1 pt-0.5">
                    <span class="text-[10px] font-semibold text-zinc-400 mr-1">Rekomendasi Judul:</span>
                    <button type="button" wire:click="$set('newTitle', 'Presensi Apel Pembukaan & Upacara')"
                        class="rounded-md border border-zinc-200 bg-zinc-50 px-2 py-0.5 text-[10px] font-medium text-zinc-700 hover:border-emerald-500 hover:bg-emerald-50 hover:text-emerald-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700 transition cursor-pointer">
                        Apel Pembukaan
                    </button>
                    <button type="button" wire:click="$set('newTitle', 'Daftar Ulang Kontingen Gudep')"
                        class="rounded-md border border-zinc-200 bg-zinc-50 px-2 py-0.5 text-[10px] font-medium text-zinc-700 hover:border-emerald-500 hover:bg-emerald-50 hover:text-emerald-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700 transition cursor-pointer">
                        Daftar Ulang
                    </button>
                    <button type="button" wire:click="$set('newTitle', 'Presensi Apel Pagi Buper')"
                        class="rounded-md border border-zinc-200 bg-zinc-50 px-2 py-0.5 text-[10px] font-medium text-zinc-700 hover:border-emerald-500 hover:bg-emerald-50 hover:text-emerald-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700 transition cursor-pointer">
                        Apel Pagi
                    </button>
                    <button type="button" wire:click="$set('newTitle', 'Malam Keakraban & Api Unggun')"
                        class="rounded-md border border-zinc-200 bg-zinc-50 px-2 py-0.5 text-[10px] font-medium text-zinc-700 hover:border-emerald-500 hover:bg-emerald-50 hover:text-emerald-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700 transition cursor-pointer">
                        Api Unggun
                    </button>
                    <button type="button" wire:click="$set('newTitle', 'Presensi Upacara Penutupan LT III')"
                        class="rounded-md border border-zinc-200 bg-zinc-50 px-2 py-0.5 text-[10px] font-medium text-zinc-700 hover:border-emerald-500 hover:bg-emerald-50 hover:text-emerald-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700 transition cursor-pointer">
                        Upacara Penutupan
                    </button>
                </div>
            </div>

            {{-- 2. Sasaran Entitas Presensi --}}
            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-bold text-zinc-800 dark:text-zinc-200 uppercase tracking-wider">
                        Sasaran Entitas Presensi <span class="text-rose-500">*</span>
                    </label>
                    <span class="text-[10px] text-zinc-400">Pilih salah satu sasaran kartu ID</span>
                </div>

                {{-- Interactive 4-Card Selector --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    {{-- Kartu 1: Peserta --}}
                    <div type="button" wire:click="$set('newSessionType', 'participant')"
                        class="cursor-pointer relative flex items-start gap-2.5 p-2.5 rounded-xl border transition {{ $newSessionType === 'participant' ? 'border-emerald-600 bg-emerald-50/60 dark:bg-emerald-950/25 ring-2 ring-emerald-500/20 shadow-xs' : 'border-zinc-200 bg-white hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900/60 dark:hover:border-zinc-700' }}">
                        <div
                            class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ $newSessionType === 'participant' ? 'bg-emerald-600 text-white' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400' }}">
                            <flux:icon name="academic-cap" class="size-4" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xs text-zinc-900 dark:text-zinc-100">Peserta
                                    Penggalang</span>
                                @if ($newSessionType === 'participant')
                                    <span class="size-2 rounded-full bg-emerald-600"></span>
                                @endif
                            </div>
                            <p class="text-[10px] text-zinc-500 dark:text-zinc-400 mt-0.5 leading-tight">
                                Anggota regu penggalang, PINRU, dan WAPINRU
                            </p>
                        </div>
                    </div>

                    {{-- Kartu 2: Pembina Pendamping --}}
                    <div type="button" wire:click="$set('newSessionType', 'chaperone')"
                        class="cursor-pointer relative flex items-start gap-2.5 p-2.5 rounded-xl border transition {{ $newSessionType === 'chaperone' ? 'border-emerald-600 bg-emerald-50/60 dark:bg-emerald-950/25 ring-2 ring-emerald-500/20 shadow-xs' : 'border-zinc-200 bg-white hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900/60 dark:hover:border-zinc-700' }}">
                        <div
                            class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ $newSessionType === 'chaperone' ? 'bg-emerald-600 text-white' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400' }}">
                            <flux:icon name="user-group" class="size-4" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xs text-zinc-900 dark:text-zinc-100">Pembina
                                    Pendamping</span>
                                @if ($newSessionType === 'chaperone')
                                    <span class="size-2 rounded-full bg-emerald-600"></span>
                                @endif
                            </div>
                            <p class="text-[10px] text-zinc-500 dark:text-zinc-400 mt-0.5 leading-tight">
                                Bindamping putra dan putri pangkalan Gudep
                            </p>
                        </div>
                    </div>

                    {{-- Kartu 3: Panitia Lapangan --}}
                    <div type="button" wire:click="$set('newSessionType', 'committee')"
                        class="cursor-pointer relative flex items-start gap-2.5 p-2.5 rounded-xl border transition {{ $newSessionType === 'committee' ? 'border-emerald-600 bg-emerald-50/60 dark:bg-emerald-950/25 ring-2 ring-emerald-500/20 shadow-xs' : 'border-zinc-200 bg-white hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900/60 dark:hover:border-zinc-700' }}">
                        <div
                            class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ $newSessionType === 'committee' ? 'bg-emerald-600 text-white' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400' }}">
                            <flux:icon name="shield-check" class="size-4" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xs text-zinc-900 dark:text-zinc-100">Panitia</span>
                                @if ($newSessionType === 'committee')
                                    <span class="size-2 rounded-full bg-emerald-600"></span>
                                @endif
                            </div>
                            <p class="text-[10px] text-zinc-500 dark:text-zinc-400 mt-0.5 leading-tight">
                                Aparatur panitia pelaksana operasional buper
                            </p>
                        </div>
                    </div>

                    {{-- Kartu 4: Dewan Juri --}}
                    <div type="button" wire:click="$set('newSessionType', 'judge')"
                        class="cursor-pointer relative flex items-start gap-2.5 p-2.5 rounded-xl border transition {{ $newSessionType === 'judge' ? 'border-emerald-600 bg-emerald-50/60 dark:bg-emerald-950/25 ring-2 ring-emerald-500/20 shadow-xs' : 'border-zinc-200 bg-white hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900/60 dark:hover:border-zinc-700' }}">
                        <div
                            class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ $newSessionType === 'judge' ? 'bg-emerald-600 text-white' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400' }}">
                            <flux:icon name="trophy" class="size-4" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xs text-zinc-900 dark:text-zinc-100">Dewan Juri</span>
                                @if ($newSessionType === 'judge')
                                    <span class="size-2 rounded-full bg-emerald-600"></span>
                                @endif
                            </div>
                            <p class="text-[10px] text-zinc-500 dark:text-zinc-400 mt-0.5 leading-tight">
                                Aparatur dewan juri penilai cabang lomba LT III
                            </p>
                        </div>
                    </div>
                </div>
                @error('newSessionType')
                    <span class="text-xs text-rose-500 font-medium">{{ $message }}</span>
                @enderror
            </div>

            {{-- 3. Rentang Waktu Buka & Tutup --}}
            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-bold text-zinc-800 dark:text-zinc-200 uppercase tracking-wider">
                        Periode Waktu Presensi
                    </label>
                    <span class="text-[10px] text-zinc-400 font-medium">Opsional</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <flux:input type="datetime-local" wire:model="newOpenedAt" label="Waktu Mulai Buka" />
                        <span class="block text-[10px] text-zinc-400 mt-0.5">
                            Kosongkan untuk langsung dibuka sekarang.
                        </span>
                        @error('newOpenedAt')
                            <span class="text-xs text-rose-500 font-medium">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <flux:input type="datetime-local" wire:model="newClosedAt" label="Waktu Selesai Tutup" />
                        <span class="block text-[10px] text-zinc-400 mt-0.5">
                            Kosongkan jika tanpa batas waktu tutup.
                        </span>
                        @error('newClosedAt')
                            <span class="text-xs text-rose-500 font-medium">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Info Notice Callout --}}
            <div
                class="rounded-xl border border-zinc-200/80 bg-zinc-50/80 p-2.5 text-xs text-zinc-600 dark:border-zinc-800 dark:bg-zinc-800/40 dark:text-zinc-300 flex items-start gap-2.5">
                <flux:icon name="information-circle"
                    class="size-4 text-emerald-600 dark:text-emerald-400 mt-0.5 shrink-0" />
                <p class="text-[11px] leading-relaxed">
                    Sesi presensi kehadiran umum tidak terikat pada jadwal mata lomba. Scanner akan memverifikasi QR
                    peserta atau aparatur sesuai dengan sasaran entitas yang Anda tetapkan.
                </p>
            </div>

            {{-- 4. Status Aktivasi Sesi --}}
            <div
                class="rounded-xl border border-zinc-200 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-800/50 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ $newIsActive ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400' : 'bg-zinc-100 text-zinc-400 dark:bg-zinc-800' }}">
                        <flux:icon name="{{ $newIsActive ? 'bolt' : 'pause' }}" class="size-4" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Status Aktivasi
                                Sesi</span>
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $newIsActive ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' }}">
                                {{ $newIsActive ? 'Siap Pindai' : 'Draft / Tertutup' }}
                            </span>
                        </div>
                        <p class="text-[10px] text-zinc-500 dark:text-zinc-400 mt-0.5">
                            {{ $newIsActive ? 'Sesi langsung aktif dan siap dipindai oleh petugas melalui menu Scanner Presensi.' : 'Sesi disimpan sebagai draft dan dapat diaktifkan sewaktu-waktu.' }}
                        </p>
                    </div>
                </div>
                <flux:switch wire:model.live="newIsActive" />
            </div>

            {{-- Modal Footer --}}
            <div class="flex items-center justify-between gap-3 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                <flux:button type="button" variant="subtle" wire:click="$set('showCreateModal', false)">
                    Batal
                </flux:button>
                <flux:button type="submit" variant="primary"
                    class="bg-emerald-600 hover:bg-emerald-700 font-semibold shadow-xs">
                    <flux:icon name="plus" class="size-4 mr-1.5" />
                    <span>Simpan Sesi Kehadiran</span>
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Modal Edit Sesi Presensi Kehadiran & Penyesuaian Jadwal (Administrator Only) --}}
    <flux:modal wire:model="showEditModal" class="w-full max-w-xl sm:max-w-2xl max-h-[92vh] overflow-y-auto">
        <form wire:submit.prevent="updateAttendanceSession" class="space-y-4">
            {{-- Modal Header --}}
            <div class="flex items-start gap-3.5 pb-3 border-b border-zinc-200/80 dark:border-zinc-800">
                <div
                    class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-amber-700 text-white shadow-sm shadow-amber-500/20 mt-0.5">
                    <flux:icon name="pencil-square" class="size-5" />
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-0.5">
                        <span
                            class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800">
                            {{ $editScheduleId ? 'Presensi Pos Lomba' : 'Presensi Kehadiran Umum' }}
                        </span>
                        <span class="text-[11px] text-zinc-400 font-medium">Khusus Administrator</span>
                    </div>
                    <h2 class="text-base sm:text-lg font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                        Edit Sesi Presensi & Jadwal Lomba
                    </h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5 leading-normal">
                        Koreksi kesalahan judul, sasaran peserta, kaitan sesi lomba, atau penyesuaian waktu dan arena
                        pelaksanaan.
                    </p>
                </div>
            </div>

            {{-- 1. Nama / Judul Sesi --}}
            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-bold text-zinc-800 dark:text-zinc-200 uppercase tracking-wider">
                        Nama / Judul Sesi Presensi <span class="text-rose-500">*</span>
                    </label>
                    <span class="text-[10px] text-zinc-400 font-medium">Wajib diisi</span>
                </div>
                <flux:input wire:model="editTitle" placeholder="Nama sesi presensi..." required />
                @error('editTitle')
                    <span class="text-xs text-rose-500 font-medium">{{ $message }}</span>
                @enderror
            </div>

            {{-- 2. Sasaran Entitas Presensi --}}
            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-bold text-zinc-800 dark:text-zinc-200 uppercase tracking-wider">
                        Sasaran Entitas Presensi <span class="text-rose-500">*</span>
                    </label>
                    <span class="text-[10px] text-zinc-400">Pilih sasaran kartu ID pemindaian</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    {{-- Peserta --}}
                    <div type="button" wire:click="$set('editSessionType', 'participant')"
                        class="cursor-pointer relative flex items-start gap-2.5 p-2.5 rounded-xl border transition {{ $editSessionType === 'participant' ? 'border-amber-600 bg-amber-50/60 dark:bg-amber-950/25 ring-2 ring-amber-500/20 shadow-xs' : 'border-zinc-200 bg-white hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900/60 dark:hover:border-zinc-700' }}">
                        <div
                            class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ $editSessionType === 'participant' ? 'bg-amber-600 text-white' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400' }}">
                            <flux:icon name="academic-cap" class="size-4" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xs text-zinc-900 dark:text-zinc-100">Peserta
                                    Penggalang</span>
                                @if ($editSessionType === 'participant')
                                    <span class="size-2 rounded-full bg-amber-600"></span>
                                @endif
                            </div>
                            <p class="text-[10px] text-zinc-500 dark:text-zinc-400 mt-0.5 leading-tight">
                                Anggota regu penggalang, PINRU, dan WAPINRU
                            </p>
                        </div>
                    </div>

                    {{-- Bindamping --}}
                    <div type="button" wire:click="$set('editSessionType', 'chaperone')"
                        class="cursor-pointer relative flex items-start gap-2.5 p-2.5 rounded-xl border transition {{ $editSessionType === 'chaperone' ? 'border-amber-600 bg-amber-50/60 dark:bg-amber-950/25 ring-2 ring-amber-500/20 shadow-xs' : 'border-zinc-200 bg-white hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900/60 dark:hover:border-zinc-700' }}">
                        <div
                            class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ $editSessionType === 'chaperone' ? 'bg-amber-600 text-white' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400' }}">
                            <flux:icon name="user-group" class="size-4" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xs text-zinc-900 dark:text-zinc-100">Pembina
                                    Pendamping</span>
                                @if ($editSessionType === 'chaperone')
                                    <span class="size-2 rounded-full bg-amber-600"></span>
                                @endif
                            </div>
                            <p class="text-[10px] text-zinc-500 dark:text-zinc-400 mt-0.5 leading-tight">
                                Bindamping putra dan putri pangkalan Gudep
                            </p>
                        </div>
                    </div>

                    {{-- Panitia --}}
                    <div type="button" wire:click="$set('editSessionType', 'committee')"
                        class="cursor-pointer relative flex items-start gap-2.5 p-2.5 rounded-xl border transition {{ $editSessionType === 'committee' ? 'border-amber-600 bg-amber-50/60 dark:bg-amber-950/25 ring-2 ring-amber-500/20 shadow-xs' : 'border-zinc-200 bg-white hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900/60 dark:hover:border-zinc-700' }}">
                        <div
                            class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ $editSessionType === 'committee' ? 'bg-amber-600 text-white' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400' }}">
                            <flux:icon name="shield-check" class="size-4" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xs text-zinc-900 dark:text-zinc-100">Panitia</span>
                                @if ($editSessionType === 'committee')
                                    <span class="size-2 rounded-full bg-amber-600"></span>
                                @endif
                            </div>
                            <p class="text-[10px] text-zinc-500 dark:text-zinc-400 mt-0.5 leading-tight">
                                Aparatur panitia pelaksana operasional buper
                            </p>
                        </div>
                    </div>

                    {{-- Dewan Juri --}}
                    <div type="button" wire:click="$set('editSessionType', 'judge')"
                        class="cursor-pointer relative flex items-start gap-2.5 p-2.5 rounded-xl border transition {{ $editSessionType === 'judge' ? 'border-amber-600 bg-amber-50/60 dark:bg-amber-950/25 ring-2 ring-amber-500/20 shadow-xs' : 'border-zinc-200 bg-white hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900/60 dark:hover:border-zinc-700' }}">
                        <div
                            class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ $editSessionType === 'judge' ? 'bg-amber-600 text-white' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400' }}">
                            <flux:icon name="trophy" class="size-4" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xs text-zinc-900 dark:text-zinc-100">Dewan Juri</span>
                                @if ($editSessionType === 'judge')
                                    <span class="size-2 rounded-full bg-amber-600"></span>
                                @endif
                            </div>
                            <p class="text-[10px] text-zinc-500 dark:text-zinc-400 mt-0.5 leading-tight">
                                Aparatur dewan juri penilai cabang lomba LT III
                            </p>
                        </div>
                    </div>
                </div>
                @error('editSessionType')
                    <span class="text-xs text-rose-500 font-medium">{{ $message }}</span>
                @enderror
            </div>

            {{-- 3. Kaitan Jadwal Lomba (Pilihan / Koreksi Tautan Jadwal) --}}
            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-bold text-zinc-800 dark:text-zinc-200 uppercase tracking-wider">
                        Kaitan Jadwal Mata Lomba
                    </label>
                    <span class="text-[10px] text-zinc-400 font-medium">Ubah jika salah tautan jadwal</span>
                </div>

                <flux:select wire:model.live="editScheduleId" placeholder="Pilih Jadwal Lomba">
                    <flux:select.option value="">Tanpa Jadwal Lomba (Presensi Kehadiran Umum)
                    </flux:select.option>
                    @foreach ($this->availableSchedules as $sch)
                        <flux:select.option value="{{ $sch->id }}">
                            {{ $sch->competition?->code ?? '-' }} — {{ $sch->session_name }}
                            ({{ $sch->arena_location }})
                            &bull; {{ $sch->genderLabel() }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
                @error('editScheduleId')
                    <span class="text-xs text-rose-500 font-medium">{{ $message }}</span>
                @enderror
            </div>

            {{-- 4. Blok Penyesuaian Jadwal Lomba (jika terhubung jadwal lomba) --}}
            @if ($editScheduleId)
                <div
                    class="rounded-2xl border border-amber-200/90 bg-amber-50/40 p-4 space-y-3 dark:border-amber-900/50 dark:bg-amber-950/20">
                    <div class="flex items-center gap-2">
                        <flux:icon name="calendar" class="size-4 text-amber-700 dark:text-amber-400" />
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-900 dark:text-amber-200">
                            Penyesuaian Jadwal Lomba Terkait
                        </span>
                        <span class="text-[10px] font-semibold text-amber-700/80 dark:text-amber-300 ml-auto">
                            Koreksi Kesalahan Jadwal
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <div>
                            <flux:input wire:model="editScheduleName" label="Nama Sesi Jadwal *"
                                placeholder="Contoh: Babak Penyisihan Regu Putra" />
                            @error('editScheduleName')
                                <span class="text-xs text-rose-500 font-medium">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <flux:input wire:model="editScheduleArena" label="Lokasi Arena / Pos *"
                                placeholder="Contoh: Pos 1 — Lapangan Utama" />
                            @error('editScheduleArena')
                                <span class="text-xs text-rose-500 font-medium">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <flux:input type="datetime-local" wire:model="editScheduleStartTime"
                                label="Waktu Mulai Lomba *" />
                            @error('editScheduleStartTime')
                                <span class="text-xs text-rose-500 font-medium">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <flux:input type="datetime-local" wire:model="editScheduleEndTime"
                                label="Waktu Selesai Lomba *" />
                            @error('editScheduleEndTime')
                                <span class="text-xs text-rose-500 font-medium">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <flux:select wire:model="editScheduleGenderCategory" label="Kategori Peserta / Regu *">
                                <flux:select.option value="all">Putra & Putri (Terbuka)</flux:select.option>
                                <flux:select.option value="boys">Khusus Putra</flux:select.option>
                                <flux:select.option value="girls">Khusus Putri</flux:select.option>
                            </flux:select>
                            @error('editScheduleGenderCategory')
                                <span class="text-xs text-rose-500 font-medium">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <flux:select wire:model="editScheduleStatus" label="Status Sesi Jadwal">
                                <flux:select.option value="scheduled">Dijadwalkan</flux:select.option>
                                <flux:select.option value="in_progress">Sedang Berlangsung</flux:select.option>
                                <flux:select.option value="completed">Selesai</flux:select.option>
                                <flux:select.option value="rescheduled">Ditunda</flux:select.option>
                                <flux:select.option value="cancelled">Dibatalkan</flux:select.option>
                            </flux:select>
                            @error('editScheduleStatus')
                                <span class="text-xs text-rose-500 font-medium">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <p class="text-[11px] text-amber-800 dark:text-amber-300/80 leading-tight">
                        Mengubah waktu mulai/selesai atau kategori regu di atas akan langsung memperbarui jadwal lomba
                        bersangkutan dan aturan scan QR.
                    </p>
                </div>
            @endif

            {{-- 5. Rentang Waktu Buka & Tutup Presensi --}}
            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-bold text-zinc-800 dark:text-zinc-200 uppercase tracking-wider">
                        Override Waktu Buka & Tutup Presensi
                    </label>
                    <span class="text-[10px] text-zinc-400 font-medium">Opsional</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <flux:input type="datetime-local" wire:model="editOpenedAt" label="Waktu Buka Presensi" />
                        <span class="block text-[10px] text-zinc-400 mt-0.5">
                            Kosongkan untuk otomatis mengikuti jadwal (H-30m sebelum lomba).
                        </span>
                        @error('editOpenedAt')
                            <span class="text-xs text-rose-500 font-medium">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <flux:input type="datetime-local" wire:model="editClosedAt" label="Waktu Tutup Presensi" />
                        <span class="block text-[10px] text-zinc-400 mt-0.5">
                            Kosongkan untuk otomatis tutup saat lomba selesai.
                        </span>
                        @error('editClosedAt')
                            <span class="text-xs text-rose-500 font-medium">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- 6. Status Aktivasi Sesi --}}
            <div
                class="rounded-xl border border-zinc-200 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-800/50 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ $editIsActive ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400' : 'bg-zinc-100 text-zinc-400 dark:bg-zinc-800' }}">
                        <flux:icon name="{{ $editIsActive ? 'bolt' : 'pause' }}" class="size-4" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Status Aktivasi
                                Sesi</span>
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $editIsActive ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' }}">
                                {{ $editIsActive ? 'Aktif Scan' : 'Nonaktif / Dikunci' }}
                            </span>
                        </div>
                        <p class="text-[10px] text-zinc-500 dark:text-zinc-400 mt-0.5">
                            {{ $editIsActive ? 'Sesi aktif dan siap menerima presensi scanner QR pos.' : 'Sesi ditutup / dikunci dari pemindaian.' }}
                        </p>
                    </div>
                </div>
                <flux:switch wire:model.live="editIsActive" />
            </div>

            {{-- Modal Footer --}}
            <div class="flex items-center justify-between gap-3 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                <flux:button type="button" variant="subtle" icon="trash"
                    wire:click="deleteAttendanceSession('{{ $editingSessionId }}')"
                    wire:confirm="Yakin ingin menghapus sesi presensi ini? Seluruh rekaman presensi pada sesi ini akan ikut terhapus."
                    class="text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/40 cursor-pointer">
                    Hapus Sesi
                </flux:button>

                <div class="flex items-center gap-2">
                    <flux:button type="button" variant="subtle" wire:click="$set('showEditModal', false)">
                        Batal
                    </flux:button>
                    <flux:button type="submit" variant="primary" icon="check"
                        class="bg-amber-600 hover:bg-amber-700 font-semibold shadow-xs">
                        <span>Simpan Perubahan</span>
                    </flux:button>
                </div>
            </div>
        </form>
    </flux:modal>

    {{-- Modal Visual Log Riwayat Presensi Interaktif --}}
    <flux:modal wire:model="showLogsModal" class="w-full max-w-4xl max-h-[92vh] overflow-hidden flex flex-col p-0">
        @if($this->selectedLogsSession)
            @php
                $selSession = $this->selectedLogsSession;
                $allLogs = $this->sessionLogs;
                $totalCount = $allLogs->count();
                $presentCount = $allLogs->where('status', 'present')->count();
                $lateCount = $allLogs->where('status', 'late')->count();
                $excusedCount = $allLogs->where('status', 'excused')->count();
            @endphp

            <div class="flex flex-col h-full max-h-[92vh]">
                {{-- Header Modal --}}
                <div class="p-5 border-b border-zinc-200/80 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3.5 min-w-0">
                            <div class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 text-white shadow-md shadow-blue-500/20 mt-0.5">
                                <flux:icon name="clock" class="size-5.5" />
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="inline-flex items-center gap-1 rounded-md bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800">
                                        {{ $selSession->sessionTypeLabel() }}
                                    </span>

                                    @if($selSession->isCompetition())
                                        <span class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800">
                                            Lomba Pos
                                        </span>

                                        @if($selSession->schedule)
                                            <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[10px] font-semibold border {{ $selSession->schedule->genderBadgeClasses() }}">
                                                {{ $selSession->schedule->genderLabel() }}
                                            </span>
                                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-semibold border {{ $selSession->schedule->participantTypeBadgeClasses() }}">
                                                {{ $selSession->schedule->participantTypeLabel() }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800">
                                            Kehadiran Umum
                                        </span>
                                    @endif

                                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold border {{ $selSession->scheduleStatusBadgeClasses() }}">
                                        {{ $selSession->scheduleStatusLabel() }}
                                    </span>
                                </div>

                                <h2 class="text-base sm:text-lg font-extrabold tracking-tight text-zinc-900 dark:text-zinc-100 mt-1 truncate" title="{{ $selSession->title }}">
                                    Log Presensi: {{ $selSession->title }}
                                </h2>

                                <div class="flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400 mt-0.5 flex-wrap">
                                    <span class="flex items-center gap-1">
                                        <flux:icon name="map-pin" class="size-3.5 text-zinc-400" />
                                        {{ $selSession->schedule?->arena_location ?: 'Pos Lapangan' }}
                                    </span>
                                    @if($selSession->schedule?->competition)
                                        <span>&bull;</span>
                                        <span class="font-semibold text-amber-600 dark:text-amber-400">
                                            {{ $selSession->schedule->competition->code }} — {{ $selSession->schedule->competition->title }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <flux:button 
                                href="{{ route('attendance.scanner', $selSession) }}" 
                                variant="primary"
                                size="sm"
                                icon="qr-code"
                                class="bg-emerald-600 hover:bg-emerald-500 text-white shadow-xs"
                                title="Buka Kamera Scanner Sesi Ini" wire:navigate>
                                <span class="hidden sm:inline">Buka Scanner</span>
                            </flux:button>
                            <flux:button type="button" variant="subtle" size="sm" icon="x-mark" wire:click="$set('showLogsModal', false)" />
                        </div>
                    </div>

                    {{-- Metrics Summary Bar --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 mt-4">
                        <x-stat-card
                            label="Total Tercatat"
                            value="{{ $totalCount }}"
                            unit="orang/regu"
                            color="neutral"
                        />

                        <x-stat-card
                            label="Tepat Waktu"
                            value="{{ $presentCount }}"
                            unit="hadir"
                            color="emerald"
                        />

                        <x-stat-card
                            label="Terlambat"
                            value="{{ $lateCount }}"
                            unit="tercatat"
                            color="amber"
                        />

                        <x-stat-card
                            label="Izin / Dispensasi"
                            value="{{ $excusedCount }}"
                            unit="khusus"
                            color="blue"
                        />
                    </div>
                </div>

                {{-- Toolbar Filter & Pencarian Log --}}
                <div class="px-5 py-3 border-b border-zinc-100 dark:border-zinc-800 bg-white dark:bg-zinc-900 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="w-full sm:max-w-xs">
                        <flux:input 
                            wire:model.live.debounce.300ms="logsSearch" 
                            size="sm"
                            icon="magnifying-glass" 
                            placeholder="Cari nama, regu, pangkalan, atau ID..." 
                            clearable 
                        />
                    </div>

                    <div class="flex items-center gap-1.5 overflow-x-auto w-full sm:w-auto">
                        <button 
                            type="button" 
                            wire:click="$set('logsStatusFilter', 'all')" 
                            class="rounded-lg px-2.5 py-1 text-xs font-semibold transition cursor-pointer {{ $logsStatusFilter === 'all' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300' }}"
                        >
                            Semua ({{ $selSession->attendances->count() }})
                        </button>
                        <button 
                            type="button" 
                            wire:click="$set('logsStatusFilter', 'present')" 
                            class="rounded-lg px-2.5 py-1 text-xs font-semibold transition cursor-pointer {{ $logsStatusFilter === 'present' ? 'bg-emerald-600 text-white' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300' }}"
                        >
                            Hadir ({{ $selSession->attendances->where('status', 'present')->count() }})
                        </button>
                        <button 
                            type="button" 
                            wire:click="$set('logsStatusFilter', 'late')" 
                            class="rounded-lg px-2.5 py-1 text-xs font-semibold transition cursor-pointer {{ $logsStatusFilter === 'late' ? 'bg-amber-600 text-white' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300' }}"
                        >
                            Terlambat ({{ $selSession->attendances->where('status', 'late')->count() }})
                        </button>
                        <button 
                            type="button" 
                            wire:click="$set('logsStatusFilter', 'excused')" 
                            class="rounded-lg px-2.5 py-1 text-xs font-semibold transition cursor-pointer {{ $logsStatusFilter === 'excused' ? 'bg-blue-600 text-white' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300' }}"
                        >
                            Izin ({{ $selSession->attendances->where('status', 'excused')->count() }})
                        </button>
                    </div>
                </div>

                {{-- Konten Utama: Daftar Log Visual --}}
                <div class="flex-1 overflow-y-auto p-5 space-y-3 bg-zinc-50/40 dark:bg-zinc-950/40">
                    @forelse($this->sessionLogs as $att)
                        @php
                            $isParticipant = $att->entity_type === 'participant';
                            $isMale = $att->participant?->gender === 'male' || $att->chaperone?->gender === 'male';
                            $avatarBorder = $isMale ? 'border-blue-300 dark:border-blue-700 text-blue-600 bg-blue-50 dark:bg-blue-950/40' : 'border-pink-300 dark:border-pink-700 text-pink-600 bg-pink-50 dark:bg-pink-950/40';
                        @endphp
                        <div class="rounded-2xl border border-zinc-200/80 bg-white p-3.5 shadow-xs transition hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                {{-- Entitas Biodata & Avatar --}}
                                <div class="flex items-start gap-3 min-w-0">
                                    @if($att->entity_photo_url)
                                        <img 
                                            src="{{ $att->entity_photo_url }}" 
                                            alt="{{ $att->entity_name }}" 
                                            class="size-11 rounded-xl object-cover border border-zinc-200 dark:border-zinc-700 shrink-0 shadow-xs" 
                                        />
                                    @else
                                        <div class="flex size-11 shrink-0 items-center justify-center rounded-xl border {{ $avatarBorder }} shadow-xs font-bold text-xs uppercase">
                                            @if($isParticipant)
                                                <flux:icon name="academic-cap" class="size-5" />
                                            @elseif($att->entity_type === 'chaperone')
                                                <flux:icon name="user-group" class="size-5" />
                                            @elseif($att->entity_type === 'user')
                                                <flux:icon name="shield-check" class="size-5" />
                                            @else
                                                <flux:icon name="user" class="size-5" />
                                            @endif
                                        </div>
                                    @endif

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-extrabold text-sm text-zinc-900 dark:text-white truncate">
                                                {{ $att->entity_name }}
                                            </span>
                                            <span class="rounded bg-zinc-100 px-1.5 py-0.5 text-[11px] font-mono font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                                {{ $att->entity_id_number }}
                                            </span>
                                            <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-[10px] font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                                {{ $att->entity_role }}
                                            </span>
                                        </div>

                                        <div class="mt-0.5 flex items-center gap-1.5 text-xs text-zinc-600 dark:text-zinc-400 truncate">
                                            <flux:icon name="building-office-2" class="size-3.5 text-zinc-400 shrink-0" />
                                            <span class="truncate">{{ $att->entity_group }}</span>
                                        </div>

                                        <div class="mt-1 flex items-center gap-3 text-[11px] text-zinc-500 dark:text-zinc-400 flex-wrap">
                                            <span class="flex items-center gap-1 font-mono">
                                                <flux:icon name="clock" class="size-3 text-zinc-400" />
                                                {{ $att->scanned_at?->format('H:i:s') }} WIB
                                                <span class="text-zinc-400">({{ $att->scanned_at?->diffForHumans() }})</span>
                                            </span>
                                            <span>&bull;</span>
                                            <span>Petugas: <strong class="text-zinc-700 dark:text-zinc-300">{{ $att->scannedBy?->name ?: 'Sistem' }}</strong></span>
                                        </div>

                                        @if($att->notes)
                                            <div class="mt-2 rounded-lg bg-zinc-50 border border-zinc-200/60 p-2 text-xs text-zinc-600 dark:bg-zinc-800/50 dark:border-zinc-800 dark:text-zinc-300">
                                                <strong class="text-zinc-800 dark:text-zinc-200">Catatan:</strong> {{ $att->notes }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Status Badge & Quick Actions --}}
                                <div class="flex items-center gap-2 self-start sm:self-center shrink-0">
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-bold border {{ $att->statusBadgeClasses() }}">
                                        {{ $att->statusLabel() }}
                                    </span>

                                    {{-- Status Changer Buttons --}}
                                    <div class="flex items-center gap-1">
                                        @if($att->status !== 'present')
                                            <flux:button 
                                                type="button" 
                                                size="xs"
                                                variant="subtle"
                                                icon="check"
                                                wire:click="updateAttendanceStatus('{{ $att->id }}', 'present')" 
                                                class="text-emerald-700 bg-emerald-50 hover:bg-emerald-100 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300"
                                                title="Tandai Hadir Tepat Waktu"
                                            >
                                                Hadir
                                            </flux:button>
                                        @endif

                                        @if($att->status !== 'late')
                                            <flux:button 
                                                type="button" 
                                                size="xs"
                                                variant="subtle"
                                                icon="clock"
                                                wire:click="updateAttendanceStatus('{{ $att->id }}', 'late')" 
                                                class="text-amber-700 bg-amber-50 hover:bg-amber-100 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-300"
                                                title="Tandai Terlambat"
                                            >
                                                Terlambat
                                            </flux:button>
                                        @endif

                                        @if($att->status !== 'excused')
                                            <flux:button 
                                                type="button" 
                                                size="xs"
                                                variant="subtle"
                                                icon="document-text"
                                                wire:click="updateAttendanceStatus('{{ $att->id }}', 'excused')" 
                                                class="text-blue-700 bg-blue-50 hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-950/40 dark:text-blue-300"
                                                title="Tandai Izin"
                                            >
                                                Izin
                                            </flux:button>
                                        @endif

                                        @if(auth()->user()?->isAdmin())
                                            <flux:button 
                                                type="button" 
                                                variant="subtle" 
                                                size="sm" 
                                                icon="trash" 
                                                wire:click="deleteAttendanceRecord('{{ $att->id }}')" 
                                                wire:confirm="Hapus rekaman presensi ini?" 
                                                class="text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/40"
                                                title="Hapus Rekaman Presensi" 
                                            />
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center text-zinc-500">
                            <flux:icon name="clock" class="size-12 mx-auto text-zinc-300 dark:text-zinc-700 mb-2" />
                            <div class="font-bold text-sm text-zinc-800 dark:text-zinc-200">
                                Belum Ada Rekaman Presensi
                            </div>
                            <p class="text-xs text-zinc-500 mt-1 max-w-sm mx-auto">
                                @if($logsSearch || $logsStatusFilter !== 'all')
                                    Tidak ada data presensi yang sesuai dengan kata kunci pencarian atau filter status ini.
                                @else
                                    Belum ada pemindaian QR atau kehadiran tercatat pada sesi ini. Buka scanner kamera untuk mencatat presensi.
                                @endif
                            </p>
                        </div>
                    @endforelse
                </div>

                {{-- Footer Modal --}}
                <div class="p-4 border-t border-zinc-200/80 dark:border-zinc-800 bg-white dark:bg-zinc-900 flex items-center justify-between gap-3">
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                        Menampilkan <strong class="text-zinc-800 dark:text-zinc-200">{{ $this->sessionLogs->count() }}</strong> catatan presensi
                    </div>

                    <div class="flex items-center gap-2">
                        <a 
                            href="{{ route('attendance.recap') }}" 
                            class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 transition shadow-xs"
                            title="Buka Rekapitulasi Presensi" wire:navigate>
                            <flux:icon name="document-text" class="size-3.5" />
                            <span>Rekapitulasi</span>
                        </a>

                        <flux:button type="button" variant="subtle" wire:click="$set('showLogsModal', false)">
                            Tutup
                        </flux:button>
                    </div>
                </div>
            </div>
        @endif
    </flux:modal>
</div>

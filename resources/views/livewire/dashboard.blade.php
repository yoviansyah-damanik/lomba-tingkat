<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Header Identitas Resmi --}}
    <x-official-header />

    {{-- Info Banner Pengguna & Peran --}}
    <div class="flex flex-col justify-between gap-4 rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 md:flex-row md:items-center">
        <div class="flex items-center gap-4">
            <div class="flex size-12 items-center justify-center rounded-2xl bg-amber-500/10 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400 font-bold text-lg">
                {{ $user->initials() }}
            </div>
            <div>
                <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 sm:text-lg">
                    Selamat Datang, {{ $user->name }}
                </h2>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 flex flex-wrap items-center gap-1.5 mt-0.5">
                    <span>Masuk sebagai:</span>
                    <span class="font-semibold text-amber-700 dark:text-amber-400">
                        {{ $user->roles->pluck('label')->join(', ') ?: 'Pengguna Umum' }}
                    </span>
                    @if($user->isCommitteeAndJudge())
                        <span class="ml-1 inline-flex items-center rounded-md bg-purple-100 px-2 py-0.5 text-[10px] font-semibold text-purple-800 dark:bg-purple-950/60 dark:text-purple-300">
                            Rangkap Panitia & Juri
                        </span>
                    @endif
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <div class="flex items-center gap-2 rounded-xl border border-zinc-200 bg-zinc-50 px-3.5 py-2 text-xs text-zinc-600 dark:border-zinc-800 dark:bg-zinc-800/60 dark:text-zinc-300">
                <flux:icon name="clock" class="size-4 text-amber-600 dark:text-amber-400" />
                <span>Waktu Server Resmi:</span>
                <span class="font-mono font-bold text-zinc-900 dark:text-zinc-100">
                    {{ now()->setTimezone('Asia/Jakarta')->locale('id')->isoFormat('dddd, D MMMM Y • HH:mm') }} WIB
                </span>
            </div>
            <livewire:notifications.bell lazy />
            <x-theme-toggle :show-label="true" class="hidden sm:inline-flex px-3 py-2 text-xs" />
        </div>
    </div>

    {{-- Tampilan Ringkasan Berdasarkan Peran Pengguna --}}
    @if($user->isAdmin())
        {{-- Statistik Administrator / Superadmin --}}
        <section class="space-y-3" aria-label="Statistik penyelenggaraan">
            <h3 class="text-[11px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Pendaftaran Kontingen</h3>
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <x-stat-card label="Gudep Terdaftar" :value="$adminStats['total_groups']" unit="Pangkalan" description="Seluruh status pendaftaran" color="indigo" icon="building-library" action-text="Direktori Pangkalan" href="{{ route('admin.gudep-directory') }}" />
                <x-stat-card label="Menunggu Verifikasi" :value="$adminStats['pending_count']" unit="Gudep" action-text="Buka Antrean" href="{{ route('admin.scout-groups.index') }}" color="amber" icon="shield-check" />
                <x-stat-card label="Terverifikasi" :value="$adminStats['verified_groups']" unit="Gudep" description="Kontingen sah" color="emerald" icon="check-badge" />
                <x-stat-card label="Perlu Revisi / Ditolak" :value="$adminStats['revision_groups']" unit="Gudep" description="Menunggu perbaikan pangkalan" color="orange" icon="exclamation-triangle" />
            </div>

            <h3 class="pt-2 text-[11px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Peserta & Pendamping</h3>
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <x-stat-card label="Regu Terdaftar" :value="$adminStats['total_patrols']" unit="Regu" :description="$adminStats['boys_patrols'].' putra / '.$adminStats['girls_patrols'].' putri'" color="emerald" icon="user-group" />
                <x-stat-card label="Peserta Penggalang" :value="$adminStats['total_participants']" unit="Orang" :description="$adminStats['boys_participants'].' putra / '.$adminStats['girls_participants'].' putri'" action-text="Direktori Peserta" href="{{ route('admin.participants.index') }}" color="indigo" icon="identification" />
                <x-stat-card label="Pembina Pendamping" :value="$adminStats['total_chaperones']" unit="Orang" :description="$adminStats['male_chaperones'].' putra / '.$adminStats['female_chaperones'].' putri'" action-text="Direktori Bindamping" href="{{ route('admin.chaperones.index') }}" color="blue" icon="user-circle" />
                <x-stat-card label="Aparatur" :value="$adminStats['officials_count']" unit="Panitia & Juri" :description="$adminStats['committee_count'].' panitia / '.$adminStats['judge_count'].' juri'" action-text="Kelola Aparatur" href="{{ route('admin.officials.index') }}" color="purple" icon="user-group" />
            </div>

            <h3 class="pt-2 text-[11px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Perlombaan & Presensi</h3>
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <x-stat-card label="Sesi Lomba" :value="$adminStats['total_schedules']" unit="Sesi" :description="$adminStats['completed_schedules'].' selesai · '.$adminStats['live_schedules'].' berlangsung'" action-text="Jadwal & Arena" href="{{ route('admin.schedules.index') }}" color="amber" icon="calendar" />
                <x-stat-card label="Mata Lomba" :value="$adminStats['total_competitions']" unit="Cabang" action-text="Mata Lomba & Rubrik" href="{{ route('admin.competitions.index') }}" color="purple" icon="trophy" />
                <x-stat-card label="Presensi Hari Ini" :value="$adminStats['attendances_today']" unit="Pemindaian" :description="$adminStats['active_sessions'].' sesi aktif'" action-text="Portal Presensi" href="{{ route('attendance.index') }}" color="emerald" icon="qr-code" />
                <x-stat-card label="Izin Belum Kembali" :value="$adminStats['outstanding_permissions']" unit="Izin sementara" action-text="Permohonan Izin" href="{{ route('admin.permission-requests.index') }}" color="rose" icon="clipboard-document-check" />
            </div>
        </section>

    @elseif($user->isCommittee())
        {{-- Statistik Panitia (monitor operasional lapangan) --}}
        <section class="space-y-3" aria-label="Statistik operasional">
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <x-stat-card label="Sesi Presensi Aktif" :value="$committeeStats['active_sessions']" unit="Sesi" :description="'Dari '.$committeeStats['total_sessions'].' sesi yang dapat diakses'" action-text="Buka Pemindai" href="{{ route('attendance.index') }}" color="emerald" icon="qr-code" />
                <x-stat-card label="Presensi Hari Ini" :value="$committeeStats['attendances_today']" unit="Pemindaian" description="Seluruh sesi yang Anda akses" color="amber" icon="clipboard-document-list" />
                <x-stat-card label="Sesi Lomba Hari Ini" :value="$committeeStats['schedules_today']" unit="Sesi" :description="$committeeStats['live_schedules'].' sedang berlangsung'" action-text="Lihat Jadwal" href="{{ route('agenda.index') }}" color="indigo" icon="calendar-days" />
                <x-stat-card label="Regu Sah" :value="$committeeStats['total_patrols_verified']" unit="Regu terverifikasi" description="Kontingen resmi tersahkan" color="emerald" icon="user-group" />
                <x-stat-card label="Peserta Terverifikasi" :value="$committeeStats['verified_participants']" unit="Orang" action-text="Direktori Peserta" href="{{ route('admin.participants.index') }}" color="blue" icon="academic-cap" />
                <x-stat-card label="Pembina Terverifikasi" :value="$committeeStats['verified_chaperones']" unit="Orang" action-text="Direktori Bindamping" href="{{ route('admin.chaperones.index') }}" color="purple" icon="user-circle" />
                <x-stat-card label="Verifikasi Identitas" value="Pindai QR" description="Periksa kartu peserta, bindamping, panitia, dan juri" action-text="Buka Verifikasi" href="{{ route('admin.identity-verification.index') }}" color="amber" icon="viewfinder-circle" />
                <x-stat-card label="Rekap Presensi" value="Laporan Kehadiran" description="Rekap kehadiran per sesi" action-text="Buka Rekap" href="{{ route('attendance.recap') }}" color="indigo" icon="clipboard-document-check" />
            </div>
        </section>

    @elseif($user->isJudge())
        {{-- Statistik Dewan Juri --}}
        <section class="space-y-3" aria-label="Statistik penjurian">
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <x-stat-card label="Sesi Ditugaskan" :value="$judgeStats['assigned_sessions']" unit="Sesi" :description="$judgeStats['completed_sessions'].' sesi selesai'" action-text="Buka Cockpit Penjurian" href="{{ route('judge.index') }}" color="indigo" icon="clock" />
                <x-stat-card label="Lomba Akan Datang" :value="$judgeStats['upcoming_sessions']" unit="Sesi" description="Belum selesai" color="amber" icon="calendar-days" />
                <x-stat-card label="Sesi Hari Ini" :value="$judgeStats['sessions_today']" unit="Sesi" description="Jadwal penjurian Anda hari ini" color="emerald" icon="calendar" />
                <x-stat-card label="Nilai Final Saya" :value="$judgeStats['finalized_scores']" unit="Penilaian" :description="$judgeStats['draft_scores'].' masih draf'" color="emerald" icon="clipboard-document-check" />
                <x-stat-card label="Rubrik Juknis Lomba" :value="$judgeStats['total_competitions']" unit="Mata Lomba" action-text="Pelajari Standar Nilai" href="{{ route('competitions') }}" color="amber" icon="document-text" />
                <x-stat-card label="Presensi Pos Aktif" :value="$judgeStats['active_attendance_sessions']" unit="Sesi" action-text="Portal Presensi" href="{{ route('attendance.index') }}" color="blue" icon="qr-code" />
                <x-stat-card label="Sistem Penilaian" value="Blind Scoring" description="Nomor undian acak menggantikan identitas regu" color="indigo" icon="shield-check" />
                <x-stat-card label="Kartu Digital" value="ID Card Juri" description="Tunjukkan QR Code saat bertugas" action-text="Buka Kartu" href="{{ route('digital-card.index') }}" color="purple" icon="qr-code" />
            </div>
        </section>

    @elseif($user->isScoutGroup())
        @php
            $gs = $groupStats;
        @endphp

        {{-- Revision Notes Alert if any --}}
        @if($scoutGroup && $scoutGroup->verification_status === \App\Enums\RegistrationStatusEnum::NeedsRevision && $scoutGroup->revision_notes)
            <div class="rounded-2xl border border-orange-300 bg-orange-50 p-4 text-xs text-orange-900 dark:border-orange-500/30 dark:bg-orange-950/40 dark:text-orange-200">
                <strong class="font-bold flex items-center gap-1.5 text-sm">
                    <flux:icon name="exclamation-triangle" class="size-4 text-orange-600" />
                    Catatan Perbaikan dari Administrator:
                </strong>
                <p class="mt-1.5 leading-relaxed pl-5.5">{{ $scoutGroup->revision_notes }}</p>
                <div class="mt-3 pl-5.5">
                    <flux:button href="{{ route('scout-group.index') }}" wire:navigate size="xs" variant="primary" icon-trailing="arrow-right">Perbaiki Data Sekarang</flux:button>
                </div>
            </div>
        @endif

        {{-- Statistik Gugus Depan --}}
        <section class="space-y-3" aria-label="Statistik kontingen">
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <x-stat-card label="Status Berkas" action-text="Buka Portal Pangkalan" href="{{ route('scout-group.index') }}" color="amber" icon="identification">
                    <div class="mt-2">
                        <span class="inline-flex items-center rounded-lg border px-2.5 py-0.5 text-xs font-bold {{ $scoutGroup?->verification_status?->badgeClasses() ?? 'bg-zinc-100 text-zinc-700 border-zinc-200' }}">
                            {{ $scoutGroup?->verification_status?->label() ?? 'Belum Terdaftar' }}
                        </span>
                    </div>
                </x-stat-card>
                <x-stat-card label="Regu Terdaftar" :value="$gs['patrols'] ?? 0" unit="Regu" :description="($gs['boys_patrols'] ?? 0).' putra / '.($gs['girls_patrols'] ?? 0).' putri'" action-text="Kelola Regu" href="{{ route('scout-group.patrols') }}" color="emerald" icon="user-group" />
                <x-stat-card label="Regu Lengkap" :value="($gs['full_patrols'] ?? 0).' / '.($gs['patrols'] ?? 0)" unit="Regu" description="8 peserta + Pinru & Wapinru" color="indigo" icon="check-circle" />
                <x-stat-card label="Anggota Peserta" :value="($gs['participants'] ?? 0).' / '.($gs['participants_target'] ?? 0)" unit="Peserta" description="Terisi dari target 8 per regu" color="blue" icon="user" />
                <x-stat-card label="Berkas Peserta Lengkap" :value="($gs['docs_complete'] ?? 0).' / '.($gs['participants'] ?? 0)" unit="Peserta" description="Pas foto + 7 dokumen" color="purple" icon="document-check" />
                <x-stat-card label="Bindamping" :value="$gs['chaperones'] ?? 0" unit="Orang" action-text="Kelola Bindamping" href="{{ route('scout-group.chaperones') }}" color="blue" icon="user-circle" />
                @include('livewire.partials.ranking-cards', ['rankings' => $rankings])
                @include('livewire.partials.achievement-card', ['achievements' => $achievements])
                <x-stat-card label="Nilai Lomba" :value="$gs['scored_competitions'] ?? 0" unit="Lomba dinilai" description="Nilai final regu Anda" action-text="Lihat Penilaian Regu" href="{{ route('team-assessment.index') }}" color="amber" icon="trophy" />
                <x-stat-card label="Sesi Berikutnya" :value="isset($gs['next_schedule']) && $gs['next_schedule'] ? $gs['next_schedule']->start_time->timezone('Asia/Jakarta')->format('H:i') : '-'" :description="isset($gs['next_schedule']) && $gs['next_schedule'] ? $gs['next_schedule']->session_name : 'Belum ada sesi terjadwal'" action-text="Lihat Jadwal" href="{{ route('agenda.index') }}" color="emerald" icon="clock" />
            </div>
        </section>
    @else
        {{-- Panel Peserta & Bindamping --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @include('livewire.partials.ranking-cards', ['rankings' => $rankings])
            @include('livewire.partials.achievement-card', ['achievements' => $achievements])

            @if($user->isParticipant())
                <x-stat-card
                    label="Identitas Peserta Regu"
                    value="Kartu Digital QR Sah"
                    description="Tunjukkan QR Code saat presensi di pos lomba"
                    color="amber"
                    icon="qr-code"
                />
            @endif

            @if($user->isParticipant() || $user->isChaperone())
                <x-stat-card
                    label="Penilaian Regu"
                    value="Nilai & Pencapaian"
                    description="Pantau nilai akhir dan peringkat regu"
                    action-text="Lihat Penilaian Regu"
                    href="{{ route('team-assessment.index') }}"
                    color="indigo"
                    icon="clipboard-document-check"
                />
            @endif

            <x-stat-card
                label="Jadwal Kegiatan"
                value="LT III Kwarcab Padangsidimpuan"
                action-text="Lihat Jadwal Terdekat"
                href="{{ route('agenda.index') }}"
                color="emerald"
                icon="clock"
            />
        </div>
    @endif

    {{-- Informasi penting menurut peran: tindak lanjut Administrator, kemajuan pendaftaran Gugus Depan --}}
    @if($user->isAdmin())
        <livewire:home.admin-attention-panel lazy />
    @elseif($user->isScoutGroup())
        <livewire:home.group-progress-panel lazy />
    @endif

    {{-- Khusus Dewan Juri: lomba yang akan datang pada penugasannya --}}
    @if($user->isJudge())
        <livewire:home.judge-upcoming-panel lazy />
    @endif

    {{-- Agenda dan pengumuman terbaru untuk seluruh peran --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <livewire:home.agenda-panel lazy />
        <livewire:home.announcements-panel lazy />
    </div>

    {{-- Pintasan menu per grup fungsi (sama dengan sidebar) --}}
    <x-dashboard.shortcuts :groups="$menuGroups" />

    {{-- Seksi Informasi Kegiatan & Navigasi Cepat --}}
    <div class="rounded-2xl border border-zinc-200/80 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 sm:text-base">
            Informasi Resmi Penyelenggaraan LT III
        </h3>
        <p class="mt-2 text-xs sm:text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
            Sistem <strong>LT III Kwarcab Padangsidimpuan</strong> terintegrasi dengan validasi pendaftaran kontingen terpusat, pengacakan identitas (*Blind Scoring*), serta pemantauan waktu resmi server WIB. Pastikan seluruh berkas telah disetujui sebelum sesi penimbangan dan presensi pos lomba dibuka.
        </p>
        <div class="mt-4 flex flex-wrap gap-4 pt-2 border-t border-zinc-100 dark:border-zinc-800">
            <a href="{{ route('competitions') }}" wire:navigate class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-700 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300">
                <flux:icon name="book-open" class="size-4" />
                Petunjuk Teknis (Juknis) Lomba &rarr;
            </a>
            <span class="text-zinc-300 dark:text-zinc-700">&bull;</span>
            <a href="{{ route('schedules') }}" wire:navigate class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-700 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300">
                <flux:icon name="calendar" class="size-4" />
                Jadwal Resmi & Arena &rarr;
            </a>
            <span class="text-zinc-300 dark:text-zinc-700">&bull;</span>
            <a href="{{ route('leaderboard') }}" wire:navigate class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-700 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300">
                <flux:icon name="trophy" class="size-4" />
                Klasemen Langsung &rarr;
            </a>
        </div>
    </div>
</div>

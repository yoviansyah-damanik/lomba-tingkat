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
        {{-- Panel Khusus Administrator --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-stat-card
                label="Verifikasi Berkas"
                :value="$adminStats['pending_count']"
                unit="Gudep Menunggu"
                action-text="Buka Antrean Verifikasi"
                href="{{ route('admin.scout-groups.index') }}"
                color="amber"
                icon="shield-check"
            />

            <x-stat-card
                label="Total Regu Terdaftar"
                :value="$adminStats['total_patrols']"
                unit="Regu Putra / Putri"
                :description="'Dari ' . $adminStats['total_groups'] . ' pangkalan Gudep'"
                color="emerald"
                icon="user-group"
            />

            <x-stat-card
                label="Peserta Penggalang"
                :value="$adminStats['total_participants']"
                unit="Orang Terdata"
                action-text="Buka Direktori Peserta"
                href="{{ route('admin.participants.index') }}"
                color="indigo"
                icon="identification"
            />

            <x-stat-card
                label="Sesi Jadwal"
                :value="$adminStats['total_schedules']"
                unit="Sesi Terjadwal"
                action-text="Lihat Jadwal & Arena"
                href="{{ route('admin.schedules.index') }}"
                color="amber"
                icon="calendar"
            />
        </div>

        {{-- Quick Navigation Actions for Admin --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <a href="{{ route('admin.scout-groups.index') }}" wire:navigate class="flex items-center gap-3.5 rounded-2xl border border-amber-200 bg-amber-50/60 p-4 transition hover:bg-amber-100/60 dark:border-amber-500/20 dark:bg-amber-950/30">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-600 text-white font-bold">
                    <flux:icon name="shield-check" class="size-5" />
                </div>
                <div>
                    <h4 class="text-xs font-bold text-amber-950 dark:text-amber-200">Verifikasi Berkas</h4>
                    <p class="text-[11px] text-amber-800/80 dark:text-amber-300/80">Antrean pendaftaran kontingen</p>
                </div>
            </a>

            @if(auth()->user()->isSuperadmin())
            <a href="{{ route('admin.officials.index') }}" wire:navigate class="flex items-center gap-3.5 rounded-2xl border border-zinc-200 bg-white p-4 transition hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                    <flux:icon name="user-group" class="size-5" />
                </div>
                <div>
                    <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Aparatur Panitia & Juri</h4>
                    <p class="text-[11px] text-zinc-500">Kelola aparatur & kartu B2</p>
                </div>
            </a>
            @endif

            <a href="{{ route('admin.competitions.index') }}" wire:navigate class="flex items-center gap-3.5 rounded-2xl border border-zinc-200 bg-white p-4 transition hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                    <flux:icon name="trophy" class="size-5" />
                </div>
                <div>
                    <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Mata Lomba & Rubrik</h4>
                    <p class="text-[11px] text-zinc-500">Format lomba & kriteria penilaian</p>
                </div>
            </a>

            <a href="{{ route('attendance.index') }}" wire:navigate class="flex items-center gap-3.5 rounded-2xl border border-emerald-200 bg-emerald-50/60 p-4 transition hover:bg-emerald-100/60 dark:border-emerald-500/20 dark:bg-emerald-950/30">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white font-bold">
                    <flux:icon name="qr-code" class="size-5" />
                </div>
                <div>
                    <h4 class="text-xs font-bold text-emerald-950 dark:text-emerald-200">Presensi & Scanner</h4>
                    <p class="text-[11px] text-emerald-800/80 dark:text-emerald-300/80">Monitor kehadiran pos arena</p>
                </div>
            </a>
        </div>

    @elseif($user->isCommittee())
        {{-- Panel Khusus Panitia (Monitor Operasional Lapangan) --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <x-stat-card
                label="Peran Operasional"
                value="Pemantauan Lapangan"
                description="Monitoring absensi pos & ketertiban lomba secara terpusat"
                color="amber"
                icon="eye"
            />

            <x-stat-card
                label="Regu Sah Siap Tanding"
                :value="$committeeStats['total_patrols_verified']"
                unit="Regu Terverifikasi"
                description="Kontingen resmi tersahkan Admin"
                color="emerald"
                icon="user-group"
            />

            <x-stat-card
                label="Scanner Kehadiran"
                :value="$committeeStats['active_sessions']"
                unit="Sesi Aktif Siap Scan"
                action-text="Buka Kamera Scanner"
                href="{{ route('attendance.index') }}"
                color="emerald"
                icon="qr-code"
            />
        </div>

        {{-- Quick Navigation Actions for Committee --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <a href="{{ route('admin.participants.index') }}" wire:navigate class="flex items-center gap-3.5 rounded-2xl border border-zinc-200 bg-white p-4 transition hover:border-amber-400 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300">
                    <flux:icon name="academic-cap" class="size-5" />
                </div>
                <div>
                    <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Direktori Peserta</h4>
                    <p class="text-[11px] text-zinc-500">Data & kartu digital peserta regu</p>
                </div>
            </a>

            <a href="{{ route('admin.chaperones.index') }}" wire:navigate class="flex items-center gap-3.5 rounded-2xl border border-zinc-200 bg-white p-4 transition hover:border-amber-400 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300">
                    <flux:icon name="user-circle" class="size-5" />
                </div>
                <div>
                    <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Pembina Pendamping</h4>
                    <p class="text-[11px] text-zinc-500">Data & kartu identitas pendamping</p>
                </div>
            </a>

            <a href="{{ route('admin.scout-groups.index') }}" wire:navigate class="flex items-center gap-3.5 rounded-2xl border border-zinc-200 bg-white p-4 transition hover:border-amber-400 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300">
                    <flux:icon name="shield-check" class="size-5" />
                </div>
                <div>
                    <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Pangkalan Gugusdepan</h4>
                    <p class="text-[11px] text-zinc-500">Daftar kontingen & berkas pendaftaran</p>
                </div>
            </a>
        </div>

    @elseif($user->isJudge())
        {{-- Panel Khusus Juri --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <x-stat-card
                label="Rubrik Juknis Lomba"
                :value="$judgeStats['total_competitions']"
                unit="Mata Lomba"
                action-text="Pelajari Standar Nilai"
                href="{{ route('competitions') }}"
                color="amber"
                icon="document-text"
            />

            <x-stat-card
                label="Jadwal Sesi Penjurian"
                :value="$judgeStats['assigned_sessions']"
                unit="Sesi Ditugaskan"
                action-text="Buka Cockpit Penjurian"
                href="{{ route('judge.index') }}"
                color="indigo"
                icon="clock"
            />

            <x-stat-card
                label="Sistem Penilaian"
                value="Blind Scoring Aktif"
                description="Identitas nomor undian acak menggantikan nama sekolah"
                color="emerald"
                icon="shield-check"
            />
        </div>

        {{-- Quick Navigation Actions for Judge --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <a href="{{ route('judge.index') }}" wire:navigate class="flex items-center gap-3.5 rounded-2xl border border-amber-200 bg-amber-50/60 p-4 transition hover:bg-amber-100/60 dark:border-amber-500/20 dark:bg-amber-950/30">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-600 text-white font-bold">
                    <flux:icon name="pencil-square" class="size-5" />
                </div>
                <div>
                    <h4 class="text-xs font-bold text-amber-950 dark:text-amber-200">Cockpit Penjurian</h4>
                    <p class="text-[11px] text-amber-800/80 dark:text-amber-300/80">Input nilai rubrik blind scoring</p>
                </div>
            </a>

            <a href="{{ route('admin.participants.index') }}" wire:navigate class="flex items-center gap-3.5 rounded-2xl border border-zinc-200 bg-white p-4 transition hover:border-amber-400 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300">
                    <flux:icon name="academic-cap" class="size-5" />
                </div>
                <div>
                    <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Direktori Peserta</h4>
                    <p class="text-[11px] text-zinc-500">Pengecekan data peserta regu</p>
                </div>
            </a>

            <a href="{{ route('admin.chaperones.index') }}" wire:navigate class="flex items-center gap-3.5 rounded-2xl border border-zinc-200 bg-white p-4 transition hover:border-amber-400 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300">
                    <flux:icon name="user-circle" class="size-5" />
                </div>
                <div>
                    <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Pembina Pendamping</h4>
                    <p class="text-[11px] text-zinc-500">Daftar pembina pendamping</p>
                </div>
            </a>

            <a href="{{ route('admin.scout-groups.index') }}" wire:navigate class="flex items-center gap-3.5 rounded-2xl border border-zinc-200 bg-white p-4 transition hover:border-amber-400 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300">
                    <flux:icon name="shield-check" class="size-5" />
                </div>
                <div>
                    <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Pangkalan Gugusdepan</h4>
                    <p class="text-[11px] text-zinc-500">Daftar kontingen pangkalan sekolah</p>
                </div>
            </a>
        </div>

    @elseif($user->isScoutGroup())
        @php
            $patrolsCount = $scoutGroup?->patrols?->count() ?? 0;
            $participantsCount = $scoutGroup ? $scoutGroup->patrols->sum(fn($p) => $p->participants->count()) : 0;
            $chaperonesCount = $scoutGroup?->chaperones?->count() ?? 0;
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
                    <a href="{{ route('scout-group.index') }}" wire:navigate class="inline-flex items-center rounded-lg bg-orange-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-orange-700">
                        Perbaiki Data Sekarang &rarr;
                    </a>
                </div>
            </div>
        @endif

        {{-- Panel Khusus Gugus Depan --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-stat-card
                label="Status Berkas"
                action-text="Buka Portal Pangkalan"
                href="{{ route('scout-group.index') }}"
                color="amber"
                icon="identification"
            >
                <div class="mt-2">
                    <span class="inline-flex items-center rounded-lg border px-2.5 py-0.5 text-xs font-bold {{ $scoutGroup?->verification_status?->badgeClasses() ?? 'bg-zinc-100 text-zinc-700' }}">
                        {{ $scoutGroup?->verification_status?->label() ?? 'Belum Terdaftar' }}
                    </span>
                </div>
            </x-stat-card>

            <x-stat-card
                label="Regu Terdaftar"
                :value="$patrolsCount"
                unit="Regu (Putra/Putri)"
                action-text="Kelola Regu"
                href="{{ route('scout-group.patrols') }}"
                color="emerald"
                icon="user-group"
            />

            <x-stat-card
                label="Anggota Peserta"
                :value="$participantsCount"
                unit="Peserta Penggalang"
                description="Pinru, Wapinru & Anggota"
                color="indigo"
                icon="user"
            />

            <x-stat-card
                label="Bindamping"
                :value="$chaperonesCount"
                unit="Orang Pembina"
                action-text="Kelola Bindamping"
                href="{{ route('scout-group.chaperones') }}"
                color="blue"
                icon="user-circle"
            />
        </div>

    @else
        {{-- Panel Peserta & Bindamping --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
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

<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Header Identitas Resmi --}}
        <x-official-header />

        {{-- Info Banner Pengguna & Peran --}}
        <div class="flex flex-col justify-between gap-4 rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 md:flex-row md:items-center">
            <div class="flex items-center gap-4">
                <div class="flex size-12 items-center justify-center rounded-2xl bg-amber-500/10 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400 font-bold text-lg">
                    {{ auth()->user()->initials() }}
                </div>
                <div>
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 sm:text-lg">
                        Selamat Datang, {{ auth()->user()->name }}
                    </h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 flex flex-wrap items-center gap-1.5 mt-0.5">
                        <span>Masuk sebagai:</span>
                        <span class="font-semibold text-amber-700 dark:text-amber-400">
                            {{ auth()->user()->roles->pluck('label')->join(', ') ?: 'Pengguna Umum' }}
                        </span>
                        @if(auth()->user()->isCommitteeAndJudge())
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
                <x-theme-toggle :show-label="true" class="hidden sm:inline-flex px-3 py-2 text-xs" />
            </div>
        </div>

        {{-- Tampilan Ringkasan Berdasarkan Peran Pengguna --}}
        @if(auth()->user()->isAdmin())
            @php
                $pendingCount = \App\Models\ScoutGroup::where('verification_status', \App\Enums\RegistrationStatusEnum::Submitted)->count();
                $totalGroups = \App\Models\ScoutGroup::count();
                $totalPatrols = \App\Models\Patrol::count();
                $totalParticipants = \App\Models\Participant::count();
                $totalSchedules = \App\Models\CompetitionSchedule::published()->count();
            @endphp
            {{-- Panel Khusus Administrator --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <x-stat-card
                    label="Verifikasi Berkas"
                    :value="$pendingCount"
                    unit="Gudep Menunggu"
                    action-text="Buka Antrean Verifikasi"
                    href="{{ route('admin.scout-groups.index') }}"
                    color="amber"
                    icon="shield-check"
                />

                <x-stat-card
                    label="Total Regu Terdaftar"
                    :value="$totalPatrols"
                    unit="Regu Putra / Putri"
                    :description="'Dari ' . $totalGroups . ' pangkalan Gudep'"
                    color="emerald"
                    icon="user-group"
                />

                <x-stat-card
                    label="Peserta Penggalang"
                    :value="$totalParticipants"
                    unit="Orang Terdata"
                    description="Pinru, Wapinru & Anggota"
                    color="indigo"
                    icon="identification"
                />

                <x-stat-card
                    label="Sesi Jadwal"
                    :value="$totalSchedules"
                    unit="Sesi Terjadwal"
                    action-text="Lihat Jadwal & Arena"
                    href="{{ route('schedules') }}"
                    color="amber"
                    icon="calendar"
                />
            </div>

            {{-- Quick Navigation Actions for Admin --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <a href="{{ route('admin.scout-groups.index') }}" class="flex items-center gap-3.5 rounded-2xl border border-amber-200 bg-amber-50/60 p-4 transition hover:bg-amber-100/60 dark:border-amber-500/20 dark:bg-amber-950/30">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-600 text-white font-bold">
                        <flux:icon name="shield-check" class="size-5" />
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-amber-950 dark:text-amber-200">Verifikasi Pendaftaran</h4>
                        <p class="text-[11px] text-amber-800/80 dark:text-amber-300/80">Periksa berkas regu & bindamping</p>
                    </div>
                </a>

                <a href="{{ route('competitions') }}" class="flex items-center gap-3.5 rounded-2xl border border-zinc-200 bg-white p-4 transition hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                        <flux:icon name="document-text" class="size-5" />
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Katalog Mata Lomba</h4>
                        <p class="text-[11px] text-zinc-500">Lihat juknis & interval peserta</p>
                    </div>
                </a>

                <a href="{{ route('schedules') }}" class="flex items-center gap-3.5 rounded-2xl border border-zinc-200 bg-white p-4 transition hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                        <flux:icon name="calendar-days" class="size-5" />
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Jadwal Sesi & Arena</h4>
                        <p class="text-[11px] text-zinc-500">Pengecekan waktu server Asia/Jakarta</p>
                    </div>
                </a>
            </div>

        @elseif(auth()->user()->isCommittee())
            @php
                $totalVerifiedPatrols = \App\Models\Patrol::where('status', \App\Enums\RegistrationStatusEnum::Verified)->count();
                $totalPublishedSchedules = \App\Models\CompetitionSchedule::published()->count();
            @endphp
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
                    :value="$totalVerifiedPatrols"
                    unit="Regu Terverifikasi"
                    description="Kontingen resmi tersahkan Admin"
                    color="emerald"
                    icon="user-group"
                />

                <x-stat-card
                    label="Jadwal Sesi Kegiatan"
                    :value="$totalPublishedSchedules"
                    unit="Sesi Terjadwal"
                    action-text="Pantau Arena Lomba"
                    href="{{ route('schedules') }}"
                    color="blue"
                    icon="calendar-days"
                />
            </div>

        @elseif(auth()->user()->isJudge())
            @php
                $totalCompetitions = \App\Models\Competition::published()->count();
                $totalSchedules = \App\Models\CompetitionSchedule::published()->count();
            @endphp
            {{-- Panel Khusus Juri --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <x-stat-card
                    label="Rubrik Juknis Lomba"
                    :value="$totalCompetitions"
                    unit="Mata Lomba"
                    action-text="Pelajari Standar Nilai"
                    href="{{ route('competitions') }}"
                    color="amber"
                    icon="document-text"
                />

                <x-stat-card
                    label="Jadwal Sesi Penjurian"
                    :value="$totalSchedules"
                    unit="Sesi Aktif"
                    action-text="Buka Jadwal & Presensi"
                    href="{{ route('schedules') }}"
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

        @elseif(auth()->user()->isScoutGroup())
            @php
                $scoutGroup = \App\Models\ScoutGroup::with(['patrols.participants', 'chaperones'])
                    ->where('user_id', auth()->id())
                    ->first();
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
                        <a href="{{ route('scout-group.index') }}" class="inline-flex items-center rounded-lg bg-orange-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-orange-700">
                            Perbaiki Data Sekarang &rarr;
                        </a>
                    </div>
                </div>
            @endif            {{-- Panel Khusus Gugus Depan --}}
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
            {{-- Panel Peserta --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-stat-card
                    label="Identitas Peserta Regu"
                    value="Kartu Digital QR Sah"
                    description="Tunjukkan QR Code saat presensi di pos lomba"
                    color="amber"
                    icon="qr-code"
                />

                <x-stat-card
                    label="Jadwal Tanding"
                    value="LT III Kwarcab Padangsidimpuan"
                    action-text="Lihat Jadwal Sesi Lomba"
                    href="{{ route('schedules') }}"
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
                <a href="{{ route('competitions') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-700 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300">
                    <flux:icon name="book-open" class="size-4" />
                    Petunjuk Teknis (Juknis) Lomba &rarr;
                </a>
                <span class="text-zinc-300 dark:text-zinc-700">&bull;</span>
                <a href="{{ route('schedules') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-700 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300">
                    <flux:icon name="calendar" class="size-4" />
                    Jadwal Resmi & Arena &rarr;
                </a>
                <span class="text-zinc-300 dark:text-zinc-700">&bull;</span>
                <a href="{{ route('leaderboard') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-700 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300">
                    <flux:icon name="trophy" class="size-4" />
                    Klasemen Langsung &rarr;
                </a>
            </div>
        </div>
    </div>
</x-layouts::app>

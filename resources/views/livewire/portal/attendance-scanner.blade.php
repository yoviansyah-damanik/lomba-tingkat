<div>
        <div class="space-y-5" x-data="attendanceScanner({
            scanUrl: '{{ route('attendance.scan', $session) }}',
            manualUrl: '{{ route('attendance.manual', $session) }}',
            searchUrl: '{{ route('attendance.search', $session) }}',
            logsUrl: '{{ route('attendance.logs', $session) }}',
            csrfToken: '{{ csrf_token() }}',
            sessionId: '{{ $session->id }}',
            isActive: {{ $session->is_active ? 'true' : 'false' }},
            isWithinSchedule: {{ $session->isWithinSchedule() ? 'true' : 'false' }}
        })" x-init="initScanner()">

            {{-- Top Bar & Session Info --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-zinc-900 text-white rounded-2xl p-4 sm:p-5 shadow-sm border border-zinc-800">
                <div class="flex items-center gap-3.5">
                    <a href="{{ route('attendance.index') }}" wire:navigate class="size-9 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-300 flex items-center justify-center transition-colors">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                    </a>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ ($session->is_active && $session->isWithinSchedule()) ? 'bg-emerald-950 text-emerald-400 border border-emerald-800' : 'bg-rose-950 text-rose-400 border border-rose-800' }}">
                                <span class="size-1.5 rounded-full {{ ($session->is_active && $session->isWithinSchedule()) ? 'bg-emerald-400 animate-pulse' : 'bg-rose-400' }}"></span>
                                {{ ! $session->is_active ? 'Pos Dinonaktifkan' : ($session->isScheduleClosed() ? 'Pos Ditutup' : (! $session->isScheduleOpen() ? 'Belum Buka' : 'Pos Dibuka')) }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $session->scheduleStatusBadgeClasses() }}">
                                {{ $session->scheduleStatusLabel() }}
                            </span>
                            <span class="text-xs text-zinc-400 font-medium">
                                {{ $session->sessionTypeLabel() }}
                            </span>
                            @if($session->isCompetition())
                                @if($session->schedule)
                                    @if($session->schedule->effectiveGenderCategory() === 'boys')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-950 text-blue-300 border border-blue-700">
                                            <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            Khusus Regu Putra
                                        </span>
                                    @elseif($session->schedule->effectiveGenderCategory() === 'girls')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-pink-950 text-pink-300 border border-pink-700">
                                            <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            Khusus Regu Putri
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-zinc-800 text-zinc-300 border border-zinc-700">
                                            Putra & Putri (Terbuka)
                                        </span>
                                    @endif
                                @endif

                                @if($session->schedule?->isPatrol())
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-950 text-amber-400 border border-amber-800">
                                        Lomba Regu Utuh &bull; Wajib PINRU / WAPINRU
                                    </span>
                                @elseif($session->schedule?->isGroup())
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-indigo-950 text-indigo-400 border border-indigo-800">
                                        Lomba Kelompok &bull; Seluruh Peserta Wajib Presensi
                                    </span>
                                @elseif($session->schedule?->isIndividual())
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-950 text-blue-400 border border-blue-800">
                                        Lomba Individu &bull; 1 Perwakilan Regu
                                    </span>
                                @endif
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-950 text-emerald-300 border border-emerald-800">
                                    Presensi Kehadiran &bull; {{ $session->sessionTypeLabel() }}
                                </span>
                            @endif
                        </div>
                        <h1 class="text-lg sm:text-xl font-black tracking-tight text-white mt-1">
                            {{ $session->title }}
                        </h1>
                        <div class="text-xs text-zinc-400 flex items-center gap-2 mt-0.5">
                            <span>{{ $session->schedule?->arena_location ?: ($session->isCompetition() ? 'Pos Lapangan' : 'Presensi Lapangan') }}</span>
                            @if($session->schedule?->competition)
                                <span>&bull;</span>
                                <span class="text-amber-400 font-medium">{{ $session->schedule->competition->title }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Live Count, Rekap Link & Manual Trigger --}}
                <div class="flex items-center gap-2 flex-wrap">
                    <a 
                        href="{{ route('attendance.recap', ['session' => $session->id]) }}" 
                        class="inline-flex items-center gap-1.5 rounded-xl border border-zinc-700 bg-zinc-800 hover:bg-zinc-700 text-zinc-200 px-3.5 py-2 text-xs font-bold transition-colors"
                        title="Buka Rekapitulasi Presensi Sesi Ini" wire:navigate>
                        <svg class="size-4 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        <span>Rekap Pos</span>
                    </a>

                    <div class="bg-zinc-800/90 border border-zinc-700/60 rounded-xl px-4 py-1.5 text-right">
                        <div class="text-[10px] uppercase font-bold text-zinc-400">Total Hadir</div>
                        <div class="text-lg font-black text-amber-400" x-text="totalAttended">
                            {{ $totalAttended }}
                        </div>
                    </div>

                    <button type="button" 
                        @if($session->isWithinSchedule() && $session->is_active) @click="openManualModal = true" @endif
                        class="inline-flex items-center gap-1.5 rounded-xl border px-3.5 py-2 text-xs font-bold transition-colors {{ ($session->isWithinSchedule() && $session->is_active) ? 'bg-zinc-800 hover:bg-zinc-700 border-zinc-700 text-zinc-200 cursor-pointer' : 'bg-zinc-900 border-zinc-800 text-zinc-500 cursor-not-allowed opacity-60' }}"
                        @disabled(! ($session->isWithinSchedule() && $session->is_active))
                        title="{{ ! $session->is_active ? 'Sesi presensi dinonaktifkan oleh Administrator' : ($session->isWithinSchedule() ? 'Presensi Manual' : 'Presensi dinonaktifkan di luar jadwal lomba') }}"
                    >
                        <svg class="size-4 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        Manual
                    </button>
                </div>
            </div>

            {{-- Main Grid: Camera Viewport on Left, Attendee Flash Card & Logs on Right --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
            
                {{-- Camera Viewport Panel (7 cols) --}}
                <div class="lg:col-span-7 space-y-3">
                    @if(! $session->is_active)
                        {{-- BLOK KAMERA KETIKA SESI PRESENSI SEDANG DINONAKTIFKAN OLEH ADMINISTRATOR --}}
                        <div class="relative bg-zinc-950 rounded-3xl overflow-hidden border border-rose-500/30 shadow-2xl aspect-4/3 sm:aspect-16/11 flex flex-col items-center justify-center p-6 text-center">
                            <div class="size-20 rounded-3xl bg-rose-500/10 border border-rose-500/30 flex items-center justify-center text-rose-400 mb-4 shadow-lg shadow-rose-500/10">
                                <svg class="size-10 text-rose-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="4.93" x2="19.07" y1="4.93" y2="19.07"/>
                                </svg>
                            </div>

                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-rose-950/80 text-rose-300 border border-rose-800 mb-2">
                                <span class="size-2 rounded-full bg-rose-400"></span>
                                Sesi Dinonaktifkan
                            </span>

                            <h2 class="text-white font-extrabold text-lg sm:text-xl">
                                Akses Kamera Diblokir
                            </h2>

                            <p class="text-xs text-zinc-400 max-w-md mt-1 leading-relaxed">
                                Sesi presensi ini sedang dinonaktifkan oleh Administrator. Seluruh aktivitas pemindaian QR code dan pencatatan presensi untuk sesi ini ditangguhkan.
                            </p>

                            {{-- Rincian Pemberitahuan Penuh --}}
                            <div class="mt-4 w-full max-w-md rounded-2xl border border-zinc-800 bg-zinc-900/90 p-4 text-left text-xs space-y-2.5">
                                <div class="flex items-start justify-between gap-2 pb-2 border-b border-zinc-800">
                                    <span class="text-zinc-400">Nama Sesi:</span>
                                    <span class="font-semibold text-zinc-200 text-right">
                                        {{ $session->title }}
                                    </span>
                                </div>
                                <div class="flex items-start justify-between gap-2 pb-2 border-b border-zinc-800">
                                    <span class="text-zinc-400">Status Administrator:</span>
                                    <span class="font-bold text-rose-400 text-right">
                                        Dikunci / Dinonaktifkan
                                    </span>
                                </div>
                                <div class="flex items-start justify-between gap-2 pb-2 border-b border-zinc-800">
                                    <span class="text-zinc-400">{{ $session->isCompetition() ? 'Jadwal Sesi Lomba:' : 'Waktu Presensi:' }}</span>
                                    <span class="font-semibold text-zinc-200 text-right">
                                        @if($session->schedule?->start_time)
                                            {{ \Carbon\Carbon::parse($session->schedule->start_time)->setTimezone('Asia/Jakarta')->isoFormat('dddd, D MMMM Y • HH:mm') }} WIB
                                        @elseif($session->opened_at)
                                            {{ \Carbon\Carbon::parse($session->opened_at)->setTimezone('Asia/Jakarta')->isoFormat('dddd, D MMMM Y • HH:mm') }} WIB
                                        @else
                                            -
                                        @endif
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-zinc-500 pt-0.5">
                                    <span>Waktu Server Saat Ini:</span>
                                    <span class="font-mono text-zinc-400 font-medium">
                                        {{ now()->setTimezone('Asia/Jakarta')->isoFormat('dddd, D MMMM Y • HH:mm:ss') }} WIB
                                    </span>
                                </div>
                            </div>

                            <p class="mt-3 text-[11px] text-zinc-500 italic">
                                * Silakan hubungi Panitia atau Administrator LT III melalui menu Manajemen Jadwal untuk mengaktifkan kembali sesi presensi ini.
                            </p>
                        </div>

                        {{-- Toolbar Kamera Dinonaktifkan --}}
                        <div class="flex items-center justify-between gap-2 bg-zinc-900/60 p-3 rounded-2xl border border-zinc-800 text-xs text-zinc-400">
                            <div class="flex items-center gap-2">
                                <svg class="size-4 text-rose-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="12" x2="12" y1="8" y2="12"/>
                                    <line x1="12" x2="12.01" y1="16" y2="16"/>
                                </svg>
                                <span>Sesi presensi ini sedang dinonaktifkan oleh Administrator.</span>
                            </div>
                            <span class="inline-flex items-center gap-1.5 rounded-xl bg-zinc-800 px-3 py-1.5 font-bold text-rose-400 border border-zinc-700 shrink-0">
                                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                Kamera Diblokir
                            </span>
                        </div>

                    @elseif(! $session->isScheduleOpen())
                        {{-- BLOK KAMERA KETIKA SESI PRESENSI BELUM TERBUKA --}}
                        <div class="relative bg-zinc-950 rounded-3xl overflow-hidden border border-amber-500/30 shadow-2xl aspect-4/3 sm:aspect-16/11 flex flex-col items-center justify-center p-6 text-center">
                            <div class="size-20 rounded-3xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400 mb-4 shadow-lg shadow-amber-500/10">
                                <svg class="size-10 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                            </div>

                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-950/80 text-amber-300 border border-amber-800 mb-2">
                                <span class="size-2 rounded-full bg-amber-400 animate-pulse"></span>
                                Presensi Belum Terbuka
                            </span>

                            <h2 class="text-white font-extrabold text-lg sm:text-xl">
                                Kamera Scanner Diblokir
                            </h2>

                            <p class="text-xs text-zinc-400 max-w-md mt-1 leading-relaxed">
                                Kamera pemindai dikunci dan tidak dapat digunakan sebelum sesi presensi dibuka sesuai jadwal resmi lomba.
                            </p>

                            {{-- Rincian Tanggal dan Jam --}}
                            <div class="mt-4 w-full max-w-md rounded-2xl border border-zinc-800 bg-zinc-900/90 p-4 text-left text-xs space-y-2.5">
                                <div class="flex items-start justify-between gap-2 pb-2 border-b border-zinc-800">
                                    <span class="text-zinc-400">{{ $session->isCompetition() ? 'Jadwal Sesi Lomba:' : 'Waktu Mulai Presensi:' }}</span>
                                    <span class="font-semibold text-zinc-200 text-right">
                                        @if($session->schedule?->start_time)
                                            {{ \Carbon\Carbon::parse($session->schedule->start_time)->setTimezone('Asia/Jakarta')->isoFormat('dddd, D MMMM Y • HH:mm') }} WIB
                                        @elseif($session->opened_at)
                                            {{ \Carbon\Carbon::parse($session->opened_at)->setTimezone('Asia/Jakarta')->isoFormat('dddd, D MMMM Y • HH:mm') }} WIB
                                        @else
                                            -
                                        @endif
                                    </span>
                                </div>
                                <div class="flex items-start justify-between gap-2 pb-2 border-b border-zinc-800">
                                    <span class="text-amber-400 font-semibold">Presensi Dibuka Paling Cepat:</span>
                                    <span class="font-bold text-amber-300 text-right">
                                        {{ $session->getEffectiveOpenTime() ? $session->getEffectiveOpenTime()->setTimezone('Asia/Jakarta')->isoFormat('dddd, D MMMM Y • HH:mm') : '-' }} WIB
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-zinc-500 pt-0.5">
                                    <span>Waktu Server Saat Ini:</span>
                                    <span class="font-mono text-zinc-400 font-medium">
                                        {{ now()->setTimezone('Asia/Jakarta')->isoFormat('dddd, D MMMM Y • HH:mm:ss') }} WIB
                                    </span>
                                </div>
                            </div>

                            <p class="mt-3 text-[11px] text-zinc-500 italic">
                                * Presensi otomatis terbuka paling cepat 30 menit sebelum jadwal lomba dimulai.
                            </p>
                        </div>

                        {{-- Toolbar Kamera Terkunci --}}
                        <div class="flex items-center justify-between gap-2 bg-zinc-900/60 p-3 rounded-2xl border border-zinc-800 text-xs text-zinc-400">
                            <div class="flex items-center gap-2">
                                <svg class="size-4 text-amber-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="12" x2="12" y1="8" y2="12"/>
                                    <line x1="12" x2="12.01" y1="16" y2="16"/>
                                </svg>
                                <span>Kamera scanner tidak dapat diaktifkan sebelum waktu presensi tiba.</span>
                            </div>
                            <span class="inline-flex items-center gap-1.5 rounded-xl bg-zinc-800 px-3 py-1.5 font-bold text-zinc-400 border border-zinc-700 shrink-0">
                                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                Kamera Terkunci
                            </span>
                        </div>

                    @elseif($session->isScheduleClosed())
                        {{-- BLOK KAMERA KETIKA SESI PRESENSI SUDAH DITUTUP --}}
                        <div class="relative bg-zinc-950 rounded-3xl overflow-hidden border border-zinc-800 shadow-2xl aspect-4/3 sm:aspect-16/11 flex flex-col items-center justify-center p-6 text-center">
                            <div class="size-20 rounded-3xl bg-zinc-900 border border-zinc-800 flex items-center justify-center text-zinc-500 mb-4 shadow-inner">
                                <svg class="size-10 text-zinc-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/><line x1="4.93" x2="19.07" y1="4.93" y2="19.07"/>
                                </svg>
                            </div>

                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-zinc-800 text-zinc-300 border border-zinc-700 mb-2">
                                Presensi Telah Ditutup
                            </span>

                            <h2 class="text-white font-extrabold text-lg sm:text-xl">
                                {{ $session->isCompetition() ? 'Sesi Lomba Telah Selesai' : 'Sesi Presensi Telah Ditutup' }}
                            </h2>

                            <p class="text-xs text-zinc-400 max-w-md mt-1 leading-relaxed">
                                {{ $session->isCompetition() ? 'Jadwal pelaksanaan sesi lomba ini' : 'Batas waktu sesi presensi ini' }} telah berakhir pada
                                <strong class="text-zinc-200">{{ $session->getEffectiveCloseTime() ? $session->getEffectiveCloseTime()->setTimezone('Asia/Jakarta')->isoFormat('dddd, D MMMM Y [pukul] HH:mm') : '-' }} WIB</strong>.
                                Kamera pemindai dinonaktifkan.
                            </p>

                            <div class="mt-4 w-full max-w-md rounded-2xl border border-zinc-800 bg-zinc-900/90 p-4 text-left text-xs space-y-2">
                                <div class="flex items-center justify-between pb-2 border-b border-zinc-800">
                                    <span class="text-zinc-400">Waktu Tutup Sesi:</span>
                                    <span class="font-semibold text-zinc-200">
                                        {{ $session->getEffectiveCloseTime() ? $session->getEffectiveCloseTime()->setTimezone('Asia/Jakarta')->isoFormat('dddd, D MMMM Y • HH:mm') : '-' }} WIB
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-zinc-500 pt-0.5">
                                    <span>Waktu Server Saat Ini:</span>
                                    <span class="font-mono text-zinc-400">
                                        {{ now()->setTimezone('Asia/Jakarta')->isoFormat('dddd, D MMMM Y • HH:mm') }} WIB
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Toolbar Kamera Ditutup --}}
                        <div class="flex items-center justify-between gap-2 bg-zinc-900/60 p-3 rounded-2xl border border-zinc-800 text-xs text-zinc-500">
                            <span>Sesi presensi pos telah berakhir</span>
                            <span class="inline-flex items-center gap-1.5 rounded-xl bg-zinc-800 px-3 py-1.5 font-bold text-zinc-400 border border-zinc-700">
                                Presensi Berakhir
                            </span>
                        </div>

                    @else
                        {{-- KAMERA AKTIF --}}
                        <div class="relative bg-black rounded-3xl overflow-hidden border border-zinc-800 shadow-xl aspect-4/3 sm:aspect-16/11 flex items-center justify-center">
                            {{-- Container video scanner --}}
                            <div id="qr-reader" class="w-full h-full object-cover [&_video]:w-full [&_video]:h-full [&_video]:object-cover [&_video]:rounded-3xl [&_#qr-shaded-region]:hidden"></div>

                            {{-- Overlay Target Reticle (Kotak Pemindai QR Rapi di Tengah) --}}
                            <div class="pointer-events-none absolute inset-0 flex items-center justify-center z-10" x-show="isCameraRunning">
                                <div class="relative w-56 h-56 sm:w-64 sm:h-64 md:w-72 md:h-72 border-2 border-amber-400/50 rounded-3xl flex items-center justify-center shadow-[0_0_30px_rgba(245,158,11,0.25)]">
                                    {{-- 4 Sudut Aksen Tebal --}}
                                    <div class="absolute -top-1 -left-1 w-9 h-9 border-t-4 border-l-4 border-amber-400 rounded-tl-2xl"></div>
                                    <div class="absolute -top-1 -right-1 w-9 h-9 border-t-4 border-r-4 border-amber-400 rounded-tr-2xl"></div>
                                    <div class="absolute -bottom-1 -left-1 w-9 h-9 border-b-4 border-l-4 border-amber-400 rounded-bl-2xl"></div>
                                    <div class="absolute -bottom-1 -right-1 w-9 h-9 border-b-4 border-r-4 border-amber-400 rounded-br-2xl"></div>

                                    {{-- Titik Fokus / Crosshair Minimalis --}}
                                    <div class="size-4 border border-amber-400/40 rounded-full flex items-center justify-center">
                                        <div class="size-1.5 bg-amber-400/80 rounded-full"></div>
                                    </div>

                                    {{-- Laser Garis Pemindai Animasi --}}
                                    <div class="absolute inset-x-2 h-0.5 bg-gradient-to-r from-transparent via-amber-400 to-transparent shadow-[0_0_12px_#f59e0b] animate-[scan_2s_ease-in-out_infinite]"></div>
                                </div>
                            </div>

                            {{-- Keterangan / Petunjuk Pemindaian (DILUAR BOX SCAN QR) --}}
                            <div class="pointer-events-none absolute bottom-3 sm:bottom-4 inset-x-0 flex justify-center z-20 px-3" x-show="isCameraRunning">
                                <div class="inline-flex items-center gap-2 rounded-full bg-black/85 px-4 py-2 border border-zinc-700/80 backdrop-blur-md shadow-2xl text-[11px] sm:text-xs">
                                    <span class="inline-flex size-2 rounded-full bg-amber-400 animate-pulse shrink-0"></span>
                                    <span class="font-bold text-amber-300 tracking-wider uppercase">ARAHKAN QR CODE KE SINI</span>
                                    @if($session->schedule?->isPatrol())
                                        <span class="text-[10px] sm:text-[11px] text-amber-200/90 font-medium pl-2 border-l border-zinc-700">Khusus PINRU / WAPINRU</span>
                                    @elseif($session->schedule?->isGroup())
                                        <span class="text-[10px] sm:text-[11px] text-indigo-300 font-medium pl-2 border-l border-zinc-700">Wajib Seluruh Peserta</span>
                                    @elseif($session->schedule?->isIndividual())
                                        <span class="text-[10px] sm:text-[11px] text-amber-200/90 font-medium pl-2 border-l border-zinc-700">Perwakilan Regu</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Camera State Overlay jika belum dimulai / error --}}
                            <div class="absolute inset-0 bg-zinc-950 flex flex-col items-center justify-center p-6 text-center text-zinc-400" x-show="!isCameraRunning">
                                <div class="size-16 rounded-2xl bg-zinc-900 border border-zinc-800 flex items-center justify-center text-amber-500 mb-3 shadow-inner">
                                    <svg class="size-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
                                </div>
                                <h3 class="text-white font-bold text-base" x-text="cameraStatusTitle">Kamera Belum Aktif</h3>
                                <p class="text-xs text-zinc-400 max-w-xs mt-1" x-text="cameraStatusMsg">Tekan tombol di bawah untuk menyalakan kamera scanner pos.</p>
                                <button type="button" @click="startScanner()" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-amber-600 px-5 py-2.5 text-xs font-bold text-white shadow-lg shadow-amber-600/30 hover:bg-amber-500 transition-all">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                    Mulai Kamera Scanner
                                </button>
                            </div>
                        </div>

                        {{-- Camera Controls Toolbar --}}
                        <div class="flex items-center justify-between gap-2 bg-white dark:bg-zinc-900 p-3 rounded-2xl border border-zinc-200 dark:border-zinc-800 text-xs">
                            <div class="flex items-center gap-2">
                                <select x-model="selectedCameraId" @change="switchCamera()" class="rounded-xl border border-zinc-200 bg-zinc-50 px-2.5 py-1.5 text-xs text-zinc-800 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200" :disabled="!isCameraRunning">
                                    <template x-for="cam in availableCameras" :key="cam.id">
                                        <option :value="cam.id" x-text="cam.label || 'Kamera ' + cam.id"></option>
                                    </template>
                                </select>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="button" @click="soundEnabled = !soundEnabled" :class="soundEnabled ? 'text-amber-600 bg-amber-50 dark:bg-amber-950/40 dark:text-amber-400 border-amber-300' : 'text-zinc-400 bg-zinc-100 dark:bg-zinc-800 border-transparent'" class="size-8 rounded-xl border flex items-center justify-center transition-colors" title="Toggle Suara Beep">
                                    <svg x-show="soundEnabled" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/></svg>
                                    <svg x-show="!soundEnabled" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><line x1="23" x2="1" y1="9" y2="15"/></svg>
                                </button>

                                <button type="button" x-show="isCameraRunning" @click="stopScanner()" class="rounded-xl border border-zinc-200 dark:border-zinc-700 px-3 py-1.5 font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800">
                                    Jeda Kamera
                                </button>
                                <button type="button" x-show="!isCameraRunning" @click="startScanner()" class="rounded-xl bg-amber-600 px-3 py-1.5 font-bold text-white hover:bg-amber-700">
                                    Nyalakan Kamera
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Result Card & Realtime Stream Panel (5 cols) --}}
                <div class="lg:col-span-5 space-y-4">
                
                    {{-- Flash Card Kartu Hasil Pindai Terbaru --}}
                    <div class="rounded-3xl border transition-all duration-300 p-5 shadow-sm" :class="{
                        'bg-emerald-50/90 border-emerald-300 dark:bg-emerald-950/30 dark:border-emerald-800': lastResult && lastResult.success,
                        'bg-amber-50/90 border-amber-300 dark:bg-amber-950/30 dark:border-amber-800': lastResult && !lastResult.success && lastResult.code === 'ALREADY_SCANNED',
                        'bg-rose-50/90 border-rose-300 dark:bg-rose-950/30 dark:border-rose-800': lastResult && !lastResult.success && lastResult.code !== 'ALREADY_SCANNED',
                        'bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800': !lastResult
                    }">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                Status Verifikasi Pindai
                            </span>
                        
                            <template x-if="lastResult">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wide" :class="{
                                    'bg-emerald-600 text-white': lastResult.success,
                                    'bg-amber-600 text-white': !lastResult.success && lastResult.code === 'ALREADY_SCANNED',
                                    'bg-rose-600 text-white': !lastResult.success && lastResult.code !== 'ALREADY_SCANNED'
                                }" x-text="lastResult.success ? 'PRESENSI BERHASIL' : (lastResult.code === 'ALREADY_SCANNED' ? 'SUDAH HADIR' : 'TIDAK VALID')">
                                </span>
                            </template>
                        </div>

                        {{-- Attendee Profile Card Flash --}}
                        <template x-if="lastAttendee">
                            <div class="flex items-start gap-4 pt-1">
                                {{-- Foto Profil --}}
                                <div class="size-16 rounded-2xl overflow-hidden bg-zinc-100 dark:bg-zinc-800 border-2 border-white dark:border-zinc-700 shadow-sm shrink-0 flex items-center justify-center">
                                    <template x-if="lastAttendee.photo_url">
                                        <img :src="lastAttendee.photo_url" :alt="lastAttendee.name" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!lastAttendee.photo_url">
                                        <div class="text-[9px] font-black text-zinc-400 text-center leading-tight">
                                            PAS FOTO<br>3x4
                                        </div>
                                    </template>
                                </div>

                                {{-- Biodata Singkat --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-mono font-bold px-1.5 py-0.5 rounded bg-zinc-200/80 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200" x-text="lastAttendee.id_number"></span>
                                        <span class="text-[10px] font-bold text-amber-700 dark:text-amber-400 uppercase" x-text="lastAttendee.role_label"></span>
                                    </div>
                                    <h3 class="text-base font-black text-zinc-900 dark:text-zinc-100 truncate mt-0.5" x-text="lastAttendee.name"></h3>
                                    <p class="text-xs text-zinc-600 dark:text-zinc-400 truncate mt-0.5">
                                        <span x-text="lastAttendee.school_name"></span> &bull; <span x-text="lastAttendee.patrol_name"></span>
                                    </p>
                                    <div class="mt-2 text-[11px] font-semibold text-emerald-700 dark:text-emerald-400 flex items-center gap-1.5">
                                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                        <span x-text="'Pukul ' + lastAttendee.scanned_at_formatted"></span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- Pesan Status Jika Belum Ada Pindaian --}}
                        <template x-if="!lastAttendee">
                            <div class="py-6 text-center text-zinc-400">
                                @if(! $session->is_active)
                                    <div class="size-12 rounded-full bg-rose-500/10 border border-rose-500/20 mx-auto flex items-center justify-center text-rose-400 mb-2">
                                        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="4.93" x2="19.07" y1="4.93" y2="19.07"/></svg>
                                    </div>
                                    <div class="text-xs font-bold text-rose-400">Sesi Presensi Dinonaktifkan</div>
                                    <div class="text-[11px] text-zinc-400 mt-0.5">Pemindaian QR ditangguhkan oleh Administrator.</div>
                                @elseif(! $session->isScheduleOpen())
                                    <div class="size-12 rounded-full bg-amber-500/10 border border-amber-500/20 mx-auto flex items-center justify-center text-amber-400 mb-2">
                                        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                    </div>
                                    <div class="text-xs font-bold text-amber-400">Presensi Belum Terbuka</div>
                                    <div class="text-[11px] text-zinc-400 mt-0.5">Pemindaian dibuka 30 menit sebelum jadwal lomba.</div>
                                @elseif($session->isScheduleClosed())
                                    <div class="size-12 rounded-full bg-zinc-800 mx-auto flex items-center justify-center text-zinc-400 mb-2">
                                        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="4.93" x2="19.07" y1="4.93" y2="19.07"/></svg>
                                    </div>
                                    <div class="text-xs font-bold text-zinc-400">Sesi Lomba Telah Selesai</div>
                                    <div class="text-[11px] text-zinc-500 mt-0.5">Waktu presensi untuk sesi ini telah ditutup.</div>
                                @else
                                    <div class="size-12 rounded-full bg-zinc-100 dark:bg-zinc-800/80 mx-auto flex items-center justify-center text-zinc-400 mb-2">
                                        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M7 7h.01"/><path d="M17 7h.01"/><path d="M7 17h.01"/><path d="M17 17h.01"/></svg>
                                    </div>
                                    <div class="text-xs font-medium text-zinc-500">Menunggu pemindaian kartu peserta...</div>
                                    <div class="text-[11px] text-zinc-400 mt-0.5">Arahkan kartu B2 atau lencana PINRU ke depan kamera.</div>
                                @endif
                            </div>
                        </template>

                        {{-- Pesan Notifikasi Error / Info --}}
                        <template x-if="lastResult && lastResult.message">
                            <div class="mt-3 pt-3 border-t border-zinc-200/60 dark:border-zinc-800/60 text-xs font-medium" :class="{
                                'text-emerald-800 dark:text-emerald-300': lastResult.success,
                                'text-amber-800 dark:text-amber-300': !lastResult.success && lastResult.code === 'ALREADY_SCANNED',
                                'text-rose-800 dark:text-rose-300': !lastResult.success && lastResult.code !== 'ALREADY_SCANNED'
                            }" x-text="lastResult.message">
                            </div>
                        </template>
                    </div>

                    {{-- Realtime Attendance Stream (Daftar yang baru saja presensi) --}}
                    <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 p-4 shadow-sm flex flex-col h-[340px]">
                        <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800 mb-2">
                            <div class="flex items-center gap-2">
                                <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300">
                                    Log Presensi Pos
                                </h2>
                                <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            </div>
                            <button type="button" @click="fetchLogs()" class="text-[11px] text-zinc-400 hover:text-amber-600 flex items-center gap-1 font-semibold">
                                <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 21h5v-5"/></svg>
                                Segarkan
                            </button>
                        </div>

                        {{-- Scrollable List --}}
                        <div class="flex-1 overflow-y-auto space-y-2 pr-1 divide-y divide-zinc-100 dark:divide-zinc-800/60">
                            <template x-for="item in attendanceLogs" :key="item.id">
                                <div class="pt-2 first:pt-0 flex items-center justify-between gap-3 text-xs">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="size-8 rounded-lg overflow-hidden bg-zinc-100 dark:bg-zinc-800 shrink-0 flex items-center justify-center font-bold text-zinc-400 text-[9px]">
                                            <template x-if="item.photo_url">
                                                <img :src="item.photo_url" :alt="item.name" class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!item.photo_url">
                                                <span>3x4</span>
                                            </template>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-zinc-900 dark:text-zinc-100 truncate" x-text="item.name"></div>
                                            <div class="text-[10px] text-zinc-500 dark:text-zinc-400 truncate">
                                                <span x-text="item.id_number"></span> &bull; <span x-text="item.role"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0 flex items-center gap-2">
                                        <div>
                                            <div class="text-[11px] font-mono font-semibold text-emerald-700 dark:text-emerald-400" x-text="item.scanned_at"></div>
                                            <div class="text-[9px] uppercase font-bold text-zinc-400" x-text="item.status === 'present' ? 'Hadir' : (item.status === 'late' ? 'Terlambat' : (item.status === 'excused' ? 'Izin' : 'Alpa'))"></div>
                                        </div>
                                        <button type="button" @click="openEditModal(item)" class="size-6 rounded-md bg-zinc-100 dark:bg-zinc-800 hover:bg-amber-100 dark:hover:bg-amber-950/40 text-zinc-500 hover:text-amber-600 flex items-center justify-center transition-colors" title="Edit Data Presensi">
                                            <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <div x-show="attendanceLogs.length === 0" class="h-full flex items-center justify-center text-center text-xs text-zinc-400 py-10">
                                Belum ada presensi tercatat di pos ini.
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            {{-- Modal Input Presensi Manual --}}
            <div x-show="openManualModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs" x-transition.opacity style="display: none;">
                <div class="w-full max-w-lg bg-white dark:bg-zinc-900 rounded-3xl p-6 shadow-2xl border border-zinc-200 dark:border-zinc-800 space-y-4" @click.outside="openManualModal = false">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">Presensi Manual Pos</h2>
                            <p class="text-xs text-zinc-500">Cari peserta atau pembina jika kartu rusak atau kamera bermasalah.</p>
                        </div>
                        <button type="button" @click="openManualModal = false" class="size-8 rounded-full bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 text-zinc-500 flex items-center justify-center">
                            &times;
                        </button>
                    </div>

                    {{-- Kotak Pencarian --}}
                    <div class="relative">
                        <input type="text" x-model="searchQuery" @input.debounce.300ms="searchAttendees()" placeholder="Ketik nama, ID-XXXXXXXX, atau NTA..." class="w-full rounded-2xl border border-zinc-200 bg-zinc-50 pl-10 pr-4 py-2.5 text-sm text-zinc-900 focus:border-amber-600 focus:bg-white focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                        <svg class="pointer-events-none absolute left-3.5 top-3 size-4 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </div>

                    {{-- Hasil Pencarian --}}
                    <div class="max-h-60 overflow-y-auto space-y-2 divide-y divide-zinc-100 dark:divide-zinc-800">
                        <template x-for="candidate in searchResults" :key="candidate.id">
                            <div class="pt-2 first:pt-0 flex items-center justify-between gap-3 p-2 rounded-xl hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="size-10 rounded-xl overflow-hidden bg-zinc-100 dark:bg-zinc-800 shrink-0 flex items-center justify-center text-[10px] font-bold text-zinc-400">
                                        <template x-if="candidate.photo_url">
                                            <img :src="candidate.photo_url" :alt="candidate.name" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!candidate.photo_url">
                                            <span>3x4</span>
                                        </template>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-zinc-900 dark:text-zinc-100 text-sm truncate" x-text="candidate.name"></span>
                                            <span class="text-[10px] font-mono px-1 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300" x-text="candidate.id_number"></span>
                                        </div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400 truncate" x-text="candidate.sub_title"></div>
                                    </div>
                                </div>

                                <div class="shrink-0">
                                    <template x-if="candidate.is_attended">
                                        <span class="text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-lg dark:bg-emerald-950/60 dark:text-emerald-400">
                                            Sudah Hadir
                                        </span>
                                    </template>
                                    <template x-if="!candidate.is_attended">
                                        <button type="button" @click="checkInCandidate(candidate)" class="rounded-xl bg-amber-600 hover:bg-amber-700 text-white px-3 py-1.5 text-xs font-bold transition-all shadow-xs">
                                            Hadirkan
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <div x-show="searchResults.length === 0 && searchQuery.length >= 2" class="py-8 text-center text-xs text-zinc-400">
                            Tidak ada entitas yang cocok dengan kata kunci pencarian.
                        </div>
                    </div>

                    <div class="flex justify-end pt-2 border-t border-zinc-100 dark:border-zinc-800">
                        <button type="button" @click="openManualModal = false" class="px-4 py-2 text-xs font-semibold text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>

            {{-- Modal Edit Catatan Presensi Pos --}}
            <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs" x-transition.opacity style="display: none;">
                <div class="w-full max-w-md bg-white dark:bg-zinc-900 rounded-3xl p-6 shadow-2xl border border-zinc-200 dark:border-zinc-800 space-y-4" @click.outside="showEditModal = false">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Edit Data Presensi Pos</h2>
                            <p class="text-xs text-zinc-500" x-text="editingItem ? editingItem.name : ''"></p>
                        </div>
                        <button type="button" @click="showEditModal = false" class="size-8 rounded-full bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 text-zinc-500 flex items-center justify-center">
                            &times;
                        </button>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div>
                            <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Status Kehadiran *</label>
                            <select x-model="editForm.status" class="w-full rounded-xl border border-zinc-200 bg-zinc-50 p-2.5 text-xs font-semibold text-zinc-900 focus:border-amber-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                <option value="present">Hadir Tepat Waktu (Present)</option>
                                <option value="late">Terlambat (Late)</option>
                                <option value="excused">Izin / Dispensasi (Excused)</option>
                                <option value="absent">Alpa / Tidak Hadir (Absent)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Catatan Khusus Petugas Pos</label>
                            <textarea x-model="editForm.notes" rows="2" placeholder="Catatan khusus atau alasan revisi..." class="w-full rounded-xl border border-zinc-200 bg-zinc-50 p-2.5 text-xs text-zinc-900 focus:border-amber-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-3 border-t border-zinc-100 dark:border-zinc-800">
                        <button type="button" @click="deleteAttendanceItem()" class="text-xs font-bold text-rose-600 hover:text-rose-700 flex items-center gap-1">
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            Batalkan
                        </button>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="showEditModal = false" class="px-3 py-1.5 text-xs font-semibold text-zinc-600 dark:text-zinc-400">
                                Batal
                            </button>
                            <button type="button" @click="saveEditAttendance()" class="rounded-xl bg-amber-600 hover:bg-amber-700 text-white px-3.5 py-1.5 text-xs font-bold">
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    @assets
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
        <script>
            window.attendanceScanner = (config) => ({
                    html5QrCode: null,
                    isCameraRunning: false,
                    cameraStatusTitle: 'Kamera Belum Aktif',
                    cameraStatusMsg: 'Tekan tombol di bawah untuk menyalakan kamera scanner pos.',
                    availableCameras: [],
                    selectedCameraId: null,
                    soundEnabled: true,
                    totalAttended: 0,
                    lastResult: null,
                    lastAttendee: null,
                    attendanceLogs: [],
                    openManualModal: false,
                    searchQuery: '',
                    searchResults: [],
                    isScanningLocked: false,
                    showEditModal: false,
                    editingItem: null,
                    editForm: {
                        status: 'present',
                        notes: ''
                    },

                    destroy() {
                        if (this.isCameraRunning) this.stopScanner();
                    },

                    initScanner() {
                        this.fetchLogs();
                        if (config.isWithinSchedule && config.isActive) {
                            this.setupCameras();
                        }
                    },

                    // Audio Beep Generator via Web Audio API (Zero external files needed)
                    playAudioFeedback(isSuccess) {
                        if (!this.soundEnabled) return;
                        try {
                            const AudioContext = window.AudioContext || window.webkitAudioContext;
                            if (!AudioContext) return;
                            const ctx = new AudioContext();

                            if (isSuccess) {
                                // Two high pitch affirmative chimes
                                const osc = ctx.createOscillator();
                                const gain = ctx.createGain();
                                osc.type = 'sine';
                                osc.frequency.setValueAtTime(880, ctx.currentTime);
                                osc.frequency.setValueAtTime(1760, ctx.currentTime + 0.08);
                                gain.gain.setValueAtTime(0.2, ctx.currentTime);
                                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.25);
                                osc.connect(gain);
                                gain.connect(ctx.destination);
                                osc.start();
                                osc.stop(ctx.currentTime + 0.25);
                            } else {
                                // Low buzz for warning or duplicate
                                const osc = ctx.createOscillator();
                                const gain = ctx.createGain();
                                osc.type = 'sawtooth';
                                osc.frequency.setValueAtTime(240, ctx.currentTime);
                                osc.frequency.setValueAtTime(160, ctx.currentTime + 0.1);
                                gain.gain.setValueAtTime(0.25, ctx.currentTime);
                                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
                                osc.connect(gain);
                                gain.connect(ctx.destination);
                                osc.start();
                                osc.stop(ctx.currentTime + 0.3);
                            }
                        } catch (e) {
                            console.warn('AudioContext error:', e);
                        }

                        // Haptic feedback
                        if (navigator.vibrate) {
                            navigator.vibrate(isSuccess ? [80, 40, 80] : [200, 100, 200]);
                        }
                    },

                    async setupCameras() {
                        if (!config.isWithinSchedule || !config.isActive) return;

                        try {
                            if (typeof Html5Qrcode === 'undefined') {
                                this.cameraStatusTitle = 'Pustaka Scanner Sedang Dimuat';
                                this.cameraStatusMsg = 'Mohon tunggu beberapa detik atau muat ulang halaman.';
                                return;
                            }

                            const devices = await Html5Qrcode.getCameras();
                            if (devices && devices.length) {
                                this.availableCameras = devices;
                                // Cari kamera belakang (environment) secara default
                                const backCam = devices.find(d => d.label.toLowerCase().includes('back') || d.label.toLowerCase().includes('belakang') || d.label.toLowerCase().includes('environment'));
                                this.selectedCameraId = backCam ? backCam.id : devices[0].id;
                                this.startScanner();
                            } else {
                                this.cameraStatusTitle = 'Kamera Tidak Ditemukan';
                                this.cameraStatusMsg = 'Pastikan perangkat memiliki kamera dan izin akses telah diberikan.';
                            }
                        } catch (err) {
                            this.cameraStatusTitle = 'Akses Kamera Dibatasi';
                            this.cameraStatusMsg = 'Berikan izin akses kamera pada browser Anda untuk memindai.';
                        }
                    },

                    async startScanner() {
                        if (!config.isWithinSchedule || !config.isActive) return;
                        if (this.isCameraRunning) return;
                        if (!this.selectedCameraId) return;

                        try {
                            this.html5QrCode = new Html5Qrcode("qr-reader");
                            const qrConfig = {
                                fps: 15,
                            };

                            await this.html5QrCode.start(
                                this.selectedCameraId,
                                qrConfig,
                                (decodedText) => this.onScanSuccess(decodedText),
                                (errorMessage) => {}
                            );

                            this.isCameraRunning = true;
                        } catch (err) {
                            console.error('Gagal menyalakan scanner:', err);
                            this.isCameraRunning = false;
                            this.cameraStatusTitle = 'Kamera Bermasalah';
                            this.cameraStatusMsg = 'Tidak dapat mengakses stream kamera: ' + (err.message || err);
                        }
                    },

                    async stopScanner() {
                        if (!this.html5QrCode || !this.isCameraRunning) return;
                        try {
                            await this.html5QrCode.stop();
                            this.isCameraRunning = false;
                        } catch (err) {
                            console.error('Error stopping scanner:', err);
                        }
                    },

                    async switchCamera() {
                        if (this.isCameraRunning) {
                            await this.stopScanner();
                            await this.startScanner();
                        }
                    },

                    async onScanSuccess(decodedText) {
                        if (!config.isWithinSchedule || !config.isActive) return;
                        if (this.isScanningLocked) return;
                        this.isScanningLocked = true;

                        try {
                            const response = await fetch(config.scanUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': config.csrfToken,
                                },
                                body: JSON.stringify({
                                    token: decodedText,
                                    device_info: navigator.userAgent
                                })
                            });

                            const data = await response.json();
                            this.lastResult = data;

                            if (data.success) {
                                this.lastAttendee = data.attendee;
                                this.playAudioFeedback(true);
                                this.fetchLogs();
                            } else {
                                if (data.attendee) {
                                    this.lastAttendee = data.attendee;
                                }
                                this.playAudioFeedback(false);
                            }
                        } catch (err) {
                            this.lastResult = {
                                success: false,
                                code: 'NETWORK_ERROR',
                                message: 'Gagal terhubung ke server atau koneksi internet terputus.'
                            };
                            this.playAudioFeedback(false);
                        } finally {
                            // Kunci jeda 1.8 detik sebelum dapat memindai kartu berikutnya
                            setTimeout(() => {
                                this.isScanningLocked = false;
                            }, 1800);
                        }
                    },

                    async searchAttendees() {
                        if (this.searchQuery.length < 2) {
                            this.searchResults = [];
                            return;
                        }

                        try {
                            const url = new URL(config.searchUrl, window.location.origin);
                            url.searchParams.set('q', this.searchQuery);

                            const res = await fetch(url.toString(), {
                                headers: { 'Accept': 'application/json' }
                            });
                            const data = await res.json();
                            this.searchResults = data.candidates || [];
                        } catch (e) {
                            console.error('Error searching candidates:', e);
                        }
                    },

                    async checkInCandidate(candidate) {
                        if (!config.isWithinSchedule || !config.isActive) {
                            alert('Sesi presensi sedang ditutup atau belum memasuki jadwal resmi.');
                            return;
                        }

                        try {
                            const res = await fetch(config.manualUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': config.csrfToken,
                                },
                                body: JSON.stringify({
                                    entity_type: candidate.type,
                                    entity_id: candidate.id,
                                    notes: 'Presensi Manual Pos Lapangan'
                                })
                            });

                            const data = await res.json();
                            if (data.success) {
                                this.openManualModal = false;
                                this.searchQuery = '';
                                this.searchResults = [];
                                this.lastResult = data;
                                this.lastAttendee = {
                                    name: candidate.name,
                                    id_number: candidate.id_number,
                                    role_label: candidate.role_label,
                                    school_name: candidate.sub_title,
                                    patrol_name: '',
                                    photo_url: candidate.photo_url,
                                    scanned_at_formatted: 'Baru saja'
                                };
                                this.playAudioFeedback(true);
                                this.fetchLogs();
                            } else {
                                alert(data.message || 'Gagal melakukan presensi manual.');
                            }
                        } catch (e) {
                            alert('Terjadi kesalahan jaringan.');
                        }
                    },

                    async fetchLogs() {
                        try {
                            const res = await fetch(config.logsUrl, {
                                headers: { 'Accept': 'application/json' }
                            });
                            const data = await res.json();
                            if (data.success) {
                                this.attendanceLogs = data.attendances || [];
                                this.totalAttended = data.total || 0;
                            }
                        } catch (e) {
                            console.error('Error fetching logs:', e);
                        }
                    },

                    openEditModal(item) {
                        this.editingItem = item;
                        this.editForm.status = item.status || 'present';
                        this.editForm.notes = item.notes || '';
                        this.showEditModal = true;
                    },

                    async saveEditAttendance() {
                        if (!this.editingItem) return;
                        try {
                            const res = await fetch(`/portal/attendance/records/${this.editingItem.id}`, {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': config.csrfToken,
                                },
                                body: JSON.stringify({
                                    status: this.editForm.status,
                                    notes: this.editForm.notes
                                })
                            });
                            const data = await res.json();
                            if (data.success) {
                                this.showEditModal = false;
                                this.fetchLogs();
                            } else {
                                alert(data.message || 'Gagal menyimpan perubahan.');
                            }
                        } catch (e) {
                            alert('Terjadi kesalahan jaringan.');
                        }
                    },

                    async deleteAttendanceItem() {
                        if (!this.editingItem) return;
                        if (!(await window.appConfirm(`Yakin ingin membatalkan catatan presensi ${this.editingItem.name}?`))) return;
                        try {
                            const res = await fetch(`/portal/attendance/records/${this.editingItem.id}`, {
                                method: 'DELETE',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': config.csrfToken,
                                }
                            });
                            const data = await res.json();
                            if (data.success) {
                                this.showEditModal = false;
                                this.fetchLogs();
                            } else {
                                alert(data.message || 'Gagal membatalkan presensi.');
                            }
                        } catch (e) {
                            alert('Terjadi kesalahan jaringan.');
                        }
                    }
                });
        </script>
    @endassets

    {{-- Keyframes untuk laser scan animasi --}}
        <style>
            @keyframes scan {
                0%, 100% {
                    top: 5%;
                }
                50% {
                    top: 90%;
                }
            }
        </style>
</div>

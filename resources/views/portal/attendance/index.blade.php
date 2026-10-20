<x-layouts::app :title="__('Portal Presensi & Scanner QR Code')">
    <div class="space-y-6">
        {{-- Header Halaman --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100 flex items-center gap-2.5">
                    <span class="inline-flex size-9 items-center justify-center rounded-xl bg-amber-600/10 text-amber-700 dark:bg-amber-400/10 dark:text-amber-400">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="18" height="18" x="3" y="3" rx="2"/><path d="M7 7h.01"/><path d="M17 7h.01"/><path d="M7 17h.01"/><path d="M17 17h.01"/>
                        </svg>
                    </span>
                    Portal Presensi & Scanner Pos LT III
                </h1>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    Pindai kartu identitas digital B2 peserta regu, pembina pendamping, dan aparatur lomba secara realtime di setiap pos kegiatan.
                </p>
            </div>
            
            <div class="flex items-center gap-2">
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.schedules.index') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-zinc-200 bg-white px-3.5 py-2 text-xs font-semibold text-zinc-700 shadow-xs hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                        <svg class="size-4 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>
                        Kelola Jadwal & Sesi
                    </a>
                @endif
            </div>
        </div>

        {{-- Kartu Statistik --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="rounded-2xl border border-zinc-200/80 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Total Sesi Presensi</span>
                    <span class="size-8 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/></svg>
                    </span>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-2xl font-black text-zinc-900 dark:text-zinc-100">{{ $stats['total_sessions'] }}</span>
                    <span class="text-xs text-zinc-500">sesi terdaftar</span>
                </div>
            </div>

            <div class="rounded-2xl border border-emerald-200/80 bg-emerald-50/40 p-4 shadow-xs dark:border-emerald-950/60 dark:bg-emerald-950/20">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-emerald-800 dark:text-emerald-400 uppercase tracking-wider">Sesi Buka / Aktif</span>
                    <span class="size-8 rounded-lg bg-emerald-600/10 text-emerald-700 dark:text-emerald-400 flex items-center justify-center">
                        <span class="relative flex size-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full size-2.5 bg-emerald-600"></span>
                        </span>
                    </span>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-2xl font-black text-emerald-900 dark:text-emerald-200">{{ $stats['active_sessions'] }}</span>
                    <span class="text-xs text-emerald-700 dark:text-emerald-400 font-medium">siap dipindai</span>
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-200/80 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Presensi Hari Ini</span>
                    <span class="size-8 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>
                    </span>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-2xl font-black text-zinc-900 dark:text-zinc-100">{{ $stats['total_attendances_today'] }}</span>
                    <span class="text-xs text-zinc-500">kartu terverifikasi</span>
                </div>
            </div>
        </div>

        {{-- Filter & Pencarian Sesi --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
            <form method="GET" action="{{ route('attendance.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                <div class="sm:col-span-8">
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama pos, cabang lomba, atau arena..." class="w-full rounded-xl border border-zinc-200 bg-zinc-50/50 pl-9 pr-3.5 py-2 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-amber-600 focus:bg-white focus:outline-none dark:border-zinc-700 dark:bg-zinc-800/50 dark:text-zinc-100">
                        <svg class="pointer-events-none absolute left-3 top-2.5 size-4 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </div>
                </div>

                <div class="sm:col-span-3">
                    <select name="status" class="w-full rounded-xl border border-zinc-200 bg-zinc-50/50 px-3 py-2 text-sm text-zinc-800 focus:border-amber-600 focus:bg-white focus:outline-none dark:border-zinc-700 dark:bg-zinc-800/50 dark:text-zinc-200">
                        <option value="">Semua Status</option>
                        <option value="active" @selected(request('status') === 'active')>Hanya Sesi Aktif</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Sesi Ditutup</option>
                    </select>
                </div>

                <div class="sm:col-span-1 flex gap-1">
                    <button type="submit" class="w-full rounded-xl bg-amber-600 px-3 py-2 text-xs font-bold text-white hover:bg-amber-700 flex items-center justify-center">
                        Cari
                    </button>
                </div>
            </form>
        </div>

        {{-- Daftar Grid Sesi Presensi --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($sessions as $session)
                <div class="group relative flex flex-col justify-between rounded-2xl border transition-all duration-200 p-5 {{ $session->is_active ? 'border-amber-500/60 bg-white shadow-sm ring-1 ring-amber-500/20 dark:border-amber-600/50 dark:bg-zinc-900' : 'border-zinc-200 bg-white/70 opacity-90 dark:border-zinc-800 dark:bg-zinc-900/60' }}">
                    <div>
                        {{-- Status Pill & Session Type --}}
                        <div class="flex items-center justify-between gap-2 mb-3 flex-wrap">
                            <div class="flex items-center gap-1.5">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold tracking-wide uppercase {{ $session->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' }}">
                                    @if($session->is_active)
                                        <span class="size-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Sesi Aktif
                                    @else
                                        <span class="size-1.5 rounded-full bg-zinc-400"></span>
                                        Sesi Ditutup
                                    @endif
                                </span>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold tracking-wide uppercase border {{ $session->scheduleStatusBadgeClasses() }}">
                                    {{ $session->scheduleStatusLabel() }}
                                </span>
                            </div>

                            <div class="flex items-center gap-1.5">
                                @if($session->schedule?->isPatrol())
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300">
                                        Regu Utuh • PINRU/WAPINRU
                                    </span>
                                @elseif($session->schedule?->isGroup())
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-950/70 dark:text-indigo-300">
                                        Kelompok • Wajib Seluruh Peserta
                                    </span>
                                @elseif($session->schedule?->isIndividual())
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-950/70 dark:text-blue-300">
                                        Individu • Perwakilan Regu
                                    </span>
                                @endif
                                <span class="text-[11px] font-semibold text-zinc-500 dark:text-zinc-400">
                                    {{ $session->sessionTypeLabel() }}
                                </span>
                            </div>
                        </div>

                        {{-- Judul Sesi --}}
                        <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 group-hover:text-amber-700 dark:group-hover:text-amber-400 transition-colors">
                            {{ $session->title }}
                        </h2>

                        {{-- Cabang Lomba & Arena --}}
                        <div class="mt-2.5 space-y-1.5 text-xs text-zinc-600 dark:text-zinc-400">
                            @if($session->schedule?->competition)
                                <div class="flex items-center gap-1.5 font-medium text-zinc-800 dark:text-zinc-200">
                                    <svg class="size-3.5 text-amber-600 dark:text-amber-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg>
                                    <span class="truncate">{{ $session->schedule->competition->title }}</span>
                                </div>
                            @endif

                            <div class="flex items-center gap-1.5">
                                <svg class="size-3.5 text-zinc-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                <span class="truncate">{{ $session->schedule?->arena_location ?: 'Pos Lapangan Terbuka' }}</span>
                            </div>

                            @if($session->schedule?->start_time)
                                <div class="flex items-center gap-1.5">
                                    <svg class="size-3.5 text-zinc-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    <span>{{ $session->schedule->start_time->translatedFormat('d M Y, H:i') }} WIB</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Footer Card: Check-in Count & Button --}}
                    <div class="mt-5 pt-4 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5">
                            <span class="inline-flex size-7 items-center justify-center rounded-lg bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400 text-xs font-black">
                                {{ $session->attendances->count() }}
                            </span>
                            <span class="text-xs text-zinc-500">hadir</span>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('attendance.scanner', $session) }}" class="inline-flex items-center gap-1.5 rounded-xl px-3.5 py-2 text-xs font-bold transition-all shadow-xs {{ $session->is_active ? 'bg-amber-600 text-white hover:bg-amber-700 shadow-amber-600/20' : 'bg-zinc-800 text-zinc-200 hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600' }}">
                                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/></svg>
                                Buka Scanner
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-zinc-300 p-12 text-center dark:border-zinc-700">
                    <svg class="mx-auto size-12 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="m9 9 6 6"/><path d="m15 9-6 6"/></svg>
                    <h3 class="mt-4 text-sm font-semibold text-zinc-900 dark:text-zinc-100">Belum ada sesi presensi</h3>
                    <p class="mt-1 text-xs text-zinc-500">Sesi presensi akan dibuat otomatis saat jadwal lomba ditambahkan di menu Jadwal & Penugasan Juri.</p>
                </div>
            @endforelse
        </div>

        {{-- Navigasi Pagination --}}
        @if($sessions->hasPages())
            <div class="mt-4">
                {{ $sessions->links() }}
            </div>
        @endif
    </div>
</x-layouts::app>

<x-layouts::public title="Jadwal Kegiatan & Lomba">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        {{-- Breadcrumb & Title --}}
        <x-heading-section
            class="mb-6"
            title="Jadwal Kegiatan & Arena Lomba"
            description="Waktu resmi mengacu pada server Asia/Jakarta (WIB). Presensi dibuka paling cepat 30 menit sebelum waktu lomba dimulai."
        >
            <x-slot:breadcrumbs>
                <a href="{{ route('home') }}" wire:navigate class="hover:underline">Beranda</a>
                <span class="mx-2">&rsaquo;</span>
                <span class="text-zinc-800 dark:text-zinc-200">Jadwal</span>
            </x-slot:breadcrumbs>
        </x-heading-section>

        {{-- Filter Hari / Tanggal --}}
        @if($dates->count())
            <div class="mb-6 flex flex-wrap items-center gap-2">
                <span class="text-xs font-semibold text-zinc-500 mr-1">Pilih Tanggal:</span>
                <a href="{{ route('schedules') }}" wire:navigate class="rounded-xl px-3 py-1.5 text-xs font-medium transition-colors {{ !request('day') ? 'bg-amber-600 text-white' : 'border border-zinc-300 bg-white text-zinc-700 hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300' }}">
                    Semua Tanggal
                </a>
                @foreach($dates as $date)
                    <a href="{{ route('schedules', ['day' => $date]) }}" wire:navigate class="rounded-xl px-3 py-1.5 text-xs font-medium transition-colors {{ request('day') === $date ? 'bg-amber-600 text-white' : 'border border-zinc-300 bg-white text-zinc-700 hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300' }}">
                        {{ \Illuminate\Support\Carbon::parse($date)->translatedFormat('d M Y') }}
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Schedules Table / Cards --}}
        <div class="space-y-4">
            @forelse($schedules as $schedule)
                <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm transition-all hover:border-amber-400 dark:border-zinc-800 dark:bg-zinc-900 sm:p-6">
                    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono text-xs font-bold text-amber-700 dark:text-amber-400">
                                    {{ $schedule->competition?->code }}
                                </span>
                                <span class="text-zinc-300 dark:text-zinc-700">&bull;</span>
                                <span class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">
                                    {{ $schedule->competition?->title }}
                                </span>
                                <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-[10px] font-semibold {{ $schedule->participantTypeBadgeClasses() }}">
                                    {{ $schedule->participantTypeLabel() }}
                                </span>
                            </div>

                            <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100 sm:text-lg">
                                {{ $schedule->session_name }}
                            </h3>

                            <div class="flex flex-wrap items-center gap-4 text-xs text-zinc-500 dark:text-zinc-400 pt-1">
                                <span class="flex items-center gap-1.5">
                                    <svg class="size-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    {{ $schedule->arena_location }}
                                </span>
                                <span class="flex items-center gap-1.5 font-mono">
                                    <svg class="size-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $schedule->start_time->format('H:i') }} - {{ $schedule->end_time->format('H:i') }} WIB
                                </span>
                                <span class="text-zinc-400">
                                    ({{ \Carbon\Carbon::parse($schedule->start_time)->locale('id')->isoFormat('dddd, D MMMM Y') }})
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 self-start sm:self-center">
                            <span class="inline-flex items-center rounded-lg border px-3 py-1 text-xs font-semibold {{ $schedule->statusBadge() }}">
                                {{ $schedule->statusLabel() }}
                            </span>

                            <div class="text-right text-[11px] text-zinc-400">
                                <span class="block">Presensi Dibuka:</span>
                                <span class="font-mono font-medium text-amber-700 dark:text-amber-400">
                                    {{ \Carbon\Carbon::parse($schedule->start_time)->subMinutes(30)->format('H:i') }} WIB
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-zinc-300 p-12 text-center text-sm text-zinc-500 dark:border-zinc-800">
                    Tidak ada jadwal yang terdaftar untuk filter tanggal ini.
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $schedules->links() }}
        </div>
    </div>
</x-layouts::public>

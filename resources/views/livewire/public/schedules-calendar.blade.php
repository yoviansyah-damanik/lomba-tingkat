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

        <x-slot:actions>
            <div class="flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-3 py-1.5 text-xs text-zinc-600 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400">
                <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Waktu Resmi: <strong>WIB</strong></span>
            </div>
        </x-slot:actions>
    </x-heading-section>

    {{-- Interactive Controls: Filter Tanggal & Search Bar (Livewire 4) --}}
    <div class="mb-6 flex flex-col gap-4 rounded-2xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
        {{-- Tanggal Tabs --}}
        @if($dates->count())
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-semibold text-zinc-500 mr-1">Pilih Tanggal:</span>
                <button
                    type="button"
                    wire:click="clearDate"
                    class="rounded-xl px-3 py-1.5 text-xs font-semibold transition-colors cursor-pointer {{ $selectedDate === '' ? 'bg-amber-600 text-white shadow-xs' : 'border border-zinc-200 bg-white text-zinc-700 hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' }}">
                    Semua Tanggal
                </button>
                @foreach($dates as $date)
                    <button
                        type="button"
                        wire:click="selectDate('{{ $date }}')"
                        class="rounded-xl px-3 py-1.5 text-xs font-semibold transition-colors cursor-pointer {{ $selectedDate === $date ? 'bg-amber-600 text-white shadow-xs' : 'border border-zinc-200 bg-white text-zinc-700 hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' }}">
                        {{ \Illuminate\Support\Carbon::parse($date)->translatedFormat('d M Y') }}
                    </button>
                @endforeach
            </div>
        @endif

        {{-- Search & Format Filter --}}
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 pt-2 border-t border-zinc-100 dark:border-zinc-800">
            <div class="relative flex-1">
                <flux:icon name="magnifying-glass" class="absolute left-3.5 top-2.5 size-4 text-zinc-400" />
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari sesi, arena, atau mata lomba..."
                    class="w-full rounded-xl border border-zinc-200 bg-zinc-50 pl-10 pr-4 py-2 text-xs sm:text-sm text-zinc-900 focus:border-amber-500 focus:bg-white focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                />
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <select
                    wire:model.live="genderFilter"
                    class="rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs font-medium text-zinc-800 focus:border-amber-500 focus:bg-white focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                    <option value="all">Semua Kategori</option>
                    <option value="boys">Khusus Putra</option>
                    <option value="girls">Khusus Putri</option>
                </select>

                <select
                    wire:model.live="typeFilter"
                    class="rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs font-medium text-zinc-800 focus:border-amber-500 focus:bg-white focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                    <option value="all">Semua Tipe Sesi</option>
                    <option value="patrol">Regu</option>
                    <option value="group">Kelompok</option>
                    <option value="individual">Individu</option>
                </select>

                @if($search || $selectedDate || $typeFilter !== 'all' || $genderFilter !== 'all')
                    <button
                        wire:click="$set('search', ''); $set('selectedDate', ''); $set('typeFilter', 'all'); $set('genderFilter', 'all');"
                        class="rounded-xl border border-zinc-200 px-3 py-2 text-xs font-semibold text-zinc-600 hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 cursor-pointer">
                        Atur Ulang
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Schedules Cards --}}
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
                            <span class="inline-flex items-center gap-1 rounded-md border px-2 py-0.5 text-[10px] font-semibold {{ $schedule->genderBadgeClasses() }}">
                                @if($schedule->effectiveGenderCategory() === 'boys')
                                    <svg class="size-2.5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                @elseif($schedule->effectiveGenderCategory() === 'girls')
                                    <svg class="size-2.5 text-pink-600 dark:text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                @else
                                    <svg class="size-2.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                @endif
                                {{ $schedule->genderLabel() }}
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
                Tidak ada jadwal yang terdaftar untuk filter ini.
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $schedules->links() }}
    </div>
</div>

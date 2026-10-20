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
    <div class="mb-6 space-y-3 rounded-2xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
        {{-- Tanggal: satu baris yang dapat digeser pada layar kecil --}}
        @if($dates->count())
            <div class="-mx-4 overflow-x-auto px-4 pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                <div class="flex w-max items-center gap-2">
                    <flux:button
                        size="sm"
                        variant="{{ $selectedDate === '' ? 'primary' : 'subtle' }}"
                        wire:click="clearDate"
                        class="shrink-0"
                    >
                        Semua Tanggal
                    </flux:button>
                    @foreach($dates as $date)
                        <flux:button
                            size="sm"
                            variant="{{ $selectedDate === $date ? 'primary' : 'subtle' }}"
                            wire:click="selectDate('{{ $date }}')"
                            wire:key="date-{{ $date }}"
                            class="shrink-0"
                        >
                            {{ \Illuminate\Support\Carbon::parse($date)->translatedFormat('d M Y') }}
                        </flux:button>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Pencarian & filter --}}
        <div class="space-y-2 border-t border-zinc-100 pt-3 dark:border-zinc-800">
            <div class="relative">
                <flux:icon name="magnifying-glass" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
                <input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari sesi, arena, atau mata lomba..."
                    class="w-full rounded-xl border border-zinc-200 bg-zinc-50 py-2.5 pl-10 pr-4 text-base text-zinc-900 focus:border-amber-500 focus:bg-white focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 sm:text-sm"
                />
            </div>

            <div class="grid grid-cols-2 gap-2 sm:flex sm:items-center">
                <select
                    wire:model.live="genderFilter"
                    class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2.5 text-sm font-medium text-zinc-800 focus:border-amber-500 focus:bg-white focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 sm:w-auto sm:py-2">
                    <option value="all">Semua Kategori</option>
                    <option value="boys">Khusus Putra</option>
                    <option value="girls">Khusus Putri</option>
                </select>

                <select
                    wire:model.live="typeFilter"
                    class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2.5 text-sm font-medium text-zinc-800 focus:border-amber-500 focus:bg-white focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 sm:w-auto sm:py-2">
                    <option value="all">Semua Tipe Sesi</option>
                    <option value="patrol">Regu</option>
                    <option value="group">Kelompok</option>
                    <option value="individual">Individu</option>
                </select>

                @if($search || $selectedDate || $typeFilter !== 'all' || $genderFilter !== 'all')
                    <flux:button
                        size="sm"
                        variant="subtle"
                        icon="x-mark"
                        wire:click="$set('search', ''); $set('selectedDate', ''); $set('typeFilter', 'all'); $set('genderFilter', 'all');"
                        class="col-span-2 sm:col-span-1"
                    >
                        Atur Ulang
                    </flux:button>
                @endif
            </div>
        </div>
    </div>

    {{-- Daftar sesi --}}
    <div class="space-y-3 sm:space-y-4">
        @forelse($schedules as $schedule)
            <x-schedule-card :schedule="$schedule" wire:key="schedule-{{ $schedule->id }}" />
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

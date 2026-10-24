@php
    $statusStyles = [
        'present' => ['label' => 'Hadir', 'badge' => 'emerald', 'icon' => 'check-circle'],
        'open' => ['label' => 'Presensi Dibuka', 'badge' => 'amber', 'icon' => 'play-circle'],
        'upcoming' => ['label' => 'Akan Datang', 'badge' => 'blue', 'icon' => 'clock'],
        'absent' => ['label' => 'Tidak Tercatat', 'badge' => 'red', 'icon' => 'x-circle'],
    ];
@endphp

<div class="flex flex-col gap-4">
    @if (auth()->user()->isJudge())
        <div class="flex flex-wrap gap-2">
            <flux:button size="sm" icon="scale" wire:click="$set('tab', 'duties')" :variant="$this->activeTab === 'duties' ? 'primary' : 'subtle'">Tugas Penjurian</flux:button>
            <flux:button size="sm" icon="clipboard-document-check" wire:click="$set('tab', 'presence')" :variant="$this->activeTab === 'presence' ? 'primary' : 'subtle'">Presensi Saya</flux:button>
        </div>
    @endif

    @if ($this->activeTab === 'duties')
        @include('livewire.portal.partials.judge-duties')
    @else
    {{-- Ringkasan --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <x-stat-card label="Hadir" value="{{ $this->summary['present'] }}" description="Presensi tercatat" color="neutral" />
        <x-stat-card label="Dibuka" value="{{ $this->summary['open'] }}" description="Siap dipindai" color="amber" />
        <x-stat-card label="Akan Datang" value="{{ $this->summary['upcoming'] }}" description="Belum dibuka" color="blue" />
        <x-stat-card label="Tidak Tercatat" value="{{ $this->summary['absent'] }}" description="Sesi sudah lewat" color="purple" />
    </div>

    {{-- Filter --}}
    <div class="flex flex-wrap gap-2">
        @foreach (['upcoming' => 'Berlangsung & Akan Datang', 'history' => 'Riwayat', 'all' => 'Semua'] as $key => $label)
            <flux:button size="sm" wire:click="$set('filter', '{{ $key }}')" :variant="$filter === '{{ $key }}' ? 'primary' : 'subtle'">
                {{ $label }}
            </flux:button>
        @endforeach
    </div>

    @forelse ($this->grouped as $date => $items)
        <div wire:key="date-{{ $date }}" class="space-y-2">
            <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-500">
                {{ $date === 'Tanpa tanggal' ? $date : \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}
            </h3>

            @foreach ($items as $item)
                @php $style = $statusStyles[$item['status']] ?? $statusStyles['upcoming']; @endphp
                <div wire:key="item-{{ $item['id'] }}" class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <flux:badge size="sm" :color="$item['kind'] === 'lomba' ? 'amber' : 'zinc'">
                                    {{ $item['kind'] === 'lomba' ? 'Lomba' : 'Kegiatan' }}
                                </flux:badge>
                                <span class="text-[11px] text-zinc-500">{{ $item['subtitle'] }}</span>
                            </div>
                            <h4 class="mt-1 text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ $item['title'] }}</h4>

                            <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-zinc-500">
                                @if ($item['start'])
                                    <span class="inline-flex items-center gap-1">
                                        <flux:icon name="clock" class="size-3.5" />
                                        {{ $item['start']->timezone('Asia/Jakarta')->format('H:i') }}
                                        @if ($item['end'])
                                            &ndash; {{ $item['end']->timezone('Asia/Jakarta')->format('H:i') }}
                                        @endif
                                        WIB
                                    </span>
                                @endif
                                @if ($item['location'])
                                    <span class="inline-flex items-center gap-1">
                                        <flux:icon name="map-pin" class="size-3.5" />
                                        {{ $item['location'] }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <flux:badge :color="$style['badge']" :icon="$style['icon']">{{ $style['label'] }}</flux:badge>
                    </div>

                    @if ($item['scanned_at'] || $item['note'])
                        <div class="mt-3 border-t border-zinc-100 pt-2 text-[11px] text-zinc-500 dark:border-zinc-800">
                            @if ($item['scanned_at'])
                                <span>Tercatat {{ $item['scanned_at']->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') }} WIB
                                    @if ($item['scanned_by']) oleh {{ $item['scanned_by'] }} @endif
                                </span>
                            @endif
                            @if ($item['note'])
                                <span class="block">{{ $item['note'] }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @empty
        <div class="rounded-2xl border border-zinc-200 bg-white p-10 text-center shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
            <flux:icon name="calendar-days" class="mx-auto mb-2 size-10 text-zinc-400" />
            <p class="text-sm font-bold text-zinc-800 dark:text-zinc-200">Belum ada jadwal pada kategori ini</p>
            <p class="mt-1 text-xs text-zinc-400">Jadwal akan tampil setelah panitia menerbitkan sesi presensi untuk Anda.</p>
        </div>
    @endforelse
    @endif
</div>

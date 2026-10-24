{{-- Jadwal lomba yang ditunjuk kepada juri; variabel: $this->duties --}}
@php
    $phaseStyles = [
        'ongoing' => ['label' => 'Sedang Berlangsung', 'badge' => 'amber', 'icon' => 'play-circle'],
        'upcoming' => ['label' => 'Akan Datang', 'badge' => 'blue', 'icon' => 'clock'],
        'finished' => ['label' => 'Selesai', 'badge' => 'zinc', 'icon' => 'check-circle'],
    ];
    $dutyGroups = $this->duties->groupBy(fn (array $duty) => $duty['start']?->timezone('Asia/Jakarta')->toDateString() ?? 'Tanpa tanggal');
@endphp

<div class="rounded-2xl border border-amber-200/70 bg-amber-50/50 p-4 text-xs text-amber-900 dark:border-amber-500/20 dark:bg-amber-950/20 dark:text-amber-200">
    Daftar lomba berikut adalah jadwal yang <strong>menunjuk Anda sebagai juri</strong>. Penilaian dilakukan di menu Cockpit Penjurian.
</div>

@forelse ($dutyGroups as $date => $duties)
    <div wire:key="duty-date-{{ $date }}" class="space-y-2">
        <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-500">
            {{ $date === 'Tanpa tanggal' ? $date : \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}
        </h3>

        @foreach ($duties as $duty)
            @php $style = $phaseStyles[$duty['phase']]; @endphp
            <div wire:key="duty-{{ $duty['id'] }}" class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <flux:badge size="sm" color="purple">{{ $duty['role'] }}</flux:badge>
                            <flux:badge size="sm" :color="$duty['confirmed'] ? 'emerald' : 'zinc'">
                                {{ $duty['confirmed'] ? 'Penugasan Terkonfirmasi' : 'Menunggu Konfirmasi' }}
                            </flux:badge>
                        </div>
                        <h4 class="mt-1 text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ $duty['competition'] }}</h4>
                        <p class="text-[11px] text-zinc-500">{{ $duty['session_name'] }} &bull; {{ $duty['category'] }} &bull; {{ $duty['participant_type'] }}</p>

                        <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-zinc-500">
                            @if ($duty['start'])
                                <span class="inline-flex items-center gap-1">
                                    <flux:icon name="clock" class="size-3.5" />
                                    {{ $duty['start']->timezone('Asia/Jakarta')->format('H:i') }}
                                    @if ($duty['end'])
                                        &ndash; {{ $duty['end']->timezone('Asia/Jakarta')->format('H:i') }}
                                    @endif
                                    WIB
                                </span>
                            @endif
                            @if ($duty['location'])
                                <span class="inline-flex items-center gap-1">
                                    <flux:icon name="map-pin" class="size-3.5" />
                                    {{ $duty['location'] }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <flux:badge :color="$style['badge']" :icon="$style['icon']">{{ $style['label'] }}</flux:badge>
                </div>
            </div>
        @endforeach
    </div>
@empty
    <div class="rounded-2xl border border-zinc-200 bg-white p-10 text-center shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
        <flux:icon name="scale" class="mx-auto mb-2 size-10 text-zinc-400" />
        <p class="text-sm font-bold text-zinc-800 dark:text-zinc-200">Belum ada penunjukan sebagai juri</p>
        <p class="mt-1 text-xs text-zinc-400">Jadwal akan tampil setelah Administrator menugaskan Anda pada sesi lomba.</p>
    </div>
@endforelse

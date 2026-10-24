{{-- Variabel: $scoutSiaga, $scoutPenggalang --}}
@php
    $filled = fn ($items) => collect($items ?? [])->map(fn ($v, $i) => ['no' => $i + 1, 'text' => trim((string) $v)])->filter(fn ($row) => $row['text'] !== '');

    $siagaLevels = ['mula' => 'Mula', 'bantu' => 'Bantu', 'tata' => 'Tata', 'garuda' => 'Garuda'];
    $penggalangLevels = ['ramu' => 'Ramu', 'rakit' => 'Rakit', 'terap' => 'Terap', 'garuda' => 'Garuda'];

    $groups = [
        [
            'title' => 'Golongan Siaga',
            'data' => $scoutSiaga,
            'levels' => $siagaLevels,
            'activityLabel' => 'Giat Siaga yang Telah Diikuti',
            'tkk' => ['Tanda Kecakapan Khusus (TKK) Siaga' => $filled($scoutSiaga['tkk'] ?? [])],
        ],
        [
            'title' => 'Golongan Penggalang',
            'data' => $scoutPenggalang,
            'levels' => $penggalangLevels,
            'activityLabel' => 'Giat Penggalang yang Telah Diikuti',
            'tkk' => [
                'TKK Purwa' => $filled($scoutPenggalang['tkk_purwa'] ?? []),
                'TKK Madya' => $filled($scoutPenggalang['tkk_madya'] ?? []),
                'TKK Utama' => $filled($scoutPenggalang['tkk_utama'] ?? []),
            ],
        ],
    ];
@endphp

{{-- Riwayat kepramukaan lengkap: tingkat, giat, dan TKK untuk golongan Siaga dan Penggalang --}}
<div class="space-y-4 text-xs">
    @foreach ($groups as $group)
        @php
            $data = $group['data'];
            $activities = $filled($data['activities'] ?? []);
        @endphp

        <div class="rounded-xl border border-zinc-200 bg-zinc-50/60 p-4 dark:border-zinc-800 dark:bg-zinc-800/30">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <h5 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ $group['title'] }}</h5>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-zinc-600 dark:text-zinc-400">
                    <span>Tahun: <span class="font-mono font-semibold text-zinc-800 dark:text-zinc-200">{{ ($data['year'] ?? '') !== '' ? $data['year'] : '-' }}</span></span>
                    <span>No. Gudep: <span class="font-mono font-semibold text-zinc-800 dark:text-zinc-200">{{ ($data['group_number'] ?? '') !== '' ? $data['group_number'] : '-' }}</span></span>
                    <span>Pangkalan: <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ ($data['pangkalan'] ?? '') !== '' ? $data['pangkalan'] : '-' }}</span></span>
                </div>
            </div>

            {{-- Tingkatan --}}
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                @foreach ($group['levels'] as $key => $label)
                    @php $value = trim((string) ($data['levels'][$key] ?? '')); @endphp
                    <div class="rounded-lg border border-zinc-200 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-900">
                        <span class="block text-[10px] font-semibold uppercase tracking-wider text-zinc-400">{{ $label }}</span>
                        <span class="font-semibold {{ $value !== '' ? 'text-zinc-800 dark:text-zinc-200' : 'text-zinc-400' }}">{{ $value !== '' ? $value : '-' }}</span>
                    </div>
                @endforeach
            </div>

            {{-- Giat yang telah diikuti --}}
            <div class="mt-4">
                <span class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">{{ $group['activityLabel'] }}</span>
                @if ($activities->isEmpty())
                    <p class="text-[11px] italic text-zinc-400">Belum ada giat yang dicatat.</p>
                @else
                    <ol class="space-y-1">
                        @foreach ($activities as $row)
                            <li class="flex gap-2">
                                <span class="w-5 shrink-0 text-right font-mono font-bold text-zinc-400">{{ $row['no'] }}.</span>
                                <span class="text-zinc-800 dark:text-zinc-200">{{ $row['text'] }}</span>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>

            {{-- Tanda Kecakapan Khusus --}}
            <div class="mt-4 grid grid-cols-1 gap-4 {{ count($group['tkk']) > 1 ? 'md:grid-cols-3' : '' }}">
                @foreach ($group['tkk'] as $label => $rows)
                    <div>
                        <span class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">{{ $label }}</span>
                        @if ($rows->isEmpty())
                            <p class="text-[11px] italic text-zinc-400">Belum ada TKK yang dicatat.</p>
                        @else
                            <ol class="space-y-1">
                                @foreach ($rows as $row)
                                    <li class="flex gap-2">
                                        <span class="w-5 shrink-0 text-right font-mono font-bold text-zinc-400">{{ $row['no'] }}.</span>
                                        <span class="text-zinc-800 dark:text-zinc-200">{{ $row['text'] }}</span>
                                    </li>
                                @endforeach
                            </ol>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

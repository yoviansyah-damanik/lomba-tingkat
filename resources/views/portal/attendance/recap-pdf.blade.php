<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Presensi</title>
    <style>
        @page { size: A4 portrait; margin: 1.5cm; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #18181b; margin: 0; }
        table { border-collapse: collapse; width: 100%; }
        .title { text-align: center; margin-top: 14px; font-size: 12px; font-weight: bold; text-transform: uppercase; text-decoration: underline; }
        .subtitle { text-align: center; margin-top: 3px; font-size: 9px; color: #3f3f46; }
        .stats { margin-top: 10px; border: 1px solid #a1a1aa; background: #fafafa; }
        .stats td { text-align: center; padding: 5px 2px; }
        .stats .label { font-size: 7px; text-transform: uppercase; color: #71717a; font-weight: bold; }
        .stats .value { font-size: 11px; font-weight: bold; }
        .data { margin-top: 10px; }
        .data th { background: #f4f4f5; border: 1px solid #a1a1aa; padding: 4px; font-size: 8px; text-align: center; vertical-align: middle; }
        .data td { border: 1px solid #d4d4d8; padding: 3px 4px; font-size: 8px; }
        .center { text-align: center; }
        .zebra { background: #fafafa; }
        .footer { margin-top: 24px; page-break-inside: avoid; }
        .footer td { vertical-align: top; }
        .muted { color: #71717a; font-size: 8px; }
        .sign { width: 220px; text-align: center; font-size: 9px; }
        .note { margin-top: 18px; border-top: 1px solid #d4d4d8; padding-top: 6px; text-align: center; font-size: 7px; color: #71717a; }
    </style>
</head>
<body>
    @php
        // Cadangan bila dirender tanpa pembagian sesi: satu lembar dari data yang diberikan
        $sections = $sections ?? collect([['session' => $selectedSession ?? null, 'number' => 'BAP-LT3/KWARCAB-PSP/'.now()->year.'/001', 'attendances' => $attendances, 'stats' => $stats]]);
    @endphp

    @foreach($sections as $section)
        @php($session = $section['session'])
        <div @if(! $loop->first) style="page-break-before: always;" @endif>
            <x-report.kop />

            <div class="title">Berita Acara &amp; Rekapitulasi Presensi Kegiatan</div>
            <div class="subtitle">Nomor: {{ $section['number'] ?? '-' }}</div>
            <div class="subtitle">
                @if($session)
                    Sesi: <strong>{{ $session->title }}</strong>
                    @if($session->schedule?->arena_location)
                        &bull; Lokasi Pos: <strong>{{ $session->schedule->arena_location }}</strong>
                    @endif
                @else
                    <strong>Seluruh Sesi Kegiatan &amp; Pos Perlombaan</strong>
                @endif
            </div>

            <table class="stats">
                <tr>
                    <td><div class="label">Total Terdata</div><div class="value">{{ $section['stats']['total'] }}</div></td>
                    <td><div class="label">Hadir Tepat</div><div class="value">{{ $section['stats']['present'] }}</div></td>
                    <td><div class="label">Terlambat</div><div class="value">{{ $section['stats']['late'] }}</div></td>
                    <td><div class="label">Izin / Disp</div><div class="value">{{ $section['stats']['excused'] }}</div></td>
                    <td><div class="label">Alpa / Absen</div><div class="value">{{ $section['stats']['absent'] }}</div></td>
                </tr>
            </table>

            <table class="data">
                <thead>
                    <tr>
                        <th class="center" style="width: 20px;">No</th>
                        <th style="width: 60px;">Nomor ID</th>
                        <th>Nama Lengkap</th>
                        <th>Peran / Jabatan</th>
                        <th>Pangkalan / Regu</th>
                        <th class="center" style="width: 48px;">Waktu</th>
                        <th class="center" style="width: 52px;">Status</th>
                        <th>Catatan / Paraf</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($section['attendances'] as $idx => $att)
                        <tr class="{{ $idx % 2 === 1 ? 'zebra' : '' }}">
                            <td class="center">{{ $idx + 1 }}</td>
                            <td>{{ $att->entity_id_number }}</td>
                            <td><strong>{{ $att->entity_name }}</strong></td>
                            <td>{{ $att->entity_role }}</td>
                            <td>{{ $att->entity_group }}</td>
                            <td class="center">{{ $att->scanned_at?->format('H:i') }} WIB</td>
                            <td class="center">
                                {{ match ($att->status) {
                                    'present' => 'HADIR',
                                    'late' => 'TERLAMBAT',
                                    'excused' => 'IZIN',
                                    'absent' => 'ALPA',
                                    default => strtoupper((string) $att->status),
                                } }}
                            </td>
                            <td>{{ $att->notes ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="center" style="padding: 18px; color: #71717a; font-style: italic;">
                                Belum ada data presensi yang tercatat untuk kriteria ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <table class="footer">
                <tr>
                    <td></td>
                    <td class="sign">
                        <div>Padangsidimpuan, {{ now()->setTimezone('Asia/Jakarta')->translatedFormat('d F Y') }}</div>
                        <div style="font-weight: bold; margin-top: 3px;">Ketua Panitia Pelaksana LT III</div>
                        <x-report.signatory :space="64" :name="$event?->signatory('committee')['name']" :nta="$event?->signatory('committee')['nta']" />
                    </td>
                </tr>
            </table>
        </div>
    @endforeach
</body>
</html>

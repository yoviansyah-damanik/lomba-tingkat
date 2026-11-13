<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Presensi</title>
    <style>
        @page { size: A4 portrait; margin: 15mm 15mm 28mm 15mm; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #18181b; margin: 0; }
        table { border-collapse: collapse; width: 100%; page-break-inside: auto; }
        tr { page-break-inside: avoid; page-break-after: auto; }
        thead { display: table-header-group; }
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
        $cols = $cols ?? ['id', 'name', 'role', 'group', 'time', 'status', 'notes'];
        $showStats = $showStats ?? true;
        $showSignature = $showSignature ?? true;
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

            @if($showStats)
            <table class="stats">
                <tr>
                    <td><div class="label">Total Terdata</div><div class="value">{{ $section['stats']['total'] }}</div></td>
                    <td><div class="label">Hadir Tepat</div><div class="value">{{ $section['stats']['present'] }}</div></td>
                    <td><div class="label">Terlambat</div><div class="value">{{ $section['stats']['late'] }}</div></td>
                    <td><div class="label">Izin / Disp</div><div class="value">{{ $section['stats']['excused'] }}</div></td>
                    <td><div class="label">Alpa / Absen</div><div class="value">{{ $section['stats']['absent'] }}</div></td>
                </tr>
            </table>
            @endif

            <table class="data">
                <thead>
                    <tr>
                        <th class="center" style="width: 20px;">No</th>
                        @if(in_array('id', $cols)) <th style="width: 60px;">Nomor ID</th> @endif
                        @if(in_array('name', $cols)) <th>Nama Lengkap</th> @endif
                        @if(in_array('role', $cols)) <th>Peran / Jabatan</th> @endif
                        @if(in_array('group', $cols)) <th>Pangkalan / Regu</th> @endif
                        @if(in_array('time', $cols)) <th class="center" style="width: 48px;">Waktu</th> @endif
                        @if(in_array('status', $cols)) <th class="center" style="width: 52px;">Status</th> @endif
                        @if(in_array('notes', $cols)) <th>Catatan / Paraf</th> @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($section['attendances'] as $idx => $att)
                        <tr class="{{ $idx % 2 === 1 ? 'zebra' : '' }}">
                            <td class="center">{{ $idx + 1 }}</td>
                            @if(in_array('id', $cols)) <td>{{ $att->entity_id_number }}</td> @endif
                            @if(in_array('name', $cols)) <td><strong>{{ $att->entity_name }}</strong></td> @endif
                            @if(in_array('role', $cols)) <td>{{ $att->entity_role }}</td> @endif
                            @if(in_array('group', $cols)) <td>{{ $att->entity_group }}</td> @endif
                            @if(in_array('time', $cols)) <td class="center">{{ $att->scanned_at?->format('H:i') }} WIB</td> @endif
                            @if(in_array('status', $cols))
                                <td class="center">
                                    {{ match ($att->status) {
                                        'present' => 'HADIR',
                                        'late' => 'TERLAMBAT',
                                        'excused' => 'IZIN',
                                        'absent' => 'ALPA',
                                        default => strtoupper((string) $att->status),
                                    } }}
                                </td>
                            @endif
                            @if(in_array('notes', $cols)) <td>{{ $att->notes ?: '-' }}</td> @endif
                        </tr>
                    @empty
                        @php($colCount = 1 + count($cols))
                        <tr>
                            <td colspan="{{ $colCount }}" class="center" style="padding: 18px; color: #71717a; font-style: italic;">
                                Belum ada data presensi yang tercatat untuk kriteria ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($showSignature)
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
            @endif
        </div>
    @endforeach
</body>
</html>

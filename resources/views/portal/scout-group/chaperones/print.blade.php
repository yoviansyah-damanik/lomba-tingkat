<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BIODATA PEMBINA PENDAMPING - {{ $chaperone->full_name }} ({{ $scoutGroup->school_name }})</title>
    <style>
        body, body * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            color: #000;
        }

        @page {
            size: A4 portrait;
            margin: 15mm 15mm 28mm 15mm;
        }

        body {
            background-color: #fff;
            padding: 0;
            margin: 0;
        }

        .sheet {
            background: #fff;
            width: 100%;
            padding: 0;
        }

        .form-code {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 6px;
        }

        .sheet-title {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            line-height: 1.3;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .sheet-subtitle {
            display: block;
            font-size: 8pt;
            font-weight: normal;
            margin-top: 2px;
        }

        table.form-grid {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
            margin-bottom: 5px;
            page-break-inside: auto;
        }

        table.form-grid tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        table.form-grid thead {
            display: table-header-group;
        }

        table.form-grid th, table.form-grid td {
            border: 1px solid #000;
            padding: 2.5px 5px;
            vertical-align: middle;
        }

        .label-cell {
            background-color: #f3f4f6;
            font-weight: bold;
        }

        .section-header {
            background-color: #e5e7eb;
            font-weight: bold;
            text-align: center;
            font-size: 8.5pt;
            letter-spacing: 0.5px;
            padding: 3px 6px;
        }

        .photo-box {
            width: 2.8cm;
            height: 3.8cm;
            border: 1px solid #000;
            display: block;
            text-align: center;
            font-size: 8pt;
            margin: 0 auto;
            background: #fafafa;
            padding-top: 1.3cm;
        }

        .photo-box img {
            width: 2.8cm;
            height: 3.8cm;
            object-fit: cover;
            display: block;
            margin: 0 auto;
        }

        table.sig-table {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }

        table.sig-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 8pt;
            line-height: 1.4;
        }

        .sig-space {
            height: 50px;
        }
    </style>
</head>
<body>

    @php
        $photoDataUri = null;
        if ($chaperone->photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($chaperone->photo_path)) {
            try {
                $photoContent = \Illuminate\Support\Facades\Storage::disk('public')->get($chaperone->photo_path);
                $mimeExt = strtolower(pathinfo($chaperone->photo_path, PATHINFO_EXTENSION));
                $mime = match($mimeExt) {
                    'png' => 'image/png',
                    'webp' => 'image/webp',
                    default => 'image/jpeg',
                };
                $photoDataUri = 'data:' . $mime . ';base64,' . base64_encode($photoContent);
            } catch (\Throwable $e) {}
        }
    @endphp

    <div class="sheet">
        <x-report.kop />

        {{-- Kode Formulir --}}
        <div class="form-code">FORM : 03</div>

        <div class="sheet-title">
            BIODATA PEMBINA PENDAMPING (BINDAMPING)
            <span class="sheet-subtitle">LOMBA TINGKAT REGU PRAMUKA PENGGALANG TIGA (LT III) KWARCAB KOTA PADANGSIDIMPUAN</span>
        </div>

        {{-- Grid Identitas Utama --}}
        <table class="form-grid">
            <tr>
                <td class="label-cell" style="width: 20%;">Kwartir Daerah</td>
                <td style="width: 30%; font-weight: bold;">SUMATERA UTARA</td>
                <td class="label-cell" style="width: 20%;">Kwartir Cabang</td>
                <td colspan="2" style="font-weight: bold;">KOTA Padangsidimpuan</td>
            </tr>
            <tr>
                <td class="label-cell">GUGUS DEPAN</td>
                <td colspan="4" style="font-weight: bold; font-size: 9pt;">
                    {{ $chaperone->gender === 'male' ? ($scoutGroup->male_group_number ?? $scoutGroup->group_number) : ($scoutGroup->female_group_number ?? $scoutGroup->group_number) }}
                    <span style="font-weight: normal; font-size: 8pt; margin-left: 8px;">
                        ({{ $scoutGroup->school_name }} &bull; {{ $chaperone->gender === 'male' ? 'Pangkalan Putra' : 'Pangkalan Putri' }})
                    </span>
                </td>
            </tr>
            <tr>
                <td class="label-cell">NOMOR ID BINDAMPING</td>
                <td colspan="4" style="font-weight: bold; font-family: monospace; font-size: 9pt; color: #0d9488;">{{ $chaperone->id_number ?: '-' }}</td>
            </tr>
            <tr>
                <td class="label-cell">NAMA LENGKAP</td>
                <td style="font-weight: bold;">{{ $chaperone->full_name }}</td>
                <td class="label-cell">Jenis Kelamin</td>
                <td style="width: 15%;">{{ $chaperone->gender === 'male' ? 'Laki-laki (Putra)' : 'Perempuan (Putri)' }}</td>
                <td rowspan="6" style="width: 32mm; text-align: center; vertical-align: middle; padding: 2px;">
                    @if($photoDataUri)
                        <img src="{{ $photoDataUri }}" alt="Pas Foto 3x4" style="width: 2.8cm; height: 3.8cm; object-fit: cover; border: 1px solid #000;" />
                    @else
                        <div class="photo-box">
                            Pas Foto<br>3 x 4
                        </div>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label-cell">Tempat Lahir</td>
                <td>{{ $chaperone->birth_place ?? '-' }}</td>
                <td class="label-cell">Tgl/Bln/Th</td>
                <td>{{ $chaperone->birth_date?->format('d/m/Y') ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label-cell">Agama</td>
                <td>{{ $chaperone->religion ?? '-' }}</td>
                <td class="label-cell">Gol Darah</td>
                <td>{{ $chaperone->blood_type ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label-cell">Alamat Rumah</td>
                <td colspan="3">{{ $chaperone->home_address ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label-cell">Kota/Kab</td>
                <td>{{ $chaperone->city ?? 'Padangsidimpuan' }}</td>
                <td class="label-cell">Kode Pos</td>
                <td>{{ $chaperone->postal_code ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label-cell">Telp. Rumah</td>
                <td>{{ $chaperone->home_phone ?? '-' }}</td>
                <td class="label-cell">Handphone</td>
                <td style="font-weight: bold;">{{ $chaperone->phone_number }}</td>
            </tr>
            <tr>
                <td class="label-cell">NTA Pramuka</td>
                <td colspan="4">{{ $chaperone->scout_id ?? '-' }}</td>
            </tr>
        </table>

        {{-- Tabel 1: PENDIDIKAN FORMAL --}}
        <table class="form-grid">
            <thead>
                <tr>
                    <th colspan="5" class="section-header">PENDIDIKAN FORMAL</th>
                </tr>
                <tr style="background:#f3f4f6;">
                    <th style="width: 14%; text-align: center;">Sekolah</th>
                    <th style="width: 36%;">Nama Sekolah</th>
                    <th style="width: 20%;">Kota</th>
                    <th style="width: 12%; text-align: center;">Tahun lulus</th>
                    <th style="width: 18%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $education = !empty($chaperone->formal_education) ? $chaperone->formal_education : \App\Models\Chaperone::defaultFormalEducation();
                @endphp
                @foreach($education as $edu)
                    <tr>
                        <td style="font-weight: bold; text-align: center;">{{ $edu['level'] ?? '' }}</td>
                        <td>{{ $edu['school_name'] ?? '' }}</td>
                        <td>{{ $edu['city'] ?? '' }}</td>
                        <td style="text-align: center;">{{ $edu['graduation_year'] ?? '' }}</td>
                        <td>{{ $edu['notes'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Tabel 2: KEPRAMUKAAN --}}
        <table class="form-grid">
            <thead>
                <tr>
                    <th colspan="4" class="section-header">
                        KEPRAMUKAAN
                    </th>
                </tr>
                <tr style="background:#f3f4f6;">
                    <th style="width: 25%;">Nama golongan</th>
                    <th style="width: 15%; text-align: center;">Tahun</th>
                    <th style="width: 25%;">Nomor Gudep</th>
                    <th style="width: 35%;">Pangkalan</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $experiences = !empty($chaperone->scout_experiences) ? $chaperone->scout_experiences : \App\Models\Chaperone::defaultScoutExperiences();
                @endphp
                @foreach($experiences as $idx => $exp)
                    <tr>
                        <td style="font-weight: bold;">{{ $idx + 1 }}. {{ $exp['level'] ?? '' }}</td>
                        <td style="text-align: center;">{{ $exp['year'] ?? '' }}</td>
                        <td>{{ $exp['group_number'] ?? '' }}</td>
                        <td>{{ $exp['base'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Tabel 3: KURSUS / PELATIHAN KEPRAMUKAAN --}}
        <table class="form-grid">
            <thead>
                <tr>
                    <th colspan="4" class="section-header">KURSUS/PELATIHAN KEPRAMUKAAN</th>
                </tr>
                <tr style="background:#f3f4f6;">
                    <th style="width: 25%;">Nama</th>
                    <th style="width: 15%; text-align: center;">Tahun</th>
                    <th style="width: 30%;">Tempat</th>
                    <th style="width: 30%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $trainings = !empty($chaperone->scout_trainings) ? $chaperone->scout_trainings : \App\Models\Chaperone::defaultScoutTrainings();
                @endphp
                @foreach($trainings as $idx => $trn)
                    <tr>
                        <td style="font-weight: bold;">{{ $idx + 1 }}. {{ $trn['course'] ?? '' }}</td>
                        <td style="text-align: center;">{{ $trn['year'] ?? '' }}</td>
                        <td>{{ $trn['location'] ?? '' }}</td>
                        <td>{{ $trn['notes'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Tabel 4: KURSUS / PELATIHAN DI LUAR KEPRAMUKAAN --}}
        <table class="form-grid">
            <thead>
                <tr>
                    <th colspan="4" class="section-header">KURSUS/PELATIHAN DI LUAR KEPRAMUKAAN</th>
                </tr>
                <tr style="background:#f3f4f6;">
                    <th style="width: 35%;">Nama</th>
                    <th style="width: 15%; text-align: center;">Tahun</th>
                    <th style="width: 25%;">Tempat</th>
                    <th style="width: 25%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $otherTrn = !empty($chaperone->other_trainings) ? $chaperone->other_trainings : [];
                @endphp
                @forelse($otherTrn as $o)
                    <tr>
                        <td>{{ $o['name'] ?? '' }}</td>
                        <td style="text-align: center;">{{ $o['year'] ?? '' }}</td>
                        <td>{{ $o['location'] ?? '' }}</td>
                        <td>{{ $o['notes'] ?? '' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td style="height: 18px;">&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="height: 18px;">&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Kolom Tanda Tangan --}}
        @php
            $selfQr = app(\App\Services\QrCodeService::class)->signatureDataUri($chaperone->qr_token, (bool) $chaperone->scoutGroup?->isVerified());
        @endphp
        <table class="sig-table">
            <tr>
                <td></td>
                <td style="text-align: center;">
                    Padangsidimpuan, {{ now()->timezone('Asia/Jakarta')->translatedFormat('d F Y') }}<br><strong>Pembina Pendamping</strong>
                    <x-report.signatory :space="64" :name="$chaperone->full_name" :nta="$chaperone->scout_id" :qr="$selfQr" />
                </td>
            </tr>
        </table>
    </div>

</body>
</html>

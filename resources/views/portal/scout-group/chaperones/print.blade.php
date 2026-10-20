<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BIODATA PEMBINA PENDAMPING - {{ $chaperone->full_name }} ({{ $scoutGroup->school_name }})</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            color: #000;
        }

        body {
            background-color: #f3f4f6;
            padding: 24px 0;
        }

        .no-print-toolbar {
            max-width: 800px;
            margin: 0 auto 16px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid transparent;
        }

        .btn-primary {
            background-color: #b45309;
            color: #fff;
        }

        .btn-primary:hover {
            background-color: #92400e;
        }

        .btn-secondary {
            background-color: #e5e7eb;
            color: #374151;
            border-color: #d1d5db;
        }

        .sheet {
            background: #fff;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 12mm 15mm;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .sheet-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
        }

        .sheet-subtitle {
            font-size: 10pt;
            font-weight: normal;
            display: block;
            margin-top: 2px;
        }

        table.form-grid {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-bottom: 8px;
        }

        table.form-grid th, table.form-grid td {
            border: 1px solid #000;
            padding: 3px 6px;
            vertical-align: middle;
        }

        .label-cell {
            font-weight: bold;
            background-color: #f9fafb;
            text-transform: uppercase;
            font-size: 8.5pt;
        }

        .section-header {
            background-color: #e5e7eb;
            font-weight: bold;
            text-align: center;
            font-size: 9.5pt;
            letter-spacing: 0.5px;
            padding: 4px 6px;
        }

        .photo-box {
            width: 30mm;
            height: 40mm;
            border: 1px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 8pt;
            margin: 0 auto;
            overflow: hidden;
            background: #fff;
        }

        .photo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .signatures {
            margin-top: 16px;
            width: 100%;
            display: flex;
            justify-content: space-between;
            font-size: 9pt;
        }

        .sig-block {
            text-align: center;
            width: 45%;
        }

        .sig-space {
            height: 55px;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .no-print-toolbar {
                display: none;
            }

            .sheet {
                box-shadow: none;
                margin: 0;
                width: 100%;
                padding: 8mm 10mm;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-toolbar">
        <div>
            <strong>Formulir Resmi Biodata Pembina Pendamping (Bindamping)</strong>
            <span style="display:block; font-size:11px; color:#6b7280;">LT III Kwarcab Gerakan Pramuka Kota Padangsidimpuan</span>
        </div>
        <div style="display:flex; gap:8px;">
            <button onclick="window.print()" class="btn btn-primary">
                &#128438; Cetak Formulir
            </button>
            <a href="javascript:window.close()" class="btn btn-secondary">
                Tutup
            </a>
        </div>
    </div>

    <div class="sheet">
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
                <td colspan="4" style="font-weight: bold; font-size: 10pt;">
                    {{ $chaperone->gender === 'male' ? ($scoutGroup->male_group_number ?? $scoutGroup->group_number) : ($scoutGroup->female_group_number ?? $scoutGroup->group_number) }}
                    <span style="font-weight: normal; font-size: 8.5pt; margin-left: 10px;">
                        ({{ $scoutGroup->school_name }} &bull; {{ $chaperone->gender === 'male' ? 'Pangkalan Putra' : 'Pangkalan Putri' }})
                    </span>
                </td>
            </tr>
            <tr>
                <td class="label-cell">NOMOR ID BINDAMPING</td>
                <td colspan="4" style="font-weight: bold; font-family: monospace; font-size: 9.5pt; color: #0d9488;">{{ $chaperone->id_number ?: '-' }}</td>
            </tr>
            <tr>
                <td class="label-cell">NAMA LENGKAP</td>
                <td style="font-weight: bold;">{{ $chaperone->full_name }}</td>
                <td class="label-cell">Jenis Kelamin</td>
                <td style="width: 15%;">{{ $chaperone->gender === 'male' ? 'Laki-laki (Putra)' : 'Perempuan (Putri)' }}</td>
                <td rowspan="6" style="width: 32mm; text-align: center; vertical-align: middle; padding: 2px;">
                    <div class="photo-box">
                        @if($chaperone->photo_path)
                            <img src="{{ asset('storage/' . $chaperone->photo_path) }}" alt="Pas Foto 3x4" />
                        @else
                            <span>Pas Poto<br>3 x 4</span>
                        @endif
                    </div>
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
        <div class="signatures">
            <div class="sig-block">
                <span>Mengetahui,</span><br>
                <strong>Ka. Mabigus / Pimpinan Pangkalan</strong>
                <div class="sig-space"></div>
                <strong>( .................................................... )</strong><br>
                <span>NTA/NIP: ............................................</span>
            </div>
            <div class="sig-block">
                <span>Padangsidimpuan, {{ now()->translatedFormat('d F Y') }}</span><br>
                <strong>Pembina Pendamping,</strong>
                <div class="sig-space"></div>
                <strong style="text-decoration: underline;">{{ $chaperone->full_name }}</strong><br>
                <span>NTA: {{ $chaperone->scout_id ?? '............................................' }}</span>
            </div>
        </div>
    </div>

</body>
</html>

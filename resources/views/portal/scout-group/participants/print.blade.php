<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BIODATA PESERTA - {{ $participant->full_name }} ({{ $scoutGroup->school_name }})</title>
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
            padding: 2px 4px;
            vertical-align: middle;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }

        .section-header {
            background-color: #ffffff;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            font-size: 8pt;
            letter-spacing: 0.5px;
        }

        .photo-box {
            width: 2.8cm;
            height: 3.8cm;
            border: 1px dashed #444;
            display: block;
            text-align: center;
            font-size: 8pt;
            font-weight: 500;
            margin: 0 auto;
            background: #fafafa;
            padding-top: 1.3cm;
        }

        .photo-img {
            width: 2.8cm;
            height: 3.8cm;
            object-fit: cover;
            border: 1px solid #000;
            display: block;
            margin: 0 auto;
        }

        .sign-table {
            width: 100%;
            margin-top: 8px;
            font-size: 8pt;
            border: none !important;
            page-break-inside: avoid;
        }

        .sign-table td {
            border: none !important;
            padding: 0 8px;
            text-align: center;
            vertical-align: top;
        }
    </style>
</head>
<body>

    <div class="sheet">
        <x-report.kop />

        {{-- Kode Formulir --}}
        <div class="form-code">FORM : 02</div>

        <div class="sheet-title">BIODATA PESERTA</div>

        {{-- Tabel Bagian 1: Header & Identitas Diri + Data Orang Tua + Pas Foto --}}
        @php
            $groupNumber = $participant->patrol?->isBoys() ? $scoutGroup->male_group_number : $scoutGroup->female_group_number;
            $formalEdu = $participant->formal_education ?: \App\Models\Participant::defaultFormalEducation();
            $siaga = $participant->scout_siaga ?: \App\Models\Participant::defaultScoutSiaga();
            $penggalang = $participant->scout_penggalang ?: \App\Models\Participant::defaultScoutPenggalang();

            $photoDataUri = null;
            if ($participant->photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($participant->photo_path)) {
                try {
                    $photoContent = \Illuminate\Support\Facades\Storage::disk('public')->get($participant->photo_path);
                    $mimeExt = strtolower(pathinfo($participant->photo_path, PATHINFO_EXTENSION));
                    $mime = match($mimeExt) {
                        'png' => 'image/png',
                        'webp' => 'image/webp',
                        default => 'image/jpeg',
                    };
                    $photoDataUri = 'data:' . $mime . ';base64,' . base64_encode($photoContent);
                } catch (\Throwable $e) {}
            }
        @endphp

        <table class="form-grid">
            <colgroup>
                <col style="width: 18%;">
                <col style="width: 32%;">
                <col style="width: 17%;">
                <col style="width: 17%;">
                <col style="width: 16%;">
            </colgroup>
            <tr>
                <td class="font-bold">Kwartir Daerah</td>
                <td class="font-bold uppercase" colspan="2">SUMATERA UTARA</td>
                <td class="font-bold">Kwartir Cabang</td>
                <td class="font-bold uppercase">KOTA PADANGSIDIMPUAN</td>
            </tr>
            <tr>
                <td class="font-bold">Nomor ID Peserta</td>
                <td colspan="4" class="font-bold font-mono" style="color: #b45309; font-size: 9pt;">{{ $participant->id_number ?: '-' }}</td>
            </tr>
            <tr>
                <td>Nama Lengkap</td>
                <td colspan="2" class="font-bold uppercase">{{ $participant->full_name }}</td>
                <td>Jenis Kelamin</td>
                <td>{{ $participant->gender === 'male' ? 'Laki-laki' : 'Perempuan' }}</td>
            </tr>
            <tr>
                <td>Tempat Lahir</td>
                <td>{{ $participant->birth_place }}</td>
                <td>Tgl/Bln/Th</td>
                <td>{{ $participant->birth_date?->format('d/m/Y') }}</td>
                <td rowspan="11" class="text-center" style="vertical-align: middle; padding: 4px;">
                    @if($photoDataUri)
                        <img src="{{ $photoDataUri }}" alt="Pas Foto" class="photo-img">
                    @else
                        <div class="photo-box">
                            Pas Foto<br>3 x 4
                        </div>
                    @endif
                </td>
            </tr>
            <tr>
                <td>Agama</td>
                <td>{{ $participant->religion ?: '-' }}</td>
                <td>Gol Darah</td>
                <td>{{ $participant->blood_type ?: '-' }}</td>
            </tr>
            <tr>
                <td>NTA</td>
                <td colspan="3">{{ $participant->scout_id ?: '-' }}</td>
            </tr>
            <tr>
                <td>Alamat Rumah</td>
                <td colspan="3">{{ $participant->home_address ?: '-' }}</td>
            </tr>
            <tr>
                <td>Kota/Kab</td>
                <td>{{ $participant->city ?: 'Padangsidimpuan' }}</td>
                <td>Kode Pos</td>
                <td>{{ $participant->postal_code ?: '-' }}</td>
            </tr>
            <tr>
                <td>Telp. Rumah</td>
                <td>{{ $participant->home_phone ?: '-' }}</td>
                <td>Handpone</td>
                <td>{{ $participant->phone ?: '-' }}</td>
            </tr>
            <tr>
                <td>Nama Orang Tua</td>
                <td colspan="3" class="font-bold">{{ $participant->parent_name ?: '-' }}</td>
            </tr>
            <tr>
                <td>Pekerjaan</td>
                <td colspan="3">{{ $participant->parent_job ?: '-' }}</td>
            </tr>
            <tr>
                <td>Alamat Rumah</td>
                <td colspan="3">{{ $participant->parent_address ?: '-' }}</td>
            </tr>
            <tr>
                <td>Kota/Kab</td>
                <td>{{ $participant->parent_city ?: 'Padangsidimpuan' }}</td>
                <td>Kode Pos</td>
                <td>{{ $participant->parent_postal_code ?: '-' }}</td>
            </tr>
            <tr>
                <td>Telp. Rumah</td>
                <td>{{ $participant->parent_home_phone ?: '-' }}</td>
                <td>Handpone</td>
                <td>{{ $participant->parent_phone ?: '-' }}</td>
            </tr>
        </table>

        {{-- Tabel Bagian 2: Pendidikan Formal --}}
        <table class="form-grid">
            <colgroup>
                <col style="width: 18%;">
                <col style="width: 32%;">
                <col style="width: 22%;">
                <col style="width: 14%;">
                <col style="width: 14%;">
            </colgroup>
            <thead>
                <tr>
                    <th colspan="5" class="section-header">PENDIDIKAN FORMAL</th>
                </tr>
                <tr class="font-bold text-center">
                    <td>Sekolah</td>
                    <td>Nama Sekolah</td>
                    <td>Kota</td>
                    <td>Tahun lulus</td>
                    <td>Keterangan</td>
                </tr>
            </thead>
            <tbody>
                @foreach($formalEdu as $edu)
                    <tr>
                        <td class="font-bold text-center">{{ $edu['level'] ?? '' }}</td>
                        <td>{{ $edu['school_name'] ?? '' }}</td>
                        <td>{{ $edu['city'] ?? 'Padangsidimpuan' }}</td>
                        <td class="text-center">{{ $edu['graduation_year'] ?? '' }}</td>
                        <td>{{ $edu['notes'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Tabel Bagian 3: Riwayat Kepramukaan SIAGA --}}
        <table class="form-grid">
            <colgroup>
                <col style="width: 5%;">
                <col style="width: 13%;">
                <col style="width: 12%;">
                <col style="width: 5%;">
                <col style="width: 28%;">
                <col style="width: 5%;">
                <col style="width: 32%;">
            </colgroup>
            <tr>
                <td colspan="2" class="font-bold text-center">Siaga</td>
                <td class="text-center">Tahun: {{ $siaga['year'] ?? '' }}</td>
                <td colspan="2" class="font-bold">Nomor Gudep: {{ $siaga['group_number'] ?? '' }}</td>
                <td colspan="2" class="font-bold">Pangkalan : {{ $siaga['pangkalan'] ?? '' }}</td>
            </tr>
            <tr>
                <td colspan="3" class="font-bold text-center" style="background:#f9fafb;">Tingkatan / Kegiatan</td>
                <td colspan="4" class="font-bold text-center section-header">TANDA KECAKAPAN KHUSUS</td>
            </tr>
            <tr>
                <td colspan="2">Mula</td>
                <td class="text-center">{{ $siaga['levels']['mula'] ?? '' }}</td>
                <td class="text-center font-bold">1.</td>
                <td>{{ $siaga['tkk'][0] ?? '' }}</td>
                <td class="text-center font-bold">11.</td>
                <td>{{ $siaga['tkk'][10] ?? '' }}</td>
            </tr>
            <tr>
                <td colspan="2">Bantu</td>
                <td class="text-center">{{ $siaga['levels']['bantu'] ?? '' }}</td>
                <td class="text-center font-bold">2.</td>
                <td>{{ $siaga['tkk'][1] ?? '' }}</td>
                <td class="text-center font-bold">12.</td>
                <td>{{ $siaga['tkk'][11] ?? '' }}</td>
            </tr>
            <tr>
                <td colspan="2">Tata</td>
                <td class="text-center">{{ $siaga['levels']['tata'] ?? '' }}</td>
                <td class="text-center font-bold">3.</td>
                <td>{{ $siaga['tkk'][2] ?? '' }}</td>
                <td class="text-center font-bold">13.</td>
                <td>{{ $siaga['tkk'][12] ?? '' }}</td>
            </tr>
            <tr>
                <td colspan="2">Garuda</td>
                <td class="text-center">{{ $siaga['levels']['garuda'] ?? '' }}</td>
                <td class="text-center font-bold">4.</td>
                <td>{{ $siaga['tkk'][3] ?? '' }}</td>
                <td class="text-center font-bold">14.</td>
                <td>{{ $siaga['tkk'][13] ?? '' }}</td>
            </tr>
            <tr>
                <td colspan="3" class="font-bold" style="background:#f9fafb;">Giat Siaga telah diikuti</td>
                <td class="text-center font-bold">5.</td>
                <td>{{ $siaga['tkk'][4] ?? '' }}</td>
                <td class="text-center font-bold">15.</td>
                <td>{{ $siaga['tkk'][14] ?? '' }}</td>
            </tr>
            <tr>
                <td class="text-center font-bold">1.</td>
                <td colspan="2">{{ $siaga['activities'][0] ?? '' }}</td>
                <td class="text-center font-bold">6.</td>
                <td>{{ $siaga['tkk'][5] ?? '' }}</td>
                <td class="text-center font-bold">16.</td>
                <td>{{ $siaga['tkk'][15] ?? '' }}</td>
            </tr>
            <tr>
                <td class="text-center font-bold">2.</td>
                <td colspan="2">{{ $siaga['activities'][1] ?? '' }}</td>
                <td class="text-center font-bold">7.</td>
                <td>{{ $siaga['tkk'][6] ?? '' }}</td>
                <td class="text-center font-bold">17.</td>
                <td>{{ $siaga['tkk'][16] ?? '' }}</td>
            </tr>
            <tr>
                <td class="text-center font-bold">3.</td>
                <td colspan="2">{{ $siaga['activities'][2] ?? '' }}</td>
                <td class="text-center font-bold">8.</td>
                <td>{{ $siaga['tkk'][7] ?? '' }}</td>
                <td class="text-center font-bold">18.</td>
                <td>{{ $siaga['tkk'][17] ?? '' }}</td>
            </tr>
            <tr>
                <td class="text-center font-bold">4.</td>
                <td colspan="2">{{ $siaga['activities'][3] ?? '' }}</td>
                <td class="text-center font-bold">9.</td>
                <td>{{ $siaga['tkk'][8] ?? '' }}</td>
                <td class="text-center font-bold">19.</td>
                <td>{{ $siaga['tkk'][18] ?? '' }}</td>
            </tr>
            <tr>
                <td class="text-center font-bold">5.</td>
                <td colspan="2">{{ $siaga['activities'][4] ?? '' }}</td>
                <td class="text-center font-bold">10.</td>
                <td>{{ $siaga['tkk'][9] ?? '' }}</td>
                <td class="text-center font-bold">20.</td>
                <td>{{ $siaga['tkk'][19] ?? '' }}</td>
            </tr>
            <tr>
                <td class="text-center font-bold">6.</td>
                <td colspan="2">{{ $siaga['activities'][5] ?? '' }}</td>
                <td colspan="4" style="background:#f9fafb;"></td>
            </tr>
        </table>

        {{-- Tabel Bagian 4: Riwayat Kepramukaan PENGGALANG --}}
        <table class="form-grid">
            <colgroup>
                <col style="width: 5%;">
                <col style="width: 11%;">
                <col style="width: 10%;">
                <col style="width: 5%;">
                <col style="width: 19.6%;">
                <col style="width: 5%;">
                <col style="width: 19.6%;">
                <col style="width: 5%;">
                <col style="width: 19.8%;">
            </colgroup>
            <tr>
                <td colspan="2" class="font-bold text-center">Penggalang</td>
                <td class="text-center">Tahun: {{ $penggalang['year'] ?? '' }}</td>
                <td colspan="2" class="font-bold">Nomor Gudep: {{ $penggalang['group_number'] ?: $groupNumber }}</td>
                <td colspan="4" class="font-bold">Pangkalan : {{ $penggalang['pangkalan'] ?: $scoutGroup->school_name }}</td>
            </tr>
            <tr>
                <td colspan="3" class="font-bold text-center" style="background:#f9fafb;">Tingkatan / Kegiatan</td>
                <td colspan="6" class="font-bold text-center section-header">TANDA KECAKAPAN KHUSUS</td>
            </tr>
            <tr class="font-bold text-center" style="background:#f9fafb;">
                <td colspan="3">Jenjang</td>
                <td colspan="2">PURWA</td>
                <td colspan="2">MADYA</td>
                <td colspan="2">UTAMA</td>
            </tr>
            <tr>
                <td colspan="2">Ramu</td>
                <td class="text-center">{{ $penggalang['levels']['ramu'] ?? '' }}</td>
                <td class="text-center font-bold">1.</td>
                <td>{{ $penggalang['tkk_purwa'][0] ?? '' }}</td>
                <td class="text-center font-bold">1.</td>
                <td>{{ $penggalang['tkk_madya'][0] ?? '' }}</td>
                <td class="text-center font-bold">1.</td>
                <td>{{ $penggalang['tkk_utama'][0] ?? '' }}</td>
            </tr>
            <tr>
                <td colspan="2">Rakit</td>
                <td class="text-center">{{ $penggalang['levels']['rakit'] ?? '' }}</td>
                <td class="text-center font-bold">2.</td>
                <td>{{ $penggalang['tkk_purwa'][1] ?? '' }}</td>
                <td class="text-center font-bold">2.</td>
                <td>{{ $penggalang['tkk_madya'][1] ?? '' }}</td>
                <td class="text-center font-bold">2.</td>
                <td>{{ $penggalang['tkk_utama'][1] ?? '' }}</td>
            </tr>
            <tr>
                <td colspan="2">Terap</td>
                <td class="text-center">{{ $penggalang['levels']['terap'] ?? '' }}</td>
                <td class="text-center font-bold">3.</td>
                <td>{{ $penggalang['tkk_purwa'][2] ?? '' }}</td>
                <td class="text-center font-bold">3.</td>
                <td>{{ $penggalang['tkk_madya'][2] ?? '' }}</td>
                <td class="text-center font-bold">3.</td>
                <td>{{ $penggalang['tkk_utama'][2] ?? '' }}</td>
            </tr>
            <tr>
                <td colspan="2">Garuda</td>
                <td class="text-center">{{ $penggalang['levels']['garuda'] ?? '' }}</td>
                <td class="text-center font-bold">4.</td>
                <td>{{ $penggalang['tkk_purwa'][3] ?? '' }}</td>
                <td class="text-center font-bold">4.</td>
                <td>{{ $penggalang['tkk_madya'][3] ?? '' }}</td>
                <td class="text-center font-bold">4.</td>
                <td>{{ $penggalang['tkk_utama'][3] ?? '' }}</td>
            </tr>
            <tr>
                <td colspan="3" class="font-bold" style="background:#f9fafb;">Giat Penggalang yang telah diikuti</td>
                <td class="text-center font-bold">5.</td>
                <td>{{ $penggalang['tkk_purwa'][4] ?? '' }}</td>
                <td class="text-center font-bold">5.</td>
                <td>{{ $penggalang['tkk_madya'][4] ?? '' }}</td>
                <td class="text-center font-bold">5.</td>
                <td>{{ $penggalang['tkk_utama'][4] ?? '' }}</td>
            </tr>
            <tr>
                <td class="text-center font-bold">1.</td>
                <td colspan="2">{{ $penggalang['activities'][0] ?? '' }}</td>
                <td class="text-center font-bold">6.</td>
                <td>{{ $penggalang['tkk_purwa'][5] ?? '' }}</td>
                <td class="text-center font-bold">6.</td>
                <td>{{ $penggalang['tkk_madya'][5] ?? '' }}</td>
                <td class="text-center font-bold">6.</td>
                <td>{{ $penggalang['tkk_utama'][5] ?? '' }}</td>
            </tr>
            <tr>
                <td class="text-center font-bold">2.</td>
                <td colspan="2">{{ $penggalang['activities'][1] ?? '' }}</td>
                <td class="text-center font-bold">7.</td>
                <td>{{ $penggalang['tkk_purwa'][6] ?? '' }}</td>
                <td class="text-center font-bold">7.</td>
                <td>{{ $penggalang['tkk_madya'][6] ?? '' }}</td>
                <td class="text-center font-bold">7.</td>
                <td>{{ $penggalang['tkk_utama'][6] ?? '' }}</td>
            </tr>
            <tr>
                <td class="text-center font-bold">3.</td>
                <td colspan="2">{{ $penggalang['activities'][2] ?? '' }}</td>
                <td class="text-center font-bold">8.</td>
                <td>{{ $penggalang['tkk_purwa'][7] ?? '' }}</td>
                <td class="text-center font-bold">8.</td>
                <td>{{ $penggalang['tkk_madya'][7] ?? '' }}</td>
                <td class="text-center font-bold">8.</td>
                <td>{{ $penggalang['tkk_utama'][7] ?? '' }}</td>
            </tr>
            <tr>
                <td class="text-center font-bold">4.</td>
                <td colspan="2">{{ $penggalang['activities'][3] ?? '' }}</td>
                <td class="text-center font-bold">9.</td>
                <td>{{ $penggalang['tkk_purwa'][8] ?? '' }}</td>
                <td class="text-center font-bold">9.</td>
                <td>{{ $penggalang['tkk_madya'][8] ?? '' }}</td>
                <td class="text-center font-bold">9.</td>
                <td>{{ $penggalang['tkk_utama'][8] ?? '' }}</td>
            </tr>
            <tr>
                <td class="text-center font-bold">5.</td>
                <td colspan="2">{{ $penggalang['activities'][4] ?? '' }}</td>
                <td class="text-center font-bold">10.</td>
                <td>{{ $penggalang['tkk_purwa'][9] ?? '' }}</td>
                <td class="text-center font-bold">10.</td>
                <td>{{ $penggalang['tkk_madya'][9] ?? '' }}</td>
                <td class="text-center font-bold">10.</td>
                <td>{{ $penggalang['tkk_utama'][9] ?? '' }}</td>
            </tr>
            <tr>
                <td class="text-center font-bold">6.</td>
                <td colspan="2">{{ $penggalang['activities'][5] ?? '' }}</td>
                <td colspan="6" style="background:#f9fafb;"></td>
            </tr>
            <tr>
                <td class="text-center font-bold">7.</td>
                <td colspan="2">{{ $penggalang['activities'][6] ?? '' }}</td>
                <td colspan="6" style="background:#f9fafb;"></td>
            </tr>
        </table>

        {{-- Kolom Tanda Tangan --}}
        @php
            $qrService = app(\App\Services\QrCodeService::class);
            $signChaperone = $scoutGroup->chaperones->firstWhere('gender', $participant->patrol?->isBoys() ? 'male' : 'female');
            $isVerifiedGroup = $scoutGroup->isVerified();
            $chaperoneQr = $qrService->signatureDataUri($signChaperone?->qr_token, $isVerifiedGroup);
            $participantQr = $qrService->signatureDataUri($participant->qr_token, $isVerifiedGroup);
        @endphp
        <table class="sign-table">
            <tr>
                <td style="width: 50%; text-align: center;">
                    Mengetahui,<br><strong>Pembina Pendamping</strong>
                    <x-report.signatory :space="64" :name="$signChaperone?->full_name" :nta="$signChaperone?->scout_id" :qr="$chaperoneQr" />
                </td>
                <td style="width: 50%; text-align: center;">
                    Padangsidimpuan, {{ now()->timezone('Asia/Jakarta')->translatedFormat('d F Y') }}<br><strong>Peserta</strong>
                    <x-report.signatory :space="64" :name="$participant->full_name" :nta="$participant->scout_id" :qr="$participantQr" />
                </td>
            </tr>
        </table>
    </div>

</body>
</html>

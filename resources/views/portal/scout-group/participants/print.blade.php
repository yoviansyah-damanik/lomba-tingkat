<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BIODATA PESERTA - {{ $participant->full_name }} ({{ $scoutGroup->school_name }})</title>
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
            max-width: 820px;
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
            padding: 10mm 14mm;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .sheet-title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            line-height: 1.3;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        table.form-grid {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-bottom: 6px;
        }

        table.form-grid th, table.form-grid td {
            border: 1px solid #000;
            padding: 2.5px 5px;
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
            font-size: 8.5pt;
            letter-spacing: 0.5px;
        }

        .photo-box {
            width: 2.8cm;
            height: 3.8cm;
            border: 1px dashed #444;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 8pt;
            font-weight: 500;
            margin: 0 auto;
            background: #fafafa;
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
            margin-top: 10px;
            font-size: 8.5pt;
            border: none !important;
        }

        .sign-table td {
            border: none !important;
            padding: 0 10px;
            text-align: center;
            vertical-align: top;
        }

        @media print {
            body {
                background: none;
                padding: 0;
            }
            .no-print-toolbar {
                display: none !important;
            }
            .sheet {
                box-shadow: none;
                margin: 0;
                width: 100%;
                min-height: auto;
                padding: 6mm 10mm;
            }
            @page {
                size: A4 portrait;
                margin: 5mm;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-toolbar">
        <div>
            <strong>Pratinjau Cetak Biodata Peserta (LT III 2026)</strong>
            <span style="font-size: 12px; color: #6b7280; display: block;">Regu {{ $participant->patrol?->patrol_name }} &bull; {{ $scoutGroup->school_name }}</span>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="javascript:window.history.back()" class="btn btn-secondary">&larr; Kembali</a>
            <button onclick="window.print()" class="btn btn-primary">Cetak Dokumen (Ctrl+P)</button>
        </div>
    </div>

    <div class="sheet">
        {{-- Judul Formulir Resmi --}}
        <div class="sheet-title">
            BIODATA PESERTA<br>
            LOMBA TINGKAT III (LT III) TAHUN 2026<br>
            KWARTIR CABANG GERAKAN PRAMUKA KOTA PADANGSIDIMPUAN
        </div>

        {{-- Tabel Bagian 1: Header & Identitas Diri + Data Orang Tua + Pas Foto --}}
        @php
            $groupNumber = $participant->patrol?->isBoys() ? $scoutGroup->male_group_number : $scoutGroup->female_group_number;
            $formalEdu = $participant->formal_education ?: \App\Models\Participant::defaultFormalEducation();
            $siaga = $participant->scout_siaga ?: \App\Models\Participant::defaultScoutSiaga();
            $penggalang = $participant->scout_penggalang ?: \App\Models\Participant::defaultScoutPenggalang();
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
                <td colspan="4" class="font-bold font-mono" style="color: #b45309; font-size: 9.5pt;">{{ $participant->id_number ?: '-' }}</td>
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
                <td rowspan="10" class="text-center" style="vertical-align: middle; padding: 4px;">
                    @if($participant->photo_path)
                        <img src="{{ asset('storage/' . $participant->photo_path) }}" alt="Pas Foto" class="photo-img">
                    @else
                        <div class="photo-box">
                            Pas Poto<br>3 x 4
                        </div>
                    @endif
                </td>
            </tr>
            <tr>
                <td>Agama</td>
                <td>{{ $participant->religion ?: '-' }}</td>
                <td>Gol Darah</td>
                <td>{{ $participant->blood_type ?: '-' }} &nbsp;&nbsp; <strong>NTA:</strong> {{ $participant->scout_id ?: '-' }}</td>
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
                <col style="width: 18%;">
                <col style="width: 12%;">
                <col style="width: 12%;">
                <col style="width: 23%;">
                <col style="width: 12%;">
                <col style="width: 23%;">
            </colgroup>
            <tr>
                <td class="font-bold text-center">Siaga</td>
                <td class="text-center">Tahun: {{ $siaga['year'] ?? '' }}</td>
                <td class="font-bold">Nomor Gudep:</td>
                <td>{{ $siaga['group_number'] ?? '' }}</td>
                <td class="font-bold">Pangkalan :</td>
                <td>{{ $siaga['pangkalan'] ?? '' }}</td>
            </tr>
            <tr>
                <td colspan="2" class="font-bold text-center" style="background:#f9fafb;">Tingkatan / Kegiatan</td>
                <td colspan="4" class="font-bold text-center section-header">TANDA KECAKAPAN KHUSUS</td>
            </tr>
            <tr>
                <td>Mula</td>
                <td class="text-center">{{ $siaga['levels']['mula'] ?? '' }}</td>
                <td class="text-center font-bold">1.</td>
                <td>{{ $siaga['tkk'][0] ?? '' }}</td>
                <td class="text-center font-bold">11.</td>
                <td>{{ $siaga['tkk'][10] ?? '' }}</td>
            </tr>
            <tr>
                <td>Bantu</td>
                <td class="text-center">{{ $siaga['levels']['bantu'] ?? '' }}</td>
                <td class="text-center font-bold">2.</td>
                <td>{{ $siaga['tkk'][1] ?? '' }}</td>
                <td class="text-center font-bold">12.</td>
                <td>{{ $siaga['tkk'][11] ?? '' }}</td>
            </tr>
            <tr>
                <td>Tata</td>
                <td class="text-center">{{ $siaga['levels']['tata'] ?? '' }}</td>
                <td class="text-center font-bold">3.</td>
                <td>{{ $siaga['tkk'][2] ?? '' }}</td>
                <td class="text-center font-bold">13.</td>
                <td>{{ $siaga['tkk'][12] ?? '' }}</td>
            </tr>
            <tr>
                <td>Garuda</td>
                <td class="text-center">{{ $siaga['levels']['garuda'] ?? '' }}</td>
                <td class="text-center font-bold">4.</td>
                <td>{{ $siaga['tkk'][3] ?? '' }}</td>
                <td class="text-center font-bold">14.</td>
                <td>{{ $siaga['tkk'][13] ?? '' }}</td>
            </tr>
            <tr>
                <td colspan="2" class="font-bold" style="background:#f9fafb;">Giat Siaga telah diikuti</td>
                <td class="text-center font-bold">5.</td>
                <td>{{ $siaga['tkk'][4] ?? '' }}</td>
                <td class="text-center font-bold">15.</td>
                <td>{{ $siaga['tkk'][14] ?? '' }}</td>
            </tr>
            <tr>
                <td class="text-center font-bold">1.</td>
                <td>{{ $siaga['activities'][0] ?? '' }}</td>
                <td class="text-center font-bold">6.</td>
                <td>{{ $siaga['tkk'][5] ?? '' }}</td>
                <td class="text-center font-bold">16.</td>
                <td>{{ $siaga['tkk'][15] ?? '' }}</td>
            </tr>
            <tr>
                <td class="text-center font-bold">2.</td>
                <td>{{ $siaga['activities'][1] ?? '' }}</td>
                <td class="text-center font-bold">7.</td>
                <td>{{ $siaga['tkk'][6] ?? '' }}</td>
                <td class="text-center font-bold">17.</td>
                <td>{{ $siaga['tkk'][16] ?? '' }}</td>
            </tr>
            <tr>
                <td class="text-center font-bold">3.</td>
                <td>{{ $siaga['activities'][2] ?? '' }}</td>
                <td class="text-center font-bold">8.</td>
                <td>{{ $siaga['tkk'][7] ?? '' }}</td>
                <td class="text-center font-bold">18.</td>
                <td>{{ $siaga['tkk'][17] ?? '' }}</td>
            </tr>
            <tr>
                <td class="text-center font-bold">4.</td>
                <td>{{ $siaga['activities'][3] ?? '' }}</td>
                <td class="text-center font-bold">9.</td>
                <td>{{ $siaga['tkk'][8] ?? '' }}</td>
                <td class="text-center font-bold">19.</td>
                <td>{{ $siaga['tkk'][18] ?? '' }}</td>
            </tr>
            <tr>
                <td class="text-center font-bold">5.</td>
                <td>{{ $siaga['activities'][4] ?? '' }}</td>
                <td class="text-center font-bold">10.</td>
                <td>{{ $siaga['tkk'][9] ?? '' }}</td>
                <td class="text-center font-bold">20.</td>
                <td>{{ $siaga['tkk'][19] ?? '' }}</td>
            </tr>
            <tr>
                <td class="text-center font-bold">6.</td>
                <td>{{ $siaga['activities'][5] ?? '' }}</td>
                <td colspan="4" style="background:#f9fafb;"></td>
            </tr>
        </table>

        {{-- Tabel Bagian 4: Riwayat Kepramukaan PENGGALANG --}}
        <table class="form-grid">
            <colgroup>
                <col style="width: 18%;">
                <col style="width: 12%;">
                <col style="width: 5%;">
                <col style="width: 18%;">
                <col style="width: 5%;">
                <col style="width: 18%;">
                <col style="width: 5%;">
                <col style="width: 19%;">
            </colgroup>
            <tr>
                <td class="font-bold text-center">Penggalang</td>
                <td class="text-center">Tahun: {{ $penggalang['year'] ?? '' }}</td>
                <td colspan="2" class="font-bold">Nomor Gudep: {{ $penggalang['group_number'] ?: $groupNumber }}</td>
                <td colspan="4" class="font-bold">Pangkalan : {{ $penggalang['pangkalan'] ?: $scoutGroup->school_name }}</td>
            </tr>
            <tr>
                <td colspan="2" class="font-bold text-center" style="background:#f9fafb;">Tingkatan / Kegiatan</td>
                <td colspan="6" class="font-bold text-center section-header">TANDA KECAKAPAN KHUSUS</td>
            </tr>
            <tr class="font-bold text-center" style="background:#f9fafb;">
                <td colspan="2">Jenjang</td>
                <td colspan="2">PURWA</td>
                <td colspan="2">MADYA</td>
                <td colspan="2">UTAMA</td>
            </tr>
            <tr>
                <td>Ramu</td>
                <td class="text-center">{{ $penggalang['levels']['ramu'] ?? '' }}</td>
                <td class="text-center font-bold">1.</td>
                <td>{{ $penggalang['tkk_purwa'][0] ?? '' }}</td>
                <td class="text-center font-bold">1.</td>
                <td>{{ $penggalang['tkk_madya'][0] ?? '' }}</td>
                <td class="text-center font-bold">1.</td>
                <td>{{ $penggalang['tkk_utama'][0] ?? '' }}</td>
            </tr>
            <tr>
                <td>Rakit</td>
                <td class="text-center">{{ $penggalang['levels']['rakit'] ?? '' }}</td>
                <td class="text-center font-bold">2.</td>
                <td>{{ $penggalang['tkk_purwa'][1] ?? '' }}</td>
                <td class="text-center font-bold">2.</td>
                <td>{{ $penggalang['tkk_madya'][1] ?? '' }}</td>
                <td class="text-center font-bold">2.</td>
                <td>{{ $penggalang['tkk_utama'][1] ?? '' }}</td>
            </tr>
            <tr>
                <td>Terap</td>
                <td class="text-center">{{ $penggalang['levels']['terap'] ?? '' }}</td>
                <td class="text-center font-bold">3.</td>
                <td>{{ $penggalang['tkk_purwa'][2] ?? '' }}</td>
                <td class="text-center font-bold">3.</td>
                <td>{{ $penggalang['tkk_madya'][2] ?? '' }}</td>
                <td class="text-center font-bold">3.</td>
                <td>{{ $penggalang['tkk_utama'][2] ?? '' }}</td>
            </tr>
            <tr>
                <td>Garuda</td>
                <td class="text-center">{{ $penggalang['levels']['garuda'] ?? '' }}</td>
                <td class="text-center font-bold">4.</td>
                <td>{{ $penggalang['tkk_purwa'][3] ?? '' }}</td>
                <td class="text-center font-bold">4.</td>
                <td>{{ $penggalang['tkk_madya'][3] ?? '' }}</td>
                <td class="text-center font-bold">4.</td>
                <td>{{ $penggalang['tkk_utama'][3] ?? '' }}</td>
            </tr>
            <tr>
                <td colspan="2" class="font-bold" style="background:#f9fafb;">Giat Penggalang yang telah diikuti</td>
                <td class="text-center font-bold">5.</td>
                <td>{{ $penggalang['tkk_purwa'][4] ?? '' }}</td>
                <td class="text-center font-bold">5.</td>
                <td>{{ $penggalang['tkk_madya'][4] ?? '' }}</td>
                <td class="text-center font-bold">5.</td>
                <td>{{ $penggalang['tkk_utama'][4] ?? '' }}</td>
            </tr>
            <tr>
                <td class="text-center font-bold">1.</td>
                <td>{{ $penggalang['activities'][0] ?? '' }}</td>
                <td class="text-center font-bold">6.</td>
                <td>{{ $penggalang['tkk_purwa'][5] ?? '' }}</td>
                <td class="text-center font-bold">6.</td>
                <td>{{ $penggalang['tkk_madya'][5] ?? '' }}</td>
                <td class="text-center font-bold">6.</td>
                <td>{{ $penggalang['tkk_utama'][5] ?? '' }}</td>
            </tr>
            <tr>
                <td class="text-center font-bold">2.</td>
                <td>{{ $penggalang['activities'][1] ?? '' }}</td>
                <td class="text-center font-bold">7.</td>
                <td>{{ $penggalang['tkk_purwa'][6] ?? '' }}</td>
                <td class="text-center font-bold">7.</td>
                <td>{{ $penggalang['tkk_madya'][6] ?? '' }}</td>
                <td class="text-center font-bold">7.</td>
                <td>{{ $penggalang['tkk_utama'][6] ?? '' }}</td>
            </tr>
            <tr>
                <td class="text-center font-bold">3.</td>
                <td>{{ $penggalang['activities'][2] ?? '' }}</td>
                <td class="text-center font-bold">8.</td>
                <td>{{ $penggalang['tkk_purwa'][7] ?? '' }}</td>
                <td class="text-center font-bold">8.</td>
                <td>{{ $penggalang['tkk_madya'][7] ?? '' }}</td>
                <td class="text-center font-bold">8.</td>
                <td>{{ $penggalang['tkk_utama'][7] ?? '' }}</td>
            </tr>
            <tr>
                <td class="text-center font-bold">4.</td>
                <td>{{ $penggalang['activities'][3] ?? '' }}</td>
                <td class="text-center font-bold">9.</td>
                <td>{{ $penggalang['tkk_purwa'][8] ?? '' }}</td>
                <td class="text-center font-bold">9.</td>
                <td>{{ $penggalang['tkk_madya'][8] ?? '' }}</td>
                <td class="text-center font-bold">9.</td>
                <td>{{ $penggalang['tkk_utama'][8] ?? '' }}</td>
            </tr>
            <tr>
                <td class="text-center font-bold">5.</td>
                <td>{{ $penggalang['activities'][4] ?? '' }}</td>
                <td class="text-center font-bold">10.</td>
                <td>{{ $penggalang['tkk_purwa'][9] ?? '' }}</td>
                <td class="text-center font-bold">10.</td>
                <td>{{ $penggalang['tkk_madya'][9] ?? '' }}</td>
                <td class="text-center font-bold">10.</td>
                <td>{{ $penggalang['tkk_utama'][9] ?? '' }}</td>
            </tr>
            <tr>
                <td class="text-center font-bold">6.</td>
                <td>{{ $penggalang['activities'][5] ?? '' }}</td>
                <td colspan="6" style="background:#f9fafb;"></td>
            </tr>
            <tr>
                <td class="text-center font-bold">7.</td>
                <td>{{ $penggalang['activities'][6] ?? '' }}</td>
                <td colspan="6" style="background:#f9fafb;"></td>
            </tr>
        </table>

        {{-- Kolom Tanda Tangan --}}
        <table class="sign-table">
            <tr>
                <td style="width: 50%;">
                    Mengetahui,<br>
                    Pembina Pendamping / Ka. Mabigus<br>
                    {{ $scoutGroup->school_name }}
                    <div style="height: 55px;"></div>
                    <strong>( ............................................................ )</strong><br>
                    <span style="font-size: 8pt; color: #333;">NTA: ........................................</span>
                </td>
                <td style="width: 50%;">
                    Padangsidimpuan, ................................. 2026<br>
                    Peserta Yang Bersangkutan / Orang Tua<br>
                    <br>
                    <div style="height: 55px;"></div>
                    <strong><u>{{ $participant->full_name }}</u></strong><br>
                    <span style="font-size: 8pt; color: #333;">NTA: {{ $participant->scout_id ?: '........................................' }}</span>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>

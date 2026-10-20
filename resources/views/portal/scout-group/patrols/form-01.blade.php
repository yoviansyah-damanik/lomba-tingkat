<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FORM 01 - REKAP PENDAFTARAN GUGUSDEPAN - REGU {{ strtoupper($patrol->patrol_name) }} ({{ $scoutGroup->school_name }})</title>
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
            padding: 12mm 16mm;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            position: relative;
        }

        .form-code {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 8px;
        }

        .sheet-title {
            text-align: center;
            font-size: 11.5pt;
            font-weight: bold;
            line-height: 1.35;
            text-transform: uppercase;
            margin-bottom: 14px;
        }

        table.form-grid {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-bottom: 0;
        }

        table.form-grid th, table.form-grid td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: middle;
        }

        .section-bar {
            background-color: #d1d5db;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            font-size: 10.5pt;
            padding: 5px !important;
            letter-spacing: 0.5px;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }

        .signature-block {
            margin-top: 18px;
            float: right;
            width: 300px;
            text-align: left;
            font-size: 9.5pt;
            line-height: 1.4;
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
                padding: 8mm 12mm;
            }
            @page {
                size: A4 portrait;
                margin: 6mm;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-toolbar">
        <div>
            <strong>FORM : 01 &bull; Rekap Pendaftaran Gugusdepan</strong>
            <span style="font-size: 12px; color: #6b7280; display: block;">
                Regu {{ $patrol->patrol_name }} ({{ $patrol->gender_category?->label() }}) &bull; {{ $scoutGroup->school_name }}
            </span>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="javascript:window.history.back()" class="btn btn-secondary">&larr; Kembali</a>
            <button onclick="window.print()" class="btn btn-primary">Cetak FORM : 01 (Ctrl+P)</button>
        </div>
    </div>

    <div class="sheet">
        {{-- Kode Formulir --}}
        <div class="form-code">
            FORM : 01
        </div>

        {{-- Judul Formulir Resmi --}}
        <div class="sheet-title">
            PENDAFTARAN GUGUSDEPAN<br>
            LOMBA TINGKAT III (LT III) TAHUN 2026<br>
            KWARTIR CABANG GERAKAN PRAMUKA KOTA PADANGSIDIMPUAN
        </div>

        {{-- Tabel Bagian Atas: Data Pangkalan & Bindamping --}}
        <table class="form-grid" style="margin-bottom: 8px;">
            <colgroup>
                <col style="width: 22%;">
                <col style="width: 78%;">
            </colgroup>
            <tr>
                <td class="font-bold">Kwartir Cabang</td>
                <td class="font-bold uppercase">KOTA PADANGSIDIMPUAN</td>
            </tr>
            <tr>
                <td class="font-bold">Gugusdepan</td>
                <td>{{ $patrol->group_number }} &nbsp;/&nbsp; {{ $scoutGroup->school_name }}</td>
            </tr>
            <tr>
                <td class="font-bold">Alamat Gudep</td>
                <td>{{ $scoutGroup->address ?: '-' }}</td>
            </tr>
            <tr>
                <td class="font-bold">Nama Bindamping</td>
                <td class="font-bold">{{ $chaperone?->full_name ?: '-' }} @if($chaperone?->id_number) <span style="font-size: 8.5pt; color: #444; font-family: monospace;">[{{ $chaperone->id_number }}]</span> @endif</td>
            </tr>
            <tr>
                <td class="font-bold">HP Bindamping</td>
                <td>{{ $chaperone?->phone_number ?: ($chaperone?->phone ?: '-') }}</td>
            </tr>
        </table>

        {{-- Tabel Bagian Bawah: Daftar Nama Peserta --}}
        <table class="form-grid">
            <colgroup>
                <col style="width: 4%;">
                <col style="width: 24%;">
                <col style="width: 36%;">
                <col style="width: 14%;">
                <col style="width: 14%;">
                <col style="width: 8%;">
            </colgroup>
            <thead>
                <tr>
                    <th colspan="6" class="section-bar">
                        DAFTAR NAMA PESERTA
                    </th>
                </tr>
                <tr>
                    <td colspan="4" class="font-bold" style="padding: 5px 8px;">
                        NAMA REGU : &nbsp;&nbsp;<span class="uppercase font-bold" style="letter-spacing: 0.5px;">{{ $patrol->patrol_name }}</span>
                    </td>
                    <td colspan="2" class="font-bold text-center" style="padding: 5px 8px;">
                        PA/PI *) &nbsp;: &nbsp;<span class="uppercase font-bold">{{ $patrol->isBoys() ? 'PA' : 'PI' }}</span>
                    </td>
                </tr>
                <tr class="font-bold text-center" style="background-color: #fafafa;">
                    <th colspan="2" style="border: 1px solid #000; padding: 6px;">JABATAN DALAM REGU</th>
                    <th style="border: 1px solid #000; padding: 6px;">NAMA PESERTA & NO. ID</th>
                    <th style="border: 1px solid #000; padding: 6px;">TINGKAT SKU</th>
                    <th style="border: 1px solid #000; padding: 6px;">TANGGAL LAHIR</th>
                    <th style="border: 1px solid #000; padding: 6px;">KELAS</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $participants = $orderedParticipants;
                    $standardRoles = [
                        1 => 'Pemimpin Regu',
                        2 => 'Wapinru',
                        3 => 'Anggota',
                        4 => 'Anggota',
                        5 => 'Anggota',
                        6 => 'Anggota',
                        7 => 'Anggota',
                        8 => 'Anggota',
                    ];
                @endphp

                {{-- 8 Baris Peserta Utama --}}
                @for($row = 1; $row <= 8; $row++)
                    @php
                        $p = $participants->get($row - 1);
                    @endphp
                    <tr>
                        <td class="text-center font-bold" style="width: 28px;">{{ $row }}</td>
                        <td class="font-bold">{{ $standardRoles[$row] }}</td>
                        <td>
                            {{ $p?->full_name ?? '' }}
                            @if($p?->id_number)
                                <div style="font-size: 7.5pt; color: #444; font-family: monospace; font-weight: bold;">{{ $p->id_number }}</div>
                            @endif
                        </td>
                        <td class="text-center">{{ $p ? $p->highestSkuLevel() : '' }}</td>
                        <td class="text-center">{{ $p?->birth_date?->format('d/m/Y') ?? '' }}</td>
                        <td class="text-center">{{ $p?->grade_label ?? '' }}</td>
                    </tr>
                @endfor

                {{-- 2 Baris Cadangan / Blank Sesuai Format Fisik --}}
                <tr>
                    <td class="text-center" style="height: 22px;"></td>
                    <td></td>
                    <td></td>
                    <td class="text-center"></td>
                    <td class="text-center"></td>
                    <td class="text-center"></td>
                </tr>
                <tr>
                    <td class="text-center" style="height: 22px;"></td>
                    <td></td>
                    <td></td>
                    <td class="text-center"></td>
                    <td class="text-center"></td>
                    <td class="text-center"></td>
                </tr>
            </tbody>
        </table>

        {{-- Bagian Tanda Tangan Pembina Gugusdepan --}}
        <div class="signature-block">
            Padangsidimpuan, &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Oktober 2026<br>
            <br>
            <strong>Pembina Gugusdepan</strong><br>
            <div style="height: 60px;"></div>
            ..................................................................<br>
            <span style="font-size: 9pt;">NTA ........................</span>
        </div>
        <div style="clear: both;"></div>
    </div>

</body>
</html>

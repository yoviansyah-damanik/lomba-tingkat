<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FORM 01 - REKAP PENDAFTARAN GUGUSDEPAN - REGU {{ strtoupper($patrol->patrol_name) }} ({{ $scoutGroup->school_name }})</title>
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
            line-height: 1.35;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        table.form-grid {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-bottom: 0;
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
            padding: 3.5px 5px;
            vertical-align: middle;
        }

        .section-bar {
            background-color: #e5e7eb;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            font-size: 10pt;
            padding: 4px !important;
            letter-spacing: 0.5px;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }

        table.sig-table {
            width: 100%;
            margin-top: 14px;
            border-collapse: collapse;
        }

        table.sig-table td {
            vertical-align: top;
            font-size: 9pt;
            line-height: 1.4;
        }
    </style>
</head>
<body>

    <div class="sheet">
        <x-report.kop />

        {{-- Kode Formulir --}}
        <div class="form-code">
            FORM : 01
        </div>

        <div class="sheet-title">PENDAFTARAN GUGUSDEPAN</div>

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
                <td>{{ $patrol->group_number ?: ($scoutGroup->male_group_number ?: $scoutGroup->group_number) }} &nbsp;/&nbsp; {{ $scoutGroup->school_name }}</td>
            </tr>
            <tr>
                <td class="font-bold">Alamat Gudep</td>
                <td>{{ $scoutGroup->address ?: '-' }}</td>
            </tr>
            <tr>
                <td class="font-bold">Nama Bindamping</td>
                <td class="font-bold">{{ $chaperone?->full_name ?: '-' }} @if($chaperone?->id_number) <span style="font-size: 8pt; color: #444; font-family: monospace;">[{{ $chaperone->id_number }}]</span> @endif</td>
            </tr>
            <tr>
                <td class="font-bold">HP Bindamping</td>
                <td>{{ $chaperone?->phone_number ?: ($chaperone?->phone ?: '-') }}</td>
            </tr>
        </table>

        {{-- Tabel Bagian Bawah: Daftar Nama Peserta --}}
        <table class="form-grid">
            <colgroup>
                <col style="width: 6%;">
                <col style="width: 22%;">
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
                    <td colspan="4" class="font-bold" style="padding: 4px 6px;">
                        NAMA REGU : &nbsp;&nbsp;<span class="uppercase font-bold" style="letter-spacing: 0.5px;">{{ $patrol->patrol_name }}</span>
                    </td>
                    <td colspan="2" class="font-bold text-center" style="padding: 4px 6px;">
                        PA/PI *) &nbsp;: &nbsp;<span class="uppercase font-bold">{{ $patrol->isBoys() ? 'PA' : 'PI' }}</span>
                    </td>
                </tr>
                <tr class="font-bold text-center" style="background-color: #fafafa;">
                    <th colspan="2" style="border: 1px solid #000; padding: 5px;">JABATAN DALAM REGU</th>
                    <th style="border: 1px solid #000; padding: 5px;">NAMA PESERTA & NO. ID</th>
                    <th style="border: 1px solid #000; padding: 5px;">TINGKAT SKU</th>
                    <th style="border: 1px solid #000; padding: 5px;">TANGGAL LAHIR</th>
                    <th style="border: 1px solid #000; padding: 5px;">KELAS</th>
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
                        <td class="text-center font-bold">{{ $row }}</td>
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
                    <td class="text-center" style="height: 20px;"></td>
                    <td></td>
                    <td></td>
                    <td class="text-center"></td>
                    <td class="text-center"></td>
                    <td class="text-center"></td>
                </tr>
                <tr>
                    <td class="text-center" style="height: 20px;"></td>
                    <td></td>
                    <td></td>
                    <td class="text-center"></td>
                    <td class="text-center"></td>
                    <td class="text-center"></td>
                </tr>
            </tbody>
        </table>

        {{-- Bagian Tanda Tangan Pembina Pendamping --}}
        @php
            // QR sebagai tanda tangan digital Pembina Pendamping, hanya setelah kontingen diverifikasi
            $chaperoneQr = app(\App\Services\QrCodeService::class)->signatureDataUri($chaperone?->qr_token, $scoutGroup->isVerified());
        @endphp
        <table class="sig-table">
            <tr>
                <td style="width: 58%;"></td>
                <td style="width: 42%; text-align: center;">
                    Padangsidimpuan, {{ now()->timezone('Asia/Jakarta')->translatedFormat('d F Y') }}<br><strong>Pembina Pendamping</strong>
                    <x-report.signatory :space="64" :name="$chaperone?->full_name" :nta="$chaperone?->scout_id" :qr="$chaperoneQr" />
                </td>
            </tr>
        </table>
    </div>

</body>
</html>

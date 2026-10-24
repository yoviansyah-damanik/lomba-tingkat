<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUKTI REGISTRASI KONTINGEN - {{ strtoupper($scoutGroup->school_name) }}</title>
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

        table {
            page-break-inside: auto;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        thead {
            display: table-header-group;
        }

        .patrol-block {
            page-break-inside: avoid;
            margin-bottom: 8px;
        }

        .bindamping-block {
            page-break-inside: avoid;
            margin-bottom: 8px;
        }

        .checklist-block {
            page-break-inside: avoid;
            margin-bottom: 8px;
        }

        .header-table {
            width: 100%;
            border-bottom: 3px double #000;
            padding-bottom: 6px;
            margin-bottom: 10px;
            border-collapse: collapse;
        }

        .header-text {
            text-align: center;
        }

        .header-text .title-kwarran {
            font-size: 10.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header-text .title-event {
            font-size: 9pt;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .doc-title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 8px 0 3px;
        }

        .doc-subtitle {
            text-align: center;
            font-size: 8.5pt;
            margin-bottom: 12px;
            color: #444;
        }

        .reg-code-box {
            border: 2px solid #000;
            text-align: center;
            padding: 6px;
            margin-bottom: 12px;
        }

        .reg-code-box .label {
            font-size: 8pt;
            color: #555;
        }

        .reg-code-box .code {
            font-size: 19pt;
            font-weight: bold;
            letter-spacing: 2px;
            font-family: "Courier New", Courier, monospace;
        }

        table.info-grid {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-bottom: 10px;
        }

        table.info-grid td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top;
        }

        table.info-grid td.label-col {
            background: #f5f5f5;
            font-weight: bold;
            width: 32%;
        }

        .section-title {
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            background: #eaeaea;
            padding: 3px 6px;
            margin: 10px 0 5px;
            border-left: 4px solid #000;
        }

        table.patrol-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-bottom: 8px;
        }

        table.patrol-table th,
        table.patrol-table td {
            border: 1px solid #000;
            padding: 3px 5px;
            vertical-align: middle;
        }

        table.patrol-table th {
            background: #ddd;
            font-weight: bold;
            text-align: center;
            font-size: 8pt;
            text-transform: uppercase;
        }

        table.patrol-table td.center {
            text-align: center;
        }

        table.patrol-table td.bold {
            font-weight: bold;
        }

        .checklist-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-bottom: 14px;
        }

        .checklist-table th,
        .checklist-table td {
            border: 1px solid #000;
            padding: 4px 8px;
        }

        .checklist-table th {
            background: #ddd;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            font-size: 8pt;
        }

        .checklist-table td.center { text-align: center; }

        .ok { color: #166534; font-weight: bold; }
        .miss { color: #9f1239; font-weight: bold; }

        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 8pt;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .status-verified {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #16a34a;
        }

        .status-pending {
            background: #fef9c3;
            color: #854d0e;
            border: 1px solid #ca8a04;
        }

        .status-revision {
            background: #fff7ed;
            color: #9a3412;
            border: 1px solid #ea580c;
        }

        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #dc2626;
        }

        table.sig-table {
            width: 100%;
            margin-top: 24px;
            border-collapse: collapse;
        }

        table.sig-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 8.5pt;
        }

        .sig-space {
            height: 55px;
        }

        .sig-name {
            display: inline-block;
            padding-top: 4px;
            font-weight: bold;
            min-width: 180px;
        }

        .footer-note {
            margin-top: 18px;
            font-size: 7.5pt;
            color: #555;
            border-top: 1px solid #ccc;
            padding-top: 8px;
            text-align: center;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none; }
            .sheet { box-shadow: none; margin: 0; width: 100%; }
        }
    </style>
</head>
<body>

<div class="sheet">
    <x-report.kop />

    {{-- Judul Dokumen --}}
    <div class="doc-title">Bukti Registrasi Kontingen Gugusdepan</div>
    <div class="doc-subtitle">Dokumen resmi ini diterbitkan secara otomatis oleh sistem setelah kontingen terdaftar</div>

    {{-- Kode Registrasi --}}
    <div class="reg-code-box">
        <div class="label">Kode Registrasi Gugus Depan</div>
        <div class="code">{{ $scoutGroup->registration_code }}</div>
        @php
            $statusLabel = match($scoutGroup->verification_status?->value ?? '') {
                'verified'        => ['label' => 'TERVERIFIKASI', 'class' => 'status-verified'],
                'needs_revision'  => ['label' => 'PERLU PERBAIKAN', 'class' => 'status-revision'],
                'rejected'        => ['label' => 'DITOLAK', 'class' => 'status-rejected'],
                default           => ['label' => 'MENUNGGU VERIFIKASI', 'class' => 'status-pending'],
            };
        @endphp
        <div style="margin-top:6px;">
            <span class="status-badge {{ $statusLabel['class'] }}">{{ $statusLabel['label'] }}</span>
        </div>
    </div>

    {{-- Informasi Pangkalan --}}
    <div class="section-title">Informasi Pangkalan Gugus Depan</div>
    <table class="info-grid">
        <tr>
            <td class="label-col">Nama Sekolah / Pangkalan</td>
            <td style="font-weight:bold;">{{ strtoupper($scoutGroup->school_name) }}</td>
        </tr>
        <tr>
            <td class="label-col">No. Gudep Putra &amp; Putri</td>
            <td>
                {{ $scoutGroup->male_group_number ?: ($scoutGroup->group_number ?: '-') }}
                &amp;
                {{ $scoutGroup->female_group_number ?: ($scoutGroup->group_number ?: '-') }}
            </td>
        </tr>
        <tr>
            <td class="label-col">Kwartir Ranting (Kwarran)</td>
            <td>{{ $scoutGroup->kwarran_name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Alamat Pangkalan</td>
            <td>{{ $scoutGroup->address ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Penanggung Jawab (PIC)</td>
            <td>{{ $scoutGroup->contact_person ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Kontak PIC</td>
            <td>{{ $scoutGroup->contact_phone ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Tanggal Registrasi Akun</td>
            <td>{{ $scoutGroup->created_at?->translatedFormat('d F Y, H:i') }} WIB</td>
        </tr>
        @if($scoutGroup->verified_at)
        <tr>
            <td class="label-col">Diverifikasi Oleh / Tanggal</td>
            <td>Administrator &mdash; {{ $scoutGroup->verified_at->translatedFormat('d F Y, H:i') }} WIB</td>
        </tr>
        @endif
    </table>

    {{-- Rekap Regu --}}
    <div class="section-title">Rekap Regu Kontingen</div>
    @foreach($scoutGroup->patrols as $patrol)
        @php
            $participants = $patrol->orderedParticipants();
            $genderLabel  = $patrol->isBoys() ? 'Penggalang Putra' : 'Penggalang Putri';
        @endphp
        <div class="patrol-block">
            <div style="font-size:8pt; font-weight:bold; margin-bottom:3px;">
                Regu {{ $genderLabel }}: {{ $patrol->patrol_name ?? '-' }}
            </div>
            <table class="patrol-table">
                <thead>
                    <tr>
                        <th style="width:5%;">No</th>
                        <th>Nama Peserta</th>
                        <th style="width:22%;">Jabatan</th>
                        <th style="width:18%;">Kode Peserta</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($participants as $no => $participant)
                    <tr>
                        <td class="center">{{ $no + 1 }}</td>
                        <td class="bold">{{ $participant->full_name }}</td>
                        <td class="center">
                            {{ match($participant->role_in_patrol?->value ?? $participant->role_in_patrol) {
                                'patrol_leader' => 'PINRU',
                                'assistant_patrol_leader' => 'WAPINRU',
                                default => 'Anggota'
                            } }}
                        </td>
                        <td class="center" style="font-family:monospace; font-size:7.5pt;">{{ $participant->id_number ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="center" style="color:#aaa;">Belum ada peserta terdaftar</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endforeach

    {{-- Rekap Bindamping --}}
    @if($scoutGroup->chaperones->count() > 0)
    <div class="bindamping-block">
        <div class="section-title">Rekap Pembina Pendamping (Bindamping)</div>
        <table class="patrol-table">
            <thead>
                <tr>
                    <th style="width:5%;">No</th>
                    <th>Nama Pembina Pendamping</th>
                    <th style="width:18%;">Gender</th>
                    <th style="width:20%;">Kode Bindamping</th>
                </tr>
            </thead>
            <tbody>
                @foreach($scoutGroup->chaperones as $no => $ch)
                <tr>
                    <td class="center">{{ $no + 1 }}</td>
                    <td class="bold">{{ $ch->full_name }}</td>
                    <td class="center">{{ $ch->gender === 'female' ? 'Putri' : 'Putra' }}</td>
                    <td class="center" style="font-family:monospace; font-size:7.5pt;">{{ $ch->id_number ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Checklist Berkas Gudep --}}
    <div class="checklist-block">
        <div class="section-title">Status Kelengkapan Berkas Unggahan Gugus Depan</div>
        <table class="checklist-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Jenis Berkas</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="center">1</td>
                    <td>Surat Tugas / Mandat Gugus Depan</td>
                    <td class="center">
                        @if($scoutGroup->mandate_letter_path)
                            <span class="ok">&#10003; Diunggah</span>
                        @else
                            <span class="miss">&#10007; Belum Diunggah</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="center">2</td>
                    <td>Bukti Pembayaran Pendaftaran</td>
                    <td class="center">
                        @if($scoutGroup->payment_proof_path)
                            <span class="ok">&#10003; Diunggah</span>
                        @else
                            <span class="miss">&#10007; Belum Diunggah</span>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    @if($scoutGroup->revision_notes)
    <div style="border:1px solid #f97316; background:#fff7ed; padding:6px 8px; font-size:7.5pt; margin-bottom:12px; border-radius:4px; page-break-inside: avoid;">
        <strong>Catatan Perbaikan Verifikasi:</strong><br>
        {{ $scoutGroup->revision_notes }}
    </div>
    @endif

    {{-- Tanda Tangan --}}
    <table class="sig-table">
        <tr>
            <td></td>
            <td>
                Padangsidimpuan, {{ now()->timezone('Asia/Jakarta')->translatedFormat('d F Y') }}<br>
                <strong>Penanggung Jawab Gugusdepan</strong>
                @php
                    $picQr = app(\App\Services\QrCodeService::class)->signatureDataUri($scoutGroup->user?->qr_token, (bool) $scoutGroup->isVerified());
                @endphp
                <x-report.signatory :space="64" :name="$scoutGroup->contact_person" :qr="$picQr" :show-nta="false" />
            </td>
        </tr>
    </table>
</div>

</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    @include('reports.partials.pdf-styles')
    <style>
        .slip-box {
            border: 2px dashed #b45309;
            background-color: #fffbeb;
            padding: 10px 14px;
            margin: 12px 0;
            border-radius: 6px;
        }
        .code-display {
            font-family: 'Courier New', Courier, monospace;
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 4px;
            color: #92400e;
        }
        .step-list {
            margin: 8px 0 0 16px;
            padding: 0;
        }
        .step-list li {
            margin-bottom: 5px;
            line-height: 1.45;
        }
        .info-table td {
            padding: 3px 6px;
            vertical-align: top;
            font-size: 8.5px;
        }
        .signature-table {
            width: 100%;
            margin-top: 18px;
            page-break-inside: avoid;
        }
        .signature-table td {
            vertical-align: top;
            font-size: 8.5px;
        }
    </style>
</head>
<body>
    @foreach ($items as $item)
        @php
            $invitation = $item['invitation'];
            $qrDataUri = $item['qr_data_uri'];
            $year = $event?->start_date?->year ?? 2026;
        @endphp

        <div class="sheet">
            <x-report.kop :year="$year" />

            <div class="doc-title" style="font-size: 11px;">Surat Pemberitahuan Kode Undangan Registrasi</div>
            <div class="doc-subtitle">
                Kegiatan Lomba Tingkat Regu Pramuka Penggalang Tiga (LT III) Tahun {{ $year }}
            </div>

            {{-- Penerima Undangan --}}
            <table style="width: 100%; margin-top: 10px; border-collapse: collapse;">
                <tr>
                    <td style="width: 60px; font-size: 8.5px; color: #52525b;">Nomor</td>
                    <td style="width: 10px; font-size: 8.5px;">:</td>
                    <td style="font-size: 8.5px; font-weight: bold;">LT3-INV/{{ $year }}/{{ str_pad($loop->iteration, 3, '0', STR_PAD_LEFT) }}</td>
                    <td style="text-align: right; font-size: 8.5px; color: #52525b;">Sifat: <strong>Penting / Rahasia</strong></td>
                </tr>
                <tr>
                    <td style="font-size: 8.5px; color: #52525b;">Lampiran</td>
                    <td style="font-size: 8.5px;">:</td>
                    <td style="font-size: 8.5px;">1 (satu) Berkas Akreditasi Registrasi</td>
                    <td style="text-align: right; font-size: 8.5px; color: #52525b;">Padangsidimpuan, {{ $generatedAt->translatedFormat('d F Y') }}</td>
                </tr>
                <tr>
                    <td style="font-size: 8.5px; color: #52525b; vertical-align: top;">Kepada Yth.</td>
                    <td style="font-size: 8.5px; vertical-align: top;">:</td>
                    <td colspan="2" style="font-size: 8.5px; font-weight: bold; line-height: 1.4;">
                        Ketua Mabigus / Pembina Gugus Depan<br>
                        {{ $invitation->school_name }}<br>
                        <span style="font-weight: normal; color: #52525b;">di Tempat</span>
                    </td>
                </tr>
            </table>

            <p class="justify" style="margin-top: 10px; font-size: 8.5px;">
                Salam Pramuka,<br>
                Sehubungan dengan penyelenggaraan <strong>Lomba Tingkat Regu Pramuka Penggalang Tiga (LT III) Tahun {{ $year }}</strong> Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan, Panitia Pelaksana mengundang pangkalan Gugus Depan Saudara untuk berpartisipasi dan melakukan pendaftaran kontingen melalui sistem informasi resmi.
            </p>

            {{-- Identitas Gugus Depan --}}
            <table class="info-table" style="width: 100%; margin-top: 6px; border: 1px solid #e4e4e7; background: #fafafa; border-radius: 4px;">
                <tr>
                    <td style="width: 140px; color: #52525b;">Nama Pangkalan / Sekolah</td>
                    <td style="width: 8px;">:</td>
                    <td style="font-weight: bold;">{{ $invitation->school_name }}</td>
                </tr>
                <tr>
                    <td style="color: #52525b;">Nomor Gugus Depan</td>
                    <td>:</td>
                    <td style="font-weight: bold; font-family: monospace;">{{ $invitation->group_number }}</td>
                </tr>
                <tr>
                    <td style="color: #52525b;">Kwartir Ranting (Kwarran)</td>
                    <td>:</td>
                    <td>{{ $invitation->kwarran_name }}</td>
                </tr>
            </table>

            {{-- Kotak Kode Undangan & QR Code --}}
            <table class="slip-box" style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="vertical-align: middle; padding-right: 14px;">
                        <div style="font-size: 8px; font-weight: bold; text-transform: uppercase; color: #b45309; letter-spacing: 1px;">
                            Kode Undangan Registrasi (Sekali Pakai)
                        </div>
                        <div class="code-display" style="margin: 4px 0 6px 0;">
                            {{ $invitation->formattedCode() }}
                        </div>
                        <div style="font-size: 7.5px; color: #78350f; line-height: 1.35;">
                            Gunakan kode 8 karakter di atas untuk membuka formulir pendaftaran akun pangkalan. Kode ini hanya dapat digunakan 1 (satu) kali.
                        </div>
                    </td>
                    <td style="width: 95px; text-align: center; vertical-align: middle; border-left: 1px dashed #f59e0b; padding-left: 10px;">
                        <img src="{{ $qrDataUri }}" alt="QR Registrasi" style="width: 85px; height: 85px; display: block; margin: 0 auto;">
                        <div style="font-size: 6.5px; color: #92400e; margin-top: 3px; font-weight: bold;">Pindai untuk Mendaftar</div>
                    </td>
                </tr>
            </table>

            {{-- Petunjuk Langkah Registrasi --}}
            <div style="font-size: 8.5px; font-weight: bold; text-transform: uppercase; color: #18181b; margin-top: 6px;">
                Petunjuk Langkah Registrasi Online:
            </div>
            <ol class="step-list" style="font-size: 8px; color: #27272a;">
                <li>Buka laman pendaftaran resmi melalui tautan <strong>{{ route('register.scout-group') }}</strong> atau pindai Kode QR di atas.</li>
                <li>Ketikkan <strong>Kode Undangan</strong> di atas pada kolom yang disediakan, lalu klik <strong>Lanjutkan Registrasi</strong>.</li>
                <li>Lengkapi identitas penanggung jawab pangkalan (Nama Pembina &amp; Nomor WhatsApp aktif) untuk membuat akun resmi pangkalan.</li>
                <li>Masuk ke <strong>Dasbor Gugus Depan</strong> untuk membentuk Regu Penggalang (Putra/Putri), entri biodata peserta, serta Pembina Pendamping (Bindamping).</li>
                <li>Lakukan pembayaran pendaftaran kontingen dan unggah Bukti Bayar serta Surat Mandat pangkalan untuk verifikasi oleh Administrator.</li>
            </ol>

            {{-- Catatan Penting --}}
            <div style="margin-top: 8px; padding: 6px 8px; background: #f4f4f5; border-left: 3px solid #71717a; font-size: 7.5px; color: #3f3f46; line-height: 1.35;">
                <strong>Perhatian:</strong> Berkas surat ini wajib disimpan oleh Pembina Gugus Depan yang ditunjuk. Segera lakukan pengisian data sebelum batas akhir pendaftaran kontingen ditutup oleh panitia penyelenggara.
            </div>

            {{-- Tanda Tangan Pengesahan --}}
            <table class="signature-table">
                <tr>
                    <td style="width: 50%;"></td>
                    <td style="width: 50%; text-align: center;">
                        <div>Padangsidimpuan, {{ $generatedAt->translatedFormat('d F Y') }}</div>
                        <div style="font-weight: bold; margin-top: 2px;">Panitia Pelaksana LT III Tahun {{ $year }}</div>
                        <div style="font-size: 7.5px; color: #52525b;">Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan</div>
                        <div style="height: 48px;"></div>
                        <div style="font-weight: bold; text-decoration: underline;">PANITIA PELAKSANA LT III</div>
                        <div style="font-size: 7.5px; color: #52525b;">Bidang Administrasi &amp; Pendaftaran</div>
                    </td>
                </tr>
            </table>
        </div>

        @if (! $loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>


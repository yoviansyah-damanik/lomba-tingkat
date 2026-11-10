<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            size: A5 landscape;
            margin: 0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #ffffff;
            margin: 0;
            padding: 0;
            background-color: #0b1120;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .bib-page {
            width: 210mm;
            height: 148mm;
            position: relative;
            page-break-after: always;
            overflow: hidden;
        }

        .bib-page:last-child {
            page-break-after: avoid;
        }

        /* Tema Kategori Putra (Deep Navy + Royal Blue + Gold) */
        .theme-boys {
            background-color: #091326;
        }

        .theme-boys .accent-bar {
            background-color: #1e40af;
        }

        .theme-boys .badge-category {
            background-color: #2563eb;
            color: #ffffff;
            border: 1.5px solid #60a5fa;
        }

        .theme-boys .hero-container {
            background-color: #0f1d38;
            border: 2px solid #1e3a8a;
        }

        .theme-boys .npp-number {
            color: #ffffff;
        }

        .theme-boys .pill-npp {
            background-color: #f59e0b;
            color: #0f172a;
        }

        .theme-boys .patrol-pill {
            background-color: #3b82f6;
            color: #ffffff;
        }

        /* Tema Kategori Putri (Deep Maroon / Wine + Magenta + Gold) */
        .theme-girls {
            background-color: #1f041b;
        }

        .theme-girls .accent-bar {
            background-color: #831843;
        }

        .theme-girls .badge-category {
            background-color: #db2777;
            color: #ffffff;
            border: 1.5px solid #f472b6;
        }

        .theme-girls .hero-container {
            background-color: #2c0828;
            border: 2px solid #831843;
        }

        .theme-girls .npp-number {
            color: #ffffff;
        }

        .theme-girls .pill-npp {
            background-color: #f59e0b;
            color: #0f172a;
        }

        .theme-girls .patrol-pill {
            background-color: #ec4899;
            color: #ffffff;
        }

        /* Safety Pin Corner Markers */
        .pin-marker {
            position: absolute;
            width: 14px;
            height: 14px;
            border: 1.5px dashed #f59e0b;
            border-radius: 50%;
            text-align: center;
            line-height: 11px;
            font-size: 9px;
            color: #f59e0b;
            font-weight: bold;
            opacity: 0.85;
        }

        .pin-tl {
            top: 7mm;
            left: 7mm;
        }

        .pin-tr {
            top: 7mm;
            right: 7mm;
        }

        .pin-bl {
            bottom: 7mm;
            left: 7mm;
        }

        .pin-br {
            bottom: 7mm;
            right: 7mm;
        }

        /* Outer Frame & Card Canvas */
        .bib-canvas {
            width: 100%;
            height: 100%;
            padding: 8mm 10mm;
            position: relative;
        }

        /* Header Bar */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3.5mm;
        }

        .header-logo {
            width: 52px;
            vertical-align: middle;
        }

        .header-logo img {
            width: 48px;
            height: 48px;
        }

        .header-title-box {
            vertical-align: middle;
            padding-left: 10px;
        }

        .header-org {
            font-size: 8px;
            font-weight: bold;
            color: #f59e0b;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .header-main-title {
            font-size: 13.5px;
            font-weight: 900;
            color: #ffffff;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-top: 1px;
        }

        .header-sub-title {
            font-size: 8.5px;
            font-weight: bold;
            color: #cbd5e1;
            margin-top: 1px;
        }

        .header-badge-box {
            width: 110px;
            text-align: right;
            vertical-align: middle;
        }

        .badge-category {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 10.5px;
            font-weight: 900;
            letter-spacing: 1.2px;
            text-transform: uppercase;
        }

        /* Hero Central NPP Display */
        .hero-container {
            width: 100%;
            border-radius: 12px;
            padding: 3mm 4mm;
            text-align: center;
            margin-bottom: 3.5mm;
            position: relative;
        }

        .pill-npp {
            display: inline-block;
            padding: 2.5px 14px;
            border-radius: 9999px;
            font-size: 8.5px;
            font-weight: 900;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .npp-number {
            font-size: 96px;
            font-weight: 900;
            line-height: 0.95;
            letter-spacing: 4px;
            margin: 2mm 0 1mm 0;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
        }

        .hero-footer-label {
            font-size: 8.5px;
            font-weight: bold;
            color: #94a3b8;
            letter-spacing: 2.5px;
            text-transform: uppercase;
        }

        /* Bottom Identity Footer */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            background-color: rgba(15, 23, 42, 0.7);
            border-radius: 10px;
            padding: 4px 8px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .patrol-pill {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 5px;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .school-name {
            font-size: 10.5px;
            font-weight: bold;
            color: #ffffff;
            margin-top: 3px;
            line-height: 1.25;
        }

        .gudep-number {
            font-size: 8.5px;
            font-weight: 600;
            color: #f59e0b;
            margin-top: 2px;
        }

        .qr-box {
            width: 65px;
            text-align: right;
            vertical-align: middle;
            padding-right: 4px;
        }

        .qr-box img {
            width: 52px;
            height: 52px;
            background: #ffffff;
            padding: 2px;
            border-radius: 5px;
        }

        .qr-caption {
            font-size: 6.5px;
            font-weight: bold;
            color: #94a3b8;
            margin-top: 2px;
            text-align: right;
            letter-spacing: 0.5px;
        }

        /* Top & Bottom Accent Stripes */
        .top-stripe {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3.5mm;
            background-color: #f59e0b;
        }

        .bottom-stripe {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3.5mm;
            background-color: #f59e0b;
        }
    </style>
</head>

<body>
    @foreach ($items as $item)
        @php
            $isBoys = $item['is_boys'];
            $themeClass = $isBoys ? 'theme-boys' : 'theme-girls';
        @endphp

        <div class="bib-page {{ $themeClass }}">
            {{-- Aksen Garis Emas Paling Atas dan Bawah --}}
            <div class="top-stripe"></div>
            <div class="bottom-stripe"></div>

            {{-- 4 Titik Penanda Peniti Nomor Punggung --}}
            <div class="pin-marker pin-tl">+</div>
            <div class="pin-marker pin-tr">+</div>
            <div class="pin-marker pin-bl">+</div>
            <div class="pin-marker pin-br">+</div>

            <div class="bib-canvas">
                {{-- Bagian Header: Identitas Resmi Penyelenggaraan --}}
                <table class="header-table">
                    <tr>
                        <td class="header-logo">
                            <img src="{{ \App\Services\PdfService::logoPath() }}" alt="Logo LT-III">
                        </td>
                        <td class="header-title-box">
                            <div class="header-org">Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan</div>
                            <div class="header-main-title">Lomba Tingkat Tiga (LT-III) 2026</div>
                            <div class="header-sub-title">14 — 17 November 2026 • Lapangan Pulo Baok Padangsidimpuan
                            </div>
                        </td>
                        <td class="header-badge-box">
                            <span class="badge-category">
                                {{ $item['gender_label'] }}
                            </span>
                        </td>
                    </tr>
                </table>

                {{-- Bagian Utama: Tampilan Hero Nomor Peserta Perlombaan (NPP) --}}
                <div class="hero-container">
                    <span class="pill-npp">NOMOR PESERTA PERLOMBAAN (NPP)</span>
                    <div class="npp-number">{{ $item['npp'] }}</div>
                    <div class="hero-footer-label">★ TANGKAS • BERKARAKTER • BERPRESTASI ★</div>
                </div>

                {{-- Bagian Bawah: Identitas Regu, Pangkalan Gudep & QR Code --}}
                <table class="footer-table">
                    <tr>
                        <td style="vertical-align: middle; padding: 4px 6px;">
                            <span class="patrol-pill">
                                Regu {{ $item['patrol_name'] }}
                            </span>
                            <div class="school-name">
                                {{ $item['school_name'] }}
                            </div>
                            <div class="gudep-number">
                                Gugus Depan: {{ $item['group_number'] }}
                            </div>
                        </td>
                        <td class="qr-box">
                            <img src="{{ $item['qr_data_uri'] }}" alt="QR NPP">
                            <div class="qr-caption">KREDENSIAL RESMI</div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    @endforeach
</body>

</html>

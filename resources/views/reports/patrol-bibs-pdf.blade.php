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

        /* Background template image spanning full A5 bleed */
        .bg-template {
            position: absolute;
            top: 0;
            left: 0;
            width: 210mm;
            height: 148mm;
            z-index: 1;
        }

        /* Dynamic Overlay Container */
        .overlay-layer {
            position: absolute;
            top: 0;
            left: 0;
            width: 210mm;
            height: 148mm;
            z-index: 10;
        }

        /* 1. Category Pill (Top Right) */
        .badge-category {
            position: absolute;
            top: 8.8mm;
            left: 177mm;
            width: 23mm;
            height: 8.5mm;
            line-height: 8.5mm;
            text-align: center;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: 1.5px;
            color: #ffffff;
            text-transform: uppercase;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.6);
        }

        /* 2. Central Giant NPP Number */
        .npp-number-box {
            position: absolute;
            top: 59mm;
            left: 55mm;
            width: 100mm;
            height: 25.5mm;
            line-height: 25.5mm;
            text-align: center;
            font-size: 64px;
            font-weight: 900;
            letter-spacing: 3px;
            color: #ffffff;
            text-shadow: 0 3px 0 #cbd5e1, 0 4px 10px rgba(0, 0, 0, 0.8);
        }

        /* 3. Bottom Identity Card: Text Area */
        .identity-box {
            position: absolute;
            top: 114.5mm;
            left: 59.5mm;
            width: 92mm;
            text-align: center;
        }

        .patrol-pill-boys {
            display: inline-block;
            background-color: #2563eb;
            color: #ffffff;
            font-size: 9px;
            font-weight: 900;
            padding: 1.5px 12px;
            border-radius: 9999px;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            border: 1px solid #60a5fa;
        }

        .patrol-pill-girls {
            display: inline-block;
            background-color: #db2777;
            color: #ffffff;
            font-size: 9px;
            font-weight: 900;
            padding: 1.5px 12px;
            border-radius: 9999px;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            border: 1px solid #f472b6;
        }

        .school-name {
            font-size: 12.5px;
            font-weight: 900;
            color: #ffffff;
            margin-top: 1.5mm;
            line-height: 1.2;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .gudep-number {
            font-size: 9.5px;
            font-weight: bold;
            color: #fbbf24;
            margin-top: 1mm;
            letter-spacing: 0.5px;
        }

        /* 4. Bottom Identity Card: QR Code */
        .qr-box {
            position: absolute;
            top: 114mm;
            left: 152mm;
            width: 17mm;
            text-align: center;
        }

        .qr-box img {
            width: 14mm;
            height: 14mm;
            background: #ffffff;
            padding: 1.5px;
            border-radius: 3px;
        }

        .qr-caption {
            font-size: 5.5px;
            font-weight: 900;
            color: #cbd5e1;
            margin-top: 1px;
            text-align: center;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }
    </style>
</head>

<body>
    @foreach ($items as $item)
        @php
            $isBoys = $item['is_boys'];
            $bgPath = public_path($isBoys ? 'images/bibs/bib-bg-boys.jpg' : 'images/bibs/bib-bg-girls.jpg');
        @endphp

        <div class="bib-page">
            {{-- Template Latar Belakang Ornamen Emas High-Res --}}
            <img class="bg-template" src="{{ $bgPath }}" alt="">

            {{-- Lapisan Data Dinamis Hasil Generate Sistem --}}
            <div class="overlay-layer">
                {{-- Kategori: PUTRA / PUTRI --}}
                <div class="badge-category">
                    {{ $item['gender_label'] }}
                </div>

                {{-- Nomor NPP Raksasa di Tengah Plakat Emas --}}
                <div class="npp-number-box">
                    {{ $item['npp'] }}
                </div>

                {{-- Identitas Regu, Sekolah & Pangkalan Gudep --}}
                <div class="identity-box">
                    <span class="{{ $isBoys ? 'patrol-pill-boys' : 'patrol-pill-girls' }}">
                        Regu {{ $item['patrol_name'] }}
                    </span>
                    <div class="school-name">
                        {{ $item['school_name'] }}
                    </div>
                    <div class="gudep-number">
                        Gugus Depan: {{ $item['group_number'] }}
                    </div>
                </div>

                {{-- QR Code Kredensial Resmi --}}
                <div class="qr-box">
                    <img src="{{ $item['qr_data_uri'] }}" alt="QR Verifikasi">
                    <div class="qr-caption">KREDENSIAL RESMI</div>
                </div>
            </div>
        </div>
    @endforeach
</body>

</html>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>{{ $recipient['certificate_number'] ?? 'E-Sertifikat' }}</title>
    <style>
        @page {
            size: 297mm 210mm landscape;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            width: 297mm;
            height: 210mm;
            font-family: 'DejaVu Sans', sans-serif;
            position: relative;
            background-color: #fffdf7;
            color: #1f2937;
            overflow: hidden;
        }

        .canvas {
            position: relative;
            width: 297mm;
            height: 210mm;
        }

        .vector-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 297mm;
            height: 210mm;
            z-index: 1;
        }

        .watermark-logo {
            position: absolute;
            top: 29%;
            left: 38.5%;
            width: 23%;
            opacity: 0.055;
            z-index: 2;
        }

        .top-left-logo {
            position: absolute;
            top: 9.8%;
            left: 9.8%;
            width: 5.7%;
            z-index: 4;
        }

        .field-container {
            position: absolute;
            z-index: 10;
        }

        /* Lapisan teks statis untuk desain vektor resmi LT-III */
        .default-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 297mm;
            height: 210mm;
            z-index: 5;
            text-align: center;
        }

        .header-block {
            position: absolute;
            top: 7.8%;
            left: 18%;
            width: 64%;
            text-align: center;
        }

        .header-title {
            font-size: 11pt;
            font-weight: bold;
            color: #78350f;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 3px;
        }

        .header-subtitle {
            font-size: 9.5pt;
            font-weight: bold;
            color: #92400e;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .cert-main-title {
            margin-top: 8px;
            font-size: 27pt;
            font-weight: bold;
            color: #260c36;
            letter-spacing: 4px;
            text-transform: uppercase;
        }

        .cert-statement {
            position: absolute;
            top: 35.8%;
            left: 15%;
            width: 70%;
            font-size: 10.5pt;
            color: #4b5563;
            font-style: italic;
        }

        .cert-appreciation {
            position: absolute;
            top: 69.5%;
            left: 12%;
            width: 76%;
            font-size: 8.8pt;
            line-height: 1.4;
            color: #374151;
        }

        .cert-event-meta {
            margin-top: 2px;
            font-size: 8.2pt;
            font-weight: bold;
            color: #78350f;
            letter-spacing: 0.3px;
        }

        .signatures {
            position: absolute;
            top: 78.2%;
            left: 8%;
            width: 84%;
            display: table;
        }

        .sig-col {
            display: table-cell;
            width: 41%;
            text-align: center;
            font-size: 8.8pt;
            color: #1f2937;
            vertical-align: top;
        }

        .sig-spacer {
            display: table-cell;
            width: 18%;
        }

        .sig-role {
            font-weight: bold;
            color: #374151;
        }

        .sig-name {
            font-size: 9.5pt;
            font-weight: bold;
            color: #111827;
            text-decoration: underline;
        }

        .sig-nta {
            font-size: 8pt;
            color: #6b7280;
            margin-top: 1px;
        }
    </style>
</head>

<body>
    <div class="canvas">
        @php
            $svgMarkup = trim(view('certificates.vector-artwork')->render());
            $svgDataUri = 'data:image/svg+xml;base64,' . base64_encode($svgMarkup);
        @endphp

        {{-- Desain Vektor Buatan Sendiri (Pure SVG tanpa file background PNG/JPG) --}}
        <img src="{{ $svgDataUri }}" class="vector-bg" alt="Certificate Vector Frame">

        @if (!empty($logoBase64))
            <img src="{{ $logoBase64 }}" class="watermark-logo" alt="Watermark LT-III">
            <img src="{{ $logoBase64 }}" class="top-left-logo" alt="Logo LT-III">
        @endif

        {{-- Informasi Statis Piagam Penghargaan --}}
        <div class="default-overlay">
            <div class="header-block">
                <div class="header-title">Gerakan Pramuka Kwartir Cabang Kota Padangsidimpuan</div>
                <div class="header-subtitle">
                    Lomba Tingkat Tiga (LT-III) Regu Penggalang Tahun
                    {{ $event?->start_date ? $event->start_date->year : 2026 }}
                </div>
                <div class="cert-main-title">PIAGAM PENGHARGAAN</div>
            </div>

            <div class="cert-statement">Diberikan secara resmi dan sah kepada:</div>

            <div class="cert-appreciation">
                <div>
                    Atas partisipasi aktif, dedikasi, dan prestasi dalam kegiatan
                    <strong>{{ $event?->name ?: 'Lomba Tingkat Tiga (LT-III) Regu Pramuka Penggalang' }}</strong>
                    Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan.
                </div>
                <div class="cert-event-meta">
                    {{ $event?->location ?: 'Lapangan Pulo Baok, Kota Padangsidimpuan' }}
                    &bull;
                    @if ($event?->start_date && $event?->end_date)
                        {{ $event->start_date->translatedFormat('d') }} &ndash; {{ $event->end_date->translatedFormat('d F Y') }}
                    @else
                        14 &ndash; 17 November 2026
                    @endif
                </div>
            </div>

            {{-- Kolom Tanda Tangan Resmi --}}
            <div class="signatures">
                <div class="sig-col">
                    <div style="font-size: 8pt; color: #6b7280; margin-bottom: 1px;">Mengetahui,</div>
                    <div class="sig-role">Ketua Panitia Pelaksana,</div>
                    <div style="height: 40px; text-align: center; margin: 2px auto;">
                        @if (!empty($committeeSigBase64))
                            <img src="{{ $committeeSigBase64 }}" style="max-height: 40px; max-width: 140px; display: inline-block;" alt="Tanda Tangan Ketua Panitia">
                        @endif
                    </div>
                    <div class="sig-name">
                        {{ $event?->committee_chair_name ?: 'Kak Fahri Siregar, S.Pd' }}
                    </div>
                    <div class="sig-nta">
                        NTA: {{ $event?->committee_chair_nta ?: '-' }}
                    </div>
                </div>

                <div class="sig-spacer"></div>

                <div class="sig-col">
                    <div style="font-size: 8pt; color: #6b7280; margin-bottom: 1px;">
                        Padangsidimpuan,
                        {{ $event?->end_date ? $event->end_date->translatedFormat('d F Y') : '17 November 2026' }}
                    </div>
                    <div class="sig-role">Ketua Kwartir Cabang Kota Padangsidimpuan,</div>
                    <div style="height: 40px; text-align: center; margin: 2px auto;">
                        @if (!empty($kwarcabSigBase64))
                            <img src="{{ $kwarcabSigBase64 }}" style="max-height: 40px; max-width: 140px; display: inline-block;" alt="Tanda Tangan Ketua Kwartir">
                        @endif
                    </div>
                    <div class="sig-name">
                        {{ $event?->kwarcab_chair_name ?: 'Ketua Kwarcab Padangsidimpuan' }}
                    </div>
                    <div class="sig-nta">
                        NTA: {{ $event?->kwarcab_chair_nta ?: '-' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Elemen Teks Dinamis Penerima --}}
        @php
            $fields = [
                'certificate_number' => $recipient['certificate_number'] ?? '',
                'full_name' => $recipient['full_name'] ?? '',
                'scout_id' => $recipient['scout_id'] ?? '',
                'institution' => $recipient['institution'] ?? '',
                'role_label' => $recipient['role_label'] ?? '',
            ];
        @endphp

        @foreach ($fields as $key => $text)
            @php
                $cfg = $layout[$key] ?? [];
                $x = (float) ($cfg['x'] ?? 50);
                $y = (float) ($cfg['y'] ?? 50);
                $fontSize = (float) ($cfg['font_size'] ?? 14);
                $color = $cfg['color'] ?? '#111827';
                $align = $cfg['align'] ?? 'center';
                $bold = !empty($cfg['bold']);

                if ($align === 'center') {
                    $style = "top: {$y}%; left: 0; width: 100%; text-align: center;";
                } elseif ($align === 'right') {
                    $right = 100 - $x;
                    $style = "top: {$y}%; right: {$right}%; text-align: right;";
                } else {
                    $style = "top: {$y}%; left: {$x}%; text-align: left;";
                }
            @endphp

            @if (!empty($text))
                <div class="field-container"
                    style="{{ $style }} font-size: {{ $fontSize }}pt; color: {{ $color }}; font-weight: {{ $bold ? 'bold' : 'normal' }};">
                    {{ $text }}
                </div>
            @endif
        @endforeach
    </div>
</body>

</html>

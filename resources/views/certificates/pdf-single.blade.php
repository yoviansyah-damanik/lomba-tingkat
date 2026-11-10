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
            background-color: #ffffff;
            color: #1f2937;
            overflow: hidden;
        }

        .canvas {
            position: relative;
            width: 297mm;
            height: 210mm;
        }

        .background-template {
            position: absolute;
            top: 0;
            left: 0;
            width: 297mm;
            height: 210mm;
            z-index: 1;
        }

        .field-container {
            position: absolute;
            z-index: 10;
        }

        /* Desain Bawaan (Default Vector/CSS Frame bila belum unggah gambar template) */
        .default-frame {
            position: absolute;
            top: 0;
            left: 0;
            width: 297mm;
            height: 210mm;
            padding: 12mm;
            background: linear-gradient(135deg, #fffbeb 0%, #ffffff 50%, #fef3c7 100%);
            z-index: 1;
        }

        .inner-border {
            width: 100%;
            height: 100%;
            border: 4px solid #b45309;
            outline: 2px solid #d97706;
            outline-offset: -8px;
            padding: 8mm 15mm;
            position: relative;
            text-align: center;
            background: #ffffff;
        }

        .header-title {
            font-size: 11pt;
            font-weight: bold;
            color: #78350f;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 2px;
        }

        .header-subtitle {
            font-size: 9pt;
            color: #92400e;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .cert-main-title {
            margin-top: 10px;
            font-size: 24pt;
            font-weight: bold;
            color: #b45309;
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        .cert-statement {
            font-size: 11pt;
            color: #4b5563;
            font-style: italic;
            margin-top: 4px;
        }

        .signatures {
            position: absolute;
            bottom: 12mm;
            left: 15mm;
            right: 15mm;
            display: table;
            width: calc(100% - 30mm);
        }

        .sig-col {
            display: table-cell;
            width: 50%;
            text-align: center;
            font-size: 9pt;
            color: #1f2937;
        }
    </style>
</head>

<body>
    <div class="canvas">
        @if ($templateBase64)
            <img src="{{ $templateBase64 }}" class="background-template" alt="Background Template">
        @else
            {{-- Default Pramuka Certificate Frame --}}
            <div class="default-frame">
                <div class="inner-border">
                    <div class="header-title">Gerakan Pramuka Kwartir Cabang Kota Padangsidimpuan</div>
                    <div class="header-subtitle">Lomba Tingkat Tiga (LT-III) Regu Penggalang Tahun
                        {{ $event?->start_date ? $event->start_date->year : 2026 }}</div>
                    <div class="cert-main-title">PIAGAM PENGHARGAAN</div>
                    <div class="cert-statement">Diberikan secara resmi dan sah kepada:</div>

                    {{-- Signatures --}}
                    <div class="signatures">
                        <div class="sig-col">
                            <div>Ketua Panitia Pelaksana,</div>
                            <div style="height: 40px;"></div>
                            <div style="font-weight: bold; text-decoration: underline;">
                                {{ $event?->committee_chair_name ?: 'Kak Fahri Siregar, S.Pd' }}</div>
                            <div style="font-size: 8pt; color: #6b7280;">NTA: {{ $event?->committee_chair_nta ?: '-' }}
                            </div>
                        </div>
                        <div class="sig-col">
                            <div>Ketua Kwartir Cabang Kota Padangsidimpuan,</div>
                            <div style="height: 40px;"></div>
                            <div style="font-weight: bold; text-decoration: underline;">
                                {{ $event?->kwarcab_chair_name ?: 'Ketua Kwarcab Padangsidimpuan' }}</div>
                            <div style="font-size: 8pt; color: #6b7280;">NTA: {{ $event?->kwarcab_chair_nta ?: '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Dynamic Text Elements --}}
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

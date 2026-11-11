@php
    // Skala kanvas vektor: 1122.52 x 793.70 px (ukuran presisi A4 Landscape 297mm x 210mm pada 96 DPI Dompdf & Browser)
    $s = 1122.52 / 2970;
    $p = fn (float $v): float => round($v * $s, 2);
    $ptStr = function (array $coords) use ($p): string {
        $out = [];
        foreach ($coords as [$x, $y]) {
            $out[] = $p($x) . ',' . $p($y);
        }
        return implode(' ', $out);
    };

    $trX = 2595;
    $trY = 295;
    $starPts1 = [];
    $starPts2 = [];
    for ($i = 0; $i < 8; $i++) {
        $rad = ($i % 2 === 0) ? 76 : 33;
        $ang1 = deg2rad($i * 45);
        $ang2 = deg2rad($i * 45 + 45);
        $starPts1[] = [$trX + $rad * cos($ang1), $trY + $rad * sin($ang1)];
        $starPts2[] = [$trX + $rad * cos($ang2), $trY + $rad * sin($ang2)];
    }

    $sealX = 1485;
    $sealY = 1810;
    $rosettePts = [];
    $numTeeth = 36;
    for ($i = 0; $i < $numTeeth * 2; $i++) {
        $ang = deg2rad($i * (360 / ($numTeeth * 2)));
        $rad = ($i % 2 === 0) ? 88 : 77;
        $rosettePts[] = [$sealX + $rad * cos($ang), $sealY + $rad * sin($ang)];
    }

    $sealStarPts = [];
    for ($i = 0; $i < 10; $i++) {
        $ang = deg2rad($i * 36 - 90);
        $rad = ($i % 2 === 0) ? 33 : 14;
        $sealStarPts[] = [$sealX + $rad * cos($ang), $sealY + $rad * sin($ang)];
    }
@endphp
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1122.52 793.70" width="1122.52" height="793.70">
    {{-- 1. Latar Dasar Perkamen Gading Hangat --}}
    <rect x="0" y="0" width="1122.52" height="793.70" fill="#fffdf7" />
    <rect x="{{ $p(112) }}" y="{{ $p(112) }}" width="{{ $p(2746) }}" height="{{ $p(1876) }}" fill="#fefcf3" />

    {{-- Pola Guilloche / Lingkaran Ornamen Halus di Tengah --}}
    <g fill="none" stroke="#f3e3be" stroke-width="{{ $p(2.5) }}">
        <circle cx="{{ $p(1485) }}" cy="{{ $p(1060) }}" r="{{ $p(460) }}" />
        <circle cx="{{ $p(1485) }}" cy="{{ $p(1060) }}" r="{{ $p(435) }}" />
        <circle cx="{{ $p(1485) }}" cy="{{ $p(1060) }}" r="{{ $p(410) }}" />
        <polygon points="{{ $ptStr([[1485, 570], [1975, 1060], [1485, 1550], [995, 1060]]) }}" />
        <rect x="{{ $p(1140) }}" y="{{ $p(715) }}" width="{{ $p(690) }}" height="{{ $p(690) }}" />
    </g>

    {{-- 2. Bingkai Luar Marun Kerajaan (0 - 86) --}}
    <rect x="0" y="0" width="1122.52" height="{{ $p(86) }}" fill="#260c36" />
    <rect x="0" y="{{ $p(2014) }}" width="1122.52" height="{{ $p(86) }}" fill="#260c36" />
    <rect x="0" y="0" width="{{ $p(86) }}" height="793.70" fill="#260c36" />
    <rect x="{{ $p(2884) }}" y="0" width="{{ $p(86) }}" height="793.70" fill="#260c36" />

    {{-- 3. Pita Sudut Diagonal (Kiri Atas, Kanan Atas, Kiri Bawah, Kanan Bawah) --}}
    {{-- Kiri Atas --}}
    <polygon points="{{ $ptStr([[0, 0], [410, 0], [0, 410]]) }}" fill="#581230" />
    <line x1="0" y1="{{ $p(410) }}" x2="{{ $p(410) }}" y2="0" stroke="#d97706" stroke-width="{{ $p(12) }}" />
    <line x1="0" y1="{{ $p(390) }}" x2="{{ $p(390) }}" y2="0" stroke="#fbbf24" stroke-width="{{ $p(5) }}" />
    <polygon points="{{ $ptStr([[0, 0], [340, 0], [0, 340]]) }}" fill="#260c36" />
    <line x1="0" y1="{{ $p(340) }}" x2="{{ $p(340) }}" y2="0" stroke="#fef08a" stroke-width="{{ $p(3) }}" />

    {{-- Kanan Atas --}}
    <polygon points="{{ $ptStr([[2970, 0], [2560, 0], [2970, 410]]) }}" fill="#581230" />
    <line x1="{{ $p(2560) }}" y1="0" x2="{{ $p(2970) }}" y2="{{ $p(410) }}" stroke="#d97706" stroke-width="{{ $p(12) }}" />
    <line x1="{{ $p(2580) }}" y1="0" x2="{{ $p(2970) }}" y2="{{ $p(390) }}" stroke="#fbbf24" stroke-width="{{ $p(5) }}" />
    <polygon points="{{ $ptStr([[2970, 0], [2630, 0], [2970, 340]]) }}" fill="#260c36" />
    <line x1="{{ $p(2630) }}" y1="0" x2="{{ $p(2970) }}" y2="{{ $p(340) }}" stroke="#fef08a" stroke-width="{{ $p(3) }}" />

    {{-- Kiri Bawah --}}
    <polygon points="{{ $ptStr([[0, 2100], [410, 2100], [0, 1690]]) }}" fill="#581230" />
    <line x1="0" y1="{{ $p(1690) }}" x2="{{ $p(410) }}" y2="{{ $p(2100) }}" stroke="#d97706" stroke-width="{{ $p(12) }}" />
    <line x1="0" y1="{{ $p(1710) }}" x2="{{ $p(390) }}" y2="{{ $p(2100) }}" stroke="#fbbf24" stroke-width="{{ $p(5) }}" />
    <polygon points="{{ $ptStr([[0, 2100], [340, 2100], [0, 1760]]) }}" fill="#260c36" />
    <line x1="0" y1="{{ $p(1760) }}" x2="{{ $p(340) }}" y2="{{ $p(2100) }}" stroke="#fef08a" stroke-width="{{ $p(3) }}" />

    {{-- Kanan Bawah --}}
    <polygon points="{{ $ptStr([[2970, 2100], [2560, 2100], [2970, 1690]]) }}" fill="#581230" />
    <line x1="{{ $p(2560) }}" y1="{{ $p(2100) }}" x2="{{ $p(2970) }}" y2="{{ $p(1690) }}" stroke="#d97706" stroke-width="{{ $p(12) }}" />
    <line x1="{{ $p(2580) }}" y1="{{ $p(2100) }}" x2="{{ $p(2970) }}" y2="{{ $p(1710) }}" stroke="#fbbf24" stroke-width="{{ $p(5) }}" />
    <polygon points="{{ $ptStr([[2970, 2100], [2630, 2100], [2970, 1760]]) }}" fill="#260c36" />
    <line x1="{{ $p(2630) }}" y1="{{ $p(2100) }}" x2="{{ $p(2970) }}" y2="{{ $p(1760) }}" stroke="#fef08a" stroke-width="{{ $p(3) }}" />

    {{-- 4. Garis Bingkai Emas Berlapis --}}
    <rect x="{{ $p(34) }}" y="{{ $p(34) }}" width="{{ $p(2902) }}" height="{{ $p(2032) }}" fill="none" stroke="#d97706" stroke-width="{{ $p(4) }}" />
    <rect x="{{ $p(46) }}" y="{{ $p(46) }}" width="{{ $p(2878) }}" height="{{ $p(2008) }}" fill="none" stroke="#fbbf24" stroke-width="{{ $p(2) }}" />
    <rect x="{{ $p(84) }}" y="{{ $p(84) }}" width="{{ $p(2802) }}" height="{{ $p(1932) }}" fill="none" stroke="#92400e" stroke-width="{{ $p(9) }}" />
    <rect x="{{ $p(88) }}" y="{{ $p(88) }}" width="{{ $p(2794) }}" height="{{ $p(1924) }}" fill="none" stroke="#fbbf24" stroke-width="{{ $p(3) }}" />
    <rect x="{{ $p(112) }}" y="{{ $p(112) }}" width="{{ $p(2746) }}" height="{{ $p(1876) }}" fill="none" stroke="#d97706" stroke-width="{{ $p(3) }}" />
    <rect x="{{ $p(126) }}" y="{{ $p(126) }}" width="{{ $p(2718) }}" height="{{ $p(1848) }}" fill="none" stroke="#f59e0b" stroke-width="{{ $p(1.5) }}" />

    {{-- 5. Ornamen Berlian Gorga di Sepanjang Bingkai Atas & Bawah --}}
    @for ($x = 340; $x <= 2630; $x += 84)
        <polygon points="{{ $ptStr([[$x, 50], [$x + 19, 62], [$x, 74], [$x - 19, 62]]) }}" fill="#fbbf24" stroke="#d97706" stroke-width="{{ $p(1.5) }}" />
        <polygon points="{{ $ptStr([[$x, 57], [$x + 8, 62], [$x, 67], [$x - 8, 62]]) }}" fill="#260c36" />
        <polygon points="{{ $ptStr([[$x, 2026], [$x + 19, 2038], [$x, 2050], [$x - 19, 2038]]) }}" fill="#fbbf24" stroke="#d97706" stroke-width="{{ $p(1.5) }}" />
        <polygon points="{{ $ptStr([[$x, 2033], [$x + 8, 2038], [$x, 2043], [$x - 8, 2038]]) }}" fill="#260c36" />
    @endfor

    {{-- Ornamen Berlian Gorga di Sepanjang Bingkai Kiri & Kanan --}}
    @for ($y = 340; $y <= 1760; $y += 84)
        <polygon points="{{ $ptStr([[62, $y - 19], [74, $y], [62, $y + 19], [50, $y]]) }}" fill="#fbbf24" stroke="#d97706" stroke-width="{{ $p(1.5) }}" />
        <polygon points="{{ $ptStr([[62, $y - 8], [67, $y], [62, $y + 8], [57, $y]]) }}" fill="#260c36" />
        <polygon points="{{ $ptStr([[2908, $y - 19], [2920, $y], [2908, $y + 19], [2896, $y]]) }}" fill="#fbbf24" stroke="#d97706" stroke-width="{{ $p(1.5) }}" />
        <polygon points="{{ $ptStr([[2908, $y - 8], [2913, $y], [2908, $y + 8], [2903, $y]]) }}" fill="#260c36" />
    @endfor

    {{-- 6. Medali Ornamen di 4 Sudut Bingkai Dalam --}}
    @foreach ([[138, 138], [2832, 138], [138, 1962], [2832, 1962]] as [$cx, $cy])
        <polygon points="{{ $ptStr([[$cx, $cy - 50], [$cx + 50, $cy], [$cx, $cy + 50], [$cx - 50, $cy]]) }}" fill="#260c36" stroke="#fbbf24" stroke-width="{{ $p(4) }}" />
        <polygon points="{{ $ptStr([[$cx, $cy - 34], [$cx + 34, $cy], [$cx, $cy + 34], [$cx - 34, $cy]]) }}" fill="#581230" stroke="#fef08a" stroke-width="{{ $p(2) }}" />
        <polygon points="{{ $ptStr([[$cx, $cy - 14], [$cx + 14, $cy], [$cx, $cy + 14], [$cx - 14, $cy]]) }}" fill="#fbbf24" />
    @endforeach

    {{-- Aksen Berlian Tengah Kiri & Kanan --}}
    @foreach ([112, 2858] as $sx)
        <polygon points="{{ $ptStr([[$sx, 985], [$sx + 28, 1050], [$sx, 1115], [$sx - 28, 1050]]) }}" fill="#260c36" stroke="#fbbf24" stroke-width="{{ $p(3.5) }}" />
        <polygon points="{{ $ptStr([[$sx, 1014], [$sx + 14, 1050], [$sx, 1086], [$sx - 14, 1050]]) }}" fill="#d97706" />
    @endforeach

    {{-- Mahkota Aksen Tengah Atas --}}
    <polygon points="{{ $ptStr([[1220, 84], [1750, 84], [1715, 115], [1255, 115]]) }}" fill="#260c36" stroke="#fbbf24" stroke-width="{{ $p(2.5) }}" />
    @foreach ([-140, -70, 0, 70, 140] as $dx)
        <polygon points="{{ $ptStr([[1485 + $dx, 93], [1485 + $dx + 10, 99.5], [1485 + $dx, 106], [1485 + $dx - 10, 99.5]]) }}" fill="#fbbf24" />
    @endforeach

    {{-- 7. Bingkai Lingkaran Medali Kiri Atas (Untuk Logo LT-III) & Bintang Kompas Kanan Atas --}}
    <circle cx="{{ $p(375) }}" cy="{{ $p(295) }}" r="{{ $p(114) }}" fill="#fef3c7" stroke="#d97706" stroke-width="{{ $p(4) }}" />
    <circle cx="{{ $p(375) }}" cy="{{ $p(295) }}" r="{{ $p(102) }}" fill="#fffdf7" stroke="#f59e0b" stroke-width="{{ $p(1.5) }}" />

    <circle cx="{{ $p($trX) }}" cy="{{ $p($trY) }}" r="{{ $p(114) }}" fill="#fef3c7" stroke="#d97706" stroke-width="{{ $p(4) }}" />
    <circle cx="{{ $p($trX) }}" cy="{{ $p($trY) }}" r="{{ $p(102) }}" fill="#260c36" stroke="#fbbf24" stroke-width="{{ $p(3.5) }}" />
    <polygon points="{{ $ptStr($starPts2) }}" fill="#d97706" stroke="#fef08a" stroke-width="{{ $p(1.5) }}" />
    <polygon points="{{ $ptStr($starPts1) }}" fill="#fbbf24" stroke="#fef08a" stroke-width="{{ $p(1.5) }}" />
    <circle cx="{{ $p($trX) }}" cy="{{ $p($trY) }}" r="{{ $p(26) }}" fill="#581230" stroke="#fef08a" stroke-width="{{ $p(2) }}" />
    <polygon points="{{ $ptStr([[$trX, $trY - 12], [$trX + 12, $trY], [$trX, $trY + 12], [$trX - 12, $trY]]) }}" fill="#fbbf24" />

    {{-- 8. Garis Pembatas Ornamen Emas --}}
    {{-- Di bawah "PIAGAM PENGHARGAAN" (y = 550 / 26.2%) --}}
    <line x1="{{ $p(910) }}" y1="{{ $p(550) }}" x2="{{ $p(2060) }}" y2="{{ $p(550) }}" stroke="#d97706" stroke-width="{{ $p(3.5) }}" />
    <line x1="{{ $p(1050) }}" y1="{{ $p(558) }}" x2="{{ $p(1920) }}" y2="{{ $p(558) }}" stroke="#f59e0b" stroke-width="{{ $p(1.2) }}" />
    <polygon points="{{ $ptStr([[1485, 539], [1507, 550], [1485, 561], [1463, 550]]) }}" fill="#fbbf24" stroke="#92400e" stroke-width="{{ $p(2.5) }}" />
    <polygon points="{{ $ptStr([[1220, 544], [1232, 550], [1220, 556], [1208, 550]]) }}" fill="#d97706" />
    <polygon points="{{ $ptStr([[1750, 544], [1762, 550], [1750, 556], [1738, 550]]) }}" fill="#d97706" />

    {{-- Di bawah Nomor Sertifikat (y = 697 / 33.2%) --}}
    <line x1="{{ $p(1220) }}" y1="{{ $p(697) }}" x2="{{ $p(1750) }}" y2="{{ $p(697) }}" stroke="#d97706" stroke-width="{{ $p(2) }}" />
    <polygon points="{{ $ptStr([[1485, 691], [1497, 697], [1485, 703], [1473, 697]]) }}" fill="#d97706" />

    {{-- Garis Bawah Ornamen Emas di Bawah Nama Lengkap (y = 1050 / 50.0%) --}}
    <line x1="{{ $p(980) }}" y1="{{ $p(1050) }}" x2="{{ $p(1990) }}" y2="{{ $p(1050) }}" stroke="#d97706" stroke-width="{{ $p(3.5) }}" />
    <line x1="{{ $p(1150) }}" y1="{{ $p(1058) }}" x2="{{ $p(1820) }}" y2="{{ $p(1058) }}" stroke="#f59e0b" stroke-width="{{ $p(1.2) }}" />
    <polygon points="{{ $ptStr([[1485, 1042], [1500, 1050], [1485, 1058], [1470, 1050]]) }}" fill="#fbbf24" stroke="#92400e" stroke-width="{{ $p(2) }}" />
    <polygon points="{{ $ptStr([[980, 1045], [989, 1050], [980, 1055], [971, 1050]]) }}" fill="#d97706" />
    <polygon points="{{ $ptStr([[1990, 1045], [1999, 1050], [1990, 1055], [1981, 1050]]) }}" fill="#d97706" />

    {{-- 9. Segel Medali Rosette Emas Resmi di Tengah Bawah (x = 1485, y = 1810) --}}
    <polygon points="{{ $ptStr([[1440, 1850], [1475, 1850], [1454, 1956], [1432, 1936], [1408, 1952]]) }}" fill="#581230" stroke="#fbbf24" stroke-width="{{ $p(2.5) }}" />
    <polygon points="{{ $ptStr([[1495, 1850], [1530, 1850], [1562, 1952], [1538, 1936], [1516, 1956]]) }}" fill="#260c36" stroke="#fbbf24" stroke-width="{{ $p(2.5) }}" />
    <polygon points="{{ $ptStr($rosettePts) }}" fill="#d97706" stroke="#92400e" stroke-width="{{ $p(2.5) }}" />
    <circle cx="{{ $p($sealX) }}" cy="{{ $p($sealY) }}" r="{{ $p(69) }}" fill="#fbbf24" stroke="#fef08a" stroke-width="{{ $p(3.5) }}" />
    <circle cx="{{ $p($sealX) }}" cy="{{ $p($sealY) }}" r="{{ $p(57) }}" fill="#260c36" stroke="#d97706" stroke-width="{{ $p(2.5) }}" />
    <circle cx="{{ $p($sealX) }}" cy="{{ $p($sealY) }}" r="{{ $p(47) }}" fill="none" stroke="#fbbf24" stroke-width="{{ $p(1.2) }}" />
    <polygon points="{{ $ptStr($sealStarPts) }}" fill="#fbbf24" stroke="#fef08a" stroke-width="{{ $p(1.2) }}" />
    <circle cx="{{ $p($sealX) }}" cy="{{ $p($sealY) }}" r="{{ $p(8) }}" fill="#581230" />
</svg>

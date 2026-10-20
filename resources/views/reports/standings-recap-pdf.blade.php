<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Nilai &amp; Regu Berprestasi</title>
    @include('reports.partials.pdf-styles')
</head>
<body>
    <x-report.kop />

    <div class="doc-title">Rekap Nilai &amp; Regu Berprestasi</div>
    <div class="doc-subtitle">Regu Berprestasi Tinggi, Baik, dan Cukup &bull; Per {{ $generatedAt->translatedFormat('d F Y') }}</div>

    <div class="section-title">Daftar Regu Berprestasi &mdash; Regu Putra</div>
    @include('reports.partials.standings-table', ['rows' => $boysStandings, 'emptyText' => 'Belum ada regu putra yang meraih predikat prestasi.', 'labels' => ['Tinggi', 'Baik', 'Cukup'], 'onlyHighlighted' => true])

    <div class="section-title">Daftar Regu Berprestasi &mdash; Regu Putri</div>
    @include('reports.partials.standings-table', ['rows' => $girlsStandings, 'emptyText' => 'Belum ada regu putri yang meraih predikat prestasi.', 'labels' => ['Tinggi', 'Baik', 'Cukup'], 'onlyHighlighted' => true])

</body>
</html>

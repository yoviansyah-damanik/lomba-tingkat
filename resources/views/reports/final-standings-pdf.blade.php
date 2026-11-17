<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Peringkat Umum (Akhir) Seluruh Regu</title>
    @include('reports.partials.pdf-styles')
</head>
<body>
    <x-report.kop />

    <div class="doc-title">Rekapitulasi Peringkat Umum (Akhir) Seluruh Regu</div>
    <div class="doc-subtitle">Klasemen Lengkap Perolehan Prestasi &amp; Poin Kejuaraan Umum LT-III &bull; Per {{ $generatedAt->translatedFormat('d F Y') }}</div>

    <div class="section-title">Daftar Peringkat Umum &mdash; Seluruh Regu Putra</div>
    @include('reports.partials.standings-table', [
        'rows' => $boysStandings,
        'emptyText' => 'Belum ada data regu putra terverifikasi.',
        'labels' => ['Tinggi', 'Baik', 'Cukup'],
    ])

    <div class="page-break"></div>

    <x-report.kop />

    <div class="doc-title">Rekapitulasi Peringkat Umum (Akhir) Seluruh Regu</div>
    <div class="doc-subtitle">Klasemen Lengkap Perolehan Prestasi &amp; Poin Kejuaraan Umum LT-III &bull; Per {{ $generatedAt->translatedFormat('d F Y') }}</div>

    <div class="section-title">Daftar Peringkat Umum &mdash; Seluruh Regu Putri</div>
    @include('reports.partials.standings-table', [
        'rows' => $girlsStandings,
        'emptyText' => 'Belum ada data regu putri terverifikasi.',
        'labels' => ['Tinggi', 'Baik', 'Cukup'],
    ])

    <x-report.signatories-block :event="$event" :space="60" />
</body>
</html>

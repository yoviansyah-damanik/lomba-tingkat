<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Nilai &amp; Prestasi &mdash; {{ $competition->title }}</title>
    @include('reports.partials.pdf-styles')
    <style>
        .meta-box {
            margin: 8px 0 12px;
            padding: 6px 10px;
            border: 1px solid #d4d4d8;
            background: #fafafa;
            font-size: 8px;
        }
        .meta-box td {
            padding: 2px 4px;
            border: none;
            font-size: 8px;
        }
    </style>
</head>
<body>
    <x-report.kop />

    <div class="doc-title">Rekapitulasi Regu Berprestasi Mata Lomba</div>
    <div class="doc-subtitle">Cabang: [{{ $competition->code }}] {{ $competition->title }} &bull; Regu Berprestasi Tinggi, Baik, dan Cukup &bull; Per {{ $generatedAt->translatedFormat('d F Y') }}</div>

    <table class="meta-box" style="width: 100%;">
        <tr>
            <td style="width: 16%;" class="strong">Mata Lomba</td>
            <td style="width: 34%;">: [{{ $competition->code }}] {{ $competition->title }}</td>
            <td style="width: 16%;" class="strong">Kategori</td>
            <td style="width: 34%;">: {{ $competition->category === 'boys' ? 'Penggalang Putra' : ($competition->category === 'girls' ? 'Penggalang Putri' : 'Penggalang Putra & Putri') }}</td>
        </tr>
        <tr>
            <td class="strong">Formula Penilaian</td>
            <td style="text-transform: capitalize;">: {{ $competition->scoring_formula }} ({{ $competition->criteria->count() }} Kriteria Rubrik)</td>
            <td class="strong">Total Regu Dinilai</td>
            <td>: {{ $totalScores }} Regu Terdaftar</td>
        </tr>
    </table>

    @if($competition->category !== 'girls')
        <div class="section-title">Daftar Regu Berprestasi &mdash; Regu Putra (Tinggi, Baik, Cukup)</div>
        @include('reports.partials.competition-standings-table', [
            'rows' => $boysScores,
            'emptyText' => 'Belum ada regu putra yang meraih predikat prestasi pada cabang ini.',
        ])
    @endif

    @if($competition->category !== 'boys')
        <div class="section-title">Daftar Regu Berprestasi &mdash; Regu Putri (Tinggi, Baik, Cukup)</div>
        @include('reports.partials.competition-standings-table', [
            'rows' => $girlsScores,
            'emptyText' => 'Belum ada regu putri yang meraih predikat prestasi pada cabang ini.',
        ])
    @endif

    <table class="signatures" style="margin-top: 18px; page-break-inside: avoid;">
        <tr>
            <td>Mengetahui,<br><strong>Ketua Panitia Pelaksana LT-III</strong><x-report.signatory :space="56" :name="$event?->signatory('committee')['name']" :nta="$event?->signatory('committee')['nta']" /></td>
            <td>Ditetapkan Oleh,<br><strong>Ketua Dewan Juri LT-III</strong><x-report.signatory :space="56" :name="$event?->signatory('judge')['name']" :nta="$event?->signatory('judge')['nta']" /></td>
            <td>Mengesahkan,<br><strong>Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan</strong><br>Ketua<x-report.signatory :space="44" :name="$event?->signatory('kwarcab')['name']" :nta="$event?->signatory('kwarcab')['nta']" /></td>
        </tr>
    </table>
</body>
</html>

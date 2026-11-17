<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Rekapitulasi Regu Berprestasi Cabang Mata Lomba' }}</title>
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
        .competition-sheet {
            page-break-after: always;
        }
        .competition-sheet:last-child {
            page-break-after: auto;
        }
    </style>
</head>
<body>
    @foreach($competitionsData as $item)
        @php
            $competition = $item['competition'];
            $boysScores = $item['boysScores'];
            $girlsScores = $item['girlsScores'];
            $totalScores = $item['totalScores'];
        @endphp
        <div class="competition-sheet">
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

            <x-report.signatories-block :event="$event" :space="56" />
        </div>
    @endforeach
</body>
</html>

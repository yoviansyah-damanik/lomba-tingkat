<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara Hasil Akhir &amp; Klasemen Resmi</title>
    @include('reports.partials.pdf-styles')
</head>
<body>
    <x-report.kop />

    <div class="doc-title">Berita Acara Penetapan Hasil Akhir &amp; Juara Umum</div>
    <div class="doc-subtitle">Nomor: BA-LT3/KWARCAB-PSP/{{ $generatedAt->year }}/001</div>

    <p class="justify" style="margin-top: 12px;">
        Pada hari ini, <strong>{{ $generatedAt->translatedFormat('l') }}</strong>, tanggal <strong>{{ $generatedAt->translatedFormat('d F Y') }}</strong>, bertempat di Bumi Perkemahan LT-III Kwarcab Gerakan Pramuka Kota Padangsidimpuan, Dewan Juri bersama Panitia Pelaksana telah menyelesaikan seluruh rangkaian penilaian cabang perlombaan serta melakukan rekapitulasi perolehan prestasi regu penggalang putra dan putri secara cermat, transparan, dan dapat dipertanggungjawabkan.
    </p>

    <div class="section-title">Daftar Peringkat &amp; Perolehan Prestasi Regu Putra</div>
    @include('reports.partials.standings-table', ['rows' => $boysStandings, 'emptyText' => 'Belum ada data regu putra terverifikasi.', 'labels' => ['Tinggi', 'Baik', 'Cukup']])

    <div class="section-title">Daftar Peringkat &amp; Perolehan Prestasi Regu Putri</div>
    @include('reports.partials.standings-table', ['rows' => $girlsStandings, 'emptyText' => 'Belum ada data regu putri terverifikasi.', 'labels' => ['Tinggi', 'Baik', 'Cukup']])

    <p class="justify" style="margin-top: 14px;">
        Demikian Berita Acara ini dibuat dan disahkan dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya sebagai dasar penerbitan Surat Keputusan Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan mengenai Kontingen Berprestasi Tinggi yang berhak mewakili Kota Padangsidimpuan ke Lomba Tingkat IV (LT-IV) Kwartir Daerah Sumatera Utara.
    </p>

    <x-report.signatories-block :event="$event" :space="60" />

</body>
</html>

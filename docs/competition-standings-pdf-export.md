# Rekap Nilai & Klasemen Prestasi Per Cabang Lomba (PDF Export)

Fitur cetak dan pratinjau PDF rekapitulasi nilai dan penetapan peringkat prestasi per cabang lomba sesuai format rekap juara umum resmi LT III Kwarcab Padangsidimpuan.

## 1. Rute & Endpoint
- **Pratinjau PDF**: `GET /standings/competition/{competition}/print` (`route('standings.competition.print')`)
  - Menggunakan view `reports.pdf-preview` dengan viewer iframe, tombol unduh langsung, dan tombol kembali ke tab lomba.
- **Berkas PDF**: `GET /standings/competition/{competition}/pdf` (`route('standings.competition.pdf')`)
  - Parameter opsional: `?raw=1` (stream inline untuk iframe) dan `?download=1` (download berkas `rekap-lomba-{code}-{timestamp}.pdf`).

## 2. Struktur Dokumen PDF
- **Kop Surat**: Memakai komponen resmi `<x-report.kop />` seragam dengan dokumen resmi LT III.
- **Metadata Card**: Kode & nama mata lomba, kategori peserta (Putra / Putri / Seluruhnya), formula penilaian, jumlah rubrik kriteria, dan total regu dinilai.
- **Tabel Rekapitulasi**:
  - Kolom: `Rank` (badge medali emas 🥇, perak 🥈, perunggu 🥉), `NPP`, `Regu & Pangkalan`, `Gudep`, `Kwarran`, `Nilai Akhir` (2 desimal), `Predikat Prestasi`, dan `Poin`.
  - Pembagian terpisah untuk kategori Putra dan Putri.
- **Blok Pengesahan (3 Tanda Tangan)**:
  - Ketua Panitia Pelaksana LT-III.
  - Ketua Dewan Juri LT-III.
  - Ketua Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan.

## 3. Keamanan & Kebijakan Blind Mode
- Dilindungi method `authorizeCompetitionRecapAccess()` di `ReportExportController`.
- Saat protokol Blind Scoring aktif (`hide_patrol_identities = true`):
  - Akun Gugus Depan, Peserta, dan Bindamping dilarang mengakses preview dan PDF (HTTP 403).
  - Dewan Juri dan Panitia melihat dokumen dengan identitas pangkalan disensor (`Pangkalan Dirahasiakan`).
  - Administrator dapat melihat identitas asli peserta.

## 4. Integrasi UI
- Tombol `<flux:button>` dengan atribut `icon="printer"` terpasang di:
  1. Header halaman `/admin/standings` saat tab "Rekap Nilai Mata Lomba" aktif (`Cetak Rekap Cabang`).
  2. Toolbar samping pemilih mata lomba (`Cetak Rekap Lomba`).
  3. Header tabel "Lembar Rekapitulasi Nilai Regu" (`Cetak PDF`).

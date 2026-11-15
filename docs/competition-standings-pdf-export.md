# Rekap Nilai & Klasemen Prestasi Per Cabang Lomba (PDF Export)

Fitur cetak dan pratinjau PDF rekapitulasi nilai dan penetapan peringkat prestasi per cabang lomba sesuai format rekap juara umum resmi LT III Kwarcab Padangsidimpuan.

## 1. Rute & Endpoint
- **Pratinjau PDF Satuan**: `GET /standings/competition/{competition}/print` (`route('standings.competition.print')`)
- **Berkas PDF Satuan**: `GET /standings/competition/{competition}/pdf` (`route('standings.competition.pdf')`)
- **Pratinjau PDF Massal (Bulk)**: `GET /standings/competitions/bulk/print?competitions={id1,id2}` (`route('standings.competitions.bulk.print')`)
- **Berkas PDF Massal (Bulk)**: `GET /standings/competitions/bulk/pdf?competitions={id1,id2}` (`route('standings.competitions.bulk.pdf')`)
  - Parameter opsional: `?raw=1` (stream inline iframe) dan `?download=1` (unduh file langsung). Jika parameter `competitions` kosong, sistem mencetak seluruh cabang lomba aktif.

## 2. Struktur Dokumen PDF & Penyaringan Prestasi
- **Penyaringan Khusus 3 Besar**: Hanya menampilkan 3 predikat prestasi resmi per kategori (Putra & Putri):
  - 🥇 Rank 1: **Prestasi Tinggi** (100 Poin)
  - 🥈 Rank 2: **Prestasi Baik** (80 Poin)
  - 🥉 Rank 3: **Prestasi Cukup** (70 Poin)
  - Peringkat 4 ke bawah tidak dicantumkan dalam lembar rekap prestasi.
- **Kop Surat & Metadata**: Memakai kop resmi `<x-report.kop />` dan kotak info cabang lomba (kode, judul, formula, kriteria).
- **Blok Pengesahan (3 Tanda Tangan)**: Ketua Panitia Pelaksana, Ketua Dewan Juri, dan Ketua Kwarcab Padangsidimpuan.
- **Cetak Massal (Bulk)**: Setiap cabang lomba yang dipilih dipisahkan otomatis dengan pembagian halaman (`page-break-after: always`).

## 3. Keamanan & Kebijakan Blind Mode
- Dilindungi method `authorizeCompetitionRecapAccess()` di `ReportExportController`.
- Saat protokol Blind Scoring aktif (`hide_patrol_identities = true`):
  - Akun Gugus Depan, Peserta, dan Bindamping dilarang mengakses preview dan PDF (HTTP 403).
  - Dewan Juri dan Panitia melihat dokumen dengan identitas pangkalan disensor (`Pangkalan Dirahasiakan`).
  - Administrator dapat melihat identitas asli peserta.

## 4. Integrasi UI & Modal Bulk
- Tombol `<flux:button>` dengan atribut `icon="printer"`:
  1. Header utama `/admin/standings`: `Cetak Mata Lomba` (membuka modal seleksi multi-lomba/bulk).
  2. Toolbar tab lomba (saat lomba dipilih): `Cetak Rekap Lomba`.
  3. Header tabel rekap: `Cetak PDF`.
- **Modal Seleksi Bulk**:
  - Filter kategori cepat: Semua, Putra, Putri.
  - Tombol aksi `Pilih Semua` dan `Batal Pilih`.
  - Checkbox per cabang lomba dengan jumlah regu yang dinilai.
  - Tombol `Unduh PDF Langsung` dan `Pratinjau PDF`.

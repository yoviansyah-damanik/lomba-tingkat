# FORM : 01 - Rekap Pendaftaran Gugusdepan

## Overview
Format resmi lembar rekap pendaftaran kontingen Gugus Depan (**FORM : 01**) yang mencakup data pangkalan, Pembina Pendamping (Bindamping), dan daftar 8 nama peserta regu penggalang (Pinru, Wapinru, Anggota).

## Structure
- **Header**:
  - Kode formulir: `FORM : 01`
  - Judul: `PENDAFTARAN GUGUSDEPAN LOMBA TINGKAT III (LT III) TAHUN 2026 KWARTIR CABANG GERAKAN PRAMUKA KOTA PADANGSIDIMPUAN`
- **Data Pangkalan & Bindamping**:
  - Kwartir Cabang: `KOTA PADANGSIDIMPUAN`
  - Gugusdepan: Nomor Gudep & Nama Sekolah / Pangkalan
  - Alamat Gudep: Alamat lengkap pangkalan
  - Nama Bindamping: Sesuai gender regu (Bindamping Putra untuk Regu PA / Bindamping Putri untuk Regu PI)
  - HP Bindamping: Nomor telepon aktif Bindamping
- **Daftar Nama Peserta**:
  - Sub-header: `NAMA REGU` & `PA/PI *)`
  - 8 Baris Peserta Utama:
    - 1: Pemimpin Regu
    - 2: Wapinru
    - 3 - 8: Anggota
  - 2 Baris Cadangan (Blank Rows)
  - Kolom: Jabatan dalam Regu, Nama Peserta, Tingkat SKU (Ramu/Rakit/Terap/Garuda), Tanggal Lahir (dd/mm/yyyy), Kelas (VII/VIII/IX)
- **Tanda Tangan**:
  - Pembina Gugusdepan & NTA

## Routes & Print Views
- Portal Gudep:
  - Cetak FORM 01: `/portal/scout-group/patrols/{patrolId}/form-01`
  - Tombol cetak tersedia di:
    - Halaman Pengelolaan Regu: `/portal/scout-group/patrols`
    - Halaman Review Pendaftaran: `/portal/scout-group/review`
- Administrator Verification:
  - Cetak FORM 01: `/admin/scout-groups/{scoutGroupId}/patrols/{patrolId}/form-01`
  - Tombol cetak tersedia di halaman detail kontingen: `/admin/scout-groups/{id}`

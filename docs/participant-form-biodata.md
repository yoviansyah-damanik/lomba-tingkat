# Participant Biodata Form Documentation

## Overview
Penyesuaian formulir biodata Peserta Regu Penggalang LT III Kwarcab Padangsidimpuan sesuai format fisik resmi.

## Form Structure & Database Fields
Tabel `participants` diperluas dengan atribut:
- **Header Info**: Terikat dengan Kwartir Daerah (SUMATERA UTARA), Kwartir Cabang (KOTA PADANGSIDIMPUAN), Pangkalan, dan Nomor Gudep (Ganjil untuk Putra, Genap untuk Putri).
- **Personal Info**:
  - `full_name`, `gender` (male/female), `role_in_patrol` (Pinru, Wapinru, Anggota)
  - `scout_id` (NTA Pramuka) *(NISN ditiadakan dari formulir pendaftaran)*
  - `birth_place`, `birth_date`, `religion`, `blood_type`
  - `home_address`, `city` (default: Padangsidimpuan), `postal_code`
  - `home_phone`, `phone` (HP/WA Peserta)
  - `photo_path`: Pas foto 3 x 4 berseragam pramuka lengkap.
- **Parent Info**:
  - `parent_name`, `parent_job`
  - `parent_address`, `parent_city` (default: Padangsidimpuan), `parent_postal_code`
  - `parent_home_phone`, `parent_phone` (HP/WA Orang Tua)
- **Formal Education (`formal_education` JSON)**:
  - Jenjang: SD, SMP
  - Kolom: Nama Sekolah, Kota, Tahun Lulus, Keterangan
- **Scout History - Siaga (`scout_siaga` JSON)**:
  - Header: Tahun Siaga, Nomor Gudep Siaga, Pangkalan Siaga
  - Tingkatan SKU: Mula, Bantu, Tata, Garuda
  - Kegiatan Siaga: 6 butir kegiatan
  - Tanda Kecakapan Khusus (TKK): 20 butir TKK Siaga
- **Scout History - Penggalang (`scout_penggalang` JSON)**:
  - Header: Tahun Penggalang, Nomor Gudep Penggalang, Pangkalan Penggalang
  - Tingkatan SKU: Ramu, Rakit, Terap, Garuda
  - Kegiatan Penggalang: 7 butir kegiatan
  - Tanda Kecakapan Khusus (TKK): 3 tingkatan (Purwa 10 butir, Madya 10 butir, Utama 10 butir)

## Routes & Print Layout
- Portal Gudep:
  - List Regu & Peserta: `/portal/scout-group/patrols`
  - Form Tambah: `/portal/scout-group/patrols/{patrolId}/participants/create`
  - Form Edit: `/portal/scout-group/participants/{participant}/edit`
  - Cetak Lembar Resmi: `/portal/scout-group/participants/{participant}/print`
- Admin Verification:
  - Cetak Form Peserta: `/admin/scout-groups/{scoutGroup}/participants/{participant}/print`

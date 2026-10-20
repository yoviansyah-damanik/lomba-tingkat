# Chaperone Biodata Form Documentation

## Overview
Penyesuaian formulir biodata Pembina Pendamping (Bindamping) LT III Kwarcab Padangsidimpuan sesuai format lembar resmi fisik.

## Form Structure & Database Fields
Tabel `chaperones` diperluas dengan atribut:
- **Header Info**: Terikat otomatis dengan Kwartir Daerah (SUMATERA UTARA), Kwartir Cabang (KOTA Padangsidimpuan), dan Nomor Gudep (Ganjil untuk Putra, Genap untuk Putri).
- **Personal Info**:
  - `name`, `gender` (male/female), `kta_number` (NTA)
  - `birth_place`, `birth_date`, `religion`, `blood_type`
  - `home_address`, `city` (default: Padangsidimpuan), `postal_code`
  - `home_phone`, `phone` (HP/WA)
  - `photo_path`: Pas foto 3 x 4 berseragam pramuka lengkap.
- **Formal Education (`formal_education` JSON)**:
  - Jenjang: SD, SMP, SMA, S1, S2
  - Kolom: Nama Sekolah, Kota, Tahun Lulus, Keterangan
- **Scout Experience (`scout_experiences` JSON)**:
  - Golongan: Siaga, Penggalang, Penegak, Pandega, Pembina
  - Kolom: Tahun, Nomor Gudep, Pangkalan
- **Scout Trainings (`scout_trainings` JSON)**:
  - Kursus: KMD, KML, KPD, KPL
  - Kolom: Tahun, Tempat, Keterangan
- **Other Trainings (`other_trainings` JSON)**:
  - Dynamic rows: Nama Kursus, Tahun, Tempat, Keterangan

## Routes & Print Layout
- Portal Gudep:
  - List & Input: `/portal/scout-group/chaperones`
  - Form Tambah: `/portal/scout-group/chaperones/create`
  - Form Edit: `/portal/scout-group/chaperones/{chaperone}/edit`
  - Cetak Lembar Resmi: `/portal/scout-group/chaperones/{chaperone}/print`
- Admin Verification:
  - Cetak Form Bindamping: `/admin/scout-groups/{scoutGroup}/chaperones/{chaperone}/print`

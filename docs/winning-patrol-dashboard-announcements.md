# Pengumuman Regu Berprestasi di Dasbor

Dokumentasi singkat modul ucapan dan pengumuman kehormatan regu berprestasi pada dasbor pengguna.

## 1. Sasaran Peran Pengguna
Pengumuman dan ucapan selamat resmi ditampilkan pada akun yang memiliki relasi dengan regu pemenang:
- **Gugus Depan** (`isScoutGroup`): Ditampilkan di `/dashboard` dan `/portal/scout-group`.
- **Pembina Pendamping (Bindamping)** (`isChaperone`): Ditampilkan di `/dashboard`.
- **Peserta Penggalang** (`isParticipant`): Ditampilkan di `/dashboard`.

## 2. Kriteria & Predikat
- **Peringkat 1**: Regu Berprestasi Tinggi (Emas / Amber). Mendapatkan banner pengumuman pemanggilan kontingen resmi menuju **LT-IV Kwarda Sumatera Utara**.
- **Peringkat 2**: Regu Berprestasi Baik (Perak / Slate).
- **Peringkat 3**: Regu Berprestasi Cukup (Perunggu / Bronze).
- **Blind Mode Protection**: Ucapan disembunyikan otomatis bila mode penyamaran identitas (`hide_patrol_identities`) sedang aktif.

## 3. Komponen & Aksi
- Komponen: `<x-congratulations-card :honor="$honor" />`
- Tombol Aksi:
  - **Unduh E-Sertifikat**: Tersedia jika penerbitan sertifikat telah dipublikasikan (`isCertificatePublished`).
  - **Lihat Klasemen**: Navigasi ke halaman peringkat publik.

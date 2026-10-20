# B2 ID Card Design Specification

## Overview
Semua kartu tanda pengenal (digital ID cards) disesuaikan ke standar lanyard pouch B2 (`79mm x 126mm`) dengan tata letak modern, watermark lambang kegiatan, logo resmi di header, dan badge identitas unik `ID-XXXXXXXX`.

## Key Specifications
- **Dimensions**: B2 Standard — `79mm x 126mm` (CSS `@page { size: 79mm 126mm; margin: 0; }`).
- **Brand Assets**:
  - Header Logo: `public/logo.png` (`12.5mm x 12.5mm`) dengan backdrop rounded putih dan bayangan halus.
  - Background Watermark: `public/logo.png` (`50-52mm`) opacity `0.045`, grayscale filter, positioned center.
- **Security Pattern**: Subtle guilloche-inspired geometric radial-linear background gradients.
- **Identity Number**: Generalized `ID-XXXXXXXX` format (misal `ID-11KIIFPM`) ditampilkan pada badge monospace berkontras tinggi.
- **Participant Data Fields & Photo Layout**:
  - `Lencana Terpadu PINRU / WAPINRU`: Wadah badge terpadu rounded rectangle (`border-radius: 4.5mm`) yang memadukan pas foto 3x4 berbingkai putih di sisi kiri, serta kolom kanan berlatar solid dengan lambang resmi LT III (`public/logo.png`), garis pemisah halus, dan susunan huruf vertikal tegak (`P-I-N-R-U` berlatar emas amber atau `W-A-P-I-N-R-U` berlatar biru royal).
  - `Anggota Regu`: Pas foto standar mandiri (`25mm x 33mm`, border netral abu-abu 1.5px `#cbd5e1`), tanpa lencana samping, dan **tanpa label/badge "Anggota Regu"**.
  - `NTA & ID`: Ditampilkan tepat di bawah nama peserta dalam satu baris bersih tanpa bingkai/kotak (`NTA: XXXXX • ID-XXXXX`), menggunakan tipografi monospace modern.
  - `Regu`: Nama Regu (misal Rajawali)
  - `Nomor Gudep`: Nomor Gugus Depan
  - `Pangkalan`: Nama Sekolah / Pangkalan
  - `Kwartir Ranting`: Mengikuti nama Kwartir Ranting dari Gudep pangkalan (misal Padangsidimpuan Selatan, Padangsidimpuan Utara, dsb.).
  - *(Darah dan SKU telah dihilangkan dari kartu peserta)*.
- **Enlarged QR Code**: Dimensi QR code diperbesar menjadi `25mm x 25mm` (kartu tunggal) dan `23mm x 23mm` (cetak batch A4) untuk memastikan pemindaian instan di pos lapangan.
- **Terminology**: Istilah "KTA" telah dihilangkan secara menyeluruh dan digantikan dengan istilah "Peserta", "Kartu Peserta", atau "Kartu Pendamping" / "Kartu Aparatur".
- **Color Themes**:
  - **Peserta Putra**: Midnight Navy & Royal Blue (`#091e42` -> `#1e3a8a` -> `#2563eb`), strip `#0f172a` (`#38bdf8`), border `#38bdf8`.
  - **Peserta Putri**: Deep Wine & Magenta (`#500724` -> `#831843` -> `#be185d`), strip `#2e0827` (`#f472b6`), border `#f472b6`.
  - **Bindamping Putra**: Forest Emerald (`#022c22` -> `#064e3b` -> `#047857`), strip `#062f24` (`#34d399`), border `#10b981`.
  - **Bindamping Putri**: Royal Violet & Amethyst (`#2e1065` -> `#581c87` -> `#7e22ce`), strip `#19053b` (`#d8b4fe`), border `#c084fc`.
  - **Panitia Pelaksana**: Crimson Ruby & Scarlet (`#450a0a` -> `#7f1d1d` -> `#991b1b`), strip `#2b0505` (`#fca5a5`), border `#f87171` *(berbeda jelas dengan Peserta Putra)*.
  - **Dewan Juri**: Rich Amber Bronze (`#451a03` -> `#78350f` -> `#b45309`), strip `#451a03` (`#fde047`), border `#f59e0b`.
- **Aparatur Staff Card**: Menampilkan Instansi, Peran, NTA, dan Kontak resmi *(baris "Akreditasi : Terverifikasi Aktif" telah dihilangkan)*.
- **Verification QR Code**: Terintegrasi di bagian bawah kartu dengan cuplikan token dan tautan ke endpoint publik `/verify/{token}`.
- **Batch Print**: Lembar A4 (`patrol-batch.blade.php` dan `chaperone-batch.blade.php`) memuat 4 kartu B2 (grid 2x2) dengan garis potong rapi dan proporsi 1:1.

## Modified Files
- `resources/views/digital-cards/participant.blade.php`
- `resources/views/digital-cards/chaperone.blade.php`
- `resources/views/digital-cards/staff.blade.php`
- `resources/views/digital-cards/patrol-batch.blade.php`
- `resources/views/digital-cards/chaperone-batch.blade.php`
- `docs/b2-card-design-specification.md`

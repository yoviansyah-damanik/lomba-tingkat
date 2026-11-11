# B2 ID Card Design Specification (BIB-Harmonized Gold Edition)

## Overview
Kartu Tanda Peserta, Pembina Pendamping, dan Aparatur (ukuran B2: `79mm x 126mm`) diselaraskan dengan identitas visual **BIB Nomor Punggung LT-III 2026** (ornamen geometris emas Mandailing/Gorga, aksen *metallic gold* `#f59e0b`/`#fbbf24`, dan tema warna kontras tinggi).

## Key Specifications
- **Dimensions**: B2 Standard — `79mm x 126mm`.
- **BIB-Harmonized Visual Elements**:
  - **Outer & Inner Frame**: Bingkai emas (`border-[#d97706]/70`) dengan garis ornamen dalam dan aksen sudut geometris SVG emas di kiri/kanan atas badan kartu.
  - **Header**: Pita emas teratas (`1.2mm`), gradien warna kategori pekat, dan siluet ornamen belah ketupat Gorga/Mandailing emas.
  - **Category Strip**: Pita kategori gelap berbingkai emas (`border-y-[1.2px] border-[#f59e0b]/80`) dengan apitan berlian emas (`◆`).
  - **NPP Badge (`npp-badge.blade.php`)**:
    - Mendukung format baru **`LT-PA-XX`** / **`LT-PI-XX`** maupun format 2 digit:
      - Bagian kepala menampilkan kode `LT` dan badge `PA`/`PI` beraksen emas.
      - Bagian tengah menampilkan digit nomor urut (`01`, `02`, dst.) secara vertikal berukuran besar (`13pt`).
      - Bagian kaki menampilkan label `NPP` emas.
  - **Patrol Badge (`patrol-badge.blade.php`)**: Lencana vertikal regu di sisi kanan berbingkai emas dengan ikon tenda pramuka emas (`#fbbf24`) dan adaptasi ukuran font otomatis hingga nama regu panjang (`>= 12` karakter).
  - **Meta & Details Box**: Pembatas berlian emas di bawah nama peserta, *pill badge* ID berkontras tinggi, dan tabel rincian dalam kotak berbingkai emas halus (`bg-amber-50/45 border-[#d97706]/30`).
  - **QR Credential Box & Footer**: Kotak verifikasi QR berlatar gelap-emas senada dengan kartu bawah BIB, ditutup footer keamanan berlambang bintang emas (`★`).

## Component Files
- [card.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/components/digital-card/card.blade.php)
- [header.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/components/digital-card/header.blade.php)
- [category-strip.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/components/digital-card/category-strip.blade.php)
- [photo.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/components/digital-card/photo.blade.php)
- [npp-badge.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/components/digital-card/npp-badge.blade.php)
- [patrol-badge.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/components/digital-card/patrol-badge.blade.php)
- [meta-info.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/components/digital-card/meta-info.blade.php)
- [details-table.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/components/digital-card/details-table.blade.php) & [details-row.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/components/digital-card/details-row.blade.php)
- [qr-section.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/components/digital-card/qr-section.blade.php)
- [footer.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/components/digital-card/footer.blade.php)

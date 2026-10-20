# Lencana Nama Regu Vertikal & Penghapusan Duplikasi pada Kartu Peserta

## Ringkasan
Memindahkan identitas nama regu peserta ke dalam lencana vertikal modern di sisi paling kanan kartu (mengadopsi desain tenda berkemah kepramukaan) dan menghapus baris "Regu" pada tabel rincian bawah guna menghindari redundansi.

## Rincian Perubahan
1. **Komponen Baru ([`patrol-badge.blade.php`](file:///d:/WebApps/lomba-tingkat/resources/views/components/digital-card/patrol-badge.blade.php))**:
   - Diletakkan di sudut kanan atas kartu (`absolute right-[2.2mm] top-[2.2mm] z-[6]`).
   - Ukuran proporsional: lebar `7mm` dan tinggi `42mm` dengan sudut rounded `3.2mm`.
   - Mengusung ikon tenda berkemah pramuka (SVG) di bagian atas dan garis pembatas halus.
   - Susunan huruf nama regu vertikal tegas ke bawah (misal: R-A-J-A-W-A-L-I atau M-E-L-A-T-I).
   - Skema warna gradient dinamis:
     - **Putra**: Midnight Navy & Royal Blue (`from-[#091e42] via-[#1e3a8a] to-[#2563eb]`).
     - **Putri**: Deep Wine & Magenta (`from-[#500724] via-[#831843] to-[#be185d]`).
2. **Pembersihan Baris "Regu" pada Tabel Rincian**:
   - [`participant.blade.php`](file:///d:/WebApps/lomba-tingkat/resources/views/digital-cards/participant.blade.php): Baris "Regu" dihapus dari `<x-digital-card.details-table>`.
   - [`patrol-batch.blade.php`](file:///d:/WebApps/lomba-tingkat/resources/views/digital-cards/patrol-batch.blade.php): Baris "Regu" dihapus dari `<x-digital-card.details-table>`.
3. **Pengujian**:
   - Seluruh 148 tes unit & feature (`php artisan test`) berhasil 100%.

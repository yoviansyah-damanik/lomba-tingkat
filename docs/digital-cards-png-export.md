# Penyimpanan Kartu Digital ke Format .PNG Kumulatif

Fitur ekspor kartu digital (Format B2 & Batch A4) ke berkas citra `.png` berkualitas tinggi (300+ DPI) secara individual maupun kumulatif.

## Fitur Utama
1. **Penyimpanan Individual (`.png`)**:
   - Tersedia di halaman kartu peserta, bindamping, dan staf aparatur.
   - Menggunakan `html2canvas` dengan resolusi `scale: 3` dan warna latar putih murni.
2. **Penyimpanan Kumulatif Berkas ZIP (`.zip` berisi berkas `.png`)**:
   - Mengonversi seluruh kartu pada regu atau kontingen secara otomatis.
   - Dikompresi menggunakan `JSZip` dengan nama terstruktur (`01_KARTU_<NAMA>_<ID>.png`).
   - Dilengkapi dialog indikator progres pemrosesan (*progress bar*).
3. **Penyimpanan Lembar Utuh A4 (`.png`)**:
   - Mengonversi satu halaman cetak A4 (berisi 4 kartu B2) langsung ke citra PNG resolusi tinggi.
4. **Cetak & Batch Kontingen Utuh**:
   - Rute baru `/digital-cards/scout-group/{id}/batch` untuk melihat, mencetak, dan mengunduh seluruh kartu regu + bindamping dalam satu kontingen.

## Hak Akses
- **Gugusdepan**: Mengakses seluruh kartu dari kontingen miliknya melalui portal pangkalan.
- **Administrator**: Mengakses seluruh kartu dari seluruh kontingen melalui menu detail verifikasi pangkalan.

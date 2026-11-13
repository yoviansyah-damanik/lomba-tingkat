# Penyimpanan & Cetak Kartu Digital (A6 pada Lembar A4)

Fitur cetak langsung kertas A4 (4 kartu A6 per halaman `210mm x 297mm`) dan ekspor kartu digital ke berkas citra `.png` / `.zip`.

## Fitur Utama
1. **Susunan Cetak Lembar A4 (`210mm x 297mm`)**:
   - Memuat 4 kartu ukuran A6 (`105mm x 148mm`, grid `2x2`) per lembar A4 lengkap dengan garis bantu potong.
   - Mendukung cetak langsung (`Cetak A4`) maupun unduh per lembar A4 (`Simpan Lembar A4 (.PNG)`).
2. **Cetak Kumulatif A4 Seluruh Panitia, Peserta & Bindamping**:
   - Seluruh Panitia & Dewan Juri: `/admin/officials/id-cards` (`OfficialsBatch`).
   - Seluruh Peserta Terverifikasi: `/admin/participants/id-cards` (`ParticipantsBatch`).
   - Seluruh Pembina Pendamping: `/admin/chaperones/id-cards` (`ChaperonesBatch`).
   - Tersedia dari menu direktori masing-masing serta halaman **Pusat Laporan & Ekspor Data Resmi**.
3. **Penyimpanan Kumulatif Berkas ZIP (`.zip` berisi `.png`)**:
   - Mengonversi seluruh kartu secara otomatis dengan `JSZip` dan indikator progres.

# Cetak PDF Formulir Resmi & Unduh Semua Berkas Kontingen Gugus Depan (.ZIP)

## Ringkasan Fitur
1. **Cetak PDF Form 01, Form 02, & Form 03**:
   - Seluruh aksi cetak formulir resmi kini menghasilkan dokumen PDF murni (`application/pdf`) menggunakan `PdfService` (Dompdf), bukan cetak webview HTML dengan toolbar browser.
   - **Form 01 (Rekap Regu)**: [`form-01.blade.php`](file:///d:/WebApps/lomba-tingkat/resources/views/portal/scout-group/patrols/form-01.blade.php) — memuat identitas pangkalan, pembina pendamping, dan daftar 8 peserta utama + cadangan dengan tanda tangan pembina.
   - **Form 02 (Biodata Peserta)**: [`participants/print.blade.php`](file:///d:/WebApps/lomba-tingkat/resources/views/portal/scout-group/participants/print.blade.php) — memuat identitas lengkap, pas foto 3x4 (embedded Base64), data orang tua, riwayat pendidikan, dan kepramukaan.
   - **Form 03 (Biodata Bindamping)**: [`chaperones/print.blade.php`](file:///d:/WebApps/lomba-tingkat/resources/views/portal/scout-group/chaperones/print.blade.php) — memuat data pembina pendamping, pas foto (embedded Base64), riwayat kursus kepramukaan, dan tanda tangan Ka. Mabigus.
   - Mendukung pratinjau langsung di viewer PDF browser (`inline`) atau unduhan langsung (`?download=1`).

2. **Perbaikan Unduh Semua Berkas (.ZIP)**:
   - Diperbaiki di [`ZipExportController.php`](file:///d:/WebApps/lomba-tingkat/app/Http/Controllers/ZipExportController.php) menggunakan streaming `response()->download(...)->deleteFileAfterSend(true)`.
   - Menjamin seluruh relasi model (`patrol.scoutGroup`, `chaperone.scoutGroup`, `participant.patrol`) terhubung saat kompilasi PDF.
   - Pengecekan dan pembacaan berkas unggahan storage public yang aman tanpa mengunci file.

## Struktur Arsip ZIP
1. `00_Bukti_Registrasi_{Gudep}.pdf` — Bukti Registrasi resmi ([`registration-certificate.blade.php`](file:///d:/WebApps/lomba-tingkat/resources/views/admin/scout-groups/registration-certificate.blade.php)).
2. `01_Gudep/` — Berkas unggahan pangkalan (`Surat_Tugas_Mandat.{ext}`, `Bukti_Pembayaran.{ext}`).
3. `02_Regu_{Putra/Putri}_{NamaRegu}/`:
   - `Form_01_Rekap_Regu_{NamaRegu}.pdf`
   - `Peserta_{No}_{NamaPeserta}/`: `Form_02_Biodata_{NamaPeserta}.pdf` + berkas unggahan (Pas Foto, KTA, BPJS, Surat Kesehatan, Akta Kelahiran, Kartu Keluarga, SKU, SKK), masing-masing dinamai `{Jenis}_{NamaPeserta}.{ext}` (contoh `KTA_Rizky_Harahap.jpg`).
4. `03_Bindamping_{No}_{Gender}_{Nama}/`:
   - `Form_03_Biodata_{NamaBindamping}.pdf` + `Pas_Foto_{NamaBindamping}.{ext}`.

## Rute Terkait
- `admin.scout-groups.patrols.form-01` & `scout-group.patrols.form-01`
- `admin.scout-groups.participants.print` & `scout-group.participants.print`
- `admin.scout-groups.chaperones.print` & `scout-group.chaperones.print`
- `admin.scout-groups.download-zip`

# Real Live Progress Bar & Bukti Registrasi Kontingen

Dokumentasi implementasi progress bar visual interaktif saat pengunduhan berkas serta penambahan tombol unduh Bukti Registrasi Kontingen.

## 1. Fitur Utama

### A. Real Live Progress Bar (0% - 100%)
- **Arsip ZIP Kontingen**:
  - Menggunakan Server-Sent Events (SSE) via endpoint [`/scout-groups/{id}/download-zip-stream`](file:///d:/WebApps/lomba-tingkat/routes/web.php).
  - Melaporkan langkah demi langkah secara real-time:
    1. `15%`: Mencetak Bukti Registrasi Kontingen (PDF)
    2. `25%`: Menyisipkan Surat Tugas & Bukti Pembayaran
    3. `30% - 75%`: Mencetak Form 01 Regu dan Form 02 tiap peserta beserta berkas persyaratan
    4. `80% - 90%`: Mencetak Form 03 tiap bindamping dan pas foto
    5. `95%`: Mengompresi dan memfinalisasi arsip `.zip`
    6. `100%`: Berkas selesai disiapkan dan diunduh otomatis
- **Berkas Tunggal (Single File)**:
  - Menggunakan API `fetch()` dengan pembacaan stream berkas.
  - Menampilkan progress visual bertahap dari koneksi server, rendering lembar PDF A4, penerimaan data stream, hingga penyimpanan berkas dengan nama file asli.

### B. Tombol Unduh Bukti Registrasi
- Disediakan pada:
  1. **Dasbor Gugus Depan**: [`scout-group-dashboard.blade.php`](file:///d:/WebApps/lomba-tingkat/resources/views/livewire/portal/scout-group-dashboard.blade.php)
  2. **Pemeriksaan Ulang Berkas Kontingen (Review)**:
     - [`review-registration.blade.php`](file:///d:/WebApps/lomba-tingkat/resources/views/livewire/portal/review-registration.blade.php) (Livewire 4 SPA)
     - [`review.blade.php`](file:///d:/WebApps/lomba-tingkat/resources/views/portal/scout-group/review.blade.php)
- **Aturan Akses**:
  - Tombol dan akses unduh hanya aktif ketika pendaftaran kontingen telah berstatus **Terverifikasi** (`$scoutGroup->canDownloadDocuments()`).
  - Proteksi server-side: mengembalikan error 403 Forbidden jika kontingen belum diverifikasi.

## 2. Rute Terkait
- `scout-group.registration-certificate`: Unduh PDF Bukti Registrasi oleh Gudep.
- `admin.scout-groups.registration-certificate`: Unduh PDF Bukti Registrasi oleh Admin/Panitia.
- `admin.scout-groups.download-zip-stream`: Streaming progress pembuatan ZIP kontingen.
- `admin.scout-groups.download-zip-file`: Unduh arsip ZIP yang selesai di-stream via token sementara.

# Dokumentasi Fitur Edit Presensi, Rekapitulasi, dan Integrasi Antartahap

## 1. Perbaikan Modal Tinjau Gugusdepan
- **Lokasi**: [verification-management.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/livewire/admin/verification-management.blade.php)
- **Perbaikan**: Mengganti pemanggilan properti yang salah:
  - `$p->name` ➔ `$p->patrol_name`
  - `$p->gender` ➔ `$p->isBoys()` / `$p->gender_category`
  - `$part->name` ➔ `$part->full_name`
  - `$part->nta` ➔ `$part->scout_id` (NTA), `$part->national_student_id` (NISN), `$part->id_number`
  - Peran PINRU/WAPINRU ➔ `$part->isLeader()`, `$part->isAssistantLeader()`
  - `$chap->name`, `$chap->nta`, `$chap->phone` ➔ `$chap->full_name`, `$chap->scout_id`, `$chap->national_identity_number`, `$chap->phone_number`

## 2. Opsi Edit Data Presensi
- **Backend**:
  - `AttendanceScannerController::update` via route `PUT /portal/attendance/records/{attendance}`.
  - Memvalidasi dan memperbarui `status` (`present`, `late`, `excused`, `absent`), `scanned_at`, dan `notes`.
- **Frontend**:
  - **Scanner View**: Modal edit interaktif di log presensi realtime.
  - **Recap View**: Modal Livewire interaktif `openEditModal` & tombol cepat `quickUpdateStatus`.

## 3. Halaman Rekap Presensi & Opsi Cetak
- **Komponen Livewire**: [AttendanceRecap.php](file:///d:/WebApps/lomba-tingkat/app/Livewire/Portal/AttendanceRecap.php)
- **Tampilan Blade**: [attendance-recap.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/livewire/portal/attendance-recap.blade.php)
- **Fitur**:
  - Filter: Sesi presensi, tipe sesi (lomba/kehadiran umum), jenis entitas, status, tanggal, dan pencarian teks.
  - Kartu KPI: Total, Hadir Tepat Waktu, Terlambat, Izin/Dispensasi, Alpa/Absen.
  - Manajemen: Edit presensi, ganti status cepat, pembatalan/penghapusan.
- **Cetak Presensi**:
  - Route: `attendance.recap.print` ([recap-print.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/portal/attendance/recap-print.blade.php)).
  - Format standar KOP resmi Gerakan Pramuka Kwarcab Kota Padangsidimpuan, rekap statistik, tabel lengkap, dan kolom pengesahan tanda tangan.

## 4. Analisis & Integrasi Antartahap
1. **Tahap Verifikasi Pendaftaran ➔ ID Card QR**:
   Kontingen yang diverifikasi (`Verified`) memiliki token QR resmi pada kartu B2 dan kartu bindamping.
2. **Tahap Jadwal ➔ Sesi Presensi Pos**:
   Setiap jadwal lomba otomatis menerbitkan sesi presensi pos lomba (buka 30 menit sebelum lomba, tutup saat lomba berakhir). Sesi kehadiran umum dikelola terpisah untuk apel/upacara.
3. **Tahap Presensi ➔ Tahap Penilaian Juri**:
   Kehadiran peserta/regu di pos memastikan regu hadir secara sah sebelum juri menginputkan nilai lomba di menu Rekap Nilai & Klasemen.
4. **Tahap Penilaian ➔ Klasemen Akhir**:
   Nilai juri diakumulasi menjadi medali emas, perak, dan perunggu untuk menentukan Juara Umum Tergiat I, II, dan III.

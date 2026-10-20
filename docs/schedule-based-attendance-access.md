# Schedule-Based Attendance Access & Automated Presensi Window

## Overview
Penyesuaian akses sesi presensi dan penghapusan input jadwal check-in manual agar terotomatisasi secara ketat mengikuti jadwal lomba.

## Aturan Utama
1. **Otomatisasi Presensi**:
   - Presensi dibuka paling cepat **30 menit sebelum waktu lomba dimulai** (`start_time - 30 minutes`).
   - Presensi ditutup tepat saat **waktu sesi lomba berakhir** (`end_time`).
2. **Penghapusan Check-in Manual**:
   - Menghapus input manual `checkin_open_time` dan `checkin_close_time` pada form admin jadwal.
   - Mengganti seluruh terminologi "Check-in" menjadi "Presensi".
3. **Pemblokiran Kamera & Validasi Scanner**:
   - Jika presensi belum dibuka atau telah berakhir, viewport kamera (`#qr-reader`) diblokir total secara fisik dan digantikan dengan tampilan peringatan detail jadwal.
   - Fungsi inisialisasi dan pemindaian scanner kamera dihentikan saat di luar jadwal resmi.
   - Pemindaian sebelum `start_time - 30m` ditolak dengan kode `SCHEDULE_NOT_OPEN`.
   - Pemindaian setelah `end_time` ditolak dengan kode `SCHEDULE_CLOSED`.
4. **Format Waktu Bahasa Indonesia**:
   - Seluruh tampilan tanggal dan jam di seluruh sistem menggunakan locale Bahasa Indonesia (`isoFormat` / `translatedFormat`, hari/bulan dalam Bahasa Indonesia, dan zona waktu resmi WIB).
5. **Kolom Sesi Presensi pada Jadwal Lomba & Penugasan Juri**:
   - Secara otomatis mengevaluasi rentang waktu `start_time` dan `end_time` lomba terhadap waktu saat ini.
   - **Otomatis Terbuka H-30m**: Jika waktu saat ini telah mencapai H-30 menit sebelum mulai hingga selesai, status otomatis berubah menjadi `Aktif Scan (Otomatis Terbuka)` dan scanner siap digunakan.
   - **Tutup Manual Saat Waktu Lomba**: Admin dapat menutup sesi presensi sewaktu-waktu saat waktu lomba sedang berlangsung (`Tutup Sesi`), dan membukanya kembali kapan saja (`Buka Kembali Sesi`).
   - **Sebelum H-30m**: Menampilkan badge `Otomatis Buka H-30m` beserta jam buka resmi, dengan opsi kunci sesi.
   - **Setelah Selesai**: Menampilkan badge `Presensi Ditutup (Selesai)` dengan opsi buka susulan darurat jika diperlukan.

## File Terkait
- [CompetitionSchedule.php](file:///d:/WebApps/lomba-tingkat/app/Models/CompetitionSchedule.php) - Helper `isAttendanceScanningReady()`, `attendanceSessionStatusLabel()`, `attendanceSessionBadgeClasses()`.
- [SchedulesManagement.php](file:///d:/WebApps/lomba-tingkat/app/Livewire/Admin/SchedulesManagement.php) & [schedules-management.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/livewire/admin/schedules-management.blade.php) - Kolom Sesi Presensi otomatis berbasis jadwal dan toggle manual saat waktu lomba.
- [AttendanceSession.php](file:///d:/WebApps/lomba-tingkat/app/Models/AttendanceSession.php) - Helper `isScheduleOpen()`, `isScheduleClosed()`, `isWithinSchedule()`.
- [AttendanceService.php](file:///d:/WebApps/lomba-tingkat/app/Services/AttendanceService.php) - Validasi jadwal pada `recordScan()` dan `manualCheckIn()`.
- [scanner.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/portal/attendance/scanner.blade.php) - Pemblokiran viewport kamera, banner status, dan tampilan waktu server WIB.
- [AdminSchedulesLivewireTest.php](file:///d:/WebApps/lomba-tingkat/tests/Feature/AdminSchedulesLivewireTest.php) - Pengujian otomasi kolom presensi pada jadwal lomba.
- [AttendanceScannerTest.php](file:///d:/WebApps/lomba-tingkat/tests/Feature/AttendanceScannerTest.php) - Pengujian fitur presensi berdasarkan jadwal.

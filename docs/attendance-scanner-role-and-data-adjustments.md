# Attendance Scanner Role-Based Access & Data Description Adjustments

## 1. Ringkasan Fitur
Penyesuaian otorisasi akses dan keterangan data sesi presensi scanner QR untuk peran Panitia, Juri, dan Administrator:
1. **Panitia (`committee`)**:
   - Hanya memiliki akses ke sesi **Kehadiran Umum** (`is_competition = false`, `competition_schedule_id = null`), seperti Apel, Upacara, dan Registrasi.
   - Tidak dapat melihat, membuka kamera scanner, atau mencatat presensi untuk sesi mata lomba (`403 Forbidden`).
2. **Dewan Juri (`judge`)**:
   - Hanya memiliki akses ke sesi **Presensi Pos Lomba** yang sesuai dengan jadwal penugasan juri (`judge_assignments`).
   - Tidak dapat mengakses sesi kehadiran umum atau sesi lomba yang tidak ditugaskan (`403 Forbidden`).
3. **Administrator (`admin`)**:
   - Memiliki akses penuh ke seluruh sesi (Kehadiran & Lomba) serta dapat membuat sesi kehadiran baru.
4. **Dual Role (Panitia & Juri)**:
   - Memiliki akses ke sesi Kehadiran umum dan sesi lomba yang ditugaskan.

---

## 2. Penyesuaian Keterangan Data Sesi
1. **Status Pill Sesi**:
   - `NONAKTIF`: Jika dinonaktifkan oleh administrator (`is_active = false`).
   - `DITUTUP`: Jika waktu jadwal telah berakhir (`isScheduleClosed() = true`), menghilangkan kontradiksi badge "AKTIF SCAN" pada sesi yang sudah tutup.
   - `BELUM BUKA`: Jika waktu belum mencapai jadwal buka presensi (`! isScheduleOpen()`).
   - `AKTIF SCAN`: Hanya jika sesi aktif dan dalam rentang jadwal resmi (`isWithinSchedule() = true`).
2. **Label dan Deskripsi Hadir Terdata**:
   - **Sesi Lomba Regu Utuh**: `Regu Hadir Terdata:` & `Wajib dipindai PINRU/WAPINRU`.
   - **Sesi Lomba Kelompok**: `Peserta Hadir Terdata:` & `Wajib dipindai anggota kelompok regu`.
   - **Sesi Lomba Individu**: `Peserta Hadir Terdata:` & `1 perwakilan peserta per regu`.
   - **Sesi Kehadiran Panitia Pos**: `Panitia Hadir Terdata:` & `Pemindaian kartu aparatur panitia pelaksana`.
   - **Sesi Kehadiran Dewan Juri**: `Juri Hadir Terdata:` & `Pemindaian kartu aparatur dewan juri`.
   - **Sesi Kehadiran Pembina**: `Pendamping Hadir Terdata:` & `Pemindaian kartu identitas pembina pendamping`.
   - **Sesi Kehadiran Peserta**: `Peserta Hadir Terdata:` & `Pemindaian kartu identitas peserta (B2) perorangan`.
3. **Waktu Pelaksanaan**:
   - Menampilkan rentang waktu mulai dan batas presensi pada kartu kehadiran umum.

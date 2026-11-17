# Matriks Penyesuaian Presensi (Attendance Matrix)

Modul penyesuaian data kehadiran tertinggal dalam format matriks (cross-tabulation) lintas sesi presensi dan pos perlombaan/perkemahan.

## 1. Rute & Akses
- **Rute**: `GET /portal/attendance/matrix` (`route('attendance.matrix')`)
- **Otorisasi**: Khusus Administrator (`role:admin`).
- **Komponen Livewire**: `App\Livewire\Portal\AttendanceMatrix`
- **Layanan Inti**: `App\Services\AttendanceAdjustmentService`

## 2. Struktur Matriks
- **Baris (Entitas)**:
  - `Per Regu` (Patrol) — default.
  - `Per Peserta` (Individual Participant).
  - `Pembina Pendamping` (Chaperone).
  - `Panitia` (Committee / Aparatur Pelaksana).
  - `Dewan Juri` (Judges / Penilai Lomba).
- **Kolom (Sesi Presensi)**:
  - Menampilkan sesi presensi yang otomatis **diselaraskan dengan Sasaran Entitas Presensi** (`session_type`) dari cakupan aktif:
    - *Peserta*: Sesi presensi ber-sasaran Peserta (`session_type = participant`).
    - *Pembina*: Sesi presensi ber-sasaran Pembina Pendamping (`session_type = chaperone`).
    - *Panitia*: Sesi presensi ber-sasaran Panitia (`session_type = committee`).
    - *Dewan Juri*: Sesi pos lomba (`is_competition = true`) dan sesi juri (`session_type = judge`).
  - Dilengkapi filter manual *Sasaran Entitas* pada toolbar dan badge sasaran di setiap *header* kolom sesi.
- **Sel (Perpotongan Baris & Kolom)**:
  - `H` (Hadir - Hijau)
  - `T` (Terlambat - Kuning/Amber)
  - `I` (Izin/Dispensasi - Biru)
  - `A` (Alpa/Tidak Hadir - Merah)
  - `-` (Belum Presensi - Abu-abu)

## 3. Fitur Penyesuaian
1. **Aksi Cepat 1-Klik**: Klik tombol `-` pada sel untuk langsung menandai `Hadir`.
2. **Modal Sel Satuan**: Klik badge status untuk mengubah status (`present`, `late`, `excused`, `absent`), mengatur waktu presensi kustom, mengisi catatan, atau menghapus catatan presensi (reset ke `-`).
3. **Penyesuaian Massal Baris Terpilih (Batch Rows)**: Checklist beberapa baris lalu terapkan status pada sesi target sekaligus.
4. **Tindakan Kolom Seluruh Sesi**: Tombol opsi di header sesi untuk menandai seluruh entitas (atau hanya yang belum presensi) pada sesi tersebut secara serentak.

## 4. Performa & Query
- Menggunakan `AttendanceAdjustmentService::getMatrixMap()` yang memuat seluruh presensi pada halaman aktif dalam **1 query SQL terindeks** ($O(1)$ lookup), bebas masalah N+1.
- Kolom kiri (NPP, Nama Regu/Peserta) dan kolom rekap kanan bersifat *sticky* untuk kemudahan *scrolling* horizontal pada tabel matriks yang lebar.

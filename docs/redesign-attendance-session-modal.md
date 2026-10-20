# Redesain Modal Tambah Sesi Presensi Kehadiran

## 1. Ringkasan Perubahan
- **Berkas Tampilan**: [`resources/views/livewire/portal/attendance-dashboard.blade.php`](file:///d:/WebApps/lomba-tingkat/resources/views/livewire/portal/attendance-dashboard.blade.php)
- **Komponen Livewire**: [`app/Livewire/Portal/AttendanceDashboard.php`](file:///d:/WebApps/lomba-tingkat/app/Livewire/Portal/AttendanceDashboard.php)
- **Uji Fitur**: [`tests/Feature/AttendanceDashboardLivewireTest.php`](file:///d:/WebApps/lomba-tingkat/tests/Feature/AttendanceDashboardLivewireTest.php)

## 2. Poin Peningkatan Redesain UI/UX
1. **Dimensi & Proporsi Modal**:
   - Diperluas dari `max-w-md` (sempit/terjepit) menjadi `max-w-xl sm:max-w-2xl` dengan batas tinggi responsif `max-h-[92vh] overflow-y-auto`.
2. **Header Modal Modern**:
   - Dilengkapi ikon gradient hijau zamrud (`emerald-500` ke `teal-700`), badge *Presensi Kegiatan Umum*, judul tegas, dan penjelasan peruntukan sesi non-lomba.
3. **Pilihan Cepat Judul (Preset Chips)**:
   - Tombol rekomendasi cepat satu-klik: *Apel Pembukaan*, *Daftar Ulang Kontingen*, *Apel Pagi Buper*, *Api Unggun*, dan *Upacara Penutupan*.
4. **Pemilih Sasaran Berbentuk Kartu Interaktif**:
   - Menggantikan dropdown biasa dengan grid 4 kartu interaktif (Peserta, Bindamping, Panitia, Juri) yang memiliki ikon, deskripsi entitas, dan indikator aktif.
5. **Jadwal & Periode Waktu Fleksibel**:
   - Kolom waktu buka dan tutup tertata sejajar 2 kolom dengan teks petunjuk jelas.
6. **Kotak Info & Status Aktivasi**:
   - Callout petunjuk pemindaian QR code dan switch aktivasi sesi langsung dengan status badge *Siap Pindai* vs *Draft / Tertutup*.

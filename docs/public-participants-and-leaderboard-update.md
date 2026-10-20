# Pembaruan Direktori Peserta Publik & Klasemen Prestasi

## 1. Pembaruan Leaderboard Publik
- **Penyesuaian Judul & Header**: Mengubah judul pada halaman leaderboard publik menjadi **Klasemen Perolehan Prestasi** ([leaderboard-page.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/livewire/public/leaderboard-page.blade.php)).
- **Penghapusan Indikator Tombol Live Rank**: Menghilangkan tombol/badge status *Live Rank Aktif* di header klasemen publik.

## 2. Halaman Direktori Peserta Publik
- **Route & Komponen**:
  - Route: `/participants` (`route('participants')`) di [routes/web.php](file:///d:/WebApps/lomba-tingkat/routes/web.php).
  - Komponen Livewire: [ParticipantsPage.php](file:///d:/WebApps/lomba-tingkat/app/Livewire/Public/ParticipantsPage.php).
  - Tampilan Blade: [participants-page.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/livewire/public/participants-page.blade.php).
- **Filter Gugus Depan Terverifikasi**:
  - Hanya menampilkan pangkalan Gugus Depan yang telah berstatus terverifikasi (`RegistrationStatusEnum::Verified`).
  - Menghilangkan label status verifikasi ("Disetujui & Terverifikasi") dari seluruh kartu pangkalan pada tampilan publik.
- **Tampilan Modal Dialog & Responsif Mobile**:
  - Menggunakan komponen `<flux:modal wire:model="showDetailModal">` untuk menampilkan rincian pangkalan terpilih.
  - **Mobile Friendly**:
    - Ukuran modal adaptif (`max-h-[92vh] sm:max-h-[90vh] overflow-y-auto`) dengan padding optimal di ponsel.
    - Penataan kartu Bindamping (Putra & Putri) dan Regu Penggalang (Putra & Putri) yang responsif stack vertikal di layar ponsel.
    - Informasi Bindamping difokuskan pada Nomor Tanda Anggota (NTA) tanpa menampilkan nomor kontak/telepon pribadi demi menjaga privasi publik.
    - Keterangan "Siswa/i Penggalang" pada daftar anggota regu digantikan dengan **NTA** peserta (`NTA: [scout_id] • Penggalang [Tingkat]`).
    - **Lencana Tanda PINRU / WAPINRU dengan Ikon**:
      - **PINRU**: Dilengkapi ikon balok 3 garis kepemimpinan Pramuka (`bars-3`) dengan label tegas `PINRU` berbingkai amber khas LT III.
      - **WAPINRU**: Dilengkapi ikon balok 2 garis wakil pemimpin (`bars-2`) dengan label tegas `WAPINRU` berbingkai biru.
      - **Anggota**: Tanda netral `Anggota` yang rapi dan ringkas.
      - Dilengkapi atribut aksesibilitas dan tooltip deskripsi lengkap (*Pimpinan Regu (Pinru)* / *Wakil Pimpinan Regu (Wapinru)*).
    - Tombol penutup dialog disesuaikan: bagian sudut kanan atas menggunakan tombol tutup silang (`✕`) bawaan Flux secara bersih tanpa tombol duplikat di header, dan footer modal dilengkapi tombol "Tutup" solid (`<flux:modal.close>`) yang responsif.
    - Sinkronisasi penutupan modal dengan Livewire (`wire:close="clearSelection"`) untuk membersihkan query string URL secara otomatis.
- **Navigasi Publik**:
  - Menu **Peserta** ditambahkan pada navigasi desktop dan menu mobile di [layouts/public.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/layouts/public.blade.php).
  - Ditambahkan tautan tombol pintasan **Peserta Kontingen** di hero beranda [welcome.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/welcome.blade.php).

## 3. Penghapusan Kolom & Atribut NIS/NISN
- **Tabel Database**:
  - Migration `2026_10_20_000550_drop_national_student_id_from_participants_table.php` mengeksekusi penghapusan kolom `national_student_id` dari tabel `participants`.
- **Model & Controller**:
  - Menghapus atribut `national_student_id` dari properti `$fillable` di model [Participant.php](file:///d:/WebApps/lomba-tingkat/app/Models/Participant.php).
  - Menghapus aturan validasi `national_student_id` pada method `storeParticipant` dan `updateParticipant` di [ScoutGroupPortalController.php](file:///d:/WebApps/lomba-tingkat/app/Http/Controllers/ScoutGroupPortalController.php).
- **Layanan & Query Pencarian**:
  - Menghapus pencarian berdasarkan NISN di [AttendanceService.php](file:///d:/WebApps/lomba-tingkat/app/Services/AttendanceService.php), [ParticipantsDirectory.php](file:///d:/WebApps/lomba-tingkat/app/Livewire/Admin/ParticipantsDirectory.php), dan [AttendanceRecap.php](file:///d:/WebApps/lomba-tingkat/app/Livewire/Portal/AttendanceRecap.php).
  - Menghapus kolom NISN dari ekspor CSV buku induk peserta di [ReportExportService.php](file:///d:/WebApps/lomba-tingkat/app/Services/ReportExportService.php).
- **Tampilan Blade & Antarmuka**:
  - Menghilangkan label, kolom, dan badge NISN dari [participants-directory.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/livewire/admin/participants-directory.blade.php), [verification-management.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/livewire/admin/verification-management.blade.php), [attendance-recap.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/livewire/portal/attendance-recap.blade.php), dan [reports-center.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/livewire/admin/reports-center.blade.php).
- **Pengujian (Tests)**:
  - Seluruh pengujian fitur registrasi, presensi, audit verifikasi, dan ekspor diperbarui dan lulus 100% (246 passed).

## 4. Penghapusan Tombol Cetak Publik
- Menghapus tombol **Cetak** (`window.print()`) pada header halaman **Klasemen Perolehan Prestasi** ([leaderboard-page.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/livewire/public/leaderboard-page.blade.php)).
- Menghapus tombol **Cetak** (`window.print()`) pada header halaman **Peserta & Pangkalan Kontingen** ([participants-page.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/livewire/public/participants-page.blade.php)).
- Tampilan antarmuka publik kini lebih bersih dan terfokus pada konsumsi informasi digital interaktif.

## 5. Penyesuaian Warna Desain Mode Gelap (Dark Mode)
- **Modal Rincian Seluruh Peserta & Bindamping**:
  - **Kartu Regu Putra & Putri**: Kontainer ditingkatkan dengan latar `dark:bg-zinc-900/90` dan batas aksen tema `dark:border-blue-900/50` serta `dark:border-rose-900/50` dengan bayangan elevasi `dark:shadow-md`.
  - **Baris Peserta**: Ditambahkan efek interaktif lembut saat kursor melayang (`dark:hover:bg-blue-950/30` untuk putra dan `dark:hover:bg-rose-950/30` untuk putri).
  - **Kotak Nomor Urut**: Dilengkapi batas aksen netral `dark:border-zinc-700/60` dan teks kontras `dark:text-zinc-300`.
  - **Informasi NTA & SKU**: Memperbaiki teks keterangan yang sebelumnya redup menjadi lebih tegas (`dark:text-zinc-400`, `dark:text-zinc-200`, dan `dark:text-zinc-300`).
  - **Lencana PINRU / WAPINRU / Anggota**: Memastikan lencana jabatan memiliki kontras yang jelas dan hidup di atas latar gelap.
  - **Kartu Bindamping**: Diberikan batas `dark:border-blue-800/50` & `dark:border-rose-800/50`, latar transparan `dark:bg-blue-950/30` & `dark:bg-rose-950/30`, serta warna badge yang tajam.
- **Halaman Direktori & Pencarian**:
  - Input pencarian dan seleksi kwarran diberikan kontras fokus `dark:focus:bg-zinc-900` dan `dark:focus:border-amber-400`.
  - Kartu pangkalan terpilih mendapatkan highlight amber yang lebih berbobot (`dark:bg-amber-950/30 dark:ring-amber-500/60`).
  - Kotak ringkasan regu putra dan putri serta footer kartu pangkalan diselaraskan agar teks dan angka statistik mudah dibaca dalam mode gelap.

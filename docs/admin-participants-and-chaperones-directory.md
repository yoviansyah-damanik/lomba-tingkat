# Admin Directory: Peserta & Pembina Pendamping (Bindamping)

## Ringkasan Fitur
Menambahkan dua modul direktori khusus Administrator untuk memeriksa dan mengelola data peserta dan pembina pendamping (bindamping) yang telah diverifikasi:

1. **Direktori Peserta (`/admin/participants`)**:
   - Komponen: `App\Livewire\Admin\ParticipantsDirectory`
   - View: `resources/views/livewire/admin/participants-directory.blade.php`
   - Scope: Menampilkan seluruh peserta penggalang yang lolos verifikasi administrasi (`status = RegistrationStatusEnum::Verified`).
   - Fitur:
     - 5 Kartu statistik: Total Peserta Terverifikasi, Penggalang Putra, Penggalang Putri, Pimpinan Regu (Pinru/Wapinru), dan Jumlah Pangkalan.
     - Pencarian teks (Nama, NTA, NISN, ID, Regu, Pangkalan).
     - Filter kategori gender (Putra/Putri), jabatan regu (Pinru/Wapinru/Anggota), pangkalan Gudep, dan filter status.
     - Modal Slide-Over detail biodata peserta lengkap (Form-01).
     - Pintasan aksi cetak Form Biodata Resmi dan Cetak Kartu Digital B2 ber-QR.

2. **Direktori Pembina Pendamping (`/admin/chaperones`)**:
   - Komponen: `App\Livewire\Admin\ChaperonesDirectory`
   - View: `resources/views/livewire/admin/chaperones-directory.blade.php`
   - Scope: Menampilkan seluruh pembina pendamping terverifikasi (`status = RegistrationStatusEnum::Verified`).
   - Fitur:
     - 5 Kartu statistik: Total Bindamping, Pembina Putra, Pembina Putri, Bersertifikat Kursus (KMD/KML), dan Pangkalan Terwakili.
     - Pencarian teks (Nama, NTA, NIK, No. HP, Pangkalan).
     - Filter kategori pendamping (Putra/Putri), pangkalan Gudep, dan status.
     - Modal Slide-Over detail biodata lengkap (data pribadi, kursus kepramukaan, pengalaman kepramukaan, dan token QR).
     - Pintasan aksi cetak Form Biodata Resmi Pembina dan Kartu Pengenal Digital B2.

3. **Navigasi & Akses**:
   - Ditambahkan pada menu sidebar Administrator ([sidebar.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/layouts/app/sidebar.blade.php)) dengan `wire:navigate`.
   - Diintegrasikan dengan kartu statistik dan tombol aksi pada dasbor admin ([dashboard.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/livewire/dashboard.blade.php)).

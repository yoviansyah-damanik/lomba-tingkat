# Akses Panitia & Dewan Juri untuk Halaman Peserta, Bindamping, dan Gugusdepan

## Ringkasan Perubahan
Akses baca (read-only directories & ID cards) untuk direktori **Peserta**, **Pembina Pendamping (Bindamping)**, dan **Pangkalan Gugusdepan** kini diberikan kepada pengguna dengan peran **Panitia (`committee`)** dan **Dewan Juri (`judge`)**, berdampingan dengan **Administrator (`admin`)**.

## 1. Routing (`routes/web.php`)
Rute direktori dipindahkan ke grup middleware `role:admin|committee|judge`:
- `GET /admin/participants` (`admin.participants.index`)
- `GET /admin/chaperones` (`admin.chaperones.index`)
- `GET /admin/scout-groups` (`admin.scout-groups.index`)
- `GET /admin/scout-groups/{id}` (`admin.scout-groups.show`)
- Dokumen cetak formulir & ID card batch kontingen

Tindakan mutasi keputusan verifikasi (`verify`, `revision`, `reject`) tetap dibatasi khusus peran `admin`.

## 2. Otorisasi Kartu Digital (`app/Http/Controllers/DigitalCardController.php`)
Diperbarui agar akun `isCommittee()` dan `isJudge()` dapat melihat ID card individual maupun batch milik seluruh pangkalan peserta dan pendamping tanpa batasan pemilik kontingen.

## 3. Komponen Verifikasi (`app/Livewire/Admin/VerificationManagement.php` & Views)
- Metode mutasi aksi verifikasi dijaga dengan pemeriksaan hak akses `isAdmin()`.
- Tombol aksi verifikasi (Setujui, Minta Revisi, Tolak) pada modal dan halaman detail disembunyikan untuk non-admin.

## 4. Navigasi & Dasbor (`sidebar.blade.php` & `dashboard.blade.php`)
- Sidebar Panitia & Dewan Juri kini memiliki tautan menu: *Direktori Peserta*, *Pembina Pendamping*, dan *Pangkalan Gugusdepan*.
- Kartu navigasi cepat terintegrasi di dasbor utama peran Panitia dan Juri.

## 5. Label Peran Pengguna Login (`User::roleLabel()`)
Label peran pada header navbar publik (`resources/views/layouts/public.blade.php`) kini secara dinamis menampilkan peran spesifik pengguna login alih-alih teks *default* "Pengguna":
- `admin` -> **Administrator**
- `committee` -> **Panitia**
- `judge` -> **Juri**
- `committee & judge` -> **Panitia & Juri**
- `scout_group` -> **Gugus Depan**
- `participant` -> **Peserta**

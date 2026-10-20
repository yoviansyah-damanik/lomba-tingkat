# Authentication and Header Redesign

## Overview
Penyesuaian tata letak navigasi halaman utama dan redesain total halaman login sistem LT III Kwarcab Padangsidimpuan.

## Key Changes
1. **Public Header Navigation (`layouts/public.blade.php`)**:
   - **Authenticated (`@auth`)**:
     - Tombol standalone "Dashboard" dihapus.
     - Kapsul data pengguna diubah menjadi **User Dropdown Menu** (menampilkan identitas, role, tombol **Halaman Dasbor**, **Pengaturan Profil**, dan **Keluar / Logout**).
   - **Guest (`@guest`)**:
     - Pada desktop: Hanya menampilkan tombol `Masuk`.
   - **Mobile View (`md:hidden`)**:
     - Topbar disederhanakan tanpa tombol login/user di bar utama.
     - Seluruh data akun / tombol masuk disematkan ke dalam mobile hamburger dropdown:
       - User terautentikasi: Kartu profil lengkap dengan tombol **Dasbor**, **Profil**, dan **Keluar**.
       - Tamu/Guest: Tombol **Masuk ke Akun** dan **Daftar Gugusdepan Baru**.
2. **Hero Section (`welcome.blade.php`)**:
   - Jika sudah login: Tombol "Dashboard" dihilangkan; fokus pada tombol eksplorasi kegiatan.
   - Jika belum login: Tombol CTA `Masuk ke Sistem`.
3. **Login Page Total Redesign (`pages/auth/login.blade.php`)**:
   - **Split Screen Layout**:
     - *Left Showcase*: Branding resmi Kwarcab Kota Padangsidimpuan, logo perisai LT III bercahaya, 3 fitur utama (Portal Gudep, Presensi QR, Blind Scoring), dan moto pramuka.
     - *Right Panel*: Formulir login responsif, opsi "Ingat Saya", tombol submit bercahaya `Masuk ke Sistem`, dan kartu khusus **Pendaftaran Pangkalan Gudep Baru** menuju `/register/scout-group`.

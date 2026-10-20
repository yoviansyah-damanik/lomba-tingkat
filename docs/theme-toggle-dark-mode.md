# Dark Mode Switcher Documentation

## Overview
Penambahan tombol sakelar mode gelap/terang (Dark Mode Toggle) pada seluruh halaman publik dan dasbor aplikasi LT III Kwarcab Padangsidimpuan.

## Component: `<x-theme-toggle />`
- **File:** `resources/views/components/theme-toggle.blade.php`
- **Mekanisme:**
  - Menggunakan Alpine.js reactive state dan `MutationObserver` untuk mendeteksi perubahan kelas `.dark` pada `<html>`.
  - Terintegrasi dengan `window.Flux.applyAppearance()` dan `localStorage.setItem('flux.appearance', ...)` untuk menyimpan preferensi pengguna secara persisten.
  - Menampilkan ikon Matahari (mode gelap aktif -> klik untuk terang) dan ikon Bulan (mode terang aktif -> klik untuk gelap).

## Penempatan Tombol:
1. **Halaman Publik & Beranda (`layouts/public.blade.php`):**
   - Header Desktop: Terpasang di sebelah kanan tombol Masuk/Dashboard.
   - Menu Mobile: Terpasang di dalam dropdown navigasi mobile.
2. **Dasbor & Portal Kontingen:**
   - **Sidebar Desktop (`layouts/app/sidebar.blade.php`):** Baris "Mode Tampilan" di bagian bawah sebelum profil pengguna.
   - **Header Mobile (`layouts/app/sidebar.blade.php`):** Di samping avatar profil mobile.
   - **Banner Dasbor (`dashboard.blade.php`):** Di samping widget Waktu Server Resmi WIB.

# Mobile Dark Mode Toggle Fix

## Root Cause
- Pada tampilan mobile view, tombol dark mode (`<x-theme-toggle />`) muncul ganda karena:
  1. Header navbar publik selalu menampilkan toggle tema di sebelah tombol menu hamburger.
  2. Bagian dalam menu drawer mobile (`resources/views/layouts/public.blade.php`) sebelumnya juga menyematkan `<x-theme-toggle :show-label="true" />`, sehingga saat drawer menu dibuka, kedua tombol terlihat bersamaan di layar.
  3. Pada halaman dashboard (`resources/views/dashboard.blade.php`), banner ucapan selamat datang memuat tombol dark mode kedua di samping toggle bawaan `<flux:header class="lg:hidden">`.

## Changes Applied
1. [public.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/layouts/public.blade.php): Dihapus duplikat tombol theme toggle di dalam menu drawer mobile sehingga toggle tetap bersih dan konsisten hanya berada di header utama.
2. [dashboard.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/dashboard.blade.php): Menambahkan class `hidden sm:inline-flex` pada banner toggle agar tidak menumpuk dengan toggle mobile header.

## Verification
- Diuji pada mobile viewport (390x844 dan 500x749) dengan browser subagent.
- Hanya 1 tombol dark mode yang aktif di navbar dan berfungsi normal saat di-klik.
- Seluruh 79 tes PHPUnit (`php artisan test`) lulus 100%.

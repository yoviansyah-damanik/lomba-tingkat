# Footer Navigation Link Fix

## Summary
Tautan navigasi pada footer halaman registrasi pangkalan ([register-scout-group.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/pages/auth/register-scout-group.blade.php)) telah diperbaiki.

## Changes Applied
- **Tautan Juknis**: Diubah dari `route('guide')` menjadi `route('competitions')` sehingga mengarah ke halaman resmi Katalog Mata Lomba & Petunjuk Teknis (Juknis).
- **Tautan Panduan**: Ditambahkan tautan terpisah untuk `route('guide')` sehingga pengguna tetap dapat mengakses Panduan Pendaftaran.
- Susunan nav link footer kini lengkap dan presisi:
  1. **Beranda** &rarr; `route('home')`
  2. **Juknis** &rarr; `route('competitions')`
  3. **Panduan** &rarr; `route('guide')`
  4. **Masuk Akun** &rarr; `route('login')`

## Verification
- Diuji navigasi klik pada browser subagent, tautan Juknis berhasil membuka `/competitions`.
- Seluruh 79 tes PHPUnit (`php artisan test`) lulus 100%.

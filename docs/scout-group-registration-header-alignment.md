# Scout Group Registration Header Alignment

## Summary
Header pada halaman pendaftaran pangkalan Gugus Depan ([register-scout-group.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/pages/auth/register-scout-group.blade.php)) telah diselaraskan agar identik dan konsisten dengan header halaman login.

## Changes Applied
1. **Brand Logo sebagai Link Beranda**:
   - Menghilangkan tombol teks *"Beranda"* dan pembatas vertikal.
   - Brand logo (Logo LT III + Nama Aplikasi + Subheading) di sisi kiri langsung dibungkus link `href="{{ route('home') }}"` menuju beranda.
2. **Penyederhanaan Header Kanan**:
   - Menghilangkan teks link *"Sudah punya akun? Masuk →"* dari header.
   - Sisi kanan header hanya menampilkan tombol Dark/Light mode toggle (`<x-theme-toggle />`).

## Verification
- Diuji secara visual di browser subagent pada desktop (1280x800) dan mobile (390x844).
- Seluruh 79 tes PHPUnit (`php artisan test`) lulus 100%.

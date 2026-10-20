# Livewire Navigate SPA Implementation

## Summary
- **Progress Bar Theme Color**: Diatur ke `#d97706` (Amber-600) pada `config/livewire.php` (`navigate.progress_bar_color`) agar selaras dengan palet identitas Pramuka.
- **Public & Landing Navigation**:
  - `layouts/public.blade.php`: Seluruh tautan navbar desktop, mobile menu, brand logo, dan menu profil menggunakan `wire:navigate`.
  - `welcome.blade.php`: CTA hero, pengumuman, katalog lomba, dan jadwal terpasang `wire:navigate`.
  - Halaman Livewire publik (`LeaderboardPage`, `CompetitionsCatalog`, `SchedulesCalendar`, `AnnouncementsList`, `GuidePage`): Seluruh breadcrumbs dan tombol aksi antar-halaman dilengkapi `wire:navigate`.
- **Dashboard & Contingent Portal**:
  - `sidebar.blade.php`: Seluruh menu administrator, juri, panitia, kontingen, dan tautan klasemen menggunakan `wire:navigate`.
  - `dashboard.blade.php` & `portal/*`: Navigasi kartu peran, form pendaftaran peserta, bindamping, dan review terintegrasi `wire:navigate`.
  - Pengecualian: Tautan cetak ID Card/PDF yang memiliki atribut `target="_blank"` dan formulir aksi POST (seperti logout) tetap diproses standar tanpa `wire:navigate`.

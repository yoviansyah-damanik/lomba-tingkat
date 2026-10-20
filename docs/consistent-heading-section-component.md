# Reusable Heading Section Component

## Summary
Created a reusable Blade component `<x-heading-section>` in `resources/views/components/heading-section.blade.php` to unify and standardize heading sections across all administrative, portal, and public pages.

## Component Features
- **Title & Description**: Consistent `h1` typographic hierarchy and descriptive subtitle with responsive spacing.
- **Badge Pills**: Configurable status/category badges with icon and theme colors (`amber`, `emerald`, `blue`, etc.) and optional `badge-extra` slot.
- **Breadcrumbs Slot**: Standardized breadcrumb navigation for nested flows and public views.
- **Actions Slot**: Clean alignment for toolbar buttons (`<flux:button>`) and live status indicators.
- **Hero Icon**: Optional large decorative icon for special dashboard headings.

## Pages Updated
1. `attendance-dashboard.blade.php` (Portal Presensi)
2. `attendance-recap.blade.php` (Rekapitulasi Presensi)
3. `schedules-management.blade.php` (Jadwal & Penugasan Juri)
4. `competitions-management.blade.php` (Mata Lomba & Rubrik)
5. `standings-leaderboard.blade.php` (Rekap Nilai & Klasemen)
6. `participants-directory.blade.php` (Direktori Peserta)
7. `chaperones-directory.blade.php` (Direktori Bindamping)
8. `officials-management.blade.php` (Aparatur Panitia & Juri)
9. `verification-management.blade.php` (Verifikasi Berkas)
10. `scout-group-dashboard.blade.php` (Portal Gugus Depan)
11. `patrols-management.blade.php` (Pengelolaan Regu)
12. `chaperones-management.blade.php` (Pengelolaan Bindamping)
13. `review-registration.blade.php` (Pemeriksaan Berkas)
14. `leaderboard-page.blade.php` (Klasemen Publik)
15. `schedules-calendar.blade.php` (Kalender Jadwal Publik)
16. `competitions-catalog.blade.php` (Katalog Lomba Publik)
17. `announcements-list.blade.php` (Pengumuman Publik)
18. `guide-page.blade.php` (Panduan Pendaftaran Publik)
19. `pages/leaderboard.blade.php`, `pages/guide.blade.php`, `pages/schedules/index.blade.php`, `pages/competitions/index.blade.php`, `pages/announcements/index.blade.php`, `portal/scout-group/index.blade.php`, `portal/scout-group/review.blade.php`, `portal/scout-group/profile.blade.php`, `portal/scout-group/patrols/index.blade.php`, `portal/scout-group/chaperones/index.blade.php`.

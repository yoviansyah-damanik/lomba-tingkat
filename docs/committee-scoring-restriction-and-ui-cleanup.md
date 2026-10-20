# Pembatasan Hak Penilaian Panitia & Pembersihan Label UI

## 1. Pembatasan Hak Input Penilaian Panitia (Committee)
- **Komponen**: [`StandingsLeaderboard.php`](file:///d:/WebApps/lomba-tingkat/app/Livewire/Admin/StandingsLeaderboard.php)
- **Perubahan**:
  - `canManageScores()` diperbarui sehingga pengguna dengan peran Panitia (`isCommittee()`) tanpa peran Juri sama sekali tidak memiliki hak akses penilaian pada cabang lomba manapun.
  - Aksi `openScoreModal()`, `saveScoreModal()`, `toggleScoreLock()`, dan `recalculateRanks()` diproteksi dengan guard otorisasi `if (! $this->canManageScores)`.
  - Tombol **Input Nilai Regu**, **Ubah Nilai**, **Kunci Nilai**, dan **Kalkulasi Ulang Rank** disembunyikan dari antarmuka bagi Panitia.
- **Pengujian**: Ditambahkan unit/feature tests di [`StandingsLeaderboardTest.php`](file:///d:/WebApps/lomba-tingkat/tests/Feature/StandingsLeaderboardTest.php) untuk memverifikasi penolakan akses penilaian bagi peran Panitia dan kelancaran akses bagi Dewan Juri.

## 2. Pembersihan Label "Livewire 4 SPA"
Badge/label "Livewire 4 SPA" dan "Livewire 4" telah dihilangkan dari seluruh halaman:
1. Dasbor Utama: [`dashboard.blade.php`](file:///d:/WebApps/lomba-tingkat/resources/views/livewire/dashboard.blade.php)
2. Dasbor Gugus Depan: [`scout-group-dashboard.blade.php`](file:///d:/WebApps/lomba-tingkat/resources/views/livewire/portal/scout-group-dashboard.blade.php)
3. Presensi & Scanner QR: [`attendance-dashboard.blade.php`](file:///d:/WebApps/lomba-tingkat/resources/views/livewire/portal/attendance-dashboard.blade.php)
4. Verifikasi Kontingen: [`verification-management.blade.php`](file:///d:/WebApps/lomba-tingkat/resources/views/livewire/admin/verification-management.blade.php)
5. Jadwal & Juri: [`schedules-management.blade.php`](file:///d:/WebApps/lomba-tingkat/resources/views/livewire/admin/schedules-management.blade.php)
6. Direktori Peserta: [`participants-directory.blade.php`](file:///d:/WebApps/lomba-tingkat/resources/views/livewire/admin/participants-directory.blade.php)
7. Direktori Pembina Pendamping: [`chaperones-directory.blade.php`](file:///d:/WebApps/lomba-tingkat/resources/views/livewire/admin/chaperones-directory.blade.php)
8. Aparatur & Dewan Juri: [`officials-management.blade.php`](file:///d:/WebApps/lomba-tingkat/resources/views/livewire/admin/officials-management.blade.php)
9. Mata Lomba & Rubrik Penilaian: [`competitions-management.blade.php`](file:///d:/WebApps/lomba-tingkat/resources/views/livewire/admin/competitions-management.blade.php)

# Rekap Nilai & Klasemen di Dasbor

## Ringkasan
Fitur rekapitulasi penilaian dan klasemen prestasi resmi telah dibuat khusus di dalam antarmuka dasbor (`layouts.app.sidebar`), terintegrasi dengan Livewire 4 SPA dan utility classes Tailwind CSS.

## Komponen & Fitur
1. **Model & Database**:
   - `CompetitionScore` (`competition_scores`): Menyimpan nilai akhir, rincian kriteria rubrik (`criteria_scores` JSON), predikat prestasi (`prestasi_tinggi` 🥇, `prestasi_baik` 🥈, `prestasi_cukup` 🥉, `partisipasi`), peringkat, catatan juri, dan status kunci.
2. **Service Layer**:
   - `StandingsService`: Mengkalkulasi klasemen regu menurut jumlah medali (Prestasi Tinggi terbanyak, lalu Prestasi Baik terbanyak, lalu Prestasi Cukup terbanyak; tanpa sistem poin), serta auto-ranking cabang lomba.
3. **Livewire 4 Component**:
   - `App\Livewire\Admin\StandingsLeaderboard`:
     - **Tab 1: Klasemen Kontingen (Gudep)** dengan visual podium 3 besar (Juara Tergiat I, II, III) dan tabel perolehan medali.
     - **Tab 2: Klasemen Regu** dengan filter kategori Putra/Putri dan live search.
     - **Tab 3: Rekap Nilai Mata Lomba** dengan dropdown mata lomba, tabel nilai seluruh regu, modal input nilai per kriteria rubrik juri, dan tombol kalkulasi ulang rank.
4. **Navigasi Sidebar**:
   - Menu *Rekap Nilai & Klasemen* di sidebar terhubung ke rute `/admin/standings` dan `/standings` untuk seluruh peran terautentikasi (Administrator, Panitia, Dewan Juri, Kontingen Gudep, dan Peserta).

## Pengujian
- `tests/Feature/StandingsLeaderboardTest.php`: 6 pengujian (RBAC auth, tab switching, auto-ranking, modal scoring, toggle lock).
- Total pengujian proyek: **148 tests** (143 passed, 5 skipped, 0 failed).

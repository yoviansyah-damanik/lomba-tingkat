# Scoring and Ranking System Update

## 1. Terminology Changes

### Branch Competition Ranks (Peringkat Setiap Cabang Lomba)
1. **Prestasi Tinggi** (Setara Medali Emas)
2. **Prestasi Baik** (Setara Medali Perak)
3. **Prestasi Cukup** (Setara Medali Perunggu)

### Overall General Ranks (Peringkat Umum)
1. **Regu Prestasi Tinggi** (Juara 1 Umum)
2. **Regu Prestasi Baik** (Juara 2 Umum)
3. **Regu Prestasi Cukup** (Juara 3 Umum)

---

## 2. Standings & Ranking Determination Mechanism

- **Pure Medal Hierarchy**: Standings order is strictly calculated based on the count of achievements:
  1. Count of `Prestasi Tinggi` (Gold equivalent) &darr;
  2. Count of `Prestasi Baik` (Silver equivalent) &darr;
  3. Count of `Prestasi Cukup` (Bronze equivalent) &darr;
  4. Total Achievements (`total_medals`) &darr;
  5. Alphabetical order.
- **Score Accumulation Removed from General Ranks**: Accumulated numerical scores (`total_score_sum`) and point weights (`total_points`) no longer determine the overall rank order. A team with 1 `Prestasi Tinggi` ranks higher than a team with multiple `Prestasi Baik`, regardless of numerical scores.

---

## 3. Public Interface (`/leaderboard`) Adjustments

- Removed `Akumulasi Nilai` / total score sums from all public leaderboard tables and podiums.
- Removed point calculation text (`Total Skor Poin`, `Poin`) on the public view.
- Added dedicated **Predikat Umum** column on public standing tables displaying `Regu Prestasi Tinggi`, `Regu Prestasi Baik`, and `Regu Prestasi Cukup`.
- **Contingent (Gugus Depan) Ranking Removed from Public View**: The "Pangkalan (Gugus Depan)" tab and contingent standings table are completely removed from the public page, reserving contingent-level rankings exclusively for the admin/dashboard portal (`/admin/standings`).
- Default active tab set to **Peringkat Regu Penggalang** (`patrols`).
- **Numerical Scores & Judge Notes Hidden on Public View**: The "Hasil Per Mata Lomba" table no longer displays numerical final scores (`Nilai Akhir`) or judges' internal notes (`Catatan Dewan Juri`), displaying only the verified achievement predicate (`Prestasi Tinggi`, `Prestasi Baik`, `Prestasi Cukup`). Numerical details and notes remain fully visible on the admin and judge panels.

---

## 4. Gender Separation for Overall Patrol Rankings (Putra & Putri)

- **Independent Ranking by Gender**: Boys patrols (`boys`) and Girls patrols (`girls`) are ranked completely separately.
- **Predicates with Gender Context**:
  - Boys: `Regu Prestasi Tinggi Putra`, `Regu Prestasi Baik Putra`, `Regu Prestasi Cukup Putra`.
  - Girls: `Regu Prestasi Tinggi Putri`, `Regu Prestasi Baik Putri`, `Regu Prestasi Cukup Putri`.
- **Dual Grand Podiums & Tables**:
  - The public leaderboard `/leaderboard` displays dual Grand Podiums: **PERINGKAT UMUM REGU PUTRA** and **PERINGKAT UMUM REGU PUTRI**.
  - On the main standings view, two separate tables are presented: **Klasemen Peringkat Umum Regu Penggalang Putra** and **Klasemen Peringkat Umum Regu Penggalang Putri**.
  - Gender filtering works seamlessly for `all`, `boys`, and `girls`.

---

## 5. Identity Masking Configuration (Blind Mode) & Admin Peek

- **Master Configuration (`hide_patrol_identities`)**:
  - Configurable directly from **Konfigurasi Sistem & Master Kegiatan** (`/admin/settings/system`).
  - Stored in the `events` table as `hide_patrol_identities` (boolean).
- **Public & Committee/Judge Masking**:
  - When enabled, all patrol names are masked to blind drawing numbers (e.g., `Regu #01`) and school/pangkalan names to `Pangkalan Dirahasiakan` with group numbers `***`.
  - Applies to the public leaderboard (`/leaderboard`), judges, and committee members.
- **Admin Peek Privilege ("Hanya Admin yang Bisa Mengintip")**:
  - An interactive toggle button (`Intip Identitas (Admin)`) is rendered exclusively for authenticated Administrators on both public leaderboard and dashboard standings.
  - Non-admin visitors (guest, committee, judges) can never unmask or see real identities when Blind Mode is active.

---

## 6. Modified Files

- [Event.php](file:///d:/WebApps/lomba-tingkat/app/Models/Event.php): Added `hide_patrol_identities` attribute and `shouldHideIdentities()` helper.
- [Patrol.php](file:///d:/WebApps/lomba-tingkat/app/Models/Patrol.php): Added `displayName()` and `displaySchool()` helpers.
- [SystemSettingsManagement.php](file:///d:/WebApps/lomba-tingkat/app/Livewire/Admin/SystemSettingsManagement.php) & [system-settings-management.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/livewire/admin/system-settings-management.blade.php): Added toggle card for `hide_patrol_identities`.
- [StandingsService.php](file:///d:/WebApps/lomba-tingkat/app/Services/StandingsService.php): Added masking and unmask parameter handling across `getPatrolStandings()`, `getContingentStandings()`, `getCompetitionRecap()`, and `getStandingsSummary()`.
- [LeaderboardPage.php](file:///d:/WebApps/lomba-tingkat/app/Livewire/Public/LeaderboardPage.php) & [leaderboard-page.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/livewire/public/leaderboard-page.blade.php): Added admin peek action, privacy alert banner, and identity masking.
- [StandingsLeaderboard.php](file:///d:/WebApps/lomba-tingkat/app/Livewire/Admin/StandingsLeaderboard.php) & [standings-leaderboard.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/livewire/admin/standings-leaderboard.blade.php): Added identity masking for committee/judges and peek toggle for admin.
- [PublicLeaderboardTest.php](file:///d:/WebApps/lomba-tingkat/tests/Feature/PublicLeaderboardTest.php), [StandingsLeaderboardTest.php](file:///d:/WebApps/lomba-tingkat/tests/Feature/StandingsLeaderboardTest.php), & [AdminSystemSettingsTest.php](file:///d:/WebApps/lomba-tingkat/tests/Feature/AdminSystemSettingsTest.php): Added test coverage for identity masking and admin peek.

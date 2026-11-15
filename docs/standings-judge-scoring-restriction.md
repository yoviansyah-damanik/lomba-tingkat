# Standings Judge Scoring Restriction

## Overview
Restricted judges from inputting, editing, or managing scores on the standings leaderboard page (`/admin/standings` / [`StandingsLeaderboard`](file:///d:/WebApps/lomba-tingkat/app/Livewire/Admin/StandingsLeaderboard.php)). All judging input and revisions must be performed officially through the Judge Cockpit (`/portal/judge` & `/portal/judge/scoring/{id}`).

## Key Changes
1. **`canInputScores` & `canManageScores` on `StandingsLeaderboard`**:
   - `canInputScores` is strictly `false` on the standings page, preventing manual entry or edits of `CompetitionScore`.
   - `canManageScores` is restricted strictly to Administrator (`$user->isAdmin()`), removing rank recalculation and lock toggle controls from judges.
2. **UI & Action Guards**:
   - "Input Nilai Regu", "Ubah Nilai" (pencil button), and "Mulai Input Nilai" are hidden.
   - Calling `openScoreModal` or `saveScoreModal` is rejected with access error toast directing judges to use the Judge Cockpit.
   - Status in the competition recap table renders as a clean read-only badge (`Final` / `Draft`) for judges.
3. **Verification**:
   - Updated `test_judge_cannot_input_or_edit_scores_in_standings_leaderboard` in `tests/Feature/StandingsLeaderboardTest.php`.

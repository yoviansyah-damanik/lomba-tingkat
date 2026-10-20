# Assigned Judge Exclusive Scoring Policy

## Overview
Enforces the strict rule that assessment scoring (`saveDraft`, `finalizeScore`, and direct score modals) can **ONLY** be executed by the designated Judge officially assigned to the competition session (`judge_assignments`). Under no circumstances, including Administrator, may any other user input, draft, or finalize scores.

## Key Changes

1. **[AssessmentScoringService](file:///d:/WebApps/lomba-tingkat/app/Services/AssessmentScoringService.php)**:
   - `saveDraft(array $data, User $judge)`: Rejects with `InvalidArgumentException` if user is Administrator or not an assigned Judge for the schedule.
   - `finalizeScore(string $assessmentId, User $judge)`: Rejects if user is Administrator, does not own the assessment, or is not an assigned Judge for the schedule.
   - Preserves `unlockScore()` exclusively for Administrator to perform audited score unlocks.

2. **[JudgeScoring](file:///d:/WebApps/lomba-tingkat/app/Livewire/Portal/JudgeScoring.php)**:
   - Introduced `isAssignedJudge` computed property verifying role and schedule assignment while strictly returning `false` for Administrator.
   - Guarded `saveDraft()`, `openFinalizeModal()`, and `finalize()` against non-assigned users and Administrator.

3. **[Judge Scoring View](file:///d:/WebApps/lomba-tingkat/resources/views/livewire/portal/judge-scoring.blade.php)**:
   - Added `MODE TINJAU AUDIT ADMINISTRATOR (READ-ONLY)` notice banner.
   - Disabled all scoring inputs (`@disabled(! $canScore)`) for read-only / audit inspection.
   - Removed scoring action buttons for Administrator; replaced with status badge and unlock button when scores are finalized.

4. **[StandingsLeaderboard](file:///d:/WebApps/lomba-tingkat/app/Livewire/Admin/StandingsLeaderboard.php)**:
   - Added `canInputScores` computed property restricting direct score input to judges.
   - Blocked `openScoreModal` and `saveScoreModal` for Administrator while preserving `recalculateRanks` and `toggleScoreLock` under `canManageScores`.
   - Converted all trigger buttons to `<flux:button>` with `icon="..."` attribute.

5. **Test Coverage**:
   - `test_admin_strictly_cannot_give_or_finalize_scores()` in [JudgeScoringTest](file:///d:/WebApps/lomba-tingkat/tests/Feature/JudgeScoringTest.php).
   - `test_unassigned_judge_cannot_give_scores()` in [JudgeScoringTest](file:///d:/WebApps/lomba-tingkat/tests/Feature/JudgeScoringTest.php).
   - `test_admin_cannot_input_score_in_standings_leaderboard()` in [StandingsLeaderboardTest](file:///d:/WebApps/lomba-tingkat/tests/Feature/StandingsLeaderboardTest.php).

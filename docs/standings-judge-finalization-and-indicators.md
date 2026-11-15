# Standings Judge Finalization and Judging Indicators

## Overview
Added the capability for administrators to finalize (draft to finalized/locked) or revert to draft per judge directly from `/admin/standings`. In addition, implemented visual indicators across all judging and schedule pages when scores have been entered for a competition or session, but one or more judges have not finalized their assessments.

## Key Changes
1. **AssessmentScoringService**:
   - `finalizeScoresByJudge(string|int $judgeId, User $admin, ?string $competitionId = null, ?string $scheduleId = null)`:
     - Transitions assessments from `draft` to `finalized` in a database transaction.
     - Sets `is_locked = true`, updates weighted scores, logs `score_finalized` in `AuditLog`.
     - Recalculates `CompetitionScore` via `aggregatePatrolScore()`.
2. **Competition & CompetitionSchedule Models**:
   - Added `hasUnfinalizedJudges()` and `judgeScoringStatus()`.
   - Returns boolean status and counts (`has_scores`, `has_unfinalized`, `draft_count`, `finalized_count`).
3. **StandingsLeaderboard Component (`/admin/standings`)**:
   - Added modal action `isJudgeActionModalOpen` supporting both `finalize` and `draft` per judge or for all judges in the selected competition.
   - Competition dropdown displays `• ⚠️ Belum Final` when draft assessments exist.
   - Alert banner and "Status Penilaian Dewan Juri Cabang Ini" list with quick action buttons.
4. **Judging Indicator Across Pages**:
   - **Judge Cockpit (`/portal/judge`)**: Schedule cards display amber warning badge `Sebagian Juri Belum Final` or emerald `Final Sempurna`.
   - **Judge Scoring (`/portal/judge/scoring/{id}`)**: Banner alerting if other judges or the current session have pending drafts.
   - **Assessments Audit (`/admin/assessments`)**: Competition filter dropdown and alert banner highlight unfinalized submissions.
   - **Schedules Management (`/admin/schedules`)**: Schedule cards display `Juri Belum Final` badge for active sessions with draft scores.

## Tests
- `tests/Feature/StandingsLeaderboardTest.php`: Verified admin finalize/draft per judge and indicator visibility toggling.
- `tests/Feature/AssessmentsAuditTest.php`: Verified judge filtering, unlocks, and audit logging.
- `tests/Feature/JudgeScoringTest.php`: Verified scoring flows and permissions.

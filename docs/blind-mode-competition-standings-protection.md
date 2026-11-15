# Blind Mode Competition Standings Protection

## Overview
Ensured strict identity and ranking masking during blind mode (`hide_patrol_identities = true`) on `/standings` (`StandingsLeaderboard`). Contestant accounts (`scout_group`, `participant`, `chaperone`) cannot access individual competition ranking recaps, and all patrol/school names in the competition recap table are properly masked for non-unmasked viewers (such as judges).

## Key Changes
1. **Access Control (`canAccessCompetitionTab`)**:
   - In `StandingsLeaderboard`, added computed property `canAccessCompetitionTab`:
     - Administrators and Judges retain access to the competition recap tab (`competitions`).
     - Contestant roles (`scout_group`, `participant`, `chaperone`) are restricted from viewing competition rankings when blind mode is active (`isEventMasked && !canUnmask`).
     - Attempts to switch to `activeTab = 'competitions'` automatically fall back to `patrols`.
     - The `Rekap Nilai Mata Lomba` tab navigation button is hidden for restricted users during blind mode.
2. **Identity Masking in Competition Recap Table**:
   - Replaced raw `$score->patrol?->patrol_name` and `$score->patrol?->scoutGroup?->school_name` with `$score->patrol?->displayName($canUnmask)` and `$score->patrol?->displaySchool($canUnmask)`.
   - Masked Gudep numbers to `(Gudep ***)` when blind mode is active and unmask is false.
   - Masked judge name to generic `Dewan Juri` and suppressed judge notes for non-admin viewers during blind mode.
3. **Modal & Portal Consistency**:
   - In `openPatrolDetail` modal and `team-assessment.blade.php`, ranks are rendered as `#•••` during blind mode instead of revealing exact ranks.

## Verification
- Added `test_scout_group_cannot_see_competition_rankings_and_identities_are_masked_in_blind_mode` in `tests/Feature/StandingsLeaderboardTest.php`.
- Verified that guest, scout group, judge, and admin roles behave properly across blind and unmasked states.

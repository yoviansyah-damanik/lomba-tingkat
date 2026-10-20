# Competition Schedule Gender Category & Attendance Enforcement

## Overview
Added gender category specification (`gender_category`: `boys`, `girls`, `all`) to competition schedules (`competition_schedules`) and enforced strict gender validation during attendance check-in (both QR scan and manual input).

## Changes

### 1. Database Schema
- Migration: `database/migrations/2026_10_19_023521_add_gender_category_to_competition_schedules_table.php`
- Added column: `gender_category` (`enum: ['boys', 'girls', 'all']`, default `'all'`).

### 2. Model: `CompetitionSchedule`
- Added helper methods:
  - `effectiveGenderCategory()`: Returns explicit schedule category or falls back to `competition->category`.
  - `isBoys()`, `isGirls()`, `isAllGender()`.
  - `genderLabel()`: Returns `'Putra'`, `'Putri'`, or `'Putra & Putri'`.
  - `genderBadgeClasses()`: Tailwind styling classes for UI badges.

### 3. Service: `AttendanceService`
- In `recordScan()` & `manualCheckIn()`:
  - Validates `effectiveGenderCategory()` against `patrol->isBoys()` and `patrol->isGirls()`.
  - Rejects scan/check-in if category does not match with response code `CATEGORY_MISMATCH` and informative Indonesian message.
- In `searchCandidates()`:
  - Flags gender mismatched participants/patrols with `eligible = false` and warning note.

### 4. Controller & Livewire
- `AdminScheduleController`: Validates and saves `gender_category` in `store()` and `update()`, with gender filter support in `index()`.
- `SchedulesManagement`: Added `gender_category` in modal form, gender filter toolbar, and auto-suggests gender when selecting a gender-specific competition.
- `SchedulesCalendar`: Added gender category filter and badge indicators on public schedule cards.
- `AttendanceScanner`: Displays gender category badge in scanner header and dashboard cards.

### 5. Automated Tests
- `AttendanceScannerTest`: Added tests for boys rejected from girls schedules, girls rejected from boys schedules, and valid check-ins.
- `AdminSchedulesLivewireTest`: Added tests for creating schedules with gender category and filtering schedules by gender.

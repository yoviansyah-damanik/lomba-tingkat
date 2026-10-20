# UUID Architecture Migration Summary

## 1. Overview
All system entities have been migrated from auto-incrementing integer IDs to Universally Unique Identifiers (UUIDv7 / UUIDv4 via `Illuminate\Database\Eloquent\Concerns\HasUuids`).

---

## 2. Updated Entities & Models

| Table / Entity | Primary Key | Foreign Key References | Model Trait |
| :--- | :--- | :--- | :--- |
| `users` | `uuid('id')->primary()` | Referenced by roles, scout_groups, announcements, etc. | `HasUuids` |
| `roles` | `uuid('id')->primary()` | Referenced in `role_user` | `HasUuids` |
| `role_user` (pivot) | `uuid('id')->primary()` | `foreignUuid('user_id')`, `foreignUuid('role_id')` | `RoleUser` Pivot with `HasUuids` |
| `events` | `uuid('id')->primary()` | Referenced in `competitions` | `HasUuids` |
| `announcements` | `uuid('id')->primary()` | `foreignUuid('author_id')` | `HasUuids` |
| `competitions` | `uuid('id')->primary()` | `foreignUuid('event_id')` | `HasUuids` |
| `competition_schedules` | `uuid('id')->primary()` | `foreignUuid('competition_id')` | `HasUuids` |
| `scout_groups` | `uuid('id')->primary()` | `foreignUuid('user_id')`, `foreignUuid('verified_by')` | `HasUuids` |
| `patrols` | `uuid('id')->primary()` | `foreignUuid('scout_group_id')` | `HasUuids` |
| `participants` | `uuid('id')->primary()` | `foreignUuid('patrol_id')` | `HasUuids` |
| `chaperones` | `uuid('id')->primary()` | `foreignUuid('scout_group_id')`, `foreignUuid('user_id')` | `HasUuids` |
| `assessment_criteria` | `uuid('id')->primary()` | `foreignUuid('competition_id')` | `HasUuids` |
| `judge_assignments` | `uuid('id')->primary()` | `foreignUuid('competition_schedule_id')`, `foreignUuid('user_id')` | `HasUuids` |
| `attendance_sessions` | `uuid('id')->primary()` | `foreignUuid('competition_schedule_id')` | `HasUuids` |
| `sessions` | `string('id')->primary()` | `foreignUuid('user_id')` | Framework session table |
| `passkeys` | `uuid('id')->primary()` | `foreignUuid('user_id')` | Passkey credential table |

---

## 2.1 Generalized Identifier Format (`ID-XXXXXXXX`)
Beyond internal UUID primary keys, each accredited individual possesses a human-readable, generalized accreditation ID format (`ID-XXXXXXXX`):
- **Peserta (Participants)**: `id_number` field on `participants` (e.g. `ID-11KIIFPM`).
- **Pembina Pendamping (Chaperones)**: `id_number` field on `chaperones` (e.g. `ID-IFAJAZP1`).
- **Panitia / Dewan Juri (Staff / Officials)**: `id_number` field on `users` (e.g. `ID-FPLWM93K`).
- Automatically generated on model creation if blank.
- Displayed prominently across:
  - Digital accreditation cards (Individual & A4 Batch prints).
  - FORM : 01 official roster and individual biodata registration printouts.
  - QR Code authentication and public verification view.
  - Admin officials and participant directory tables.

---

## 3. Controller & Route Adaptations
- Controllers updated from `int $id` to `string $id` parameter types:
  - `DigitalCardController`
  - `ScoutGroupPortalController`
  - `AdminVerificationController`
  - `AdminOfficialController`
- Validation rules updated in `ProfileValidationRules` (`int|string|null $userId`).
- Route model binding automatically binds model UUID strings to route parameters.

---

## 4. Verification
- Fresh migrations and seeders executed cleanly: `php artisan migrate:fresh --seed`.
- Automated test suite: **91 tests executed, 86 passed, 5 skipped, 0 failed**.
- Browser visual verification: Confirmed routes like `/admin/competitions/{uuid}/edit` and `/admin/schedules/{uuid}/edit` load and render with 0 errors.

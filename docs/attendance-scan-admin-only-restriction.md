# Attendance Scanning Role Restriction

## Overview
Restricted attendance scanning capabilities (opening scanner camera, recording scans, and manual check-ins) exclusively to the `admin` role.

## Changes
1. **Model Authorization** (`app/Models/AttendanceSession.php`):
   - `canBeAccessedBy(?User $user)`: Updated to allow only `$user && $user->isAdmin()`.
2. **Component & Controller Guards**:
   - `app/Livewire/Portal/AttendanceScanner.php`: `mount()` aborts `403` for non-admin users.
   - `app/Livewire/Portal/AttendanceDashboard.php`: `mount()` aborts `403` for non-admin users.
   - `app/Http/Controllers/AttendanceScannerController.php`: Returns `403` JSON responses when non-admins attempt `scan`, `manual`, `search`, `logs`, `update`, or `destroy`.
3. **Routing** (`routes/web.php`):
   - Grouped `attendance.index`, `attendance.scanner`, and scanning mutation endpoints under `middleware(['auth', 'role:admin'])`.
   - Kept `attendance.recap`, preview, and PDF export under `middleware(['auth', 'role:admin|committee|judge'])`.
4. **Navigation & UI** (`app/Support/Navigation.php`, `resources/views/livewire/dashboard.blade.php`, `resources/views/livewire/portal/attendance-recap.blade.php`):
   - Sidebar item `Portal Presensi` restricted to `roles: ['admin']`.
   - Dashboard stat cards for Committee and Judge updated to direct them to `attendance.recap` instead of the scanner dashboard.
   - Return button to Portal Presensi on `attendance-recap` conditioned with `@if (auth()->user()?->isAdmin())`.
5. **Automated Testing** (`tests/Feature/AttendanceScannerTest.php`, `tests/Feature/AttendanceDashboardLivewireTest.php`):
   - Verified non-admin roles (committee, judge, dual-role) receive `403 Forbidden` on scanner endpoints.
   - Verified admin retains full access to scanning general and competition sessions.


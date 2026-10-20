# Visual Attendance Logs Modal & Terminology Unification

## Summary
Optimized the "Log" button in the Attendance Portal (`/portal/attendance`) to display interactive, visually rich attendance logs in a Livewire modal rather than navigating to raw JSON output, while preserving JSON API endpoint for Alpine.js scanner polling. Unified all "Panitia Pos" labels to "Panitia".

## Key Changes
1. **Terminology Standardization**:
   - Updated `AttendanceSession::sessionTypeLabel()`: changed `'committee' => 'Panitia Pos'` to `'committee' => 'Panitia'`.
   - Updated UI text across attendance and officials management views to "Panitia".

2. **Livewire Component (`AttendanceDashboard.php`)**:
   - Added modal state properties (`$showLogsModal`, `$selectedLogsSessionId`, `$logsSearch`, `$logsStatusFilter`).
   - Added actions: `openLogsModal(id)`, `updateAttendanceStatus(id, status)`, `deleteAttendanceRecord(id)`.
   - Added computed properties: `selectedLogsSession`, `sessionLogs` with real-time searching and filtering.

3. **Blade UI (`attendance-dashboard.blade.php`)**:
   - Converted "Log" button from raw JSON route link to `wire:click="openLogsModal('{{ $session->id }}')"`.
   - Added `<flux:modal wire:model="showLogsModal">` featuring:
     - Header with session details, badges, and quick link to QR Scanner.
     - Metric cards strip (Total, Hadir Tepat Waktu, Terlambat, Izin).
     - Live search bar and status filter buttons (`all`, `present`, `late`, `permission`).
     - Card items with participant/user avatars, ID tokens, Gudep/Regu, scan time (`diffForHumans()`), scanner user, notes, and quick status modifier buttons.
     - Empty state illustration when no records match filter.
     - Admin deletion option.

4. **Testing (`AttendanceDashboardLivewireTest.php`)**:
   - Added automated tests verifying visual logs modal opening, search/rendering, and status update actions.

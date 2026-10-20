# Attendance Session Edit & Schedule Handling

## Overview
Added the ability for administrators to edit Attendance Sessions (`AttendanceSession`) and handle/correct linked competition schedule mistakes directly from the Attendance Dashboard.

## Key Features

### 1. Attendance Session Edit Modal
- Location: `app/Livewire/Portal/AttendanceDashboard.php` & `resources/views/livewire/portal/attendance-dashboard.blade.php`.
- Trigger: An **Edit** button (`pencil-square` icon) on each session card, visible exclusively to administrators.
- Editable Fields:
  - **Judul Sesi**: Rename the session title.
  - **Sasaran Entitas**: Target recipient selection (Peserta, Pembina Pendamping, Panitia, Dewan Juri).
  - **Kaitan Jadwal Lomba**: Change or re-link the session to a different `CompetitionSchedule` or convert to general attendance.
  - **Waktu Buka / Tutup Presensi**: Datetime inputs for manual schedule override.
  - **Status Aktivasi**: Instant toggle between active scan and closed/draft status.

### 2. Schedule Correction ("Koreksi Kesalahan Jadwal")
When an attendance session is linked to a competition schedule, administrators can edit the schedule's details directly inside the modal:
- **Nama Sesi Jadwal**: Modify `session_name`.
- **Lokasi Arena / Pos**: Update `arena_location`.
- **Waktu Mulai & Selesai**: Adjust `start_time` and `end_time` (automatically updates the scanner open/close windows).
- **Kategori Peserta / Regu**: Change `gender_category` (`boys`, `girls`, `all`) to fix category registration errors.
- **Status Sesi Jadwal**: Change status (`scheduled`, `in_progress`, `completed`, `rescheduled`, `cancelled`).

### 3. Session Deletion
- Administrators can delete duplicate or erroneous attendance sessions with a safety confirmation prompt.

## Automated Testing
- `tests/Feature/AttendanceDashboardLivewireTest.php`:
  - `test_admin_can_edit_attendance_session_and_schedule`: Verifies editing session title and correcting schedule times, arena, and gender category.
  - `test_admin_can_delete_attendance_session`: Verifies session deletion.
  - `test_non_admin_cannot_edit_attendance_session`: Verifies 403 Forbidden for non-admins.

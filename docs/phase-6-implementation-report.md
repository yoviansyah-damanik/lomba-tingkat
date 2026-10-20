# Phase 6 Implementation Report: Camera QR Code Scanner Engine & Multi-Entity Attendance Processing

## 1. Overview
Phase 6 implements the real-time camera-based QR code scanning cockpit, multi-entity attendance validation engine, anti-duplicate scanning controls, manual fallback entry, and real-time pos attendance stream for LT III Kwarcab Padangsidimpuan.

---

## 2. Deliverables & Components

### 2.1 Database & Migrations
- **Table `attendances`**: Multi-entity check-in records supporting participants, chaperones, and officials/judges (`id`, `attendance_session_id`, `entity_type`, `participant_id`, `chaperone_id`, `user_id`, `scanned_by_user_id`, `scanned_at`, `status`, `notes`, `device_info`).
- **Migration**: `2026_10_18_160000_create_attendances_table.php`.

### 2.2 Eloquent Models & Service Layer
- [Attendance](file:///d:/WebApps/lomba-tingkat/app/Models/Attendance.php): Relationships to `AttendanceSession`, `Participant`, `Chaperone`, `User`, and `scannedBy`. Accessors for entity name, role label, contingent/group, ID number (`ID-XXXXXXXX`), and photo URL.
- [AttendanceSession](file:///d:/WebApps/lomba-tingkat/app/Models/AttendanceSession.php): Added `attendances(): HasMany` relationship and `active()` scope.
- [AttendanceService](file:///d:/WebApps/lomba-tingkat/app/Services/AttendanceService.php):
  - Token resolution via `QrCodeService`.
  - Session active verification & duplicate scan rejection (HTTP 409).
  - Manual check-in and candidate fuzzy search with attendance status flags.

### 2.3 Controllers & Routes
- [AttendanceScannerController](file:///d:/WebApps/lomba-tingkat/app/Http/Controllers/AttendanceScannerController.php):
  - `index`: Overview dashboard with session status filters, search, and live check-in counters.
  - `scanner`: Full-screen camera scanner cockpit for pos field operators.
  - `scan`: AJAX endpoint for instant QR token processing with sound/haptic feedback payload.
  - `manual`: AJAX endpoint for cardless manual check-in.
  - `search`: Instant candidate search with debounce.
  - `logs`: Real-time attendance feed polling endpoint.
  - `destroy`: Cancels an accidental check-in record.
- **Routes**: Grouped under `/portal/attendance/*` protected by `auth` and `role:admin|committee`.

### 2.4 User Interface & Views
- [portal.attendance.index](file:///d:/WebApps/lomba-tingkat/resources/views/portal/attendance/index.blade.php): Grid catalog of attendance sessions with live pulse indicators, arena badges, and direct scanner launcher.
- [portal.attendance.scanner](file:///d:/WebApps/lomba-tingkat/resources/views/portal/attendance/scanner.blade.php):
  - WebRTC video stream with high-tech target reticle and sweeping laser animation.
  - Camera switching (environment/user) and synthetic audio feedback via Web Audio API.
  - Attendee flash card showing photo, name, contingent, and scanned timestamp.
  - Manual check-in drawer modal.
  - Live attendance log feed.

### 2.5 Tri-Format Competition Architecture & Attendance Rules
Format kepesertaan lomba terbagi menjadi 3 jenis:
1. **Regu Utuh (`patrol` / `full_patrol`)**:
   - Seluruh anggota regu ikut serta (contoh: PBB 8 orang).
   - Presensi di pos registrasi **wajib dipresensi oleh PINRU atau WAPINRU** (`isLeader() || isAssistantLeader()`).
   - Anggota biasa ditolak (HTTP 422 `LEADER_REQUIRED`).
   - Dibatasi 1 presensi per regu (HTTP 409 `ALREADY_SCANNED`).
2. **Kelompok (`group`)**:
   - Terdiri dari beberapa orang utusan regu (misal: 2 - 4 orang, diset via `min_participants` & `max_participants`).
   - **Seluruh peserta di dalam kelompok wajib presensi secara individual** menggunakan kartu masing-masing.
   - Pengecekan perorangan mencegah scan ganda per peserta (HTTP 409 `ALREADY_SCANNED`).
   - Dibatasi kuota maksimal per regu (HTTP 422 `GROUP_QUOTA_EXCEEDED` jika kuota anggota kelompok regu telah terpenuhi).
3. **Individu (`individual`)**:
   - Format 1 orang peserta/perwakilan per regu.
   - Presensi dapat dilakukan oleh salah satu peserta perwakilan regu dan dicatat oleh sistem (1 presensi per regu).
   - Regu yang sama ditolak jika memindai peserta kedua kalinya (HTTP 409 `ALREADY_SCANNED`).

---

## 3. Verification & Testing
- **Automated Feature Tests**:
  - `tests/Feature/AttendanceScannerTest.php` (16 tests covering RBAC authorization, QR token resolution, duplicate prevention, session inactive check, manual check-in, search candidates, auto-creation on schedule creation, PINRU/WAPINRU enforcement, individual representative rule, and group multi-member attendance with quota check).
  - Full suite: **108 tests executed, 103 passed, 5 skipped, 0 failed**.
- **Frontend Asset Compilation**:
  - `npm run build` completed cleanly.

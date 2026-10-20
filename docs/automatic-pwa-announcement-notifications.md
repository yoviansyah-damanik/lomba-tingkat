# Automatic PWA Announcement Notifications

## Summary
Enabled automatic Progressive Web App (PWA) Web Push notifications whenever a new announcement is created and published in the system.

## Changes
- [AnnouncementsManagement.php](file:///d:/WebApps/lomba-tingkat/app/Livewire/Admin/AnnouncementsManagement.php):
  - Set `$broadcastPush` property to default to `true` (both on component mount and when opening the create modal via `openCreateModal()`).
  - Automatically dispatches `WebPushService::broadcastNotification()` with announcement title, summary/content excerpt, URL slug, and brand icons when a new announcement is published.
  - Updated flash and toast feedback to confirm both announcement publication and PWA push dispatch.
- [announcements-management.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/livewire/admin/announcements-management.blade.php):
  - Updated create modal label to **Kirim Notifikasi PWA Otomatis** with clear explainer text and pre-checked state.
- [AdminAnnouncementsManagementTest.php](file:///d:/WebApps/lomba-tingkat/tests/Feature/AdminAnnouncementsManagementTest.php):
  - Added assertion verifying `push_broadcast_sent` audit log is automatically recorded upon announcement creation.
- Test suite: All 251 tests pass.

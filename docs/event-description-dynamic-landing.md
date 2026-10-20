# Event Description Dynamic Landing

## Summary
Updated the landing page hero section to display the dynamic "Deskripsi Singkat / Sambutan" from the event configuration (`Event::first()->description`) managed in the system settings, with graceful fallback.

## Changes
- [welcome.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/welcome.blade.php): Replaced the hardcoded paragraph with `{!! nl2br(e($event?->description ?: '...')) !!}` to dynamically display the greeting/brief description configured by the administrator in **Konfigurasi Master Kegiatan & Pengaturan Sistem**.
- [PublicInformationTest.php](file:///d:/WebApps/lomba-tingkat/tests/Feature/PublicInformationTest.php): Added test assertion verifying configured event description renders on the home landing page.
- Test suite: All 251 tests pass.

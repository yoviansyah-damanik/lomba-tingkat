# Indonesian UI Localization

## Summary
Audited application interface views, components, and templates to replace remaining English text with Indonesian, ensuring a unified and consistent user experience across the system.

## Changes
1. **Localization Dictionaries (`lang/id.json` & `lang/id/*.php`)**:
   - Added comprehensive JSON translations for standard starter kit strings (Settings, Profile, Security, Passkeys, 2FA, Dashboard, Appearance, Search, Pagination).
   - Added complete Indonesian translation files for `auth.php`, `pagination.php`, `passwords.php`, and `validation.php`.

2. **UI & Templates**:
   - Standardized action buttons (`Filter` -> `Saring`, `Reset` -> `Atur Ulang`, `Edit` -> `Ubah`) using `<flux:button>`.
   - Translated status badges and select dropdown options (e.g. `Rescheduled` -> `Ditunda`, `In Progress` -> `Sedang Berlangsung`).
   - Cleaned footer text and installation CTA to `Pasang Aplikasi`.

3. **Test Suite Verification**:
   - Updated `tests/Feature/Settings/SecurityTest.php` assertions to use Laravel's `__()` localization helper.
   - All 251 tests passing.

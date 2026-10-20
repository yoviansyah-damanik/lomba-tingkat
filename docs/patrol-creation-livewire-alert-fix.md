# Patrol Creation Livewire & Alert Component Fix

## Summary
Fixed the issue where scout groups could not add a female patrol (Penggalang Putri) after already creating a male patrol (Penggalang Putra) in the contingent portal.

## Cause
`$gender_category` in [PatrolsManagement.php](file:///d:/WebApps/lomba-tingkat/app/Livewire/Portal/PatrolsManagement.php) initialized to `'boys'`. When the boys patrol already existed, the Blade select rendered only the `'girls'` option. Since the browser displayed the single available option without firing a DOM `change` event, Livewire retained `'boys'`, triggering the error *"Pangkalan Anda sudah memiliki regu untuk kategori ini"* upon submission.

## Changes
1. **[PatrolsManagement.php](file:///d:/WebApps/lomba-tingkat/app/Livewire/Portal/PatrolsManagement.php)**:
   - Added `mount()` and `syncAvailableGenderCategory()` to initialize `$gender_category` to the remaining available quota category.
   - Added auto-correction in `storePatrol()` ensuring that if one category already exists, the input is aligned with the available category.
   - Reset computed scout group relations and resynchronized category after creation.
   - Emitted flash messages via `session()->flash('success', ...)` for `<x-alert />` alongside `Flux::toast`.
2. **[patrols-management.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/livewire/portal/patrols-management.blade.php)**:
   - Switched form to pure Livewire (`wire:submit="storePatrol"`) and bound select via `wire:model.live="gender_category"`.
   - Converted all submit/action buttons to `<flux:button>` with the `icon="..."` attribute.
   - Retained `<x-alert />` for unified feedback rendering.
3. **[ContingentRegistrationTest.php](file:///d:/WebApps/lomba-tingkat/tests/Feature/ContingentRegistrationTest.php)**:
   - Added `test_scout_group_can_create_boys_and_then_girls_patrol_via_livewire()`.
   - All 252 tests pass.

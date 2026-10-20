# Patrol Deletion Feature

## Summary
Added a feature allowing scout groups to delete a patrol (regu) along with its associated participants and files directly from the contingent portal.

## Changes
1. **[PatrolsManagement.php](file:///d:/WebApps/lomba-tingkat/app/Livewire/Portal/PatrolsManagement.php)**:
   - Added `deletePatrol(string $id)` action.
   - Cleans up participant photo files from storage disk before deleting the patrol.
   - Deletes the patrol, unsets cached relationships, and re-synchronizes the next available gender category (`syncAvailableGenderCategory()`).
   - Flashes success message for `<x-alert />` and dispatches `Flux::toast`.
2. **[patrols-management.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/livewire/portal/patrols-management.blade.php)**:
   - Added `<flux:button variant="danger" size="sm" icon="trash">Hapus Regu</flux:button>` with `wire:confirm` dialog to each editable patrol card header.
3. **[ScoutGroupPortalController.php](file:///d:/WebApps/lomba-tingkat/app/Http/Controllers/ScoutGroupPortalController.php) & [routes/web.php](file:///d:/WebApps/lomba-tingkat/routes/web.php)**:
   - Added `patrolDestroy` method and `DELETE /portal/scout-group/patrols/{patrolId}` route named `scout-group.patrols.destroy`.
4. **[ContingentRegistrationTest.php](file:///d:/WebApps/lomba-tingkat/tests/Feature/ContingentRegistrationTest.php)**:
   - Added `test_scout_group_can_delete_patrol()` testing both Livewire and HTTP routes.
   - All 253 tests pass.

# Contingent Verification Revocation & NPP Reset

## Overview
When a verified contingent (ScoutGroup / Gugus Depan) has its verification revoked (cancelled), all assigned Patrol Registration Numbers (NPP) for its patrols are now automatically revoked and reset to `null`.

## Changes
1. **Contingent Verification Service** (`app/Services/ContingentVerificationService.php`):
   - In `cancelVerification()`, counts and sets `npp` to `null` for all patrols under the scout group (`$scoutGroup->patrols()->update(['npp' => null])`).
   - In `cascadeStatus()`, whenever a status other than `Verified` is applied, `patrols.npp` is set to `null`.
   - Audit log captures `npp_revoked` count alongside status rollback.
2. **ScoutGroup Model Lifecycle Event** (`app/Models/ScoutGroup.php`):
   - In `booted()` under `static::updated()`, if `verification_status` changes away from `Verified`, all related patrols' `npp` values are cleared to `null` as a model-level guarantee.
3. **Livewire Component & UI** (`app/Livewire/Admin/ScoutGroupVerificationActions.php`, `resources/views/livewire/admin/scout-group-verification-actions.blade.php`):
   - Toast feedback explicitly informs the operator of the number of revoked NPPs.
   - Confirmation modal warning text updated to indicate NPP revocation.
4. **Automated Testing** (`tests/Feature/ContingentVerificationCancelNppTest.php`):
   - Verified that service-level `cancelVerification()`, Livewire modal action, and direct model status updates cleanly reset `patrols.npp` to `null`.


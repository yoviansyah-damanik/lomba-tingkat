# Reusable Alert Component Implementation

## Overview
`<x-alert>` is a unified, reactive notification banner component with auto-detection for session flash messages (`success`, `error`, `warning`, `status`) and form validation errors (`$errors->any()`). It supports custom explicit declarations, title/message slots, multi-item error lists, and interactive dismiss functionality with smooth Alpine.js transitions and vanilla DOM fallbacks.

## Component Specifications

- **Component Path**: `resources/views/components/alert.blade.php`
- **Supported Variants**:
  - `success`: Emerald theme with check-circle icon
  - `danger` / `error`: Rose theme with x-circle icon
  - `warning`: Amber theme with exclamation-triangle icon
  - `info`: Sky theme with information-circle icon
- **Interactive Attributes**:
  - `dismissible` (default `true`): Renders dismiss button with smooth fade-out and scale transition.
  - `autoSession` (default `true`): Automatically extracts session notifications and validation bags.

## Usage Examples

### 1. Auto-Detection (Layout or View)
```blade
{{-- Automatically renders session('success'), session('error'), session('warning'), session('status'), or $errors --}}
<x-alert class="mb-4" />
```

### 2. Explicit Custom Alert
```blade
<x-alert type="warning" title="Perhatian" message="Batas waktu pendaftaran hampir ditutup." />
```

### 3. Slot / Custom Content
```blade
<x-alert type="success" title="Registrasi Berhasil">
    Silakan cek email Anda untuk verifikasi akun.
</x-alert>
```

## Integrated Locations
1. **Global App Layout**: `resources/views/layouts/app.blade.php` (applied to all authenticated dashboard & admin views).
2. **Global Auth Simple Layout**: `resources/views/components/auth-session-status.blade.php` and `resources/views/pages/auth/login.blade.php`.
3. **Admin Verification & Details**: `resources/views/admin/scout-groups/show.blade.php`.
4. **Contingent Portal Views**:
   - `resources/views/portal/scout-group/index.blade.php`
   - `resources/views/portal/scout-group/patrols/index.blade.php`
   - `resources/views/portal/scout-group/chaperones/index.blade.php`
   - `resources/views/portal/scout-group/review.blade.php`
5. **Admin Operations Views**:
   - `resources/views/admin/officials/index.blade.php`
   - `resources/views/admin/schedules/index.blade.php` & `edit.blade.php`
   - `resources/views/admin/competitions/index.blade.php` & `edit.blade.php`
6. **Auth & Settings Views**:
   - `resources/views/pages/auth/verify-email.blade.php`
   - `resources/views/pages/settings/⚡profile.blade.php`

## Automated Test Coverage
- `tests/Feature/AlertComponentTest.php`: 7 automated tests passing (explicit variants, session auto-detection, error list rendering, dismiss attribute).

# Official (Committee & Judge) Registration Notifications

## Overview
Every committee (*panitia*) or judge (*dewan juri*) registration dispatches in-app bell notifications and PWA web push notifications to both **Superadmin** and **Administrator** roles.

## Notification Triggers
1. **Public Self-Registration** (`OfficialRegistrationForm`):
   - Triggered when an official registers via `/register/aparatur/{token}`.
   - Status: Inactive, awaiting approval.
   - Dispatches: `SendOfficialRegisteredPush::dispatch($user->id)`.
2. **Admin Dashboard Registration** (`OfficialsManagement`):
   - Triggered when an admin creates a new official from `/admin/officials`.
   - Status: Active immediately.
   - Dispatches: `SendOfficialRegisteredPush::dispatch($official->id)`.

## Notification Target & Payload
- **Recipients**: `RoleEnum::Superadmin` and `RoleEnum::Admin`.
- **Payload**:
  - Title: `Pendaftaran Aparatur Baru`
  - Body:
    - Inactive: `{name} ({id_number}) mendaftar sebagai {roles} dan menunggu persetujuan.`
    - Active: `{name} ({id_number}) telah didaftarkan sebagai {roles}.`
  - Target URL: `/admin/officials` (`admin.officials.index`)


# Registration Invitations Seeder & Print Feature

## Overview
Added production-ready seeder for 23 official scout groups' registration invitation codes and PDF printing capabilities for official invitation slips and recap sheets.

## 1. Production Seeder
- **File**: `database/seeders/RegistrationInvitationSeeder.php`
- **Source**: `database/seeders/data/schools.php` (23 schools across 6 kwarrans in Padangsidimpuan).
- **Behavior**: Idempotent (`firstOrCreate` by combined group number). Executed in all environments including production via `database/seeders/DatabaseSeeder.php`.

## 2. Print Features
- **Controller**: `app/Http/Controllers/RegistrationInvitationPrintController.php`
- **Routes**:
  - `GET /admin/registration-invitations/print-slips` (`admin.registration-invitations.print-slips`): Official A4 invitation slips per school with Kwarcab header, formatted invitation code, QR code for instant auto-filled registration, step-by-step guide, and committee signature.
  - `GET /admin/registration-invitations/print-recap` (`admin.registration-invitations.print-recap`): Distribution recap table with receipt signature column.
  - `GET /admin/registration-invitations/{id}/print` (`admin.registration-invitations.print-single`): Single school slip printout.
- **Views**:
  - `resources/views/reports/invitation-slips-pdf.blade.php`
  - `resources/views/reports/invitation-recap-pdf.blade.php`

## 3. UI Updates
- `resources/views/livewire/admin/registration-invitations.blade.php`: Added "Cetak Surat Undangan" & "Cetak Rekap Undangan" buttons to header action slots.
- `resources/views/livewire/admin/registration-invitations-list.blade.php`: Added "Cetak" button to each row.
- `app/Livewire/Auth/InvitationCodeGate.php`: Auto-mounts code from `?code=...` query string.


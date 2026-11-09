# Dashboard Verified Counts and Draft Scout Groups

## Overview

Adjusted the administrator dashboard statistics to calculate **verified** patrols, participants, and chaperones instead of counting all database entries. Added a stat card for scout groups still in **Draft** status.

## Changes

1. **[Dashboard.php](file:///d:/WebApps/lomba-tingkat/app/Livewire/Dashboard.php)**:
    - Added `'draft_groups'` to `adminStats` using `RegistrationStatusEnum::Draft`.
    - Updated `'total_patrols'`, `'boys_patrols'`, `'girls_patrols'`, `'total_participants'`, `'boys_participants'`, `'girls_participants'`, `'total_chaperones'`, `'male_chaperones'`, and `'female_chaperones'` to query verified records only (matching status `Verified` or parent scout group `verification_status` `Verified`).
    - Aligned `committeeStats` verified queries for consistency.
2. **[dashboard.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/livewire/dashboard.blade.php)**:
    - Expanded "Pendaftaran Kontingen" grid to 5 columns (`sm:grid-cols-3 lg:grid-cols-5`) and added `Masih Draft` stat card with `color="neutral"` and icon `pencil-square`.
    - Updated "Peserta & Pendamping Terverifikasi" section header and units (`Regu Sah`, `Orang Sah`).

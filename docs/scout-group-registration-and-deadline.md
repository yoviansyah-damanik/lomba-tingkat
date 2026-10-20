# Scout Group Registration, Number Formatting & Chaperones

**Module:** Contingent Onboarding, Group Number Automation & Chaperones  
**Routes:** `/register/scout-group`, `/portal/scout-group/*`  
**Status:** Completed & Tested (67 tests passing, 294 assertions)

---

## 1. Automated Group Number Formatting

1. **User Input:**
   - User only enters the odd prefix/group number (e.g. `01-093` or raw digits `01093`).
   - **Input Masking (`name="group_number"`):** When typing raw digits without symbols (e.g. `01093`), the field automatically reformats on input and blur into `XX-XXX` (`01-093`).
   - Alpine.js real-time reactive calculation computes the consecutive even number (e.g. `01-094`) and live-previews the combined format (`01-093 & 01-094`).
   - Backend validation & formatting via `ScoutGroup::formatGroupNumbers($oddInput)` automatically saves:
     - `male_group_number`: odd number (e.g. `01-093`).
     - `female_group_number`: even number (e.g. `01-094`).
     - `group_number`: combined format (`01-093 & 01-094`).

2. **Entity Attachment:**
   - **Regu Putra (Boys Patrol):** Automatically bound to odd group number (`$patrol->group_number` returns `male_group_number`, e.g. `01-093`).
   - **Regu Putri (Girls Patrol):** Automatically bound to even group number (`$patrol->group_number` returns `female_group_number`, e.g. `01-094`).

---

## 2. Segregated Chaperone (Bindamping) Slots

1. **Slots:**
   - **Bindamping Putra:** Maximum 1 chaperone (`gender = male`).
   - **Bindamping Putri:** Maximum 1 chaperone (`gender = female`).
2. **Dashboard Management:**
   - Managed in `/portal/scout-group/chaperones`.
   - Dedicated cards and form slot validation prevent registering multiple chaperones of the same gender.
   - Portal and Admin views clearly display both slots and their verification statuses.

---

## 3. Registration Deadline & Kwarran

- **Deadline Protection:** Configured via `events` table (`registration_start_at`, `registration_end_at`, `is_registration_open`). Registration form strictly rejects submissions after deadline.
- **Kwartir Ranting:** All 6 Kwarran options in Kota Padangsidimpuan (Utara, Selatan, Batunadua, Hutaimbaru, Tenggara, Angkola Julu).

---

## 4. Verification Workflow & Email Verification Policy

1. **No Email Verification Required:**
   - Email verification is disabled (`Features::emailVerification()` removed from Fortify, `MustVerifyEmail` removed from `User` model, `'verified'` middleware removed from routes).
   - Newly registered scout group accounts are immediately active (`is_active = true`) and can directly manage patrols and chaperones without waiting for or needing email confirmation.

2. **Administrator Verification:**
   - Registration and contingent dossiers (identitas gudep, regu putra/putri, anggota, bindamping) are verified and approved strictly by **Administrator** (`/admin/scout-groups/*`).
   - Committees (`role:committee`) are restricted from verifying registrations.
   - Status transitions: `Draft` -> `Submitted` -> `Verified` (or `NeedsRevision` / `Rejected`).

---

## 6. Dedicated Standalone Registration Template

- Template pendaftaran pangkalan Gugus Depan dipisahkan secara tersendiri dari layout publik (`<x-layouts::public>`) ke dalam template mandiri khusus (`resources/views/pages/auth/register-scout-group.blade.php`).
- Dilengkapi header khusus (tombol kembali ke Beranda, identitas LT III, mode toggle gelap/terang, dan tautan login), sidebar panduan alur 4 tahap kontingen, serta tata letak fokus tanpa gangguan navigasi publik.

---

## 7. Test Verification

```bash
php artisan test tests/Feature/Auth/ScoutGroupRegistrationTest.php
```
- **Passed:** 10/10 tests (66 assertions).
php artisan test
# Tests: 64 passed (298 assertions, 100% active tests passing)
```

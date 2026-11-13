# Certificate Signatory Digital Signatures

## Overview
Added the capability to upload and embed digital signatures for the Committee Chairperson (Ketua Panitia) and Kwarcab Chairperson (Ketua Kwartir Cabang) on official e-certificates.

## Changes
1. **Database Migration** (`database/migrations/2026_11_13_000001_add_signatures_to_events_table.php`):
   - Added `committee_chair_signature_path` (nullable string).
   - Added `kwarcab_chair_signature_path` (nullable string).
   - Added `show_signatures_on_certificate` (boolean, default true).

2. **Model Updates** (`app/Models/Event.php`):
   - Fillable attributes added.
   - Helper methods: `committeeSignatureUrl()` and `kwarcabSignatureUrl()`.

3. **Livewire Component & UI** (`app/Livewire/Admin/Certificates/Settings.php`, `resources/views/livewire/admin/certificates/settings.blade.php`):
   - Signatory management section with file uploads, live previews, and delete buttons.
   - Live interactive canvas preview displaying uploaded signatures.
   - Embed toggle switch (`show_signatures_on_certificate`).

4. **PDF Generator & Template** (`app/Services/CertificateService.php`, `resources/views/certificates/pdf-single.blade.php`):
   - Signatures converted to base64 data URIs for Dompdf compatibility (`isRemoteEnabled: false`).
   - Rendered in signature columns between role title and signatory name with fixed-height container to preserve layout stability.

5. **Automated Tests** (`tests/Feature/CertificateManagementTest.php`):
   - Verified upload, save, delete, and PDF base64 embedding.


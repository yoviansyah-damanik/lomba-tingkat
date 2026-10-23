# Participant and Scout Group Document Upload

## Overview
Implements document upload capabilities for contingent participants (7 requirement files) and scout groups (Mandate Letter & Payment Proof) for LT III Kwarcab Kota Padangsidimpuan.

## Database & Models
- **Migration:** `2026_10_22_160000_add_document_paths_to_participants_and_scout_groups.php`
- **Participant Model (`app/Models/Participant.php`):**
  - Added constants `Participant::DOCUMENTS` mapping input fields to database columns:
    - `doc_kta` => `doc_kta_path` (Kartu Tanda Anggota)
    - `doc_bpjs` => `doc_bpjs_path` (BPJS)
    - `doc_health_certificate` => `doc_health_certificate_path` (Surat Sehat)
    - `doc_birth_certificate` => `doc_birth_certificate_path` (Akta Kelahiran)
    - `doc_family_card` => `doc_family_card_path` (Kartu Keluarga)
    - `doc_sku` => `doc_sku_path` (SKU)
    - `doc_skk` => `doc_skk_path` (SKK)
  - Updated `$fillable` array.
- **ScoutGroup Model (`app/Models/ScoutGroup.php`):**
  - Added `$fillable`: `mandate_letter_path`, `payment_proof_path`.

## Portal & Livewire Controllers
- **ScoutGroupPortalController:**
  - `participantStore()`: Stores uploaded requirement files to `participants/documents/` on `public` disk.
  - `participantUpdate()`: Uploads new documents and cleans up previously stored files.
  - `participantDestroy()`: Deletes all associated documents and photo upon participant removal.
- **ReviewRegistration (Livewire):**
  - Added `WithFileUploads` trait and properties `$mandate_letter`, `$payment_proof`.
  - Added readiness flags `has_mandate_letter` & `has_payment_proof`.
  - Uploads files to `scout-groups/documents/` upon contingent submission.

## Views
- `resources/views/portal/scout-group/participants/create.blade.php`: Section 6 with file inputs for all 7 participant requirements.
- `resources/views/portal/scout-group/participants/edit.blade.php`: Section 6 with file inputs and "Lihat Berkas" previews for uploaded documents.
- `resources/views/livewire/portal/review-registration.blade.php`:
  - Readiness cards with indicators for Mandate Letter and Payment Proof.
  - Administrative Document upload panel with `<flux:button>` integration.

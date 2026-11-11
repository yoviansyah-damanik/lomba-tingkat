# Certificate Configuration & Issuance System

## Overview

Provides certificate template customization (A4 Landscape), position fine-tuning via interactive visual canvas, master issuance toggle (ON/OFF), landing page announcements, role-based dashboard download links, and a dedicated public certificate and verification portal.

## Architecture & Components

1. **Database Schema**:
    - `events.is_certificate_published`: Boolean master toggle.
    - `certificate_templates`: Stores role-based (`participant`, `chaperone`, `committee`, `judge`) background image path (`template_path`) and element positions (`layout` JSON).

2. **Core Service (`App\Services\CertificateService`)**:
    - Prepares certificate data: Full Name (_Nama Lengkap_), NTA (_Nomor Tanda Anggota_), Institution (_Gugusdepan / Pangkalan_), Role (_Sebagai: Peserta / Pembina Pendamping / Panitia / Dewan Juri_), and Certificate Number (without QR code).
    - Generates A4 landscape PDFs (`297mm x 210mm`) via Dompdf using pure-code SVG/CSS vector artwork (`resources/views/certificates/vector-artwork.blade.php` & `pdf-single.blade.php`) without external background PNG/JPG files.

3. **Admin Configuration (`App\Livewire\Admin\Certificates\Settings`)**:
    - Route: `/admin/certificates/settings` (`admin.certificates.settings`).
    - Interactive Alpine.js visual canvas (297:210 ratio) with drag-and-drop coordinates.
    - Master ON/OFF publishing toggle.
    - Per-role tabs with template upload, layout inputs (X, Y, size, color, align, bold), "Terapkan ke Semua Peran", and "Lihat Contoh PDF".

4. **Public & Dashboard Access**:
    - Landing page (`/`): Prominent official announcement banner when publishing is ON, informing users to log in to their dashboard. No public certificate catalog.
    - Dashboard: Direct certificate access for all accounts:
        - Peserta, Pembina Pendamping, Dewan Juri, & Panitia: One-click "Unduh Sertifikat Saya" via `route('certificates.my')`.
        - Gugus Depan / Pangkalan: Dedicated "E-Sertifikat Kontingen Pangkalan" section listing all verified participants and chaperones with individual PDF download buttons.
        - Administrator: Instant shortcut to certificate template configuration.
    - Public Verification Page (`/certificates/verify/{id}`): Real-time authenticity verification for certificate IDs.

5. **Download Controller (`App\Http\Controllers\CertificateController`)**:
    - Authorized routes for participants, chaperones, officials, and personal `myCertificate`.

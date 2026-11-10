# Certificate Configuration & Issuance System

## Overview

Provides certificate template customization (A4 Landscape), position fine-tuning via interactive visual canvas, master issuance toggle (ON/OFF), landing page announcements, role-based dashboard download links, and a dedicated public certificate and verification portal.

## Architecture & Components

1. **Database Schema**:
    - `events.is_certificate_published`: Boolean master toggle.
    - `certificate_templates`: Stores role-based (`participant`, `chaperone`, `committee`, `judge`) background image path (`template_path`) and element positions (`layout` JSON).

2. **Core Service (`App\Services\CertificateService`)**:
    - Prepares certificate data: Full Name (_Nama Lengkap_), NTA (_Nomor Tanda Anggota_), Institution (_Gugusdepan / Pangkalan_), Role (_Sebagai: Peserta / Pembina Pendamping / Panitia / Dewan Juri_), and Certificate Number (without QR code).
    - Generates A4 landscape PDFs via Dompdf with full-bleed custom image background or default vector/CSS frame.

3. **Admin Configuration (`App\Livewire\Admin\Certificates\Settings`)**:
    - Route: `/admin/certificates/settings` (`admin.certificates.settings`).
    - Interactive Alpine.js visual canvas (297:210 ratio) with drag-and-drop coordinates.
    - Master ON/OFF publishing toggle.
    - Per-role tabs with template upload, layout inputs (X, Y, size, color, align, bold), "Terapkan ke Semua Peran", and "Lihat Contoh PDF".

4. **Public & Portal Pages**:
    - Landing page (`/`): Prominent banner and navigation badge when certificate publishing is ON.
    - Dedicated Certificate Page (`/certificates`): Search by name, NTA, or ID; 1-click personal certificate download; and scout group batch roster.
    - Public Verification Page (`/certificates/verify/{id}`): Real-time authenticity validation from QR code scan.
    - Dashboard: Download cards and announcement banners tailored to active authenticated roles.

5. **Download Controller (`App\Http\Controllers\CertificateController`)**:
    - Authorized routes for participants, chaperones, officials, and personal `myCertificate`.

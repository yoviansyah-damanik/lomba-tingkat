# Patrol Bibs Print (NPP A5 Landscape)

## Overview

Feature to print official **Nomor Punggung Regu (Bibs)** in **A5 Landscape** format based on the generated **Nomor Peserta Perlombaan (NPP)**. Features a full-bleed modern design with distinct theme colors for Boys (Navy & Royal Blue) and Girls (Deep Plum & Magenta), pin guides, contingent identification, and official verification QR code.

## Key Components & Routes

1. **[PatrolBibPrintController.php](file:///d:/WebApps/lomba-tingkat/app/Http/Controllers/PatrolBibPrintController.php)**:
    - Supports printing all patrols, filtering by gender (`boys` or `girls`), or printing a single patrol by ID.
    - Enforces role access control: Admin & Committee can print all/any patrols; Scout Groups can only print their own patrols.
    - Route for Admin: `/admin/npp/print-bibs` (`admin.npp.print-bibs`).
    - Route for Scout Group: `/portal/scout-group/patrols/{patrolId}/bib` (`scout-group.patrols.bib`).
2. **[patrol-bibs-pdf.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/reports/patrol-bibs-pdf.blade.php)**:
    - Formatted in A5 landscape (`210mm x 148mm`) with full-bleed background (`margin: 0`).
    - Includes safety-pin corner crosshairs, official LT-III header branding, giant high-visibility NPP number, patrol & school name, and verification QR code.
3. **[PdfService.php](file:///d:/WebApps/lomba-tingkat/app/Services/PdfService.php)**:
    - Enhanced `render()` and `response()` to support custom paper sizes (`'A5'`), orientation, and optional document footer toggle.
4. **UI Integrations**:
    - **NPP Management** (`/admin/npp`): Dropdown to print all bibs, boys-only, or girls-only, plus row-level print buttons.
    - **Reports Center** (`/admin/reports`): Card to print A5 bibs.
    - **Scout Group Portal** (`/portal/scout-group/patrols`): Print button for each patrol with assigned NPP.

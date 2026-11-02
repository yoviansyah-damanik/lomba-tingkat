# Scout Group Registration Video Tutorial Section

## Overview
Added a dedicated video tutorial section for scout group registration (`resources/video/demo.webm`).

## Implementation Summary
1. **Public Video Storage & Route**:
   - Source: `resources/video/demo.webm`
   - Published copy: `public/video/demo.webm`
   - Route fallback: `GET /video/demo.webm` (`video.tutorial`) with `video/webm` MIME type and RFC 7233 range request support.

2. **UI Sections**:
   - **Home Page (`resources/views/livewire/public/home.blade.php`)**:
     - Added `#video-tutorial` section with responsive HTML5 video player (`aspect-video`), key process highlights (Kode Undangan, Kelola Regu & Peserta, Verifikasi & Kartu QR), and `<flux:button>` CTA buttons.
   - **Guide Page (`resources/views/livewire/public/guide-page.blade.php`)**:
     - Added an embedded visual video tutorial section with step guide and registration trigger.
   - **Registration Sidebar (`resources/views/livewire/auth/partials/registration-sidebar.blade.php`)**:
     - Added quick shortcut button to view the video tutorial guide.

3. **Styling & Standards**:
   - Strictly Tailwind CSS utility classes.
   - All interactive triggers use `<flux:button>` with the `icon="..."` attribute.


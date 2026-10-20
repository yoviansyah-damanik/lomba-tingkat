# Brand Logo and Favicon Configuration

## Overview
Asset `resources/images/logo.png` is published and configured as the primary logo and favicon across all portal and public views.

## File Locations
- Source asset: `resources/images/logo.png`
- Public assets:
  - `public/logo.png`
  - `public/favicon.png`
  - `public/favicon.ico`

## Updated Components & Layouts
1. `resources/views/partials/head.blade.php`: Favicon `<link>` tags updated to `logo.png` / `favicon.ico`.
2. `resources/views/layouts/public.blade.php`: Favicon `<link>` tags and header navigation logo updated.
3. `resources/views/components/app-logo-icon.blade.php`: Render `<img src="{{ asset('logo.png') }}">` with clean object-fit styling.
4. `resources/views/components/app-logo.blade.php`: Brand container styling adjusted for shield emblem rendering.
5. Auth layouts (`split.blade.php`, `card.blade.php`, `simple.blade.php`): Logo sizing standardized.

# Winning Patrols Certificate Configuration & Issuance

Official certificate configuration and dashboard downloads for patrols winning Prestasi Tinggi, Baik, and Cukup.

## 1. Features & Configuration
1. **Admin Certificate Settings (`/admin/certificates/settings`)**:
   - Added tabs for `Regu Berprestasi Tinggi` (`prestasi_tinggi`), `Regu Berprestasi Baik` (`prestasi_baik`), and `Regu Berprestasi Cukup` (`prestasi_cukup`).
   - Supports interactive canvas drag-and-drop, custom styling (font size, color, bold, alignment), custom template image upload, and sample PDF preview via `route('admin.certificates.preview', ['role' => $role])`.
2. **Winning Patrol Certificate Issuance**:
   - **Kejuaraan Umum**: Automatically generates certificate for patrols ranked 1 (Tinggi), 2 (Baik), or 3 (Cukup) in the verified overall LT-III standings.
   - **Mata Lomba**: Automatically generates certificate for locked competition scores having predicates `prestasi_tinggi`, `prestasi_baik`, or `prestasi_cukup`.
3. **Scout Group Dashboards (`/dashboard` & `/portal/scout-group`)**:
   - Displays dedicated section "E-Sertifikat Regu Berprestasi (Tinggi / Baik / Cukup)" with badge, patrol name, competition/general title, and one-click PDF download buttons using `<flux:button>`.
4. **Verification & Authorization**:
   - Public verification (`/certificates/verify/{id}`) authenticates winning patrol certificate IDs (`GEN-{patrol_id}` and `LMB-{score_id}`).
   - Direct download routes (`certificates.download.patrol.general` and `certificates.download.competition-score`) enforce authorization (accessible by Admin, owner Scout Group, and assigned Chaperone).

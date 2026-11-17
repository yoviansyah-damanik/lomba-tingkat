# Berita Acara & Final Standings PDF Export

Official printable reports for LT III final standing decrees and cumulative general rank.

## 1. Features
1. **Berita Acara Points Display**:
   - The official decree document (`reports.berita-acara-pdf`) now displays cumulative weight points (`total_points`) alongside gold, silver, bronze counts, Gudep, and official predicate.
2. **Cetak Peringkat Umum (Akhir) Seluruh Regu**:
   - New printable document (`reports.final-standings-pdf`) listing all verified patrols ranked 1 to N for both Putra and Putri categories.
   - Displays: Rank, NPP, Patrol Name & School, Gudep, Kwarran, Prestasi Tinggi, Prestasi Baik, Prestasi Cukup, Total Poin, and Predicate.
   - Includes standard official Kop, legal decree statement, and independent three-party signatory block (Panitia, Dewan Juri, Kwarcab) rendered on each respective category section (Putra and Putri).
3. **Aligned Signatories Block**:
   - Component `x-report.signatories-block` splits the 3-column signature block into two table rows (`<tr>` for headers, `<tr>` for name and NTA baseline), ensuring all three signatories' names align horizontally regardless of line wrap differences in their designations.

## 2. Routes & Navigation
- **Print Preview**: `GET /standings/all/print` (`standings.final.print`) & `GET /admin/reports/print/final-standings` (`reports.print.final-standings`).
- **PDF Generation**: `GET /standings/all/pdf` (`standings.final.pdf`) & `GET /admin/reports/pdf/final-standings` (`reports.pdf.final-standings`).
- **UI Triggers**: Action buttons in `/admin/standings` and `/admin/reports`.

{{-- Gaya dasar dokumen PDF A4 (CSS sederhana yang didukung pembuat PDF) --}}
<style>
    @page { size: A4 portrait; margin: 15mm 15mm 28mm 15mm; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #18181b; margin: 0; }
    table { border-collapse: collapse; width: 100%; page-break-inside: auto; }
    tr { page-break-inside: avoid; page-break-after: auto; }
    thead { display: table-header-group; }
    .doc-title { text-align: center; font-size: 12px; font-weight: bold; text-transform: uppercase; text-decoration: underline; margin-top: 4px; }
    .doc-subtitle { text-align: center; font-size: 9px; color: #52525b; margin-top: 3px; }
    .section-title { font-size: 9px; font-weight: bold; text-transform: uppercase; margin: 14px 0 5px; }
    .data th { background: #f4f4f5; border: 1px solid #a1a1aa; padding: 4px; font-size: 8px; text-transform: uppercase; text-align: center; vertical-align: middle; }
    .data td { border: 1px solid #d4d4d8; padding: 3px 4px; font-size: 8px; }
    .center { text-align: center; }
    .strong { font-weight: bold; }
    .muted { color: #71717a; font-size: 8px; }
    .zebra { background: #fafafa; }
    .highlight { background: #fffbeb; font-weight: bold; }
    .justify { text-align: justify; line-height: 1.5; }
    .signatures { page-break-inside: avoid; }
    .signatures td { width: 33%; text-align: center; vertical-align: top; padding: 0 6px; font-size: 9px; }
    .note { margin-top: 18px; border-top: 1px solid #d4d4d8; padding-top: 6px; font-size: 7px; color: #71717a; }
</style>

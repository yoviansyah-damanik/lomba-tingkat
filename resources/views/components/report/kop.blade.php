@props(['year' => null])

@php
    $year ??= \App\Models\Event::where('is_active', true)->first()?->start_date?->year ?? 2026;
@endphp

{{-- Kop seluruh dokumen cetak PDF: logo di kiri, tiga baris identitas kegiatan (tanpa utilitas Tailwind agar kompatibel dengan PDF) --}}
<table style="width: 100%; border-collapse: collapse; border-bottom: 3px double #18181b; margin-bottom: 14px;">
    <tr>
        <td style="width: 72px; padding: 0 0 8px 0; vertical-align: middle;">
            <img src="{{ \App\Services\PdfService::logoPath() }}" alt="Logo" style="width: 64px; height: 64px;">
        </td>
        <td style="padding: 0 72px 8px 0; text-align: center; vertical-align: middle;">
            <div style="font-size: 15px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; color: #18181b;">Lomba Tingkat III</div>
            <div style="font-size: 12px; font-weight: bold; text-transform: uppercase; color: #18181b; margin-top: 2px;">Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan</div>
            <div style="font-size: 11px; font-weight: bold; color: #3f3f46; margin-top: 2px;">Tahun {{ $year }}</div>
        </td>
    </tr>
</table>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    @include('reports.partials.pdf-styles')
</head>
<body>
    @php
        $year = $event?->start_date?->year ?? 2026;
    @endphp

    <x-report.kop :year="$year" />

    <div class="doc-title">{{ $title }}</div>
    <div class="doc-subtitle">
        {{ $invitations->count() }} Pangkalan &bull; Per {{ $generatedAt->translatedFormat('d F Y, H:i') }} WIB
    </div>

    <table class="data" style="margin-top: 12px;">
        <thead>
            <tr>
                <th style="width: 22px;">No</th>
                <th>Pangkalan / Sekolah</th>
                <th style="width: 75px;">No. Gudep</th>
                <th style="width: 110px;">Kwarran</th>
                <th style="width: 75px;">Kode Undangan</th>
                <th style="width: 65px;">Status</th>
                <th style="width: 75px;">Tgl Registrasi</th>
                <th style="width: 85px;">Paraf Penerima</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($invitations as $invitation)
                <tr class="{{ $loop->even ? 'zebra' : '' }}">
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>
                        <span class="strong">{{ $invitation->school_name }}</span>
                    </td>
                    <td class="center" style="font-family: monospace; white-space: nowrap;">
                        {{ $invitation->group_number }}
                    </td>
                    <td>{{ $invitation->kwarran_name }}</td>
                    <td class="center" style="font-family: monospace; font-weight: bold; letter-spacing: 1px;">
                        {{ $invitation->formattedCode() }}
                    </td>
                    <td class="center">
                        @if ($invitation->used_at)
                            <span style="color: #059669; font-weight: bold;">Terpakai</span>
                        @elseif ($invitation->is_active)
                            <span style="color: #2563eb;">Aktif</span>
                        @else
                            <span style="color: #71717a;">Off</span>
                        @endif
                    </td>
                    <td class="center muted" style="font-size: 7.5px;">
                        {{ $invitation->used_at ? $invitation->used_at->translatedFormat('d/m/y H:i') : '-' }}
                    </td>
                    <td class="center"></td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="center muted">Belum ada kode undangan registrasi yang diterbitkan.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($invitations->count() > 0)
            <tfoot>
                <tr class="highlight">
                    <td colspan="4" class="center">TOTAL PANGKALAN TERDAFTAR</td>
                    <td colspan="4" class="center">
                        {{ $invitations->count() }} Pangkalan ({{ $invitations->whereNotNull('used_at')->count() }} Terpakai, {{ $invitations->where('is_active', true)->whereNull('used_at')->count() }} Belum Dipakai)
                    </td>
                </tr>
            </tfoot>
        @endif
    </table>

    <table class="signatures" style="width: 100%; margin-top: 24px;">
        <tr>
            <td style="width: 50%;"></td>
            <td style="width: 50%; text-align: center;">
                <div>Padangsidimpuan, {{ $generatedAt->translatedFormat('d F Y') }}</div>
                <div style="font-weight: bold; margin-top: 2px;">Panitia Pelaksana LT III Tahun {{ $year }}</div>
                <div style="font-size: 7.5px; color: #52525b;">Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan</div>
                <div style="height: 50px;"></div>
                <div style="font-weight: bold; text-decoration: underline;">PANITIA PELAKSANA LT III</div>
                <div style="font-size: 7.5px; color: #52525b;">Bidang Administrasi &amp; Pendaftaran</div>
            </td>
        </tr>
    </table>
</body>
</html>


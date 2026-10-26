<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    @include('reports.partials.pdf-styles')
</head>
<body>
    <x-report.kop />

    <div class="doc-title">{{ $title }}</div>
    <div class="doc-subtitle">{{ $groups->count() }} pangkalan &bull; Per {{ $generatedAt->translatedFormat('d F Y, H:i') }} WIB</div>

    <table class="data" style="margin-top: 12px;">
        <thead>
            <tr>
                <th style="width: 22px;">No</th>
                <th>Pangkalan / Sekolah</th>
                <th style="width: 70px;">No. Gudep</th>
                <th>Kwarran</th>
                <th>Penanggung Jawab</th>
                <th style="width: 28px;">Regu</th>
                <th style="width: 34px;">Peserta</th>
                <th style="width: 34px;">Binda</th>
                <th style="width: 56px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($groups as $group)
                <tr class="{{ $loop->even ? 'zebra' : '' }}">
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>
                        <span class="strong">{{ $group->school_name }}</span>
                        @if ($group->address)<br><span class="muted">{{ $group->address }}</span>@endif
                    </td>
                    <td class="center" style="white-space: nowrap;">{{ $group->group_number }}</td>
                    <td>{{ $group->kwarran_name }}</td>
                    <td>
                        {{ $group->contact_person ?? '-' }}
                        @if ($group->contact_phone)<br><span class="muted">{{ $group->contact_phone }}</span>@endif
                    </td>
                    <td class="center">{{ $group->patrols_count }}</td>
                    <td class="center">{{ $group->participants_count }}</td>
                    <td class="center">{{ $group->chaperones_count }}</td>
                    <td class="center">{{ $group->verification_status instanceof \App\Enums\RegistrationStatusEnum ? $group->verification_status->label() : ucfirst((string) $group->verification_status) }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="center muted">Belum ada pangkalan terdaftar.</td></tr>
            @endforelse
        </tbody>
        @if ($groups->count() > 1)
            <tfoot>
                <tr class="highlight">
                    <td colspan="5" class="center">TOTAL</td>
                    <td class="center">{{ $groups->sum('patrols_count') }}</td>
                    <td class="center">{{ $groups->sum('participants_count') }}</td>
                    <td class="center">{{ $groups->sum('chaperones_count') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>

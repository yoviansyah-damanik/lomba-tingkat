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
    <div class="doc-subtitle">{{ $groups->sum(fn ($row) => $row['members']->count()) }} bindamping &bull; Per {{ $generatedAt->translatedFormat('d F Y, H:i') }} WIB</div>

    <table class="data" style="margin-top: 12px;">
        <thead>
            <tr>
                <th style="width: 22px;">No</th>
                <th>Nama Lengkap</th>
                <th style="width: 20px;">L/P</th>
                <th>Pangkalan / Gudep</th>
                <th style="width: 90px;">Mendampingi Regu</th>
                <th style="width: 78px;">NTA</th>
                <th style="width: 76px;">No. HP</th>
            </tr>
        </thead>
        <tbody>
            @php($no = 0)
            @forelse ($groups as $row)
                <tr class="highlight">
                    <td colspan="7">{{ $row['group']->school_name }} &mdash; Gudep {{ $row['group']->group_number }} &bull; {{ $row['group']->kwarran_name }}</td>
                </tr>
                @foreach ($row['members'] as $c)
                    <tr class="{{ $loop->even ? 'zebra' : '' }}">
                        <td class="center">{{ ++$no }}</td>
                        <td class="strong">{{ $c->full_name }}</td>
                        <td class="center">{{ in_array($c->gender, ['female', 'P'], true) ? 'P' : 'L' }}</td>
                        <td>{{ $row['group']->school_name }}</td>
                        <td>{{ $c->patrols->pluck('patrol_name')->implode(', ') ?: '-' }}</td>
                        <td style="white-space: nowrap;">{{ $c->scout_id ?: '-' }}</td>
                        <td style="white-space: nowrap;">{{ $c->phone_number ?: '-' }}</td>
                    </tr>
                @endforeach
            @empty
                <tr><td colspan="7" class="center muted">Belum ada bindamping terverifikasi.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

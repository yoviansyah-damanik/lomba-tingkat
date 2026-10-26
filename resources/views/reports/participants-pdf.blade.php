<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    @include('reports.partials.pdf-styles')
</head>
<body>
    {{-- Setiap gugus depan memulai halaman baru: kop, identitas pangkalan, lalu daftar pesertanya --}}
    @forelse ($groups as $row)
        @php($group = $row['group'])
        <div class="{{ $loop->first ? '' : 'page-break' }}">
            <x-report.kop />

            <div class="doc-title">{{ $title }}</div>
            <div class="doc-subtitle">Per {{ $generatedAt->translatedFormat('d F Y, H:i') }} WIB</div>

            <table style="margin-top: 10px; font-size: 9px;">
                <tr>
                    <td style="width: 90px;">Pangkalan</td><td style="width: 8px;">:</td><td class="strong">{{ $group->school_name }}</td>
                    <td style="width: 90px;">Kwartir Ranting</td><td style="width: 8px;">:</td><td>{{ $group->kwarran_name }}</td>
                </tr>
                <tr>
                    <td>Nomor Gudep</td><td>:</td><td>{{ $group->group_number }}</td>
                    <td>Jumlah Peserta</td><td>:</td><td>{{ $row['members']->count() }} orang</td>
                </tr>
            </table>

            <table class="data" style="margin-top: 8px;">
                <thead>
                    <tr>
                        <th style="width: 22px;">No</th>
                        <th>Nama Lengkap</th>
                        <th style="width: 20px;">L/P</th>
                        <th style="width: 70px;">Regu</th>
                        <th style="width: 58px;">Posisi</th>
                        <th style="width: 78px;">NTA</th>
                        <th style="width: 82px;">Tempat, Tgl. Lahir</th>
                        <th style="width: 26px;">Kelas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($row['members'] as $p)
                        <tr class="{{ $loop->even ? 'zebra' : '' }}">
                            <td class="center">{{ $loop->iteration }}</td>
                            <td class="strong">{{ $p->full_name }}</td>
                            <td class="center">{{ in_array($p->gender, ['female', 'P'], true) ? 'P' : 'L' }}</td>
                            <td>{{ $p->patrol?->patrol_name ?? '-' }}</td>
                            <td class="center">{{ match ($p->role_in_patrol?->value) { 'patrol_leader' => 'Pinru', 'assistant_patrol_leader' => 'Wapinru', default => 'Anggota' } }}</td>
                            <td style="white-space: nowrap;">{{ $p->scout_id ?: '-' }}</td>
                            <td>{{ trim(($p->birth_place ?? '').', '.($p->birth_date?->translatedFormat('d/m/Y') ?? ''), ', ') ?: '-' }}</td>
                            <td class="center">{{ $p->class_grade ?: '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <x-report.kop />
        <div class="doc-title">{{ $title }}</div>
        <p class="center muted" style="margin-top: 20px;">Belum ada peserta terverifikasi.</p>
    @endforelse
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Izin — {{ $permission->type->label() }}</title>
    @include('reports.partials.pdf-styles')
</head>
<body>
    <x-report.kop />

    <div class="doc-title">Surat Izin</div>
    <div class="doc-subtitle">{{ $permission->type->label() }}</div>
    <div class="doc-subtitle">Nomor: {{ $permission->letterNumber() }}</div>

    <p class="justify" style="margin-top: 12px;">
        Yang bertanda tangan di bawah ini, Ketua Panitia Pelaksana Lomba Tingkat III Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan,
        @if ($permission->type === \App\Enums\PermissionTypeEnum::GuestVisit)
            memberikan izin kunjungan tamu ke area perkemahan dengan keterangan sebagai berikut:
        @elseif ($permission->type === \App\Enums\PermissionTypeEnum::LeavePermanent)
            memberikan izin kepada peserta dan pembina pendamping di bawah ini untuk meninggalkan perkemahan secara permanen (tidak kembali) dengan keterangan sebagai berikut:
        @else
            memberikan izin kepada peserta dan pembina pendamping di bawah ini untuk meninggalkan perkemahan sementara, dan wajib kembali serta dikonfirmasi petugas dengan pemindaian QR Code, dengan keterangan sebagai berikut:
        @endif
    </p>

    <table class="data" style="margin-top: 8px;">
        <tbody>
            <tr><td style="width: 28%;" class="strong">Jenis Izin</td><td>{{ $permission->type->label() }}</td></tr>
            @if ($permission->scoutGroup)
                <tr><td class="strong">Gugus Depan / Pangkalan</td><td>{{ $permission->scoutGroup->school_name }} ({{ $permission->scoutGroup->group_number }})</td></tr>
            @endif
            @if ($permission->guest_name)
                <tr><td class="strong">Nama Tamu</td><td>{{ $permission->guest_name }}</td></tr>
            @endif
            <tr><td class="strong">Keterangan</td><td>{{ $permission->reason }}</td></tr>
            <tr><td class="strong">Tanggal Izin</td><td>{{ $issuedAt->translatedFormat('l, d F Y') }}, pukul {{ $issuedAt->format('H:i') }} WIB</td></tr>
        </tbody>
    </table>

    @if ($participants->isNotEmpty())
        <div class="section-title">Daftar Peserta</div>
        <table class="data">
            <thead>
                <tr>
                    <th style="width: 24px;">No</th>
                    <th>Nama Peserta</th>
                    <th style="width: 80px;">NTA</th>
                    <th style="width: 70px;">ID</th>
                    <th>Regu</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($participants as $i => $participant)
                    <tr>
                        <td class="center">{{ $i + 1 }}</td>
                        <td>{{ $participant->full_name }}</td>
                        <td class="center">{{ $participant->scout_id ?: '-' }}</td>
                        <td class="center">{{ $participant->id_number }}</td>
                        <td>{{ $participant->patrol?->patrol_name ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if ($chaperones->isNotEmpty())
        <div class="section-title">Penanggung Jawab (Pembina Pendamping)</div>
        <table class="data">
            <thead>
                <tr>
                    <th style="width: 24px;">No</th>
                    <th>Nama Pembina Pendamping</th>
                    <th style="width: 80px;">NTA</th>
                    <th style="width: 70px;">ID</th>
                    <th style="width: 80px;">Kategori</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($chaperones as $i => $chaperone)
                    <tr>
                        <td class="center">{{ $i + 1 }}</td>
                        <td>{{ $chaperone->full_name }}</td>
                        <td class="center">{{ $chaperone->scout_id ?: '-' }}</td>
                        <td class="center">{{ $chaperone->id_number }}</td>
                        <td class="center">{{ $chaperone->gender === 'male' ? 'Putra' : 'Putri' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    @if ($permission->type === \App\Enums\PermissionTypeEnum::LeaveTemporary)
        <p class="justify" style="margin-top: 10px;">
            Peserta wajib kembali ke perkemahan dan melapor kepada petugas untuk konfirmasi kembali. Pembina pendamping bertanggung jawab atas peserta selama berada di luar perkemahan.
        </p>
    @elseif ($permission->type === \App\Enums\PermissionTypeEnum::LeavePermanent)
        <p class="justify" style="margin-top: 10px;">
            Pembina pendamping bertanggung jawab atas peserta selama perjalanan setelah meninggalkan perkemahan.
        </p>
    @endif

    <p style="margin-top: 14px;">Padangsidimpuan, {{ $issuedAt->translatedFormat('d F Y') }}</p>

    <table class="signatures" style="margin-top: 6px;">
        <tr>
            @if ($responsible)
                <td>Penanggung Jawab,<br><strong>Pembina Pendamping</strong>
                    <x-report.signatory :space="64" :name="$responsible->full_name" :nta="$responsible->scout_id" :qr="$responsibleQr" />
                </td>
            @endif
            <td>Mengetahui,<br><strong>Ketua Panitia Pelaksana LT III</strong>
                <x-report.signatory :space="64" :name="$chair['name']" :nta="$chair['nta']" :qr="$chairQr" />
            </td>
        </tr>
    </table>

    <p class="muted" style="margin-top: 10px; text-align: center;">Pindai QR Code tanda tangan untuk memeriksa keaslian surat ini.</p>
</body>
</html>

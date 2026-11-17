@props(['event' => null, 'space' => 56])

{{-- Tabel tanda tangan 3 pihak dengan posisi baris jabatan & nama yang sejajar sempurna --}}
<table class="signatures" style="margin-top: 18px; width: 100%; border-collapse: collapse; page-break-inside: avoid;">
    <tr>
        <td style="width: 33.3%; text-align: center; vertical-align: top;">
            Mengetahui,<br>
            <strong>Ketua Panitia Pelaksana LT-III</strong>
        </td>
        <td style="width: 33.3%; text-align: center; vertical-align: top;">
            Ditetapkan Oleh,<br>
            <strong>Ketua Dewan Juri LT-III</strong>
        </td>
        <td style="width: 33.3%; text-align: center; vertical-align: top;">
            Mengesahkan,<br>
            <strong>Kwartir Cabang Gerakan Pramuka<br>Kota Padangsidimpuan</strong><br>
            Ketua
        </td>
    </tr>
    <tr>
        <td style="width: 33.3%; text-align: center; vertical-align: bottom; padding-top: {{ $space }}px;">
            <x-report.signatory :space="0" :name="$event?->signatory('committee')['name']" :nta="$event?->signatory('committee')['nta']" />
        </td>
        <td style="width: 33.3%; text-align: center; vertical-align: bottom; padding-top: {{ $space }}px;">
            <x-report.signatory :space="0" :name="$event?->signatory('judge')['name']" :nta="$event?->signatory('judge')['nta']" />
        </td>
        <td style="width: 33.3%; text-align: center; vertical-align: bottom; padding-top: {{ $space }}px;">
            <x-report.signatory :space="0" :name="$event?->signatory('kwarcab')['name']" :nta="$event?->signatory('kwarcab')['nta']" />
        </td>
    </tr>
</table>

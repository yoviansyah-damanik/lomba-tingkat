@props(['item', 'scoutGroup' => null])

@php
    $rows = $item['rows'] ?? [
        'Nomor Gudep' => $item['group_number'] ?? '-',
        'Pangkalan' => $item['school_name'] ?? ($scoutGroup?->school_name ?? '-'),
        'Kwartir Ranting' => $item['kwarran_name'] ?? ($scoutGroup?->kwarran_name ?? 'Padangsidimpuan'),
        'Kontak/WA' => $item['contact'] ?? '-',
    ];
@endphp

{{-- Satu kartu A6 peserta/bindamping/aparatur dari data item yang sudah disiapkan service --}}
<x-digital-card.card
    data-card-name="{{ $item['name'] }}"
    data-card-id="{{ $item['id_number'] }}"
    data-card-group="{{ $item['group'] }}"
>
    <x-digital-card.header :theme="$item['theme']" />
    <x-digital-card.category-strip :theme="$item['theme']">
        {!! $item['category_label'] !!}
    </x-digital-card.category-strip>
    <x-digital-card.body>
        @if (! empty($item['patrol_name']))
            <x-digital-card.patrol-badge :name="$item['patrol_name']" :theme="$item['theme']" />
        @endif
        <x-digital-card.photo :photo-url="! empty($item['photo_path']) ? asset('storage/' . $item['photo_path']) : null" :name="$item['name']" :role="$item['role'] ?? 'member'" :npp="$item['npp'] ?? null" :theme="$item['theme']" />
        <x-digital-card.meta-info :name="$item['name']" :scout-id="$item['scout_id']" :id-number="$item['id_number']" :theme="$item['theme']" />
        <x-digital-card.details-table>
            @foreach ($rows as $label => $value)
                <x-digital-card.details-row :label="$label" :value="$value" />
            @endforeach
        </x-digital-card.details-table>
        <x-digital-card.qr-section :qr-data-uri="$item['qrDataUri']" :token="$item['qr_token']" :theme="$item['theme']" />
    </x-digital-card.body>
    <x-digital-card.footer :theme="$item['theme']">
        PANITIA LT III 2026 &bull; KWARCAB PADANGSIDIMPUAN
    </x-digital-card.footer>
</x-digital-card.card>

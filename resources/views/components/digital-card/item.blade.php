@props(['item', 'scoutGroup'])

{{-- Satu kartu B2 peserta/bindamping dari data item yang sudah disiapkan controller --}}
<x-digital-card.card
    data-card-name="{{ $item['name'] }}"
    data-card-id="{{ $item['id_number'] }}"
    data-card-group="{{ $item['group'] }}"
>
    <x-digital-card.header :theme="$item['theme']" />
    <x-digital-card.category-strip :theme="$item['theme']">
        {{ $item['category_label'] }}
    </x-digital-card.category-strip>
    <x-digital-card.body>
        @if ($item['patrol_name'])
            <x-digital-card.patrol-badge :name="$item['patrol_name']" :theme="$item['theme']" />
        @endif
        <x-digital-card.photo :photo-url="$item['photo_path'] ? asset('storage/' . $item['photo_path']) : null" :name="$item['name']" :role="$item['role']" :npp="$item['npp'] ?? null" :theme="$item['theme']" />
        <x-digital-card.meta-info :name="$item['name']" :scout-id="$item['scout_id']" :id-number="$item['id_number']" :theme="$item['theme']" />
        <x-digital-card.details-table>
            <x-digital-card.details-row label="Nomor Gudep" :value="$item['group_number']" />
            <x-digital-card.details-row label="Pangkalan" :value="Str::limit($scoutGroup->school_name, 22)" />
            <x-digital-card.details-row label="Kwartir Ranting" :value="$scoutGroup->kwarran_name ?? 'Padangsidimpuan'" />
            <x-digital-card.details-row label="Kontak/WA" :value="$item['contact']" />
        </x-digital-card.details-table>
        <x-digital-card.qr-section :qr-data-uri="$item['qrDataUri']" :token="$item['qr_token']" :theme="$item['theme']" />
    </x-digital-card.body>
    <x-digital-card.footer :theme="$item['theme']">
        PANITIA LT III 2026 &bull; KWARCAB PADANGSIDIMPUAN
    </x-digital-card.footer>
</x-digital-card.card>

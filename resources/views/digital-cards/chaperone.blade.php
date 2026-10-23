@php
    $theme = $chaperone->gender === 'male' ? 'chaperone-male' : 'chaperone-female';
    $photoUrl = $chaperone->photo_path ? asset('storage/' . $chaperone->photo_path) : null;
    $bodyBg = $chaperone->isFemale() ? 'bg-[#1e0938]' : 'bg-[#064e3b]';
@endphp

<x-digital-card.layout :title="'KARTU PEMBINA PENDAMPING (B2) - ' . $chaperone->full_name" :body-class="$bodyBg">
    {{-- Toolbar Aksi --}}
    <x-digital-card.toolbar :subtitle="$scoutGroup?->school_name . ' • ' . $chaperone->full_name" :batch-url="$scoutGroup ? route('digital-cards.chaperone.batch', $scoutGroup->id) : null" :batch-label="'Kartu Seluruh Bindamping'" :download-filename="'KARTU_BINDAMPING_' . Str::slug($chaperone->full_name)" />

    {{-- KARTU UKURAN RESMI B2 --}}
    <x-digital-card.card data-card-name="{{ $chaperone->full_name }}" data-card-id="{{ $chaperone->id_number }}">
        {{-- Header Kartu --}}
        <x-digital-card.header :theme="$theme" />

        {{-- Pita Kategori --}}
        <x-digital-card.category-strip :theme="$theme">
            PEMBINA PENDAMPING {{ $chaperone->gender === 'male' ? 'PUTRA' : 'PUTRI' }}
        </x-digital-card.category-strip>

        {{-- Badan Kartu --}}
        <x-digital-card.body>
            {{-- Pas Foto --}}
            <x-digital-card.photo :photo-url="$photoUrl" :name="$chaperone->full_name" />

            {{-- Nama, NTA & Nomor ID (Tanpa Box) --}}
            <x-digital-card.meta-info :name="$chaperone->full_name" :scout-id="$chaperone->scout_id" :id-number="$chaperone->id_number ?: 'ID-BINDAMPING'" :theme="$theme" />

            {{-- Informasi Rincian (Tanpa Duplikasi NTA & Tanpa NIK) --}}
            <x-digital-card.details-table>
                <x-digital-card.details-row label="Nomor Gudep" :value="$chaperone->group_number ?: $scoutGroup?->group_number ?? '-'" />
                <x-digital-card.details-row label="Pangkalan" :value="$scoutGroup?->school_name ?? '-'" />
                <x-digital-card.details-row label="Kwartir Ranting" :value="$scoutGroup?->kwarran_name ?? 'Padangsidimpuan'" />
                <x-digital-card.details-row label="Kontak/WA" :value="$chaperone->phone_number ?: '-'" />
            </x-digital-card.details-table>

            {{-- Validasi QR Code Digital --}}
            <x-digital-card.qr-section :qr-data-uri="$qrDataUri" :token="$chaperone->qr_token" :theme="$theme" />
        </x-digital-card.body>

        {{-- Footer Keamanan --}}
        <x-digital-card.footer :theme="$theme">
            PANITIA LT III 2026 &bull; KWARCAB PADANGSIDIMPUAN
        </x-digital-card.footer>
    </x-digital-card.card>
</x-digital-card.layout>

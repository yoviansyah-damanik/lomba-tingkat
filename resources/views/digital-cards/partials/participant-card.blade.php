@php
    $theme = $participant->patrol?->isBoys() ? 'boys' : 'girls';
    $role = $participant->isLeader() ? 'leader' : ($participant->isAssistantLeader() ? 'assistant_leader' : 'member');
    $photoUrl = $participant->photo_path ? asset('storage/' . $participant->photo_path) : null;
@endphp

{{-- KARTU UKURAN RESMI B2 --}}
<x-digital-card.card data-card-name="{{ $participant->full_name }}" data-card-id="{{ $participant->id_number }}">
    {{-- Header Kartu --}}
    <x-digital-card.header :theme="$theme" />

    {{-- Pita Kategori --}}
    <x-digital-card.category-strip :theme="$theme">
        {{ $participant->patrol?->isBoys() ? 'PESERTA PENGGALANG PUTRA' : 'PESERTA PENGGALANG PUTRI' }}
    </x-digital-card.category-strip>

    {{-- Badan Kartu --}}
    <x-digital-card.body>
        {{-- Lencana Vertikal Nama Regu (Sisi Paling Kanan Kartu) --}}
        @if ($participant->patrol?->patrol_name)
            <x-digital-card.patrol-badge :name="$participant->patrol->patrol_name" :theme="$theme" />
        @endif

        {{-- Pas Foto & Lencana Pinru/Wapinru --}}
        <x-digital-card.photo :photo-url="$photoUrl" :name="$participant->full_name" :role="$role" :npp="$participant->patrol?->npp" :theme="$theme" />

        {{-- Nama Peserta, NTA & Nomor ID (Tanpa Box) --}}
        <x-digital-card.meta-info :name="$participant->full_name" :scout-id="$participant->scout_id" :id-number="$participant->id_number ?: 'ID-PESERTA'" :theme="$theme" />

        {{-- Informasi Rincian --}}
        <x-digital-card.details-table>
            <x-digital-card.details-row label="Nomor Gudep" :value="$participant->patrol?->group_number" />
            <x-digital-card.details-row label="Pangkalan" :value="$scoutGroup?->school_name ?? '-'" />
            <x-digital-card.details-row label="Kwartir Ranting" :value="$scoutGroup?->kwarran_name ?? 'Padangsidimpuan'" />
            <x-digital-card.details-row label="Kontak/WA" :value="$participant->phone ?: ($participant->parent_phone ?: '-')" />
        </x-digital-card.details-table>

        {{-- Validasi QR Code Digital --}}
        <x-digital-card.qr-section :qr-data-uri="$qrDataUri" :token="$participant->qr_token" :theme="$theme" />
    </x-digital-card.body>

    {{-- Footer Keamanan --}}
    <x-digital-card.footer :theme="$theme">
        PANITIA LT III 2026 &bull; KWARCAB PADANGSIDIMPUAN
    </x-digital-card.footer>
</x-digital-card.card>

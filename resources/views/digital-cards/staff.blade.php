@php
    $theme = $staff->isJudge() ? 'judge' : 'staff';
    $photoUrl = $staff->avatar_path ? asset('storage/' . $staff->avatar_path) : null;
    $bodyBg = $staff->isJudge() ? 'bg-[#1c0d02]' : 'bg-[#1a0505]';
    $roleName =
        $staff->isJudge() && $staff->isCommittee()
            ? 'PANITIA PELAKSANA & DEWAN JURI'
            : ($staff->isJudge()
                ? 'DEWAN JURI PERLOMBAAN'
                : 'PANITIA PELAKSANA');
@endphp

<x-digital-card.layout :title="'KARTU APARATUR (B2) - ' . $staff->name" :body-class="$bodyBg">
    {{-- Toolbar Cetak & Aksi --}}
    <x-digital-card.toolbar :subtitle="'Aparatur: ' . $staff->name" :download-filename="'KARTU_APARATUR_' . Str::slug($staff->name)" />

    {{-- KARTU UKURAN RESMI B2 --}}
    <x-digital-card.card data-card-name="{{ $staff->name }}" data-card-id="{{ $staff->id_number }}">
        {{-- Header Kartu --}}
        <x-digital-card.header :theme="$theme" />

        {{-- Pita Kategori --}}
        <x-digital-card.category-strip :theme="$theme">
            {!! $roleName !!}
        </x-digital-card.category-strip>

        {{-- Badan Kartu --}}
        <x-digital-card.body>
            {{-- Pas Foto --}}
            <x-digital-card.photo :photo-url="$photoUrl" :name="$staff->name" />

            {{-- Nama, NTA & Nomor ID (Tanpa Box) --}}
            <x-digital-card.meta-info :name="$staff->name" :scout-id="$staff->scout_id" :id-number="$staff->id_number ?: 'ID-PANITIA'" :theme="$theme" />

            {{-- Informasi Rincian --}}
            <x-digital-card.details-table>
                <x-digital-card.details-row label="Jabatan/Tugas" :value="$staff->position ?? $staff->roleLabel()" />
                <x-digital-card.details-row label="Pangkalan/Instansi" :value="$staff->institution ?? 'Kwartir Cabang Kota Padangsidimpuan'" />
                <x-digital-card.details-row label="Kwartir Ranting" :value="$staff->kwarran ?? 'Padangsidimpuan'" />
                <x-digital-card.details-row label="Kontak/WA" :value="$staff->phone_number ?: '-'" />
            </x-digital-card.details-table>

            {{-- Validasi QR Code Digital --}}
            <x-digital-card.qr-section :qr-data-uri="$qrDataUri" :token="$staff->qr_token" :theme="$theme" />
        </x-digital-card.body>

        {{-- Footer Keamanan --}}
        <x-digital-card.footer :theme="$theme">
            PANITIA LT III 2026 &bull; KWARCAB PADANGSIDIMPUAN
        </x-digital-card.footer>
    </x-digital-card.card>
</x-digital-card.layout>

@php
    $theme = $chaperone->gender === 'male' ? 'chaperone-male' : 'chaperone-female';
    $photoUrl = $chaperone->photo_path ? asset('storage/' . $chaperone->photo_path) : null;
    $bodyBg = $chaperone->isFemale() ? 'bg-[#1e0938]' : 'bg-[#064e3b]';
@endphp

<x-digital-card.layout :title="'KARTU PEMBINA PENDAMPING (B2) - ' . $chaperone->full_name" :body-class="$bodyBg">
    {{-- Toolbar Aksi --}}
    <x-digital-card.toolbar :subtitle="$scoutGroup?->school_name . ' • ' . $chaperone->full_name" :batch-url="$scoutGroup ? route('digital-cards.chaperone.batch', $scoutGroup->id) : null" :batch-label="'Kartu Seluruh Bindamping'" :download-filename="'KARTU_BINDAMPING_' . Str::slug($chaperone->full_name)" />

    @include('digital-cards.partials.chaperone-card')
</x-digital-card.layout>

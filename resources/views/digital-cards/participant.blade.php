@php
    $theme = $participant->patrol?->isBoys() ? 'boys' : 'girls';
    $role = $participant->isLeader() ? 'leader' : ($participant->isAssistantLeader() ? 'assistant_leader' : 'member');
    $photoUrl = $participant->photo_path ? asset('storage/' . $participant->photo_path) : null;
@endphp

<x-digital-card.layout :title="'KARTU PESERTA (B2) - ' . $participant->full_name">
    {{-- Toolbar Aksi --}}
    <x-digital-card.toolbar :subtitle="'Peserta: ' . $participant->full_name" :batch-url="$participant->patrol_id ? route('digital-cards.patrol.batch', $participant->patrol_id) : null" :batch-label="'Kartu Seluruh Regu'" :download-filename="'KARTU_B2_' . Str::slug($participant->full_name)" />

    @include('digital-cards.partials.participant-card')
</x-digital-card.layout>

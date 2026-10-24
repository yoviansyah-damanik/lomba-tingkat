<?php

namespace App\Livewire\Portal;

use App\Models\Participant;
use App\Models\ScoutGroup;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Halaman detail lengkap peserta (data diri, orang tua, riwayat, dan seluruh unggahan);
 * hanya dapat dibuka oleh gugus depan pemilik peserta.
 */
#[Layout('layouts.app', ['title' => 'Detail Peserta'])]
#[Title('Detail Peserta — LT III')]
class ParticipantDetail extends Component
{
    public string $participantId;

    public function mount(string $id): void
    {
        $scoutGroup = ScoutGroup::where('user_id', auth()->id())->firstOrFail();

        // Peserta milik gudep lain diperlakukan seolah tidak ada
        Participant::whereHas('patrol', fn ($q) => $q->where('scout_group_id', $scoutGroup->id))
            ->findOrFail($id);

        $this->participantId = $id;
    }

    public function render()
    {
        $participant = Participant::with('patrol.scoutGroup')->findOrFail($this->participantId);

        return view('livewire.portal.participant-detail', [
            'participant' => $participant,
            'scoutGroup' => $participant->patrol->scoutGroup,
        ]);
    }
}

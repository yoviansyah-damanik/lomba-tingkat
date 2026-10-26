<?php

namespace App\Livewire\Admin;

use App\Models\Participant;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Detail lengkap peserta untuk Administrator / Panitia / Juri
 */
#[Layout('layouts.app')]
class ParticipantDetail extends Component
{
    public string $participantId;

    /**
     * Dua rute: /participants/{id} dan /scout-groups/{scoutGroupId}/participants/{participantId}
     */
    public function mount(?string $id = null, ?string $scoutGroupId = null, ?string $participantId = null): void
    {
        $this->participantId = $participantId ?? $id;
        Participant::findOrFail($this->participantId);
    }

    public function render()
    {
        $participant = Participant::with('patrol.scoutGroup')->findOrFail($this->participantId);

        return view('livewire.admin.participant-detail', [
            'participant' => $participant,
            'scoutGroup' => $participant->patrol?->scoutGroup,
        ])->title('Detail Peserta — '.$participant->full_name);
    }
}

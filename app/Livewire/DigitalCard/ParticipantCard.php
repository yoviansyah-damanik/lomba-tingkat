<?php

namespace App\Livewire\DigitalCard;

use App\Models\Participant;
use App\Services\DigitalCardService;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * ID Card digital individual peserta (ukuran resmi B2)
 */
#[Layout('layouts.digital-card')]
class ParticipantCard extends Component
{
    public string $participantId;

    public function mount(string $id, DigitalCardService $cards): void
    {
        $participant = Participant::with('patrol.scoutGroup')->findOrFail($id);

        // Admin, Panitia, Dewan Juri, atau Gudep pemilik kontingen (setelah diverifikasi)
        $cards->authorizeScoutGroup(auth()->user(), $participant->patrol?->scoutGroup, 'Anda tidak berhak mengakses ID Card peserta dari pangkalan lain.');

        $this->participantId = $participant->id;
    }

    public function render(DigitalCardService $cards)
    {
        $participant = Participant::with('patrol.scoutGroup')->findOrFail($this->participantId);

        return view('livewire.digital-card.participant-card', [
            'participant' => $participant,
            'scoutGroup' => $participant->patrol?->scoutGroup,
            'qrDataUri' => $cards->qr($participant->qr_token),
        ])->title('KARTU PESERTA (B2) - '.$participant->full_name);
    }
}

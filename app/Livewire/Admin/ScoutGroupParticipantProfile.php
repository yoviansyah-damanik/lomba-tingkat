<?php

namespace App\Livewire\Admin;

use App\Models\Participant;
use App\Models\ScoutGroup;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Halaman informasi lengkap satu peserta dari direktori pangkalan (hanya baca, terpisah dari verifikasi)
 */
#[Layout('layouts.app')]
#[Title('Informasi Peserta — LT III')]
class ScoutGroupParticipantProfile extends Component
{
    public string $scoutGroupId;

    public string $participantId;

    public function mount(string $id, string $participantId): void
    {
        $participant = Participant::whereHas('patrol', fn ($q) => $q->where('scout_group_id', $id))->findOrFail($participantId);

        $this->scoutGroupId = $id;
        $this->participantId = $participant->id;
    }

    public function render()
    {
        return view('livewire.admin.scout-group-participant-profile', [
            'participant' => Participant::with('patrol')->findOrFail($this->participantId),
            'scoutGroup' => ScoutGroup::findOrFail($this->scoutGroupId),
        ]);
    }
}

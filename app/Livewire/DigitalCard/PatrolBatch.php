<?php

namespace App\Livewire\DigitalCard;

use App\Models\Patrol;
use App\Services\DigitalCardService;

/**
 * Seluruh ID Card satu regu; rute admin membawa scoutGroupId sehingga hanya patrolId yang dipakai
 */
class PatrolBatch extends BatchCards
{
    public string $patrolId;

    public function mount(string $patrolId, DigitalCardService $cards, ?string $scoutGroupId = null): void
    {
        $patrol = Patrol::with('scoutGroup')->findOrFail($patrolId);
        $this->authorizeAndLoad($cards, $patrol->scoutGroup);
        $this->patrolId = $patrol->id;
    }

    protected function build(DigitalCardService $cards): array
    {
        $patrol = Patrol::with(['scoutGroup', 'participants'])->findOrFail($this->patrolId);
        $this->scoutGroup = $patrol->scoutGroup;

        return [
            'title' => 'ID Card Regu '.$patrol->patrol_name,
            'subtitle' => $this->scoutGroup->school_name,
            'zipName' => 'KARTU_REGU_'.$this->slug($this->scoutGroup->school_name, $patrol->patrol_name),
            'emptyMessage' => 'Belum ada anggota terdaftar dalam regu ini.',
            'cards' => $patrol->orderedParticipants()->map(fn ($p) => $cards->participantItem($p, $patrol))->values(),
        ];
    }
}

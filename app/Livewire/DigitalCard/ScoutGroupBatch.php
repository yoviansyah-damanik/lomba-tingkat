<?php

namespace App\Livewire\DigitalCard;

use App\Models\ScoutGroup;
use App\Services\DigitalCardService;

/**
 * Seluruh ID Card kontingen (semua regu dan bindamping); ZIP dipisah subfolder per regu
 */
class ScoutGroupBatch extends BatchCards
{
    public function mount(string $scoutGroupId, DigitalCardService $cards): void
    {
        $this->authorizeAndLoad($cards, ScoutGroup::findOrFail($scoutGroupId));
    }

    protected function build(DigitalCardService $cards): array
    {
        $scoutGroup = ScoutGroup::with(['patrols.participants', 'chaperones'])->findOrFail($this->scoutGroupId);
        $this->scoutGroup = $scoutGroup;

        $items = collect();

        foreach ($scoutGroup->patrols as $patrol) {
            foreach ($patrol->orderedParticipants() as $participant) {
                $items->push($cards->participantItem($participant, $patrol));
            }
        }

        foreach ($scoutGroup->chaperones as $chaperone) {
            $items->push($cards->chaperoneItem($chaperone, $scoutGroup));
        }

        return [
            'title' => 'ID Card Kontingen',
            'subtitle' => $scoutGroup->school_name.' • '.$scoutGroup->patrols->count().' regu, '.$scoutGroup->chaperones->count().' pembina',
            'zipName' => 'KARTU_KONTINGEN_'.$this->slug($scoutGroup->school_name),
            'emptyMessage' => 'Belum ada kartu anggota atau pembina yang terdaftar dalam kontingen ini.',
            'cards' => $items,
        ];
    }
}

<?php

namespace App\Livewire\DigitalCard;

use App\Models\ScoutGroup;
use App\Services\DigitalCardService;

/**
 * Seluruh ID Card pembina pendamping satu kontingen
 */
class ChaperoneBatch extends BatchCards
{
    public function mount(string $scoutGroupId, DigitalCardService $cards): void
    {
        $this->authorizeAndLoad($cards, ScoutGroup::findOrFail($scoutGroupId));
    }

    protected function build(DigitalCardService $cards): array
    {
        $scoutGroup = ScoutGroup::with('chaperones')->findOrFail($this->scoutGroupId);
        $this->scoutGroup = $scoutGroup;

        return [
            'title' => 'ID Card Pembina Pendamping',
            'subtitle' => $scoutGroup->school_name,
            'zipName' => 'KARTU_BINDAMPING_'.$this->slug($scoutGroup->school_name),
            'emptyMessage' => 'Belum ada pembina pendamping terdaftar.',
            'cards' => $scoutGroup->chaperones->map(fn ($c) => $cards->chaperoneItem($c, $scoutGroup))->values(),
        ];
    }
}

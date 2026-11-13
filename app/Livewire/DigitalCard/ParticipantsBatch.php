<?php

namespace App\Livewire\DigitalCard;

use App\Enums\RegistrationStatusEnum;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use App\Services\DigitalCardService;
use Livewire\Attributes\Url;

/**
 * Cetak batch A4 seluruh ID Card peserta terverifikasi (seluruh kontingen atau per gudep)
 */
class ParticipantsBatch extends BatchCards
{
    #[Url]
    public string $gudep = '';

    #[Url]
    public string $gender = '';

    public function mount(DigitalCardService $cards): void
    {
        $cards->authorizeOfficial(auth()->user(), 'Anda tidak berhak mengakses cetak kumulatif ID Card seluruh peserta.');
    }

    protected function build(DigitalCardService $cards): array
    {
        $resolvedGender = match ($this->gender) {
            'male', 'boys' => 'boys',
            'female', 'girls' => 'girls',
            default => '',
        };

        $patrols = Patrol::with(['scoutGroup', 'participants'])
            ->whereHas('scoutGroup', fn ($q) => $q->where('verification_status', RegistrationStatusEnum::Verified))
            ->when($this->gudep !== '' && $this->gudep !== 'all', fn ($q) => $q->where('scout_group_id', $this->gudep))
            ->when($resolvedGender !== '', fn ($q) => $q->where('gender_category', $resolvedGender))
            ->get()
            ->sortBy(fn (Patrol $p) => [
                $p->scoutGroup?->school_name ?? '',
                $p->isBoys() ? 0 : 1,
                $p->patrol_name,
            ])
            ->values();

        $items = collect();

        foreach ($patrols as $patrol) {
            foreach ($patrol->orderedParticipants() as $participant) {
                $item = $cards->participantItem($participant, $patrol, $patrol->scoutGroup);
                $item['group'] = ($patrol->scoutGroup?->school_name ?? 'Gudep').' • '.$item['group'];
                $items->push($item);
            }
        }

        $schoolLabel = $this->gudep !== ''
            ? (ScoutGroup::find($this->gudep)?->school_name ?? 'Pangkalan Terpilih')
            : 'Seluruh Kontingen Terverifikasi';

        return [
            'title' => 'ID Card Seluruh Peserta (Susunan A4)',
            'subtitle' => $schoolLabel.' • '.$patrols->count().' regu',
            'zipName' => 'KARTU_SELURUH_PESERTA_A4',
            'emptyMessage' => 'Belum ada peserta terverifikasi yang dapat dicetak.',
            'cards' => $items,
        ];
    }
}

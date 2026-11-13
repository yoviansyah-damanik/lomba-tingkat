<?php

namespace App\Livewire\DigitalCard;

use App\Enums\RegistrationStatusEnum;
use App\Models\Chaperone;
use App\Models\ScoutGroup;
use App\Services\DigitalCardService;
use Livewire\Attributes\Url;

/**
 * Cetak batch A4 seluruh ID Card pembina pendamping (bindamping) terverifikasi
 */
class ChaperonesBatch extends BatchCards
{
    #[Url]
    public string $gudep = '';

    #[Url]
    public string $gender = '';

    public function mount(DigitalCardService $cards): void
    {
        $cards->authorizeOfficial(auth()->user(), 'Anda tidak berhak mengakses cetak kumulatif ID Card seluruh pembina pendamping.');
    }

    protected function build(DigitalCardService $cards): array
    {
        $chaperones = Chaperone::with('scoutGroup')
            ->whereHas('scoutGroup', fn ($q) => $q->where('verification_status', RegistrationStatusEnum::Verified))
            ->when($this->gudep !== '', fn ($q) => $q->where('scout_group_id', $this->gudep))
            ->when($this->gender !== '', fn ($q) => $q->where('gender', $this->gender))
            ->get()
            ->sortBy(fn (Chaperone $c) => [
                $c->scoutGroup?->school_name ?? '',
                $c->gender === 'male' ? 0 : 1,
                $c->full_name,
            ])
            ->values();

        $items = $chaperones->map(function (Chaperone $chaperone) use ($cards) {
            $item = $cards->chaperoneItem($chaperone, $chaperone->scoutGroup);
            $item['group'] = ($chaperone->scoutGroup?->school_name ?? 'Gudep').' • Pembina Pendamping';

            return $item;
        })->values();

        $schoolLabel = $this->gudep !== ''
            ? (ScoutGroup::find($this->gudep)?->school_name ?? 'Pangkalan Terpilih')
            : 'Seluruh Kontingen Terverifikasi';

        return [
            'title' => 'ID Card Seluruh Pembina Pendamping (Susunan A4)',
            'subtitle' => $schoolLabel,
            'zipName' => 'KARTU_SELURUH_BINDAMPING_A4',
            'emptyMessage' => 'Belum ada pembina pendamping terverifikasi yang dapat dicetak.',
            'cards' => $items,
        ];
    }
}


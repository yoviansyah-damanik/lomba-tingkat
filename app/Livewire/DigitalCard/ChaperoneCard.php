<?php

namespace App\Livewire\DigitalCard;

use App\Models\Chaperone;
use App\Services\DigitalCardService;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * ID Card digital pembina pendamping (ukuran resmi B2)
 */
#[Layout('layouts.digital-card')]
class ChaperoneCard extends Component
{
    public string $chaperoneId;

    public function mount(string $id, DigitalCardService $cards): void
    {
        $chaperone = Chaperone::with('scoutGroup')->findOrFail($id);

        $cards->authorizeScoutGroup(auth()->user(), $chaperone->scoutGroup, 'Anda tidak berhak mengakses ID Card pendamping dari pangkalan lain.');

        $this->chaperoneId = $chaperone->id;
    }

    public function render(DigitalCardService $cards)
    {
        $chaperone = Chaperone::with('scoutGroup')->findOrFail($this->chaperoneId);

        return view('livewire.digital-card.chaperone-card', [
            'chaperone' => $chaperone,
            'scoutGroup' => $chaperone->scoutGroup,
            'qrDataUri' => $cards->qr($chaperone->qr_token),
        ])->title('KARTU PEMBINA PENDAMPING (B2) - '.$chaperone->full_name)
            ->layoutData(['bodyClass' => $chaperone->isFemale() ? 'bg-[#1e0938]' : 'bg-[#064e3b]']);
    }
}

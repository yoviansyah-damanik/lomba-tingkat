<?php

namespace App\Livewire\Portal;

use App\Models\Chaperone;
use App\Models\ScoutGroup;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Halaman detail lengkap pembina pendamping (data diri, riwayat, dan seluruh unggahan);
 * hanya dapat dibuka oleh gugus depan pemilik bindamping.
 */
#[Layout('layouts.app', ['title' => 'Detail Pembina Pendamping'])]
#[Title('Detail Pembina Pendamping — LT III')]
class ChaperoneDetail extends Component
{
    public string $chaperoneId;

    public function mount(string $id): void
    {
        $scoutGroup = ScoutGroup::where('user_id', auth()->id())->firstOrFail();

        // Bindamping milik gudep lain diperlakukan seolah tidak ada
        Chaperone::where('scout_group_id', $scoutGroup->id)->findOrFail($id);

        $this->chaperoneId = $id;
    }

    public function render()
    {
        $chaperone = Chaperone::with('scoutGroup')->findOrFail($this->chaperoneId);

        return view('livewire.portal.chaperone-detail', [
            'chaperone' => $chaperone,
            'scoutGroup' => $chaperone->scoutGroup,
        ]);
    }
}

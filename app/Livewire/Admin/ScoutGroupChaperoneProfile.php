<?php

namespace App\Livewire\Admin;

use App\Models\Chaperone;
use App\Models\ScoutGroup;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Halaman informasi lengkap satu pembina pendamping dari direktori pangkalan (hanya baca, terpisah dari verifikasi)
 */
#[Layout('layouts.app')]
#[Title('Informasi Pembina Pendamping — LT III')]
class ScoutGroupChaperoneProfile extends Component
{
    public string $scoutGroupId;

    public string $chaperoneId;

    public function mount(string $id, string $chaperoneId): void
    {
        $chaperone = Chaperone::where('scout_group_id', $id)->findOrFail($chaperoneId);

        $this->scoutGroupId = $id;
        $this->chaperoneId = $chaperone->id;
    }

    public function render()
    {
        return view('livewire.admin.scout-group-chaperone-profile', [
            'chaperone' => Chaperone::findOrFail($this->chaperoneId),
            'scoutGroup' => ScoutGroup::findOrFail($this->scoutGroupId),
        ]);
    }
}

<?php

namespace App\Livewire\Admin;

use App\Models\Chaperone;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Detail lengkap pembina pendamping untuk Administrator / Panitia / Juri
 */
#[Layout('layouts.app')]
class ChaperoneDetail extends Component
{
    public string $chaperoneId;

    /**
     * Dua rute: /chaperones/{id} dan /scout-groups/{scoutGroupId}/chaperones/{chaperoneId}
     */
    public function mount(?string $id = null, ?string $scoutGroupId = null, ?string $chaperoneId = null): void
    {
        $this->chaperoneId = $chaperoneId ?? $id;
        Chaperone::findOrFail($this->chaperoneId);
    }

    public function render()
    {
        $chaperone = Chaperone::with('scoutGroup')->findOrFail($this->chaperoneId);

        return view('livewire.admin.chaperone-detail', [
            'chaperone' => $chaperone,
            'scoutGroup' => $chaperone->scoutGroup,
        ])->title('Detail Pembina Pendamping — '.$chaperone->full_name);
    }
}

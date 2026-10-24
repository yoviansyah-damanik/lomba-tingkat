<?php

namespace App\Livewire\Admin;

use App\Models\ScoutGroup;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Halaman informasi lengkap satu pangkalan gugus depan (hanya baca, terpisah dari halaman verifikasi)
 */
#[Layout('layouts.app')]
#[Title('Informasi Gugusdepan — LT III')]
class ScoutGroupProfile extends Component
{
    public string $scoutGroupId;

    public function mount(string $id): void
    {
        $this->scoutGroupId = ScoutGroup::findOrFail($id)->id;
    }

    #[Computed]
    public function group(): ScoutGroup
    {
        return ScoutGroup::with(['patrols.participants', 'chaperones', 'user'])->findOrFail($this->scoutGroupId);
    }

    public function render()
    {
        return view('livewire.admin.scout-group-profile', ['g' => $this->group]);
    }
}

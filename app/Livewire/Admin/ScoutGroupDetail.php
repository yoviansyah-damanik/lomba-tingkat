<?php

namespace App\Livewire\Admin;

use App\Models\ScoutGroup;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Component;

/**
 * Isi modal informasi lengkap satu pangkalan gugus depan (data pangkalan, regu, peserta, bindamping)
 */
#[Lazy]
class ScoutGroupDetail extends Component
{
    public string $scoutGroupId;

    public function placeholder(): string
    {
        return '<div class="space-y-3 p-6"><div class="h-16 animate-pulse rounded-2xl bg-zinc-100 dark:bg-zinc-800"></div><div class="h-40 animate-pulse rounded-2xl bg-zinc-100 dark:bg-zinc-800"></div></div>';
    }

    #[Computed]
    public function group(): ?ScoutGroup
    {
        return ScoutGroup::with(['patrols.participants', 'chaperones', 'user'])->find($this->scoutGroupId);
    }

    public function render()
    {
        return view('livewire.admin.scout-group-detail');
    }
}

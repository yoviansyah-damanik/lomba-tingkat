<?php

namespace App\Livewire\Admin;

use App\Models\Patrol;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Daftar NPP regu beserta identitas aslinya (hanya Administrator), dipisah per kategori putra dan putri
 */
#[Lazy]
class NppList extends Component
{
    public string $gender = 'boys';

    public string $search = '';

    public function placeholder(): string
    {
        return '<div class="h-64 animate-pulse rounded-2xl border border-zinc-200 bg-zinc-100 dark:border-zinc-800 dark:bg-zinc-800/60"></div>';
    }

    #[On('npp-updated')]
    public function refreshList(): void {}

    public function render()
    {
        $patrols = Patrol::with(['scoutGroup', 'chaperone'])
            ->where('gender_category', $this->gender)
            ->whereNotNull('npp')
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';
                $query->where(fn ($q) => $q->where('npp', 'like', $term)
                    ->orWhere('patrol_name', 'like', $term)
                    ->orWhereHas('scoutGroup', fn ($g) => $g->where('school_name', 'like', $term)));
            })
            ->orderBy('npp')
            ->get();

        return view('livewire.admin.npp-list', ['patrols' => $patrols]);
    }
}

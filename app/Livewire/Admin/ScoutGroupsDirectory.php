<?php

namespace App\Livewire\Admin;

use App\Enums\RegistrationStatusEnum;
use App\Models\ScoutGroup;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Direktori pangkalan gugus depan terverifikasi dengan informasi lengkap dalam modal
 */
#[Layout('layouts.app')]
#[Title('Pangkalan Gugusdepan — LT III')]
class ScoutGroupsDirectory extends Component
{
    use WithPagination;

    public string $search = '';
    public string $kwarranFilter = 'all';
    public int $perPage = 15;

    public ?string $selectedGroupId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'kwarranFilter' => ['except' => 'all'],
        'perPage' => ['except' => 15],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedKwarranFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function openDetail(string $groupId): void
    {
        $this->selectedGroupId = $groupId;
    }

    public function closeDetail(): void
    {
        $this->selectedGroupId = null;
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'kwarranFilter']);
        $this->resetPage();
    }

    /**
     * Pangkalan terverifikasi: kueri dasar seluruh data direktori
     */
    private function verifiedQuery()
    {
        return ScoutGroup::query()->where('verification_status', RegistrationStatusEnum::Verified);
    }

    #[Computed]
    public function stats(): array
    {
        $groups = $this->verifiedQuery()->withCount(['patrols', 'chaperones', 'participants'])->get();

        return [
            'groups' => $groups->count(),
            'patrols' => $groups->sum('patrols_count'),
            'participants' => $groups->sum('participants_count'),
            'chaperones' => $groups->sum('chaperones_count'),
        ];
    }

    #[Computed]
    public function kwarrans()
    {
        return $this->verifiedQuery()->orderBy('kwarran_name')->distinct()->pluck('kwarran_name');
    }

    public function render()
    {
        $query = $this->verifiedQuery()->withCount(['patrols', 'chaperones', 'participants']);

        if ($this->kwarranFilter !== 'all') {
            $query->where('kwarran_name', $this->kwarranFilter);
        }

        if (filled(trim($this->search))) {
            $term = '%'.trim($this->search).'%';
            $query->where(function ($q) use ($term) {
                $q->where('school_name', 'like', $term)
                    ->orWhere('group_number', 'like', $term)
                    ->orWhere('registration_code', 'like', $term)
                    ->orWhere('kwarran_name', 'like', $term)
                    ->orWhere('contact_person', 'like', $term);
            });
        }

        return view('livewire.admin.scout-groups-directory', [
            'groups' => $query->orderBy('school_name')->paginate($this->perPage),
        ]);
    }
}

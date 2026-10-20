<?php

namespace App\Livewire\Public;

use App\Models\Chaperone;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Komponen Livewire 4 Publik: Direktori Peserta & Gugus Depan Terdaftar
 */
#[Layout('layouts.public', ['title' => 'Direktori Peserta & Gugus Depan'])]
#[Title('Peserta & Kontingen Gugus Depan — LT III 2026')]
class ParticipantsPage extends Component
{
    #[Url(as: 'pangkalan')]
    public ?string $selectedScoutGroupId = null;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'kwarran')]
    public string $kwarranFilter = 'all';

    public bool $showDetailModal = false;

    public function mount(): void
    {
        if ($this->selectedScoutGroupId) {
            $this->showDetailModal = true;
        }
    }

    public function selectScoutGroup(string $id): void
    {
        $this->selectedScoutGroupId = $id;
        $this->showDetailModal = true;
    }

    public function clearSelection(): void
    {
        $this->selectedScoutGroupId = null;
        $this->showDetailModal = false;
    }

    public function updatedShowDetailModal(bool $value): void
    {
        if (! $value) {
            $this->selectedScoutGroupId = null;
        }
    }

    #[Computed]
    public function scoutGroups(): Collection
    {
        return ScoutGroup::query()
            ->where('verification_status', \App\Enums\RegistrationStatusEnum::Verified)
            ->with([
                'patrols.participants',
                'chaperones',
            ])
            ->when($this->search, function (Builder $query) {
                $term = '%' . trim($this->search) . '%';
                $query->where(function (Builder $sub) use ($term) {
                    $sub->where('school_name', 'like', $term)
                        ->orWhere('group_number', 'like', $term)
                        ->orWhere('male_group_number', 'like', $term)
                        ->orWhere('female_group_number', 'like', $term)
                        ->orWhere('kwarran_name', 'like', $term)
                        ->orWhereHas('patrols', function (Builder $p) use ($term) {
                            $p->where('patrol_name', 'like', $term)
                                ->orWhereHas('participants', function (Builder $part) use ($term) {
                                    $part->where('full_name', 'like', $term);
                                });
                        })
                        ->orWhereHas('chaperones', function (Builder $c) use ($term) {
                            $c->where('full_name', 'like', $term);
                        });
                });
            })
            ->when($this->kwarranFilter !== 'all', function (Builder $query) {
                $query->where('kwarran_name', $this->kwarranFilter);
            })
            ->orderBy('school_name')
            ->get();
    }

    #[Computed]
    public function selectedScoutGroup(): ?ScoutGroup
    {
        if (! $this->selectedScoutGroupId) {
            return null;
        }

        return ScoutGroup::where('verification_status', \App\Enums\RegistrationStatusEnum::Verified)
            ->with([
                'patrols.participants',
                'boysPatrol.participants',
                'girlsPatrol.participants',
                'chaperones',
            ])->find($this->selectedScoutGroupId);
    }

    #[Computed]
    public function kwarrans(): Collection
    {
        return ScoutGroup::query()
            ->where('verification_status', \App\Enums\RegistrationStatusEnum::Verified)
            ->whereNotNull('kwarran_name')
            ->where('kwarran_name', '!=', '')
            ->distinct()
            ->pluck('kwarran_name')
            ->sort()
            ->values();
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'total_groups' => ScoutGroup::where('verification_status', \App\Enums\RegistrationStatusEnum::Verified)->count(),
            'total_patrols' => Patrol::whereHas('scoutGroup', fn($q) => $q->where('verification_status', \App\Enums\RegistrationStatusEnum::Verified))->count(),
            'total_participants' => Participant::whereHas('patrol.scoutGroup', fn($q) => $q->where('verification_status', \App\Enums\RegistrationStatusEnum::Verified))->count(),
            'total_chaperones' => Chaperone::whereHas('scoutGroup', fn($q) => $q->where('verification_status', \App\Enums\RegistrationStatusEnum::Verified))->count(),
        ];
    }

    public function render()
    {
        return view('livewire.public.participants-page', [
            'groups' => $this->scoutGroups,
            'selectedGroup' => $this->selectedScoutGroup,
            'kwarranList' => $this->kwarrans,
            'statistics' => $this->stats,
        ]);
    }
}

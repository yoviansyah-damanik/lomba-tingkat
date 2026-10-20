<?php

namespace App\Livewire\Public;

use App\Models\Competition;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Komponen Livewire 4 Katalog Mata Lomba & Juknis Publik
 */
#[Layout('layouts.public', ['title' => 'Mata Lomba & Juknis Kegiatan'])]
#[Title('Mata Lomba & Juknis — LT III')]
class CompetitionsCatalog extends Component
{
    public string $search = '';
    public string $categoryFilter = 'all';
    public string $formatFilter = 'all';
    public ?string $selectedCompetitionId = null;
    public bool $showDetailsModal = false;

    // Buka detail juknis lomba dalam drawer/modal interaktif
    public function openDetails(string $competitionId): void
    {
        $this->selectedCompetitionId = $competitionId;
        $this->showDetailsModal = true;
    }

    public function closeDetails(): void
    {
        $this->showDetailsModal = false;
        $this->selectedCompetitionId = null;
    }

    #[Computed]
    public function selectedCompetition(): ?Competition
    {
        if (! $this->selectedCompetitionId) {
            return null;
        }

        return Competition::with(['schedules', 'criteria'])->find($this->selectedCompetitionId);
    }

    #[Computed]
    public function competitions()
    {
        $query = Competition::published()->with(['schedules', 'criteria']);

        if ($this->search !== '') {
            $term = '%'.$this->search.'%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)
                    ->orWhere('code', 'like', $term)
                    ->orWhere('description', 'like', $term);
            });
        }

        if ($this->categoryFilter !== 'all') {
            $query->where('category', $this->categoryFilter);
        }

        if ($this->formatFilter !== 'all') {
            $query->where('participant_type', $this->formatFilter);
        }

        return $query->orderBy('code')->get();
    }

    public function render()
    {
        return view('livewire.public.competitions-catalog');
    }
}

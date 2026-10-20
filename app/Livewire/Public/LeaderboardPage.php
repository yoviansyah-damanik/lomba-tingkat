<?php

namespace App\Livewire\Public;

use App\Models\Competition;
use App\Models\ScoutGroup;
use App\Services\StandingsService;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Komponen Livewire 4 Klasemen Resmi & Live Rank Perolehan Prestasi LT III
 */
#[Layout('layouts.public', ['title' => 'Klasemen Resmi Prestasi'])]
#[Title('Klasemen Resmi Prestasi — LT III 2026')]
class LeaderboardPage extends Component
{
    #[Url(as: 'tab')]
    public string $activeTab = 'patrols'; // patrols, competitions

    #[Url(as: 'gender')]
    public string $genderFilter = 'all'; // all, boys, girls

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'comp')]
    public ?string $selectedCompetitionId = null;

    public string $lastUpdated = '';

    public bool $isRefreshing = false;

    public function mount(): void
    {
        $this->lastUpdated = now()->timezone('Asia/Jakarta')->format('H:i:s');

        if (! $this->selectedCompetitionId) {
            $firstComp = Competition::published()->first();
            $this->selectedCompetitionId = $firstComp?->id;
        }
    }

    #[On('echo:standings,StandingsUpdated')]
    #[On('standings-updated')]
    public function handleStandingsUpdated(?string $competitionId = null): void
    {
        $this->lastUpdated = now()->timezone('Asia/Jakarta')->format('H:i:s');
        Flux::toast(variant: 'info', heading: 'Klasemen Diperbarui', text: 'Data klasemen baru saja diperbarui oleh sistem!');
    }

    public function refreshStandings(): void
    {
        $this->isRefreshing = true;
        $this->lastUpdated = now()->timezone('Asia/Jakarta')->format('H:i:s');
        $this->isRefreshing = false;
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['patrols', 'competitions'])) {
            $this->activeTab = $tab;
        }
    }

    public function setGender(string $gender): void
    {
        if (in_array($gender, ['all', 'boys', 'girls'])) {
            $this->genderFilter = $gender;
        }
    }

    public bool $unmaskIdentities = false;

    public function toggleUnmask(): void
    {
        // Hanya admin yang bisa mengintip identitas asli
        if (auth()->check() && auth()->user()->isAdmin()) {
            $this->unmaskIdentities = ! $this->unmaskIdentities;
        }
    }

    #[Computed]
    public function canUnmask(): bool
    {
        return auth()->check() && auth()->user()->isAdmin() && $this->unmaskIdentities;
    }

    #[Computed]
    public function isEventMasked(): bool
    {
        return (bool) (\App\Models\Event::first()?->hide_patrol_identities ?? false);
    }

    #[Computed]
    public function summary(): array
    {
        $standingsService = app(StandingsService::class);
        return $standingsService->getStandingsSummary($this->genderFilter, $this->canUnmask);
    }

    #[Computed]
    public function patrolStandings(): Collection
    {
        $standingsService = app(StandingsService::class);
        return $standingsService->getPatrolStandings($this->genderFilter, $this->search, $this->canUnmask);
    }

    #[Computed]
    public function competitions(): Collection
    {
        return Competition::published()->withCount('criteria', 'scores')->get();
    }

    #[Computed]
    public function selectedCompetition(): ?Competition
    {
        if (! $this->selectedCompetitionId) {
            return null;
        }

        return Competition::with('criteria')->find($this->selectedCompetitionId);
    }

    #[Computed]
    public function competitionRecap(): Collection
    {
        if (! $this->selectedCompetitionId) {
            return collect();
        }

        $standingsService = app(StandingsService::class);
        return $standingsService->getCompetitionRecap($this->selectedCompetitionId, $this->canUnmask);
    }

    public function render()
    {
        return view('livewire.public.leaderboard-page', [
            'summary' => $this->summary,
            'patrols' => $this->patrolStandings,
            'competitions' => $this->competitions,
            'selectedComp' => $this->selectedCompetition,
            'recap' => $this->competitionRecap,
            'isEventMasked' => $this->isEventMasked,
            'canUnmask' => $this->canUnmask,
            'isAdmin' => auth()->check() && auth()->user()->isAdmin(),
        ]);
    }
}

<?php

namespace App\Livewire\Admin;

use App\Models\AssessmentCriterion;
use App\Models\Competition;
use App\Models\CompetitionScore;
use App\Models\Patrol;
use App\Services\StandingsService;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Komponen Livewire 4 Rekap Nilai & Klasemen Khusus Dasbor
 */
#[Layout('layouts.app')]
#[Title('Rekap Nilai & Klasemen')]
class StandingsLeaderboard extends Component
{
    #[Url(as: 'tab')]
    public string $activeTab = 'patrols'; // patrols, competitions

    #[Url(as: 'gender')]
    public string $genderTab = 'boys'; // boys, girls

    public string $search = '';

    #[Url(as: 'comp')]
    public ?string $selectedCompetitionId = null;

    // Modal Input / Edit Nilai (Admin & Juri)
    public bool $showScoreModal = false;
    public ?string $editingScoreId = null;
    public ?string $modalPatrolId = null;
    public array $modalCriteriaScores = [];
    public ?float $modalFinalScore = null;
    public string $modalNotes = '';

    public function mount(StandingsService $standingsService): void
    {
        if (! $this->selectedCompetitionId) {
            $firstComp = Competition::published()->first();
            $this->selectedCompetitionId = $firstComp?->id;
        }
    }

    #[Computed]
    public function canManageScores(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        // Administrator memiliki hak untuk memantau, mengunci, dan mengkalkulasi ulang peringkat
        if ($user->isAdmin()) {
            return true;
        }

        // Panitia sama sekali TIDAK memiliki hak kelola penilaian di lomba manapun
        if ($user->isCommittee() && ! $user->isJudge()) {
            return false;
        }

        // Dewan juri memiliki hak kelola penilaian
        if ($user->isJudge()) {
            return true;
        }

        return false;
    }

    #[Computed]
    public function canInputScores(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        // Administrator yang tidak merangkap Juri dilarang melakukan penilaian lomba
        if ($user->isAdminOnly()) {
            return false;
        }

        // Panitia dilarang melakukan penilaian
        if ($user->isCommittee() && ! $user->isJudge()) {
            return false;
        }

        // Hanya dewan juri yang dapat melakukan input penilaian
        return $user->isJudge();
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

    public bool $unmaskIdentities = false;

    public function toggleUnmask(): void
    {
        // Hanya administrator yang dapat mengintip identitas asli
        if (auth()->check() && auth()->user()->isAdmin()) {
            $this->unmaskIdentities = ! $this->unmaskIdentities;
        }
    }

    #[Computed]
    public function canUnmask(): bool
    {
        $user = auth()->user();
        if (! $user || ! $user->isAdmin()) {
            return false;
        }

        return $this->unmaskIdentities;
    }

    #[Computed]
    public function isEventMasked(): bool
    {
        return (bool) (\App\Models\Event::first()?->hide_patrol_identities ?? false);
    }

    #[Computed]
    public function patrolStandings(): Collection
    {
        return app(StandingsService::class)->getPatrolStandings(
            'all',
            $this->search,
            $this->canUnmask
        );
    }

    /**
     * Batasi tab kategori hanya pada putra atau putri
     */
    public function updatedGenderTab(string $value): void
    {
        if (! in_array($value, ['boys', 'girls'], true)) {
            $this->genderTab = 'boys';
        }
    }

    /**
     * Seluruh rekap nilai cabang terpilih (putra dan putri)
     */
    #[Computed]
    public function competitionRecapAll(): Collection
    {
        if (! $this->selectedCompetitionId) {
            return collect();
        }

        return app(StandingsService::class)->getCompetitionRecap(
            $this->selectedCompetitionId,
            $this->canUnmask
        );
    }

    /**
     * Rekap nilai cabang terpilih sesuai tab kategori aktif
     */
    #[Computed]
    public function competitionRecap(): Collection
    {
        return $this->competitionRecapAll
            ->filter(fn ($score) => $this->genderOf($score->patrol?->gender_category) === $this->genderTab)
            ->values();
    }

    /**
     * Jumlah regu per kategori untuk lencana tab (klasemen regu atau rekap nilai)
     *
     * @return array{boys: int, girls: int}
     */
    #[Computed]
    public function categoryCounts(): array
    {
        if ($this->activeTab === 'competitions') {
            $grouped = $this->competitionRecapAll->groupBy(fn ($score) => $this->genderOf($score->patrol?->gender_category));
        } else {
            $grouped = $this->patrolStandings->groupBy('gender_category');
        }

        return ['boys' => $grouped->get('boys', collect())->count(), 'girls' => $grouped->get('girls', collect())->count()];
    }

    protected function genderOf(mixed $gender): string
    {
        return $gender instanceof \App\Enums\PatrolGenderEnum ? $gender->value : (string) $gender;
    }

    #[Computed]
    public function eligiblePatrolsForScoring(): Collection
    {
        $comp = $this->selectedCompetition;
        if (! $comp) {
            return collect();
        }

        $query = Patrol::with('scoutGroup')->where('status', \App\Enums\RegistrationStatusEnum::Verified);

        if ($comp->category === 'boys') {
            $query->where('gender_category', 'boys');
        } elseif ($comp->category === 'girls') {
            $query->where('gender_category', 'girls');
        }

        // Hanya regu pada tab kategori yang sedang dibuka
        $query->where('gender_category', $this->genderTab);

        return $query->orderBy('patrol_name')->get();
    }

    /**
     * Membuka modal input nilai untuk regu tertentu
     */
    public function openScoreModal(?string $patrolId = null, ?string $scoreId = null): void
    {
        if (auth()->user()?->isAdminOnly()) {
            Flux::toast(variant: 'danger', heading: 'Akses Ditolak', text: 'Administrator tidak diperkenankan melakukan penilaian lomba. Penilaian hanya dapat dilakukan oleh Juri yang telah ditunjuk.');
            return;
        }

        if (! $this->canInputScores) {
            Flux::toast(variant: 'danger', heading: 'Akses Ditolak', text: 'Panitia tidak memiliki hak akses untuk melakukan penilaian lomba.');
            return;
        }

        $this->editingScoreId = $scoreId;
        $this->modalPatrolId = $patrolId;
        $this->modalCriteriaScores = [];
        $this->modalFinalScore = null;
        $this->modalNotes = '';

        if ($scoreId) {
            $score = CompetitionScore::with('competition.criteria')->find($scoreId);
            if ($score) {
                $this->modalPatrolId = $score->patrol_id;
                $this->modalFinalScore = (float) $score->final_score;
                $this->modalNotes = $score->notes ?? '';
                $this->modalCriteriaScores = $score->criteria_scores ?? [];
            }
        } elseif ($patrolId && $this->selectedCompetitionId) {
            $existing = CompetitionScore::where('competition_id', $this->selectedCompetitionId)
                ->where('patrol_id', $patrolId)
                ->first();

            if ($existing) {
                $this->editingScoreId = $existing->id;
                $this->modalFinalScore = (float) $existing->final_score;
                $this->modalNotes = $existing->notes ?? '';
                $this->modalCriteriaScores = $existing->criteria_scores ?? [];
            } else {
                $criteria = AssessmentCriterion::where('competition_id', $this->selectedCompetitionId)->get();
                foreach ($criteria as $c) {
                    $this->modalCriteriaScores[$c->id] = 80;
                }
            }
        }

        $this->showScoreModal = true;
    }

    /**
     * Menutup modal penilaian
     */
    public function closeScoreModal(): void
    {
        $this->showScoreModal = false;
        $this->editingScoreId = null;
        $this->modalPatrolId = null;
        $this->modalCriteriaScores = [];
        $this->modalFinalScore = null;
        $this->modalNotes = '';
    }

    /**
     * Menyimpan nilai lomba
     */
    public function saveScoreModal(): void
    {
        if (auth()->user()?->isAdminOnly()) {
            Flux::toast(variant: 'danger', heading: 'Akses Ditolak', text: 'Administrator tidak diperkenankan melakukan penilaian lomba. Penilaian hanya dapat dilakukan oleh Juri yang telah ditunjuk.');
            return;
        }

        if (! $this->canInputScores) {
            Flux::toast(variant: 'danger', heading: 'Akses Ditolak', text: 'Anda tidak memiliki hak akses untuk mengubah nilai.');
            return;
        }

        $this->validate([
            'selectedCompetitionId' => 'required|exists:competitions,id',
            'modalPatrolId' => 'required|exists:patrols,id',
            'modalFinalScore' => 'nullable|numeric|min:0|max:100',
            'modalNotes' => 'nullable|string|max:500',
        ]);

        $standingsService = app(StandingsService::class);

        $standingsService->saveScore([
            'competition_id' => $this->selectedCompetitionId,
            'patrol_id' => $this->modalPatrolId,
            'judge_id' => auth()->id(),
            'final_score' => $this->modalFinalScore ?? 0.0,
            'criteria_scores' => ! empty($this->modalCriteriaScores) ? $this->modalCriteriaScores : null,
            'notes' => $this->modalNotes,
            'is_locked' => true,
        ]);

        $this->closeScoreModal();
        Flux::toast(variant: 'success', heading: 'Nilai Disimpan', text: 'Nilai perlombaan dan peringkat klasemen berhasil diperbarui.');
    }

    /**
     * Mengunci atau membuka kunci nilai
     */
    public function toggleScoreLock(string $scoreId): void
    {
        if (! $this->canManageScores) {
            return;
        }

        $score = CompetitionScore::find($scoreId);
        if ($score) {
            $score->update(['is_locked' => ! $score->is_locked]);
            Flux::toast(variant: 'info', heading: 'Penguncian Nilai', text: 'Status penguncian nilai berhasil diubah.');
        }
    }

    /**
     * Menghitung ulang seluruh peringkat pada cabang lomba aktif
     */
    public function recalculateRanks(): void
    {
        if (! $this->canManageScores) {
            Flux::toast(variant: 'danger', heading: 'Akses Ditolak', text: 'Panitia tidak memiliki hak akses untuk mengkalkulasi ulang peringkat.');
            return;
        }

        if ($this->selectedCompetitionId) {
            app(StandingsService::class)->recalculateRanks($this->selectedCompetitionId);
            Flux::toast(variant: 'success', heading: 'Kalkulasi Selesai', text: 'Peringkat mata lomba berhasil dikalkulasi ulang.');
        }
    }

    public function render()
    {
        return view('livewire.admin.standings-leaderboard', [
            'patrols' => $this->patrolStandings,
            'recap' => $this->competitionRecap,
            'categoryCounts' => $this->categoryCounts,
            'competitions' => $this->competitions,
            'selectedComp' => $this->selectedCompetition,
            'eligiblePatrols' => $this->eligiblePatrolsForScoring,
            'canManageScores' => $this->canManageScores,
            'canInputScores' => $this->canInputScores,
            'isEventMasked' => $this->isEventMasked,
            'canUnmask' => $this->canUnmask,
            'isAdmin' => (bool) (auth()->user()?->isAdmin()),
        ]);
    }
}

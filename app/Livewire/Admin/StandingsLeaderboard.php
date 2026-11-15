<?php

namespace App\Livewire\Admin;

use App\Enums\PatrolGenderEnum;
use App\Enums\RegistrationStatusEnum;
use App\Enums\RoleEnum;
use App\Models\Assessment;
use App\Models\AssessmentCriterion;
use App\Models\Competition;
use App\Models\CompetitionScore;
use App\Models\Event;
use App\Models\Patrol;
use App\Models\User;
use App\Services\AssessmentScoringService;
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

    // Modal rincian poin regu
    public ?string $detailPatrolId = null;

    // Modal Finalisasi / Draf Per Juri (Khusus Administrator)
    public bool $isJudgeActionModalOpen = false;

    public string $judgeActionType = 'finalize'; // 'finalize' atau 'draft'

    public string $targetJudgeId = '';

    public string $targetCompetitionId = 'all';

    public string $judgeActionReason = '';

    public function mount(StandingsService $standingsService): void
    {
        if (! $this->selectedCompetitionId) {
            $firstComp = Competition::published()->first();
            $this->selectedCompetitionId = $firstComp?->id;
        }

        if (! $this->canAccessCompetitionTab && $this->activeTab === 'competitions') {
            $this->activeTab = 'patrols';
        }
    }

    public function updatedActiveTab(string $value): void
    {
        if ($value === 'competitions' && ! $this->canAccessCompetitionTab) {
            $this->activeTab = 'patrols';
        }
    }

    #[Computed]
    public function canAccessCompetitionTab(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        // Administrator dan Dewan Juri berwenang mengakses tab rekapitulasi nilai lomba
        if ($user->isAdmin() || $user->isJudge()) {
            return true;
        }

        // Pada blind mode aktif, akun gugusdepan, peserta, dan bindamping tidak diizinkan membuka daftar peringkat cabang
        if ($this->isEventMasked && ! $this->canUnmask) {
            return false;
        }

        return true;
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
        return Competition::published()
            ->withCount([
                'criteria',
                'scores',
                'assessments as total_assessments',
                'assessments as draft_assessments' => fn ($q) => $q->where('status', 'draft'),
            ])
            ->get();
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
        return (bool) (Event::first()?->hide_patrol_identities ?? false);
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

    public function openPatrolDetail(string $patrolId): void
    {
        $this->detailPatrolId = $patrolId;
    }

    public function closePatrolDetail(): void
    {
        $this->detailPatrolId = null;
    }

    /**
     * Rincian poin regu terpilih: posisi klasemen (mengikuti mode sensor) dan poin per lomba
     *
     * @return array{standing: array, rows: Collection}|null
     */
    #[Computed]
    public function patrolDetail(): ?array
    {
        if (! $this->detailPatrolId) {
            return null;
        }

        $standing = $this->patrolStandings->firstWhere('id', $this->detailPatrolId);

        if (! $standing) {
            return null;
        }

        $rows = CompetitionScore::with('competition')
            ->where('patrol_id', $this->detailPatrolId)
            ->get()
            ->map(fn (CompetitionScore $score) => [
                'competition' => $score->competition?->title ?? '-',
                'code' => $score->competition?->code,
                'final_score' => $score->final_score,
                'rank' => $score->rank,
                'predicate_label' => $score->predicateLabel(),
                'predicate_classes' => $score->predicateBadgeClasses(),
                'points' => CompetitionScore::rankWeight($score->rank, $score->predicate),
            ])
            ->sortBy('code')
            ->values();

        return ['standing' => $standing, 'rows' => $rows];
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
        if (! $this->selectedCompetitionId || ! $this->canAccessCompetitionTab) {
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
        return $gender instanceof PatrolGenderEnum ? $gender->value : (string) $gender;
    }

    #[Computed]
    public function eligiblePatrolsForScoring(): Collection
    {
        $comp = $this->selectedCompetition;
        if (! $comp) {
            return collect();
        }

        $query = Patrol::with('scoutGroup')->where('status', RegistrationStatusEnum::Verified);

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

    #[Computed]
    public function allJudges(): Collection
    {
        return User::whereHas('roles', fn ($q) => $q->where('name', RoleEnum::Judge->value))
            ->orWhereIn('id', Assessment::select('judge_id')->distinct())
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function competitionJudgesSummary(): Collection
    {
        $compId = $this->selectedCompetitionId;

        $judgeIds = Assessment::when($compId, fn ($q) => $q->where('competition_id', $compId))
            ->select('judge_id')
            ->distinct()
            ->pluck('judge_id');

        if ($judgeIds->isEmpty()) {
            return collect();
        }

        return User::whereIn('id', $judgeIds)
            ->withCount([
                'assessments as total_assessments' => fn ($q) => $compId ? $q->where('competition_id', $compId) : $q,
                'assessments as finalized_count' => fn ($q) => $compId ? $q->where('competition_id', $compId)->where('status', 'finalized') : $q->where('status', 'finalized'),
                'assessments as draft_count' => fn ($q) => $compId ? $q->where('competition_id', $compId)->where('status', 'draft') : $q->where('status', 'draft'),
            ])
            ->orderBy('name')
            ->get();
    }

    public function getTargetActionCount(): int
    {
        if (! $this->targetJudgeId) {
            return 0;
        }

        $status = $this->judgeActionType === 'finalize' ? 'draft' : 'finalized';

        $query = Assessment::where('judge_id', $this->targetJudgeId)
            ->where('status', $status);

        if ($this->targetCompetitionId && $this->targetCompetitionId !== 'all') {
            $query->where('competition_id', $this->targetCompetitionId);
        }

        return $query->count();
    }

    public function openJudgeActionModal(string $actionType = 'finalize', ?string $judgeId = null, ?string $competitionId = null): void
    {
        if (! auth()->user()?->isAdmin()) {
            Flux::toast(variant: 'danger', heading: 'Akses Ditolak', text: 'Hanya Administrator yang memiliki wewenang mengelola status finalisasi nilai.');

            return;
        }

        $this->judgeActionType = in_array($actionType, ['finalize', 'draft'], true) ? $actionType : 'finalize';
        $this->targetJudgeId = $judgeId ?? '';
        $this->targetCompetitionId = $competitionId ?? ($this->selectedCompetitionId ?? 'all');
        $this->judgeActionReason = '';
        $this->isJudgeActionModalOpen = true;
    }

    public function executeJudgeAction(AssessmentScoringService $scoringService): void
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403);
        }

        $rules = [
            'targetJudgeId' => 'required|exists:users,id',
        ];

        if ($this->judgeActionType === 'draft') {
            $rules['judgeActionReason'] = 'required|string|min:5';
        } else {
            $rules['judgeActionReason'] = 'nullable|string';
        }

        $this->validate($rules, [
            'targetJudgeId.required' => 'Pilih dewan juri terlebih dahulu.',
            'targetJudgeId.exists' => 'Juri yang dipilih tidak valid.',
            'judgeActionReason.required' => 'Alasan pembukaan kunci nilai ke draf wajib diisi.',
            'judgeActionReason.min' => 'Alasan minimal 5 karakter.',
        ]);

        $judgeUser = User::find($this->targetJudgeId);
        $judgeName = $judgeUser?->name ?? 'Dewan Juri';
        $compId = $this->targetCompetitionId !== 'all' ? $this->targetCompetitionId : null;

        if ($this->judgeActionType === 'finalize') {
            $affected = $scoringService->finalizeScoresByJudge(
                judgeId: $this->targetJudgeId,
                admin: auth()->user(),
                competitionId: $compId
            );

            if ($affected->count() > 0) {
                Flux::toast(variant: 'success', heading: 'Finalisasi Berhasil', text: "Sebanyak {$affected->count()} penilaian dari {$judgeName} berhasil difinalisasi.");
            } else {
                Flux::toast(variant: 'warning', heading: 'Tidak Ada Draf', text: "Tidak ditemukan penilaian berstatus draf dari {$judgeName} untuk difinalisasi.");
            }
        } else {
            $affected = $scoringService->unlockScoresByJudge(
                judgeId: $this->targetJudgeId,
                reason: $this->judgeActionReason,
                admin: auth()->user(),
                competitionId: $compId
            );

            if ($affected->count() > 0) {
                Flux::toast(variant: 'success', heading: 'Penilaian Dibuka', text: "Sebanyak {$affected->count()} penilaian dari {$judgeName} berhasil diubah kembali ke status Draf.");
            } else {
                Flux::toast(variant: 'warning', heading: 'Tidak Ada Data', text: "Tidak ditemukan penilaian terfinalisasi dari {$judgeName} untuk dibuka.");
            }
        }

        $this->isJudgeActionModalOpen = false;
        $this->targetJudgeId = '';
        $this->judgeActionReason = '';
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
            'canAccessCompetitionTab' => $this->canAccessCompetitionTab,
            'isAdmin' => (bool) (auth()->user()?->isAdmin()),
            'allJudges' => $this->allJudges,
            'competitionJudgesSummary' => $this->competitionJudgesSummary,
            'targetActionCount' => $this->getTargetActionCount(),
        ]);
    }
}

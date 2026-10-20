<?php

namespace App\Livewire\Portal;

use App\Models\Assessment;
use App\Models\CompetitionSchedule;
use App\Services\AssessmentScoringService;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Komponen Livewire 4 Lembar Penilaian Juri (Blind Scoring Cockpit)
 */
#[Layout('layouts.app')]
#[Title('Lembar Penilaian Juri — Blind Scoring')]
class JudgeScoring extends Component
{
    public string $scheduleId;

    public ?string $selectedPatrolId = null;

    /** @var array<string, float|int|string> */
    public array $criteriaScores = [];

    public ?string $notes = null;

    public bool $isFinalizeModalOpen = false;

    public bool $isUnlockModalOpen = false;

    public string $unlockReason = '';

    public bool $unmaskNames = false;

    public function mount(string $scheduleId, AssessmentScoringService $scoringService): void
    {
        $this->scheduleId = $scheduleId;
        $user = Auth::user();

        // Verifikasi hak akses juri atau administrator
        $schedule = $this->schedule();
        if (! $user->isAdmin()) {
            $isAssigned = $schedule->judgeAssignments()
                ->where('user_id', $user->id)
                ->exists();

            if (! $isAssigned) {
                abort(403, 'Anda tidak ditugaskan sebagai dewan juri pada sesi lomba ini.');
            }
        }

        // Pilih regu pertama secara default
        $roster = $this->roster();
        if ($roster->isNotEmpty()) {
            $this->selectPatrol($roster->first()['patrol_id']);
        }
    }

    #[Computed]
    public function schedule(): CompetitionSchedule
    {
        return CompetitionSchedule::with([
            'competition.criteria',
            'judgeAssignments.judge',
            'attendanceSessions',
        ])->findOrFail($this->scheduleId);
    }

    #[Computed]
    public function competition()
    {
        return $this->schedule()->competition;
    }

    #[Computed]
    public function criteria(): Collection
    {
        return $this->competition()->criteria ?: collect();
    }

    #[Computed]
    public function roster(): Collection
    {
        $scoringService = app(AssessmentScoringService::class);
        $user = Auth::user();

        // Hanya administrator yang dapat melihat unmask nama sekolah
        $canUnmask = $user->isAdmin() && $this->unmaskNames;

        return $scoringService->getBlindScoringRoster(
            $this->schedule(),
            $user,
            $canUnmask
        );
    }

    #[Computed]
    public function selectedPatrol(): ?array
    {
        if (! $this->selectedPatrolId) {
            return null;
        }

        return $this->roster()->firstWhere('patrol_id', $this->selectedPatrolId);
    }

    #[Computed]
    public function activeAssessment(): ?Assessment
    {
        if (! $this->selectedPatrolId) {
            return null;
        }

        $query = Assessment::where('competition_schedule_id', $this->scheduleId)
            ->where('patrol_id', $this->selectedPatrolId);

        if (! Auth::user()->isAdmin()) {
            $query->where('judge_id', Auth::id());
        }

        return $query->first();
    }

    #[Computed]
    public function currentCalculatedScore(): array
    {
        $scoringService = app(AssessmentScoringService::class);
        return $scoringService->calculateWeightedScore(
            $this->criteriaScores,
            $this->competition()
        );
    }

    #[Computed]
    public function isAssignedJudge(): bool
    {
        $user = Auth::user();
        if (! $user || $user->isAdmin()) {
            return false;
        }

        return $user->isJudge() && $this->schedule()->judgeAssignments()
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Memilih regu peserta yang akan dinilai
     */
    public function selectPatrol(string $patrolId): void
    {
        $this->selectedPatrolId = $patrolId;
        $query = Assessment::where('competition_schedule_id', $this->scheduleId)
            ->where('patrol_id', $patrolId);

        if (! Auth::user()->isAdmin()) {
            $query->where('judge_id', Auth::id());
        }

        $assessment = $query->first();

        $this->criteriaScores = [];
        $criteria = $this->criteria();

        if ($assessment && is_array($assessment->criteria_scores)) {
            foreach ($criteria as $criterion) {
                $this->criteriaScores[$criterion->id] = $assessment->criteria_scores[$criterion->id] ?? (float) $criterion->min_score;
            }
            $this->notes = $assessment->notes;
        } else {
            // Default nilai ke batas minimal kriteria
            foreach ($criteria as $criterion) {
                $this->criteriaScores[$criterion->id] = (float) $criterion->min_score;
            }
            $this->notes = null;
        }
    }

    /**
     * Menyimpan draf penilaian (Autosave / Manual Save)
     */
    public function saveDraft(AssessmentScoringService $scoringService): void
    {
        // Penilaian mutlak hanya dapat dilakukan oleh Juri yang ditugaskan
        if (! $this->isAssignedJudge) {
            $msg = 'Pemberian nilai hanya dapat dilakukan oleh Juri yang telah ditunjuk (Administrator tidak diperkenankan melakukan penilaian).';
            Flux::toast(variant: 'danger', heading: 'Akses Ditolak', text: $msg);
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => $msg,
            ]);
            return;
        }

        if (! $this->selectedPatrolId) {
            return;
        }

        $assessment = $this->activeAssessment();
        if ($assessment && $assessment->isLocked()) {
            $msg = 'Penilaian ini telah difinalisasi dan terkunci.';
            Flux::toast(variant: 'warning', heading: 'Penilaian Terkunci', text: $msg);
            $this->dispatch('alert', [
                'type' => 'warning',
                'message' => $msg,
            ]);
            return;
        }

        $scoringService->saveDraft([
            'competition_schedule_id' => $this->scheduleId,
            'patrol_id' => $this->selectedPatrolId,
            'criteria_scores' => $this->criteriaScores,
            'notes' => $this->notes,
        ], Auth::user());

        $msg = 'Draf penilaian untuk ' . ($this->selectedPatrol()['blind_label'] ?? 'Peserta') . ' berhasil disimpan.';
        Flux::toast(variant: 'success', heading: 'Draf Disimpan', text: $msg);
        $this->dispatch('alert', [
            'type' => 'success',
            'message' => $msg,
        ]);
    }

    /**
     * Membuka modal konfirmasi finalisasi nilai
     */
    public function openFinalizeModal(): void
    {
        if (! $this->isAssignedJudge) {
            $msg = 'Pemberian nilai hanya dapat dilakukan oleh Juri yang telah ditunjuk.';
            Flux::toast(variant: 'danger', heading: 'Akses Ditolak', text: $msg);
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => $msg,
            ]);
            return;
        }

        $this->isFinalizeModalOpen = true;
    }

    /**
     * Memfinalisasi dan mengunci nilai resmi ke sistem klasemen
     */
    public function finalize(AssessmentScoringService $scoringService): void
    {
        // Penilaian mutlak hanya dapat dilakukan oleh Juri yang ditugaskan
        if (! $this->isAssignedJudge) {
            $msg = 'Pemberian nilai hanya dapat dilakukan oleh Juri yang telah ditunjuk (Administrator tidak diperkenankan melakukan penilaian).';
            Flux::toast(variant: 'danger', heading: 'Akses Ditolak', text: $msg);
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => $msg,
            ]);
            return;
        }

        if (! $this->selectedPatrolId) {
            return;
        }

        // Simpan draf terlebih dahulu agar skor mutakhir tercatat
        $assessment = $scoringService->saveDraft([
            'competition_schedule_id' => $this->scheduleId,
            'patrol_id' => $this->selectedPatrolId,
            'criteria_scores' => $this->criteriaScores,
            'notes' => $this->notes,
        ], Auth::user());

        // Finalisasi & kunci
        $scoringService->finalizeScore($assessment->id, Auth::user());

        $this->isFinalizeModalOpen = false;

        $msg = 'Nilai resmi untuk ' . ($this->selectedPatrol()['blind_label'] ?? 'Peserta') . ' telah difinalisasi dan dikunci ke klasemen.';
        Flux::toast(variant: 'success', heading: 'Nilai Difinalisasi', text: $msg);
        $this->dispatch('alert', [
            'type' => 'success',
            'message' => $msg,
        ]);
    }

    /**
     * Membuka modal unlock (Khusus Administrator)
     */
    public function openUnlockModal(): void
    {
        if (! Auth::user()->isAdmin()) {
            abort(403);
        }
        $this->unlockReason = '';
        $this->isUnlockModalOpen = true;
    }

    /**
     * Membuka kunci penilaian oleh Administrator dengan catatan alasan
     */
    public function unlock(AssessmentScoringService $scoringService): void
    {
        if (! Auth::user()->isAdmin()) {
            abort(403);
        }

        $this->validate([
            'unlockReason' => 'required|string|min:5',
        ], [
            'unlockReason.required' => 'Alasan pembukaan kunci penilaian wajib diisi.',
            'unlockReason.min' => 'Alasan minimal 5 karakter.',
        ]);

        $assessment = $this->activeAssessment();
        if ($assessment) {
            $scoringService->unlockScore($assessment->id, $this->unlockReason, Auth::user());
        }

        $this->isUnlockModalOpen = false;
        $this->unlockReason = '';

        $msg = 'Kunci penilaian berhasil dibuka dan dicatat dalam audit trail.';
        Flux::toast(variant: 'success', heading: 'Kunci Dibuka', text: $msg);
        $this->dispatch('alert', [
            'type' => 'success',
            'message' => $msg,
        ]);
    }

    /**
     * Toggle unmask nama sekolah (Khusus Administrator)
     */
    public function toggleUnmask(): void
    {
        if (Auth::user()->isAdmin()) {
            $this->unmaskNames = ! $this->unmaskNames;
        }
    }

    public function render()
    {
        return view('livewire.portal.judge-scoring', [
            'schedule' => $this->schedule(),
            'competition' => $this->competition(),
            'criteria' => $this->criteria(),
            'roster' => $this->roster(),
            'selectedPatrol' => $this->selectedPatrol(),
            'activeAssessment' => $this->activeAssessment(),
            'calculatedScore' => $this->currentCalculatedScore(),
            'isAdmin' => Auth::user()->isAdmin(),
            'isAssignedJudge' => $this->isAssignedJudge,
        ]);
    }
}

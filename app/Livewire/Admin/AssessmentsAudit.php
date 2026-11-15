<?php

namespace App\Livewire\Admin;

use App\Enums\RoleEnum;
use App\Models\Assessment;
use App\Models\AuditLog;
use App\Models\Competition;
use App\Models\User;
use App\Services\AssessmentScoringService;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Komponen Livewire 4 Audit Penilaian Juri & Jejak Rekam Sistem
 */
#[Layout('layouts.app')]
#[Title('Audit Penilaian Juri & Log — Administrator')]
class AssessmentsAudit extends Component
{
    use WithPagination;

    #[Url]
    public string $activeTab = 'assessments'; // assessments, audit_logs

    #[Url]
    public string $search = '';

    #[Url]
    public string $competitionFilter = 'all';

    #[Url]
    public string $statusFilter = 'all';

    #[Url]
    public string $judgeFilter = 'all';

    // Modal Pembukaan Nilai Per Individual Assessment
    public bool $isUnlockModalOpen = false;

    public ?string $selectedAssessmentId = null;

    public string $unlockReason = '';

    // Modal Pembukaan Nilai Per Juri (Bulk per Judge)
    public bool $isUnlockJudgeModalOpen = false;

    public string $unlockJudgeId = '';

    public string $unlockJudgeCompetitionId = 'all';

    public string $unlockJudgeReason = '';

    public function mount(): void
    {
        if (! Auth::user()->isAdmin()) {
            abort(403, 'Akses terbatas khusus Administrator.');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCompetitionFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingJudgeFilter(): void
    {
        $this->resetPage();
    }

    public function openUnlockModal(string $assessmentId): void
    {
        $this->selectedAssessmentId = $assessmentId;
        $this->unlockReason = '';
        $this->isUnlockModalOpen = true;
    }

    public function unlockAssessment(AssessmentScoringService $scoringService): void
    {
        $this->validate([
            'unlockReason' => 'required|string|min:5',
        ], [
            'unlockReason.required' => 'Alasan pembukaan kunci penilaian wajib diisi.',
            'unlockReason.min' => 'Alasan minimal 5 karakter.',
        ]);

        if ($this->selectedAssessmentId) {
            $scoringService->unlockScore(
                $this->selectedAssessmentId,
                $this->unlockReason,
                Auth::user()
            );

            Flux::toast(variant: 'success', heading: 'Audit Berhasil', text: 'Penilaian berhasil dibuka dan dicatat dalam riwayat audit log.');
        }

        $this->isUnlockModalOpen = false;
        $this->selectedAssessmentId = null;
        $this->unlockReason = '';
    }

    public function openUnlockJudgeModal(?string $judgeId = null, ?string $competitionId = null): void
    {
        $this->unlockJudgeId = $judgeId ?? '';
        $this->unlockJudgeCompetitionId = $competitionId ?? ($this->competitionFilter !== 'all' ? $this->competitionFilter : 'all');
        $this->unlockJudgeReason = '';
        $this->isUnlockJudgeModalOpen = true;
    }

    public function unlockJudgeAssessments(AssessmentScoringService $scoringService): void
    {
        $this->validate([
            'unlockJudgeId' => 'required|exists:users,id',
            'unlockJudgeReason' => 'required|string|min:5',
        ], [
            'unlockJudgeId.required' => 'Pilih dewan juri yang akan dibuka penilaiannya.',
            'unlockJudgeId.exists' => 'Juri yang dipilih tidak valid.',
            'unlockJudgeReason.required' => 'Alasan pembukaan kunci penilaian wajib diisi.',
            'unlockJudgeReason.min' => 'Alasan minimal 5 karakter.',
        ]);

        $unlocked = $scoringService->unlockScoresByJudge(
            judgeId: $this->unlockJudgeId,
            reason: $this->unlockJudgeReason,
            admin: Auth::user(),
            competitionId: $this->unlockJudgeCompetitionId !== 'all' ? $this->unlockJudgeCompetitionId : null
        );

        $judgeUser = User::find($this->unlockJudgeId);
        $judgeName = $judgeUser?->name ?? 'Dewan Juri';

        if ($unlocked->count() > 0) {
            Flux::toast(
                variant: 'success',
                heading: 'Penilaian Dibuka',
                text: "Sebanyak {$unlocked->count()} penilaian dari {$judgeName} berhasil dibuka kembali ke status Draf Terbuka."
            );
        } else {
            Flux::toast(
                variant: 'warning',
                heading: 'Tidak Ada Penilaian Terkunci',
                text: "Tidak ditemukan penilaian terfinalisasi dari {$judgeName} pada kriteria filter yang dipilih."
            );
        }

        $this->isUnlockJudgeModalOpen = false;
        $this->unlockJudgeId = '';
        $this->unlockJudgeCompetitionId = 'all';
        $this->unlockJudgeReason = '';
    }

    public function getTargetFinalizedCount(): int
    {
        if (! $this->unlockJudgeId) {
            return 0;
        }

        $query = Assessment::where('judge_id', $this->unlockJudgeId)
            ->where('status', 'finalized');

        if ($this->unlockJudgeCompetitionId !== 'all') {
            $query->where('competition_id', $this->unlockJudgeCompetitionId);
        }

        return $query->count();
    }

    public function render()
    {
        $competitions = Competition::published()->get();

        // Daftar seluruh Dewan Juri
        $judges = User::whereHas('roles', fn ($q) => $q->where('name', RoleEnum::Judge->value))
            ->orWhereIn('id', Assessment::select('judge_id')->distinct())
            ->orderBy('name')
            ->get();

        // Ringkasan status penilaian per juri
        $judgesSummary = User::whereIn('id', Assessment::select('judge_id')->distinct())
            ->withCount([
                'assessments as total_assessments',
                'assessments as finalized_count' => fn ($q) => $q->where('status', 'finalized'),
                'assessments as draft_count' => fn ($q) => $q->where('status', 'draft'),
            ])
            ->orderBy('name')
            ->get();

        // 1. Data Penilaian Juri
        $assessmentsQuery = Assessment::with([
            'competition',
            'schedule',
            'patrol.scoutGroup',
            'judge',
        ]);

        if ($this->competitionFilter !== 'all') {
            $assessmentsQuery->where('competition_id', $this->competitionFilter);
        }

        if ($this->statusFilter !== 'all') {
            $assessmentsQuery->where('status', $this->statusFilter);
        }

        if ($this->judgeFilter !== 'all') {
            $assessmentsQuery->where('judge_id', $this->judgeFilter);
        }

        if ($this->search) {
            $search = $this->search;
            $assessmentsQuery->where(function ($q) use ($search) {
                $q->whereHas('patrol', function ($sub) use ($search) {
                    $sub->where('patrol_name', 'like', "%{$search}%")
                        ->orWhereHas('scoutGroup', function ($g) use ($search) {
                            $g->where('school_name', 'like', "%{$search}%");
                        });
                })->orWhereHas('judge', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                });
            });
        }

        $assessments = $assessmentsQuery->latest()->paginate(15);

        // 2. Data Audit Log
        $logsQuery = AuditLog::with('user');
        if ($this->search) {
            $search = $this->search;
            $logsQuery->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%");
                    });
            });
        }
        $auditLogs = $logsQuery->latest('created_at')->paginate(20);

        $stats = [
            'total_assessments' => Assessment::count(),
            'finalized' => Assessment::where('status', 'finalized')->count(),
            'draft' => Assessment::where('status', 'draft')->count(),
            'total_logs' => AuditLog::count(),
        ];

        return view('livewire.admin.assessments-audit', [
            'competitions' => $competitions,
            'judges' => $judges,
            'judgesSummary' => $judgesSummary,
            'assessments' => $assessments,
            'auditLogs' => $auditLogs,
            'stats' => $stats,
            'targetFinalizedCount' => $this->getTargetFinalizedCount(),
        ]);
    }
}

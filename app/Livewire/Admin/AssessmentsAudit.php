<?php

namespace App\Livewire\Admin;

use App\Models\Assessment;
use App\Models\AuditLog;
use App\Models\Competition;
use App\Models\CompetitionSchedule;
use App\Services\AssessmentScoringService;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Komponen Livewire 4 Audit Penilaian Juri & Jejak Rekam Sistem
 */
#[Layout('layouts.app')]
#[Title('Audit Penilaian Juri & Log — Administrator')]
class AssessmentsAudit extends Component
{
    #[Url]
    public string $activeTab = 'assessments'; // assessments, audit_logs

    #[Url]
    public string $search = '';

    #[Url]
    public string $competitionFilter = 'all';

    #[Url]
    public string $statusFilter = 'all';

    public bool $isUnlockModalOpen = false;

    public ?string $selectedAssessmentId = null;

    public string $unlockReason = '';

    public function mount(): void
    {
        if (! Auth::user()->isAdmin()) {
            abort(403, 'Akses terbatas khusus Administrator.');
        }
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

    public function render()
    {
        $competitions = Competition::published()->get();

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
            'assessments' => $assessments,
            'auditLogs' => $auditLogs,
            'stats' => $stats,
        ]);
    }
}

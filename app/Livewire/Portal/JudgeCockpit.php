<?php

namespace App\Livewire\Portal;

use App\Models\Assessment;
use App\Models\CompetitionSchedule;
use App\Services\AssessmentScoringService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Komponen Livewire 4 Cockpit Utama Dewan Juri
 */
#[Layout('layouts.app')]
#[Title('Cockpit Penjurian — Dewan Juri LT III')]
class JudgeCockpit extends Component
{
    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = 'all';

    public function render(AssessmentScoringService $scoringService)
    {
        $user = Auth::user();

        // Ambil jadwal sesi lomba yang ditugaskan kepada juri
        $query = CompetitionSchedule::with([
            'competition.criteria',
            'judgeAssignments.judge',
            'attendanceSessions.attendances',
        ]);

        if (! $user->isAdmin()) {
            $query->whereHas('judgeAssignments', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('session_name', 'like', "%{$search}%")
                    ->orWhere('arena_location', 'like', "%{$search}%")
                    ->orWhereHas('competition', function ($sub) use ($search) {
                        $sub->where('title', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
            });
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $schedules = $query->orderBy('start_time')->get();

        // Hitung statistik ringkasan kartu juri
        $allAssignedQuery = CompetitionSchedule::query();
        if (! $user->isAdmin()) {
            $allAssignedQuery->whereHas('judgeAssignments', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        $allAssigned = $allAssignedQuery->get();

        $myAssessments = Assessment::where('judge_id', $user->id)->get();
        $finalizedCount = $myAssessments->where('status', 'finalized')->count();
        $draftCount = $myAssessments->where('status', 'draft')->count();

        $stats = [
            'total_assigned' => $allAssigned->count(),
            'in_progress' => $allAssigned->where('status', 'in_progress')->count(),
            'finalized_scores' => $finalizedCount,
            'draft_scores' => $draftCount,
        ];

        // Format data jadwal untuk tampilan dengan kalkulasi progres penilaian
        $schedulesData = $schedules->map(function (CompetitionSchedule $schedule) use ($user, $scoringService) {
            $progress = $scoringService->scoringProgress($schedule, $user);
            $totalPatrols = $progress['total'];
            $scoredPatrols = $progress['scored'];
            $finalizedPatrols = $progress['finalized'];

            $myAssignment = $schedule->judgeAssignments->firstWhere('user_id', $user->id);

            return [
                'id' => $schedule->id,
                'competition_title' => $schedule->competition?->title ?? 'Mata Lomba',
                'competition_code' => $schedule->competition?->code ?? '-',
                'session_name' => $schedule->session_name,
                'arena_location' => $schedule->arena_location,
                'status' => $schedule->status,
                'gender_category' => $schedule->gender_category,
                'start_time' => $schedule->start_time?->format('d M Y, H:i') ?? '-',
                'end_time' => $schedule->end_time?->format('H:i') ?? '-',
                'total_patrols' => $totalPatrols,
                'scored_patrols' => $scoredPatrols,
                'finalized_patrols' => $finalizedPatrols,
                'progress_percentage' => $totalPatrols > 0 ? round(($finalizedPatrols / $totalPatrols) * 100) : 0,
                'my_role' => $myAssignment?->roleLabel() ?? ($user->isAdmin() ? 'Administrator' : 'Juri'),
                'scoring_state' => $schedule->scoringState(),
                'scoring_message' => $schedule->scoringClosedMessage(),
                'criteria_count' => $schedule->competition?->criteria?->count() ?? 0,
            ];
        });

        return view('livewire.portal.judge-cockpit', [
            'stats' => $stats,
            'schedules' => $schedulesData,
            'isJudge' => $user->isJudge(),
            'isAdmin' => $user->isAdmin(),
        ]);
    }
}

<?php

namespace App\Livewire;

use App\Enums\PatrolGenderEnum;
use App\Enums\RegistrationStatusEnum;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Competition;
use App\Models\CompetitionSchedule;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Komponen Livewire 4 Dashboard Utama Multi-Peran
 */
#[Layout('layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    #[Computed]
    public function user()
    {
        return auth()->user();
    }

    #[Computed]
    public function activeEvent()
    {
        return Event::where('is_active', true)->first();
    }

    #[Computed]
    public function adminStats(): array
    {
        return [
            'pending_count' => ScoutGroup::where('verification_status', RegistrationStatusEnum::Submitted)->count(),
            'total_groups' => ScoutGroup::count(),
            'total_patrols' => Patrol::count(),
            'total_participants' => Participant::count(),
            'total_schedules' => CompetitionSchedule::published()->count(),
            'active_sessions' => AttendanceSession::where('is_active', true)->count(),
            'total_competitions' => Competition::published()->count(),
        ];
    }

    #[Computed]
    public function scoutGroup(): ?ScoutGroup
    {
        if (!$this->user?->isScoutGroup()) {
            return null;
        }

        return ScoutGroup::with(['patrols.participants', 'chaperones'])
            ->where('user_id', $this->user->id)
            ->first();
    }

    #[Computed]
    public function committeeStats(): array
    {
        return [
            'total_sessions' => AttendanceSession::accessibleBy($this->user)->count(),
            'active_sessions' => AttendanceSession::accessibleBy($this->user)->where('is_active', true)->count(),
            'attendances_today' => Attendance::whereHas('session', fn ($q) => $q->accessibleBy($this->user))->whereDate('scanned_at', today())->count(),
            'total_patrols_verified' => Patrol::where('status', RegistrationStatusEnum::Verified)->count(),
        ];
    }

    #[Computed]
    public function judgeStats(): array
    {
        $userId = $this->user?->id;

        return [
            'assigned_sessions' => CompetitionSchedule::whereHas('judgeAssignments', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })->count(),
            'total_competitions' => Competition::published()->count(),
            'active_attendance_sessions' => AttendanceSession::accessibleBy($this->user)->where('is_active', true)->count(),
        ];
    }

    #[Computed]
    public function participantData(): ?Participant
    {
        if (!$this->user?->isParticipant()) {
            return null;
        }

        return Participant::with(['patrol.scoutGroup'])->find($this->user->participant_id);
    }

    public function render()
    {
        return view('livewire.dashboard', [
            'user' => $this->user,
            'activeEvent' => $this->activeEvent,
            'adminStats' => $this->adminStats,
            'scoutGroup' => $this->scoutGroup,
            'committeeStats' => $this->committeeStats,
            'judgeStats' => $this->judgeStats,
            'participantData' => $this->participantData,
        ]);
    }
}

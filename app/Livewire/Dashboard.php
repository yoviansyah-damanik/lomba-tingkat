<?php

namespace App\Livewire;

use App\Enums\PatrolGenderEnum;
use App\Enums\RegistrationStatusEnum;
use App\Enums\RoleEnum;
use App\Models\Assessment;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Competition;
use App\Models\CompetitionSchedule;
use App\Models\Chaperone;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\PermissionRequest;
use App\Models\ScoutGroup;
use App\Models\User;
use App\Services\StandingsService;
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
        $groupsByStatus = ScoutGroup::selectRaw('verification_status, count(*) as total')->groupBy('verification_status')->pluck('total', 'verification_status');
        $count = fn (RegistrationStatusEnum $status) => (int) ($groupsByStatus[$status->value] ?? 0);

        return [
            'pending_count' => $count(RegistrationStatusEnum::Submitted),
            'verified_groups' => $count(RegistrationStatusEnum::Verified),
            'revision_groups' => $count(RegistrationStatusEnum::NeedsRevision) + $count(RegistrationStatusEnum::Rejected),
            'total_groups' => $groupsByStatus->sum(),
            'total_patrols' => Patrol::count(),
            'boys_patrols' => Patrol::where('gender_category', PatrolGenderEnum::Boys)->count(),
            'girls_patrols' => Patrol::where('gender_category', PatrolGenderEnum::Girls)->count(),
            'total_participants' => Participant::count(),
            'boys_participants' => Participant::whereHas('patrol', fn ($q) => $q->where('gender_category', PatrolGenderEnum::Boys))->count(),
            'girls_participants' => Participant::whereHas('patrol', fn ($q) => $q->where('gender_category', PatrolGenderEnum::Girls))->count(),
            'total_chaperones' => Chaperone::count(),
            'male_chaperones' => Chaperone::where('gender', 'male')->count(),
            'female_chaperones' => Chaperone::where('gender', 'female')->count(),
            'committee_count' => User::whereHas('roles', fn ($q) => $q->where('name', RoleEnum::Committee->value))->count(),
            'judge_count' => User::whereHas('roles', fn ($q) => $q->where('name', RoleEnum::Judge->value))->count(),
            'officials_count' => User::whereHas('roles', fn ($q) => $q->whereIn('name', [RoleEnum::Committee->value, RoleEnum::Judge->value]))->count(),
            'total_schedules' => CompetitionSchedule::published()->count(),
            'completed_schedules' => CompetitionSchedule::published()->where('status', 'completed')->count(),
            'live_schedules' => CompetitionSchedule::published()->where('status', 'in_progress')->count(),
            'active_sessions' => AttendanceSession::where('is_active', true)->count(),
            'attendances_today' => Attendance::whereDate('scanned_at', today())->count(),
            'outstanding_permissions' => PermissionRequest::where('status', 'active')->count(),
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
            'verified_participants' => Participant::where('status', RegistrationStatusEnum::Verified)->count(),
            'verified_chaperones' => Chaperone::where('status', RegistrationStatusEnum::Verified)->count(),
            'schedules_today' => CompetitionSchedule::published()->where('status', '!=', 'cancelled')->whereDate('start_time', today())->count(),
            'live_schedules' => CompetitionSchedule::published()->where('status', 'in_progress')->count(),
        ];
    }

    #[Computed]
    public function judgeStats(): array
    {
        $userId = $this->user?->id;
        $assigned = fn () => CompetitionSchedule::published()->where('status', '!=', 'cancelled')
            ->whereHas('judgeAssignments', fn ($q) => $q->where('user_id', $userId));

        return [
            'assigned_sessions' => $assigned()->count(),
            'upcoming_sessions' => $assigned()->where('end_time', '>=', now())->count(),
            'sessions_today' => $assigned()->whereDate('start_time', today())->count(),
            'completed_sessions' => $assigned()->where('status', 'completed')->count(),
            'finalized_scores' => Assessment::where('judge_id', $userId)->where('status', 'finalized')->count(),
            'draft_scores' => Assessment::where('judge_id', $userId)->where('status', '!=', 'finalized')->count(),
            'total_competitions' => Competition::published()->count(),
            'active_attendance_sessions' => AttendanceSession::accessibleBy($this->user)->where('is_active', true)->count(),
        ];
    }

    #[Computed]
    public function groupStats(): array
    {
        $group = $this->scoutGroup;

        if (! $group) {
            return [];
        }

        $participants = $group->patrols->flatMap->participants;
        $docColumns = array_column(array_values(Participant::DOCUMENTS), 0);
        $docsComplete = $participants->filter(fn (Participant $p) => collect($docColumns)->every(fn ($column) => filled($p->{$column}) ) && filled($p->photo_path))->count();
        $fullPatrols = $group->patrols->filter(fn (Patrol $patrol) => $group->rosterProblemsFor($patrol) === [])->count();
        $patrolIds = $group->patrols->pluck('id');

        return [
            'patrols' => $group->patrols->count(),
            'boys_patrols' => $group->patrols->where('gender_category', PatrolGenderEnum::Boys)->count(),
            'girls_patrols' => $group->patrols->where('gender_category', PatrolGenderEnum::Girls)->count(),
            'full_patrols' => $fullPatrols,
            'participants' => $participants->count(),
            'participants_target' => $group->patrols->count() * Patrol::REQUIRED_MEMBERS,
            'docs_complete' => $docsComplete,
            'chaperones' => $group->chaperones->count(),
            'scored_competitions' => \App\Models\CompetitionScore::whereIn('patrol_id', $patrolIds)->where('is_locked', true)->count(),
            'next_schedule' => CompetitionSchedule::published()->where('status', '!=', 'cancelled')->where('end_time', '>=', now())->first(),
        ];
    }
    /**
     * Regu yang relevan bagi pengguna (Gugus Depan: seluruh regu; Peserta: regu sendiri; Bindamping: regu sesuai kategori)
     */
    #[Computed]
    public function userPatrols(): \Illuminate\Support\Collection
    {
        $user = $this->user;

        return match (true) {
            $user->isScoutGroup() => $this->scoutGroup?->patrols ?? collect(),
            $user->isParticipant() => collect([$this->participantData?->patrol])->filter(),
            $user->isChaperone() && $user->chaperone => Patrol::where('chaperone_id', $user->chaperone->id)->get(),
            default => collect(),
        };
    }

    /**
     * Jumlah prestasi (Prestasi Tinggi, Baik, Cukup) dari nilai final seluruh regu pengguna
     *
     * @return array{total: int, high: int, good: int, fair: int, scored: int}
     */
    #[Computed]
    public function achievements(): array
    {
        $counts = \App\Models\CompetitionScore::whereIn('patrol_id', $this->userPatrols->pluck('id'))
            ->where('is_locked', true)
            ->selectRaw('predicate, count(*) as total')
            ->groupBy('predicate')
            ->pluck('total', 'predicate');

        $high = (int) ($counts['prestasi_tinggi'] ?? 0);
        $good = (int) ($counts['prestasi_baik'] ?? 0);
        $fair = (int) ($counts['prestasi_cukup'] ?? 0);

        return ['total' => $high + $good + $fair, 'high' => $high, 'good' => $good, 'fair' => $fair, 'scored' => (int) $counts->sum()];
    }

    /**
     * Peringkat umum regu milik pengguna (Gugus Depan: seluruh regu; Peserta: regu sendiri; Bindamping: regu
     * sesuai kategori). Disensor selama blind mode aktif.
     *
     * @return array<int, array{patrol: string, category: string, rank: ?int, total: int, censored: bool}>
     */
    #[Computed]
    public function rankings(): array
    {
        $patrols = $this->userPatrols;

        if ($patrols->isEmpty()) {
            return [];
        }

        $censored = (bool) Event::first()?->hide_patrol_identities;
        $standings = app(StandingsService::class);
        $byGender = [];

        return $patrols->map(function (Patrol $patrol) use ($standings, &$byGender, $censored) {
            $gender = $patrol->gender_category instanceof \BackedEnum ? $patrol->gender_category->value : $patrol->gender_category;
            $byGender[$gender] ??= $standings->getPatrolStandings($gender, null, true);
            $row = $byGender[$gender]->firstWhere('id', $patrol->id);

            return [
                'patrol' => $patrol->patrol_name,
                'category' => $gender === 'girls' ? 'Putri' : 'Putra',
                'rank' => $censored ? null : ($row['rank'] ?? null),
                'total' => $byGender[$gender]->count(),
                'censored' => $censored,
            ];
        })->values()->all();
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
            'groupStats' => $this->groupStats,
            'achievements' => ($this->user->isScoutGroup() || $this->user->isParticipant() || $this->user->isChaperone()) ? $this->achievements : [],
            'rankings' => ($this->user->isScoutGroup() || $this->user->isParticipant() || $this->user->isChaperone()) ? $this->rankings : [],
            'participantData' => $this->participantData,
            'menuGroups' => \App\Support\Navigation::groupsFor($this->user),
        ]);
    }
}

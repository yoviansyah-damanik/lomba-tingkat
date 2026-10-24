<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Chaperone;
use App\Models\Participant;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Menyusun halaman "Presensi Saya": jadwal kegiatan yang relevan bagi peserta, bindamping, panitia, dan juri
 * beserta status kehadirannya yang dicatat petugas pemindai.
 */
class MyAttendanceService
{
    public const STATUS_PRESENT = 'present';

    public const STATUS_OPEN = 'open';

    public const STATUS_UPCOMING = 'upcoming';

    public const STATUS_ABSENT = 'absent';

    /**
     * Identitas pemilik QR: peserta, bindamping, atau aparatur (panitia/juri)
     *
     * @return array{type: string, model: Participant|Chaperone|User, name: string, id_number: ?string, role_label: string, sub_title: string, qr_token: ?string}|null
     */
    public function identity(User $user): ?array
    {
        if ($user->isParticipant() && ($participant = $user->participant()->with('patrol.scoutGroup')->first())) {
            return [
                'type' => 'participant',
                'model' => $participant,
                'name' => $participant->full_name,
                'id_number' => $participant->id_number,
                'role_label' => $participant->role_in_patrol?->label() ?? 'Peserta',
                'sub_title' => ($participant->patrol?->scoutGroup?->school_name ?? '-').' • Regu '.($participant->patrol?->patrol_name ?? '-'),
                'qr_token' => $participant->qr_token,
            ];
        }

        if ($user->isChaperone() && ($chaperone = $user->chaperone()->with('scoutGroup')->first())) {
            return [
                'type' => 'chaperone',
                'model' => $chaperone,
                'name' => $chaperone->full_name,
                'id_number' => $chaperone->id_number,
                'role_label' => 'Pembina Pendamping '.($chaperone->gender === 'male' ? 'Putra' : 'Putri'),
                'sub_title' => $chaperone->scoutGroup?->school_name ?? '-',
                'qr_token' => $chaperone->qr_token,
            ];
        }

        if ($user->isCommittee() || $user->isJudge()) {
            return [
                'type' => 'user',
                'model' => $user,
                'name' => $user->name,
                'id_number' => $user->id_number,
                'role_label' => $user->roleLabel(),
                'sub_title' => $user->institution ?: 'Kwartir Cabang Kota Padangsidimpuan',
                'qr_token' => $user->qr_token,
            ];
        }

        return null;
    }

    /**
     * Seluruh jadwal terkait pengguna, berurutan menurut waktu mulai
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function items(User $user): Collection
    {
        $identity = $this->identity($user);

        if (! $identity) {
            return collect();
        }

        $sessions = $this->relevantSessions($user, $identity)
            ->sortBy(fn (AttendanceSession $session) => $this->startTime($session)?->timestamp ?? 0)
            ->values();

        $attendances = $this->attendancesFor($sessions, $identity);

        return $sessions->map(fn (AttendanceSession $session) => $this->buildItem($session, $identity, $attendances, $user));
    }

    /**
     * Jadwal lomba yang ditugaskan kepada juri (penunjukan juri), berurutan menurut waktu mulai
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function judgeDuties(User $user): Collection
    {
        if (! $user->isJudge()) {
            return collect();
        }

        return $user->judgeAssignments()
            ->with(['schedule.competition'])
            ->whereHas('schedule', fn ($q) => $q->where('is_published', true))
            ->get()
            ->filter(fn ($assignment) => $assignment->schedule)
            ->sortBy(fn ($assignment) => $assignment->schedule->start_time?->timestamp ?? 0)
            ->map(function ($assignment) {
                $schedule = $assignment->schedule;
                $now = now();

                $phase = match (true) {
                    $schedule->end_time && $now->greaterThan($schedule->end_time) => 'finished',
                    $schedule->start_time && $now->greaterThanOrEqualTo($schedule->start_time) => 'ongoing',
                    default => 'upcoming',
                };

                return [
                    'id' => $assignment->id,
                    'competition' => $schedule->competition?->title ?? '-',
                    'session_name' => $schedule->session_name,
                    'location' => $schedule->arena_location,
                    'start' => $schedule->start_time,
                    'end' => $schedule->end_time,
                    'role' => $assignment->roleLabel(),
                    'confirmed' => (bool) $assignment->is_confirmed,
                    'category' => match ($schedule->effectiveGenderCategory()) {
                        'boys' => 'Penggalang Putra',
                        'girls' => 'Penggalang Putri',
                        default => 'Putra & Putri',
                    },
                    'participant_type' => $schedule->participantTypeLabel(),
                    'phase' => $phase,
                ];
            })
            ->values();
    }

    /**
     * @return Collection<int, AttendanceSession>
     */
    private function relevantSessions(User $user, array $identity): Collection
    {
        $sessions = collect();

        $base = fn () => AttendanceSession::with(['schedule.competition'])
            ->where(fn ($q) => $q->whereNull('competition_schedule_id')->orWhereHas('schedule', fn ($s) => $s->where('is_published', true)));

        if ($identity['type'] === 'participant') {
            $patrol = $identity['model']->patrol;

            $sessions = $base()->where('session_type', 'participant')->get()
                ->filter(fn (AttendanceSession $s) => $this->genderMatches($s, $patrol?->isBoys(), $patrol?->isGirls()));
        }

        if ($identity['type'] === 'chaperone') {
            $isMale = $identity['model']->gender === 'male';

            $own = $base()->where('session_type', 'chaperone')->get();
            // Jadwal lomba regu yang didampingi (dipantau lewat kehadiran regu)
            $regu = $base()->where('session_type', 'participant')->whereNotNull('competition_schedule_id')->get()
                ->filter(fn (AttendanceSession $s) => $this->genderMatches($s, $isMale, ! $isMale));

            $sessions = $own->concat($regu);
        }

        if ($identity['type'] === 'user') {
            if ($user->isCommittee()) {
                $sessions = $sessions->concat($base()->where('session_type', 'committee')->get());
            }

            if ($user->isJudge()) {
                $sessions = $sessions->concat($base()->where('session_type', 'judge')->get());
            }
        }

        return $sessions->unique('id')->values();
    }

    /**
     * Sesi lomba hanya relevan bagi gender yang sesuai; sesi umum relevan bagi semua
     */
    private function genderMatches(AttendanceSession $session, ?bool $isBoys, ?bool $isGirls): bool
    {
        if (! $session->schedule) {
            return true;
        }

        return match ($session->schedule->effectiveGenderCategory()) {
            'boys' => (bool) $isBoys,
            'girls' => (bool) $isGirls,
            default => true,
        };
    }

    /**
     * Catatan kehadiran pada sesi-sesi tersebut yang menyangkut pengguna (pribadi atau regunya)
     *
     * @return Collection<int, Attendance>
     */
    private function attendancesFor(Collection $sessions, array $identity): Collection
    {
        if ($sessions->isEmpty()) {
            return collect();
        }

        $query = Attendance::with('scannedBy:id,name')->whereIn('attendance_session_id', $sessions->pluck('id'));

        $model = $identity['model'];

        return match ($identity['type']) {
            'participant' => $query->where(function ($q) use ($model) {
                $q->where('participant_id', $model->id);

                if ($model->patrol_id) {
                    $q->orWhere('patrol_id', $model->patrol_id);
                }
            })->get(),
            'chaperone' => $query->where(function ($q) use ($model) {
                $q->where('chaperone_id', $model->id)
                    ->orWhereIn('patrol_id', $model->patrols()->pluck('id'));
            })->get(),
            default => $query->where('user_id', $model->id)->get(),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function buildItem(AttendanceSession $session, array $identity, Collection $attendances, User $user): array
    {
        $schedule = $session->schedule;
        $isCompetition = $session->isCompetition() && $schedule;
        $own = $attendances->where('attendance_session_id', $session->id);

        $status = null;
        $record = null;
        $note = null;

        if ($identity['type'] === 'chaperone' && $session->session_type === 'participant') {
            // Kehadiran regu yang didampingi
            $regu = $own->whereNotNull('patrol_id');
            $record = $regu->first();
            $patrolCount = $regu->pluck('patrol_id')->unique()->count();
            $status = $record ? self::STATUS_PRESENT : null;
            $note = $record
                ? "Regu telah tercatat hadir ({$regu->count()} catatan, {$patrolCount} regu)."
                : 'Presensi regu dilakukan petugas saat regu tiba di pos.';
        } else {
            $record = $identity['type'] === 'participant'
                ? ($own->firstWhere('participant_id', $identity['model']->id) ?? $own->whereNotNull('patrol_id')->first())
                : $own->first();

            $status = $record ? self::STATUS_PRESENT : null;

            if ($record && $identity['type'] === 'participant' && $record->participant_id !== $identity['model']->id) {
                $note = 'Presensi tercatat atas nama '.($record->entity_name ?? 'perwakilan regu').' sebagai perwakilan regu.';
            } elseif ($identity['type'] === 'participant' && $schedule?->isPatrol() && ! $record) {
                $note = 'Presensi regu utuh dilakukan oleh PINRU atau WAPINRU.';
            }
        }

        $window = $this->windowStatus($session);

        if ($status === null) {
            $status = $window; // open | upcoming | absent
        }

        return [
            'id' => $session->id,
            'title' => $isCompetition ? ($schedule->competition?->title ?? $session->title) : $session->title,
            'subtitle' => $isCompetition ? $schedule->session_name : $session->sessionTypeLabel(),
            'kind' => $isCompetition ? 'lomba' : 'kegiatan',
            'location' => $isCompetition ? $schedule->arena_location : null,
            'start' => $this->startTime($session),
            'end' => $this->endTime($session),
            'status' => $status,
            'window' => $window,
            'scanned_at' => $record?->scanned_at,
            'scanned_by' => $record?->scannedBy?->name,
            'note' => $note,
        ];
    }

    /**
     * Posisi waktu saat ini terhadap jendela presensi sesi: open, upcoming, atau absent (sudah lewat)
     */
    private function windowStatus(AttendanceSession $session): string
    {
        if ($session->isScheduleClosed()) {
            return self::STATUS_ABSENT;
        }

        if (! $session->isScheduleOpen()) {
            return self::STATUS_UPCOMING;
        }

        return $session->is_active ? self::STATUS_OPEN : self::STATUS_UPCOMING;
    }

    private function startTime(AttendanceSession $session): ?CarbonInterface
    {
        return $session->schedule?->start_time ?? $session->opened_at;
    }

    private function endTime(AttendanceSession $session): ?CarbonInterface
    {
        return $session->schedule?->end_time ?? $session->closed_at;
    }
}

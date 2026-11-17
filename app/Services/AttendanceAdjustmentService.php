<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Chaperone;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceAdjustmentService
{
    /**
     * Sesuaikan atau buat catatan presensi baru secara manual untuk entitas pada sesi tertentu.
     */
    public function adjust(
        AttendanceSession $session,
        string $entityType,
        string $entityId,
        string $status,
        ?User $admin = null,
        ?string $notes = null,
        ?Carbon $scannedAt = null
    ): Attendance {
        $scannedAt ??= now();
        $notes ??= 'Penyesuaian susulan kehadiran oleh Administrator';

        return DB::transaction(function () use ($session, $entityType, $entityId, $status, $admin, $notes, $scannedAt) {
            $existing = $this->findExistingAttendance($session->id, $entityType, $entityId);

            if ($existing) {
                $existing->update([
                    'status' => $status,
                    'notes' => $notes,
                    'scanned_at' => $scannedAt,
                    'scanned_by_user_id' => $admin?->id ?? $existing->scanned_by_user_id,
                ]);

                return $existing->fresh();
            }

            // Siapkan relasi berdasarkan tipe entitas
            $participantId = null;
            $patrolId = null;
            $chaperoneId = null;
            $userId = null;

            if ($entityType === 'patrol') {
                $patrol = Patrol::with('participants')->find($entityId);
                $patrolId = $patrol?->id;

                // Tentukan perwakilan peserta dalam regu (PINRU, WAPINRU, atau anggota pertama)
                $representative = $patrol?->participants
                    ?->first(fn ($p) => $p->isLeader() || $p->isAssistantLeader())
                    ?? $patrol?->participants?->first();

                $participantId = $representative?->id;
                $entityType = 'participant';
            } elseif ($entityType === 'participant') {
                $participant = Participant::find($entityId);
                $participantId = $participant?->id;
                $patrolId = $participant?->patrol_id;
            } elseif ($entityType === 'chaperone') {
                $chaperone = Chaperone::find($entityId);
                $chaperoneId = $chaperone?->id;
            } elseif ($entityType === 'user') {
                $user = User::find($entityId);
                $userId = $user?->id;
            }

            return Attendance::create([
                'attendance_session_id' => $session->id,
                'entity_type' => $entityType,
                'participant_id' => $participantId,
                'patrol_id' => $patrolId,
                'chaperone_id' => $chaperoneId,
                'user_id' => $userId,
                'scanned_by_user_id' => $admin?->id,
                'scanned_at' => $scannedAt,
                'status' => $status,
                'notes' => $notes,
                'device_info' => 'Matriks Penyesuaian Presensi (Admin)',
            ]);
        });
    }

    /**
     * Hapus catatan presensi (mengembalikan status menjadi belum presensi / kosong).
     */
    public function remove(AttendanceSession $session, string $entityType, string $entityId): bool
    {
        $existing = $this->findExistingAttendance($session->id, $entityType, $entityId);

        if ($existing) {
            return (bool) $existing->delete();
        }

        return false;
    }

    /**
     * Penyesuaian massal untuk beberapa entitas pada satu sesi.
     *
     * @param  array<int, string>  $entityIds
     */
    public function batchAdjust(
        AttendanceSession $session,
        string $entityType,
        array $entityIds,
        string $status,
        ?User $admin = null,
        ?string $notes = null
    ): int {
        $count = 0;

        DB::transaction(function () use ($session, $entityType, $entityIds, $status, $admin, $notes, &$count) {
            foreach ($entityIds as $entityId) {
                $this->adjust($session, $entityType, $entityId, $status, $admin, $notes);
                $count++;
            }
        });

        return $count;
    }

    /**
     * Penyesuaian massal untuk beberapa entitas pada beberapa sesi sekaligus.
     *
     * @param  array<int, string>  $sessionIds
     * @param  array<int, string>  $entityIds
     */
    public function batchAdjustMultipleSessions(
        array $sessionIds,
        string $entityType,
        array $entityIds,
        string $status,
        ?User $admin = null,
        ?string $notes = null
    ): int {
        $sessions = AttendanceSession::whereIn('id', $sessionIds)->get();
        $total = 0;

        DB::transaction(function () use ($sessions, $entityType, $entityIds, $status, $admin, $notes, &$total) {
            foreach ($sessions as $session) {
                foreach ($entityIds as $entityId) {
                    $this->adjust($session, $entityType, $entityId, $status, $admin, $notes);
                    $total++;
                }
            }
        });

        return $total;
    }

    /**
     * Ambil peta catatan presensi dalam format matriks terindeks O(1) untuk menghindari N+1 query.
     *
     * @param  array<int, string>  $sessionIds
     * @param  array<int, string>  $entityIds
     * @return array<string, Attendance> [entityId_sessionId => Attendance]
     */
    public function getMatrixMap(array $sessionIds, string $scope, array $entityIds): array
    {
        if (empty($sessionIds) || empty($entityIds)) {
            return [];
        }

        $query = Attendance::whereIn('attendance_session_id', $sessionIds);

        if ($scope === 'patrol') {
            $query->whereIn('patrol_id', $entityIds);
        } elseif ($scope === 'participant') {
            $query->whereIn('participant_id', $entityIds);
        } elseif ($scope === 'chaperone') {
            $query->whereIn('chaperone_id', $entityIds);
        } elseif ($scope === 'user') {
            $query->whereIn('user_id', $entityIds);
        }

        $attendances = $query->with('participant')->get();

        $map = [];
        foreach ($attendances as $att) {
            $keyId = match ($scope) {
                'patrol' => $att->patrol_id,
                'participant' => $att->participant_id,
                'chaperone' => $att->chaperone_id,
                'user' => $att->user_id,
                default => null,
            };

            if ($keyId) {
                $map["{$keyId}_{$att->attendance_session_id}"] = $att;
            }
        }

        return $map;
    }

    /**
     * Cari presensi yang sudah tercatat berdasarkan tipe entitas
     */
    protected function findExistingAttendance(string $sessionId, string $entityType, string $entityId): ?Attendance
    {
        $query = Attendance::where('attendance_session_id', $sessionId);

        if ($entityType === 'patrol') {
            $query->where('patrol_id', $entityId);
        } elseif ($entityType === 'participant') {
            $query->where('participant_id', $entityId);
        } elseif ($entityType === 'chaperone') {
            $query->where('chaperone_id', $entityId);
        } elseif ($entityType === 'user') {
            $query->where('user_id', $entityId);
        }

        return $query->first();
    }
}

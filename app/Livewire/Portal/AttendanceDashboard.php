<?php

namespace App\Livewire\Portal;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\CompetitionSchedule;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Komponen Livewire 4 Dashboard Portal Presensi Pos & Operasional Lapangan
 */
#[Layout('layouts.app')]
#[Title('Portal Presensi & Scanner QR — Livewire 4')]
class AttendanceDashboard extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';

    // State Modal Tambah Sesi Presensi Kehadiran (Khusus Admin)
    public bool $showCreateModal = false;
    public string $newTitle = '';
    public string $newSessionType = 'participant';
    public string $newOpenedAt = '';
    public string $newClosedAt = '';
    public bool $newIsActive = true;

    // State Modal Edit Sesi Presensi Kehadiran (Khusus Admin)
    public bool $showEditModal = false;
    public ?string $editingSessionId = null;
    public string $editTitle = '';
    public string $editSessionType = 'participant';
    public ?string $editScheduleId = null;
    public string $editOpenedAt = '';
    public string $editClosedAt = '';
    public bool $editIsActive = true;

    // Properti Penyesuaian Jadwal Terkait (jika terhubung ke jadwal lomba)
    public string $editScheduleName = '';
    public string $editScheduleArena = '';
    public string $editScheduleStartTime = '';
    public string $editScheduleEndTime = '';
    public string $editScheduleGenderCategory = 'all';
    public string $editScheduleStatus = 'scheduled';

    // State Modal Log Presensi Interaktif
    public bool $showLogsModal = false;
    public ?string $selectedLogsSessionId = null;
    public string $logsSearch = '';
    public string $logsStatusFilter = 'all';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function stats(): array
    {
        $user = auth()->user();

        return [
            'total_sessions' => AttendanceSession::accessibleBy($user)->count(),
            'active_sessions' => AttendanceSession::accessibleBy($user)->where('is_active', true)->count(),
            'total_attendances_today' => Attendance::whereHas('session', fn($q) => $q->accessibleBy($user))->whereDate('scanned_at', today())->count(),
        ];
    }

    #[Computed]
    public function sessions()
    {
        $user = auth()->user();

        $query = AttendanceSession::accessibleBy($user)
            ->with(['schedule.competition', 'attendances'])
            ->orderByDesc('is_active')
            ->orderBy('title');

        if ($this->statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        if (! empty($this->search)) {
            $term = $this->search;
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhereHas('schedule', function ($sq) use ($term) {
                        $sq->where('arena_location', 'like', "%{$term}%")
                            ->orWhere('session_name', 'like', "%{$term}%")
                            ->orWhereHas('competition', function ($cq) use ($term) {
                                $cq->where('title', 'like', "%{$term}%")
                                    ->orWhere('code', 'like', "%{$term}%");
                            });
                    });
            });
        }

        return $query->paginate(12);
    }

    /**
     * Buka / Tutup Sesi Presensi Pos secara instan
     */
    public function toggleSession(string $sessionId): void
    {
        $session = AttendanceSession::findOrFail($sessionId);

        if (! $session->canBeAccessedBy(auth()->user())) {
            abort(403, 'Anda tidak berwenang mengelola status sesi presensi ini.');
        }

        $newStatus = ! $session->is_active;

        $session->update([
            'is_active' => $newStatus,
            'opened_at' => $newStatus ? now()->setTimezone('Asia/Jakarta') : $session->opened_at,
            'closed_at' => ! $newStatus ? now()->setTimezone('Asia/Jakarta') : null,
        ]);

        $label = $newStatus ? 'DIBUKA' : 'DITUTUP';
        Flux::toast(
            variant: $newStatus ? 'success' : 'warning',
            heading: 'Sesi Presensi Diperbarui',
            text: "Sesi \"{$session->title}\" kini {$label} untuk pemindaian QR."
        );
    }

    /**
     * Tambah Sesi Presensi Kehadiran Baru (Bukan Lomba) oleh Administrator
     */
    public function createAttendanceSession(): void
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Hanya Administrator yang berwenang menambahkan sesi presensi kehadiran.');
        }

        $this->validate([
            'newTitle' => 'required|string|min:3|max:255',
            'newSessionType' => 'required|in:participant,chaperone,committee,judge',
            'newOpenedAt' => 'nullable|date',
            'newClosedAt' => 'nullable|date|after_or_equal:newOpenedAt',
        ]);

        AttendanceSession::create([
            'competition_schedule_id' => null,
            'is_competition' => false,
            'session_type' => $this->newSessionType,
            'title' => $this->newTitle,
            'opened_at' => $this->newOpenedAt ? \Carbon\Carbon::parse($this->newOpenedAt) : now(),
            'closed_at' => $this->newClosedAt ? \Carbon\Carbon::parse($this->newClosedAt) : null,
            'is_active' => $this->newIsActive,
        ]);

        $this->showCreateModal = false;
        $this->reset(['newTitle', 'newSessionType', 'newOpenedAt', 'newClosedAt', 'newIsActive']);
        Flux::toast(variant: 'success', heading: 'Sesi Kehadiran Dibuat', text: 'Sesi presensi kehadiran umum berhasil ditambahkan.');
    }

    /**
     * Buka modal edit sesi presensi kehadiran beserta data jadwal terkait
     */
    public function openEditModal(string $sessionId): void
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Hanya Administrator yang berwenang mengubah sesi presensi.');
        }

        $session = AttendanceSession::with('schedule.competition')->findOrFail($sessionId);
        $this->editingSessionId = $session->id;
        $this->editTitle = $session->title;
        $this->editSessionType = $session->session_type;
        $this->editScheduleId = $session->competition_schedule_id;
        $this->editOpenedAt = $session->opened_at ? $session->opened_at->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i') : '';
        $this->editClosedAt = $session->closed_at ? $session->closed_at->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i') : '';
        $this->editIsActive = (bool) $session->is_active;

        if ($session->schedule) {
            $this->editScheduleName = $session->schedule->session_name ?? '';
            $this->editScheduleArena = $session->schedule->arena_location ?? '';
            $this->editScheduleStartTime = $session->schedule->start_time ? $session->schedule->start_time->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i') : '';
            $this->editScheduleEndTime = $session->schedule->end_time ? $session->schedule->end_time->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i') : '';
            $this->editScheduleGenderCategory = $session->schedule->gender_category ?? 'all';
            $this->editScheduleStatus = $session->schedule->status ?? 'scheduled';
        } else {
            $this->editScheduleName = '';
            $this->editScheduleArena = '';
            $this->editScheduleStartTime = '';
            $this->editScheduleEndTime = '';
            $this->editScheduleGenderCategory = 'all';
            $this->editScheduleStatus = 'scheduled';
        }

        $this->showEditModal = true;
    }

    /**
     * Auto-sync rincian jadwal ketika admin memilih jadwal lomba tertentu
     */
    public function updatedEditScheduleId(?string $value): void
    {
        if ($value) {
            $sch = CompetitionSchedule::with('competition')->find($value);
            if ($sch) {
                $this->editScheduleName = $sch->session_name ?? '';
                $this->editScheduleArena = $sch->arena_location ?? '';
                $this->editScheduleStartTime = $sch->start_time ? $sch->start_time->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i') : '';
                $this->editScheduleEndTime = $sch->end_time ? $sch->end_time->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i') : '';
                $this->editScheduleGenderCategory = $sch->gender_category ?? 'all';
                $this->editScheduleStatus = $sch->status ?? 'scheduled';
            }
        }
    }

    /**
     * Simpan pembaruan data sesi presensi dan koreksi jadwal lomba
     */
    public function updateAttendanceSession(): void
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Hanya Administrator yang berwenang mengubah sesi presensi.');
        }

        $this->validate([
            'editTitle' => 'required|string|min:3|max:255',
            'editSessionType' => 'required|in:participant,chaperone,committee,judge',
            'editScheduleId' => 'nullable|exists:competition_schedules,id',
            'editOpenedAt' => 'nullable|date',
            'editClosedAt' => 'nullable|date|after_or_equal:editOpenedAt',
            'editScheduleStartTime' => 'nullable|date',
            'editScheduleEndTime' => 'nullable|date|after:editScheduleStartTime',
            'editScheduleGenderCategory' => 'nullable|in:boys,girls,all',
            'editScheduleArena' => 'nullable|string|max:255',
            'editScheduleStatus' => 'nullable|in:scheduled,in_progress,completed,rescheduled,cancelled',
        ]);

        $session = AttendanceSession::findOrFail($this->editingSessionId);

        $session->update([
            'title' => $this->editTitle,
            'session_type' => $this->editSessionType,
            'is_competition' => ! empty($this->editScheduleId),
            'competition_schedule_id' => $this->editScheduleId ?: null,
            'opened_at' => $this->editOpenedAt ? \Carbon\Carbon::parse($this->editOpenedAt) : null,
            'closed_at' => $this->editClosedAt ? \Carbon\Carbon::parse($this->editClosedAt) : null,
            'is_active' => $this->editIsActive,
        ]);

        // Jika terhubung ke jadwal lomba, koreksi jadwalnya juga
        if ($session->competition_schedule_id) {
            $schedule = CompetitionSchedule::find($session->competition_schedule_id);
            if ($schedule) {
                $scheduleUpdate = [];
                if (! empty($this->editScheduleName)) {
                    $scheduleUpdate['session_name'] = $this->editScheduleName;
                }
                if (! empty($this->editScheduleArena)) {
                    $scheduleUpdate['arena_location'] = $this->editScheduleArena;
                }
                if (! empty($this->editScheduleStartTime)) {
                    $scheduleUpdate['start_time'] = \Carbon\Carbon::parse($this->editScheduleStartTime);
                }
                if (! empty($this->editScheduleEndTime)) {
                    $scheduleUpdate['end_time'] = \Carbon\Carbon::parse($this->editScheduleEndTime);
                }
                if (! empty($this->editScheduleGenderCategory)) {
                    $scheduleUpdate['gender_category'] = $this->editScheduleGenderCategory;
                }
                if (! empty($this->editScheduleStatus)) {
                    $scheduleUpdate['status'] = $this->editScheduleStatus;
                }
                if (! empty($scheduleUpdate)) {
                    $schedule->update($scheduleUpdate);
                }
            }
        }

        $this->showEditModal = false;
        $this->reset([
            'editingSessionId',
            'editTitle',
            'editSessionType',
            'editScheduleId',
            'editOpenedAt',
            'editClosedAt',
            'editIsActive',
            'editScheduleName',
            'editScheduleArena',
            'editScheduleStartTime',
            'editScheduleEndTime',
            'editScheduleGenderCategory',
            'editScheduleStatus',
        ]);

        Flux::toast(
            variant: 'success',
            heading: 'Sesi Presensi Diperbarui',
            text: "Perubahan sesi presensi dan jadwal berhasil disimpan."
        );
    }

    /**
     * Hapus sesi presensi oleh Administrator
     */
    public function deleteAttendanceSession(string $sessionId): void
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Hanya Administrator yang berwenang menghapus sesi presensi.');
        }

        $session = AttendanceSession::findOrFail($sessionId);
        $title = $session->title;
        $session->delete();

        if ($this->showEditModal && $this->editingSessionId === $sessionId) {
            $this->showEditModal = false;
        }

        Flux::toast(
            variant: 'warning',
            heading: 'Sesi Presensi Dihapus',
            text: "Sesi \"{$title}\" berhasil dihapus dari sistem."
        );
    }

    /**
     * Buka modal visual log presensi sesi tertentu
     */
    public function openLogsModal(string $sessionId): void
    {
        $session = AttendanceSession::findOrFail($sessionId);

        if (! $session->canBeAccessedBy(auth()->user())) {
            abort(403, 'Anda tidak berwenang melihat log presensi pada sesi ini.');
        }

        $this->selectedLogsSessionId = $session->id;
        $this->logsSearch = '';
        $this->logsStatusFilter = 'all';
        $this->showLogsModal = true;
    }

    /**
     * Perbarui status presensi rekaman tertentu (misal: hadir, terlambat, izin)
     */
    public function updateAttendanceStatus(string $attendanceId, string $status): void
    {
        $attendance = Attendance::findOrFail($attendanceId);

        if (! $attendance->session?->canBeAccessedBy(auth()->user())) {
            abort(403, 'Anda tidak berwenang memperbarui presensi pada sesi ini.');
        }

        $attendance->update([
            'status' => $status,
        ]);

        Flux::toast(
            variant: 'success',
            heading: 'Status Presensi Diperbarui',
            text: "Status presensi untuk {$attendance->entity_name} diubah menjadi {$attendance->statusLabel()}."
        );
    }

    /**
     * Hapus rekaman presensi log tertentu oleh Administrator
     */
    public function deleteAttendanceRecord(string $attendanceId): void
    {
        $attendance = Attendance::findOrFail($attendanceId);

        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Hanya Administrator yang berwenang menghapus rekaman presensi.');
        }

        $name = $attendance->entity_name;
        $attendance->delete();

        Flux::toast(
            variant: 'warning',
            heading: 'Presensi Dihapus',
            text: "Data presensi untuk {$name} berhasil dihapus."
        );
    }

    #[Computed]
    public function selectedLogsSession(): ?AttendanceSession
    {
        if (! $this->selectedLogsSessionId) {
            return null;
        }

        return AttendanceSession::with([
            'schedule.competition',
            'attendances',
        ])->find($this->selectedLogsSessionId);
    }

    #[Computed]
    public function sessionLogs()
    {
        if (! $this->selectedLogsSessionId) {
            return collect();
        }

        $query = Attendance::with([
            'participant.patrol.scoutGroup',
            'chaperone.scoutGroup',
            'user',
            'scannedBy',
        ])
        ->where('attendance_session_id', $this->selectedLogsSessionId)
        ->orderByDesc('scanned_at');

        if ($this->logsStatusFilter !== 'all') {
            $query->where('status', $this->logsStatusFilter);
        }

        if (! empty($this->logsSearch)) {
            $term = $this->logsSearch;
            $query->where(function ($q) use ($term) {
                $q->where('notes', 'like', "%{$term}%")
                    ->orWhereHas('participant', function ($pq) use ($term) {
                        $pq->where('full_name', 'like', "%{$term}%")
                            ->orWhere('id_number', 'like', "%{$term}%")
                            ->orWhereHas('patrol', function ($ptq) use ($term) {
                                $ptq->where('patrol_name', 'like', "%{$term}%")
                                    ->orWhereHas('scoutGroup', fn($sq) => $sq->where('school_name', 'like', "%{$term}%"));
                            });
                    })
                    ->orWhereHas('chaperone', function ($cq) use ($term) {
                        $cq->where('full_name', 'like', "%{$term}%")
                            ->orWhere('id_number', 'like', "%{$term}%")
                            ->orWhereHas('scoutGroup', fn($sq) => $sq->where('school_name', 'like', "%{$term}%"));
                    })
                    ->orWhereHas('user', function ($uq) use ($term) {
                        $uq->where('name', 'like', "%{$term}%")
                            ->orWhere('id_number', 'like', "%{$term}%");
                    });
            });
        }

        return $query->get();
    }

    #[Computed]
    public function availableSchedules()
    {
        return CompetitionSchedule::with('competition')
            ->orderBy('start_time')
            ->get();
    }

    public function render()
    {
        return view('livewire.portal.attendance-dashboard', [
            'sessions' => $this->sessions,
            'stats' => $this->stats,
        ]);
    }
}

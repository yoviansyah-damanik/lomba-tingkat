<?php

namespace App\Livewire\Portal;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Komponen Livewire 4 Rekapitulasi Presensi & Manajemen Data Kehadiran Pos / Acara
 */
#[Layout('layouts.app')]
#[Title('Rekapitulasi Presensi — Lomba Tingkat III')]
class AttendanceRecap extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'session')]
    public string $sessionId = 'all';

    #[Url(as: 'type')]
    public string $sessionType = 'all'; // all, competition, general

    #[Url(as: 'entity')]
    public string $entityType = 'all'; // all, participant, chaperone, user

    #[Url(as: 'status')]
    public string $statusFilter = 'all'; // all, present, late, excused, absent

    #[Url(as: 'date')]
    public string $dateFilter = '';

    // State Modal Edit Data Presensi
    public bool $showEditModal = false;
    public ?string $editingAttendanceId = null;
    public string $editEntityName = '';
    public string $editEntityRole = '';
    public string $editSessionTitle = '';
    public string $editStatus = 'present';
    public string $editNotes = '';
    public string $editScannedAt = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSessionId(): void
    {
        $this->resetPage();
    }

    public function updatedSessionType(): void
    {
        $this->resetPage();
    }

    public function updatedEntityType(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDateFilter(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'sessionId', 'sessionType', 'entityType', 'statusFilter', 'dateFilter']);
        $this->resetPage();
    }

    #[Computed]
    public function accessibleSessions()
    {
        return AttendanceSession::accessibleBy(auth()->user())
            ->with(['schedule.competition'])
            ->orderByDesc('is_active')
            ->orderBy('title')
            ->get();
    }

    #[Computed]
    public function stats(): array
    {
        $user = auth()->user();
        $baseQuery = Attendance::whereHas('session', fn($q) => $q->accessibleBy($user));

        if ($this->sessionId !== 'all') {
            $baseQuery->where('attendance_session_id', $this->sessionId);
        }

        if (! empty($this->dateFilter)) {
            $baseQuery->whereDate('scanned_at', $this->dateFilter);
        }

        return [
            'total' => (clone $baseQuery)->count(),
            'present' => (clone $baseQuery)->where('status', 'present')->count(),
            'late' => (clone $baseQuery)->where('status', 'late')->count(),
            'excused' => (clone $baseQuery)->where('status', 'excused')->count(),
            'absent' => (clone $baseQuery)->where('status', 'absent')->count(),
        ];
    }

    #[Computed]
    public function attendances()
    {
        $user = auth()->user();

        $query = Attendance::whereHas('session', fn($q) => $q->accessibleBy($user))
            ->with([
                'session.schedule.competition',
                'participant.patrol.scoutGroup',
                'chaperone.scoutGroup',
                'user',
                'scannedBy',
            ]);

        // Filter berdasarkan sesi presensi
        if ($this->sessionId !== 'all') {
            $query->where('attendance_session_id', $this->sessionId);
        }

        // Filter tipe sesi (Lomba vs Kehadiran Umum)
        if ($this->sessionType === 'competition') {
            $query->whereHas('session', fn($q) => $q->where('is_competition', true));
        } elseif ($this->sessionType === 'general') {
            $query->whereHas('session', fn($q) => $q->where(function ($sub) {
                $sub->whereNull('competition_schedule_id')
                    ->orWhere('is_competition', false);
            }));
        }

        // Filter tipe entitas (peserta, bindamping, user)
        if ($this->entityType !== 'all') {
            $query->where('entity_type', $this->entityType);
        }

        // Filter status kehadiran
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        // Filter tanggal presensi
        if (! empty($this->dateFilter)) {
            $query->whereDate('scanned_at', $this->dateFilter);
        }

        // Pencarian teks
        if (! empty($this->search)) {
            $term = $this->search;
            $query->where(function ($q) use ($term) {
                $q->whereHas('participant', function ($pq) use ($term) {
                    $pq->where('full_name', 'like', "%{$term}%")
                        ->orWhere('id_number', 'like', "%{$term}%")
                        ->orWhere('scout_id', 'like', "%{$term}%");
                })->orWhereHas('chaperone', function ($cq) use ($term) {
                    $cq->where('full_name', 'like', "%{$term}%")
                        ->orWhere('id_number', 'like', "%{$term}%")
                        ->orWhere('scout_id', 'like', "%{$term}%");
                })->orWhereHas('user', function ($uq) use ($term) {
                    $uq->where('name', 'like', "%{$term}%")
                        ->orWhere('id_number', 'like', "%{$term}%");
                })->orWhereHas('patrol', function ($ptq) use ($term) {
                    $ptq->where('patrol_name', 'like', "%{$term}%")
                        ->orWhereHas('scoutGroup', fn($sg) => $sg->where('school_name', 'like', "%{$term}%"));
                })->orWhere('notes', 'like', "%{$term}%");
            });
        }

        return $query->orderByDesc('scanned_at')->paginate(15);
    }

    /**
     * Buka modal untuk mengedit data presensi
     */
    public function openEditModal(string $attendanceId): void
    {
        $attendance = Attendance::with(['session', 'participant', 'chaperone', 'user'])->findOrFail($attendanceId);

        if (! $attendance->session?->canBeAccessedBy(auth()->user())) {
            Flux::toast(variant: 'danger', heading: 'Akses Ditolak', text: 'Anda tidak berwenang mengedit catatan presensi pada sesi ini.');
            return;
        }

        $this->editingAttendanceId = $attendance->id;
        $this->editEntityName = $attendance->entity_name;
        $this->editEntityRole = $attendance->entity_role . ' • ' . $attendance->entity_group;
        $this->editSessionTitle = $attendance->session?->title ?: 'Sesi Presensi';
        $this->editStatus = $attendance->status ?: 'present';
        $this->editNotes = $attendance->notes ?: '';
        $this->editScannedAt = $attendance->scanned_at ? $attendance->scanned_at->format('Y-m-d\TH:i') : '';
        $this->showEditModal = true;
    }

    /**
     * Simpan perubahan edit data presensi
     */
    public function saveEdit(): void
    {
        if (! $this->editingAttendanceId) {
            return;
        }

        $attendance = Attendance::findOrFail($this->editingAttendanceId);

        if (! $attendance->session?->canBeAccessedBy(auth()->user())) {
            Flux::toast(variant: 'danger', heading: 'Akses Ditolak', text: 'Anda tidak berwenang mengedit catatan presensi pada sesi ini.');
            return;
        }

        $this->validate([
            'editStatus' => 'required|string|in:present,late,excused,absent',
            'editNotes' => 'nullable|string|max:255',
            'editScannedAt' => 'nullable|date',
        ]);

        $updateData = [
            'status' => $this->editStatus,
            'notes' => $this->editNotes,
        ];

        if (! empty($this->editScannedAt)) {
            $updateData['scanned_at'] = \Carbon\Carbon::parse($this->editScannedAt);
        }

        $attendance->update($updateData);

        $this->showEditModal = false;
        $this->reset(['editingAttendanceId', 'editEntityName', 'editEntityRole', 'editSessionTitle', 'editStatus', 'editNotes', 'editScannedAt']);

        Flux::toast(
            variant: 'success',
            heading: 'Data Presensi Diperbarui',
            text: "Presensi {$attendance->entity_name} berhasil disimpan."
        );
    }

    /**
     * Ubah status kehadiran secara instan
     */
    public function quickUpdateStatus(string $attendanceId, string $status): void
    {
        $attendance = Attendance::findOrFail($attendanceId);

        if (! $attendance->session?->canBeAccessedBy(auth()->user())) {
            Flux::toast(variant: 'danger', heading: 'Akses Ditolak', text: 'Anda tidak berwenang memperbarui presensi ini.');
            return;
        }

        if (! in_array($status, ['present', 'late', 'excused', 'absent'])) {
            return;
        }

        $attendance->update(['status' => $status]);

        $statusLabels = [
            'present' => 'HADIR',
            'late' => 'TERLAMBAT',
            'excused' => 'IZIN / DISPENSASI',
            'absent' => 'ALPA / TIDAK HADIR',
        ];

        Flux::toast(
            variant: 'success',
            heading: 'Status Presensi Berubah',
            text: "Status {$attendance->entity_name} diubah menjadi {$statusLabels[$status]}."
        );
    }

    /**
     * Hapus / batalkan catatan presensi
     */
    public function deleteAttendance(string $attendanceId): void
    {
        $attendance = Attendance::findOrFail($attendanceId);

        if (! $attendance->session?->canBeAccessedBy(auth()->user())) {
            Flux::toast(variant: 'danger', heading: 'Akses Ditolak', text: 'Anda tidak berwenang membatalkan presensi pada sesi ini.');
            return;
        }

        $name = $attendance->entity_name;
        $attendance->delete();

        Flux::toast(
            variant: 'warning',
            heading: 'Presensi Dibatalkan',
            text: "Catatan presensi {$name} telah dihapus dari sistem."
        );
    }

    public function render()
    {
        return view('livewire.portal.attendance-recap', [
            'attendances' => $this->attendances,
            'sessions' => $this->accessibleSessions,
            'stats' => $this->stats,
        ]);
    }
}

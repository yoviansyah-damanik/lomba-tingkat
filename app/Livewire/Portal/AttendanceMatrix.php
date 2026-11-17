<?php

namespace App\Livewire\Portal;

use App\Enums\RoleEnum;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Chaperone;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\User;
use App\Services\AttendanceAdjustmentService;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Komponen Livewire 4 Matriks Penyesuaian Presensi untuk Administrator
 */
#[Layout('layouts.app')]
#[Title('Matriks Penyesuaian Presensi — Lomba Tingkat III')]
class AttendanceMatrix extends Component
{
    use WithPagination;

    #[Url(as: 'scope')]
    public string $scope = 'patrol'; // 'patrol', 'participant', 'chaperone', 'committee', 'judge'

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'gender')]
    public string $genderFilter = 'all'; // 'all', 'boys', 'girls'

    #[Url(as: 'type')]
    public string $sessionTypeFilter = 'all'; // 'all', 'competition', 'general'

    #[Url(as: 'date')]
    public string $sessionDateFilter = 'all'; // 'all' atau 'Y-m-d'

    public int $perPage = 25;

    /** @var array<int, string> */
    public array $selectedEntityIds = [];

    // State Modal Penyesuaian Sel Satuan
    public bool $showCellModal = false;

    public string $cellEntityId = '';

    public string $cellEntityType = 'patrol';

    public string $cellEntityName = '';

    public string $cellEntityInfo = '';

    public string $cellSessionId = '';

    public string $cellSessionTitle = '';

    public string $cellStatus = 'present';

    public string $cellNotes = '';

    public string $cellScannedAt = '';

    public bool $cellHasRecord = false;

    // State Modal Penyesuaian Massal (Batch Selected Rows)
    public bool $showBatchModal = false;

    public string $batchTargetSessionId = '';

    public string $batchStatus = 'present';

    public string $batchNotes = '';

    // State Modal Penyesuaian Seluruh Sesi (Kolom)
    public bool $showSessionBulkModal = false;

    public string $sessionBulkTargetSessionId = '';

    public string $sessionBulkStatus = 'present';

    public bool $sessionBulkOnlyUnrecorded = true;

    public string $sessionBulkNotes = '';

    public function mount(): void
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Akses ditolak. Modul matriks penyesuaian presensi hanya dapat diakses oleh Administrator.');
        }
    }

    public function updatedScope(): void
    {
        $this->resetPage();
        $this->selectedEntityIds = [];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedGenderFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSessionTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSessionDateFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Daftar sesi presensi yang relevan sesuai filter
     */
    #[Computed]
    public function sessions(): Collection
    {
        return AttendanceSession::query()
            ->with(['schedule.competition'])
            ->when($this->scope === 'committee', function ($q) {
                if ($this->sessionTypeFilter === 'all') {
                    $q->where(function ($sub) {
                        $sub->where('session_type', 'committee')
                            ->orWhere('is_competition', false);
                    });
                }
            })
            ->when($this->scope === 'judge', function ($q) {
                if ($this->sessionTypeFilter === 'all') {
                    $q->where(function ($sub) {
                        $sub->where('session_type', 'judge')
                            ->orWhere('is_competition', true);
                    });
                }
            })
            ->when($this->sessionTypeFilter === 'competition', fn ($q) => $q->where('is_competition', true))
            ->when($this->sessionTypeFilter === 'general', fn ($q) => $q->where('is_competition', false))
            ->when($this->sessionDateFilter !== 'all', function ($q) {
                $q->where(function ($sub) {
                    $sub->whereDate('opened_at', $this->sessionDateFilter)
                        ->orWhereHas('schedule', fn ($sq) => $sq->whereDate('date', $this->sessionDateFilter));
                });
            })
            ->orderByRaw('CASE WHEN competition_schedule_id IS NULL THEN 1 ELSE 0 END')
            ->orderBy('opened_at')
            ->get();
    }

    /**
     * Daftar tanggal unik dari seluruh sesi presensi
     */
    #[Computed]
    public function sessionDates(): Collection
    {
        $sessions = AttendanceSession::with('schedule')->get();

        return $sessions->map(function ($s) {
            $date = $s->opened_at?->format('Y-m-d') ?? $s->schedule?->date?->format('Y-m-d');

            return $date ? [
                'value' => $date,
                'label' => Carbon::parse($date)->translatedFormat('l, d F Y'),
            ] : null;
        })->filter()->unique('value')->values();
    }

    /**
     * Daftar entitas terpaginasi sesuai cakupan (Regu / Peserta / Pembina)
     */
    #[Computed]
    public function entities(): LengthAwarePaginator
    {
        $term = trim($this->search);

        if ($this->scope === 'patrol') {
            return Patrol::query()
                ->with(['scoutGroup', 'participants'])
                ->when($this->genderFilter !== 'all', fn ($q) => $q->where('gender_category', $this->genderFilter))
                ->when($term !== '', function ($q) use ($term) {
                    $q->where(function ($sub) use ($term) {
                        $sub->where('patrol_name', 'like', "%{$term}%")
                            ->orWhere('npp', 'like', "%{$term}%")
                            ->orWhereHas('scoutGroup', function ($sg) use ($term) {
                                $sg->where('school_name', 'like', "%{$term}%")
                                    ->orWhere('kwarran_name', 'like', "%{$term}%")
                                    ->orWhere('group_number', 'like', "%{$term}%");
                            });
                    });
                })
                ->orderByRaw('CASE WHEN npp IS NULL THEN 1 ELSE 0 END, npp ASC, patrol_name ASC')
                ->paginate($this->perPage);
        }

        if ($this->scope === 'participant') {
            return Participant::query()
                ->with(['patrol.scoutGroup'])
                ->when($this->genderFilter === 'boys', fn ($q) => $q->whereHas('patrol', fn ($pq) => $pq->where('gender_category', 'boys')))
                ->when($this->genderFilter === 'girls', fn ($q) => $q->whereHas('patrol', fn ($pq) => $pq->where('gender_category', 'girls')))
                ->when($term !== '', function ($q) use ($term) {
                    $q->where(function ($sub) use ($term) {
                        $sub->where('full_name', 'like', "%{$term}%")
                            ->orWhere('id_number', 'like', "%{$term}%")
                            ->orWhere('scout_id', 'like', "%{$term}%")
                            ->orWhereHas('patrol', function ($pq) use ($term) {
                                $pq->where('patrol_name', 'like', "%{$term}%")
                                    ->orWhere('npp', 'like', "%{$term}%")
                                    ->orWhereHas('scoutGroup', fn ($sg) => $sg->where('school_name', 'like', "%{$term}%"));
                            });
                    });
                })
                ->orderBy('full_name')
                ->paginate($this->perPage);
        }

        if ($this->scope === 'chaperone') {
            return Chaperone::query()
                ->with('scoutGroup')
                ->when($this->genderFilter === 'boys', fn ($q) => $q->where('gender', 'male'))
                ->when($this->genderFilter === 'girls', fn ($q) => $q->where('gender', 'female'))
                ->when($term !== '', function ($q) use ($term) {
                    $q->where(function ($sub) use ($term) {
                        $sub->where('full_name', 'like', "%{$term}%")
                            ->orWhere('id_number', 'like', "%{$term}%")
                            ->orWhere('scout_id', 'like', "%{$term}%")
                            ->orWhereHas('scoutGroup', fn ($sg) => $sg->where('school_name', 'like', "%{$term}%"));
                    });
                })
                ->orderBy('full_name')
                ->paginate($this->perPage);
        }

        if ($this->scope === 'committee') {
            return User::query()
                ->whereHas('roles', fn ($q) => $q->where('name', RoleEnum::Committee->value))
                ->where('is_active', true)
                ->when($term !== '', function ($q) use ($term) {
                    $q->where(function ($sub) use ($term) {
                        $sub->where('name', 'like', "%{$term}%")
                            ->orWhere('username', 'like', "%{$term}%")
                            ->orWhere('id_number', 'like', "%{$term}%")
                            ->orWhere('institution', 'like', "%{$term}%")
                            ->orWhere('position', 'like', "%{$term}%")
                            ->orWhere('committee_position', 'like', "%{$term}%");
                    });
                })
                ->orderBy('name')
                ->paginate($this->perPage);
        }

        // Default: judge
        return User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', RoleEnum::Judge->value))
            ->where('is_active', true)
            ->when($term !== '', function ($q) use ($term) {
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', "%{$term}%")
                        ->orWhere('username', 'like', "%{$term}%")
                        ->orWhere('id_number', 'like', "%{$term}%")
                        ->orWhere('institution', 'like', "%{$term}%")
                        ->orWhere('position', 'like', "%{$term}%");
                });
            })
            ->orderBy('name')
            ->paginate($this->perPage);
    }

    /**
     * Peta matriks presensi untuk seluruh sel yang sedang ditampilkan pada halaman aktif
     *
     * @return array<string, Attendance>
     */
    #[Computed]
    public function matrixMap(): array
    {
        $sessionIds = $this->sessions->pluck('id')->all();
        $entityIds = $this->entities->pluck('id')->all();
        $queryScope = in_array($this->scope, ['committee', 'judge'], true) ? 'user' : $this->scope;

        return app(AttendanceAdjustmentService::class)->getMatrixMap($sessionIds, $queryScope, $entityIds);
    }

    /**
     * Buka modal penyesuaian detail untuk satu sel
     */
    public function openCellModal(string $entityId, string $sessionId): void
    {
        $session = AttendanceSession::with('schedule.competition')->findOrFail($sessionId);

        $this->cellEntityId = $entityId;
        $this->cellSessionId = $sessionId;
        $this->cellSessionTitle = $session->title;
        $this->cellEntityType = in_array($this->scope, ['committee', 'judge'], true) ? 'user' : $this->scope;

        if ($this->scope === 'patrol') {
            $patrol = Patrol::with('scoutGroup')->findOrFail($entityId);
            $this->cellEntityName = $patrol->patrol_name;
            $this->cellEntityInfo = "NPP: {$patrol->npp} • {$patrol->scoutGroup?->school_name} (Gudep {$patrol->group_number})";
        } elseif ($this->scope === 'participant') {
            $participant = Participant::with('patrol.scoutGroup')->findOrFail($entityId);
            $this->cellEntityName = $participant->full_name;
            $this->cellEntityInfo = "{$participant->patrol?->patrol_name} • {$participant->patrol?->scoutGroup?->school_name}";
        } elseif ($this->scope === 'chaperone') {
            $chaperone = Chaperone::with('scoutGroup')->findOrFail($entityId);
            $this->cellEntityName = $chaperone->full_name;
            $this->cellEntityInfo = "Pembina Pendamping • {$chaperone->scoutGroup?->school_name}";
        } elseif ($this->scope === 'committee') {
            $user = User::findOrFail($entityId);
            $this->cellEntityName = $user->name;
            $pos = $user->committee_position ?: $user->position ?: 'Panitia Pelaksana';
            $inst = $user->institution ? " • {$user->institution}" : '';
            $this->cellEntityInfo = "Panitia ({$pos}){$inst} • ID: {$user->id_number}";
        } else {
            $user = User::findOrFail($entityId);
            $this->cellEntityName = $user->name;
            $pos = $user->position ?: 'Dewan Juri';
            $inst = $user->institution ? " • {$user->institution}" : '';
            $this->cellEntityInfo = "Dewan Juri ({$pos}){$inst} • ID: {$user->id_number}";
        }

        $key = "{$entityId}_{$sessionId}";
        $record = $this->matrixMap[$key] ?? null;

        if ($record) {
            $this->cellHasRecord = true;
            $this->cellStatus = $record->status ?: 'present';
            $this->cellNotes = $record->notes ?: '';
            $this->cellScannedAt = $record->scanned_at ? $record->scanned_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i');
        } else {
            $this->cellHasRecord = false;
            $this->cellStatus = 'present';
            $this->cellNotes = 'Penyesuaian susulan kehadiran oleh Administrator';
            $this->cellScannedAt = now()->format('Y-m-d\TH:i');
        }

        $this->showCellModal = true;
    }

    /**
     * Simpan penyesuaian presensi dari modal sel
     */
    public function saveCellAdjustment(AttendanceAdjustmentService $adjustmentService): void
    {
        $session = AttendanceSession::findOrFail($this->cellSessionId);

        $this->validate([
            'cellStatus' => 'required|in:present,late,excused,absent',
            'cellNotes' => 'nullable|string|max:255',
            'cellScannedAt' => 'nullable|date',
        ]);

        $scannedAt = ! empty($this->cellScannedAt) ? Carbon::parse($this->cellScannedAt) : now();

        $adjustmentService->adjust(
            session: $session,
            entityType: $this->cellEntityType,
            entityId: $this->cellEntityId,
            status: $this->cellStatus,
            admin: auth()->user(),
            notes: $this->cellNotes,
            scannedAt: $scannedAt
        );

        $this->showCellModal = false;

        Flux::toast(
            variant: 'success',
            heading: 'Presensi Disimpan',
            text: "Penyesuaian kehadiran {$this->cellEntityName} pada sesi {$session->title} berhasil diperbarui."
        );
    }

    /**
     * Hapus catatan presensi (kembalikan ke status belum presensi)
     */
    public function deleteCellAttendance(AttendanceAdjustmentService $adjustmentService): void
    {
        $session = AttendanceSession::findOrFail($this->cellSessionId);

        $adjustmentService->remove($session, $this->cellEntityType, $this->cellEntityId);

        $this->showCellModal = false;

        Flux::toast(
            variant: 'warning',
            heading: 'Catatan Presensi Dihapus',
            text: "Presensi {$this->cellEntityName} pada sesi {$session->title} telah direset."
        );
    }

    /**
     * Aksi cepat 1-klik untuk mengubah status langsung dari sel matriks
     */
    public function quickSetStatus(string $entityId, string $sessionId, string $status, AttendanceAdjustmentService $adjustmentService): void
    {
        $session = AttendanceSession::findOrFail($sessionId);
        $entityType = in_array($this->scope, ['committee', 'judge'], true) ? 'user' : $this->scope;

        $adjustmentService->adjust(
            session: $session,
            entityType: $entityType,
            entityId: $entityId,
            status: $status,
            admin: auth()->user(),
            notes: 'Penyesuaian cepat matriks presensi'
        );

        Flux::toast(
            variant: 'success',
            heading: 'Status Diperbarui',
            text: 'Kehadiran berhasil ditandai sebagai '.strtoupper($status).'.'
        );
    }

    /**
     * Buka modal penyesuaian massal untuk baris-baris terpilih
     */
    public function openBatchModal(): void
    {
        if (empty($this->selectedEntityIds)) {
            Flux::toast(variant: 'warning', heading: 'Pilih Baris', text: 'Pilih minimal satu baris regu/peserta untuk penyesuaian massal.');

            return;
        }

        $this->batchTargetSessionId = (string) ($this->sessions->first()?->id ?? '');
        $this->batchStatus = 'present';
        $this->batchNotes = 'Penyesuaian massal kehadiran oleh Administrator';
        $this->showBatchModal = true;
    }

    /**
     * Eksekusi penyesuaian massal untuk baris terpilih
     */
    public function executeBatchAdjustment(AttendanceAdjustmentService $adjustmentService): void
    {
        $this->validate([
            'batchTargetSessionId' => 'required|exists:attendance_sessions,id',
            'batchStatus' => 'required|in:present,late,excused,absent',
            'batchNotes' => 'nullable|string|max:255',
        ]);

        $session = AttendanceSession::findOrFail($this->batchTargetSessionId);
        $entityType = in_array($this->scope, ['committee', 'judge'], true) ? 'user' : $this->scope;

        $affected = $adjustmentService->batchAdjust(
            session: $session,
            entityType: $entityType,
            entityIds: $this->selectedEntityIds,
            status: $this->batchStatus,
            admin: auth()->user(),
            notes: $this->batchNotes
        );

        $this->showBatchModal = false;
        $this->selectedEntityIds = [];

        Flux::toast(
            variant: 'success',
            heading: 'Penyesuaian Massal Berhasil',
            text: "Sebanyak {$affected} data presensi pada sesi \"{$session->title}\" berhasil diperbarui."
        );
    }

    /**
     * Buka modal konfirmasi untuk menandai seluruh kolom sesi
     */
    public function openSessionBulkModal(string $sessionId): void
    {
        $this->sessionBulkTargetSessionId = $sessionId;
        $this->sessionBulkStatus = 'present';
        $this->sessionBulkOnlyUnrecorded = true;
        $this->sessionBulkNotes = 'Penyesuaian massal sesi oleh Administrator';
        $this->showSessionBulkModal = true;
    }

    /**
     * Eksekusi penyesuaian untuk seluruh entitas pada kolom sesi
     */
    public function executeSessionBulkAction(AttendanceAdjustmentService $adjustmentService): void
    {
        $session = AttendanceSession::findOrFail($this->sessionBulkTargetSessionId);
        $entityType = in_array($this->scope, ['committee', 'judge'], true) ? 'user' : $this->scope;

        $query = match ($this->scope) {
            'patrol' => Patrol::query()->when($this->genderFilter !== 'all', fn ($q) => $q->where('gender_category', $this->genderFilter)),
            'participant' => Participant::query()->when($this->genderFilter === 'boys', fn ($q) => $q->whereHas('patrol', fn ($pq) => $pq->where('gender_category', 'boys')))->when($this->genderFilter === 'girls', fn ($q) => $q->whereHas('patrol', fn ($pq) => $pq->where('gender_category', 'girls'))),
            'chaperone' => Chaperone::query()->when($this->genderFilter === 'boys', fn ($q) => $q->where('gender', 'male'))->when($this->genderFilter === 'girls', fn ($q) => $q->where('gender', 'female')),
            'committee' => User::query()->whereHas('roles', fn ($q) => $q->where('name', RoleEnum::Committee->value))->where('is_active', true),
            'judge' => User::query()->whereHas('roles', fn ($q) => $q->where('name', RoleEnum::Judge->value))->where('is_active', true),
        };

        $allEntityIds = $query->pluck('id')->all();

        if ($this->sessionBulkOnlyUnrecorded) {
            // Saring hanya entitas yang belum memiliki catatan presensi pada sesi ini
            $alreadyRecordedIds = Attendance::where('attendance_session_id', $session->id)
                ->pluck(match ($this->scope) {
                    'patrol' => 'patrol_id',
                    'participant' => 'participant_id',
                    'chaperone' => 'chaperone_id',
                    'committee', 'judge' => 'user_id',
                })
                ->filter()
                ->unique()
                ->all();

            $targetEntityIds = array_values(array_diff($allEntityIds, $alreadyRecordedIds));
        } else {
            $targetEntityIds = $allEntityIds;
        }

        $affected = $adjustmentService->batchAdjust(
            session: $session,
            entityType: $entityType,
            entityIds: $targetEntityIds,
            status: $this->sessionBulkStatus,
            admin: auth()->user(),
            notes: $this->sessionBulkNotes
        );

        $this->showSessionBulkModal = false;

        Flux::toast(
            variant: 'success',
            heading: 'Presensi Sesi Diselaraskan',
            text: "Sebanyak {$affected} data presensi pada sesi \"{$session->title}\" berhasil diperbarui."
        );
    }

    /**
     * Pilih seluruh baris pada halaman aktif
     */
    public function selectAllPageRows(): void
    {
        $this->selectedEntityIds = $this->entities->pluck('id')->map(fn ($id) => (string) $id)->all();
    }

    /**
     * Batalkan seluruh pilihan baris
     */
    public function deselectAllRows(): void
    {
        $this->selectedEntityIds = [];
    }

    public function render()
    {
        return view('livewire.portal.attendance-matrix', [
            'sessions' => $this->sessions,
            'entities' => $this->entities,
            'matrixMap' => $this->matrixMap,
            'sessionDates' => $this->sessionDates,
        ]);
    }
}

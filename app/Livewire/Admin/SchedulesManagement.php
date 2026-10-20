<?php

namespace App\Livewire\Admin;

use App\Enums\RoleEnum;
use App\Models\AttendanceSession;
use App\Models\Competition;
use App\Models\CompetitionSchedule;
use App\Models\JudgeAssignment;
use App\Models\User;
use App\Services\WebPushService;
use Carbon\Carbon;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Komponen Livewire 4 Manajemen Jadwal Lomba, Penugasan Juri & Sesi Presensi Pos
 */
#[Layout('layouts.app')]
#[Title('Jadwal & Penugasan Juri — Livewire 4')]
class SchedulesManagement extends Component
{
    use WithPagination;

    public string $search = '';
    public string $competitionFilter = 'all';
    public string $genderFilter = 'all';
    public string $statusFilter = 'all_status';

    // State Modal Form Jadwal
    public bool $showModal = false;
    public ?string $editingId = null;

    // Field Form Jadwal
    public string $competition_id = '';
    public string $session_name = '';
    public string $arena_location = '';
    public string $start_time = '';
    public string $end_time = '';
    public string $status = 'scheduled';
    public string $participant_type = 'patrol';
    public string $gender_category = 'all';
    public bool $is_published = true;

    // State Modal Penugasan Juri
    public bool $showJudgesModal = false;
    public ?string $selectedScheduleId = null;
    public string $selectedJudgeId = '';
    public string $judgeRole = 'scoring_judge';
    public string $judgeNotes = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'competitionFilter' => ['except' => 'all'],
        'genderFilter' => ['except' => 'all'],
        'statusFilter' => ['except' => 'all_status'],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCompetitionFilter(): void
    {
        $this->resetPage();
    }

    public function updatedGenderFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedCompetitionId($value): void
    {
        if ($value) {
            $comp = Competition::find($value);
            if ($comp) {
                if ($comp->participant_type) {
                    $this->participant_type = $comp->participant_type;
                }
                if (in_array($comp->category, ['boys', 'girls'])) {
                    $this->gender_category = $comp->category;
                }
            }
        }
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'total' => CompetitionSchedule::count(),
            'scheduled' => CompetitionSchedule::where('status', 'scheduled')->count(),
            'in_progress' => CompetitionSchedule::where('status', 'in_progress')->count(),
            'completed' => CompetitionSchedule::where('status', 'completed')->count(),
        ];
    }

    #[Computed]
    public function competitions()
    {
        return Competition::orderBy('title')->get();
    }

    #[Computed]
    public function availableJudges()
    {
        return User::whereHas('roles', function ($q) {
            $q->whereIn('name', [RoleEnum::Judge->value, RoleEnum::Committee->value]);
        })->where('is_active', true)->orderBy('name')->get();
    }

    #[Computed]
    public function selectedSchedule(): ?CompetitionSchedule
    {
        if (! $this->selectedScheduleId) {
            return null;
        }

        return CompetitionSchedule::with(['competition', 'judgeAssignments.judge', 'attendanceSessions'])
            ->find($this->selectedScheduleId);
    }

    #[Computed]
    public function schedules()
    {
        $query = CompetitionSchedule::with(['competition', 'judges', 'attendanceSessions'])
            ->orderBy('start_time');

        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('session_name', 'like', '%' . $this->search . '%')
                    ->orWhere('arena_location', 'like', '%' . $this->search . '%')
                    ->orWhereHas('competition', function ($sub) {
                        $sub->where('title', 'like', '%' . $this->search . '%')
                            ->orWhere('code', 'like', '%' . $this->search . '%');
                    });
            });
        }

        if ($this->competitionFilter !== 'all' && ! empty($this->competitionFilter)) {
            $query->where('competition_id', $this->competitionFilter);
        }

        if ($this->genderFilter !== 'all' && ! empty($this->genderFilter)) {
            $filter = $this->genderFilter;
            $query->where(function ($q) use ($filter) {
                $q->where('gender_category', $filter)
                    ->orWhere(function ($sub) use ($filter) {
                        $sub->where(function ($s) {
                            $s->where('gender_category', 'all')->orWhereNull('gender_category');
                        })->whereHas('competition', fn ($cq) => $cq->where('category', $filter));
                    });
            });
        }

        if ($this->statusFilter !== 'all_status' && ! empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        return $query->paginate(10);
    }

    /**
     * Buka modal untuk tambah jadwal baru
     */
    public function openCreateModal(): void
    {
        $this->resetForm();
        $firstComp = Competition::first();
        if ($firstComp) {
            $this->competition_id = $firstComp->id;
            $this->participant_type = $firstComp->participant_type;
            $this->gender_category = in_array($firstComp->category, ['boys', 'girls']) ? $firstComp->category : 'all';
        }

        $now = Carbon::now()->addDay()->setTime(8, 0);
        $this->start_time = $now->format('Y-m-d\TH:i');
        $this->end_time = $now->copy()->addHours(2)->format('Y-m-d\TH:i');

        $this->showModal = true;
    }

    /**
     * Buka modal untuk edit jadwal
     */
    public function openEditModal(string $id): void
    {
        $schedule = CompetitionSchedule::findOrFail($id);

        $this->editingId = $schedule->id;
        $this->competition_id = $schedule->competition_id;
        $this->session_name = $schedule->session_name;
        $this->arena_location = $schedule->arena_location;
        $this->start_time = Carbon::parse($schedule->start_time)->format('Y-m-d\TH:i');
        $this->end_time = Carbon::parse($schedule->end_time)->format('Y-m-d\TH:i');
        $this->status = $schedule->status;
        $this->participant_type = $schedule->participant_type ?? 'patrol';
        $this->gender_category = $schedule->gender_category ?? 'all';
        $this->is_published = (bool) $schedule->is_published;

        $this->showModal = true;
    }

    /**
     * Simpan jadwal (Create / Update)
     */
    public function saveSchedule(): void
    {
        $this->validate([
            'competition_id' => 'required|exists:competitions,id',
            'session_name' => 'required|string|max:255',
            'arena_location' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'status' => 'required|in:scheduled,in_progress,completed,rescheduled,cancelled',
            'participant_type' => 'nullable|in:patrol,group,individual',
            'gender_category' => 'required|in:boys,girls,all',
        ]);

        $data = [
            'competition_id' => $this->competition_id,
            'session_name' => $this->session_name,
            'arena_location' => $this->arena_location,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'status' => $this->status,
            'participant_type' => $this->participant_type,
            'gender_category' => $this->gender_category,
            'is_published' => $this->is_published,
        ];

        if ($this->editingId) {
            $schedule = CompetitionSchedule::findOrFail($this->editingId);
            $schedule->update($data);
            Flux::toast(variant: 'success', heading: 'Jadwal Diperbarui', text: "Jadwal {$this->session_name} berhasil disimpan.");
        } else {
            $schedule = CompetitionSchedule::create($data);
            $schedule->loadMissing('competition');
            $competitionTitle = $schedule->competition?->title ?: 'Lomba';

            $genderSuffix = match ($schedule->gender_category) {
                'boys' => ' - Putra',
                'girls' => ' - Putri',
                default => '',
            };

            // Otomatis buat sesi presensi
            $schedule->attendanceSessions()->firstOrCreate(
                ['competition_schedule_id' => $schedule->id],
                [
                    'session_type' => 'participant',
                    'title' => 'Presensi: ' . $competitionTitle . ' (' . $schedule->session_name . $genderSuffix . ')',
                    'is_competition' => true,
                    'opened_at' => $schedule->checkin_open_time,
                    'closed_at' => $schedule->checkin_close_time,
                    'is_active' => true,
                ]
            );

            Flux::toast(variant: 'success', heading: 'Jadwal Dibuat', text: "Jadwal sesi {$this->session_name} berhasil dibuat beserta sesi presensi otomatis.");
        }

        $this->showModal = false;
        $this->resetForm();
    }

    /**
     * Buka modal manajemen penugasan dewan juri
     */
    public function openJudgesModal(string $scheduleId): void
    {
        $this->selectedScheduleId = $scheduleId;
        $this->selectedJudgeId = '';
        $this->judgeRole = 'scoring_judge';
        $this->judgeNotes = '';
        $this->showJudgesModal = true;
    }

    /**
     * Tugaskan juri secara instan
     */
    public function assignJudge(): void
    {
        $this->validate([
            'selectedJudgeId' => 'required|exists:users,id',
            'judgeRole' => 'required|in:lead_judge,scoring_judge,companion_judge',
            'judgeNotes' => 'nullable|string|max:255',
        ]);

        if (! $this->selectedScheduleId) {
            return;
        }

        $schedule = CompetitionSchedule::findOrFail($this->selectedScheduleId);

        $existing = JudgeAssignment::where('competition_schedule_id', $schedule->id)
            ->where('user_id', $this->selectedJudgeId)
            ->first();

        if ($existing) {
            $existing->update([
                'role' => $this->judgeRole,
                'notes' => $this->judgeNotes ?: $existing->notes,
            ]);
            Flux::toast(variant: 'success', heading: 'Peran Juri Diperbarui', text: 'Peran juri berhasil diperbarui.');
        } else {
            $schedule->judgeAssignments()->create([
                'user_id' => $this->selectedJudgeId,
                'role' => $this->judgeRole,
                'is_confirmed' => true,
                'notes' => $this->judgeNotes ?: null,
            ]);
            $judgeName = User::find($this->selectedJudgeId)?->name;
            Flux::toast(variant: 'success', heading: 'Juri Ditugaskan', text: "Juri {$judgeName} berhasil ditugaskan.");
        }

        $this->selectedJudgeId = '';
        $this->judgeNotes = '';
    }

    /**
     * Hapus penugasan juri secara instan
     */
    public function removeJudge(string $assignmentId): void
    {
        $assignment = JudgeAssignment::with('judge')->findOrFail($assignmentId);
        $judgeName = $assignment->judge?->name ?? 'Juri';
        $assignment->delete();

        Flux::toast(variant: 'danger', heading: 'Juri Dilepas', text: "Penugasan juri {$judgeName} telah dibatalkan.");
    }

    /**
     * Toggle status aktif Sesi Presensi Pos secara instan
     */
    public function toggleAttendanceSession(string $scheduleId): void
    {
        $schedule = CompetitionSchedule::findOrFail($scheduleId);

        $session = $schedule->attendanceSessions()->firstOrCreate(
            ['session_type' => 'participant'],
            [
                'title' => 'Presensi Peserta ' . $schedule->session_name,
                'is_competition' => true,
                'opened_at' => $schedule->attendance_open_time ?: now(),
                'closed_at' => $schedule->attendance_close_time ?: now()->addHours(2),
            ]
        );

        $newStatus = ! $session->is_active;

        $session->update([
            'is_active' => $newStatus,
            'opened_at' => $newStatus ? ($schedule->attendance_open_time ?: now()) : $session->opened_at,
            'closed_at' => ! $newStatus ? now()->setTimezone('Asia/Jakarta') : $schedule->attendance_close_time,
        ]);

        if ($schedule->isAttendanceOpen()) {
            $statusLabel = $newStatus ? 'DIBUKA KEMBALI (Aktif Scan Pos)' : 'DITUTUP SEMENTARA SAAT WAKTU LOMBA';
            $toastHeading = $newStatus ? 'Presensi Pos Aktif' : 'Presensi Ditutup Sementara';
        } elseif ($schedule->isAttendanceNotOpenYet()) {
            $statusLabel = $newStatus ? 'DIAKTIFKAN (Akan otomatis terbuka H-30m)' : 'DIKUNCI / DINONAKTIFKAN';
            $toastHeading = $newStatus ? 'Auto-Buka Aktif' : 'Sesi Dikunci';
        } else {
            $statusLabel = $newStatus ? 'DIBUKA KEMBALI (Susulan)' : 'DITUTUP';
            $toastHeading = $newStatus ? 'Presensi Susulan Dibuka' : 'Presensi Ditutup';
        }

        Flux::toast(
            variant: $newStatus ? 'success' : 'warning',
            heading: $toastHeading,
            text: "Sesi presensi pos {$schedule->session_name} kini {$statusLabel}."
        );
    }

    /**
     * Kirim notifikasi push pengingat manual untuk satu jadwal ke seluruh perangkat terdaftar
     */
    public function sendReminder(string $id, WebPushService $webPush): void
    {
        $schedule = CompetitionSchedule::with('competition')->findOrFail($id);
        $start = $schedule->start_time->copy()->setTimezone('Asia/Jakarta');

        $result = $webPush->broadcastNotification(
            payload: [
                'title' => 'Pengingat Jadwal Lomba',
                'body' => sprintf(
                    '%s (%s) di %s, %s pukul %s WIB.',
                    $schedule->competition?->title ?: 'Lomba',
                    $schedule->session_name,
                    $schedule->arena_location,
                    $start->translatedFormat('l, d F Y'),
                    $start->format('H:i')
                ),
                'url' => url('/schedules'),
                'tag' => 'lt3-reminder-'.$schedule->id,
            ],
            targetRole: null,
            sender: auth()->user()
        );

        Flux::toast(
            variant: $result['sent'] > 0 ? 'success' : 'warning',
            heading: $result['sent'] > 0 ? 'Notifikasi Terkirim' : 'Tidak Ada Perangkat Terkirim',
            text: "Pengingat {$schedule->session_name}: terkirim {$result['sent']}, gagal {$result['failed']}."
        );
    }

    /**
     * Hapus jadwal lomba
     */
    public function deleteSchedule(string $id): void
    {
        $schedule = CompetitionSchedule::findOrFail($id);
        $name = $schedule->session_name;
        $schedule->delete();

        Flux::toast(variant: 'danger', heading: 'Jadwal Dihapus', text: "Jadwal sesi {$name} telah dihapus.");
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->competition_id = '';
        $this->session_name = '';
        $this->arena_location = '';
        $this->start_time = '';
        $this->end_time = '';
        $this->status = 'scheduled';
        $this->participant_type = 'patrol';
        $this->is_published = true;
    }

    public function render()
    {
        return view('livewire.admin.schedules-management', [
            'schedules' => $this->schedules,
            'stats' => $this->stats,
            'competitions' => $this->competitions,
            'availableJudges' => $this->availableJudges,
            'selectedSchedule' => $this->selectedSchedule,
        ]);
    }
}

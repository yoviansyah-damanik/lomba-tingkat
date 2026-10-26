<?php

namespace App\Livewire\Portal;

use App\Models\Assessment;
use App\Models\CompetitionSchedule;
use App\Models\Patrol;
use App\Services\AssessmentScoringService;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Komponen Livewire 4 Lembar Penilaian Juri: juri memasukkan NPP regu, bukan memilih dari daftar peserta
 */
#[Layout('layouts.app')]
#[Title('Lembar Penilaian Juri — Input NPP')]
class JudgeScoring extends Component
{
    public string $scheduleId;

    public ?string $selectedPatrolId = null;

    /** NPP yang diketik juri pada langkah 2 */
    public string $nppInput = '';

    public ?string $nppError = null;

    /** @var array<string, float|int|string> */
    public array $criteriaScores = [];

    public ?string $notes = null;

    public bool $isFinalizeModalOpen = false;

    public bool $isUnlockModalOpen = false;

    public string $unlockReason = '';

    public bool $unmaskNames = false;

    public function mount(string $scheduleId): void
    {
        $this->scheduleId = $scheduleId;
        $user = Auth::user();

        // Verifikasi hak akses juri atau administrator
        $schedule = $this->schedule();
        if (! $user->isAdminOnly()) {
            $isAssigned = $schedule->judgeAssignments()
                ->where('user_id', $user->id)
                ->exists();

            if (! $isAssigned) {
                abort(403, 'Anda tidak ditugaskan sebagai dewan juri pada sesi lomba ini.');
            }
        }
    }

    #[Computed]
    public function schedule(): CompetitionSchedule
    {
        return CompetitionSchedule::with([
            'competition.criteria',
            'judgeAssignments.judge',
            'attendanceSessions',
        ])->findOrFail($this->scheduleId);
    }

    #[Computed]
    public function competition()
    {
        return $this->schedule()->competition;
    }

    #[Computed]
    public function criteria(): Collection
    {
        return $this->competition()->criteria ?: collect();
    }

    /**
     * Apakah NPP sudah digenerate untuk kategori sesi ini
     */
    #[Computed]
    public function nppReady(): bool
    {
        return Patrol::whereNotNull('npp')
            ->when($this->schedule()->gender_category, fn ($q, $gender) => $q->where('gender_category', $gender))
            ->exists();
    }

    /**
     * Regu aktif yang lolos verifikasi NPP. Hanya NPP yang ditampilkan; nama regu dan pangkalan
     * hanya dibuka untuk Administrator bila mode audit dinyalakan.
     */
    #[Computed]
    public function selectedPatrol(): ?array
    {
        if (! $this->selectedPatrolId) {
            return null;
        }

        $patrol = Patrol::with('scoutGroup')->find($this->selectedPatrolId);
        if (! $patrol) {
            return null;
        }

        $canUnmask = Auth::user()->isAdmin() && $this->unmaskNames;

        return [
            'patrol_id' => $patrol->id,
            'npp' => $patrol->nppLabel(),
            'gender_category' => $patrol->isBoys() ? 'boys' : 'girls',
            'gender_label' => $patrol->isBoys() ? 'Putra' : 'Putri',
            'patrol_name' => $canUnmask ? $patrol->patrol_name : null,
            'school_name' => $canUnmask ? ($patrol->scoutGroup?->school_name ?? '-') : null,
        ];
    }

    /**
     * Riwayat penilaian pada sesi ini: NPP yang sudah dinilai juri (seluruh juri bagi Administrator)
     */
    #[Computed]
    public function scoredList(): Collection
    {
        $query = Assessment::with('patrol')
            ->where('competition_schedule_id', $this->scheduleId);

        if (! Auth::user()->isAdminOnly()) {
            $query->where('judge_id', Auth::id());
        }

        return $query->latest('updated_at')->get()
            ->map(fn (Assessment $a) => [
                'patrol_id' => $a->patrol_id,
                'npp' => $a->patrol?->nppLabel() ?? '—',
                'status' => $a->status,
                'final_score' => (float) $a->final_score,
            ])
            ->unique('patrol_id')
            ->values();
    }

    #[Computed]
    public function activeAssessment(): ?Assessment
    {
        if (! $this->selectedPatrolId) {
            return null;
        }

        $query = Assessment::where('competition_schedule_id', $this->scheduleId)
            ->where('patrol_id', $this->selectedPatrolId);

        if (! Auth::user()->isAdminOnly()) {
            $query->where('judge_id', Auth::id());
        }

        return $query->first();
    }

    #[Computed]
    public function currentCalculatedScore(): array
    {
        $scoringService = app(AssessmentScoringService::class);

        return $scoringService->calculateWeightedScore(
            $this->criteriaScores,
            $this->competition()
        );
    }

    #[Computed]
    public function isAssignedJudge(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return $user->isJudge() && $this->schedule()->judgeAssignments()
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Pesan penutupan jendela penilaian (null bila penilaian sedang dibuka)
     */
    #[Computed]
    public function scoringClosedMessage(): ?string
    {
        return $this->schedule()->scoringClosedMessage();
    }

    /**
     * Tolak aksi penilaian di luar jendela waktu (jadwal dimulai s.d. 30 menit setelah berakhir)
     */
    protected function denyIfScoringClosed(): bool
    {
        if (! $message = $this->scoringClosedMessage) {
            return false;
        }

        Flux::toast(variant: 'warning', heading: 'Penilaian Ditutup', text: $message);
        $this->dispatch('alert', ['type' => 'warning', 'message' => $message]);

        return true;
    }

    /**
     * Langkah 2-4: juri memasukkan NPP, sistem memverifikasi, lalu membuka form atau menampilkan notifikasi
     */
    public function submitNpp(AssessmentScoringService $scoringService): void
    {
        $result = $scoringService->resolveNpp($this->schedule(), $this->nppInput);

        if (! $result['patrol']) {
            $this->nppError = $result['error'];
            $this->selectedPatrolId = null;
            Flux::toast(variant: 'danger', heading: 'NPP Tidak Valid', text: $result['error']);
            $this->dispatch('alert', ['type' => 'error', 'message' => $result['error']]);

            return;
        }

        $this->nppError = null;
        $this->nppInput = $result['patrol']->npp;
        $this->selectPatrol($result['patrol']->id);
    }

    /**
     * Membuka form penilaian untuk regu yang sudah lolos verifikasi NPP
     */
    public function selectPatrol(string $patrolId): void
    {
        $this->selectedPatrolId = $patrolId;
        $this->nppError = null;
        $this->nppInput = Patrol::find($patrolId)?->npp ?? '';

        $assessment = $this->activeAssessment();
        $this->criteriaScores = [];

        foreach ($this->criteria() as $criterion) {
            $saved = $assessment && is_array($assessment->criteria_scores) ? ($assessment->criteria_scores[$criterion->id] ?? null) : null;
            $this->criteriaScores[$criterion->id] = $saved ?? (float) $criterion->min_score;
        }

        $this->notes = $assessment?->notes;
    }

    /**
     * Kembali ke langkah input NPP
     */
    public function resetNpp(): void
    {
        $this->reset(['selectedPatrolId', 'nppInput', 'nppError', 'notes', 'criteriaScores']);
    }

    /**
     * Menyimpan draf penilaian (Autosave / Manual Save)
     */
    public function saveDraft(AssessmentScoringService $scoringService): void
    {
        // Penilaian mutlak hanya dapat dilakukan oleh Juri yang ditugaskan
        if (! $this->isAssignedJudge) {
            $msg = 'Pemberian nilai hanya dapat dilakukan oleh Juri yang telah ditunjuk (Administrator tidak diperkenankan melakukan penilaian).';
            Flux::toast(variant: 'danger', heading: 'Akses Ditolak', text: $msg);
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => $msg,
            ]);

            return;
        }

        if ($this->denyIfScoringClosed()) {
            return;
        }

        if (! $this->selectedPatrolId) {
            return;
        }

        $assessment = $this->activeAssessment();
        if ($assessment && $assessment->isLocked()) {
            $msg = 'Penilaian ini telah difinalisasi dan terkunci.';
            Flux::toast(variant: 'warning', heading: 'Penilaian Terkunci', text: $msg);
            $this->dispatch('alert', [
                'type' => 'warning',
                'message' => $msg,
            ]);

            return;
        }

        $scoringService->saveDraft([
            'competition_schedule_id' => $this->scheduleId,
            'patrol_id' => $this->selectedPatrolId,
            'criteria_scores' => $this->criteriaScores,
            'notes' => $this->notes,
        ], Auth::user());

        $msg = 'Draf penilaian untuk '.('NPP '.($this->selectedPatrol()['npp'] ?? '-')).' berhasil disimpan.';
        Flux::toast(variant: 'success', heading: 'Draf Disimpan', text: $msg);
        $this->dispatch('alert', [
            'type' => 'success',
            'message' => $msg,
        ]);
    }

    /**
     * Membuka modal konfirmasi finalisasi nilai
     */
    public function openFinalizeModal(): void
    {
        if (! $this->isAssignedJudge) {
            $msg = 'Pemberian nilai hanya dapat dilakukan oleh Juri yang telah ditunjuk.';
            Flux::toast(variant: 'danger', heading: 'Akses Ditolak', text: $msg);
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => $msg,
            ]);

            return;
        }

        if ($this->denyIfScoringClosed()) {
            return;
        }

        $this->isFinalizeModalOpen = true;
    }

    /**
     * Memfinalisasi dan mengunci nilai resmi ke sistem klasemen
     */
    public function finalize(AssessmentScoringService $scoringService): void
    {
        // Penilaian mutlak hanya dapat dilakukan oleh Juri yang ditugaskan
        if (! $this->isAssignedJudge) {
            $msg = 'Pemberian nilai hanya dapat dilakukan oleh Juri yang telah ditunjuk (Administrator tidak diperkenankan melakukan penilaian).';
            Flux::toast(variant: 'danger', heading: 'Akses Ditolak', text: $msg);
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => $msg,
            ]);

            return;
        }

        if ($this->denyIfScoringClosed()) {
            $this->isFinalizeModalOpen = false;

            return;
        }

        if (! $this->selectedPatrolId) {
            return;
        }

        // Simpan draf terlebih dahulu agar skor mutakhir tercatat
        $assessment = $scoringService->saveDraft([
            'competition_schedule_id' => $this->scheduleId,
            'patrol_id' => $this->selectedPatrolId,
            'criteria_scores' => $this->criteriaScores,
            'notes' => $this->notes,
        ], Auth::user());

        // Finalisasi & kunci
        $scoringService->finalizeScore($assessment->id, Auth::user());

        $this->isFinalizeModalOpen = false;

        $msg = 'Nilai resmi untuk '.('NPP '.($this->selectedPatrol()['npp'] ?? '-')).' telah difinalisasi dan dikunci ke klasemen.';
        Flux::toast(variant: 'success', heading: 'Nilai Difinalisasi', text: $msg);
        $this->dispatch('alert', [
            'type' => 'success',
            'message' => $msg,
        ]);
    }

    /**
     * Membuka modal unlock (Khusus Administrator)
     */
    public function openUnlockModal(): void
    {
        if (! Auth::user()->isAdmin()) {
            abort(403);
        }
        $this->unlockReason = '';
        $this->isUnlockModalOpen = true;
    }

    /**
     * Membuka kunci penilaian oleh Administrator dengan catatan alasan
     */
    public function unlock(AssessmentScoringService $scoringService): void
    {
        if (! Auth::user()->isAdmin()) {
            abort(403);
        }

        $this->validate([
            'unlockReason' => 'required|string|min:5',
        ], [
            'unlockReason.required' => 'Alasan pembukaan kunci penilaian wajib diisi.',
            'unlockReason.min' => 'Alasan minimal 5 karakter.',
        ]);

        $assessment = $this->activeAssessment();
        if ($assessment) {
            $scoringService->unlockScore($assessment->id, $this->unlockReason, Auth::user());
        }

        $this->isUnlockModalOpen = false;
        $this->unlockReason = '';

        $msg = 'Kunci penilaian berhasil dibuka dan dicatat dalam audit trail.';
        Flux::toast(variant: 'success', heading: 'Kunci Dibuka', text: $msg);
        $this->dispatch('alert', [
            'type' => 'success',
            'message' => $msg,
        ]);
    }

    /**
     * Toggle unmask nama sekolah (Khusus Administrator)
     */
    public function toggleUnmask(): void
    {
        if (Auth::user()->isAdmin()) {
            $this->unmaskNames = ! $this->unmaskNames;
        }
    }

    public function render()
    {
        return view('livewire.portal.judge-scoring', [
            'schedule' => $this->schedule(),
            'competition' => $this->competition(),
            'criteria' => $this->criteria(),
            'scoredList' => $this->scoredList(),
            'nppReady' => $this->nppReady(),
            'selectedPatrol' => $this->selectedPatrol(),
            'activeAssessment' => $this->activeAssessment(),
            'calculatedScore' => $this->currentCalculatedScore(),
            'isAdmin' => Auth::user()->isAdmin(),
            'isAssignedJudge' => $this->isAssignedJudge,
        ]);
    }
}

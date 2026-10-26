<?php

namespace App\Services;

use App\Enums\RegistrationStatusEnum;
use App\Models\Assessment;
use App\Models\AuditLog;
use App\Models\Competition;
use App\Models\CompetitionSchedule;
use App\Models\CompetitionScore;
use App\Models\Patrol;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Service untuk orkestrasi penilaian rubrik juri, penjurian berbasis NPP, dan jejak audit
 */
class AssessmentScoringService
{
    public function __construct(
        protected StandingsService $standingsService
    ) {}

    /**
     * Menghitung nilai terbobot berdasarkan kriteria rubrik lomba
     */
    public function calculateWeightedScore(array $criteriaScores, Competition $competition): array
    {
        $criteria = $competition->criteria;
        if ($criteria->isEmpty()) {
            $sum = array_sum(array_map('floatval', $criteriaScores));

            return [
                'raw_total' => round($sum, 2),
                'final_score' => round($sum, 2),
                'breakdown' => [],
            ];
        }

        $totalWeight = $criteria->sum('weight_percentage') ?: 100.0;
        $calculatedFinal = 0.0;
        $rawTotal = 0.0;
        $breakdown = [];

        foreach ($criteria as $criterion) {
            $score = (float) ($criteriaScores[$criterion->id] ?? 0.0);

            // Validasi batas rentang nilai kriteria
            if ($score < (float) $criterion->min_score) {
                $score = (float) $criterion->min_score;
            } elseif ($score > (float) $criterion->max_score) {
                $score = (float) $criterion->max_score;
            }

            $rawTotal += $score;
            $weighted = $score * ((float) $criterion->weight_percentage / $totalWeight);
            $calculatedFinal += $weighted;

            $breakdown[$criterion->id] = [
                'name' => $criterion->name,
                'raw_score' => $score,
                'weight_percentage' => (float) $criterion->weight_percentage,
                'weighted_score' => round($weighted, 2),
            ];
        }

        return [
            'raw_total' => round($rawTotal, 2),
            'final_score' => round($calculatedFinal, 2),
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Menyimpan draf penilaian juri (Autosave / Manual Save)
     */
    public function saveDraft(array $data, User $judge): Assessment
    {
        $schedule = CompetitionSchedule::with('competition.criteria')->findOrFail($data['competition_schedule_id']);

        // Pemberian nilai hanya dapat dilakukan oleh Juri yang ditugaskan pada sesi lomba ini
        $isAssigned = $judge->isJudge() && $schedule->judgeAssignments()->where('user_id', $judge->id)->exists();
        if (! $isAssigned) {
            throw new InvalidArgumentException('Pemberian nilai hanya dapat dilakukan oleh Juri yang telah ditunjuk pada sesi lomba ini.');
        }

        // Penilaian hanya dalam jendela waktu: sejak jadwal dimulai hingga 30 menit setelah berakhir
        if ($message = $schedule->scoringClosedMessage()) {
            throw new InvalidArgumentException($message);
        }

        $competition = $schedule->competition;

        $calc = $this->calculateWeightedScore($data['criteria_scores'] ?? [], $competition);

        return DB::transaction(function () use ($data, $judge, $schedule, $calc) {
            $assessment = Assessment::firstOrNew([
                'competition_schedule_id' => $schedule->id,
                'judge_id' => $judge->id,
                'patrol_id' => $data['patrol_id'],
            ]);

            $isNew = ! $assessment->exists;
            $oldValues = $isNew ? null : $assessment->toArray();

            // Jika nilai telah dikunci, juri tidak boleh mengubah draf tanpa pembukaan kunci oleh Administrator
            if (! $isNew && $assessment->isLocked()) {
                throw new InvalidArgumentException('Penilaian ini telah difinalisasi dan terkunci. Hubungi Administrator untuk permohonan pembukaan kunci nilai.');
            }

            $assessment->fill([
                'competition_id' => $schedule->competition_id,
                'participant_id' => $data['participant_id'] ?? null,
                'criteria_scores' => $data['criteria_scores'] ?? [],
                'raw_total_score' => $calc['raw_total'],
                'final_score' => $calc['final_score'],
                'status' => 'draft',
                'notes' => $data['notes'] ?? null,
                'is_locked' => false,
            ]);

            $assessment->save();

            AuditLog::log(
                action: $isNew ? 'score_draft_created' : 'score_draft_updated',
                modelType: Assessment::class,
                modelId: $assessment->id,
                oldValues: $oldValues,
                newValues: $assessment->toArray(),
                userId: $judge->id,
                actingRole: 'judge'
            );

            return $assessment;
        });
    }

    /**
     * Memfinalisasi dan mengunci penilaian juri, lalu mengakumulasikan ke klasemen
     */
    public function finalizeScore(string $assessmentId, User $judge): Assessment
    {
        return DB::transaction(function () use ($assessmentId, $judge) {
            $assessment = Assessment::with(['competition.criteria', 'schedule.judgeAssignments', 'patrol'])->findOrFail($assessmentId);

            if ($assessment->judge_id !== $judge->id) {
                throw new InvalidArgumentException('Anda tidak memiliki wewenang memfinalisasi penilaian juri lain.');
            }

            // Pemberian dan finalisasi nilai hanya dapat dilakukan oleh Juri yang ditugaskan pada sesi lomba ini
            $isAssigned = $judge->isJudge() && $assessment->schedule?->judgeAssignments()->where('user_id', $judge->id)->exists();
            if (! $isAssigned) {
                throw new InvalidArgumentException('Pemberian nilai hanya dapat dilakukan oleh Juri yang telah ditunjuk pada sesi lomba ini.');
            }

            // Finalisasi pun hanya dalam jendela waktu penilaian
            if ($message = $assessment->schedule?->scoringClosedMessage()) {
                throw new InvalidArgumentException($message);
            }

            $oldValues = $assessment->toArray();

            // Hitung ulang untuk memastikan skor mutakhir
            $calc = $this->calculateWeightedScore($assessment->criteria_scores ?? [], $assessment->competition);

            $assessment->update([
                'raw_total_score' => $calc['raw_total'],
                'final_score' => $calc['final_score'],
                'status' => 'finalized',
                'is_locked' => true,
                'finalized_at' => now(),
            ]);

            AuditLog::log(
                action: 'score_finalized',
                modelType: Assessment::class,
                modelId: $assessment->id,
                oldValues: $oldValues,
                newValues: $assessment->toArray(),
                reason: 'Finalisasi nilai resmi oleh juri',
                userId: $judge->id,
                actingRole: 'judge'
            );

            // Akumulasi skor seluruh juri untuk regu ini ke CompetitionScore
            $this->aggregatePatrolScore($assessment->competition_id, $assessment->patrol_id);

            return $assessment->fresh();
        });
    }

    /**
     * Membuka kunci penilaian oleh Administrator dengan alasan wajib
     */
    public function unlockScore(string $assessmentId, string $reason, User $admin): Assessment
    {
        if (! $admin->isAdmin()) {
            throw new InvalidArgumentException('Hanya Administrator yang memiliki wewenang membuka kunci penilaian.');
        }

        if (empty(trim($reason))) {
            throw new InvalidArgumentException('Alasan pembukaan kunci penilaian wajib diisi.');
        }

        return DB::transaction(function () use ($assessmentId, $reason, $admin) {
            $assessment = Assessment::findOrFail($assessmentId);
            $oldValues = $assessment->toArray();

            $assessment->update([
                'status' => 'draft',
                'is_locked' => false,
            ]);

            AuditLog::log(
                action: 'score_unlocked',
                modelType: Assessment::class,
                modelId: $assessment->id,
                oldValues: $oldValues,
                newValues: $assessment->toArray(),
                reason: $reason,
                userId: $admin->id,
                actingRole: 'admin'
            );

            // Hitung ulang akumulasi skor
            $this->aggregatePatrolScore($assessment->competition_id, $assessment->patrol_id);

            return $assessment->fresh();
        });
    }

    /**
     * Mengakumulasikan seluruh penilaian juri untuk satu regu dan memperbarui klasemen
     */
    public function aggregatePatrolScore(string $competitionId, string $patrolId): void
    {
        $competition = Competition::findOrFail($competitionId);

        // Ambil seluruh penilaian juri yang sudah difinalisasi
        $finalizedAssessments = Assessment::where('competition_id', $competitionId)
            ->where('patrol_id', $patrolId)
            ->where('status', 'finalized')
            ->get();

        if ($finalizedAssessments->isEmpty()) {
            // Jika belum ada juri yang memfinalisasi, atau semua di-unlock
            $draftAssessments = Assessment::where('competition_id', $competitionId)
                ->where('patrol_id', $patrolId)
                ->get();

            if ($draftAssessments->isEmpty()) {
                CompetitionScore::where('competition_id', $competitionId)
                    ->where('patrol_id', $patrolId)
                    ->delete();
                $this->standingsService->recalculateRanks($competitionId);

                return;
            }
        }

        // Metode perhitungan skor akhir (Pilihan user: Sum Scoring / Akumulasi Penjumlahan)
        $isAverage = $competition->scoring_formula === 'average';
        $scoresToAggregate = $finalizedAssessments->isNotEmpty() ? $finalizedAssessments : Assessment::where('competition_id', $competitionId)->where('patrol_id', $patrolId)->get();

        $sumScore = (float) $scoresToAggregate->sum('final_score');
        $aggregatedFinalScore = $isAverage && $scoresToAggregate->count() > 0
            ? round($sumScore / $scoresToAggregate->count(), 2)
            : round($sumScore, 2);

        // Kumpulkan rincian kriteria dari seluruh juri
        $consolidatedCriteria = [];
        foreach ($scoresToAggregate as $ass) {
            $judgeName = $ass->judge?->name ?? 'Juri';
            $consolidatedCriteria[$ass->judge_id] = [
                'judge_name' => $judgeName,
                'final_score' => (float) $ass->final_score,
                'scores' => $ass->criteria_scores,
                'notes' => $ass->notes,
            ];
        }

        CompetitionScore::updateOrCreate(
            [
                'competition_id' => $competitionId,
                'patrol_id' => $patrolId,
            ],
            [
                'final_score' => $aggregatedFinalScore,
                'criteria_scores' => $consolidatedCriteria,
                'is_locked' => $finalizedAssessments->isNotEmpty(),
            ]
        );

        $this->standingsService->recalculateRanks($competitionId);
    }

    /**
     * Verifikasi NPP yang dimasukkan juri terhadap sesi lomba: NPP harus terdaftar, regu terverifikasi,
     * dan dicari pada kategori (putra/putri) sesi. Mengembalikan regu atau pesan penolakan; identitas gudep tidak dibuka.
     *
     * @return array{patrol: ?Patrol, error: ?string}
     */
    public function resolveNpp(CompetitionSchedule $schedule, string $npp): array
    {
        $npp = PatrolNumberService::normalize($npp);

        if ($npp === '') {
            return ['patrol' => null, 'error' => 'NPP wajib diisi.'];
        }

        $gender = $schedule->gender_category;
        $label = $gender === 'boys' ? ' Putra' : ($gender === 'girls' ? ' Putri' : '');

        // NPP putra dan putri sama-sama dimulai dari 01, jadi pencarian dibatasi pada kategori sesi
        $matches = Patrol::where('npp', $npp)->when($gender, fn ($q) => $q->where('gender_category', $gender))->get();

        if ($matches->count() > 1) {
            return ['patrol' => null, 'error' => "NPP {$npp} ada pada kategori putra dan putri. Gunakan sesi yang sesuai kategori regu."];
        }

        $patrol = $matches->first();

        if (! $patrol) {
            return ['patrol' => null, 'error' => "NPP {$npp} tidak ditemukan pada Regu{$label}. Periksa kembali nomor pada kartu/papan regu."];
        }

        if ($patrol->status !== RegistrationStatusEnum::Verified) {
            return ['patrol' => null, 'error' => "NPP {$npp} bukan peserta lomba yang terverifikasi."];
        }

        return ['patrol' => $patrol, 'error' => null];
    }

    /**
     * Progres penilaian satu juri pada sesi: jumlah regu peserta, sudah dinilai (draf/final), dan final
     *
     * @return array{total: int, scored: int, finalized: int}
     */
    public function scoringProgress(CompetitionSchedule $schedule, ?User $judge = null): array
    {
        $total = Patrol::where('status', RegistrationStatusEnum::Verified)
            ->when($schedule->gender_category, fn ($q, $gender) => $q->where('gender_category', $gender))
            ->count();

        $assessments = Assessment::where('competition_schedule_id', $schedule->id)
            ->when($judge, fn ($q) => $q->where('judge_id', $judge->id))
            ->get();

        return [
            'total' => $total,
            'scored' => $assessments->pluck('patrol_id')->unique()->count(),
            'finalized' => $assessments->where('status', 'finalized')->pluck('patrol_id')->unique()->count(),
        ];
    }
}

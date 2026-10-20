<?php

namespace App\Services;

use App\Enums\RegistrationStatusEnum;
use App\Models\AssessmentCriterion;
use App\Models\Competition;
use App\Models\CompetitionScore;
use App\Models\Event;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use Illuminate\Support\Collection;

/**
 * Service untuk perhitungan akumulasi nilai, medali prestasi, dan klasemen kontingen LT III
 */
class StandingsService
{
    /**
     * Mengambil klasemen perolehan medali per Regu Penggalang
     */
    public function getPatrolStandings(string $gender = 'all', ?string $search = null, bool $unmask = false): Collection
    {
        $shouldHide = Event::first()?->hide_patrol_identities && ! $unmask;

        $query = Patrol::with(['scoutGroup', 'competitionScores'])
            ->where('status', RegistrationStatusEnum::Verified);

        if ($gender === 'boys') {
            $query->where('gender_category', 'boys');
        } elseif ($gender === 'girls') {
            $query->where('gender_category', 'girls');
        }

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('patrol_name', 'like', "%{$search}%")
                    ->orWhereHas('scoutGroup', function ($sq) use ($search) {
                        $sq->where('school_name', 'like', "%{$search}%")
                            ->orWhere('group_number', 'like', "%{$search}%");
                    });
            });
        }

        $patrols = $query->get();

        $standings = $patrols->map(function ($patrol) use ($shouldHide) {
            $gold = 0;
            $silver = 0;
            $bronze = 0;
            $totalScoreSum = 0.0;
            $competitionsScored = 0;

            foreach ($patrol->competitionScores as $score) {
                $competitionsScored++;
                $totalScoreSum += (float) $score->final_score;

                match ($score->predicate) {
                    'prestasi_tinggi' => $gold++,
                    'prestasi_baik' => $silver++,
                    'prestasi_cukup' => $bronze++,
                    default => null,
                };
            }

            $totalPoints = ($gold * 5) + ($silver * 3) + ($bronze * 1);
            $num = $patrol->draw_number ?: 1;
            $maskedPatrolName = 'Regu #' . str_pad((string) $num, 2, '0', STR_PAD_LEFT);
            $maskedSchoolName = 'Pangkalan Dirahasiakan';

            return [
                'id' => $patrol->id,
                'patrol_name' => $shouldHide ? $maskedPatrolName : $patrol->patrol_name,
                'real_patrol_name' => $patrol->patrol_name,
                'gender_category' => $patrol->gender_category instanceof \App\Enums\PatrolGenderEnum
                    ? $patrol->gender_category->value
                    : $patrol->gender_category,
                'school_name' => $shouldHide ? $maskedSchoolName : ($patrol->scoutGroup?->school_name ?? '-'),
                'real_school_name' => $patrol->scoutGroup?->school_name ?? '-',
                'group_number' => $shouldHide ? '***' : $patrol->group_number,
                'kwarran_name' => $shouldHide ? 'Dirahasiakan' : ($patrol->scoutGroup?->kwarran_name ?? 'Padangsidimpuan'),
                'real_group_number' => $patrol->group_number,
                'draw_number' => $patrol->draw_number,
                'is_masked' => $shouldHide,
                'gold' => $gold,
                'silver' => $silver,
                'bronze' => $bronze,
                'total_medals' => $gold + $silver + $bronze,
                'total_points' => $totalPoints,
                'total_score_sum' => round($totalScoreSum, 2),
                'competitions_scored' => $competitionsScored,
            ];
        });

        // Urutkan berdasarkan perolehan peringkat lomba (Prestasi Tinggi > Prestasi Baik > Prestasi Cukup > Total Prestasi)
        $sortCallback = function ($a, $b) {
            if ($a['gold'] !== $b['gold']) {
                return $b['gold'] <=> $a['gold'];
            }
            if ($a['silver'] !== $b['silver']) {
                return $b['silver'] <=> $a['silver'];
            }
            if ($a['bronze'] !== $b['bronze']) {
                return $b['bronze'] <=> $a['bronze'];
            }
            if ($a['total_medals'] !== $b['total_medals']) {
                return $b['total_medals'] <=> $a['total_medals'];
            }

            return strcmp($a['real_patrol_name'] ?? $a['patrol_name'], $b['real_patrol_name'] ?? $b['patrol_name']);
        };

        if ($gender === 'all') {
            $boys = $standings->where('gender_category', 'boys')->sort($sortCallback)->values()->map(function ($item, $index) {
                $item['rank'] = $index + 1;
                $item['general_rank_title'] = match ($item['rank']) {
                    1 => 'Regu Berprestasi Tinggi Putra',
                    2 => 'Regu Berprestasi Baik Putra',
                    3 => 'Regu Berprestasi Cukup Putra',
                    default => null,
                };
                return $item;
            });

            $girls = $standings->where('gender_category', 'girls')->sort($sortCallback)->values()->map(function ($item, $index) {
                $item['rank'] = $index + 1;
                $item['general_rank_title'] = match ($item['rank']) {
                    1 => 'Regu Berprestasi Tinggi Putri',
                    2 => 'Regu Berprestasi Baik Putri',
                    3 => 'Regu Berprestasi Cukup Putri',
                    default => null,
                };
                return $item;
            });

            return $boys->concat($girls);
        }

        $suffix = $gender === 'boys' ? ' Putra' : ($gender === 'girls' ? ' Putri' : '');
        $sorted = $standings->sort($sortCallback)->values();

        return $sorted->map(function ($item, $index) use ($suffix) {
            $item['rank'] = $index + 1;
            $item['general_rank_title'] = match ($item['rank']) {
                1 => 'Regu Berprestasi Tinggi' . $suffix,
                2 => 'Regu Berprestasi Baik' . $suffix,
                3 => 'Regu Berprestasi Cukup' . $suffix,
                default => null,
            };
            return $item;
        });
    }

    /**
     * Mengambil rekapitulasi penilaian satu cabang mata lomba
     */
    public function getCompetitionRecap(string $competitionId, bool $unmask = false): Collection
    {
        $scores = CompetitionScore::with(['patrol.scoutGroup', 'participant', 'judge'])
            ->where('competition_id', $competitionId)
            ->orderByRaw('CASE WHEN rank IS NULL THEN 1 ELSE 0 END, rank ASC, final_score DESC')
            ->get();

        $shouldHide = Event::first()?->hide_patrol_identities && ! $unmask;

        foreach ($scores as $score) {
            if ($score->patrol) {
                $num = $score->patrol->draw_number ?: 1;
                $score->patrol->display_name = $shouldHide
                    ? ('Regu #' . str_pad((string) $num, 2, '0', STR_PAD_LEFT))
                    : $score->patrol->patrol_name;
                $score->patrol->display_school = $shouldHide
                    ? 'Pangkalan Dirahasiakan'
                    : ($score->patrol->scoutGroup?->school_name ?? '-');
                $score->patrol->is_masked = $shouldHide;
            }
        }

        return $scores;
    }

    /**
     * Menyimpan atau memperbarui nilai regu untuk suatu cabang lomba
     */
    public function saveScore(array $data): CompetitionScore
    {
        $finalScore = $data['final_score'] ?? 0.00;
        $criteriaScores = $data['criteria_scores'] ?? null;

        // Jika ada kriteria penilaian berbobot, hitung otomatis nilai akhir
        if (! empty($criteriaScores) && is_array($criteriaScores)) {
            $calculatedTotal = 0.0;
            $criteria = AssessmentCriterion::where('competition_id', $data['competition_id'])->get();

            if ($criteria->isNotEmpty()) {
                $totalWeight = $criteria->sum('weight_percentage') ?: 100;
                foreach ($criteria as $criterion) {
                    $scoreVal = (float) ($criteriaScores[$criterion->id] ?? 0.0);
                    $calculatedTotal += ($scoreVal * ($criterion->weight_percentage / $totalWeight));
                }
                $finalScore = round($calculatedTotal, 2);
            }
        }

        $score = CompetitionScore::updateOrCreate(
            [
                'competition_id' => $data['competition_id'],
                'patrol_id' => $data['patrol_id'],
            ],
            [
                'participant_id' => $data['participant_id'] ?? null,
                'judge_id' => $data['judge_id'] ?? auth()->id(),
                'competition_schedule_id' => $data['competition_schedule_id'] ?? null,
                'final_score' => $finalScore,
                'criteria_scores' => $criteriaScores,
                'notes' => $data['notes'] ?? null,
                'is_locked' => $data['is_locked'] ?? false,
            ]
        );

        // Otomatis susun ulang peringkat cabang lomba setelah nilai baru masuk
        $this->recalculateRanks($data['competition_id']);

        return $score->fresh();
    }

    /**
     * Mengkalkulasi ulang peringkat dan predikat prestasi seluruh peserta pada cabang lomba
     */
    public function recalculateRanks(string $competitionId): void
    {
        $scores = CompetitionScore::with('patrol:id,gender_category')
            ->where('competition_id', $competitionId)
            ->orderBy('final_score', 'desc')
            ->get();

        // Peringkat putra dan putri dihitung terpisah pada setiap cabang lomba
        $perCategory = $scores->groupBy(function (CompetitionScore $score) {
            $gender = $score->patrol?->gender_category;

            return $gender instanceof \App\Enums\PatrolGenderEnum ? $gender->value : (string) $gender;
        });

        foreach ($perCategory as $categoryScores) {
            foreach ($categoryScores->values() as $index => $score) {
                $rank = $index + 1;
                $predicate = match ($rank) {
                    1 => 'prestasi_tinggi',
                    2 => 'prestasi_baik',
                    3 => 'prestasi_cukup',
                    default => 'partisipasi',
                };

                $score->update([
                    'rank' => $rank,
                    'predicate' => $predicate,
                ]);
            }
        }

        // Siarkan event pembaruan klasemen secara realtime
        event(new \App\Events\StandingsUpdated($competitionId));
    }

    /**
     * Mengambil ringkasan statistik perolehan medali dan podium klasemen
     */
    public function getStandingsSummary(string $gender = 'all', bool $unmask = false): array
    {
        $patrolStandings = $this->getPatrolStandings($gender, null, $unmask);

        $totalGold = $patrolStandings->sum('gold');
        $totalSilver = $patrolStandings->sum('silver');
        $totalBronze = $patrolStandings->sum('bronze');

        $topPatrols = $patrolStandings->filter(fn ($p) => $p['total_medals'] > 0)->take(3)->values();

        // Peringkat Umum Regu terpisah untuk Putra dan Putri
        $topBoysPatrols = $this->getPatrolStandings('boys', null, $unmask)->filter(fn ($p) => $p['total_medals'] > 0)->take(3)->values();
        $topGirlsPatrols = $this->getPatrolStandings('girls', null, $unmask)->filter(fn ($p) => $p['total_medals'] > 0)->take(3)->values();

        $completedCompetitionsCount = Competition::whereHas('scores', function ($q) {
            $q->whereNotNull('final_score');
        })->count();

        return [
            'total_scout_groups' => ScoutGroup::where('verification_status', RegistrationStatusEnum::Verified)->count(),
            'total_patrols' => $patrolStandings->count(),
            'total_gold' => $totalGold,
            'total_silver' => $totalSilver,
            'total_bronze' => $totalBronze,
            'total_medals' => $totalGold + $totalSilver + $totalBronze,
            'completed_competitions_count' => $completedCompetitionsCount,
            'top_patrols' => $topPatrols,
            'top_boys_patrols' => $topBoysPatrols,
            'top_girls_patrols' => $topGirlsPatrols,
        ];
    }
}

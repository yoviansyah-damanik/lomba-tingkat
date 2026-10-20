<?php

namespace Database\Seeders;

use App\Models\AssessmentCriterion;
use App\Models\Competition;
use App\Models\Patrol;
use App\Models\User;
use App\Services\StandingsService;
use Illuminate\Database\Seeder;

/**
 * Seeder data nilai awal untuk cabang lomba dan klasemen
 */
class CompetitionScoreSeeder extends Seeder
{
    public function run(): void
    {
        $competitions = Competition::with('criteria')->published()->get();
        $patrols = Patrol::where('status', \App\Enums\RegistrationStatusEnum::Verified)->get();
        $judge = User::whereHas('roles', fn ($q) => $q->where('name', 'judge'))->first() ?? User::first();

        if ($competitions->isEmpty() || $patrols->isEmpty()) {
            return;
        }

        $standingsService = app(StandingsService::class);

        // Berikan nilai acak berkualitas (70 - 98) untuk beberapa cabang lomba
        $targetCompetitions = $competitions->take(4);

        foreach ($targetCompetitions as $competition) {
            foreach ($patrols as $index => $patrol) {
                $criteriaScores = [];
                $criteria = $competition->criteria;

                if ($criteria->isNotEmpty()) {
                    foreach ($criteria as $criterion) {
                        $criteriaScores[$criterion->id] = rand(70, 98);
                    }
                } else {
                    $criteriaScores = null;
                }

                $standingsService->saveScore([
                    'competition_id' => $competition->id,
                    'patrol_id' => $patrol->id,
                    'judge_id' => $judge?->id,
                    'criteria_scores' => $criteriaScores,
                    'final_score' => $criteriaScores ? 0 : rand(75, 95),
                    'notes' => 'Penilaian resmi dewan juri pos perlombaan.',
                    'is_locked' => true,
                ]);
            }
        }
    }
}

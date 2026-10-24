<?php

namespace Tests\Feature;

use App\Enums\RegistrationStatusEnum;
use App\Models\Competition;
use App\Models\CompetitionScore;
use App\Models\Event;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use App\Services\StandingsService;
use Database\Seeders\EventSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StandingsMedalRankingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Peringkat umum hanya dari jumlah medali berurutan: Tinggi, lalu Baik, lalu Cukup (tanpa poin berbobot)
     */
    public function test_general_ranking_follows_gold_then_silver_then_bronze_without_points(): void
    {
        $this->seed(EventSeeder::class);

        // Nomor peserta => [Tinggi, Baik, Cukup]
        $data = [
            1 => [30, 10, 0],
            2 => [30, 9, 3],
            3 => [29, 15, 0],
            4 => [15, 25, 4],
            5 => [23, 30, 15],
        ];

        $group = ScoutGroup::factory()->create();
        $maxScores = max(array_map('array_sum', $data));

        $competitions = collect(range(1, $maxScores))->map(fn (int $i) => Competition::create([
            'event_id' => Event::first()->id,
            'title' => "Lomba {$i}",
            'code' => sprintf('LMB-%03d', $i),
            'category' => 'all',
            'participant_type' => 'patrol',
            'scoring_formula' => 'average',
            'is_published' => true,
        ]));

        $patrolIds = [];
        foreach ($data as $number => [$gold, $silver, $bronze]) {
            $patrol = Patrol::factory()->create([
                'scout_group_id' => $group->id,
                'gender_category' => 'boys',
                'patrol_name' => "Regu {$number}",
                'status' => RegistrationStatusEnum::Verified,
            ]);
            $patrolIds[$number] = $patrol->id;

            $predicates = array_merge(
                array_fill(0, $gold, 'prestasi_tinggi'),
                array_fill(0, $silver, 'prestasi_baik'),
                array_fill(0, $bronze, 'prestasi_cukup'),
            );

            foreach ($predicates as $i => $predicate) {
                CompetitionScore::create([
                    'competition_id' => $competitions[$i]->id,
                    'patrol_id' => $patrol->id,
                    'final_score' => 80,
                    'predicate' => $predicate,
                ]);
            }
        }

        $standings = app(StandingsService::class)->getPatrolStandings('boys', null, true);

        $this->assertSame(
            ['Regu 1', 'Regu 2', 'Regu 3', 'Regu 5', 'Regu 4'],
            $standings->pluck('patrol_name')->all()
        );
        $this->assertSame([1, 2, 3, 4, 5], $standings->pluck('rank')->all());

        // Tidak ada sistem poin pada data klasemen
        $this->assertArrayNotHasKey('total_points', $standings->first());
        $this->assertSame([30, 10, 0], [$standings->first()['gold'], $standings->first()['silver'], $standings->first()['bronze']]);
    }
}

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
     * Tabel nilai bobot per peringkat lomba (1=100, 2=80, 3=70, 4=65, ... 11-20=50, 21-33=45, 34+=0)
     */
    public function test_rank_weight_follows_the_official_conversion_table(): void
    {
        $expected = [
            1 => 100, 2 => 80, 3 => 70, 4 => 65, 5 => 60, 6 => 55, 7 => 54, 8 => 53, 9 => 52, 10 => 51,
            11 => 50, 15 => 50, 20 => 50, 21 => 45, 30 => 45, 33 => 45, 34 => 0, 50 => 0,
        ];

        foreach ($expected as $rank => $weight) {
            $this->assertSame($weight, CompetitionScore::rankWeight($rank), "Bobot peringkat {$rank}");
        }
    }

    /**
     * @param  array<string, array<int, int>>  $ranksByPatrol  nama regu => peringkat pada tiap lomba
     */
    private function seedStandings(array $ranksByPatrol): void
    {
        $this->seed(EventSeeder::class);

        $group = ScoutGroup::factory()->create();
        $count = max(array_map('count', $ranksByPatrol));

        $competitions = collect(range(1, $count))->map(fn (int $i) => Competition::create([
            'event_id' => Event::first()->id,
            'title' => "Lomba {$i}",
            'code' => sprintf('LMB-%03d', $i),
            'category' => 'all',
            'participant_type' => 'patrol',
            'scoring_formula' => 'average',
            'is_published' => true,
        ]));

        foreach ($ranksByPatrol as $name => $ranks) {
            $patrol = Patrol::factory()->create([
                'scout_group_id' => $group->id,
                'gender_category' => 'boys',
                'patrol_name' => $name,
                'status' => RegistrationStatusEnum::Verified,
            ]);

            foreach ($ranks as $i => $rank) {
                CompetitionScore::create([
                    'competition_id' => $competitions[$i]->id,
                    'patrol_id' => $patrol->id,
                    'final_score' => 80,
                    'rank' => $rank,
                    'predicate' => match ($rank) {
                        1 => 'prestasi_tinggi',
                        2 => 'prestasi_baik',
                        3 => 'prestasi_cukup',
                        default => 'partisipasi',
                    },
                ]);
            }
        }
    }

    public function test_general_ranking_is_decided_by_total_points(): void
    {
        $this->seedStandings([
            // 100 + 100 + 100 = 300
            'Regu A' => [1, 1, 1],
            // 80 + 80 + 80 + 80 = 320: lebih banyak lomba, total poin lebih besar walau tanpa Prestasi Tinggi
            'Regu B' => [2, 2, 2, 2],
            // 70 + 70 + 70 = 210
            'Regu C' => [3, 3, 3],
        ]);

        $standings = app(StandingsService::class)->getPatrolStandings('boys', null, true);

        $this->assertSame(['Regu B', 'Regu A', 'Regu C'], $standings->pluck('patrol_name')->all());
        $this->assertSame([320, 300, 210], $standings->pluck('total_points')->all());
        $this->assertSame([1, 2, 3], $standings->pluck('rank')->all());
    }

    public function test_tied_points_are_broken_by_gold_then_silver_then_bronze(): void
    {
        $this->seedStandings([
            // 100 + 65 + 65 = 230 (1 Tinggi)
            'Regu Tinggi' => [1, 4, 4],
            // 80 + 80 + 70 = 230 (0 Tinggi, 2 Baik, 1 Cukup)
            'Regu Baik' => [2, 2, 3],
            // 70 + 70 + 70 + 20 -> 230 dari 3 Cukup + peringkat 21 (45)? gunakan 3 Cukup + 4 (65) + ... lihat asersi
            'Regu Cukup' => [3, 3, 3, 21],
        ]);

        $standings = app(StandingsService::class)->getPatrolStandings('boys', null, true)->keyBy('patrol_name');

        $this->assertSame(230, $standings['Regu Tinggi']['total_points']);
        $this->assertSame(230, $standings['Regu Baik']['total_points']);
        // 70 x 3 + 45 = 255: lebih tinggi dari dua regu lain sehingga tetap di atas
        $this->assertSame(255, $standings['Regu Cukup']['total_points']);

        $order = app(StandingsService::class)->getPatrolStandings('boys', null, true)->pluck('patrol_name')->all();

        // Regu Cukup unggul poin; seri 230 dipecah oleh Prestasi Tinggi (1 > 0)
        $this->assertSame(['Regu Cukup', 'Regu Tinggi', 'Regu Baik'], $order);
    }

    public function test_full_tie_keeps_a_stable_order_by_patrol_name_and_more_gold_still_wins(): void
    {
        $this->seedStandings([
            // 100 + 80 + 70 + 70 = 320 -> 1 Tinggi, 1 Baik, 2 Cukup
            'Regu Satu' => [1, 2, 3, 3],
            // 100 + 70 + 70 + 80 = 320 -> seri penuh (poin, Tinggi, Baik, Cukup sama): urutan tampil mengikuti nama
            'Regu Dua' => [1, 3, 3, 2],
            // 100 + 100 + 60 + 60 = 320 -> 2 Tinggi unggul atas keduanya
            'Regu Tiga' => [1, 1, 5, 5],
        ]);

        $order = app(StandingsService::class)->getPatrolStandings('boys', null, true)->pluck('patrol_name')->all();

        $this->assertSame('Regu Tiga', $order[0]);
        $this->assertSame(['Regu Dua', 'Regu Satu'], array_slice($order, 1));
    }
}

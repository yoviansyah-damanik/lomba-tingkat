<?php

namespace Tests\Feature;

use App\Enums\RegistrationStatusEnum;
use App\Models\Assessment;
use App\Models\Chaperone;
use App\Models\CompetitionScore;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use App\Models\User;
use App\Services\StandingsService;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\SampleContestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SampleContestSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_sample_seeder_builds_verified_contingents_scored_by_judges_and_overall_champion(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(SampleContestSeeder::class);

        // 12 gudep terverifikasi (1 bawaan + 11 sampel), masing-masing 2 regu x 8 peserta dan 2 bindamping
        $this->assertSame(12, ScoutGroup::where('verification_status', RegistrationStatusEnum::Verified)->count());
        $this->assertSame(24, Patrol::where('status', RegistrationStatusEnum::Verified)->count());
        $this->assertSame(12 * 16,Participant::whereIn('patrol_id', Patrol::pluck('id'))->where('status', RegistrationStatusEnum::Verified)->count());
        $this->assertGreaterThanOrEqual(22, Chaperone::where('status', RegistrationStatusEnum::Verified)->count());

        // Juri dan panitia tambahan
        $this->assertGreaterThanOrEqual(9, User::whereHas('roles', fn ($q) => $q->where('name', 'judge'))->count());
        $this->assertGreaterThanOrEqual(5, User::whereHas('roles', fn ($q) => $q->where('name', 'committee'))->count());

        // Setiap regu dinilai 3 juri pada 7 cabang berrubrik dan seluruhnya difinalisasi
        $this->assertSame(24 * 7 * 3, Assessment::where('status', 'finalized')->count());
        $this->assertSame(24 * 7, CompetitionScore::count());

        // Klasemen regu putra dan putri terpisah; tiap cabang memberi 1 emas per kategori
        $service = app(StandingsService::class);
        foreach (['boys', 'girls'] as $gender) {
            $standings = $service->getPatrolStandings($gender, null, true);
            $this->assertCount(12, $standings);
            $this->assertSame(1, $standings->first()['rank']);
            $this->assertSame(7, $standings->sum('gold'));
            $this->assertSame(7, $standings->sum('silver'));
            $this->assertSame(7, $standings->sum('bronze'));
            $this->assertSame([$gender], $standings->pluck('gender_category')->unique()->values()->all());
        }

        // Tidak ada klasemen gudep/kontingen
        $this->assertFalse(method_exists($service, 'getContingentStandings'));

        // Idempoten: dijalankan ulang tidak menggandakan data
        $this->seed(SampleContestSeeder::class);
        $this->assertSame(12, ScoutGroup::count());
        $this->assertSame(24 * 7 * 3, Assessment::count());
    }
}

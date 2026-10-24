<?php

namespace Tests\Feature;

use App\Enums\RegistrationStatusEnum;
use App\Models\Assessment;
use App\Models\Chaperone;
use App\Models\Competition;
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

    public function test_sample_seeder_builds_unverified_contingents_scored_by_judges_and_standings_after_verification(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(SampleContestSeeder::class);

        // Seluruh gudep (11 sampel + gudep01) berstatus Diajukan; tidak ada yang diverifikasi
        $this->assertSame(0, ScoutGroup::where('verification_status', RegistrationStatusEnum::Verified)->count());
        $this->assertSame(12, ScoutGroup::where('verification_status', RegistrationStatusEnum::Submitted)->count());
        $this->assertSame(0, Patrol::where('status', RegistrationStatusEnum::Verified)->count());
        $this->assertSame(24, Patrol::where('status', RegistrationStatusEnum::Submitted)->count());
        $this->assertSame(0, Participant::where('status', RegistrationStatusEnum::Verified)->count());
        $this->assertSame(0, Chaperone::where('status', RegistrationStatusEnum::Verified)->count());
        $this->assertGreaterThanOrEqual(22, Chaperone::where('status', RegistrationStatusEnum::Submitted)->count());

        // Juri dan panitia tambahan
        $this->assertGreaterThanOrEqual(9, User::whereHas('roles', fn ($q) => $q->where('name', 'judge'))->count());
        $this->assertGreaterThanOrEqual(5, User::whereHas('roles', fn ($q) => $q->where('name', 'committee'))->count());

        // 23 mata lomba berrubrik sesuai Juknis; setiap regu dinilai 3 juri pada seluruhnya dan difinalisasi
        $lombas = Competition::whereHas('criteria')->count();
        $this->assertSame(23, $lombas);
        $this->assertSame(22 * $lombas * 3, Assessment::where('status', 'finalized')->count());
        $this->assertSame(22 * $lombas, CompetitionScore::count());

        // Belum ada klasemen sebelum gudep diverifikasi Administrator
        $service = app(StandingsService::class);
        $this->assertCount(0, $service->getPatrolStandings('boys', null, true));

        // Setelah diverifikasi, nilai contoh membentuk klasemen
        Patrol::whereIn('id', CompetitionScore::pluck('patrol_id'))->update(['status' => RegistrationStatusEnum::Verified]);

        // Klasemen regu putra dan putri terpisah; tiap lomba memberi 1 emas, 1 perak, 1 perunggu per kategori
        foreach (['boys', 'girls'] as $gender) {
            $standings = $service->getPatrolStandings($gender, null, true);
            $this->assertCount(11, $standings);
            $this->assertSame(1, $standings->first()['rank']);
            $this->assertSame($lombas, $standings->sum('gold'));
            $this->assertSame($lombas, $standings->sum('silver'));
            $this->assertSame($lombas, $standings->sum('bronze'));
            $this->assertSame([$gender], $standings->pluck('gender_category')->unique()->values()->all());
        }

        // Tidak ada klasemen gudep/kontingen
        $this->assertFalse(method_exists($service, 'getContingentStandings'));

        // Idempoten: dijalankan ulang tidak menggandakan data
        $this->seed(SampleContestSeeder::class);
        $this->assertSame(12, ScoutGroup::count());
        $this->assertSame(22 * $lombas * 3, Assessment::count());
    }
}

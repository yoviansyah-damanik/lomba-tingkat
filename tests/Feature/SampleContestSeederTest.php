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

    public function test_sample_seeder_builds_submitted_and_one_verified_contingent_scored_by_judges_with_standings(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(SampleContestSeeder::class);

        // Satu gudep contoh (2 regu putra + 2 regu putri) sudah terverifikasi; 11 gudep lain berstatus Diajukan
        $this->assertSame(1, ScoutGroup::where('verification_status', RegistrationStatusEnum::Verified)->count());
        $this->assertSame(11, ScoutGroup::where('verification_status', RegistrationStatusEnum::Submitted)->count());
        $this->assertSame(4, Patrol::where('status', RegistrationStatusEnum::Verified)->count());
        $this->assertSame(25, Patrol::where('status', RegistrationStatusEnum::Submitted)->count());
        $this->assertSame(32, Participant::where('status', RegistrationStatusEnum::Verified)->count());
        $this->assertSame(2, Chaperone::where('status', RegistrationStatusEnum::Verified)->count());
        $this->assertGreaterThanOrEqual(20, Chaperone::where('status', RegistrationStatusEnum::Submitted)->count());

        // Juri dan panitia tambahan
        $this->assertGreaterThanOrEqual(9, User::whereHas('roles', fn ($q) => $q->where('name', 'judge'))->count());
        $this->assertGreaterThanOrEqual(5, User::whereHas('roles', fn ($q) => $q->where('name', 'committee'))->count());

        // 17 mata lomba berrubrik sesuai Juknis; setiap regu dinilai 3 juri pada seluruhnya dan difinalisasi
        $lombas = Competition::whereHas('criteria')->count();
        $this->assertSame(17, $lombas);
        $this->assertSame(27 * $lombas * 3, Assessment::where('status', 'finalized')->count());
        $this->assertSame(27 * $lombas, CompetitionScore::count());

        // Klasemen hanya memuat regu gudep terverifikasi (2 putra, 2 putri)
        $service = app(StandingsService::class);
        $this->assertCount(2, $service->getPatrolStandings('boys', null, true));
        $this->assertCount(2, $service->getPatrolStandings('girls', null, true));

        // Setelah seluruh gudep diverifikasi, nilai contoh membentuk klasemen lengkap
        Patrol::whereIn('id', CompetitionScore::pluck('patrol_id'))->update(['status' => RegistrationStatusEnum::Verified]);

        // Klasemen regu putra dan putri terpisah; tiap lomba memberi 1 emas, 1 perak, 1 perunggu per kategori
        foreach (['boys', 'girls'] as $gender) {
            $standings = $service->getPatrolStandings($gender, null, true);
            $this->assertCount($gender === 'boys' ? 14 : 13, $standings);
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
        $this->assertSame(27 * $lombas * 3, Assessment::count());
    }
}

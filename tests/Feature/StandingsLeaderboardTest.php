<?php

namespace Tests\Feature;

use App\Enums\PatrolGenderEnum;
use App\Enums\RegistrationStatusEnum;
use App\Livewire\Admin\StandingsLeaderboard;
use App\Models\Competition;
use App\Models\CompetitionScore;
use App\Models\Patrol;
use App\Models\Role;
use App\Models\ScoutGroup;
use App\Models\User;
use App\Services\StandingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StandingsLeaderboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $judge;
    protected User $scoutUser;
    protected ScoutGroup $scoutGroup;
    protected Patrol $patrol;
    protected Competition $competition;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        $adminRole = Role::where('name', 'admin')->first();
        $judgeRole = Role::where('name', 'judge')->first();
        $scoutRole = Role::where('name', 'scout_group')->first();

        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($adminRole);

        $this->judge = User::factory()->create();
        $this->judge->roles()->attach($judgeRole);

        $this->scoutUser = User::factory()->create();
        $this->scoutUser->roles()->attach($scoutRole);

        $this->scoutGroup = ScoutGroup::create([
            'user_id' => $this->scoutUser->id,
            'registration_code' => 'GDP-TEST-01',
            'school_name' => 'SMP Negeri 1 Padangsidimpuan',
            'group_number' => '01-001 & 01-002',
            'male_group_number' => '01-001',
            'female_group_number' => '01-002',
            'address' => 'Jl. Teuku Umar No. 12',
            'contact_person' => 'Kak Pembina',
            'contact_phone' => '081234567890',
            'verification_status' => RegistrationStatusEnum::Verified,
        ]);

        $this->patrol = Patrol::create([
            'scout_group_id' => $this->scoutGroup->id,
            'patrol_name' => 'Rajawali',
            'gender_category' => PatrolGenderEnum::Boys,
            'status' => RegistrationStatusEnum::Verified,
        ]);

        $this->competition = Competition::create([
            'code' => 'PBB-01',
            'title' => 'PBB Berisyarat dan Koloni Tongkat',
            'category' => 'all',
            'participant_type' => 'patrol',
            'min_participants' => 8,
            'max_participants' => 8,
            'scoring_formula' => 'average',
            'is_published' => true,
        ]);
    }

    /**
     * Memastikan guest diarahkan ke halaman login
     */
    public function test_guest_is_redirected_from_standings_page(): void
    {
        $response = $this->get(route('admin.standings.index'));
        $response->assertRedirect(route('login'));

        $responsePublic = $this->get(route('standings'));
        $responsePublic->assertRedirect(route('login'));
    }

    /**
     * Memastikan administrator dapat mengakses halaman rekap nilai dan klasemen
     */
    public function test_admin_can_access_standings_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.standings.index'));
        $response->assertOk();
        $response->assertSee('Rekap Nilai & Klasemen');
        $response->assertSee('SMP Negeri 1 Padangsidimpuan');
    }

    /**
     * Memastikan juri dan pangkalan gudep dapat mengakses route standings dashboard
     */
    public function test_judge_and_scout_group_can_access_dashboard_standings(): void
    {
        $responseJudge = $this->actingAs($this->judge)->get(route('standings'));
        $responseJudge->assertOk();
        $responseJudge->assertSee('Rekap Nilai & Klasemen');

        $responseScout = $this->actingAs($this->scoutUser)->get(route('standings'));
        $responseScout->assertOk();
        $responseScout->assertSee('Rekap Nilai & Klasemen');
    }

    /**
     * Memastikan komponen Livewire 4 dapat beralih tab dan filter gender
     */
    public function test_livewire_can_switch_tabs_and_gender_filter(): void
    {
        Livewire::actingAs($this->admin, 'web')
            ->test(StandingsLeaderboard::class)
            ->assertSet('activeTab', 'contingents')
            ->set('activeTab', 'patrols')
            ->assertSet('activeTab', 'patrols')
            ->set('genderFilter', 'boys')
            ->assertSet('genderFilter', 'boys')
            ->set('activeTab', 'competitions')
            ->assertSet('activeTab', 'competitions');
    }

    /**
     * Memastikan penilaian lomba dapat diinput dan otomatis menentukan rank & predikat
     */
    public function test_admin_can_save_score_and_auto_calculate_ranks(): void
    {
        // 1. Buat regu kedua untuk uji ranking
        $patrol2 = Patrol::create([
            'scout_group_id' => $this->scoutGroup->id,
            'patrol_name' => 'Garuda',
            'gender_category' => PatrolGenderEnum::Boys,
            'status' => RegistrationStatusEnum::Verified,
        ]);

        $standingsService = app(StandingsService::class);

        // Regu 1 dapat nilai 95
        $standingsService->saveScore([
            'competition_id' => $this->competition->id,
            'patrol_id' => $this->patrol->id,
            'judge_id' => $this->judge->id,
            'final_score' => 95.0,
            'notes' => 'Sangat rapi dan seragam.',
        ]);

        // Regu 2 dapat nilai 88
        $standingsService->saveScore([
            'competition_id' => $this->competition->id,
            'patrol_id' => $patrol2->id,
            'judge_id' => $this->judge->id,
            'final_score' => 88.0,
            'notes' => 'Cukup kompak.',
        ]);

        $score1 = CompetitionScore::where('patrol_id', $this->patrol->id)->first();
        $score2 = CompetitionScore::where('patrol_id', $patrol2->id)->first();

        $this->assertEquals(1, $score1->rank);
        $this->assertEquals('prestasi_tinggi', $score1->predicate);

        $this->assertEquals(2, $score2->rank);
        $this->assertEquals('prestasi_baik', $score2->predicate);

        // Uji di halaman Livewire
        Livewire::actingAs($this->admin, 'web')
            ->test(StandingsLeaderboard::class, ['selectedCompetitionId' => $this->competition->id])
            ->set('activeTab', 'competitions')
            ->assertSee('95.00')
            ->assertSee('Prestasi Tinggi')
            ->assertSee('88.00')
            ->assertSee('Prestasi Baik');
    }

    /**
     * Memastikan lock nilai dapat di-toggle oleh administrator
     */
    public function test_admin_can_toggle_score_lock(): void
    {
        $score = CompetitionScore::create([
            'competition_id' => $this->competition->id,
            'patrol_id' => $this->patrol->id,
            'judge_id' => $this->judge->id,
            'final_score' => 90.0,
            'is_locked' => false,
        ]);

        Livewire::actingAs($this->admin, 'web')
            ->test(StandingsLeaderboard::class)
            ->call('toggleScoreLock', $score->id);

        $this->assertTrue($score->fresh()->is_locked);
    }

    /**
     * Memastikan panitia sama sekali tidak memiliki hak untuk melakukan input penilaian di lomba manapun
     */
    public function test_committee_cannot_manage_or_input_scores_in_any_competition(): void
    {
        $committeeRole = Role::where('name', 'committee')->first();
        $committee = User::factory()->create();
        $committee->roles()->attach($committeeRole);

        $this->assertTrue($committee->isCommittee());
        $this->assertFalse($committee->isJudge());
        $this->assertFalse($committee->isAdmin());

        $score = CompetitionScore::create([
            'competition_id' => $this->competition->id,
            'patrol_id' => $this->patrol->id,
            'judge_id' => $this->judge->id,
            'final_score' => 85.0,
            'is_locked' => false,
        ]);

        $component = Livewire::actingAs($committee, 'web')
            ->test(StandingsLeaderboard::class, ['selectedCompetitionId' => $this->competition->id])
            ->set('activeTab', 'competitions');

        // Hak akses kelola nilai harus false
        $this->assertFalse($component->get('canManageScores'));

        // Tombol input dan kalkulasi tidak boleh ada
        $component->assertDontSee('Input Nilai Regu')
            ->assertDontSee('Kalkulasi Ulang Rank');

        // Membuka modal penilaian harus ditolak (modal tetap tertutup)
        $component->call('openScoreModal', $this->patrol->id)
            ->assertSet('showScoreModal', false);

        // Mencoba menyimpan penilaian via modal harus ditolak (data nilai tidak berubah)
        $component->set('modalPatrolId', $this->patrol->id)
            ->set('modalFinalScore', 99.0)
            ->call('saveScoreModal');

        // Nilai di database tidak berubah
        $this->assertEquals(85.0, (float) $score->fresh()->final_score);

        // Mengunci atau membuka kunci nilai harus ditolak
        $component->call('toggleScoreLock', $score->id);
        $this->assertFalse($score->fresh()->is_locked);

        // Rekalkulasi rank harus ditolak tanpa mengubah apapun
        $component->call('recalculateRanks');
        $this->assertEquals(85.0, (float) $score->fresh()->final_score);
    }

    /**
     * Memastikan dewan juri memiliki hak akses input penilaian lomba
     */
    public function test_judge_has_rights_to_manage_scores(): void
    {
        $component = Livewire::actingAs($this->judge, 'web')
            ->test(StandingsLeaderboard::class, ['selectedCompetitionId' => $this->competition->id])
            ->set('activeTab', 'competitions');

        $this->assertTrue($component->get('canManageScores'));
        $this->assertTrue($component->get('canInputScores'));
        $component->assertSee('Input Nilai Regu');
    }

    /**
     * Memastikan Administrator dilarang melakukan input nilai di klasemen (hanya Juri)
     */
    public function test_admin_cannot_input_score_in_standings_leaderboard(): void
    {
        $component = Livewire::actingAs($this->admin, 'web')
            ->test(StandingsLeaderboard::class, ['selectedCompetitionId' => $this->competition->id])
            ->set('activeTab', 'competitions');

        // canManageScores tetap true untuk kalkulasi rank & toggle lock
        $this->assertTrue($component->get('canManageScores'));
        // Namun canInputScores mutlak false untuk Administrator
        $this->assertFalse($component->get('canInputScores'));

        // Tombol input nilai tidak boleh terlihat oleh Administrator
        $component->assertDontSee('Input Nilai Regu');

        // Membuka modal penilaian ditolak
        $component->call('openScoreModal', $this->patrol->id)
            ->assertSet('showScoreModal', false);

        // Menyimpan nilai ditolak
        $component->set('modalPatrolId', $this->patrol->id)
            ->set('modalFinalScore', 99.0)
            ->call('saveScoreModal');

        $this->assertDatabaseMissing('competition_scores', [
            'patrol_id' => $this->patrol->id,
            'final_score' => 99.0,
        ]);
    }

    /**
     * Memastikan peringkat umum ditentukan oleh jumlah perolehan medali (Prestasi Tinggi setara emas)
     * bukan berdasarkan akumulasi nilai numerik.
     */
    public function test_standings_ranking_is_determined_by_medals_not_accumulated_scores(): void
    {
        $standingsService = app(StandingsService::class);

        // Buat sekolah dan regu kedua
        $group2 = \App\Models\ScoutGroup::factory()->create([
            'school_name' => 'SMP Swasta Harapan',
            'group_number' => '02-002',
            'verification_status' => \App\Enums\RegistrationStatusEnum::Verified,
        ]);
        $patrol2 = \App\Models\Patrol::factory()->create([
            'scout_group_id' => $group2->id,
            'patrol_name' => 'Garuda',
            'gender_category' => 'boys',
            'status' => \App\Enums\RegistrationStatusEnum::Verified,
        ]);

        // Buat lomba kedua
        $comp2 = \App\Models\Competition::create([
            'code' => 'PIO-02',
            'title' => 'Lomba Pionering',
            'category' => 'all',
            'participant_type' => 'patrol',
            'scoring_formula' => 'rubric',
            'is_published' => true,
        ]);
        // Buat lomba ketiga
        $comp3 = \App\Models\Competition::create([
            'code' => 'MOR-03',
            'title' => 'Lomba Morse',
            'category' => 'all',
            'participant_type' => 'patrol',
            'scoring_formula' => 'rubric',
            'is_published' => true,
        ]);

        // Buat regu ketiga sebagai pemenang emas di lomba 2
        $patrol3 = \App\Models\Patrol::factory()->create([
            'scout_group_id' => $group2->id,
            'patrol_name' => 'Singa',
            'gender_category' => 'boys',
            'status' => \App\Enums\RegistrationStatusEnum::Verified,
        ]);

        // Lomba 1: Patrol 1 (Elang) dapat Emas (skor 90)
        // Patrol 2 (Garuda) dapat Perak (skor 85)
        $standingsService->saveScore([
            'competition_id' => $this->competition->id,
            'patrol_id' => $this->patrol->id,
            'judge_id' => $this->judge->id,
            'final_score' => 90.0,
        ]);
        $standingsService->saveScore([
            'competition_id' => $this->competition->id,
            'patrol_id' => $patrol2->id,
            'judge_id' => $this->judge->id,
            'final_score' => 85.0,
        ]);

        // Lomba 2: Patrol 3 (Singa) dapat Emas (skor 99)
        // Patrol 2 (Garuda) dapat Perak (skor 95)
        // Patrol 1 (Elang) dapat Perunggu (skor 80)
        $standingsService->saveScore([
            'competition_id' => $comp2->id,
            'patrol_id' => $patrol3->id,
            'judge_id' => $this->judge->id,
            'final_score' => 99.0,
        ]);
        $standingsService->saveScore([
            'competition_id' => $comp2->id,
            'patrol_id' => $patrol2->id,
            'judge_id' => $this->judge->id,
            'final_score' => 95.0,
        ]);
        $standingsService->saveScore([
            'competition_id' => $comp2->id,
            'patrol_id' => $this->patrol->id,
            'judge_id' => $this->judge->id,
            'final_score' => 80.0,
        ]);

        // Patrol 2 memiliki total skor numerik tinggi: 85 + 95 = 180.00, tapi 0 Emas, 2 Perak
        // Patrol 1 memiliki total skor numerik lebih rendah: 90 + 80 = 170.00, tapi 1 Emas, 1 Perunggu
        // Karena 1 Emas (Prestasi Tinggi) mengalahkan perak berapapun, Patrol 1 harus tetap lebih unggul dari Patrol 2!
        $patrolStandings = $standingsService->getPatrolStandings('boys');

        $firstPatrol = $patrolStandings->firstWhere('id', $this->patrol->id);
        $secondPatrol = $patrolStandings->firstWhere('id', $patrol2->id);

        $thirdPatrol = $patrolStandings->firstWhere('id', $patrol3->id);

        // Patrol 1: 1 Emas, 1 Perunggu -> Peringkat 1
        $this->assertEquals(1, $firstPatrol['rank']);
        $this->assertEquals('Regu Prestasi Tinggi Putra', $firstPatrol['general_rank_title']);
        $this->assertEquals(1, $firstPatrol['gold']);
        $this->assertEquals(1, $firstPatrol['bronze']);

        // Patrol 3: 1 Emas, 0 Perunggu -> Peringkat 2
        $this->assertEquals(2, $thirdPatrol['rank']);
        $this->assertEquals('Regu Prestasi Baik Putra', $thirdPatrol['general_rank_title']);

        // Patrol 2: 0 Emas, 2 Perak -> Peringkat 3 (Kalah dari peraih Emas meskipun total skor numerik lebih tinggi)
        $this->assertEquals(3, $secondPatrol['rank']);
        $this->assertEquals('Regu Prestasi Cukup Putra', $secondPatrol['general_rank_title']);
        $this->assertEquals(0, $secondPatrol['gold']);
        $this->assertEquals(2, $secondPatrol['silver']);

        // Uji juga label predikat pada skor
        $score = CompetitionScore::where('patrol_id', $this->patrol->id)
            ->where('competition_id', $this->competition->id)
            ->first();
        $this->assertEquals('Prestasi Tinggi', $score->predicateLabel());
    }

    /**
     * Memastikan peringkat umum regu dipisah secara independen untuk Putra dan Putri
     */
    public function test_patrol_general_standings_are_separated_by_gender(): void
    {
        $standingsService = app(StandingsService::class);

        // Buat regu putri
        $girlsPatrol = Patrol::create([
            'scout_group_id' => $this->scoutGroup->id,
            'patrol_name' => 'Mawar Indah',
            'gender_category' => PatrolGenderEnum::Girls,
            'status' => RegistrationStatusEnum::Verified,
        ]);

        // Berikan medali emas / prestasi tinggi untuk kedua regu (Putra dan Putri)
        $standingsService->saveScore([
            'competition_id' => $this->competition->id,
            'patrol_id' => $this->patrol->id, // Regu Elang (Putra)
            'judge_id' => $this->judge->id,
            'final_score' => 95.0,
        ]);

        $standingsService->saveScore([
            'competition_id' => $this->competition->id,
            'patrol_id' => $girlsPatrol->id, // Regu Mawar (Putri)
            'judge_id' => $this->judge->id,
            'final_score' => 96.0,
        ]);

        // Cek getPatrolStandings('all')
        $allStandings = $standingsService->getPatrolStandings('all');

        $boysRank1 = $allStandings->firstWhere('id', $this->patrol->id);
        $girlsRank1 = $allStandings->firstWhere('id', $girlsPatrol->id);

        // Kedua regu harus memiliki Rank #1 di masing-masing kategorinya
        $this->assertEquals(1, $boysRank1['rank']);
        $this->assertEquals('Regu Prestasi Tinggi Putra', $boysRank1['general_rank_title']);

        $this->assertEquals(1, $girlsRank1['rank']);
        $this->assertEquals('Regu Prestasi Tinggi Putri', $girlsRank1['general_rank_title']);

        // Cek summary juga memisahkan top_boys_patrols dan top_girls_patrols
        $summary = $standingsService->getStandingsSummary();
        $this->assertArrayHasKey('top_boys_patrols', $summary);
        $this->assertArrayHasKey('top_girls_patrols', $summary);
        $this->assertEquals($this->patrol->id, $summary['top_boys_patrols']->first()['id']);
        $this->assertEquals($girlsPatrol->id, $summary['top_girls_patrols']->first()['id']);
    }

    /**
     * Memastikan StandingsService menyamarkan identitas jika hide_patrol_identities aktif dan bisa di-unmask
     */
    public function test_standings_identities_masking_and_admin_unmasking(): void
    {
        \App\Models\Event::create([
            'title' => 'LT III Kwarcab Padangsidimpuan 2026',
            'location' => 'Bumi Perkemahan Tor Simarsayang',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'is_active' => true,
            'hide_patrol_identities' => true,
        ]);

        $standingsService = app(StandingsService::class);

        // Panggilan default (tanpa unmask): nama harus disensor
        $maskedStandings = $standingsService->getPatrolStandings('boys', null, false);
        $patrolData = $maskedStandings->firstWhere('id', $this->patrol->id);

        $this->assertTrue($patrolData['is_masked']);
        $this->assertStringContainsString('Regu #', $patrolData['patrol_name']);
        $this->assertEquals('Pangkalan Dirahasiakan', $patrolData['school_name']);
        $this->assertEquals('***', $patrolData['group_number']);

        // Panggilan dengan unmask = true (oleh admin): nama asli tampil
        $unmaskedStandings = $standingsService->getPatrolStandings('boys', null, true);
        $unmaskedPatrolData = $unmaskedStandings->firstWhere('id', $this->patrol->id);

        $this->assertFalse($unmaskedPatrolData['is_masked']);
        $this->assertEquals('Rajawali', $unmaskedPatrolData['patrol_name']);
        $this->assertEquals('SMP Negeri 1 Padangsidimpuan', $unmaskedPatrolData['school_name']);
        $this->assertEquals('01-001', $unmaskedPatrolData['group_number']);
    }
}


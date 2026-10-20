<?php

namespace Tests\Feature;

use App\Enums\PatrolGenderEnum;
use App\Enums\RegistrationStatusEnum;
use App\Events\StandingsUpdated;
use App\Livewire\Public\LeaderboardPage;
use App\Models\Competition;
use App\Models\CompetitionScore;
use App\Models\Patrol;
use App\Models\Role;
use App\Models\ScoutGroup;
use App\Models\User;
use App\Services\StandingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Tests\TestCase;

class PublicLeaderboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $judge;
    protected ScoutGroup $school1;
    protected ScoutGroup $school2;
    protected Patrol $patrol1;
    protected Patrol $patrol2;
    protected Competition $comp1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        $judgeRole = Role::where('name', 'judge')->first();
        $this->judge = User::factory()->create();
        $this->judge->roles()->attach($judgeRole);

        $scoutRole = Role::where('name', 'scout_group')->first();
        $user1 = User::factory()->create();
        $user1->roles()->attach($scoutRole);
        $user2 = User::factory()->create();
        $user2->roles()->attach($scoutRole);

        $this->school1 = ScoutGroup::create([
            'user_id' => $user1->id,
            'registration_code' => 'GDP-TEST-01',
            'school_name' => 'SMP Negeri 1 Padangsidimpuan',
            'group_number' => '01-001',
            'kwarran_name' => 'Padangsidimpuan Utara',
            'address' => 'Jl. Teuku Umar No. 1',
            'contact_person' => 'Pembina Satu',
            'contact_phone' => '081234567891',
            'verification_status' => RegistrationStatusEnum::Verified,
        ]);

        $this->school2 = ScoutGroup::create([
            'user_id' => $user2->id,
            'registration_code' => 'GDP-TEST-02',
            'school_name' => 'MTs Negeri 2 Padangsidimpuan',
            'group_number' => '02-001',
            'kwarran_name' => 'Padangsidimpuan Selatan',
            'address' => 'Jl. Imam Bonjol No. 2',
            'contact_person' => 'Pembina Dua',
            'contact_phone' => '081234567892',
            'verification_status' => RegistrationStatusEnum::Verified,
        ]);

        $this->patrol1 = Patrol::create([
            'scout_group_id' => $this->school1->id,
            'patrol_name' => 'Rajawali',
            'gender_category' => PatrolGenderEnum::Boys,
            'status' => RegistrationStatusEnum::Verified,
        ]);

        $this->patrol2 = Patrol::create([
            'scout_group_id' => $this->school2->id,
            'patrol_name' => 'Garuda',
            'gender_category' => PatrolGenderEnum::Boys,
            'status' => RegistrationStatusEnum::Verified,
        ]);

        $this->comp1 = Competition::create([
            'code' => 'PBB-01',
            'title' => 'PBB Berisyarat',
            'category' => 'all',
            'participant_type' => 'patrol',
            'scoring_formula' => 'sum',
            'is_published' => true,
        ]);
    }

    /**
     * Memastikan halaman publik klasemen dapat diakses oleh guest tanpa login
     */
    public function test_guest_can_access_public_leaderboard_page(): void
    {
        $response = $this->get(route('leaderboard'));
        $response->assertOk();
        $response->assertSee('Klasemen Resmi Prestasi');
    }

    /**
     * Memastikan klasemen publik menampilkan ranking regu dan tidak lagi menampilkan peringkat gudep/pangkalan
     */
    public function test_leaderboard_displays_patrol_standings_and_no_contingent_ranking_tab(): void
    {
        $standingsService = app(StandingsService::class);

        // Sekolah 1 dapat Juara 1 (Emas / Prestasi Tinggi)
        $standingsService->saveScore([
            'competition_id' => $this->comp1->id,
            'patrol_id' => $this->patrol1->id,
            'judge_id' => $this->judge->id,
            'final_score' => 95.0,
        ]);

        // Sekolah 2 dapat Juara 2 (Perak / Prestasi Baik)
        $standingsService->saveScore([
            'competition_id' => $this->comp1->id,
            'patrol_id' => $this->patrol2->id,
            'judge_id' => $this->judge->id,
            'final_score' => 88.0,
        ]);

        Livewire::test(LeaderboardPage::class)
            ->assertDontSee('Pangkalan (Gugus Depan)') // Tab peringkat gudep sudah dihapus di publik
            ->assertSee('Peringkat Regu Penggalang')
            ->assertSee('SMP Negeri 1 Padangsidimpuan')
            ->assertSee('MTs Negeri 2 Padangsidimpuan')
            ->assertSee('Regu Berprestasi Tinggi')
            ->assertDontSee('Total Skor Poin')
            ->assertDontSee('Akumulasi Nilai')
            ->set('activeTab', 'competitions')
            ->assertDontSee('Nilai Akhir')
            ->assertDontSee('Catatan Dewan Juri')
            ->assertDontSee('95.00')
            ->assertDontSee('88.00')
            ->assertSee('Prestasi Tinggi')
            ->assertSee('Prestasi Baik');
    }

    /**
     * Memastikan peralihan tab pada leaderboard bekerja dengan lancar
     */
    public function test_leaderboard_can_switch_tabs(): void
    {
        Livewire::test(LeaderboardPage::class)
            ->assertSet('activeTab', 'patrols')
            ->call('setTab', 'competitions')
            ->assertSet('activeTab', 'competitions')
            ->call('setTab', 'patrols')
            ->assertSet('activeTab', 'patrols');
    }

    /**
     * Memastikan filter gender dan pencarian berfungsi
     */
    public function test_leaderboard_filters_and_search_work(): void
    {
        Livewire::test(LeaderboardPage::class)
            ->call('setGender', 'boys')
            ->assertSet('genderFilter', 'boys')
            ->set('search', 'Rajawali')
            ->assertSet('search', 'Rajawali');
    }

    /**
     * Memastikan StandingsUpdated event terpicu saat rank dikalkulasi ulang
     */
    public function test_standings_updated_event_is_dispatched(): void
    {
        Event::fake([StandingsUpdated::class]);

        $standingsService = app(StandingsService::class);
        $standingsService->recalculateRanks($this->comp1->id);

        Event::assertDispatched(StandingsUpdated::class, function ($event) {
            return $event->competitionId === $this->comp1->id;
        });
    }

    /**
     * Memastikan leaderboard memisahkan klasemen dan podium regu Putra dan Putri
     */
    public function test_public_leaderboard_displays_separated_boys_and_girls_patrol_rankings(): void
    {
        $girlsPatrol = Patrol::create([
            'scout_group_id' => $this->school2->id,
            'patrol_name' => 'Melati',
            'gender_category' => PatrolGenderEnum::Girls,
            'status' => RegistrationStatusEnum::Verified,
        ]);

        $standingsService = app(StandingsService::class);

        // Putra juara
        $standingsService->saveScore([
            'competition_id' => $this->comp1->id,
            'patrol_id' => $this->patrol1->id,
            'judge_id' => $this->judge->id,
            'final_score' => 95.0,
        ]);

        // Putri juara
        $standingsService->saveScore([
            'competition_id' => $this->comp1->id,
            'patrol_id' => $girlsPatrol->id,
            'judge_id' => $this->judge->id,
            'final_score' => 97.0,
        ]);

        Livewire::test(LeaderboardPage::class)
            ->assertSee('Regu Putra Berprestasi')
            ->assertSee('Regu Putri Berprestasi')
            ->assertSee('Regu Berprestasi Tinggi')
            ->call('setTab', 'patrols')
            // Klasemen putra dan putri tidak digabung: hanya kategori terpilih (bawaan: putra)
            ->assertSet('genderFilter', 'boys')
            ->assertSee('Klasemen Peringkat Umum Regu Penggalang Putra')
            ->assertDontSee('Klasemen Peringkat Umum Regu Penggalang Putri')
            ->assertSee('Rajawali')
            ->call('setGender', 'girls')
            ->assertSee('Klasemen Peringkat Umum Regu Penggalang Putri')
            ->assertDontSee('Klasemen Peringkat Umum Regu Penggalang Putra')
            ->assertSee('Melati')
            // Pilihan "Semua" tidak tersedia
            ->call('setGender', 'all')
            ->assertSet('genderFilter', 'boys')
            ->assertDontSee('setGender(&#039;all&#039;)', false);
    }

    /**
     * Memastikan konfigurasi sensor identitas menyembunyikan nama regu dan pangkalan di publik dan juri/panitia,
     * serta hanya admin yang dapat mengintip nama aslinya.
     */
    public function test_public_leaderboard_hides_patrol_and_school_names_when_configured_and_admin_can_peek(): void
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
        $standingsService->saveScore([
            'competition_id' => $this->comp1->id,
            'patrol_id' => $this->patrol1->id,
            'judge_id' => $this->judge->id,
            'final_score' => 95.0,
        ]);

        $adminRole = Role::where('name', 'admin')->first();
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole);

        $committeeRole = Role::where('name', 'committee')->first();
        $committee = User::factory()->create();
        $committee->roles()->attach($committeeRole);

        // 1. Uji tampilan Tamu (Guest / Publik Umum)
        Livewire::test(LeaderboardPage::class)
            ->assertSee('Mode Sensor Identitas Aktif')
            ->assertDontSee('Intip Identitas (Admin)')
            ->assertSee('Pangkalan Dirahasiakan')
            ->assertDontSee('SMP Negeri 1 Padangsidimpuan')
            ->assertDontSee('Rajawali');

        // 2. Uji tampilan Panitia (Committee) / Juri
        Livewire::actingAs($committee)
            ->test(LeaderboardPage::class)
            ->assertSee('Mode Sensor Identitas Aktif')
            ->assertDontSee('Intip Identitas (Admin)')
            ->assertSee('Pangkalan Dirahasiakan')
            ->assertDontSee('SMP Negeri 1 Padangsidimpuan')
            ->assertDontSee('Rajawali');

        // 3. Uji tampilan Administrator (Bisa mengintip nama asli)
        Livewire::actingAs($admin)
            ->test(LeaderboardPage::class)
            ->assertSee('Mode Sensor Identitas Aktif')
            ->assertSee('Intip Identitas (Admin)')
            ->assertSee('Pangkalan Dirahasiakan') // Saat belum dibuka, tetap disensor
            ->call('toggleUnmask') // Admin membuka sensor / mengintip
            ->assertSee('SMP Negeri 1 Padangsidimpuan')
            ->assertSee('Rajawali');
    }
}


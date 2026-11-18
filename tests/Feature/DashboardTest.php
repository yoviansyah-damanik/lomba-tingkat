<?php

namespace Tests\Feature;

use App\Enums\RegistrationStatusEnum;
use App\Enums\RoleEnum;
use App\Models\Chaperone;
use App\Models\Competition;
use App\Models\CompetitionScore;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_sees_admin_dashboard_metrics(): void
    {
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole(RoleEnum::Admin);

        $response = $this->actingAs($admin)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Menunggu Verifikasi');
        $response->assertSee('Regu Terdaftar');
        $response->assertSee('Peserta Penggalang');
        $response->assertSee('Waktu Server Resmi');
    }

    public function test_scout_group_sees_scout_group_dashboard(): void
    {
        $gudep = User::factory()->create(['is_active' => true]);
        $gudep->assignRole(RoleEnum::ScoutGroup);

        $response = $this->actingAs($gudep)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Status Berkas');
        $response->assertSee('Regu Terdaftar');
        $response->assertSee('Anggota Peserta');
        $response->assertSee('Bindamping');
    }

    public function test_committee_sees_operational_monitoring_dashboard(): void
    {
        $committee = User::factory()->create(['is_active' => true]);
        $committee->assignRole(RoleEnum::Committee);

        $response = $this->actingAs($committee)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Sesi Presensi Aktif');
        $response->assertSee('Regu Sah');
    }

    public function test_judge_sees_judge_dashboard(): void
    {
        $judge = User::factory()->create(['is_active' => true]);
        $judge->assignRole(RoleEnum::Judge);

        $response = $this->actingAs($judge)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Rubrik Juknis Lomba');
        $response->assertSee('Sesi Ditugaskan');
        $response->assertSee('Blind Scoring');
    }

    public function test_participant_sees_participant_dashboard(): void
    {
        $participant = User::factory()->create(['is_active' => true]);
        $participant->assignRole(RoleEnum::Participant);

        $response = $this->actingAs($participant)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Identitas Peserta Regu');
        $response->assertSee('Kartu Digital QR Sah');
    }

    public function test_admin_dashboard_counts_only_verified_members_and_shows_draft_gudep_card(): void
    {
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole(RoleEnum::Admin);

        // Gudep terverifikasi dengan regu, peserta, dan bindamping
        $verifiedGroup = ScoutGroup::factory()->create(['verification_status' => RegistrationStatusEnum::Verified]);
        $verifiedPatrol = Patrol::factory()->create([
            'scout_group_id' => $verifiedGroup->id,
            'gender_category' => 'boys',
            'status' => RegistrationStatusEnum::Verified,
        ]);
        Participant::factory()->count(4)->create([
            'patrol_id' => $verifiedPatrol->id,
            'status' => RegistrationStatusEnum::Verified,
        ]);
        Chaperone::factory()->create([
            'scout_group_id' => $verifiedGroup->id,
            'gender' => 'male',
            'status' => RegistrationStatusEnum::Verified,
        ]);

        // Gudep masih draft dengan peserta dan bindamping draft (tidak boleh dihitung pada statistik sah)
        $draftGroup = ScoutGroup::factory()->create(['verification_status' => RegistrationStatusEnum::Draft]);
        $draftPatrol = Patrol::factory()->create([
            'scout_group_id' => $draftGroup->id,
            'gender_category' => 'boys',
            'status' => RegistrationStatusEnum::Draft,
        ]);
        Participant::factory()->count(8)->create([
            'patrol_id' => $draftPatrol->id,
            'status' => RegistrationStatusEnum::Draft,
        ]);
        Chaperone::factory()->create([
            'scout_group_id' => $draftGroup->id,
            'gender' => 'male',
            'status' => RegistrationStatusEnum::Draft,
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));
        $response->assertOk();

        // Kartu Gudep Masih Draft tampil
        $response->assertSee('Masih Draft');

        // Statistik peserta & bindamping hanya menghitung yang terverifikasi (4 peserta, 1 bindamping)
        // Bukan total seluruh database (12 peserta, 2 bindamping)
        $component = \Livewire\Livewire::actingAs($admin)->test(\App\Livewire\Dashboard::class);
        $adminStats = $component->get('adminStats');

        $this->assertSame(2, $adminStats['total_groups']);
        $this->assertSame(1, $adminStats['draft_groups']);
        $this->assertSame(1, $adminStats['verified_groups']);
        $this->assertSame(1, $adminStats['total_patrols']);
        $this->assertSame(4, $adminStats['total_participants']);
        $this->assertSame(1, $adminStats['total_chaperones']);
    }

    public function test_winning_patrol_congratulations_displayed_on_scout_group_dashboards(): void
    {
        $event = Event::create([
            'title' => 'LT-III Padangsidimpuan 2026',
            'location' => 'Bumi Perkemahan Tor Simarsayang',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'is_active' => true,
            'hide_patrol_identities' => false,
        ]);

        $gudepUser = User::factory()->create(['is_active' => true]);
        $gudepUser->assignRole(RoleEnum::ScoutGroup);

        $scoutGroup = ScoutGroup::factory()->create([
            'user_id' => $gudepUser->id,
            'school_name' => 'SMP Negeri 1 Padangsidimpuan',
            'verification_status' => RegistrationStatusEnum::Verified,
        ]);

        $patrol = Patrol::factory()->create([
            'scout_group_id' => $scoutGroup->id,
            'patrol_name' => 'Rajawali',
            'gender_category' => 'boys',
            'status' => RegistrationStatusEnum::Verified,
        ]);

        $competition = Competition::create([
            'code' => 'PIO-01',
            'title' => 'Pionering',
            'category' => 'all',
            'participant_type' => 'patrol',
            'scoring_formula' => 'sum',
            'is_published' => true,
        ]);
        CompetitionScore::create([
            'competition_id' => $competition->id,
            'patrol_id' => $patrol->id,
            'final_score' => 95,
            'rank' => 1,
            'predicate' => 'prestasi_tinggi',
            'is_final' => true,
        ]);

        // Main dashboard
        $response = $this->actingAs($gudepUser)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Regu Rajawali');
        $response->assertSee('Regu Berprestasi Tinggi');
        $response->assertSee('Selamat &amp; Sukses', false);

        // Scout Group Portal dashboard
        $portalResponse = $this->actingAs($gudepUser)->get(route('scout-group.index'));
        $portalResponse->assertOk();
        $portalResponse->assertSee('Regu Rajawali');
        $portalResponse->assertSee('Regu Berprestasi Tinggi');
    }

    public function test_winning_patrol_congratulations_displayed_on_chaperone_and_participant_dashboards(): void
    {
        $event = Event::create([
            'title' => 'LT-III Padangsidimpuan 2026',
            'location' => 'Bumi Perkemahan Tor Simarsayang',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'is_active' => true,
            'hide_patrol_identities' => false,
        ]);

        $scoutGroup = ScoutGroup::factory()->create([
            'school_name' => 'MTs Negeri 1 Padangsidimpuan',
            'verification_status' => RegistrationStatusEnum::Verified,
        ]);

        $chaperoneUser = User::factory()->create(['is_active' => true]);
        $chaperoneUser->assignRole(RoleEnum::Chaperone);
        $chaperone = Chaperone::factory()->create([
            'user_id' => $chaperoneUser->id,
            'scout_group_id' => $scoutGroup->id,
            'gender' => 'male',
            'status' => RegistrationStatusEnum::Verified,
        ]);

        $patrol = Patrol::factory()->create([
            'scout_group_id' => $scoutGroup->id,
            'chaperone_id' => $chaperone->id,
            'patrol_name' => 'Elang',
            'gender_category' => 'boys',
            'status' => RegistrationStatusEnum::Verified,
        ]);

        $participant = Participant::factory()->create([
            'patrol_id' => $patrol->id,
            'gender' => 'male',
            'status' => RegistrationStatusEnum::Verified,
        ]);
        $participantUser = User::factory()->create([
            'is_active' => true,
            'participant_id' => $participant->id,
        ]);
        $participantUser->assignRole(RoleEnum::Participant);

        $competition = Competition::create([
            'code' => 'TAP-01',
            'title' => 'Tapak Perkemahan',
            'category' => 'all',
            'participant_type' => 'patrol',
            'scoring_formula' => 'sum',
            'is_published' => true,
        ]);
        CompetitionScore::create([
            'competition_id' => $competition->id,
            'patrol_id' => $patrol->id,
            'final_score' => 90,
            'rank' => 1,
            'predicate' => 'prestasi_tinggi',
            'is_final' => true,
        ]);

        // Chaperone dashboard
        $responseChaperone = $this->actingAs($chaperoneUser)->get(route('dashboard'));
        $responseChaperone->assertOk();
        $responseChaperone->assertSee('Regu Elang');
        $responseChaperone->assertSee('Regu Berprestasi Tinggi');

        // Participant dashboard
        $responseParticipant = $this->actingAs($participantUser)->get(route('dashboard'));
        $responseParticipant->assertOk();
        $responseParticipant->assertSee('Regu Elang');
        $responseParticipant->assertSee('Regu Berprestasi Tinggi');
    }

    public function test_winning_patrol_congratulations_hidden_when_blind_mode_active(): void
    {
        $event = Event::create([
            'title' => 'LT-III Padangsidimpuan 2026',
            'location' => 'Bumi Perkemahan Tor Simarsayang',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'is_active' => true,
            'hide_patrol_identities' => true, // Blind mode aktif
        ]);

        $gudepUser = User::factory()->create(['is_active' => true]);
        $gudepUser->assignRole(RoleEnum::ScoutGroup);

        $scoutGroup = ScoutGroup::factory()->create([
            'user_id' => $gudepUser->id,
            'school_name' => 'SMP Negeri 2 Padangsidimpuan',
            'verification_status' => RegistrationStatusEnum::Verified,
        ]);

        $patrol = Patrol::factory()->create([
            'scout_group_id' => $scoutGroup->id,
            'patrol_name' => 'Singa',
            'gender_category' => 'boys',
            'status' => RegistrationStatusEnum::Verified,
        ]);

        $competition = Competition::create([
            'code' => 'SEMA-01',
            'title' => 'Semaphore',
            'category' => 'all',
            'participant_type' => 'patrol',
            'scoring_formula' => 'sum',
            'is_published' => true,
        ]);
        CompetitionScore::create([
            'competition_id' => $competition->id,
            'patrol_id' => $patrol->id,
            'final_score' => 95,
            'rank' => 1,
            'predicate' => 'prestasi_tinggi',
            'is_final' => true,
        ]);

        $response = $this->actingAs($gudepUser)->get(route('dashboard'));
        $response->assertOk();
        $response->assertDontSee('Selamat atas Raihan Predikat Kehormatan');
        $response->assertDontSee('Regu Berprestasi Tinggi');
    }
}

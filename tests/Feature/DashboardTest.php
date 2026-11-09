<?php

namespace Tests\Feature;

use App\Enums\RegistrationStatusEnum;
use App\Enums\RoleEnum;
use App\Models\Chaperone;
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
}

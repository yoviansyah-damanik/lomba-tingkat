<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
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
        $response->assertSee('Verifikasi Berkas');
        $response->assertSee('Total Regu Terdaftar');
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
        $response->assertSee('Pemantauan Lapangan');
        $response->assertSee('Regu Sah Siap Tanding');
    }

    public function test_judge_sees_judge_dashboard(): void
    {
        $judge = User::factory()->create(['is_active' => true]);
        $judge->assignRole(RoleEnum::Judge);

        $response = $this->actingAs($judge)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Rubrik Juknis Lomba');
        $response->assertSee('Blind Scoring Aktif');
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
}

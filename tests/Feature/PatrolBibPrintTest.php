<?php

namespace Tests\Feature;

use App\Enums\PatrolGenderEnum;
use App\Enums\RegistrationStatusEnum;
use App\Enums\RoleEnum;
use App\Models\Event;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatrolBibPrintTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $scoutGroupUser;
    protected ScoutGroup $scoutGroup;
    protected Patrol $boysPatrol;
    protected Patrol $girlsPatrol;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        Event::create([
            'title' => 'LT-III 2026',
            'start_date' => '2026-11-14',
            'end_date' => '2026-11-17',
            'location' => 'Pulo Baok',
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create(['is_active' => true]);
        $this->admin->assignRole(RoleEnum::Admin);

        $this->scoutGroupUser = User::factory()->create(['is_active' => true]);
        $this->scoutGroupUser->assignRole(RoleEnum::ScoutGroup);

        $this->scoutGroup = ScoutGroup::factory()->create([
            'user_id' => $this->scoutGroupUser->id,
            'school_name' => 'SMP Negeri 1 Padangsidimpuan',
            'verification_status' => RegistrationStatusEnum::Verified,
        ]);

        $this->boysPatrol = Patrol::factory()->create([
            'scout_group_id' => $this->scoutGroup->id,
            'patrol_name' => 'Rajawali',
            'gender_category' => PatrolGenderEnum::Boys,
            'npp' => '01',
            'status' => RegistrationStatusEnum::Verified,
        ]);

        $this->girlsPatrol = Patrol::factory()->create([
            'scout_group_id' => $this->scoutGroup->id,
            'patrol_name' => 'Melati',
            'gender_category' => PatrolGenderEnum::Girls,
            'npp' => '02',
            'status' => RegistrationStatusEnum::Verified,
        ]);
    }

    public function test_guest_cannot_access_patrol_bibs_print(): void
    {
        $response = $this->get(route('admin.npp.print-bibs'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_preview_all_patrol_bibs(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.npp.print-bibs'));
        $response->assertOk();
        $response->assertSee('Nomor Punggung Regu');
    }

    public function test_admin_can_render_raw_pdf_of_patrol_bibs(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.npp.print-bibs', ['raw' => 1]));
        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('inline', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_admin_can_filter_patrol_bibs_by_gender(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.npp.print-bibs', ['gender' => 'boys', 'raw' => 1]));
        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_admin_can_print_single_patrol_bib(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.npp.print-bibs', ['patrol_id' => $this->boysPatrol->id, 'raw' => 1]));
        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('nomor-punggung-01-rajawali.pdf', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_scout_group_can_print_own_patrol_bib(): void
    {
        $response = $this->actingAs($this->scoutGroupUser)->get(route('scout-group.patrols.bib', ['patrolId' => $this->boysPatrol->id, 'patrol_id' => $this->boysPatrol->id, 'raw' => 1]));
        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_scout_group_cannot_print_other_scout_group_patrol_bib(): void
    {
        $otherGroup = ScoutGroup::factory()->create(['verification_status' => RegistrationStatusEnum::Verified]);
        $otherPatrol = Patrol::factory()->create([
            'scout_group_id' => $otherGroup->id,
            'patrol_name' => 'Garuda',
            'gender_category' => PatrolGenderEnum::Boys,
            'npp' => '03',
            'status' => RegistrationStatusEnum::Verified,
        ]);

        $response = $this->actingAs($this->scoutGroupUser)->get(route('scout-group.patrols.bib', ['patrolId' => $otherPatrol->id, 'patrol_id' => $otherPatrol->id]));
        $response->assertNotFound();
    }

    public function test_patrol_without_npp_returns_404(): void
    {
        $noNppPatrol = Patrol::factory()->create([
            'scout_group_id' => $this->scoutGroup->id,
            'patrol_name' => 'Kancil',
            'npp' => null,
            'status' => RegistrationStatusEnum::Verified,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.npp.print-bibs', ['patrol_id' => $noNppPatrol->id]));
        $response->assertNotFound();
    }
}


<?php

namespace Tests\Feature;

use App\Enums\PatrolGenderEnum;
use App\Enums\PatrolRoleEnum;
use App\Enums\RegistrationStatusEnum;
use App\Enums\RoleEnum;
use App\Livewire\Admin\ScoutGroupVerificationActions;
use App\Models\Chaperone;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use App\Models\User;
use App\Services\ContingentVerificationService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class ContingentVerificationCancelNppTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;

    protected User $admin;

    protected ScoutGroup $group;

    protected Patrol $patrolBoys;

    protected Patrol $patrolGirls;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->superadmin = User::factory()->create(['is_active' => true]);
        $this->superadmin->assignRole(RoleEnum::Superadmin);

        $this->admin = User::factory()->create(['is_active' => true]);
        $this->admin->assignRole(RoleEnum::Admin);

        $picUser = User::factory()->create();
        $this->group = ScoutGroup::create([
            'user_id' => $picUser->id,
            'registration_code' => 'LT3-2026-NPP-TEST',
            'school_name' => 'SMP Negeri 1 Padangsidimpuan',
            'group_number' => '01-093',
            'kwarran_name' => 'Padangsidimpuan Utara',
            'address' => 'Jl. Pramuka',
            'contact_person' => 'Kak Pembina',
            'contact_phone' => '081200000000',
            'verification_status' => RegistrationStatusEnum::Verified,
            'verified_at' => now(),
        ]);

        $this->patrolBoys = Patrol::create([
            'scout_group_id' => $this->group->id,
            'patrol_name' => 'Rajawali',
            'gender_category' => PatrolGenderEnum::Boys,
            'status' => RegistrationStatusEnum::Verified,
            'npp' => 'LT-PA-01',
        ]);

        $this->patrolGirls = Patrol::create([
            'scout_group_id' => $this->group->id,
            'patrol_name' => 'Melati',
            'gender_category' => PatrolGenderEnum::Girls,
            'status' => RegistrationStatusEnum::Verified,
            'npp' => 'LT-PI-01',
        ]);

        Participant::create([
            'patrol_id' => $this->patrolBoys->id,
            'full_name' => 'Pinru Putra',
            'role_in_patrol' => PatrolRoleEnum::PatrolLeader,
            'gender' => 'male',
            'birth_place' => 'Padangsidimpuan',
            'birth_date' => '2011-03-09',
            'qr_token' => (string) Str::uuid(),
            'id_number' => 'p-01093-01',
            'status' => RegistrationStatusEnum::Verified,
        ]);

        Chaperone::create([
            'scout_group_id' => $this->group->id,
            'full_name' => 'Bindamping Putra',
            'gender' => 'male',
            'phone_number' => '081211111111',
            'birth_date' => '1985-05-15',
            'id_number' => 'b-01093-01',
            'status' => RegistrationStatusEnum::Verified,
        ]);
    }

    /**
     * Memastikan saat verifikasi kontingen dibatalkan (cabut verifikasi) via Service,
     * seluruh NPP regu di bawah kontingen tersebut direset menjadi null.
     */
    public function test_cancelling_contingent_verification_revokes_patrol_npps_via_service(): void
    {
        $this->assertEquals('LT-PA-01', $this->patrolBoys->fresh()->npp);
        $this->assertEquals('LT-PI-01', $this->patrolGirls->fresh()->npp);

        $service = app(ContingentVerificationService::class);
        $result = $service->cancelVerification($this->group, $this->superadmin, 'Berkas perlu diperbaiki ulang');

        $this->assertEquals(2, $result['npp']);
        $this->assertNull($this->patrolBoys->fresh()->npp);
        $this->assertNull($this->patrolGirls->fresh()->npp);
        $this->assertEquals(RegistrationStatusEnum::NeedsRevision, $this->patrolBoys->fresh()->status);
        $this->assertEquals(RegistrationStatusEnum::NeedsRevision, $this->patrolGirls->fresh()->status);
        $this->assertEquals(RegistrationStatusEnum::NeedsRevision, $this->group->fresh()->verification_status);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'contingent_verification_cancelled',
            'model_id' => $this->group->id,
        ]);
    }

    /**
     * Memastikan pembatalan verifikasi lewat komponen Livewire mencabut NPP dan menampilkan toast
     */
    public function test_livewire_component_cancel_verification_revokes_npp(): void
    {
        Livewire::withoutLazyLoading()->actingAs($this->superadmin)
            ->test(ScoutGroupVerificationActions::class, ['scoutGroupId' => $this->group->id])
            ->set('cancelReason', 'Revisi berkas oleh Superadmin')
            ->call('cancelVerification')
            ->assertHasNoErrors();

        $this->assertNull($this->patrolBoys->fresh()->npp);
        $this->assertNull($this->patrolGirls->fresh()->npp);
        $this->assertEquals(RegistrationStatusEnum::NeedsRevision, $this->group->fresh()->verification_status);
    }

    /**
     * Memastikan ketika status verifikasi ScoutGroup diubah menjauhi Verified lewat model update,
     * seluruh NPP regu juga otomatis tercabut (null).
     */
    public function test_scout_group_model_updated_event_clears_npp_when_verification_status_is_reverted(): void
    {
        $this->group->update(['verification_status' => RegistrationStatusEnum::NeedsRevision]);

        $this->assertNull($this->patrolBoys->fresh()->npp);
        $this->assertNull($this->patrolGirls->fresh()->npp);
    }
}

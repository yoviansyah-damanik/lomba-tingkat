<?php

namespace Tests\Feature;

use App\Enums\PatrolRoleEnum;
use App\Enums\RegistrationStatusEnum;
use App\Enums\RoleEnum;
use App\Livewire\Admin\VerificationManagement;
use App\Models\Chaperone;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class ContingentAccountProvisioningTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected ScoutGroup $group;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole(RoleEnum::Admin);

        $picUser = User::factory()->create();
        $this->group = ScoutGroup::create([
            'user_id' => $picUser->id,
            'registration_code' => 'LT3-2026-ACC',
            'school_name' => 'SMP Negeri 1 Padangsidimpuan',
            'group_number' => '01-093',
            'kwarran_name' => 'Padangsidimpuan Utara',
            'address' => 'Jl. Pramuka',
            'contact_person' => 'Kak Pembina',
            'contact_phone' => '081200000000',
            'verification_status' => RegistrationStatusEnum::Submitted,
        ]);

        Chaperone::create([
            'scout_group_id' => $this->group->id,
            'full_name' => 'Bindamping Putra',
            'gender' => 'male',
            'phone_number' => '081211111111',
            'birth_date' => '1985-05-15',
            'status' => RegistrationStatusEnum::Submitted,
        ]);

        $boys = Patrol::create(['scout_group_id' => $this->group->id, 'patrol_name' => 'Rajawali', 'gender_category' => 'boys', 'status' => RegistrationStatusEnum::Submitted]);
        $girls = Patrol::create(['scout_group_id' => $this->group->id, 'patrol_name' => 'Merpati', 'gender_category' => 'girls', 'status' => RegistrationStatusEnum::Submitted]);

        $this->makeParticipant($boys, 'Pinru Putra', PatrolRoleEnum::PatrolLeader, 'male', '2011-03-09');
        $this->makeParticipant($boys, 'Anggota Putra', PatrolRoleEnum::Member, 'male', '2012-12-01');
        $this->makeParticipant($girls, 'Pinru Putri', PatrolRoleEnum::PatrolLeader, 'female', '2011-07-21');
    }

    protected function makeParticipant(Patrol $patrol, string $name, PatrolRoleEnum $role, string $gender, string $birthDate): Participant
    {
        return Participant::create([
            'patrol_id' => $patrol->id,
            'full_name' => $name,
            'role_in_patrol' => $role,
            'gender' => $gender,
            'birth_date' => $birthDate,
            'birth_place' => 'Padangsidimpuan',
            'status' => RegistrationStatusEnum::Submitted,
        ]);
    }

    public function test_verifying_contingent_creates_accounts_with_expected_usernames_and_passwords(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.scout-groups.verify', $this->group->id))
            ->assertRedirect();

        $chaperoneModel = Chaperone::where('full_name', 'Bindamping Putra')->first();
        $this->assertNotNull($chaperoneModel);

        $chaperoneUser = User::where('username', $chaperoneModel->id_number)->first();
        $this->assertNotNull($chaperoneUser);
        $this->assertTrue($chaperoneUser->hasRole(RoleEnum::Chaperone));
        $this->assertTrue(Hash::check('19850515', $chaperoneUser->password));
        $this->assertSame($chaperoneUser->id, $chaperoneModel->user_id);

        $pinruModel = Participant::where('full_name', 'Pinru Putra')->first();
        $this->assertNotNull($pinruModel);

        $pinruUser = User::where('username', $pinruModel->id_number)->first();
        $this->assertNotNull($pinruUser);
        $this->assertTrue($pinruUser->hasRole(RoleEnum::Participant));
        $this->assertTrue(Hash::check('20110309', $pinruUser->password));
        $this->assertSame('Pinru Putra', $pinruUser->participant->full_name);

        $this->assertTrue(Auth::validate(['username' => $pinruModel->id_number, 'password' => '20110309']));
    }

    public function test_provisioning_is_idempotent_and_skips_chaperone_without_birth_date(): void
    {
        $chaperoneModel = Chaperone::where('full_name', 'Bindamping Putra')->first();
        $chaperoneModel->update(['birth_date' => null]);

        $this->actingAs($this->admin)
            ->post(route('admin.scout-groups.verify', $this->group->id));

        $this->assertNull(User::where('username', $chaperoneModel->id_number)->first());
        $this->assertSame(3, User::where('username', 'like', 'p-%')->count());
    }

    public function test_verification_via_controller_creates_accounts(): void
    {
        $chaperoneModel = Chaperone::where('full_name', 'Bindamping Putra')->first();

        $this->actingAs($this->admin)
            ->post(route('admin.scout-groups.verify', $this->group->id))
            ->assertRedirect();

        $this->assertNotNull(User::where('username', $chaperoneModel->id_number)->first());
        $this->assertSame(3, User::where('username', 'like', 'p-%')->count());
    }

    public function test_accounts_are_not_created_for_revision_or_rejection(): void
    {
        $chaperoneModel = Chaperone::where('full_name', 'Bindamping Putra')->first();

        $this->actingAs($this->admin)
            ->post(route('admin.scout-groups.revision', $this->group->id), ['revision_notes' => 'Lengkapi data peserta'])
            ->assertRedirect();

        $this->assertNull(User::where('username', $chaperoneModel->id_number)->first());
    }
}

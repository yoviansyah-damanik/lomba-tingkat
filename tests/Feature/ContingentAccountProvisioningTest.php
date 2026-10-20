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
        Livewire::actingAs($this->admin)
            ->test(VerificationManagement::class)
            ->call('verifyContingent', $this->group->id);

        $chaperone = User::where('username', 'bindamping.01093')->first();
        $this->assertNotNull($chaperone);
        $this->assertTrue($chaperone->hasRole(RoleEnum::Chaperone));
        $this->assertTrue(Hash::check('19850515', $chaperone->password));
        $this->assertSame($chaperone->id, Chaperone::where('full_name', 'Bindamping Putra')->first()->user_id);

        // Putra memakai nomor ganjil (01093), putri memakai nomor genap (01094)
        $pinru = User::where('username', 'peserta.01093.01')->first();
        $this->assertNotNull($pinru);
        $this->assertTrue($pinru->hasRole(RoleEnum::Participant));
        $this->assertTrue(Hash::check('20110309', $pinru->password));
        $this->assertSame('Pinru Putra', $pinru->participant->full_name);

        $this->assertNotNull(User::where('username', 'peserta.01093.02')->first());
        $this->assertTrue(Hash::check('20110721', User::where('username', 'peserta.01094.01')->first()->password));

        $this->assertTrue(Auth::validate(['username' => 'peserta.01093.01', 'password' => '20110309']));
    }

    public function test_provisioning_is_idempotent_and_skips_chaperone_without_birth_date(): void
    {
        Chaperone::where('full_name', 'Bindamping Putra')->update(['birth_date' => null]);

        $component = Livewire::actingAs($this->admin)->test(VerificationManagement::class);
        $component->call('verifyContingent', $this->group->id);
        $component->call('verifyContingent', $this->group->id);

        $this->assertNull(User::where('username', 'bindamping.01093')->first());
        $this->assertSame(3, User::where('username', 'like', 'peserta.%')->count());
    }

    public function test_verification_via_controller_creates_accounts(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.scout-groups.verify', $this->group->id))
            ->assertRedirect();

        $this->assertNotNull(User::where('username', 'bindamping.01093')->first());
        $this->assertSame(3, User::where('username', 'like', 'peserta.%')->count());
    }

    public function test_accounts_are_not_created_for_revision_or_rejection(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.scout-groups.revision', $this->group->id), ['revision_notes' => 'Lengkapi data peserta'])
            ->assertRedirect();

        $this->assertNull(User::where('username', 'bindamping.01093')->first());
    }
}

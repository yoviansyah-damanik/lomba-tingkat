<?php

namespace Tests\Feature;

use App\Enums\RegistrationStatusEnum;
use App\Enums\RoleEnum;
use App\Livewire\Admin\VerificationManagement;
use App\Models\ScoutGroup;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminVerificationLivewireTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(\Database\Seeders\EventSeeder::class);

        $this->admin = User::factory()->create([
            'is_active' => true,
        ]);
        $this->admin->assignRole(RoleEnum::Admin);
    }

    public function test_verification_livewire_component_renders_correctly(): void
    {
        $groupUser = User::factory()->create();
        $scoutGroup = ScoutGroup::create([
            'user_id' => $groupUser->id,
            'registration_code' => 'LT3-2026-TEST',
            'school_name' => 'SMP Negeri 1 Merdeka Beraksi',
            'group_number' => '01.001 - 01.002',
            'kwarran_name' => 'Cibinong',
            'address' => 'Jl. Pramuka Raya No. 10',
            'contact_person' => 'Kak Pembina Utama',
            'contact_phone' => '081234567890',
            'verification_status' => RegistrationStatusEnum::Submitted,
        ]);

        Livewire::actingAs($this->admin)
            ->test(VerificationManagement::class)
            ->assertStatus(200)
            ->assertSee('SMP Negeri 1 Merdeka Beraksi')
            ->assertSee('LT3-2026-TEST');
    }

    public function test_livewire_can_verify_contingent_inline(): void
    {
        $groupUser = User::factory()->create();
        $scoutGroup = ScoutGroup::create([
            'user_id' => $groupUser->id,
            'registration_code' => 'LT3-2026-APPROVE',
            'school_name' => 'SMP Juara Terpadu',
            'group_number' => '02.001 - 02.002',
            'kwarran_name' => 'Citeureup',
            'address' => 'Jl. Pahlawan No. 45',
            'contact_person' => 'Kak Pembina Putri',
            'contact_phone' => '081298765432',
            'verification_status' => RegistrationStatusEnum::Submitted,
        ]);

        Livewire::actingAs($this->admin)
            ->test(VerificationManagement::class)
            ->call('verifyContingent', $scoutGroup->id)
            ->assertHasNoErrors();

        $this->assertEquals(RegistrationStatusEnum::Verified, $scoutGroup->fresh()->verification_status);
        $this->assertEquals($this->admin->id, $scoutGroup->fresh()->verified_by);
    }

    public function test_livewire_can_request_revision_with_notes(): void
    {
        $groupUser = User::factory()->create();
        $scoutGroup = ScoutGroup::create([
            'user_id' => $groupUser->id,
            'registration_code' => 'LT3-2026-REVISE',
            'school_name' => 'SMP Pelita Bangsa',
            'group_number' => '03.001 - 03.002',
            'kwarran_name' => 'Babakan Madang',
            'address' => 'Jl. Raya Sentul No. 1',
            'contact_person' => 'Kak Surya',
            'contact_phone' => '081300001111',
            'verification_status' => RegistrationStatusEnum::Submitted,
        ]);

        Livewire::actingAs($this->admin)
            ->test(VerificationManagement::class)
            ->call('openReviewModal', $scoutGroup->id, 'revision')
            ->set('revisionNotes', 'Mohon lengkapi NTA seluruh peserta regu putri.')
            ->call('requestRevision')
            ->assertHasNoErrors();

        $this->assertEquals(RegistrationStatusEnum::NeedsRevision, $scoutGroup->fresh()->verification_status);
        $this->assertStringContainsString('lengkapi NTA', $scoutGroup->fresh()->revision_notes);
    }

    public function test_review_modal_displays_patrol_participants_and_chaperones_correctly(): void
    {
        $groupUser = User::factory()->create();
        $scoutGroup = ScoutGroup::create([
            'user_id' => $groupUser->id,
            'registration_code' => 'LT3-2026-DETAIL',
            'school_name' => 'SMP Cendrawasih Emas',
            'group_number' => '04.001 - 04.002',
            'kwarran_name' => 'Bogor Timur',
            'address' => 'Jl. Pajajaran No. 88',
            'contact_person' => 'Kak Pembina Utama',
            'contact_phone' => '081234567890',
            'verification_status' => RegistrationStatusEnum::Submitted,
        ]);

        $patrol = \App\Models\Patrol::create([
            'scout_group_id' => $scoutGroup->id,
            'patrol_name' => 'Rajawali Sakti',
            'gender_category' => \App\Enums\PatrolGenderEnum::Boys,
            'status' => RegistrationStatusEnum::Submitted,
        ]);

        $participant = \App\Models\Participant::create([
            'patrol_id' => $patrol->id,
            'full_name' => 'Bintang Alamsyah',
            'role_in_patrol' => \App\Enums\PatrolRoleEnum::PatrolLeader,
            'scout_id' => '02.08.01.123',
            'gender' => 'male',
            'class_grade' => 'VIII',
            'birth_place' => 'Bogor',
            'birth_date' => '2012-05-10',
            'status' => RegistrationStatusEnum::Submitted,
        ]);

        $chaperone = \App\Models\Chaperone::create([
            'scout_group_id' => $scoutGroup->id,
            'full_name' => 'Kak Hendra Gunawan, S.Pd',
            'gender' => 'male',
            'scout_id' => '02.08.00.001',
            'national_identity_number' => '3201010101900001',
            'phone_number' => '081299887766',
            'status' => RegistrationStatusEnum::Submitted,
        ]);

        Livewire::actingAs($this->admin)
            ->test(VerificationManagement::class)
            ->call('openReviewModal', $scoutGroup->id, 'view')
            ->assertSet('showReviewModal', true)
            ->assertSee('SMP Cendrawasih Emas')
            ->assertSee('Regu Rajawali Sakti')
            ->assertSee('Bintang Alamsyah')
            ->assertSee('PINRU')
            ->assertSee('02.08.01.123')
            ->assertSee('Kak Hendra Gunawan, S.Pd')
            ->assertSee('Bindamping Putra')
            ->assertSee('081299887766')
            ->assertSee('3201010101900001');
    }
}


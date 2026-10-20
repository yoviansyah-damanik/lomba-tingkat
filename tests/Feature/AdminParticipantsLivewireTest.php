<?php

namespace Tests\Feature;

use App\Enums\PatrolGenderEnum;
use App\Enums\PatrolRoleEnum;
use App\Enums\RegistrationStatusEnum;
use App\Enums\RoleEnum;
use App\Livewire\Admin\ParticipantsDirectory;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use App\Models\User;
use Database\Seeders\EventSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminParticipantsLivewireTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected ScoutGroup $verifiedGroup;
    protected Patrol $boysPatrol;
    protected Participant $verifiedParticipant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(EventSeeder::class);

        $this->admin = User::factory()->create([
            'is_active' => true,
        ]);
        $this->admin->assignRole(RoleEnum::Admin);

        $groupUser = User::factory()->create();
        $this->verifiedGroup = ScoutGroup::create([
            'user_id' => $groupUser->id,
            'registration_code' => 'LT3-2026-001',
            'school_name' => 'SMP Negeri 1 Padangsidimpuan',
            'group_number' => '01.001 - 01.002',
            'kwarran_name' => 'Padangsidimpuan Utara',
            'address' => 'Jl. Merdeka No. 1',
            'contact_person' => 'Kak Pembina',
            'contact_phone' => '081234567890',
            'verification_status' => RegistrationStatusEnum::Verified,
        ]);

        $this->boysPatrol = Patrol::create([
            'scout_group_id' => $this->verifiedGroup->id,
            'patrol_name' => 'Elang Perkasa',
            'gender_category' => PatrolGenderEnum::Boys,
            'status' => RegistrationStatusEnum::Verified,
        ]);

        $this->verifiedParticipant = Participant::create([
            'patrol_id' => $this->boysPatrol->id,
            'full_name' => 'Ahmad Rasyid Pratama',
            'scout_id' => '02.08.01.001.0001',
            'role_in_patrol' => PatrolRoleEnum::PatrolLeader,
            'class_grade' => 'VIII-A',
            'gender' => 'male',
            'birth_date' => '2011-05-14',
            'birth_place' => 'Padangsidimpuan',
            'blood_type' => 'O',
            'religion' => 'Islam',
            'status' => RegistrationStatusEnum::Verified,
        ]);
    }

    public function test_guests_cannot_access_participants_directory(): void
    {
        $response = $this->get(route('admin.participants.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_users_cannot_access_participants_directory(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(RoleEnum::ScoutGroup);

        $response = $this->actingAs($user)->get(route('admin.participants.index'));
        $response->assertForbidden();
    }

    public function test_admin_can_access_participants_directory_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.participants.index'));
        $response->assertOk();
        $response->assertSee('Direktori Peserta Terverifikasi');
        $response->assertSee('Ahmad Rasyid Pratama');
        $response->assertSee('SMP Negeri 1 Padangsidimpuan');
    }

    public function test_component_displays_verified_participants_and_filters_unverified_by_default(): void
    {
        // Buat peserta yang belum diverifikasi / draft
        $draftGroup = ScoutGroup::create([
            'user_id' => User::factory()->create()->id,
            'registration_code' => 'LT3-2026-DRAFT',
            'school_name' => 'SMP Swasta Mandiri',
            'group_number' => '02.001 - 02.002',
            'kwarran_name' => 'Padangsidimpuan Selatan',
            'address' => 'Jl. Sudirman No. 20',
            'contact_person' => 'Kak Pembina 2',
            'contact_phone' => '081298765432',
            'verification_status' => RegistrationStatusEnum::Draft,
        ]);

        $draftPatrol = Patrol::create([
            'scout_group_id' => $draftGroup->id,
            'patrol_name' => 'Garuda Merah',
            'gender_category' => PatrolGenderEnum::Boys,
            'status' => RegistrationStatusEnum::Draft,
        ]);

        $draftParticipant = Participant::create([
            'patrol_id' => $draftPatrol->id,
            'full_name' => 'Budi Santoso Draft',
            'gender' => 'male',
            'birth_place' => 'Padangsidimpuan',
            'birth_date' => '2011-06-15',
            'status' => RegistrationStatusEnum::Draft,
        ]);

        Livewire::actingAs($this->admin)
            ->test(ParticipantsDirectory::class)
            ->assertSee('Ahmad Rasyid Pratama')
            ->assertDontSee('Budi Santoso Draft')
            ->set('statusFilter', 'all')
            ->assertSee('Budi Santoso Draft');
    }

    public function test_admin_can_search_participant_by_name_or_scout_id(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ParticipantsDirectory::class)
            ->set('search', 'Ahmad Rasyid')
            ->assertSee('Ahmad Rasyid Pratama')
            ->set('search', 'NonExistentPersonXYZ')
            ->assertSee('Tidak ada data peserta ditemukan')
            ->assertDontSee('Ahmad Rasyid Pratama');
    }

    public function test_admin_can_open_participant_detail_modal(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ParticipantsDirectory::class)
            ->assertSet('showDetailModal', false)
            ->call('openDetail', $this->verifiedParticipant->id)
            ->assertSet('showDetailModal', true)
            ->assertSet('selectedParticipantId', $this->verifiedParticipant->id)
            ->assertSee('02.08.01.001.0001')
            ->call('closeDetail')
            ->assertSet('showDetailModal', false)
            ->assertSet('selectedParticipantId', null);
    }
}

<?php

namespace Tests\Feature;

use App\Enums\RegistrationStatusEnum;
use App\Enums\RoleEnum;
use App\Livewire\Admin\ChaperonesDirectory;
use App\Models\Chaperone;
use App\Models\ScoutGroup;
use App\Models\User;
use Database\Seeders\EventSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminChaperonesLivewireTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected ScoutGroup $verifiedGroup;
    protected Chaperone $verifiedChaperone;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(EventSeeder::class);

        $this->admin = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $this->admin->assignRole(RoleEnum::Admin);

        $groupUser = User::factory()->create();
        $this->verifiedGroup = ScoutGroup::create([
            'user_id' => $groupUser->id,
            'registration_code' => 'LT3-2026-CHAP-01',
            'school_name' => 'SMP Negeri 2 Padangsidimpuan',
            'group_number' => '02.001 - 02.002',
            'kwarran_name' => 'Padangsidimpuan Selatan',
            'address' => 'Jl. Imam Bonjol No. 45',
            'contact_person' => 'Kak Pembina SMPN 2',
            'contact_phone' => '081211112222',
            'verification_status' => RegistrationStatusEnum::Verified,
        ]);

        $this->verifiedChaperone = Chaperone::create([
            'scout_group_id' => $this->verifiedGroup->id,
            'full_name' => 'Kak Suhardi Harahap, S.Pd',
            'scout_id' => '02.08.02.001.0001',
            'national_identity_number' => '1277011405850001',
            'gender' => 'male',
            'birth_place' => 'Padangsidimpuan',
            'birth_date' => '1985-05-14',
            'religion' => 'Islam',
            'blood_type' => 'A',
            'phone_number' => '081233445566',
            'status' => RegistrationStatusEnum::Verified,
        ]);
    }

    public function test_guests_cannot_access_chaperones_directory(): void
    {
        $response = $this->get(route('admin.chaperones.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_users_cannot_access_chaperones_directory(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(RoleEnum::ScoutGroup);

        $response = $this->actingAs($user)->get(route('admin.chaperones.index'));
        $response->assertForbidden();
    }

    public function test_admin_can_access_chaperones_directory_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.chaperones.index'));
        $response->assertOk();
        $response->assertSee('Direktori Pembina Pendamping');
        $response->assertSee('Kak Suhardi Harahap, S.Pd');
        $response->assertSee('SMP Negeri 2 Padangsidimpuan');
    }

    public function test_component_displays_verified_chaperones_and_filters_unverified_by_default(): void
    {
        $draftGroup = ScoutGroup::create([
            'user_id' => User::factory()->create()->id,
            'registration_code' => 'LT3-2026-CHAP-DRAFT',
            'school_name' => 'MTs Swasta Bina Bangsa',
            'group_number' => '03.001 - 03.002',
            'kwarran_name' => 'Padangsidimpuan Batunadua',
            'address' => 'Jl. Raja Inal Siregar',
            'contact_person' => 'Kak Pembina MTs',
            'contact_phone' => '081399887766',
            'verification_status' => RegistrationStatusEnum::Draft,
        ]);

        $draftChaperone = Chaperone::create([
            'scout_group_id' => $draftGroup->id,
            'full_name' => 'Nurhalimah Siregar Draft',
            'gender' => 'female',
            'birth_place' => 'Padangsidimpuan',
            'birth_date' => '1990-08-20',
            'phone_number' => '081298765432',
            'status' => RegistrationStatusEnum::Draft,
        ]);

        Livewire::actingAs($this->admin)
            ->test(ChaperonesDirectory::class)
            ->assertSee('Kak Suhardi Harahap, S.Pd')
            ->assertDontSee('Nurhalimah Siregar Draft')
            ->set('statusFilter', 'all')
            ->assertSee('Nurhalimah Siregar Draft');
    }

    public function test_admin_can_search_chaperone_by_name_or_phone(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ChaperonesDirectory::class)
            ->set('search', 'Suhardi')
            ->assertSee('Kak Suhardi Harahap, S.Pd')
            ->set('search', 'NonExistentChaperoneXYZ')
            ->assertSee('Tidak ada data pembina pendamping ditemukan')
            ->assertDontSee('Kak Suhardi Harahap, S.Pd');
    }

    public function test_admin_can_open_chaperone_detail_modal(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ChaperonesDirectory::class)
            ->assertSet('showDetailModal', false)
            ->call('openDetail', $this->verifiedChaperone->id)
            ->assertSet('showDetailModal', true)
            ->assertSet('selectedChaperoneId', $this->verifiedChaperone->id)
            ->assertSee('02.08.02.001.0001')
            ->assertSee('081233445566')
            ->call('closeDetail')
            ->assertSet('showDetailModal', false)
            ->assertSet('selectedChaperoneId', null);
    }
}

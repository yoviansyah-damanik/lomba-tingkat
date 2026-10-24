<?php

namespace Tests\Feature;

use App\Enums\RegistrationStatusEnum;
use App\Enums\RoleEnum;
use App\Livewire\Admin\ScoutGroupDetail;
use App\Livewire\Admin\ScoutGroupsDirectory;
use App\Models\ScoutGroup;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ScoutGroupsDirectoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    private function user(RoleEnum $role): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role);

        return $user;
    }

    public function test_directory_lists_only_verified_groups_and_is_open_to_committee(): void
    {
        ScoutGroup::factory()->create(['school_name' => 'SMP Terverifikasi Satu', 'verification_status' => RegistrationStatusEnum::Verified]);
        ScoutGroup::factory()->create(['school_name' => 'SMP Masih Menunggu', 'verification_status' => RegistrationStatusEnum::Submitted]);

        $this->actingAs($this->user(RoleEnum::Committee))
            ->get(route('admin.gudep-directory'))
            ->assertOk()
            ->assertSee('Pangkalan Gugusdepan')
            ->assertSee('SMP Terverifikasi Satu')
            ->assertDontSee('SMP Masih Menunggu');
    }

    public function test_directory_filters_by_search_and_opens_detail_modal(): void
    {
        $verified = ScoutGroup::factory()->create(['school_name' => 'SMP Cari Saya', 'verification_status' => RegistrationStatusEnum::Verified]);
        ScoutGroup::factory()->create(['school_name' => 'SMP Lainnya', 'verification_status' => RegistrationStatusEnum::Verified]);

        $this->actingAs($this->user(RoleEnum::Admin));

        Livewire::test(ScoutGroupsDirectory::class)
            ->set('search', 'Cari Saya')
            ->assertSee('SMP Cari Saya')
            ->assertDontSee('SMP Lainnya')
            ->call('openDetail', $verified->id)
            ->assertSet('selectedGroupId', $verified->id)
            ->call('closeDetail')
            ->assertSet('selectedGroupId', null);

        Livewire::withoutLazyLoading()
            ->test(ScoutGroupDetail::class, ['scoutGroupId' => $verified->id])
            ->assertSee('SMP Cari Saya')
            ->assertSee('Data Pangkalan');
    }

    public function test_full_profile_page_is_separate_from_verification_and_linked_from_modal(): void
    {
        $group = ScoutGroup::factory()->create([
            'school_name' => 'SMP Profil Lengkap',
            'contact_person' => 'Kak Penanggung',
            'verification_status' => RegistrationStatusEnum::Verified,
        ]);

        $committee = $this->user(RoleEnum::Committee);

        $this->actingAs($committee)
            ->get(route('admin.gudep-directory.show', $group->id))
            ->assertOk()
            ->assertSee('SMP Profil Lengkap')
            ->assertSee('Identitas Pangkalan')
            ->assertSee('Kak Penanggung')
            ->assertDontSee('Setujui & Sahkan Kontingen');

        Livewire::withoutLazyLoading()
            ->test(ScoutGroupDetail::class, ['scoutGroupId' => $group->id])
            ->assertSee('Buka Berkas Lengkap')
            ->assertSee(route('admin.gudep-directory.show', $group->id), false);
    }

    public function test_profile_has_detail_buttons_and_detail_pages_return_to_profile(): void
    {
        $group = ScoutGroup::factory()->create(['verification_status' => RegistrationStatusEnum::Verified]);
        $patrol = \App\Models\Patrol::create([
            'scout_group_id' => $group->id,
            'patrol_name' => 'Elang',
            'gender_category' => \App\Enums\PatrolGenderEnum::Boys,
            'status' => RegistrationStatusEnum::Verified,
        ]);
        $participant = \App\Models\Participant::factory()->create(['patrol_id' => $patrol->id]);
        $chaperone = \App\Models\Chaperone::factory()->create(['scout_group_id' => $group->id]);

        $committee = $this->user(RoleEnum::Committee);

        $participantUrl = route('admin.gudep-directory.participants.show', [$group->id, $participant->id]);
        $chaperoneUrl = route('admin.gudep-directory.chaperones.show', [$group->id, $chaperone->id]);

        $this->actingAs($committee)->get(route('admin.gudep-directory.show', $group->id))
            ->assertOk()
            ->assertSee('Lihat Detail')
            ->assertSee($participantUrl, false)
            ->assertSee($chaperoneUrl, false);

        $this->actingAs($committee)->get($participantUrl)->assertOk()
            ->assertSee($participant->full_name)
            ->assertSee(route('admin.gudep-directory.show', $group->id), false)
            ->assertDontSee(route('admin.scout-groups.show', $group->id), false);
        $this->actingAs($committee)->get($chaperoneUrl)->assertOk()
            ->assertSee($chaperone->full_name)
            ->assertSee(route('admin.gudep-directory.show', $group->id), false)
            ->assertDontSee(route('admin.scout-groups.show', $group->id), false);
    }

    public function test_sidebar_links_verification_and_directory_to_different_pages(): void
    {
        $admin = $this->user(RoleEnum::Admin);

        $html = $this->actingAs($admin)->get(route('dashboard'))->getContent();

        $this->assertStringContainsString(route('admin.scout-groups.index'), $html);
        $this->assertStringContainsString(route('admin.gudep-directory'), $html);
    }
}

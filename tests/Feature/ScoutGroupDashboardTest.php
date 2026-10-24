<?php

namespace Tests\Feature;

use App\Enums\PatrolGenderEnum;
use App\Enums\RoleEnum;
use App\Models\Chaperone;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsCompleteRoster;
use Tests\TestCase;

class ScoutGroupDashboardTest extends TestCase
{
    use BuildsCompleteRoster, RefreshDatabase;

    private User $user;

    private ScoutGroup $group;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->user = User::factory()->create();
        $this->user->assignRole(RoleEnum::ScoutGroup);
        $this->group = ScoutGroup::factory()->create([
            'user_id' => $this->user->id,
            'school_name' => 'SMP Negeri 3 Padangsidimpuan A',
            'male_group_number' => '01-123',
            'female_group_number' => '01-124',
        ]);
    }

    public function test_empty_contingent_shows_empty_states_without_inline_styles(): void
    {
        $response = $this->actingAs($this->user)->get(route('scout-group.index'))->assertOk();

        $response
            ->assertSee('SMP Negeri 3 Padangsidimpuan A')
            ->assertSee('Putra 01-123')
            ->assertSee('Putri 01-124')
            ->assertDontSee('Langkah Pendaftaran')
            ->assertSee('Regu putra belum didaftarkan.')
            ->assertSee('Regu putri belum didaftarkan.')
            ->assertSee('Menunggu Regu Putra')
            ->assertSee('Menunggu Regu Putri')
            ->assertDontSee('style="width', false);

    }

    public function test_patrol_progress_roster_flags_and_required_chaperone_slot(): void
    {
        $patrol = Patrol::factory()->create(['scout_group_id' => $this->group->id, 'gender_category' => PatrolGenderEnum::Boys, 'patrol_name' => 'Elang']);
        $this->fillPatrolRoster($patrol, existingMembers: 0);

        $girls = Patrol::factory()->create(['scout_group_id' => $this->group->id, 'gender_category' => PatrolGenderEnum::Girls, 'patrol_name' => 'Melati']);

        // Bindamping putra wajib ada karena Regu Putra sudah terdaftar, tetapi belum didaftarkan
        $this->actingAs($this->user)->get(route('scout-group.index'))->assertOk()
            ->assertSee('Regu Elang')
            ->assertSee('8/8 peserta')
            ->assertSee('Belum lengkap')
            ->assertSee('Regu Melati')
            ->assertSee('0/8 peserta')
            ->assertSee('Belum didaftarkan')
            ->assertDontSee(route('scout-group.chaperones.create', ['gender' => 'male']), false)
            ->assertDontSee('Daftarkan Bindamping');

        // Bindamping putra terdaftar dan ditetapkan: regu lengkap, slot menampilkan nama dan tautan detail
        $chaperone = Chaperone::factory()->create(['scout_group_id' => $this->group->id, 'gender' => 'male', 'full_name' => 'Rahmat Hidayat']);
        $patrol->update(['chaperone_id' => $chaperone->id]);

        $this->actingAs($this->user)->get(route('scout-group.index'))
            ->assertSee('Rahmat Hidayat')
            ->assertSee('Lengkap')
            ->assertSee(route('scout-group.chaperones.show', $chaperone->id), false);
    }

    public function test_locked_contingent_hides_edit_actions(): void
    {
        $this->group->update(['verification_status' => \App\Enums\RegistrationStatusEnum::Submitted]);

        $this->actingAs($this->user)->get(route('scout-group.index'))
            ->assertOk()
            ->assertDontSee('Daftarkan Regu Putra')
            ->assertDontSee('wire:click="openProfileModal"', false)
            ->assertSee('Data terkunci');
    }
}

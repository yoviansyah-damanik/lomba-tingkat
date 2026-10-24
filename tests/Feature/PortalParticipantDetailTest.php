<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalParticipantDetailTest extends TestCase
{
    use RefreshDatabase;

    private function gudepWithParticipant(string $name): array
    {
        $user = User::factory()->create();
        $user->assignRole(RoleEnum::ScoutGroup);

        $group = ScoutGroup::factory()->create(['user_id' => $user->id]);
        $patrol = Patrol::factory()->create(['scout_group_id' => $group->id]);

        $siaga = Participant::defaultScoutSiaga();
        $siaga['tkk'][0] = 'TKK Pengamat Burung';

        $participant = Participant::factory()->create([
            'patrol_id' => $patrol->id,
            'full_name' => $name,
            'photo_path' => 'participants/foto.jpg',
            'scout_siaga' => $siaga,
        ]);

        return [$user, $participant];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_gudep_can_view_full_detail_of_its_own_participant(): void
    {
        [$user, $participant] = $this->gudepWithParticipant('Rizky Harahap');

        $this->actingAs($user)
            ->get(route('scout-group.participants.show', $participant->id))
            ->assertOk()
            ->assertSee('Rizky Harahap')
            ->assertSee('Dokumen Persyaratan Yang Diunggah')
            ->assertSee('Data Orang Tua / Wali')
            ->assertSee('TKK Pengamat Burung');
    }

    public function test_gudep_cannot_view_participant_of_another_gudep(): void
    {
        [$owner, $participant] = $this->gudepWithParticipant('Peserta Milik Gudep A');
        [$other] = $this->gudepWithParticipant('Peserta Milik Gudep B');

        $this->actingAs($other)
            ->get(route('scout-group.participants.show', $participant->id))
            ->assertNotFound();

        $this->actingAs($owner)
            ->get(route('scout-group.participants.show', $participant->id))
            ->assertOk();
    }

    public function test_guest_and_non_gudep_roles_cannot_open_the_page(): void
    {
        [, $participant] = $this->gudepWithParticipant('Peserta');

        $this->get(route('scout-group.participants.show', $participant->id))->assertRedirect(route('login'));

        $admin = User::factory()->create();
        $admin->assignRole(RoleEnum::Admin);
        $this->actingAs($admin)
            ->get(route('scout-group.participants.show', $participant->id))
            ->assertForbidden();
    }

    public function test_patrols_page_links_to_participant_detail(): void
    {
        [$user, $participant] = $this->gudepWithParticipant('Rizky Harahap');

        $this->actingAs($user)
            ->get(route('scout-group.patrols'))
            ->assertOk()
            ->assertSee(route('scout-group.participants.show', $participant->id), false);
    }
}

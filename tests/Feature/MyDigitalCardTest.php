<?php

namespace Tests\Feature;

use App\Enums\RegistrationStatusEnum;
use App\Enums\RoleEnum;
use App\Models\Chaperone;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyDigitalCardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    private function account(RoleEnum $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes + ['is_active' => true]);
        $user->assignRole($role);

        return $user;
    }

    public function test_participant_sees_own_card_only_after_contingent_is_verified(): void
    {
        $group = ScoutGroup::factory()->create(['verification_status' => RegistrationStatusEnum::Draft]);
        $patrol = Patrol::factory()->create(['scout_group_id' => $group->id]);
        $participant = Participant::factory()->create(['patrol_id' => $patrol->id, 'full_name' => 'Rizky Peserta']);
        $user = $this->account(RoleEnum::Participant, ['participant_id' => $participant->id]);

        $this->actingAs($user)->get(route('digital-card.index'))
            ->assertOk()
            ->assertSee('Kartu Digital')
            ->assertSee(ScoutGroup::DOWNLOAD_LOCKED_MESSAGE)
            ->assertDontSee('Unduh ID Card');

        $group->update(['verification_status' => RegistrationStatusEnum::Verified]);

        $this->actingAs($user->fresh())->get(route('digital-card.index'))
            ->assertOk()
            ->assertSee('Rizky Peserta')
            ->assertSee($participant->id_number)
            ->assertSee('Unduh ID Card')->assertSee('Layar Penuh')->assertSee('open-card-preview', false)->assertSee('Tunjukkan QR Code ini kepada petugas')->assertSee('ID Card Resmi (79 x 126 mm)')
            ->assertSee(route('digital-cards.verify', $participant->qr_token), false);
    }

    public function test_chaperone_committee_and_judge_see_their_cards(): void
    {
        $group = ScoutGroup::factory()->create(['verification_status' => RegistrationStatusEnum::Verified]);
        $chaperone = Chaperone::factory()->create(['scout_group_id' => $group->id, 'full_name' => 'Kak Bindamping Kartu']);
        $chaperoneUser = $this->account(RoleEnum::Chaperone);
        $chaperone->update(['user_id' => $chaperoneUser->id]);

        $this->actingAs($chaperoneUser->fresh())->get(route('digital-card.index'))
            ->assertOk()->assertSee('Kak Bindamping Kartu');

        $committee = $this->account(RoleEnum::Committee, ['name' => 'Panitia Kartu']);
        $this->actingAs($committee)->get(route('digital-card.index'))
            ->assertOk()->assertSee('Panitia Kartu')->assertSee('PANITIA PELAKSANA');

        $judge = $this->account(RoleEnum::Judge, ['name' => 'Juri Kartu']);
        $this->actingAs($judge)->get(route('digital-card.index'))
            ->assertOk()->assertSee('Juri Kartu')->assertSee('DEWAN JURI PERLOMBAAN');
    }

    public function test_other_roles_cannot_open_page_and_menu_is_shown_to_card_holders(): void
    {
        $this->actingAs($this->account(RoleEnum::ScoutGroup))->get(route('digital-card.index'))->assertForbidden();

        $this->actingAs($this->account(RoleEnum::Judge))->get(route('dashboard'))
            ->assertSee(route('digital-card.index'), false);
    }
}

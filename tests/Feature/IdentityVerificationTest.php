<?php

namespace Tests\Feature;

use App\Enums\RegistrationStatusEnum;
use App\Enums\RoleEnum;
use App\Livewire\Admin\IdentityVerification;
use App\Models\Chaperone;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IdentityVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function userWith(RoleEnum $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function participantIn(RegistrationStatusEnum $groupStatus, string $name): Participant
    {
        $group = ScoutGroup::factory()->create(['verification_status' => $groupStatus]);
        $patrol = Patrol::factory()->create(['scout_group_id' => $group->id, 'gender_category' => 'boys']);

        return Participant::factory()->create(['patrol_id' => $patrol->id, 'full_name' => $name]);
    }

    public function test_only_administrator_superadmin_and_committee_can_open_the_page(): void
    {
        foreach ([RoleEnum::Admin, RoleEnum::Superadmin, RoleEnum::Committee] as $role) {
            $this->actingAs($this->userWith($role))
                ->get(route('admin.identity-verification.index'))
                ->assertOk()
                ->assertSee('Verifikasi Identitas');
        }

        foreach ([RoleEnum::Judge, RoleEnum::ScoutGroup, RoleEnum::Participant, RoleEnum::Chaperone] as $role) {
            $this->actingAs($this->userWith($role))
                ->get(route('admin.identity-verification.index'))
                ->assertForbidden();
        }

        auth()->logout();
        $this->get(route('admin.identity-verification.index'))->assertRedirect(route('login'));
    }

    public function test_scanning_a_verified_participant_card_shows_verified_identity(): void
    {
        $participant = $this->participantIn(RegistrationStatusEnum::Verified, 'Rizky Harahap');

        Livewire::actingAs($this->userWith(RoleEnum::Admin))
            ->test(IdentityVerification::class)
            ->call('verifyToken', route('digital-cards.verify', $participant->qr_token))
            ->assertSee('Identitas Terverifikasi')
            ->assertSee('Rizky Harahap')
            ->assertSee($participant->id_number)
            ->assertSee(route('admin.participants.show', $participant->id), false)
            ->assertSet('result.valid', true)
            ->assertSet('result.is_verified', true);
    }

    public function test_participant_of_unverified_contingent_is_flagged(): void
    {
        $participant = $this->participantIn(RegistrationStatusEnum::Submitted, 'Peserta Belum Sah');

        Livewire::actingAs($this->userWith(RoleEnum::Admin))
            ->test(IdentityVerification::class)
            ->call('verifyToken', $participant->qr_token)
            ->assertSee('Belum Terverifikasi')
            ->assertSee('Peserta Belum Sah')
            ->assertSet('result.is_verified', false);
    }

    public function test_chaperone_and_staff_cards_are_resolved(): void
    {
        $group = ScoutGroup::factory()->create(['verification_status' => RegistrationStatusEnum::Verified]);
        $chaperone = Chaperone::factory()->create(['scout_group_id' => $group->id, 'full_name' => 'Rahmat Hidayat', 'gender' => 'male']);
        $judge = $this->userWith(RoleEnum::Judge);
        $judge->update(['name' => 'Drs. Ahmad Fauzi', 'is_active' => true]);

        $component = Livewire::actingAs($this->userWith(RoleEnum::Superadmin))->test(IdentityVerification::class);

        $component->call('verifyToken', $chaperone->qr_token)
            ->assertSee('Rahmat Hidayat')
            ->assertSet('result.type', 'chaperone')
            ->assertSee(route('admin.chaperones.show', $chaperone->id), false);

        $component->call('verifyToken', $judge->qr_token)
            ->assertSee('Drs. Ahmad Fauzi')
            ->assertSet('result.type', 'staff')
            ->assertSet('result.is_verified', true);

        // Riwayat memuat kedua pemindaian terbaru lebih dulu
        $this->assertSame('Drs. Ahmad Fauzi', $component->get('history')[0]['name']);
        $this->assertCount(2, $component->get('history'));
    }

    public function test_inactive_staff_account_is_not_verified(): void
    {
        $committee = $this->userWith(RoleEnum::Committee);
        $committee->update(['is_active' => false]);

        Livewire::actingAs($this->userWith(RoleEnum::Admin))
            ->test(IdentityVerification::class)
            ->call('verifyToken', $committee->qr_token)
            ->assertSet('result.is_verified', false)
            ->assertSee('nonaktif');
    }

    public function test_unknown_qr_is_reported_invalid(): void
    {
        Livewire::actingAs($this->userWith(RoleEnum::Admin))
            ->test(IdentityVerification::class)
            ->call('verifyToken', 'bukan-token-yang-sah')
            ->assertSee('QR Code Tidak Valid')
            ->assertSet('result.valid', false)
            ->call('verifyToken', '')
            ->assertSet('result.valid', false);
    }

    public function test_manual_input_accepts_full_verification_url(): void
    {
        $participant = $this->participantIn(RegistrationStatusEnum::Verified, 'Input Manual');

        Livewire::actingAs($this->userWith(RoleEnum::Admin))
            ->test(IdentityVerification::class)
            ->set('manualInput', route('digital-cards.verify', $participant->qr_token).'?x=1')
            ->call('verifyManual')
            ->assertSee('Input Manual')
            ->assertSet('manualInput', '');
    }
}

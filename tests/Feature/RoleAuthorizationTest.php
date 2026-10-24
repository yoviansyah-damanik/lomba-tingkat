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
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    /**
     * Menguji hak akses Admin terhadap dashboard dan Gate otorisasi
     */
    public function test_admin_can_access_admin_dashboard_and_has_administration_gates(): void
    {
        $admin = User::factory()->create([
            'is_active' => true,
        ]);
        $admin->assignRole(RoleEnum::Admin);

        $this->assertTrue($admin->isAdmin());
        $this->assertTrue(Gate::forUser($admin)->allows('admin'));
        $this->assertTrue(Gate::forUser($admin)->allows('verify-registrations'));
        $this->assertTrue(Gate::forUser($admin)->allows('execute-pos-scanning'));
        $this->assertTrue(Gate::forUser($admin)->allows('monitor-operations'));

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertRedirect(route('dashboard'));
    }

    /**
     * Menguji bahwa panitia hanya memonitor operasional dan ditolak dari aksi admin
     */
    public function test_committee_member_cannot_access_admin_dashboard_or_verify_registrations(): void
    {
        $committee = User::factory()->create([
            'is_active' => true,
        ]);
        $committee->assignRole(RoleEnum::Committee);

        $this->assertTrue($committee->isCommittee());
        $this->assertFalse($committee->isAdmin());
        $this->assertFalse(Gate::forUser($committee)->allows('verify-registrations'));
        $this->assertFalse(Gate::forUser($committee)->allows('execute-pos-scanning'));
        $this->assertTrue(Gate::forUser($committee)->allows('monitor-operations'));

        // Panitia ditolak dari area admin
        $response = $this->actingAs($committee)->get('/admin/dashboard');
        $response->assertStatus(403);

        // Panitia dapat membuka area panitia
        $committeeResponse = $this->actingAs($committee)->get('/committee/dashboard');
        $committeeResponse->assertRedirect(route('dashboard'));
    }

    /**
     * Menguji hak akses juri untuk penilaian dan pencegahan akses admin
     */
    public function test_judge_can_access_judge_dashboard_but_is_forbidden_from_admin_dashboard(): void
    {
        $judge = User::factory()->create([
            'is_active' => true,
        ]);
        $judge->assignRole(RoleEnum::Judge);

        $this->assertTrue($judge->isJudge());
        $this->assertFalse(Gate::forUser($judge)->allows('admin'));
        $this->assertFalse(Gate::forUser($judge)->allows('verify-registrations'));

        $this->actingAs($judge)->get('/admin/dashboard')->assertStatus(403);
        $this->actingAs($judge)->get('/judge/dashboard')->assertRedirect(route('dashboard'));
    }

    /**
     * Menguji rangkap peran yang sah: Panitia Merangkap Juri
     */
    public function test_dual_role_user_committee_and_judge_can_access_both_committee_and_judge_routes(): void
    {
        $dualUser = User::factory()->create([
            'is_active' => true,
        ]);
        $dualUser->assignRole(RoleEnum::Committee);
        $dualUser->assignRole(RoleEnum::Judge);

        $this->assertTrue($dualUser->isCommitteeAndJudge());

        $this->actingAs($dualUser)->get('/committee/dashboard')->assertRedirect(route('dashboard'));
        $this->actingAs($dualUser)->get('/judge/dashboard')->assertRedirect(route('dashboard'));
        $this->actingAs($dualUser)->get('/admin/dashboard')->assertStatus(403);
    }

    /**
     * Menguji bahwa akun non-aktif tidak dapat masuk ke sistem
     */
    public function test_inactive_user_is_logged_out_and_blocked_from_accessing_dashboard(): void
    {
        $user = User::factory()->create([
            'is_active' => false,
        ]);
        $user->assignRole(RoleEnum::Participant);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    /**
     * Menguji Panitia dan Juri diberikan akses untuk halaman peserta, bindamping, gugusdepan, serta kartu digitalnya
     */
    public function test_committee_and_judge_can_access_participants_chaperones_and_scout_groups_pages(): void
    {
        $committee = User::factory()->create(['is_active' => true]);
        $committee->assignRole(RoleEnum::Committee);

        $judge = User::factory()->create(['is_active' => true]);
        $judge->assignRole(RoleEnum::Judge);

        $gudepUser = User::factory()->create(['is_active' => true]);
        $gudepUser->assignRole(RoleEnum::ScoutGroup);

        $scoutGroup = ScoutGroup::factory()->create([
            'user_id' => $gudepUser->id,
            'verification_status' => RegistrationStatusEnum::Verified,
        ]);
        $patrol = Patrol::factory()->create(['scout_group_id' => $scoutGroup->id]);
        $participant = Participant::factory()->create(['patrol_id' => $patrol->id]);
        $chaperone = Chaperone::factory()->create(['scout_group_id' => $scoutGroup->id]);

        foreach ([$committee, $judge] as $actor) {
            // Halaman Peserta
            $this->actingAs($actor)->get(route('admin.participants.index'))->assertOk();

            // Halaman Bindamping
            $this->actingAs($actor)->get(route('admin.chaperones.index'))->assertOk();

            // Halaman Gugusdepan (index & detail)
            $this->actingAs($actor)->get(route('admin.scout-groups.index'))->assertOk();
            $this->actingAs($actor)->get(route('admin.scout-groups.show', $scoutGroup->id))->assertOk();

            // Kartu Digital (Peserta & Bindamping)
            $this->actingAs($actor)->get(route('digital-cards.participant', $participant->id))->assertOk();
            $this->actingAs($actor)->get(route('digital-cards.chaperone', $chaperone->id))->assertOk();
            $this->actingAs($actor)->get(route('digital-cards.patrol.batch', $patrol->id))->assertOk();
            $this->actingAs($actor)->get(route('digital-cards.scout-group.batch', $scoutGroup->id))->assertOk();

            // Dilarang melakukan tindakan verifikasi berkas kontingen (Hanya Admin)
            $panel = fn () => \Livewire\Livewire::withoutLazyLoading()->actingAs($actor)->test(\App\Livewire\Admin\ScoutGroupVerificationActions::class, ['scoutGroupId' => $scoutGroup->id]);
            $panel()->call('verify')->assertForbidden();
            $panel()->set('revisionNotes', 'Catatan revisi')->call('requestRevision')->assertForbidden();
            $panel()->set('rejectNotes', 'Catatan penolakan')->call('reject')->assertForbidden();
        }
    }

    /**
     * Menguji bahwa roleLabel pengguna sesuai dengan role yang login dan tampil di navbar publik
     */
    public function test_user_role_label_is_correct_and_displayed_on_public_navbar(): void
    {
        $admin = User::factory()->create(['name' => 'Admin Utama', 'is_active' => true]);
        $admin->assignRole(RoleEnum::Admin);
        $this->assertEquals('Administrator', $admin->roleLabel());

        $committee = User::factory()->create(['name' => 'Panitia Lapangan', 'is_active' => true]);
        $committee->assignRole(RoleEnum::Committee);
        $this->assertEquals('Panitia', $committee->roleLabel());

        $judge = User::factory()->create(['name' => 'Juri Lomba', 'is_active' => true]);
        $judge->assignRole(RoleEnum::Judge);
        $this->assertEquals('Juri', $judge->roleLabel());

        $dual = User::factory()->create(['name' => 'Panitia dan Juri', 'is_active' => true]);
        $dual->assignRole(RoleEnum::Committee);
        $dual->assignRole(RoleEnum::Judge);
        $this->assertEquals('Panitia & Juri', $dual->roleLabel());

        $scoutGroupUser = User::factory()->create(['name' => 'Pembina Gudep', 'is_active' => true]);
        $scoutGroupUser->assignRole(RoleEnum::ScoutGroup);
        $this->assertEquals('Gugus Depan', $scoutGroupUser->roleLabel());

        $participantUser = User::factory()->create(['name' => 'Peserta Penggalang', 'is_active' => true]);
        $participantUser->assignRole(RoleEnum::Participant);
        $this->assertEquals('Peserta', $participantUser->roleLabel());

        // Verifikasi navbar di layout publik menampilkan label role bukan "Pengguna"
        $response = $this->actingAs($committee)->get(route('schedules'));
        $response->assertOk();
        $response->assertSee('Panitia');
        $response->assertDontSee('id="user-menu-button">Pengguna', false);
    }
}

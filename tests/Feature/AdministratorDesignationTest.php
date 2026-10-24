<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Livewire\Admin\OfficialsManagement;
use App\Models\AuditLog;
use App\Models\User;
use Database\Seeders\AdministratorSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdministratorDesignationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, AdministratorSeeder::class]);
    }

    private function makeUser(string $username, array $roles): User
    {
        $user = User::factory()->create(['username' => $username, 'is_active' => true]);
        foreach ($roles as $role) {
            $user->assignRole($role);
        }

        return $user->fresh();
    }

    /**
     * Administrator bawaan adalah Panitia yang ditunjuk, bukan Superadmin
     */
    public function test_seeded_administrator_is_a_designated_committee_member(): void
    {
        $admin = User::where('username', 'admin')->first();

        $this->assertTrue($admin->isAdministrator());
        $this->assertTrue($admin->isCommittee());
        $this->assertFalse($admin->isSuperadmin());
        $this->assertSame('Panitia & Administrator', $admin->roleLabel());

        $this->assertFalse(User::where('username', 'superadmin')->first()->isAdministrator());
    }

    /**
     * Superadmin menunjuk Panitia menjadi Administrator; penunjukan tanpa peran Panitia ditolak
     */
    public function test_superadmin_designates_administrator_only_from_committee(): void
    {
        $this->actingAs(User::where('username', 'superadmin')->first());

        Livewire::test(OfficialsManagement::class)
            ->call('openCreateModal')
            ->set('name', 'Kak Juri Saja')
            ->set('selectedRoles', ['judge'])
            ->set('is_administrator', true)
            ->call('save')
            ->assertHasErrors(['is_administrator']);

        Livewire::test(OfficialsManagement::class)
            ->call('openCreateModal')
            ->set('name', 'Kak Panitia Baru')
            ->set('selectedRoles', ['committee'])
            ->set('is_administrator', true)
            ->call('save')
            ->assertHasNoErrors();

        $created = User::where('name', 'Kak Panitia Baru')->first();
        $this->assertTrue($created->isAdministrator());
        $this->assertTrue($created->isCommittee());
    }

    /**
     * Administrator biasa tidak dapat menunjuk Administrator lain, dan penunjukan tetap bila diedit
     */
    public function test_administrator_cannot_designate_others_and_keeps_own_designation(): void
    {
        $admin = User::where('username', 'admin')->first();
        $this->actingAs($admin);

        Livewire::test(OfficialsManagement::class)
            ->call('openCreateModal')
            ->set('name', 'Kak Tanpa Hak')
            ->set('selectedRoles', ['committee'])
            ->set('is_administrator', true)
            ->call('save');

        $this->assertFalse(User::where('name', 'Kak Tanpa Hak')->first()->isAdministrator());

        Livewire::test(OfficialsManagement::class)
            ->call('openEditModal', $admin->id)
            ->set('position', 'Koordinator')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertTrue($admin->fresh()->isAdministrator());
    }

    /**
     * Audit log tetap atas nama akun, dengan status Administrator hanya bila bertindak sebagai Administrator
     */
    public function test_audit_log_marks_acting_role_only_for_designated_administrator(): void
    {
        $admin = User::where('username', 'admin')->first();
        $this->actingAs($admin);
        $log = AuditLog::log('test_action', 'Model', '1');
        $this->assertSame($admin->id, $log->user_id);
        $this->assertSame('admin', $log->acting_role);

        $panitia = $this->makeUser('panitia.biasa', [RoleEnum::Committee]);
        $this->actingAs($panitia);
        $log = AuditLog::log('test_action', 'Model', '2');
        $this->assertSame($panitia->id, $log->user_id);
        $this->assertNull($log->acting_role);

        $this->actingAs(User::where('username', 'superadmin')->first());
        $this->assertNull(AuditLog::log('test_action', 'Model', '3')->acting_role);

        $this->actingAs($admin);
        $this->assertSame('judge', AuditLog::log('score_finalized', 'Model', '4', userId: $admin->id, actingRole: 'judge')->acting_role);
    }

    /**
     * Administrator yang merangkap Juri boleh menilai; Administrator murni hanya memantau
     */
    public function test_admin_who_is_also_judge_is_not_admin_only(): void
    {
        $dual = $this->makeUser('admin.juri', [RoleEnum::Committee, RoleEnum::Admin, RoleEnum::Judge]);
        $pure = User::where('username', 'admin')->first();

        $this->assertFalse($dual->isAdminOnly());
        $this->assertTrue($pure->isAdminOnly());
        $this->assertSame('Panitia, Juri & Administrator', $dual->roleLabel());
    }

    /**
     * ID pelaku non-audit tidak diisi untuk Administrator
     */
    public function test_recordable_id_is_null_for_administrator(): void
    {
        $this->assertNull(User::where('username', 'admin')->first()->recordableId());

        $panitia = $this->makeUser('panitia.lain', [RoleEnum::Committee]);
        $this->assertSame($panitia->id, $panitia->recordableId());
    }
}

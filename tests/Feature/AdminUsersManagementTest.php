<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Livewire\Admin\UsersManagement;
use App\Models\AuditLog;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AdminUsersManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;
    protected User $admin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RoleSeeder::class,
            UserSeeder::class,
        ]);

        $this->superadmin = User::whereHas('roles', fn ($q) => $q->where('name', 'superadmin'))->first();
        $this->admin = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->first();
        $this->regularUser = User::where('username', 'panitia')->first();
    }

    /**
     * Memastikan tamu (guest) tidak dapat mengakses manajemen pengguna
     */
    public function test_guest_cannot_access_users_management(): void
    {
        $response = $this->get(route('admin.users.index'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Memastikan pengguna non-admin (misal Panitia) dilarang mengakses
     */
    public function test_non_admin_cannot_access_users_management(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('admin.users.index'));
        $response->assertStatus(403);
    }

    /**
     * Memastikan admin dapat mengakses halaman manajemen pengguna
     */
    public function test_admin_can_access_users_management(): void
    {
        // Halaman Users Management hanya dapat diakses oleh Superadmin
        $response = $this->actingAs($this->superadmin)->get(route('admin.users.index'));
        $response->assertStatus(200);
        $response->assertSee('Tata Kelola Pengguna & Peran');

        Livewire::actingAs($this->superadmin)
            ->test(UsersManagement::class)
            ->assertStatus(200)
            ->assertSee($this->superadmin->name)
            ->assertSee($this->regularUser->name);
    }

    /**
     * Superadmin tidak dapat membuat, mengubah, atau menghapus data pengguna (aksinya tidak tersedia)
     */
    public function test_superadmin_cannot_create_update_or_delete_user_data(): void
    {
        $component = Livewire::actingAs($this->superadmin)->test(UsersManagement::class);

        foreach (['openCreateModal', 'createUser', 'openEditModal', 'updateUser', 'openDeleteModal', 'deleteUser'] as $method) {
            $this->assertFalse(method_exists(UsersManagement::class, $method), "Aksi {$method} seharusnya tidak tersedia");
        }

        $component->assertDontSee('Tambah Pengguna Baru')
            ->assertDontSee('Edit Pengguna & Peran')
            ->assertDontSee('Hapus Akun');

        $before = $this->regularUser->only(['name', 'username', 'phone_number', 'institution', 'position']);
        $roles = $this->regularUser->roles()->pluck('name')->sort()->values()->all();
        $count = User::count();

        $this->regularUser->refresh();
        $this->assertSame($before, $this->regularUser->only(['name', 'username', 'phone_number', 'institution', 'position']));
        $this->assertSame($roles, $this->regularUser->roles()->pluck('name')->sort()->values()->all());
        $this->assertSame($count, User::count());
    }
    /**
     * Memastikan admin dapat menonaktifkan dan mengaktifkan akun pengguna lain
     */
    public function test_admin_can_toggle_user_active_status(): void
    {
        $this->assertTrue($this->regularUser->is_active);

        // Nonaktifkan
        Livewire::actingAs($this->superadmin)
            ->test(UsersManagement::class)
            ->call('toggleActive', $this->regularUser->id)
            ->assertDispatched('toast-show');

        $this->regularUser->refresh();
        $this->assertFalse($this->regularUser->is_active);

        // Aktifkan kembali
        Livewire::actingAs($this->superadmin)
            ->test(UsersManagement::class)
            ->call('toggleActive', $this->regularUser->id)
            ->assertDispatched('toast-show');

        $this->regularUser->refresh();
        $this->assertTrue($this->regularUser->is_active);
    }

    /**
     * Memastikan admin tidak dapat menonaktifkan akun sendiri
     */
    public function test_admin_cannot_deactivate_self(): void
    {
        Livewire::actingAs($this->superadmin)
            ->test(UsersManagement::class)
            ->call('toggleActive', $this->superadmin->id)
            ->assertDispatched('toast-show');

        $this->superadmin->refresh();
        $this->assertTrue($this->superadmin->is_active);
    }

    /**
     * Memastikan admin dapat mereset kata sandi pengguna
     */
    public function test_admin_can_reset_user_password(): void
    {
        Livewire::actingAs($this->superadmin)
            ->test(UsersManagement::class)
            ->call('openResetPasswordModal', $this->regularUser->id)
            ->set('newPassword', 'passwordBaru2026')
            ->call('resetUserPassword')
            ->assertHasNoErrors()
            ->assertDispatched('toast-show');

        $this->regularUser->refresh();
        $this->assertTrue(Hash::check('passwordBaru2026', $this->regularUser->password));
    }

    /**
     * Superadmin dapat menonaktifkan/mengaktifkan dan mereset kata sandi akun apa pun, termasuk Peserta/Gudep/Bindamping
     */
    public function test_superadmin_can_toggle_and_reset_password_of_any_account_type(): void
    {
        $gudep = User::factory()->create(['is_active' => true]);
        $gudep->assignRole(RoleEnum::ScoutGroup);
        $participant = User::factory()->create(['is_active' => true]);
        $participant->assignRole(RoleEnum::Participant);

        foreach ([$gudep, $participant] as $account) {
            Livewire::actingAs($this->superadmin)->test(UsersManagement::class)
                ->call('toggleActive', $account->id)
                ->call('openResetPasswordModal', $account->id)
                ->set('newPassword', 'sandiBaru123')
                ->call('resetUserPassword')
                ->assertHasNoErrors();

            $account->refresh();
            $this->assertFalse($account->is_active);
            $this->assertTrue(Hash::check('sandiBaru123', $account->password));
        }

        $this->assertDatabaseHas('audit_logs', ['action' => 'user_status_toggled', 'model_id' => $gudep->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'user_password_reset', 'model_id' => $participant->id]);
    }

    /**
     * Modal riwayat menampilkan login (berhasil dan gagal) serta audit log pengguna terpilih
     */
    public function test_superadmin_can_open_login_history_and_audit_log_modal(): void
    {
        \App\Models\LoginHistory::record($this->regularUser->id, $this->regularUser->username, 'success');
        \App\Models\LoginHistory::record($this->regularUser->id, $this->regularUser->username, 'failed');
        \App\Models\LoginHistory::record(User::where('username', 'juri')->first()->id, 'juri', 'success');
        AuditLog::log('uji_aksi_pengguna', User::class, $this->regularUser->id, null, null, 'Aksi terhadap akun panitia', $this->superadmin->id);

        Livewire::actingAs($this->superadmin)->test(UsersManagement::class)
            ->assertSee('Riwayat Login & Audit Log', false)
            ->call('openHistoryModal', $this->regularUser->id)
            ->assertSet('historyUserId', $this->regularUser->id)
            ->call('closeHistoryModal')
            ->assertSet('historyUserId', null);

        Livewire::withoutLazyLoading()->actingAs($this->superadmin)
            ->test(\App\Livewire\Admin\UserActivityHistory::class, ['userId' => $this->regularUser->id])
            ->assertSee('Riwayat Login (2)')
            ->assertSee('Berhasil')
            ->assertSee('Gagal')
            ->call('$set', 'tab', 'audit')
            ->assertSee('uji_aksi_pengguna')
            ->assertSee('Aksi terhadap akun panitia');
    }

    public function test_successful_and_failed_logins_are_recorded(): void
    {
        $user = User::factory()->create(['username' => 'riwayat.user', 'password' => Hash::make('rahasia123'), 'is_active' => true]);

        Livewire::test(\App\Livewire\Auth\Login::class)
            ->set('username', 'riwayat.user')->set('password', 'salah-total')->call('login');
        Livewire::test(\App\Livewire\Auth\Login::class)
            ->set('username', 'riwayat.user')->set('password', 'rahasia123')->call('login');

        $this->assertSame(1, \App\Models\LoginHistory::where('user_id', $user->id)->where('status', 'failed')->count());
        $this->assertSame(1, \App\Models\LoginHistory::where('user_id', $user->id)->where('status', 'success')->count());
        $this->assertNotNull(\App\Models\LoginHistory::where('user_id', $user->id)->first()->ip_address);
    }
}
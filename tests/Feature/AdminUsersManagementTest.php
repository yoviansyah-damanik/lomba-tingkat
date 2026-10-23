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
        $this->regularUser = User::whereHas('roles', fn ($q) => $q->where('name', 'committee'))->first();
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
     * Memastikan admin dapat membuat akun pengguna baru dan menetapkan perannya
     */
    public function test_admin_can_create_new_user(): void
    {
        Livewire::actingAs($this->superadmin)
            ->test(UsersManagement::class)
            ->call('openCreateModal')
            ->set('name', 'Kak Budi Santoso')
            ->set('username', 'budi.santoso')
            ->set('phone_number', '081298765432')
            ->set('institution', 'Kwarran Padangsidimpuan Selatan')
            ->set('position', 'Anggota Dewan Juri')
            ->set('password', 'secret123')
            ->set('selectedRoles', [RoleEnum::Judge->value])
            ->call('createUser')
            ->assertHasNoErrors()
            ->assertDispatched('toast-show');

        $newUser = User::where('username', 'budi.santoso')->first();
        $this->assertNotNull($newUser);
        $this->assertEquals('Kak Budi Santoso', $newUser->name);
        $this->assertTrue($newUser->hasRole(RoleEnum::Judge));
        $this->assertTrue(Hash::check('secret123', $newUser->password));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user_created',
            'model_id' => $newUser->id,
        ]);
    }

    /**
     * Memastikan admin dapat memperbarui data pengguna dan mengubah perannya
     */
    public function test_admin_can_update_user(): void
    {
        Livewire::actingAs($this->superadmin)
            ->test(UsersManagement::class)
            ->call('openEditModal', $this->regularUser->id)
            ->set('name', 'Panitia Lapangan Senior')
            ->set('selectedRoles', [RoleEnum::Committee->value, RoleEnum::Judge->value])
            ->call('updateUser')
            ->assertHasNoErrors()
            ->assertDispatched('toast-show');

        $this->regularUser->refresh();
        $this->assertEquals('Panitia Lapangan Senior', $this->regularUser->name);
        $this->assertTrue($this->regularUser->hasRole(RoleEnum::Committee));
        $this->assertTrue($this->regularUser->hasRole(RoleEnum::Judge));
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
     * Memastikan admin dapat menghapus akun pengguna lain
     */
    public function test_admin_can_delete_user(): void
    {
        // Akun Participant adalah managed account dan diblokir ensureEditable.
        // Gunakan role committee yang dapat dikelola superadmin secara penuh.
        $userToDelete = User::factory()->create();
        $userToDelete->assignRole(RoleEnum::Committee);

        Livewire::actingAs($this->superadmin)
            ->test(UsersManagement::class)
            ->call('openDeleteModal', $userToDelete->id)
            ->call('deleteUser');

        // Verifikasi user berhasil dihapus dari database
        $this->assertDatabaseMissing('users', ['id' => $userToDelete->id]);
    }
}

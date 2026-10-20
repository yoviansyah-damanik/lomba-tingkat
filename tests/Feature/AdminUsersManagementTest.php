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

    protected User $admin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RoleSeeder::class,
            UserSeeder::class,
        ]);

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
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));
        $response->assertStatus(200);
        $response->assertSee('Tata Kelola Pengguna & Peran');

        Livewire::actingAs($this->admin)
            ->test(UsersManagement::class)
            ->assertStatus(200)
            ->assertSee($this->admin->name)
            ->assertSee($this->regularUser->name);
    }

    /**
     * Memastikan admin dapat membuat akun pengguna baru dan menetapkan perannya
     */
    public function test_admin_can_create_new_user(): void
    {
        Livewire::actingAs($this->admin)
            ->test(UsersManagement::class)
            ->call('openCreateModal')
            ->set('name', 'Kak Budi Santoso')
            ->set('email', 'budi.santoso@lt3kwarcab.id')
            ->set('phone_number', '081298765432')
            ->set('institution', 'Kwarran Padangsidimpuan Selatan')
            ->set('position', 'Anggota Dewan Juri')
            ->set('password', 'secret123')
            ->set('selectedRoles', [RoleEnum::Judge->value])
            ->call('createUser')
            ->assertHasNoErrors()
            ->assertSee('berhasil ditambahkan');

        $newUser = User::where('email', 'budi.santoso@lt3kwarcab.id')->first();
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
        Livewire::actingAs($this->admin)
            ->test(UsersManagement::class)
            ->call('openEditModal', $this->regularUser->id)
            ->set('name', 'Panitia Lapangan Senior')
            ->set('selectedRoles', [RoleEnum::Committee->value, RoleEnum::Judge->value])
            ->call('updateUser')
            ->assertHasNoErrors()
            ->assertSee('berhasil diperbarui');

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
        Livewire::actingAs($this->admin)
            ->test(UsersManagement::class)
            ->call('toggleActive', $this->regularUser->id)
            ->assertSee('berhasil dinonaktifkan');

        $this->regularUser->refresh();
        $this->assertFalse($this->regularUser->is_active);

        // Aktifkan kembali
        Livewire::actingAs($this->admin)
            ->test(UsersManagement::class)
            ->call('toggleActive', $this->regularUser->id)
            ->assertSee('berhasil diaktifkan');

        $this->regularUser->refresh();
        $this->assertTrue($this->regularUser->is_active);
    }

    /**
     * Memastikan admin tidak dapat menonaktifkan akun sendiri
     */
    public function test_admin_cannot_deactivate_self(): void
    {
        Livewire::actingAs($this->admin)
            ->test(UsersManagement::class)
            ->call('toggleActive', $this->admin->id)
            ->assertSee('Anda tidak dapat menonaktifkan akun sendiri');

        $this->admin->refresh();
        $this->assertTrue($this->admin->is_active);
    }

    /**
     * Memastikan admin dapat mereset kata sandi pengguna
     */
    public function test_admin_can_reset_user_password(): void
    {
        Livewire::actingAs($this->admin)
            ->test(UsersManagement::class)
            ->call('openResetPasswordModal', $this->regularUser->id)
            ->set('newPassword', 'passwordBaru2026')
            ->call('resetUserPassword')
            ->assertHasNoErrors()
            ->assertSee('berhasil disetel ulang');

        $this->regularUser->refresh();
        $this->assertTrue(Hash::check('passwordBaru2026', $this->regularUser->password));
    }

    /**
     * Memastikan admin dapat menghapus akun pengguna lain
     */
    public function test_admin_can_delete_user(): void
    {
        $userToDelete = User::factory()->create();
        $userToDelete->assignRole(RoleEnum::Participant);

        Livewire::actingAs($this->admin)
            ->test(UsersManagement::class)
            ->call('openDeleteModal', $userToDelete->id)
            ->call('deleteUser')
            ->assertSee('berhasil dihapus');

        $this->assertDatabaseMissing('users', ['id' => $userToDelete->id]);
    }
}

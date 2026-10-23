<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Livewire\Admin\OfficialsManagement;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminOfficialsLivewireTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $committeeUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->admin = User::factory()->create([
            'username' => 'admin',
            'is_active' => true,
        ]);
        $this->admin->assignRole(RoleEnum::Admin);

        $this->committeeUser = User::factory()->create([
            'name' => 'Kak Budi Panitia',
            'username' => 'budi',
            'is_active' => true,
        ]);
        $this->committeeUser->assignRole(RoleEnum::Committee);
    }

    public function test_admin_can_render_officials_livewire_page(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(OfficialsManagement::class)
            ->assertOk()
            ->assertSee('Aparatur Panitia')
            ->assertSee('Kak Budi Panitia')
            ->assertSee('Dewan Juri');
    }

    public function test_officials_search_and_role_filter_works_reactively(): void
    {
        $this->actingAs($this->admin);

        $judge = User::factory()->create([
            'name' => 'Kak Siti Dewan Juri',
            'username' => 'siti',
            'is_active' => true,
        ]);
        $judge->assignRole(RoleEnum::Judge);

        Livewire::test(OfficialsManagement::class)
            ->set('search', 'Siti')
            ->assertSee('Kak Siti Dewan Juri')
            ->assertDontSee('Kak Budi Panitia')
            ->set('search', '')
            ->set('roleFilter', 'committee')
            ->assertSee('Kak Budi Panitia')
            ->assertDontSee('Kak Siti Dewan Juri');
    }

    public function test_admin_can_toggle_official_status_reactively(): void
    {
        $this->actingAs($this->admin);

        $this->assertTrue($this->committeeUser->is_active);

        Livewire::test(OfficialsManagement::class)
            ->call('toggleStatus', $this->committeeUser->id);

        $this->assertFalse($this->committeeUser->fresh()->is_active);
    }

    public function test_admin_can_create_new_official_with_auto_generated_credentials_via_livewire(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(OfficialsManagement::class)
            ->call('openCreateModal')
            ->set('name', 'Kak Rahmat Baru')
            ->set('selectedRoles', ['committee', 'judge'])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'name' => 'Kak Rahmat Baru',
            'is_active' => true,
        ]);

        $created = User::where('name', 'Kak Rahmat Baru')->first();
        $this->assertNotNull($created);
        $this->assertMatchesRegularExpression('/^A-\d{6}$/', $created->username);
        $this->assertMatchesRegularExpression('/^A-\d{6}$/', $created->id_number);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check($created->id_number, $created->password));
        $this->assertTrue($created->must_change_password);
        $this->assertTrue($created->isCommittee());
        $this->assertTrue($created->isJudge());
    }

    public function test_official_with_default_password_is_prompted_to_change_password_on_login(): void
    {
        $official = User::factory()->create([
            'id_number' => 'A-123456',
            'username' => 'a-123456',
            'password' => \Illuminate\Support\Facades\Hash::make('A-123456'),
            'must_change_password' => true,
            'is_active' => true,
        ]);
        $official->assignRole(RoleEnum::Committee);

        $this->actingAs($official);

        // Akses rute berwewenang (misalnya dashboard) dialihkan ke security.edit
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('security.edit'));
    }

    public function test_admin_can_edit_official_roles_via_livewire(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(OfficialsManagement::class)
            ->call('openEditModal', $this->committeeUser->id)
            ->set('name', 'Kak Budi Update')
            ->set('selectedRoles', ['judge'])
            ->call('save')
            ->assertHasNoErrors();

        $updated = $this->committeeUser->fresh();
        $this->assertEquals('Kak Budi Update', $updated->name);
        $this->assertFalse($updated->isCommittee());
        $this->assertTrue($updated->isJudge());
    }

    public function test_admin_can_delete_official_and_account_is_removed(): void
    {
        $this->actingAs($this->admin);

        $userId = $this->committeeUser->id;

        Livewire::test(OfficialsManagement::class)
            ->call('delete', $userId);

        $this->assertDatabaseMissing('users', [
            'id' => $userId,
        ]);
    }
}

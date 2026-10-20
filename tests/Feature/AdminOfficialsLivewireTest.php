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
            'email' => 'admin@test.com',
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $this->admin->assignRole(RoleEnum::Admin);

        $this->committeeUser = User::factory()->create([
            'name' => 'Kak Budi Panitia',
            'email' => 'budi@test.com',
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
            'email' => 'siti@test.com',
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

    public function test_admin_can_create_new_official_via_livewire(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(OfficialsManagement::class)
            ->call('openCreateModal')
            ->set('name', 'Kak Rahmat Baru')
            ->set('email', 'rahmat@test.com')
            ->set('password', 'password123')
            ->set('selectedRoles', ['committee', 'judge'])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'name' => 'Kak Rahmat Baru',
            'email' => 'rahmat@test.com',
        ]);

        $created = User::where('email', 'rahmat@test.com')->first();
        $this->assertTrue($created->isCommittee());
        $this->assertTrue($created->isJudge());
    }
}

<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\Chaperone;
use App\Models\ScoutGroup;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalChaperoneDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function gudepWithChaperone(string $name): array
    {
        $user = User::factory()->create();
        $user->assignRole(RoleEnum::ScoutGroup);

        $group = ScoutGroup::factory()->create(['user_id' => $user->id]);
        $chaperone = Chaperone::factory()->create([
            'scout_group_id' => $group->id,
            'full_name' => $name,
            'gender' => 'male',
            'other_trainings' => [['name' => 'Pelatihan Pertolongan Pertama', 'year' => '2022', 'location' => 'PMI Kota', 'notes' => 'Sertifikat']],
        ]);

        return [$user, $chaperone];
    }

    public function test_gudep_can_view_full_detail_of_its_own_chaperone(): void
    {
        [$user, $chaperone] = $this->gudepWithChaperone('Rahmat Hidayat');

        $this->actingAs($user)
            ->get(route('scout-group.chaperones.show', $chaperone->id))
            ->assertOk()
            ->assertSee('Rahmat Hidayat')
            ->assertSee('Bindamping Putra')
            ->assertSee('Pelatihan Pertolongan Pertama');
    }

    public function test_gudep_cannot_view_chaperone_of_another_gudep(): void
    {
        [$owner, $chaperone] = $this->gudepWithChaperone('Bindamping Gudep A');
        [$other] = $this->gudepWithChaperone('Bindamping Gudep B');

        $this->actingAs($other)
            ->get(route('scout-group.chaperones.show', $chaperone->id))
            ->assertNotFound();

        $this->actingAs($owner)
            ->get(route('scout-group.chaperones.show', $chaperone->id))
            ->assertOk();
    }

    public function test_guest_and_non_gudep_roles_cannot_open_the_page(): void
    {
        [, $chaperone] = $this->gudepWithChaperone('Bindamping');

        $this->get(route('scout-group.chaperones.show', $chaperone->id))->assertRedirect(route('login'));

        $admin = User::factory()->create();
        $admin->assignRole(RoleEnum::Admin);
        $this->actingAs($admin)
            ->get(route('scout-group.chaperones.show', $chaperone->id))
            ->assertForbidden();
    }

    public function test_chaperones_page_links_to_detail(): void
    {
        [$user, $chaperone] = $this->gudepWithChaperone('Rahmat Hidayat');

        $this->actingAs($user)
            ->get(route('scout-group.chaperones'))
            ->assertOk()
            ->assertSee(route('scout-group.chaperones.show', $chaperone->id), false);
    }
}

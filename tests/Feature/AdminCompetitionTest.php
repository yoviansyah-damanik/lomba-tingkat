<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\Competition;
use App\Models\Event;
use App\Models\User;
use Database\Seeders\EventSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCompetitionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $judge;

    protected Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(EventSeeder::class);

        $this->event = Event::first();

        $this->admin = User::factory()->create([
            'is_active' => true,
        ]);
        $this->admin->assignRole(RoleEnum::Admin);

        $this->judge = User::factory()->create([
            'is_active' => true,
        ]);
        $this->judge->assignRole(RoleEnum::Judge);
    }

    /**
     * Non-admin users are forbidden from competition management
     */
    public function test_non_admin_cannot_access_competition_management(): void
    {
        $this->get(route('admin.competitions.index'))->assertRedirect(route('login'));

        $this->actingAs($this->judge)
            ->get(route('admin.competitions.index'))
            ->assertStatus(403);
    }

    /**
     * Admin can view competition index and see statistics
     */
    public function test_admin_can_view_competition_index(): void
    {
        Competition::create([
            'event_id' => $this->event->id,
            'title' => 'Pionering Menara Kaki Tiga',
            'code' => 'PION-01',
            'category' => 'all',
            'participant_type' => 'patrol',
            'min_participants' => 4,
            'max_participants' => 6,
            'scoring_formula' => 'average',
            'is_published' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.competitions.index'));

        $response->assertStatus(200);
        $response->assertSee('Pionering Menara Kaki Tiga');
        $response->assertSee('PION-01');
    }
}

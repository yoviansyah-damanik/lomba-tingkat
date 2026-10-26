<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\Competition;
use App\Models\CompetitionSchedule;
use App\Models\Event;
use App\Models\User;
use Database\Seeders\EventSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminScheduleTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $judgeUser;

    protected Competition $competition;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(EventSeeder::class);

        $event = Event::first();

        $this->competition = Competition::create([
            'event_id' => $event->id,
            'title' => 'Pionering Menara Pandang',
            'code' => 'PION-01',
            'category' => 'all',
            'participant_type' => 'patrol',
            'min_participants' => 3,
            'max_participants' => 5,
            'scoring_formula' => 'average',
            'is_published' => true,
        ]);

        $this->admin = User::factory()->create([
            'is_active' => true,
        ]);
        $this->admin->assignRole(RoleEnum::Admin);

        $this->judgeUser = User::factory()->create([
            'name' => 'Kak Pembina Juri Lapangan',
            'is_active' => true,
        ]);
        $this->judgeUser->assignRole(RoleEnum::Judge);
    }

    /**
     * Non-admin cannot access schedules management
     */
    public function test_non_admin_cannot_access_schedules_management(): void
    {
        $this->get(route('admin.schedules.index'))->assertRedirect(route('login'));

        $this->actingAs($this->judgeUser)
            ->get(route('admin.schedules.index'))
            ->assertStatus(403);
    }

    /**
     * Admin can view schedules index
     */
    public function test_admin_can_view_schedules_index(): void
    {
        CompetitionSchedule::create([
            'competition_id' => $this->competition->id,
            'session_name' => 'Sesi Babak Penyisihan Putra',
            'arena_location' => 'Lapangan Utama',
            'start_time' => now()->addDays(1),
            'end_time' => now()->addDays(1)->addHours(2),
            'status' => 'scheduled',
            'is_published' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.schedules.index'));

        $response->assertStatus(200);
        $response->assertSee('Sesi Babak Penyisihan Putra');
        $response->assertSee('Lapangan Utama');
    }
}

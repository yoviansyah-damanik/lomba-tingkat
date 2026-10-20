<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\AttendanceSession;
use App\Models\Competition;
use App\Models\CompetitionSchedule;
use App\Models\Event;
use App\Models\JudgeAssignment;
use App\Models\User;
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
        $this->seed(\Database\Seeders\EventSeeder::class);

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

    /**
     * Admin can create a new schedule which initializes attendance session
     */
    public function test_admin_can_create_new_schedule_and_auto_initializes_attendance(): void
    {
        $startTime = now()->addDays(2)->setTime(8, 0)->format('Y-m-d H:i');
        $endTime = now()->addDays(2)->setTime(11, 0)->format('Y-m-d H:i');

        $response = $this->actingAs($this->admin)->post(route('admin.schedules.store'), [
            'competition_id' => $this->competition->id,
            'session_name' => 'Sesi Penilaian Final',
            'arena_location' => 'Zona Barat Bumi Perkemahan',
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'scheduled',
            'is_published' => 1,
        ]);

        $this->assertDatabaseHas('competition_schedules', [
            'competition_id' => $this->competition->id,
            'session_name' => 'Sesi Penilaian Final',
            'arena_location' => 'Zona Barat Bumi Perkemahan',
        ]);

        $schedule = CompetitionSchedule::where('session_name', 'Sesi Penilaian Final')->firstOrFail();

        // Check auto initialized attendance session
        $this->assertDatabaseHas('attendance_sessions', [
            'competition_schedule_id' => $schedule->id,
            'session_type' => 'participant',
        ]);

        $response->assertRedirect(route('admin.schedules.edit', $schedule));
    }

    /**
     * Admin can assign judge and reassign role
     */
    public function test_admin_can_assign_and_update_judge_to_schedule(): void
    {
        $schedule = CompetitionSchedule::create([
            'competition_id' => $this->competition->id,
            'session_name' => 'Sesi Utama',
            'arena_location' => 'Arena Pos 1',
            'start_time' => now()->addDays(1),
            'end_time' => now()->addDays(1)->addHours(3),
            'status' => 'scheduled',
            'is_published' => true,
        ]);

        // Assign judge as lead_judge
        $assignResponse = $this->actingAs($this->admin)->post(route('admin.schedules.judges.assign', $schedule), [
            'user_id' => $this->judgeUser->id,
            'role' => 'lead_judge',
            'notes' => 'Koordinator Dewan Juri',
        ]);

        $assignResponse->assertSessionHas('status');
        $this->assertDatabaseHas('judge_assignments', [
            'competition_schedule_id' => $schedule->id,
            'user_id' => $this->judgeUser->id,
            'role' => 'lead_judge',
        ]);

        // Update role to scoring_judge
        $updateResponse = $this->actingAs($this->admin)->post(route('admin.schedules.judges.assign', $schedule), [
            'user_id' => $this->judgeUser->id,
            'role' => 'scoring_judge',
            'notes' => 'Juri Teknis',
        ]);

        $updateResponse->assertSessionHas('status');
        $this->assertDatabaseHas('judge_assignments', [
            'competition_schedule_id' => $schedule->id,
            'user_id' => $this->judgeUser->id,
            'role' => 'scoring_judge',
        ]);

        $assignment = JudgeAssignment::where('competition_schedule_id', $schedule->id)->firstOrFail();

        // Remove judge
        $removeResponse = $this->actingAs($this->admin)->delete(route('admin.schedules.judges.remove', [$schedule, $assignment]));
        $removeResponse->assertSessionHas('status');
        $this->assertDatabaseMissing('judge_assignments', [
            'id' => $assignment->id,
        ]);
    }

    /**
     * Admin can toggle attendance session active status
     */
    public function test_admin_can_toggle_pos_attendance_session(): void
    {
        $schedule = CompetitionSchedule::create([
            'competition_id' => $this->competition->id,
            'session_name' => 'Sesi Penjelajahan Alam',
            'arena_location' => 'Pos Registrasi Gerbang',
            'start_time' => now()->addDays(1),
            'end_time' => now()->addDays(1)->addHours(4),
            'status' => 'scheduled',
            'is_published' => true,
        ]);

        $session = $schedule->attendanceSessions()->first();
        $session->update(['is_active' => false]);

        // Toggle OPEN
        $toggleOpenResponse = $this->actingAs($this->admin)->post(route('admin.schedules.sessions.toggle', $schedule));
        $toggleOpenResponse->assertSessionHas('status');
        $this->assertTrue($session->fresh()->is_active);

        // Toggle CLOSE
        $toggleCloseResponse = $this->actingAs($this->admin)->post(route('admin.schedules.sessions.toggle', $schedule));
        $toggleCloseResponse->assertSessionHas('status');
        $this->assertFalse($session->fresh()->is_active);
    }

    /**
     * Admin can delete schedule
     */
    public function test_admin_can_delete_schedule(): void
    {
        $schedule = CompetitionSchedule::create([
            'competition_id' => $this->competition->id,
            'session_name' => 'Sesi yang dibatalkan',
            'arena_location' => 'Aula Terbuka',
            'start_time' => now()->addDays(1),
            'end_time' => now()->addDays(1)->addHours(2),
            'status' => 'cancelled',
            'is_published' => true,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.schedules.destroy', $schedule));
        $response->assertRedirect(route('admin.schedules.index'));
        $this->assertDatabaseMissing('competition_schedules', [
            'id' => $schedule->id,
        ]);
    }
}

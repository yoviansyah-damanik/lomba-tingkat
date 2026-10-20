<?php

namespace Tests\Feature;

use App\Jobs\SendScheduleReminderPush;
use App\Models\Competition;
use App\Models\CompetitionSchedule;
use App\Models\Event;
use Database\Seeders\EventSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ScheduleReminderPushTest extends TestCase
{
    use RefreshDatabase;

    protected Competition $competition;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EventSeeder::class);

        $this->competition = Competition::create([
            'event_id' => Event::first()->id,
            'title' => 'Pionering Menara Pandang',
            'code' => 'PION-01',
            'category' => 'all',
            'participant_type' => 'patrol',
            'min_participants' => 3,
            'max_participants' => 5,
            'scoring_formula' => 'average',
            'is_published' => true,
        ]);
    }

    protected function makeSchedule(int $minutesFromNow): CompetitionSchedule
    {
        return CompetitionSchedule::create([
            'competition_id' => $this->competition->id,
            'session_name' => 'Sesi 1',
            'arena_location' => 'Lapangan Utama',
            'start_time' => now()->addMinutes($minutesFromNow),
            'end_time' => now()->addMinutes($minutesFromNow + 60),
            'status' => 'scheduled',
            'is_published' => true,
        ]);
    }

    /**
     * Sesi dalam jendela 30 menit diantrekan sekali saja
     */
    public function test_upcoming_schedule_is_queued_once(): void
    {
        Queue::fake();
        $schedule = $this->makeSchedule(25);
        $this->makeSchedule(120);

        $this->artisan('schedules:notify-upcoming')->assertSuccessful();
        $this->artisan('schedules:notify-upcoming')->assertSuccessful();

        Queue::assertPushed(SendScheduleReminderPush::class, 1);
        Queue::assertPushed(SendScheduleReminderPush::class, fn ($job) => $job->scheduleId === $schedule->id);
        $this->assertNotNull($schedule->fresh()->reminder_sent_at);
    }

    /**
     * Mengubah waktu mulai mereset penanda sehingga pengingat dapat dikirim ulang
     */
    public function test_rescheduling_resets_reminder_flag(): void
    {
        $schedule = $this->makeSchedule(25);
        $schedule->forceFill(['reminder_sent_at' => now()])->saveQuietly();

        $schedule->refresh()->update(['start_time' => now()->addMinutes(20)]);

        $this->assertNull($schedule->fresh()->reminder_sent_at);
    }

    /**
     * Sesi dibatalkan tidak diantrekan
     */
    public function test_cancelled_schedule_is_skipped(): void
    {
        Queue::fake();
        $schedule = $this->makeSchedule(20);
        $schedule->update(['status' => 'cancelled']);

        $this->artisan('schedules:notify-upcoming')->assertSuccessful();

        Queue::assertNothingPushed();
    }
}

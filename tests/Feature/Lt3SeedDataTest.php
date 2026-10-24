<?php

namespace Tests\Feature;

use App\Models\AttendanceSession;
use App\Models\Competition;
use App\Models\CompetitionSchedule;
use App\Models\Event;
use Database\Seeders\AnnouncementSeeder;
use Database\Seeders\AssessmentCriteriaSeeder;
use Database\Seeders\CompetitionScheduleSeeder;
use Database\Seeders\CompetitionSeeder;
use Database\Seeders\EventSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Data seeder kegiatan sesuai Petunjuk Teknis LT-III Tahun 2026
 */
class Lt3SeedDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RoleSeeder::class,
            UserSeeder::class,
            EventSeeder::class,
            AnnouncementSeeder::class,
            CompetitionSeeder::class,
            CompetitionScheduleSeeder::class,
            AssessmentCriteriaSeeder::class,
        ]);
    }

    public function test_event_matches_juknis(): void
    {
        $event = Event::first();

        $this->assertStringContainsString('LT-III', $event->title);
        $this->assertSame('2026-11-14', $event->start_date->toDateString());
        $this->assertSame('2026-11-17', $event->end_date->toDateString());
        $this->assertStringContainsString('Pulo Baok', $event->location);
    }

    public function test_seventeen_competitions_with_complete_rubrics(): void
    {
        $competitions = Competition::with('criteria')->get();

        $this->assertCount(17, $competitions);

        foreach ($competitions as $competition) {
            $this->assertSame(100.0, (float) $competition->criteria->sum('weight_percentage'), "Bobot rubrik {$competition->code} harus 100%");
            $this->assertNotEmpty($competition->guidelines_text);
            $this->assertTrue($competition->is_published);
        }
    }

    public function test_participant_counts_follow_juknis(): void
    {
        $expected = ['PION-08' => 4, 'ADM-09' => 1, 'PACK-10' => 8, 'SEM-11' => 2, 'SKT-12' => 1, 'PTP-13' => 5, 'MNK-14' => 2, 'P3K-15' => 5,
            'MSK-16' => 2, 'BVK-17' => 8, 'BBT-18' => 8, 'SNM-19' => 8, 'PID-20' => 1, 'CCR-21' => 3, 'POS-22' => 1,
            'HSK-23' => 2, 'REP-24' => 2];

        foreach ($expected as $code => $count) {
            $competition = Competition::where('code', $code)->firstOrFail();
            $this->assertSame($count, $competition->min_participants, $code);
            $this->assertSame($count, $competition->max_participants, $code);
        }
    }

    public function test_each_competition_has_separate_boys_and_girls_sessions_within_event_days(): void
    {
        $this->assertSame(34, CompetitionSchedule::count());

        foreach (Competition::all() as $competition) {
            $this->assertEqualsCanonicalizing(['boys', 'girls'], $competition->schedules()->pluck('gender_category')->all(), $competition->code);
        }

        $this->assertSame(0, CompetitionSchedule::whereDate('start_time', '<', '2026-11-14')->orWhereDate('start_time', '>', '2026-11-17')->count());

        // Waktu yang ditetapkan Juknis
        $pionering = CompetitionSchedule::whereHas('competition', fn ($q) => $q->where('code', 'PION-08'))->first();
        $this->assertSame('2026-11-14 16:00', $pionering->start_time->format('Y-m-d H:i'));
        $this->assertSame('17:30', $pionering->end_time->format('H:i'));

        $packing = CompetitionSchedule::whereHas('competition', fn ($q) => $q->where('code', 'PACK-10'))->first();
        $this->assertSame('2026-11-14 10:00', $packing->start_time->format('Y-m-d H:i'));
        $this->assertSame('12:00', $packing->end_time->format('H:i'));
    }

    public function test_camp_activity_sessions_and_announcements_are_seeded_and_idempotent(): void
    {
        $this->assertTrue(AttendanceSession::where('title', 'like', 'Presensi Upacara Pembukaan%')->whereDate('opened_at', '2026-11-14')->exists());
        $this->assertTrue(AttendanceSession::where('session_type', 'chaperone')->exists());
        $this->assertGreaterThanOrEqual(5, \App\Models\Announcement::published()->count());

        $this->seed([EventSeeder::class, CompetitionSeeder::class, CompetitionScheduleSeeder::class, AssessmentCriteriaSeeder::class, AnnouncementSeeder::class]);

        $this->assertSame(1, Event::count());
        $this->assertSame(17, Competition::count());
        $this->assertSame(34, CompetitionSchedule::count());
    }
}

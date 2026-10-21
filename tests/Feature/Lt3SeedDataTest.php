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
        $this->assertSame('2026-11-27', $event->start_date->toDateString());
        $this->assertSame('2026-11-29', $event->end_date->toDateString());
        $this->assertStringContainsString('Pulo Baok', $event->location);
    }

    public function test_twenty_three_competitions_with_complete_rubrics(): void
    {
        $competitions = Competition::with('criteria')->get();

        $this->assertCount(23, $competitions);

        foreach ($competitions as $competition) {
            $this->assertSame(100.0, (float) $competition->criteria->sum('weight_percentage'), "Bobot rubrik {$competition->code} harus 100%");
            $this->assertNotEmpty($competition->guidelines_text);
            $this->assertTrue($competition->is_published);
        }
    }

    public function test_participant_counts_follow_juknis(): void
    {
        $expected = ['PION-08' => 6, 'ADM-09' => 2, 'PACK-10' => 1, 'SEM-11' => 7, 'SKT-12' => 2, 'PTP-13' => 8, 'MNK-14' => 8, 'P3K-15' => 8,
            'MSK-16' => 3, 'BVK-17' => 3, 'BBT-18' => 8, 'SNM-19' => 8, 'MMN-20' => 1, 'RNG-21' => 1, 'PID-22' => 1, 'OBT-23' => 2, 'POS-24' => 1,
            'HSK-25' => 4, 'REP-26' => 2, 'SNI-27' => 8, 'KUL-28' => 8, 'ROB-29' => 2, 'COD-30' => 2];

        foreach ($expected as $code => $count) {
            $competition = Competition::where('code', $code)->firstOrFail();
            $this->assertSame($count, $competition->min_participants, $code);
            $this->assertSame($count, $competition->max_participants, $code);
        }
    }

    public function test_each_competition_has_separate_boys_and_girls_sessions_within_event_days(): void
    {
        $this->assertSame(46, CompetitionSchedule::count());

        foreach (Competition::all() as $competition) {
            $this->assertEqualsCanonicalizing(['boys', 'girls'], $competition->schedules()->pluck('gender_category')->all(), $competition->code);
        }

        $this->assertSame(0, CompetitionSchedule::whereDate('start_time', '<', '2026-11-27')->orWhereDate('start_time', '>', '2026-11-29')->count());

        // Waktu yang ditetapkan Juknis
        $pionering = CompetitionSchedule::whereHas('competition', fn ($q) => $q->where('code', 'PION-08'))->first();
        $this->assertSame('2026-11-27 16:00', $pionering->start_time->format('Y-m-d H:i'));
        $this->assertSame('18:00', $pionering->end_time->format('H:i'));

        $packing = CompetitionSchedule::whereHas('competition', fn ($q) => $q->where('code', 'PACK-10'))->first();
        $this->assertSame('2026-11-28 08:00', $packing->start_time->format('Y-m-d H:i'));
        $this->assertSame('17:00', $packing->end_time->format('H:i'));
    }

    public function test_camp_activity_sessions_and_announcements_are_seeded_and_idempotent(): void
    {
        $this->assertTrue(AttendanceSession::where('title', 'like', 'Presensi Upacara Pembukaan%')->whereDate('opened_at', '2026-11-27')->exists());
        $this->assertTrue(AttendanceSession::where('session_type', 'chaperone')->exists());
        $this->assertGreaterThanOrEqual(5, \App\Models\Announcement::published()->count());

        $this->seed([EventSeeder::class, CompetitionSeeder::class, CompetitionScheduleSeeder::class, AssessmentCriteriaSeeder::class, AnnouncementSeeder::class]);

        $this->assertSame(1, Event::count());
        $this->assertSame(23, Competition::count());
        $this->assertSame(46, CompetitionSchedule::count());
    }
}

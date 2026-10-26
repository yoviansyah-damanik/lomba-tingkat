<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Livewire\Admin\SchedulesManagement;
use App\Models\Competition;
use App\Models\CompetitionSchedule;
use App\Models\JudgeAssignment;
use App\Models\User;
use App\Services\JudgeAutoAssignmentService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class JudgeAutoAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function judges(int $count)
    {
        return User::factory()->count($count)->create()->each(fn (User $u) => $u->assignRole(RoleEnum::Judge));
    }

    private function schedule(string $start, string $end): CompetitionSchedule
    {
        $number = Competition::count() + 1;
        $competition = Competition::create([
            'code' => "C-{$number}",
            'title' => "Lomba {$number}",
            'category' => 'all',
            'participant_type' => 'patrol',
            'scoring_formula' => 'sum',
            'is_published' => true,
        ]);

        return CompetitionSchedule::create([
            'competition_id' => $competition->id,
            'session_name' => 'Sesi '.$competition->id,
            'participant_type' => 'patrol',
            'gender_category' => 'boys',
            'arena_location' => 'Lapangan',
            'start_time' => $start,
            'end_time' => $end,
            'status' => 'scheduled',
            'is_published' => true,
        ]);
    }

    public function test_every_schedule_gets_a_full_panel_with_one_lead_and_balanced_load(): void
    {
        $this->judges(6);
        $a = $this->schedule('2026-11-14 08:00', '2026-11-14 10:00');
        $b = $this->schedule('2026-11-14 09:00', '2026-11-14 11:00');
        $c = $this->schedule('2026-11-14 13:00', '2026-11-14 15:00');

        $result = app(JudgeAutoAssignmentService::class)->assign();

        $this->assertSame(9, $result['assignments']);
        foreach ([$a, $b, $c] as $schedule) {
            $assignments = JudgeAssignment::where('competition_schedule_id', $schedule->id)->get();
            $this->assertCount(3, $assignments);
            $this->assertSame(1, $assignments->where('role', 'lead_judge')->count());
        }

        // Sesi bertumpuk memakai juri berbeda selama juri mencukupi
        $overlap = JudgeAssignment::whereIn('competition_schedule_id', [$a->id, $b->id])->pluck('user_id');
        $this->assertCount(6, $overlap->unique());

        // Idempoten: panel yang sudah lengkap tidak ditambah
        $this->assertSame(0, app(JudgeAutoAssignmentService::class)->assign()['assignments']);
    }

    public function test_existing_assignments_are_kept_and_only_the_gap_is_filled(): void
    {
        $judges = $this->judges(4);
        $schedule = $this->schedule('2026-11-14 08:00', '2026-11-14 10:00');
        JudgeAssignment::create(['competition_schedule_id' => $schedule->id, 'user_id' => $judges[0]->id, 'role' => 'lead_judge', 'is_confirmed' => true]);

        $result = app(JudgeAutoAssignmentService::class)->assign();

        $this->assertSame(2, $result['assignments']);
        $this->assertSame('lead_judge', JudgeAssignment::where('user_id', $judges[0]->id)->value('role'));
        $this->assertSame(1, JudgeAssignment::where('competition_schedule_id', $schedule->id)->where('role', 'lead_judge')->count());
    }

    public function test_admin_can_trigger_auto_assignment_from_schedule_page(): void
    {
        $this->judges(3);
        $schedule = $this->schedule('2026-11-14 08:00', '2026-11-14 10:00');
        $admin = User::factory()->create();
        $admin->assignRole(RoleEnum::Admin);

        Livewire::actingAs($admin)->test(SchedulesManagement::class)
            ->call('autoAssignJudges')
            ->assertSet('showAutoAssignModal', false);

        $this->assertSame(3, JudgeAssignment::where('competition_schedule_id', $schedule->id)->count());
    }
}

<?php

namespace Tests\Feature;

use App\Enums\PatrolGenderEnum;
use App\Enums\RegistrationStatusEnum;
use App\Livewire\Admin\NppGeneration;
use App\Models\Assessment;
use App\Models\Competition;
use App\Models\CompetitionSchedule;
use App\Models\Event;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use App\Models\User;
use App\Services\PatrolNumberService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NppGenerationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        Event::create([
            'title' => 'LT-III 2026',
            'start_date' => '2026-11-14',
            'end_date' => '2026-11-17',
            'location' => 'Pulo Baok',
            'registration_start_at' => now()->subWeeks(2),
            'registration_end_at' => now()->subDay(),
            'is_registration_open' => true,
        ]);
    }

    private function patrols(PatrolGenderEnum $gender, int $count, RegistrationStatusEnum $status = RegistrationStatusEnum::Verified): void
    {
        $owner = User::factory()->create();
        $group = ScoutGroup::create([
            'user_id' => $owner->id,
            'registration_code' => 'GDP-'.fake()->unique()->numerify('####'),
            'school_name' => 'SMP '.fake()->unique()->numerify('###'),
            'group_number' => '01-001 & 01-002',
            'address' => 'Jl. Contoh',
            'contact_person' => 'Pembina',
            'contact_phone' => '081234567890',
            'verification_status' => RegistrationStatusEnum::Verified,
        ]);

        for ($i = 1; $i <= $count; $i++) {
            Patrol::create(['scout_group_id' => $group->id, 'patrol_name' => "Regu {$gender->value} {$i}", 'gender_category' => $gender, 'status' => $status]);
        }
    }

    public function test_npp_is_numbered_separately_for_boys_and_girls_and_only_for_verified_patrols(): void
    {
        $this->patrols(PatrolGenderEnum::Boys, 3);
        $this->patrols(PatrolGenderEnum::Girls, 2);
        $this->patrols(PatrolGenderEnum::Boys, 1, RegistrationStatusEnum::Submitted);

        $result = app(PatrolNumberService::class)->generate();

        $this->assertSame(['boys' => 3, 'girls' => 2], $result);
        $this->assertEqualsCanonicalizing(['01', '02', '03'], Patrol::where('gender_category', 'boys')->whereNotNull('npp')->pluck('npp')->all());
        $this->assertEqualsCanonicalizing(['01', '02'], Patrol::where('gender_category', 'girls')->pluck('npp')->all());
        $this->assertSame(0, Patrol::where('status', RegistrationStatusEnum::Submitted)->whereNotNull('npp')->count());

        // Generate ulang tidak mengubah nomor yang ada; regu baru mendapat nomor lanjutan
        $before = Patrol::pluck('npp', 'id')->all();
        $this->patrols(PatrolGenderEnum::Girls, 1);
        app(PatrolNumberService::class)->generate();
        foreach ($before as $id => $npp) {
            $this->assertSame($npp, Patrol::find($id)->npp);
        }
        $this->assertTrue(Patrol::where('gender_category', 'girls')->where('npp', '03')->exists());
    }

    public function test_npp_input_is_normalized(): void
    {
        $this->assertSame('01', PatrolNumberService::normalize('1'));
        $this->assertSame('12', PatrolNumberService::normalize(' 12 '));
        $this->assertSame('01', PatrolNumberService::normalize('01'));
    }

    public function test_admin_can_generate_only_after_registration_closed(): void
    {
        $this->patrols(PatrolGenderEnum::Boys, 2);

        Event::first()->update(['registration_end_at' => now()->addWeek()]);
        Livewire::actingAs($this->admin)->test(NppGeneration::class)->call('generate');
        $this->assertSame(0, Patrol::whereNotNull('npp')->count());

        Event::first()->update(['registration_end_at' => now()->subMinute()]);
        Livewire::actingAs($this->admin)->test(NppGeneration::class)->call('generate')->assertDispatched('npp-updated');
        $this->assertSame(2, Patrol::whereNotNull('npp')->count());
        $this->assertDatabaseHas('audit_logs', ['action' => 'npp_generated', 'user_id' => $this->admin->id]);
    }

    public function test_reshuffle_is_blocked_once_assessments_exist(): void
    {
        $this->patrols(PatrolGenderEnum::Boys, 2);
        $service = app(PatrolNumberService::class);
        $service->generate();

        $service->generate(reshuffle: true);
        $this->assertSame(2, Patrol::whereNotNull('npp')->count());

        $patrol = Patrol::first();
        $competition = Competition::create(['code' => 'X-01', 'title' => 'Lomba X', 'category' => 'all', 'participant_type' => 'patrol', 'scoring_formula' => 'sum', 'is_published' => true]);
        $schedule = CompetitionSchedule::create(['competition_id' => $competition->id, 'session_name' => 'S', 'gender_category' => 'boys', 'arena_location' => 'A', 'start_time' => now(), 'end_time' => now()->addHour(), 'status' => 'scheduled', 'is_published' => true]);
        Assessment::create(['competition_id' => $competition->id, 'competition_schedule_id' => $schedule->id, 'patrol_id' => $patrol->id, 'judge_id' => $this->admin->id, 'criteria_scores' => [], 'raw_total_score' => 0, 'final_score' => 0, 'status' => 'draft']);

        $this->expectException(\RuntimeException::class);
        $service->generate(reshuffle: true);
    }

    public function test_non_admin_cannot_open_npp_page(): void
    {
        $judge = User::factory()->create();
        $judge->assignRole('judge');

        $this->actingAs($judge)->get(route('admin.npp.index'))->assertForbidden();
        $this->actingAs($this->admin)->get(route('admin.npp.index'))->assertOk()->assertSee('Generate NPP');
    }
}

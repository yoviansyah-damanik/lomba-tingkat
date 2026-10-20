<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\AssessmentCriterion;
use App\Models\Competition;
use App\Models\Event;
use App\Models\User;
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
        $this->seed(\Database\Seeders\EventSeeder::class);

        $this->event = Event::first();

        $this->admin = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $this->admin->assignRole(RoleEnum::Admin);

        $this->judge = User::factory()->create([
            'email_verified_at' => now(),
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

    /**
     * Admin can create a new competition
     */
    public function test_admin_can_create_new_competition(): void
    {
        $payload = [
            'title' => 'Lomba Pidato Kebangsaan',
            'code' => 'PID-01',
            'category' => 'all',
            'participant_type' => 'individual',
            'min_participants' => 1,
            'max_participants' => 1,
            'scoring_formula' => 'average',
            'description' => 'Orasi kepemudaan dan nasionalisme.',
            'guidelines_text' => 'Maksimal 7 menit.',
            'is_published' => 1,
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.competitions.store'), $payload);

        $this->assertDatabaseHas('competitions', [
            'code' => 'PID-01',
            'title' => 'Lomba Pidato Kebangsaan',
            'participant_type' => 'individual',
        ]);

        $competition = Competition::where('code', 'PID-01')->firstOrFail();
        $response->assertRedirect(route('admin.competitions.edit', $competition));
    }

    /**
     * Admin can update existing competition
     */
    public function test_admin_can_update_competition(): void
    {
        $competition = Competition::create([
            'event_id' => $this->event->id,
            'title' => 'Lomba Semboyan & Isyarat',
            'code' => 'SEM-01',
            'category' => 'all',
            'participant_type' => 'patrol',
            'min_participants' => 3,
            'max_participants' => 3,
            'scoring_formula' => 'average',
            'is_published' => true,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.competitions.update', $competition), [
            'title' => 'Lomba Semboyan & Isyarat Semaphore Morse',
            'code' => 'SEM-01',
            'category' => 'all',
            'participant_type' => 'patrol',
            'min_participants' => 3,
            'max_participants' => 3,
            'scoring_formula' => 'sum',
            'is_published' => 1,
        ]);

        $response->assertSessionHas('status');
        $this->assertDatabaseHas('competitions', [
            'id' => $competition->id,
            'title' => 'Lomba Semboyan & Isyarat Semaphore Morse',
            'scoring_formula' => 'sum',
        ]);
    }

    /**
     * Admin can add and delete rubric assessment criteria
     */
    public function test_admin_can_manage_assessment_criteria(): void
    {
        $competition = Competition::create([
            'event_id' => $this->event->id,
            'title' => 'PBB Koloni Tongkat',
            'code' => 'PBB-01',
            'category' => 'all',
            'participant_type' => 'patrol',
            'min_participants' => 8,
            'max_participants' => 8,
            'scoring_formula' => 'average',
            'is_published' => true,
        ]);

        // Add Criterion
        $response = $this->actingAs($this->admin)->post(route('admin.competitions.criteria.store', $competition), [
            'name' => 'Kerapian Atribut Seragam',
            'code' => 'PBB-KRP',
            'description' => 'Penilaian kelengkapan hasduk dan badge',
            'min_score' => 0,
            'max_score' => 100,
            'weight_percentage' => 25,
            'order_index' => 1,
        ]);

        $response->assertSessionHas('status');
        $this->assertDatabaseHas('assessment_criteria', [
            'competition_id' => $competition->id,
            'code' => 'PBB-KRP',
            'weight_percentage' => 25,
        ]);

        $criterion = AssessmentCriterion::where('code', 'PBB-KRP')->firstOrFail();

        // Delete Criterion
        $deleteResponse = $this->actingAs($this->admin)->delete(route('admin.competitions.criteria.destroy', $criterion));
        $deleteResponse->assertSessionHas('status');

        $this->assertDatabaseMissing('assessment_criteria', [
            'id' => $criterion->id,
        ]);
    }

    /**
     * Admin can delete a competition
     */
    public function test_admin_can_delete_competition(): void
    {
        $competition = Competition::create([
            'event_id' => $this->event->id,
            'title' => 'Lomba Masak Rimba',
            'code' => 'MSK-01',
            'category' => 'all',
            'participant_type' => 'patrol',
            'min_participants' => 2,
            'max_participants' => 2,
            'scoring_formula' => 'average',
            'is_published' => true,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.competitions.destroy', $competition));

        $response->assertRedirect(route('admin.competitions.index'));
        $this->assertDatabaseMissing('competitions', [
            'id' => $competition->id,
        ]);
    }
}

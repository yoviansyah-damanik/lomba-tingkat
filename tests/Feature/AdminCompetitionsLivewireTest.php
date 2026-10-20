<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Livewire\Admin\CompetitionsManagement;
use App\Models\Competition;
use App\Models\Event;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminCompetitionsLivewireTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(\Database\Seeders\EventSeeder::class);

        $this->event = Event::first();

        $this->admin = User::factory()->create([
            'is_active' => true,
        ]);
        $this->admin->assignRole(RoleEnum::Admin);
    }

    public function test_competitions_livewire_component_renders_correctly(): void
    {
        Competition::create([
            'event_id' => $this->event->id,
            'code' => 'PBB-01',
            'title' => 'Peraturan Baris Berbaris',
            'category' => 'ketangkasan',
            'participant_type' => 'patrol',
            'min_participants' => 8,
            'max_participants' => 8,
            'is_published' => true,
        ]);

        Livewire::actingAs($this->admin)
            ->test(CompetitionsManagement::class)
            ->assertStatus(200)
            ->assertSee('Peraturan Baris Berbaris')
            ->assertSee('PBB-01');
    }

    public function test_livewire_can_filter_competitions_by_format_type(): void
    {
        Competition::create([
            'event_id' => $this->event->id,
            'code' => 'REGU-01',
            'title' => 'Lomba Regu Utuh',
            'category' => 'keterampilan',
            'participant_type' => 'patrol',
            'min_participants' => 8,
            'max_participants' => 8,
            'is_published' => true,
        ]);

        Competition::create([
            'event_id' => $this->event->id,
            'code' => 'INDIVIDU-01',
            'title' => 'Lomba Pidato Individual',
            'category' => 'wawasan',
            'participant_type' => 'individual',
            'min_participants' => 1,
            'max_participants' => 1,
            'is_published' => true,
        ]);

        Livewire::actingAs($this->admin)
            ->test(CompetitionsManagement::class)
            ->set('participantTypeFilter', 'individual')
            ->assertSee('Lomba Pidato Individual')
            ->assertDontSee('Lomba Regu Utuh');
    }

    public function test_livewire_can_manage_assessment_criteria_inline(): void
    {
        $comp = Competition::create([
            'event_id' => $this->event->id,
            'code' => 'CRIT-01',
            'title' => 'Lomba Uji Kriteria',
            'category' => 'ketangkasan',
            'participant_type' => 'group',
            'min_participants' => 3,
            'max_participants' => 3,
            'is_published' => true,
        ]);

        Livewire::actingAs($this->admin)
            ->test(CompetitionsManagement::class)
            ->call('openCriteriaModal', $comp->id)
            ->set('criterionName', 'Kerapihan dan Keselarasan')
            ->set('criterionCode', 'KRP-01')
            ->set('minScore', 0)
            ->set('maxScore', 100)
            ->set('weightPercentage', 35)
            ->call('addCriterion')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('assessment_criteria', [
            'competition_id' => $comp->id,
            'name' => 'Kerapihan dan Keselarasan',
            'weight_percentage' => 35,
        ]);
    }
}

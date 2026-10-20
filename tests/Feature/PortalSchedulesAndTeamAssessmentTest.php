<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Livewire\Portal\TeamAssessment;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PortalSchedulesAndTeamAssessmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_chaperone_accounts_are_seeded_with_role_and_link(): void
    {
        $putra = User::where('username', 'bindamping')->first();
        $putri = User::where('username', 'bindamping.putri')->first();

        $this->assertTrue($putra->hasRole(RoleEnum::Chaperone));
        $this->assertSame('Bindamping', $putra->roleLabel());
        $this->assertSame('male', $putra->chaperone->gender);
        $this->assertSame('female', $putri->chaperone->gender);
        $this->assertNotNull(User::where('username', 'peserta')->first()->participant);
    }

    public function test_every_role_can_open_upcoming_schedules_page_and_guest_cannot(): void
    {
        $this->get(route('agenda.index'))->assertRedirect();

        foreach (['admin', 'panitia', 'juri', 'gudep01', 'peserta', 'bindamping'] as $local) {
            $user = User::where('username', $local)->first();
            $this->actingAs($user)->get(route('agenda.index'))->assertOk()->assertSee('Jadwal Kegiatan');
        }
    }

    public function test_upcoming_schedules_are_ordered_and_hide_past_sessions(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 24)->setTime(9, 0));

        $admin = User::where('username', 'admin')->first();
        $html = $this->actingAs($admin)->get(route('agenda.index'))->assertOk()->getContent();

        // Sesi 23 Okt sudah lewat, sesi 24 Okt 13:30 dan seterusnya tampil berurutan
        $this->assertStringNotContainsString('Jumat, 23 Oktober 2026', $html);
        $this->assertLessThan(strpos($html, 'Minggu, 25 Oktober 2026'), strpos($html, 'Sabtu, 24 Oktober 2026'));
    }

    public function test_participant_sees_own_patrol_assessment(): void
    {
        $user = User::where('username', 'peserta')->first();

        $this->actingAs($user)->get(route('team-assessment.index'))->assertOk()->assertSee('Penilaian Regu');

        Livewire::actingAs($user)->test(TeamAssessment::class)
            ->assertSee($user->participant->patrol->patrol_name)
            ->assertSee('Nilai per Mata Lomba');
    }

    public function test_chaperone_sees_patrol_matching_gender(): void
    {
        $putra = User::where('username', 'bindamping')->first();
        $putri = User::where('username', 'bindamping.putri')->first();

        Livewire::actingAs($putra)->test(TeamAssessment::class)->assertSee('Penggalang Putra');
        Livewire::actingAs($putri)->test(TeamAssessment::class)->assertSee('Penggalang Putri');
    }

    public function test_scout_group_sees_both_boys_and_girls_patrols(): void
    {
        $gudep = User::where('username', 'gudep01')->first();

        $this->actingAs($gudep)->get(route('team-assessment.index'))->assertOk();

        $component = Livewire::actingAs($gudep)->test(TeamAssessment::class)
            ->assertSee('Putra')
            ->assertSee('Putri');

        $girls = $gudep->scoutGroup->patrols()->where('gender_category', 'girls')->first();
        $component->call('$set', 'patrolId', $girls->id)->assertSee('Penggalang Putri');

        // Bindamping putra hanya melihat regu putra, bukan regu putri
        $putra = User::where('username', 'bindamping')->first();
        Livewire::actingAs($putra)->test(TeamAssessment::class)->assertDontSee($girls->patrol_name);
    }

    public function test_team_assessment_is_restricted_to_participant_chaperone_and_scout_group(): void
    {
        foreach (['admin', 'panitia', 'juri'] as $local) {
            $user = User::where('username', $local)->first();
            $this->actingAs($user)->get(route('team-assessment.index'))->assertForbidden();
        }

        foreach (['bindamping', 'peserta', 'gudep01'] as $local) {
            $this->actingAs(User::where('username', $local)->first())
                ->get(route('team-assessment.index'))->assertOk();
        }
    }
}

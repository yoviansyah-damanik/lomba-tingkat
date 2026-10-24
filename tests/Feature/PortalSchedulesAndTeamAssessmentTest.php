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

        foreach (['admin', 'panitia', 'juri', \Database\Seeders\ContingentSeeder::GUDEP_ID, 'peserta', 'bindamping'] as $local) {
            $user = User::where('username', $local)->first();
            $this->actingAs($user)->get(route('agenda.index'))->assertOk()->assertSee('Jadwal Kegiatan');
        }
    }

    public function test_schedules_are_ordered_and_keep_finished_sessions_visible(): void
    {
        // Sabtu 28 November 2026 pukul 12.30: sesi Jumat 27 November dan sesi pagi 28 November sudah lewat
        $this->travelTo(now()->setDate(2026, 11, 28)->setTime(12, 30));

        $admin = User::where('username', 'admin')->first();
        $html = $this->actingAs($admin)->get(route('agenda.index', ['tanggal' => 'all']))->assertOk()->getContent();

        // Sesi yang sudah lewat tetap tampil, berurutan dari yang paling awal
        $this->assertStringContainsString('Jumat, 27 November 2026', $html);
        $this->assertLessThan(strpos($html, 'Sabtu, 28 November 2026'), strpos($html, 'Jumat, 27 November 2026'));
        $this->assertLessThan(strpos($html, 'Minggu, 29 November 2026'), strpos($html, 'Sabtu, 28 November 2026'));
    }

    public function test_date_filter_defaults_to_today_when_it_is_a_schedule_day(): void
    {
        $admin = User::where('username', 'admin')->first();

        // Hari ini adalah hari lomba (Sabtu 28 November): filter tanggal otomatis aktif
        $this->travelTo(now()->setDate(2026, 11, 28)->setTime(9, 0));
        $html = $this->actingAs($admin)->get(route('agenda.index'))->assertOk()->getContent();
        $this->assertStringContainsString('Sabtu, 28 November 2026', $html);
        $this->assertStringNotContainsString('Jumat, 27 November 2026', $html);
        $this->assertStringNotContainsString('Minggu, 29 November 2026', $html);

        // Pilihan tanggal lain dan "semua tanggal" dapat dipilih
        Livewire::actingAs($admin)->test(\App\Livewire\Portal\UpcomingSchedules::class)
            ->assertSet('date', '2026-11-28')
            ->call('filterDate', '2026-11-27')
            ->assertSee('Jumat, 27 November 2026')
            ->assertDontSee('Sabtu, 28 November 2026')
            ->call('filterDate', 'all')
            ->assertSee('Jumat, 27 November 2026')
            ->assertSee('Minggu, 29 November 2026');

        // Bukan hari lomba: tampil semua tanggal
        $this->travelTo(now()->setDate(2026, 10, 1));
        Livewire::actingAs($admin)->test(\App\Livewire\Portal\UpcomingSchedules::class)->assertSet('date', 'all');
    }

    public function test_participant_sees_own_patrol_assessment(): void
    {
        $user = User::where('username', 'peserta')->first();

        $this->actingAs($user)->get(route('team-assessment.index'))->assertOk()->assertSee('Penilaian Regu');

        Livewire::actingAs($user)->test(TeamAssessment::class)
            ->assertSee($user->participant->patrol->patrol_name)
            ->assertSee('Nilai per Mata Lomba');
    }

    public function test_general_rank_is_censored_while_blind_mode_is_active(): void
    {
        $user = User::where('username', 'peserta')->first();
        $event = \App\Models\Event::first();

        $event->update(['hide_patrol_identities' => false]);
        Livewire::actingAs($user)->test(TeamAssessment::class)
            ->assertSee('Peringkat Umum')
            ->assertDontSee('Disensor selama blind mode aktif');

        $event->update(['hide_patrol_identities' => true]);
        Livewire::actingAs($user)->test(TeamAssessment::class)
            ->assertSee('Peringkat Umum')
            ->assertSee('#•••')
            ->assertSee('Disensor selama blind mode aktif');
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
        $gudep = User::where('username', \Database\Seeders\ContingentSeeder::GUDEP_ID)->first();

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

        foreach (['bindamping', 'peserta', \Database\Seeders\ContingentSeeder::GUDEP_ID] as $local) {
            $this->actingAs(User::where('username', $local)->first())
                ->get(route('team-assessment.index'))->assertOk();
        }
    }
}

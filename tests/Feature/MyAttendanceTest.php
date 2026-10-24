<?php

namespace Tests\Feature;

use App\Enums\PatrolGenderEnum;
use App\Enums\RoleEnum;
use App\Livewire\Portal\MyAttendanceList;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Chaperone;
use App\Models\Competition;
use App\Models\CompetitionSchedule;
use App\Models\Event;
use App\Models\JudgeAssignment;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use App\Models\User;
use Database\Seeders\EventSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MyAttendanceTest extends TestCase
{
    use RefreshDatabase;

    private Competition $competition;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(EventSeeder::class);

        $this->competition = Competition::create([
            'event_id' => Event::first()->id,
            'title' => 'Pionering Menara Pandang',
            'code' => 'PION-01',
            'category' => 'all',
            'participant_type' => 'patrol',
            'scoring_formula' => 'average',
            'is_published' => true,
        ]);
    }

    private function competitionSession(string $gender, string $name): AttendanceSession
    {
        $schedule = CompetitionSchedule::create([
            'competition_id' => $this->competition->id,
            'session_name' => $name,
            'gender_category' => $gender,
            'arena_location' => 'Lapangan Utama',
            'start_time' => now()->addHour(),
            'end_time' => now()->addHours(3),
            'status' => 'scheduled',
            'is_published' => true,
        ]);

        return $schedule->attendanceSessions()->firstOrFail();
    }

    private function generalSession(string $type, string $title): AttendanceSession
    {
        return AttendanceSession::create([
            'competition_schedule_id' => null,
            'is_competition' => false,
            'session_type' => $type,
            'title' => $title,
            'opened_at' => now()->subHour(),
            'closed_at' => now()->addHours(4),
            'is_active' => true,
        ]);
    }

    private function participantUser(PatrolGenderEnum $gender): array
    {
        $group = ScoutGroup::factory()->create();
        $patrol = Patrol::factory()->create(['scout_group_id' => $group->id, 'gender_category' => $gender]);
        $participant = Participant::factory()->create(['patrol_id' => $patrol->id]);

        $user = User::factory()->create(['participant_id' => $participant->id]);
        $user->assignRole(RoleEnum::Participant);

        return [$user, $participant, $patrol];
    }

    public function test_participant_only_sees_sessions_for_their_own_patrol_gender(): void
    {
        [$user, $participant] = $this->participantUser(PatrolGenderEnum::Boys);

        $this->competitionSession('boys', 'Sesi Khusus Putra');
        $this->competitionSession('girls', 'Sesi Khusus Putri');
        $general = $this->generalSession('participant', 'Upacara Pembukaan');
        $this->generalSession('committee', 'Rapat Koordinasi Panitia');
        $this->generalSession('judge', 'Briefing Juri');
        $this->generalSession('chaperone', 'Technical Meeting Bindamping');

        Attendance::create([
            'attendance_session_id' => $general->id,
            'entity_type' => 'participant',
            'participant_id' => $participant->id,
            'patrol_id' => $participant->patrol_id,
            'scanned_at' => now(),
            'status' => 'present',
        ]);

        Livewire::actingAs($user)->test(MyAttendanceList::class)
            ->set('filter', 'all')
            ->assertSee('Sesi Khusus Putra')
            ->assertSee('Upacara Pembukaan')
            ->assertSee('Hadir')
            ->assertDontSee('Sesi Khusus Putri')
            ->assertDontSee('Rapat Koordinasi Panitia')
            ->assertDontSee('Briefing Juri')
            ->assertDontSee('Technical Meeting Bindamping');
    }

    public function test_chaperone_sees_chaperone_sessions_and_patrol_sessions_of_their_gender_only(): void
    {
        $group = ScoutGroup::factory()->create();
        Patrol::factory()->create(['scout_group_id' => $group->id, 'gender_category' => PatrolGenderEnum::Girls]);
        $chaperone = Chaperone::factory()->create(['scout_group_id' => $group->id, 'gender' => 'female']);

        $user = User::factory()->create();
        $user->assignRole(RoleEnum::Chaperone);
        $chaperone->update(['user_id' => $user->id]);

        $this->competitionSession('girls', 'Sesi Khusus Putri');
        $this->competitionSession('boys', 'Sesi Khusus Putra');
        $this->generalSession('chaperone', 'Technical Meeting Bindamping');
        $this->generalSession('participant', 'Upacara Peserta');
        $this->generalSession('committee', 'Rapat Koordinasi Panitia');

        Livewire::actingAs($user)->test(MyAttendanceList::class)
            ->set('filter', 'all')
            ->assertSee('Technical Meeting Bindamping')
            ->assertSee('Sesi Khusus Putri')
            ->assertDontSee('Sesi Khusus Putra')
            ->assertDontSee('Upacara Peserta')
            ->assertDontSee('Rapat Koordinasi Panitia');
    }

    public function test_committee_only_sees_committee_sessions(): void
    {
        $user = User::factory()->create();
        $user->assignRole(RoleEnum::Committee);

        $this->generalSession('committee', 'Rapat Koordinasi Panitia');
        $this->generalSession('judge', 'Briefing Juri');
        $this->generalSession('participant', 'Upacara Peserta');
        $this->competitionSession('boys', 'Sesi Khusus Putra');

        Livewire::actingAs($user)->test(MyAttendanceList::class)
            ->set('filter', 'all')
            ->assertSee('Rapat Koordinasi Panitia')
            ->assertDontSee('Briefing Juri')
            ->assertDontSee('Upacara Peserta')
            ->assertDontSee('Sesi Khusus Putra');
    }

    public function test_judge_has_a_dedicated_view_of_assigned_competition_schedules(): void
    {
        $judge = User::factory()->create();
        $judge->assignRole(RoleEnum::Judge);

        $assigned = $this->competitionSession('boys', 'Sesi Ditugaskan');
        $this->competitionSession('boys', 'Sesi Tidak Ditugaskan');

        JudgeAssignment::create([
            'competition_schedule_id' => $assigned->competition_schedule_id,
            'user_id' => $judge->id,
            'role' => 'lead_judge',
            'is_confirmed' => true,
        ]);

        $this->generalSession('judge', 'Briefing Juri');
        $this->generalSession('committee', 'Rapat Koordinasi Panitia');

        $component = Livewire::actingAs($judge)->test(MyAttendanceList::class)->call('$refresh');

        // Tampilan khusus juri: hanya jadwal lomba yang menunjuknya
        $component->assertSee('Tugas Penjurian')
            ->assertSee('Sesi Ditugaskan')
            ->assertSee('Ketua Juri')
            ->assertDontSee('Sesi Tidak Ditugaskan');

        // Tab presensi pribadi: hanya sesi untuk juri
        $component->set('tab', 'presence')->set('filter', 'all')
            ->assertSee('Briefing Juri')
            ->assertDontSee('Rapat Koordinasi Panitia')
            ->assertDontSee('Sesi Ditugaskan');
    }

    public function test_page_is_available_only_for_participant_chaperone_committee_and_judge(): void
    {
        $this->get(route('my-attendance.index'))->assertRedirect(route('login'));

        $gudep = User::factory()->create();
        $gudep->assignRole(RoleEnum::ScoutGroup);
        $this->actingAs($gudep)->get(route('my-attendance.index'))->assertForbidden();

        [$participantUser] = $this->participantUser(PatrolGenderEnum::Boys);
        $this->actingAs($participantUser)->get(route('my-attendance.index'))
            ->assertOk()
            ->assertSee('Presensi Saya')
            ->assertDontSee('QR Presensi Saya')
            ->assertDontSee('QR Code presensi');

        $committee = User::factory()->create();
        $committee->assignRole(RoleEnum::Committee);
        $this->actingAs($committee)->get(route('my-attendance.index'))->assertOk();
    }
}

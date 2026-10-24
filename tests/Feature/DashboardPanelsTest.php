<?php

namespace Tests\Feature;

use App\Enums\RegistrationStatusEnum;
use App\Enums\RoleEnum;
use App\Livewire\Home\AdminAttentionPanel;
use App\Livewire\Home\AgendaPanel;
use App\Livewire\Home\AnnouncementsPanel;
use App\Livewire\Home\GroupProgressPanel;
use App\Livewire\Home\JudgeUpcomingPanel;
use App\Models\Announcement;
use App\Models\Competition;
use App\Models\CompetitionSchedule;
use App\Models\PermissionRequest;
use App\Models\ScoutGroup;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardPanelsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    private function account(RoleEnum $role): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role);

        return $user;
    }

    private function competition(): Competition
    {
        return Competition::firstOrCreate(['code' => 'PBB-01'], [
            'title' => 'PBB Uji',
            'category' => 'all',
            'participant_type' => 'patrol',
            'min_participants' => 8,
            'max_participants' => 8,
            'scoring_formula' => 'average',
            'is_published' => true,
        ]);
    }

    private function schedule(string $name, \DateTimeInterface $start, string $status = 'scheduled'): CompetitionSchedule
    {
        return CompetitionSchedule::create([
            'competition_id' => $this->competition()->id,
            'session_name' => $name,
            'participant_type' => 'patrol',
            'gender_category' => 'boys',
            'arena_location' => 'Lapangan Uji',
            'start_time' => $start,
            'end_time' => (clone $start)->modify('+1 hour'),
            'status' => $status,
            'is_published' => true,
        ]);
    }

    public function test_dashboard_loads_agenda_and_announcement_panels_for_every_role(): void
    {
        foreach ([RoleEnum::Admin, RoleEnum::Committee, RoleEnum::Judge, RoleEnum::ScoutGroup] as $role) {
            $this->actingAs($this->account($role))->get(route('dashboard'))
                ->assertOk()
                ->assertSeeLivewire(AgendaPanel::class)
                ->assertSeeLivewire(AnnouncementsPanel::class);
        }

        $this->actingAs($this->account(RoleEnum::Admin))->get(route('dashboard'))->assertSeeLivewire(AdminAttentionPanel::class);
        $this->actingAs($this->account(RoleEnum::ScoutGroup))->get(route('dashboard'))->assertSeeLivewire(GroupProgressPanel::class);
    }

    public function test_agenda_shows_today_sessions_or_falls_back_to_upcoming(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 28)->setTime(9, 0));
        $user = $this->account(RoleEnum::Committee);

        $this->schedule('Sesi Kemarin', now()->subDay()->setTime(10, 0), 'completed');
        $this->schedule('Sesi Hari Ini Pagi', now()->setTime(8, 0), 'completed');
        $this->schedule('Sesi Hari Ini Siang', now()->setTime(13, 0));
        $this->schedule('Sesi Lusa', now()->addDays(2)->setTime(8, 0));

        Livewire::withoutLazyLoading()->actingAs($user)->test(AgendaPanel::class)
            ->assertSee('Agenda Hari Ini')
            ->assertSee('Sesi Hari Ini Pagi')
            ->assertSee('Sesi Hari Ini Siang')
            ->assertDontSee('Sesi Kemarin')
            ->assertDontSee('Sesi Lusa');

        // Tanggal tanpa jadwal: tampil agenda terdekat
        $this->travelTo(now()->setDate(2026, 11, 29)->setTime(9, 0));

        Livewire::withoutLazyLoading()->actingAs($user)->test(AgendaPanel::class)
            ->assertSee('Agenda Terdekat')
            ->assertSee('Sesi Lusa')
            ->assertDontSee('Sesi Hari Ini Siang');
    }

    public function test_announcements_panel_lists_latest_published_only(): void
    {
        Announcement::create(['title' => 'Pengumuman Terbit', 'slug' => 'terbit', 'category' => 'general', 'summary' => 'Ringkas', 'content' => 'Isi', 'is_published' => true, 'published_at' => now()->subHour()]);
        Announcement::create(['title' => 'Pengumuman Draf', 'slug' => 'draf', 'category' => 'general', 'summary' => 'Ringkas', 'content' => 'Isi', 'is_published' => false, 'published_at' => null]);

        Livewire::withoutLazyLoading()->actingAs($this->account(RoleEnum::Judge))->test(AnnouncementsPanel::class)
            ->assertSee('Pengumuman Terbit')
            ->assertDontSee('Pengumuman Draf');
    }

    public function test_admin_attention_panel_lists_pending_verifications_and_outstanding_permissions(): void
    {
        ScoutGroup::factory()->create(['school_name' => 'SMP Menunggu Tinjau', 'verification_status' => RegistrationStatusEnum::Submitted]);
        ScoutGroup::factory()->create(['school_name' => 'SMP Masih Draf', 'verification_status' => RegistrationStatusEnum::Draft]);
        PermissionRequest::create(['type' => 'leave_temporary', 'reason' => 'Berobat sementara', 'status' => 'active']);

        Livewire::withoutLazyLoading()->actingAs($this->account(RoleEnum::Admin))->test(AdminAttentionPanel::class)
            ->assertSee('Perlu Ditindaklanjuti')
            ->assertSee('SMP Menunggu Tinjau')
            ->assertDontSee('SMP Masih Draf')
            ->assertSee('Izin sementara belum kembali');
    }

    public function test_group_progress_panel_shows_checklist_for_scout_group(): void
    {
        $user = $this->account(RoleEnum::ScoutGroup);
        ScoutGroup::factory()->create(['user_id' => $user->id, 'mandate_letter_path' => null, 'payment_proof_path' => null]);

        Livewire::withoutLazyLoading()->actingAs($user)->test(GroupProgressPanel::class)
            ->assertSee('Kemajuan Pendaftaran')
            ->assertSee('Regu terbentuk')
            ->assertSee('Setiap regu 8 peserta dengan Pinru & Wapinru')
            ->assertSee('Berkas administrasi')
            ->assertSee('Surat tugas belum');
    }

    public function test_judge_upcoming_panel_lists_only_assigned_unfinished_sessions(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 28)->setTime(9, 0));
        $judge = $this->account(RoleEnum::Judge);
        $other = $this->account(RoleEnum::Judge);

        $mine = $this->schedule('Sesi Saya Mendatang', now()->setTime(13, 0));
        $past = $this->schedule('Sesi Saya Lewat', now()->subDay()->setTime(8, 0), 'completed');
        $notMine = $this->schedule('Sesi Juri Lain', now()->setTime(14, 0));
        $mine->judgeAssignments()->create(['user_id' => $judge->id, 'role' => 'scoring_judge', 'is_confirmed' => true]);
        $past->judgeAssignments()->create(['user_id' => $judge->id, 'role' => 'scoring_judge', 'is_confirmed' => true]);
        $notMine->judgeAssignments()->create(['user_id' => $other->id, 'role' => 'scoring_judge', 'is_confirmed' => true]);

        Livewire::withoutLazyLoading()->actingAs($judge)->test(JudgeUpcomingPanel::class)
            ->assertSee('Lomba yang Akan Datang')
            ->assertSee('PBB Uji')
            ->assertDontSee('Sesi Juri Lain');

        $this->actingAs($judge)->get(route('dashboard'))->assertSeeLivewire(JudgeUpcomingPanel::class);
        $this->actingAs($this->account(RoleEnum::Committee))->get(route('dashboard'))->assertDontSeeLivewire(JudgeUpcomingPanel::class);
    }

    public function test_stat_cards_are_complete_for_each_role(): void
    {
        $expectations = [
            [RoleEnum::Admin, ['Gudep Terdaftar', 'Menunggu Verifikasi', 'Terverifikasi', 'Perlu Revisi / Ditolak', 'Regu Terdaftar', 'Peserta Penggalang', 'Pembina Pendamping', 'Izin Belum Kembali', 'Sesi Lomba', 'Mata Lomba', 'Presensi Hari Ini', 'Aparatur']],
            [RoleEnum::Committee, ['Sesi Presensi Aktif', 'Presensi Hari Ini', 'Sesi Lomba Hari Ini', 'Regu Sah', 'Peserta Terverifikasi', 'Pembina Terverifikasi']],
            [RoleEnum::Judge, ['Sesi Ditugaskan', 'Lomba Akan Datang', 'Sesi Hari Ini', 'Nilai Final Saya', 'Rubrik Juknis Lomba', 'Presensi Pos Aktif']],
        ];

        foreach ($expectations as [$role, $labels]) {
            $response = $this->actingAs($this->account($role))->get(route('dashboard'))->assertOk()->assertDontSee('Nilai Final Juri');
            foreach ($labels as $label) {
                $response->assertSee($label);
            }
        }

        $group = $this->account(RoleEnum::ScoutGroup);
        ScoutGroup::factory()->create(['user_id' => $group->id]);
        $response = $this->actingAs($group)->get(route('dashboard'))->assertOk();
        foreach (['Status Berkas', 'Regu Terdaftar', 'Regu Lengkap', 'Anggota Peserta', 'Berkas Peserta Lengkap', 'Bindamping', 'Nilai Lomba', 'Sesi Berikutnya'] as $label) {
            $response->assertSee($label);
        }
    }

    public function test_admin_cards_split_participants_and_chaperones_by_gender_and_count_officials(): void
    {
        $boys = \App\Models\Patrol::factory()->create(['gender_category' => 'boys']);
        $girls = \App\Models\Patrol::factory()->create(['gender_category' => 'girls']);
        \App\Models\Participant::factory()->count(3)->create(['patrol_id' => $boys->id]);
        \App\Models\Participant::factory()->count(2)->create(['patrol_id' => $girls->id]);
        \App\Models\Chaperone::factory()->count(2)->create(['gender' => 'male']);
        \App\Models\Chaperone::factory()->create(['gender' => 'female']);
        $this->account(RoleEnum::Committee);
        $this->account(RoleEnum::Judge);
        $this->account(RoleEnum::Judge);

        $this->actingAs($this->account(RoleEnum::Admin))->get(route('dashboard'))
            ->assertOk()
            ->assertSee('3 putra / 2 putri')
            ->assertSee('2 putra / 1 putri')
            ->assertSee('1 panitia / 2 juri');
    }

    public function test_general_rank_cards_for_participant_and_scout_group_are_censored_in_blind_mode(): void
    {
        $group = ScoutGroup::factory()->create(['verification_status' => RegistrationStatusEnum::Verified]);
        $patrol = \App\Models\Patrol::factory()->create(['scout_group_id' => $group->id, 'gender_category' => 'boys', 'patrol_name' => 'Elang Uji']);
        $participant = \App\Models\Participant::factory()->create(['patrol_id' => $patrol->id]);
        $participantUser = $this->account(RoleEnum::Participant);
        $participantUser->update(['participant_id' => $participant->id]);
        $groupUser = $this->account(RoleEnum::ScoutGroup);
        $group->update(['user_id' => $groupUser->id]);
        $this->seed(\Database\Seeders\EventSeeder::class);
        $event = \App\Models\Event::first();

        $event->update(['hide_patrol_identities' => false]);
        foreach ([$participantUser->fresh(), $groupUser] as $user) {
            $this->actingAs($user)->get(route('dashboard'))->assertOk()
                ->assertSee('Peringkat Umum')
                ->assertSee('Elang Uji')
                ->assertDontSee('Disensor selama blind mode aktif');
        }

        $event->update(['hide_patrol_identities' => true]);
        foreach ([$participantUser->fresh(), $groupUser] as $user) {
            $this->actingAs($user)->get(route('dashboard'))->assertOk()
                ->assertSee('Peringkat Umum')
                ->assertSee('#•••')
                ->assertSee('Disensor selama blind mode aktif');
        }
    }

    public function test_achievement_count_card_for_scout_group_participant_and_chaperone(): void
    {
        $group = ScoutGroup::factory()->create(['verification_status' => RegistrationStatusEnum::Verified]);
        $patrol = \App\Models\Patrol::factory()->create(['scout_group_id' => $group->id, 'gender_category' => 'boys']);
        $participant = \App\Models\Participant::factory()->create(['patrol_id' => $patrol->id]);
        $chaperone = \App\Models\Chaperone::factory()->create(['scout_group_id' => $group->id, 'gender' => 'male']);

        $participantUser = $this->account(RoleEnum::Participant);
        $participantUser->update(['participant_id' => $participant->id]);
        $chaperoneUser = $this->account(RoleEnum::Chaperone);
        $chaperone->update(['user_id' => $chaperoneUser->id]);
        $groupUser = $this->account(RoleEnum::ScoutGroup);
        $group->update(['user_id' => $groupUser->id]);

        // 2 Tinggi, 1 Baik, 1 Cukup (dikunci), serta 1 Tinggi yang belum dikunci dan 1 tanpa prestasi (tidak dihitung)
        $competition = $this->competition();
        $other = \App\Models\Competition::create(['code' => 'PIO-02', 'title' => 'Pionering Uji', 'category' => 'all', 'participant_type' => 'patrol', 'min_participants' => 8, 'max_participants' => 8, 'scoring_formula' => 'average', 'is_published' => true]);
        $make = fn ($predicate, $locked, $comp) => \App\Models\CompetitionScore::create(['competition_id' => $comp->id, 'patrol_id' => $patrol->id, 'final_score' => 80, 'predicate' => $predicate, 'is_locked' => $locked]);
        $make('prestasi_tinggi', true, $competition);
        $make('prestasi_baik', true, $other);
        foreach (['TKK-03' => ['prestasi_tinggi', true], 'TKK-04' => ['prestasi_cukup', true], 'TKK-05' => ['prestasi_tinggi', false], 'TKK-06' => ['partisipasi', true]] as $code => [$predicate, $locked]) {
            $comp = \App\Models\Competition::create(['code' => $code, 'title' => $code, 'category' => 'all', 'participant_type' => 'patrol', 'min_participants' => 8, 'max_participants' => 8, 'scoring_formula' => 'average', 'is_published' => true]);
            $make($predicate, $locked, $comp);
        }

        foreach ([$groupUser, $participantUser->fresh(), $chaperoneUser->fresh()] as $user) {
            $this->actingAs($user)->get(route('dashboard'))->assertOk()
                ->assertSee('Prestasi Diperoleh')
                ->assertSee('Tinggi 2 · Baik 1 · Cukup 1', false);
        }
    }
}
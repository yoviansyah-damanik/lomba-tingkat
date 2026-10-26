<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Livewire\Admin\SchedulesManagement;
use App\Models\Competition;
use App\Models\CompetitionSchedule;
use App\Models\Event;
use App\Models\User;
use App\Services\WebPushService;
use Database\Seeders\EventSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminSchedulesLivewireTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $judge;

    protected Event $event;

    protected Competition $competition;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(EventSeeder::class);

        $this->event = Event::first();

        $this->admin = User::factory()->create([
            'is_active' => true,
        ]);
        $this->admin->assignRole(RoleEnum::Admin);

        $this->judge = User::factory()->create([
            'name' => 'Kak Pembina Juri Livewire',
            'is_active' => true,
        ]);
        $this->judge->assignRole(RoleEnum::Judge);

        $this->competition = Competition::create([
            'event_id' => $this->event->id,
            'title' => 'Sandi Morse dan Semaphore',
            'code' => 'SMS-01',
            'category' => 'keterampilan',
            'participant_type' => 'patrol',
            'min_participants' => 8,
            'max_participants' => 8,
            'is_published' => true,
        ]);
    }

    public function test_schedules_livewire_component_renders_correctly(): void
    {
        CompetitionSchedule::create([
            'competition_id' => $this->competition->id,
            'session_name' => 'Sesi Uji Sandi Regu Putra',
            'arena_location' => 'Pos Semaphore Lapangan 1',
            'start_time' => now()->addDays(1),
            'end_time' => now()->addDays(1)->addHours(2),
            'status' => 'scheduled',
            'is_published' => true,
        ]);

        Livewire::actingAs($this->admin)
            ->test(SchedulesManagement::class)
            ->assertStatus(200)
            ->assertSee('Sesi Uji Sandi Regu Putra')
            ->assertSee('Pos Semaphore Lapangan 1');
    }

    public function test_livewire_can_toggle_attendance_pos_session(): void
    {
        $schedule = CompetitionSchedule::create([
            'competition_id' => $this->competition->id,
            'session_name' => 'Sesi Semaphore Cepat',
            'arena_location' => 'Pos 2',
            'start_time' => now()->addDays(1),
            'end_time' => now()->addDays(1)->addHours(2),
            'status' => 'scheduled',
            'is_published' => true,
        ]);

        $session = $schedule->attendanceSessions()->create([
            'session_type' => 'participant',
            'title' => 'Presensi Semaphore',
            'is_active' => false,
        ]);

        Livewire::actingAs($this->admin)
            ->test(SchedulesManagement::class)
            ->call('toggleAttendanceSession', $schedule->id)
            ->assertHasNoErrors();

        $this->assertTrue($session->fresh()->is_active);
    }

    public function test_livewire_can_assign_judge_to_schedule(): void
    {
        $schedule = CompetitionSchedule::create([
            'competition_id' => $this->competition->id,
            'session_name' => 'Sesi Penjurian Utama',
            'arena_location' => 'Arena Utama',
            'start_time' => now()->addDays(1),
            'end_time' => now()->addDays(1)->addHours(2),
            'status' => 'scheduled',
            'is_published' => true,
        ]);

        Livewire::actingAs($this->admin)
            ->test(SchedulesManagement::class)
            ->call('openJudgesModal', $schedule->id)
            ->set('selectedJudgeId', $this->judge->id)
            ->set('judgeRole', 'lead_judge')
            ->set('judgeNotes', 'Ketua Juri Morse')
            ->call('assignJudge')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('judge_assignments', [
            'competition_schedule_id' => $schedule->id,
            'user_id' => $this->judge->id,
            'role' => 'lead_judge',
            'notes' => 'Ketua Juri Morse',
        ]);
    }

    public function test_schedules_column_displays_auto_open_status_and_allows_manual_close_during_competition(): void
    {
        $liveSchedule = CompetitionSchedule::create([
            'competition_id' => $this->competition->id,
            'session_name' => 'Sesi Lomba Berlangsung',
            'arena_location' => 'Arena Lapangan 1',
            'start_time' => now()->addMinutes(10),
            'end_time' => now()->addHours(2),
            'status' => 'in_progress',
            'is_published' => true,
        ]);

        $futureSchedule = CompetitionSchedule::create([
            'competition_id' => $this->competition->id,
            'session_name' => 'Sesi Lomba Besok',
            'arena_location' => 'Arena Lapangan 2',
            'start_time' => now()->addDays(2),
            'end_time' => now()->addDays(2)->addHours(2),
            'status' => 'scheduled',
            'is_published' => true,
        ]);

        $pastSchedule = CompetitionSchedule::create([
            'competition_id' => $this->competition->id,
            'session_name' => 'Sesi Lomba Selesai',
            'arena_location' => 'Arena Lapangan 3',
            'start_time' => now()->subHours(4),
            'end_time' => now()->subHours(2),
            'status' => 'completed',
            'is_published' => true,
        ]);

        $component = Livewire::actingAs($this->admin)
            ->test(SchedulesManagement::class);

        $component->assertSee('Aktif Scan (Otomatis Terbuka)')
            ->assertSee('Otomatis Buka H-30m')
            ->assertSee('Presensi Ditutup (Selesai)')
            ->assertSee('Tutup Sesi');

        $component->call('toggleAttendanceSession', $liveSchedule->id);

        $component->assertSee('Ditutup Manual Saat Lomba')
            ->assertSee('Buka Kembali Sesi');
    }

    public function test_livewire_can_create_schedule_with_gender_category(): void
    {
        Livewire::actingAs($this->admin)
            ->test(SchedulesManagement::class)
            ->call('openCreateModal')
            ->set('competition_id', $this->competition->id)
            ->set('session_name', 'Sesi Pionering Regu Putri')
            ->set('arena_location', 'Arena Pos Rimba Putri')
            ->set('start_time', now()->addDay()->format('Y-m-d\TH:i'))
            ->set('end_time', now()->addDay()->addHours(2)->format('Y-m-d\TH:i'))
            ->set('gender_category', 'girls')
            ->set('status', 'scheduled')
            ->call('saveSchedule')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('competition_schedules', [
            'competition_id' => $this->competition->id,
            'session_name' => 'Sesi Pionering Regu Putri',
            'gender_category' => 'girls',
        ]);
    }

    public function test_livewire_can_filter_schedules_by_gender_category(): void
    {
        CompetitionSchedule::create([
            'competition_id' => $this->competition->id,
            'session_name' => 'Sesi Khusus Putra Saja',
            'gender_category' => 'boys',
            'arena_location' => 'Arena Pos Putra',
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHours(2),
            'status' => 'scheduled',
            'is_published' => true,
        ]);

        CompetitionSchedule::create([
            'competition_id' => $this->competition->id,
            'session_name' => 'Sesi Khusus Putri Saja',
            'gender_category' => 'girls',
            'arena_location' => 'Arena Pos Putri',
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHours(2),
            'status' => 'scheduled',
            'is_published' => true,
        ]);

        Livewire::actingAs($this->admin)
            ->test(SchedulesManagement::class)
            ->set('genderFilter', 'boys')
            ->assertSee('Sesi Khusus Putra Saja')
            ->assertDontSee('Sesi Khusus Putri Saja')
            ->set('genderFilter', 'girls')
            ->assertSee('Sesi Khusus Putri Saja')
            ->assertDontSee('Sesi Khusus Putra Saja');
    }

    public function test_admin_can_send_schedule_reminder_push(): void
    {
        $schedule = CompetitionSchedule::create([
            'competition_id' => $this->competition->id,
            'session_name' => 'Sesi Pengingat Uji',
            'arena_location' => 'Pos Sandi',
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHours(2),
            'status' => 'scheduled',
            'is_published' => true,
        ]);

        $this->mock(WebPushService::class, function ($mock) {
            $mock->shouldReceive('broadcastNotification')
                ->once()
                ->withArgs(fn ($payload) => $payload['title'] === 'Pengingat Jadwal Lomba')
                ->andReturn(['total' => 1, 'sent' => 1, 'failed' => 0, 'expired' => 0, 'target_role' => null]);
        });

        Livewire::actingAs($this->admin)
            ->test(SchedulesManagement::class)
            ->assertSee('Kirim Notifikasi')
            ->call('sendReminder', $schedule->id)
            ->assertHasNoErrors();
    }

    /**
     * Sesi baru otomatis menyiapkan sesi presensi pos, dan jadwal dapat dihapus
     */
    public function test_livewire_schedule_initializes_attendance_session_and_can_be_deleted(): void
    {
        Livewire::actingAs($this->admin)
            ->test(SchedulesManagement::class)
            ->call('openCreateModal')
            ->set('competition_id', $this->competition->id)
            ->set('session_name', 'Sesi Penilaian Final')
            ->set('arena_location', 'Zona Barat Bumi Perkemahan')
            ->set('start_time', now()->addDays(2)->setTime(8, 0)->format('Y-m-d\TH:i'))
            ->set('end_time', now()->addDays(2)->setTime(11, 0)->format('Y-m-d\TH:i'))
            ->set('status', 'scheduled')
            ->call('saveSchedule')
            ->assertHasNoErrors();

        $schedule = CompetitionSchedule::where('session_name', 'Sesi Penilaian Final')->firstOrFail();
        $this->assertDatabaseHas('attendance_sessions', ['competition_schedule_id' => $schedule->id, 'session_type' => 'participant']);

        Livewire::actingAs($this->admin)->test(SchedulesManagement::class)->call('deleteSchedule', $schedule->id);
        $this->assertDatabaseMissing('competition_schedules', ['id' => $schedule->id]);
    }
}

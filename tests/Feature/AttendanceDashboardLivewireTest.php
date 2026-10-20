<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Livewire\Portal\AttendanceDashboard;
use App\Models\AttendanceSession;
use App\Models\Competition;
use App\Models\CompetitionSchedule;
use App\Models\Event;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AttendanceDashboardLivewireTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $committee;
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

        $this->committee = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $this->committee->assignRole(RoleEnum::Committee);
    }

    public function test_attendance_dashboard_renders_for_admin_and_committee(): void
    {
        $comp = Competition::create([
            'event_id' => $this->event->id,
            'title' => 'Menaksir Tinggi dan Lebar',
            'code' => 'TAKSIR-01',
            'category' => 'keterampilan',
            'participant_type' => 'group',
            'min_participants' => 3,
            'max_participants' => 3,
            'is_published' => true,
        ]);

        $schedule = CompetitionSchedule::create([
            'competition_id' => $comp->id,
            'session_name' => 'Sesi Menaksir Pos Lembah',
            'arena_location' => 'Pos Lembah',
            'start_time' => now()->addDays(1),
            'end_time' => now()->addDays(1)->addHours(2),
            'status' => 'scheduled',
            'is_published' => true,
        ]);

        $compSession = $schedule->attendanceSessions()->create([
            'session_type' => 'participant',
            'title' => 'Presensi Menaksir Lembah',
            'is_competition' => true,
            'is_active' => true,
        ]);

        $generalSession = AttendanceSession::create([
            'competition_schedule_id' => null,
            'session_type' => 'participant',
            'title' => 'Presensi Kehadiran Apel Pagi',
            'is_competition' => false,
            'is_active' => true,
        ]);

        // Panitia hanya melihat sesi kehadiran umum, bukan sesi lomba
        Livewire::actingAs($this->committee)
            ->test(AttendanceDashboard::class)
            ->assertStatus(200)
            ->assertSee('Portal Presensi Pos Arena')
            ->assertSee('Presensi Kehadiran Apel Pagi')
            ->assertDontSee('Presensi Menaksir Lembah');

        // Admin dapat melihat semua sesi presensi
        Livewire::actingAs($this->admin)
            ->test(AttendanceDashboard::class)
            ->assertStatus(200)
            ->assertSee('Presensi Kehadiran Apel Pagi')
            ->assertSee('Presensi Menaksir Lembah');
    }

    public function test_livewire_can_toggle_session_status(): void
    {
        $comp = Competition::create([
            'event_id' => $this->event->id,
            'title' => 'Uji Pioneering Cepat',
            'code' => 'PION-99',
            'category' => 'ketangkasan',
            'participant_type' => 'patrol',
            'min_participants' => 8,
            'max_participants' => 8,
            'is_published' => true,
        ]);

        $schedule = CompetitionSchedule::create([
            'competition_id' => $comp->id,
            'session_name' => 'Sesi Uji Coba',
            'arena_location' => 'Pos Uji',
            'start_time' => now()->addDays(1),
            'end_time' => now()->addDays(1)->addHours(2),
            'status' => 'scheduled',
            'is_published' => true,
        ]);

        $session = $schedule->attendanceSessions()->create([
            'session_type' => 'participant',
            'title' => 'Presensi Sesi Uji Coba',
            'is_active' => false,
        ]);

        Livewire::actingAs($this->admin)
            ->test(AttendanceDashboard::class)
            ->call('toggleSession', $session->id)
            ->assertHasNoErrors();

        $this->assertTrue($session->fresh()->is_active);

        Livewire::actingAs($this->admin)
            ->test(AttendanceDashboard::class)
            ->call('toggleSession', $session->id)
            ->assertHasNoErrors();

        $this->assertFalse($session->fresh()->is_active);
    }

    /**
     * Memastikan administrator dapat membuat sesi presensi kehadiran baru lewat modal
     */
    public function test_admin_can_create_attendance_session_via_modal(): void
    {
        Livewire::actingAs($this->admin)
            ->test(AttendanceDashboard::class)
            ->set('showCreateModal', true)
            ->set('newTitle', 'Presensi Apel Pembukaan & Upacara Adat')
            ->set('newSessionType', 'participant')
            ->set('newIsActive', true)
            ->call('createAttendanceSession')
            ->assertHasNoErrors()
            ->assertSet('showCreateModal', false);

        $this->assertDatabaseHas('attendance_sessions', [
            'title' => 'Presensi Apel Pembukaan & Upacara Adat',
            'session_type' => 'participant',
            'is_competition' => false,
            'is_active' => true,
        ]);
    }

    /**
     * Memastikan panitia atau non-admin tidak dapat membuat sesi presensi kehadiran baru
     */
    public function test_non_admin_cannot_create_attendance_session(): void
    {
        Livewire::actingAs($this->committee)
            ->test(AttendanceDashboard::class)
            ->set('newTitle', 'Sesi Ilegal Panitia')
            ->set('newSessionType', 'participant')
            ->call('createAttendanceSession')
            ->assertStatus(403);

        $this->assertDatabaseMissing('attendance_sessions', [
            'title' => 'Sesi Ilegal Panitia',
        ]);
    }

    /**
     * Memastikan administrator dapat membuka modal dan mengedit sesi presensi beserta jadwal terkait
     */
    public function test_admin_can_edit_attendance_session_and_schedule(): void
    {
        $comp = Competition::create([
            'event_id' => $this->event->id,
            'title' => 'Semaphore & Morse',
            'code' => 'SM-01',
            'category' => 'all',
            'participant_type' => 'patrol',
            'is_published' => true,
        ]);

        $schedule = CompetitionSchedule::create([
            'competition_id' => $comp->id,
            'session_name' => 'Sesi Jadwal Awal',
            'gender_category' => 'all',
            'arena_location' => 'Pos Lapangan 1',
            'start_time' => now()->addDays(1)->setTime(8, 0),
            'end_time' => now()->addDays(1)->setTime(10, 0),
            'status' => 'scheduled',
            'is_published' => true,
        ]);

        $session = $schedule->attendanceSessions()->first();

        Livewire::actingAs($this->admin)
            ->test(AttendanceDashboard::class)
            ->call('openEditModal', $session->id)
            ->assertSet('showEditModal', true)
            ->assertSet('editTitle', $session->title)
            ->assertSet('editScheduleName', 'Sesi Jadwal Awal')
            // Ubah judul sesi dan koreksi kesalahan waktu & gender jadwal
            ->set('editTitle', 'Presensi Sesi Morse Putra Terkoreksi')
            ->set('editScheduleName', 'Sesi Morse Putra')
            ->set('editScheduleArena', 'Pos Lapangan Utama')
            ->set('editScheduleGenderCategory', 'boys')
            ->set('editScheduleStartTime', now()->addDays(1)->setTime(9, 30)->format('Y-m-d\TH:i'))
            ->set('editScheduleEndTime', now()->addDays(1)->setTime(11, 30)->format('Y-m-d\TH:i'))
            ->call('updateAttendanceSession')
            ->assertHasNoErrors()
            ->assertSet('showEditModal', false);

        $this->assertDatabaseHas('attendance_sessions', [
            'id' => $session->id,
            'title' => 'Presensi Sesi Morse Putra Terkoreksi',
        ]);

        $this->assertDatabaseHas('competition_schedules', [
            'id' => $schedule->id,
            'session_name' => 'Sesi Morse Putra',
            'arena_location' => 'Pos Lapangan Utama',
            'gender_category' => 'boys',
        ]);
    }

    /**
     * Memastikan administrator dapat menghapus sesi presensi
     */
    public function test_admin_can_delete_attendance_session(): void
    {
        $session = AttendanceSession::create([
            'title' => 'Sesi Percobaan yang Akan Dihapus',
            'session_type' => 'participant',
            'is_competition' => false,
            'is_active' => false,
        ]);

        Livewire::actingAs($this->admin)
            ->test(AttendanceDashboard::class)
            ->call('deleteAttendanceSession', $session->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('attendance_sessions', [
            'id' => $session->id,
        ]);
    }

    /**
     * Memastikan non-admin tidak berwenang mengedit sesi presensi
     */
    public function test_non_admin_cannot_edit_attendance_session(): void
    {
        $session = AttendanceSession::create([
            'title' => 'Sesi Kehadiran Tetap',
            'session_type' => 'participant',
            'is_competition' => false,
            'is_active' => true,
        ]);

        Livewire::actingAs($this->committee)
            ->test(AttendanceDashboard::class)
            ->call('openEditModal', $session->id)
            ->assertStatus(403);
    }

    /**
     * Memastikan modal visual log menampilkan daftar rekaman presensi dengan benar
     */
    public function test_user_can_open_visual_logs_modal(): void
    {
        $session = AttendanceSession::create([
            'title' => 'Presensi Apel Besar Buper',
            'session_type' => 'participant',
            'is_competition' => false,
            'is_active' => true,
        ]);

        $scoutGroup = \App\Models\ScoutGroup::create([
            'user_id' => $this->admin->id,
            'registration_code' => 'REG-SMP-LOG',
            'school_name' => 'SMP Negeri 2 Padangsidimpuan',
            'group_number' => '02.001',
            'kwarran_name' => 'Padangsidimpuan Selatan',
            'address' => 'Jl. Merdeka',
            'contact_person' => 'Kak Pembina',
            'contact_phone' => '081234567890',
            'verification_status' => 'verified',
        ]);

        $patrol = \App\Models\Patrol::create([
            'scout_group_id' => $scoutGroup->id,
            'patrol_name' => 'Garuda Sakti',
            'gender_category' => \App\Enums\PatrolGenderEnum::Boys,
            'group_number' => '02.001',
        ]);

        $participant = \App\Models\Participant::create([
            'patrol_id' => $patrol->id,
            'full_name' => 'Ahmad Fakhri',
            'gender' => 'male',
            'role_in_patrol' => \App\Enums\PatrolRoleEnum::PatrolLeader,
            'birth_place' => 'Padangsidimpuan',
            'birth_date' => '2011-04-12',
            'qr_token' => (string) \Illuminate\Support\Str::uuid(),
            'id_number' => 'ID-FAKHRI01',
            'status' => 'verified',
        ]);

        $attendance = \App\Models\Attendance::create([
            'attendance_session_id' => $session->id,
            'entity_type' => 'participant',
            'participant_id' => $participant->id,
            'patrol_id' => $patrol->id,
            'scanned_by_user_id' => $this->admin->id,
            'scanned_at' => now(),
            'status' => 'present',
            'notes' => 'Tepat waktu di pos',
        ]);

        Livewire::actingAs($this->admin)
            ->test(AttendanceDashboard::class)
            ->call('openLogsModal', $session->id)
            ->assertSet('showLogsModal', true)
            ->assertSee('Log Presensi: Presensi Apel Besar Buper')
            ->assertSee('Ahmad Fakhri')
            ->assertSee('ID-FAKHRI01')
            ->assertSee('Garuda Sakti')
            ->assertSee('Tepat Waktu')
            ->assertSee('Tepat waktu di pos');
    }

    /**
     * Memastikan status presensi dapat diperbarui langsung dari modal log
     */
    public function test_user_can_update_attendance_status_from_logs_modal(): void
    {
        $session = AttendanceSession::create([
            'title' => 'Presensi Apel Sore',
            'session_type' => 'participant',
            'is_competition' => false,
            'is_active' => true,
        ]);

        $attendance = \App\Models\Attendance::create([
            'attendance_session_id' => $session->id,
            'entity_type' => 'user',
            'user_id' => $this->admin->id,
            'scanned_by_user_id' => $this->admin->id,
            'scanned_at' => now(),
            'status' => 'present',
        ]);

        Livewire::actingAs($this->admin)
            ->test(AttendanceDashboard::class)
            ->call('openLogsModal', $session->id)
            ->call('updateAttendanceStatus', $attendance->id, 'late')
            ->assertHasNoErrors();

        $this->assertEquals('late', $attendance->fresh()->status);
    }
}


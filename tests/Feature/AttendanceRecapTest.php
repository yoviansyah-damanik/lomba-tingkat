<?php

namespace Tests\Feature;

use App\Enums\PatrolGenderEnum;
use App\Enums\PatrolRoleEnum;
use App\Enums\RegistrationStatusEnum;
use App\Enums\RoleEnum;
use App\Livewire\Portal\AttendanceRecap;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Chaperone;
use App\Models\Competition;
use App\Models\CompetitionSchedule;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AttendanceRecapTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $committee;
    protected User $judge;
    protected AttendanceSession $session;
    protected Attendance $attendance;
    protected Participant $participant;
    protected ScoutGroup $scoutGroup;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(\Database\Seeders\EventSeeder::class);

        $event = Event::first();

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

        $this->judge = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $this->judge->assignRole(RoleEnum::Judge);

        $groupUser = User::factory()->create();
        $this->scoutGroup = ScoutGroup::create([
            'user_id' => $groupUser->id,
            'registration_code' => 'LT3-RECAP-01',
            'school_name' => 'SMP Negeri 1 Padangsidimpuan',
            'group_number' => '01.001 - 01.002',
            'kwarran_name' => 'Padangsidimpuan Utara',
            'address' => 'Jl. Merdeka No. 1',
            'contact_person' => 'Kak Surya',
            'contact_phone' => '081234567890',
            'verification_status' => RegistrationStatusEnum::Verified,
        ]);

        $patrol = Patrol::create([
            'scout_group_id' => $this->scoutGroup->id,
            'patrol_name' => 'Rajawali',
            'gender_category' => PatrolGenderEnum::Boys,
            'status' => RegistrationStatusEnum::Verified,
        ]);

        $this->participant = Participant::create([
            'patrol_id' => $patrol->id,
            'full_name' => 'Aditya Pratama',
            'role_in_patrol' => PatrolRoleEnum::PatrolLeader,
            'scout_id' => '02.08.01.999',
            'gender' => 'male',
            'class_grade' => 'VIII',
            'birth_place' => 'Padangsidimpuan',
            'birth_date' => '2012-01-15',
            'status' => RegistrationStatusEnum::Verified,
        ]);

        $comp = Competition::create([
            'event_id' => $event->id,
            'title' => 'Pionering Menara Pandang',
            'code' => 'PIONERING-01',
            'category' => 'boys',
            'participant_type' => 'patrol',
            'min_participants' => 8,
            'max_participants' => 8,
            'is_published' => true,
        ]);

        $schedule = CompetitionSchedule::create([
            'competition_id' => $comp->id,
            'session_name' => 'Gelombang 1 Pos Utama',
            'start_time' => now()->addHour(),
            'end_time' => now()->addHours(3),
            'arena_location' => 'Lapangan Utama Kwartir Cabang',
            'status' => 'scheduled',
            'is_published' => true,
        ]);

        $this->session = $schedule->attendanceSessions()->first();

        // Tugaskan juri ke jadwal agar juri berhak mengakses
        $schedule->judgeAssignments()->create([
            'user_id' => $this->judge->id,
            'role' => 'lead_judge',
            'is_confirmed' => true,
        ]);

        $this->attendance = Attendance::create([
            'attendance_session_id' => $this->session->id,
            'entity_type' => 'participant',
            'participant_id' => $this->participant->id,
            'patrol_id' => $patrol->id,
            'scanned_by_user_id' => $this->admin->id,
            'scanned_at' => now(),
            'status' => 'present',
            'notes' => 'Hadir tepat waktu di pos pionering',
            'device_info' => 'PHPUnit Test Device',
        ]);
    }

    public function test_attendance_recap_renders_for_authorized_users(): void
    {
        Livewire::actingAs($this->admin)
            ->test(AttendanceRecap::class)
            ->assertStatus(200)
            ->assertSee('Rekapitulasi Presensi')
            ->assertSee('Kehadiran Pos')
            ->assertSee('Aditya Pratama')
            ->assertSee('Rajawali')
            ->assertSee('Pionering Menara Pandang');

        Livewire::actingAs($this->judge)
            ->test(AttendanceRecap::class)
            ->assertStatus(200)
            ->assertSee('Aditya Pratama');
    }

    public function test_attendance_recap_filtering(): void
    {
        // Tambahkan presensi terlambat
        $chaperone = Chaperone::create([
            'scout_group_id' => $this->scoutGroup->id,
            'full_name' => 'Kak Hendra, S.Pd',
            'gender' => 'male',
            'scout_id' => '02.08.00.888',
            'phone_number' => '081299998888',
            'status' => RegistrationStatusEnum::Verified,
        ]);

        Attendance::create([
            'attendance_session_id' => $this->session->id,
            'entity_type' => 'chaperone',
            'chaperone_id' => $chaperone->id,
            'scanned_by_user_id' => $this->admin->id,
            'scanned_at' => now(),
            'status' => 'late',
            'notes' => 'Terlambat 15 menit',
        ]);

        Livewire::actingAs($this->admin)
            ->test(AttendanceRecap::class)
            ->set('statusFilter', 'late')
            ->assertSee('Kak Hendra, S.Pd')
            ->assertDontSee('Aditya Pratama');

        Livewire::actingAs($this->admin)
            ->test(AttendanceRecap::class)
            ->set('search', 'Aditya')
            ->assertSee('Aditya Pratama')
            ->assertDontSee('Kak Hendra, S.Pd');
    }

    public function test_user_can_edit_attendance_via_livewire_modal(): void
    {
        Livewire::actingAs($this->admin)
            ->test(AttendanceRecap::class)
            ->call('openEditModal', $this->attendance->id)
            ->assertSet('showEditModal', true)
            ->assertSet('editStatus', 'present')
            ->set('editStatus', 'late')
            ->set('editNotes', 'Revisi: Terlambat karena kendala posko')
            ->call('saveEdit')
            ->assertSet('showEditModal', false);

        $this->assertEquals('late', $this->attendance->fresh()->status);
        $this->assertStringContainsString('Revisi: Terlambat', $this->attendance->fresh()->notes);
    }

    public function test_user_can_quick_update_attendance_status(): void
    {
        Livewire::actingAs($this->admin)
            ->test(AttendanceRecap::class)
            ->call('quickUpdateStatus', $this->attendance->id, 'excused');

        $this->assertEquals('excused', $this->attendance->fresh()->status);
    }

    public function test_user_can_delete_attendance_record(): void
    {
        Livewire::actingAs($this->admin)
            ->test(AttendanceRecap::class)
            ->call('deleteAttendance', $this->attendance->id);

        $this->assertDatabaseMissing('attendances', [
            'id' => $this->attendance->id,
        ]);
    }

    public function test_attendance_update_controller_endpoint(): void
    {
        $response = $this->actingAs($this->admin)->putJson(
            route('attendance.update', $this->attendance),
            [
                'status' => 'excused',
                'notes' => 'Izin medis posko kesehatan',
            ]
        );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertEquals('excused', $this->attendance->fresh()->status);
        $this->assertEquals('Izin medis posko kesehatan', $this->attendance->fresh()->notes);
    }

    public function test_attendance_recap_print_view(): void
    {
        $response = $this->actingAs($this->admin)->get(
            route('attendance.recap.print', ['session_id' => $this->session->id])
        );

        $response->assertStatus(200)
            ->assertSee('BERITA ACARA', false)
            ->assertSee('REKAPITULASI PRESENSI KEGIATAN', false)
            ->assertSee('Aditya Pratama')
            ->assertSee('Pionering Menara Pandang');
    }
}

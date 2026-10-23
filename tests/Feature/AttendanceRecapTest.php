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
            'is_active' => true,
        ]);
        $this->admin->assignRole(RoleEnum::Admin);

        $this->committee = User::factory()->create([
            'is_active' => true,
        ]);
        $this->committee->assignRole(RoleEnum::Committee);

        $this->judge = User::factory()->create([
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

    public function test_attendance_recap_pdf_template_content(): void
    {
        $attendances = \App\Models\Attendance::with(['participant.patrol.scoutGroup', 'chaperone.scoutGroup', 'user', 'session.schedule.competition'])->get();

        $html = view('portal.attendance.recap-pdf', [
            'attendances' => $attendances,
            'selectedSession' => $this->session->load('schedule.competition'),
            'stats' => ['total' => $attendances->count(), 'present' => 1, 'late' => 0, 'excused' => 0, 'absent' => 0],
            'event' => \App\Models\Event::first(),
        ])->render();

        $this->assertStringContainsString('Berita Acara', $html);
        $this->assertStringContainsString('Rekapitulasi Presensi Kegiatan', $html);
        $this->assertStringContainsString('Aditya Pratama', $html);
        $this->assertStringContainsString('Pionering Menara Pandang', $html);
        $this->assertStringContainsString('Ketua Panitia Pelaksana LT III', $html);
        $this->assertStringContainsString('NTA.', $html);
        $this->assertStringNotContainsString('GERAKAN PRAMUKA KWARTIR CABANG', $html);
    }

    public function test_all_sessions_recap_prints_each_session_on_its_own_sheet_with_configured_signatory(): void
    {
        $event = \App\Models\Event::first();
        $event->update(['committee_chair_name' => 'Drs. Ahmad Siregar', 'committee_chair_nta' => '02.08.01.001']);

        $attendances = \App\Models\Attendance::with(['participant.patrol.scoutGroup', 'chaperone.scoutGroup', 'user', 'session.schedule.competition'])->get();
        $stats = ['total' => $attendances->count(), 'present' => 1, 'late' => 0, 'excused' => 0, 'absent' => 0];

        $secondSession = \App\Models\AttendanceSession::create([
            'title' => 'Presensi Sesi Kedua Uji',
            'session_type' => 'participant',
            'is_competition' => false,
            'opened_at' => now(),
            'closed_at' => now()->addHour(),
            'is_active' => true,
        ]);

        $html = view('portal.attendance.recap-pdf', [
            'attendances' => $attendances,
            'selectedSession' => null,
            'stats' => $stats,
            'event' => $event,
            'sections' => collect([
                ['session' => $this->session->load('schedule.competition'), 'number' => 'BAP-LT3/KWARCAB-PSP/2026/001', 'attendances' => $attendances, 'stats' => $stats],
                ['session' => $secondSession, 'number' => 'BAP-LT3/KWARCAB-PSP/2026/002', 'attendances' => collect(), 'stats' => array_fill_keys(['total', 'present', 'late', 'excused', 'absent'], 0)],
            ]),
        ])->render();

        // Lembar kedua dimulai pada halaman baru; lembar pertama tidak
        $this->assertSame(1, substr_count($html, 'page-break-before: always'));
        $this->assertStringContainsString('Nomor: BAP-LT3/KWARCAB-PSP/2026/001', $html);
        $this->assertStringContainsString('Nomor: BAP-LT3/KWARCAB-PSP/2026/002', $html);
        $this->assertSame(2, substr_count($html, 'Rekapitulasi Presensi Kegiatan'));
        $this->assertStringContainsString('Presensi Sesi Kedua Uji', $html);
        $this->assertStringContainsString('Drs. Ahmad Siregar', $html);
        $this->assertStringNotContainsString('text-transform: uppercase', mb_substr($html, (int) mb_strpos($html, 'Drs. Ahmad Siregar') - 80, 100));
        $this->assertStringContainsString('NTA. 02.08.01.001', $html);
    }

    public function test_recap_data_for_all_sessions_returns_every_session_including_empty_ones(): void
    {
        \App\Models\AttendanceSession::create([
            'title' => 'Sesi Tanpa Presensi Uji',
            'session_type' => 'participant',
            'is_competition' => false,
            'opened_at' => now()->addDay(),
            'closed_at' => now()->addDay()->addHour(),
            'is_active' => true,
        ]);

        $request = \Illuminate\Http\Request::create('/portal/attendance/recap/pdf', 'GET', ['session_id' => 'all']);
        $request->setUserResolver(fn () => $this->admin);

        $method = new \ReflectionMethod(\App\Http\Controllers\AttendanceScannerController::class, 'recapData');
        $method->setAccessible(true);
        $data = $method->invoke(app(\App\Http\Controllers\AttendanceScannerController::class), $request);

        $titles = $data['sections']->pluck('session.title');
        $this->assertSame(\App\Models\AttendanceSession::count(), $data['sections']->count());
        $this->assertTrue($titles->contains('Sesi Tanpa Presensi Uji'));
        $this->assertSame(0, $data['sections']->firstWhere('session.title', 'Sesi Tanpa Presensi Uji')['stats']['total']);
        $this->assertSame($data['attendances']->count(), $data['sections']->sum(fn ($section) => $section['attendances']->count()));
        $this->assertSame($data['sections']->count(), $data['sections']->pluck('number')->unique()->count());
        $this->assertMatchesRegularExpression('#^BAP-LT3/KWARCAB-PSP/\\d{4}/\\d{3}$#', $data['sections']->first()['number']);
    }

    public function test_recap_data_for_a_selected_session_contains_only_that_session(): void
    {
        \App\Models\AttendanceSession::create([
            'title' => 'Sesi Lain Uji',
            'session_type' => 'participant',
            'is_competition' => false,
            'opened_at' => now()->addDay(),
            'closed_at' => now()->addDay()->addHour(),
            'is_active' => true,
        ]);

        $request = \Illuminate\Http\Request::create('/portal/attendance/recap/pdf', 'GET', ['session_id' => $this->session->id]);
        $request->setUserResolver(fn () => $this->admin);

        $method = new \ReflectionMethod(\App\Http\Controllers\AttendanceScannerController::class, 'recapData');
        $method->setAccessible(true);
        $data = $method->invoke(app(\App\Http\Controllers\AttendanceScannerController::class), $request);

        $this->assertCount(1, $data['sections']);
        $this->assertSame($this->session->id, $data['sections']->first()['session']->id);
    }

    public function test_preview_link_on_recap_page_carries_selected_session(): void
    {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Portal\AttendanceRecap::class)
            ->assertDontSeeHtml('recap/preview?session_id=')
            ->set('sessionId', $this->session->id)
            ->assertSeeHtml('recap/preview?session_id='.$this->session->id);
    }

    public function test_print_window_route_is_removed(): void
    {
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('attendance.recap.print'));
    }

    public function test_attendance_recap_preview_page_embeds_pdf_and_offers_download(): void
    {
        $response = $this->actingAs($this->admin)->get(
            route('attendance.recap.preview', ['session_id' => $this->session->id])
        );

        $response->assertOk()
            ->assertSee('PRATINJAU PDF')
            ->assertSee('Unduh PDF')
            ->assertSee(route('attendance.recap.pdf', ['session_id' => $this->session->id]), false)
            ->assertSee('download=1', false);
    }

    public function test_attendance_recap_pdf_is_inline_by_default_and_attachment_on_download(): void
    {
        $inline = $this->actingAs($this->admin)->get(
            route('attendance.recap.pdf', ['session_id' => $this->session->id, 'raw' => 1])
        );

        $inline->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $inline->getContent());
        $this->assertStringContainsString('inline; filename="rekap-presensi-', $inline->headers->get('Content-Disposition'));

        $download = $this->actingAs($this->admin)->get(
            route('attendance.recap.pdf', ['session_id' => $this->session->id, 'download' => 1])
        );

        $this->assertStringContainsString('attachment; filename="rekap-presensi-', $download->headers->get('Content-Disposition'));
    }
}

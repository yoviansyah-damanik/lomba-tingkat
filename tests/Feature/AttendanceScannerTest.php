<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Competition;
use App\Models\CompetitionSchedule;
use App\Models\Event;
use App\Models\JudgeAssignment;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AttendanceScannerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $committee;
    protected User $assignedJudge;
    protected User $unassignedJudge;
    protected User $dualRoleUser;
    protected User $regularUser;
    protected AttendanceSession $activeSession; // Sesi Lomba Aktif
    protected AttendanceSession $inactiveSession; // Sesi Lomba Nonaktif
    protected AttendanceSession $generalSession; // Sesi Kehadiran Umum Aktif
    protected AttendanceSession $inactiveGeneralSession; // Sesi Kehadiran Umum Nonaktif
    protected CompetitionSchedule $schedule;
    protected Participant $participant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(\Database\Seeders\EventSeeder::class);

        // Buat berbagai akun pengguna sesuai peran
        $this->admin = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
        $this->admin->assignRole(RoleEnum::Admin);

        $this->committee = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
        $this->committee->assignRole(RoleEnum::Committee);

        $this->assignedJudge = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
        $this->assignedJudge->assignRole(RoleEnum::Judge);

        $this->unassignedJudge = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
        $this->unassignedJudge->assignRole(RoleEnum::Judge);

        $this->dualRoleUser = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
        $this->dualRoleUser->assignRole(RoleEnum::Committee);
        $this->dualRoleUser->assignRole(RoleEnum::Judge);

        $this->regularUser = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);

        $event = Event::first();
        $competition = Competition::create([
            'event_id' => $event->id,
            'title' => 'Pionering Menara Pandang',
            'code' => 'PION-01',
            'category' => 'all',
            'participant_type' => 'patrol',
            'scoring_formula' => 'average',
            'is_published' => true,
        ]);

        $this->schedule = CompetitionSchedule::create([
            'competition_id' => $competition->id,
            'session_name' => 'Sesi Utama Pagi',
            'arena_location' => 'Lapangan Utama Pos 1',
            'start_time' => now()->addMinutes(15),
            'end_time' => now()->addHours(3),
            'status' => 'scheduled',
            'is_published' => true,
        ]);

        // Tugaskan juri ke jadwal ini
        JudgeAssignment::create([
            'competition_schedule_id' => $this->schedule->id,
            'user_id' => $this->assignedJudge->id,
            'role' => 'lead_judge',
            'is_confirmed' => true,
        ]);

        JudgeAssignment::create([
            'competition_schedule_id' => $this->schedule->id,
            'user_id' => $this->dualRoleUser->id,
            'role' => 'scoring_judge',
            'is_confirmed' => true,
        ]);

        // Sesi Presensi Lomba
        $this->activeSession = AttendanceSession::create([
            'competition_schedule_id' => $this->schedule->id,
            'session_type' => 'participant',
            'title' => 'Pos Registrasi Pionering',
            'is_competition' => true,
            'is_active' => true,
            'opened_at' => now(),
        ]);

        $this->inactiveSession = AttendanceSession::create([
            'competition_schedule_id' => $this->schedule->id,
            'session_type' => 'participant',
            'title' => 'Pos Morse Tertutup',
            'is_competition' => true,
            'is_active' => false,
        ]);

        // Sesi Presensi Kehadiran Umum (Bukan Lomba)
        $this->generalSession = AttendanceSession::create([
            'competition_schedule_id' => null,
            'session_type' => 'participant',
            'title' => 'Presensi Registrasi Kedatangan',
            'is_competition' => false,
            'is_active' => true,
            'opened_at' => now(),
        ]);

        $this->inactiveGeneralSession = AttendanceSession::create([
            'competition_schedule_id' => null,
            'session_type' => 'participant',
            'title' => 'Presensi Apel Tertutup',
            'is_competition' => false,
            'is_active' => false,
        ]);

        $scoutUser = User::factory()->create();
        $scoutUser->assignRole(RoleEnum::ScoutGroup);

        $scoutGroup = ScoutGroup::create([
            'user_id' => $scoutUser->id,
            'registration_code' => 'REG-SMP1-2026',
            'school_name' => 'SMP Negeri 1 Padangsidimpuan',
            'group_number' => '01.001 - 01.002',
            'male_group_number' => '01.001',
            'female_group_number' => '01.002',
            'kwarran_name' => 'Padangsidimpuan Utara',
            'address' => 'Jl. Sudirman No. 1',
            'contact_person' => 'Kak Pembina',
            'contact_phone' => '081234567890',
            'verification_status' => 'verified',
        ]);

        $patrol = Patrol::create([
            'scout_group_id' => $scoutGroup->id,
            'patrol_name' => 'Rajawali',
            'gender_category' => \App\Enums\PatrolGenderEnum::Boys,
            'group_number' => '01.001',
        ]);

        $this->participant = Participant::create([
            'patrol_id' => $patrol->id,
            'full_name' => 'Budi Santoso',
            'role_in_patrol' => \App\Enums\PatrolRoleEnum::PatrolLeader,
            'gender' => 'male',
            'birth_place' => 'Padangsidimpuan',
            'birth_date' => '2011-05-12',
            'qr_token' => (string) Str::uuid(),
            'id_number' => 'ID-BUDI1234',
        ]);
    }

    public function test_guest_is_redirected_to_login_when_accessing_attendance_portal(): void
    {
        $response = $this->get(route('attendance.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_is_forbidden_from_attendance_portal(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('attendance.index'));
        $response->assertForbidden();
    }

    public function test_committee_only_sees_general_attendance_in_dashboard(): void
    {
        $response = $this->actingAs($this->committee)->get(route('attendance.index'));

        $response->assertOk()
            ->assertSee('Portal Presensi')
            ->assertSee('Presensi Registrasi Kedatangan')
            ->assertDontSee('Pos Registrasi Pionering');
    }

    public function test_judge_only_sees_assigned_competition_sessions_in_dashboard(): void
    {
        // Juri yang ditugaskan melihat sesi lomba yang ditunjuk, tidak melihat kehadiran umum
        $this->actingAs($this->assignedJudge)
            ->get(route('attendance.index'))
            ->assertOk()
            ->assertSee('Pos Registrasi Pionering')
            ->assertDontSee('Presensi Registrasi Kedatangan');

        // Juri yang tidak ditugaskan tidak melihat sesi lomba pionering
        $this->actingAs($this->unassignedJudge)
            ->get(route('attendance.index'))
            ->assertOk()
            ->assertDontSee('Pos Registrasi Pionering')
            ->assertDontSee('Presensi Registrasi Kedatangan');
    }

    public function test_admin_and_dual_role_can_see_both_session_types_in_dashboard(): void
    {
        // Admin melihat kedua jenis sesi
        $this->actingAs($this->admin)
            ->get(route('attendance.index'))
            ->assertOk()
            ->assertSee('Presensi Registrasi Kedatangan')
            ->assertSee('Pos Registrasi Pionering');

        // Pengguna rangkap Panitia & Juri melihat sesi kehadiran + sesi lomba yang ditunjuk
        $this->actingAs($this->dualRoleUser)
            ->get(route('attendance.index'))
            ->assertOk()
            ->assertSee('Presensi Registrasi Kedatangan')
            ->assertSee('Pos Registrasi Pionering');
    }

    public function test_committee_can_access_and_scan_general_attendance_session(): void
    {
        // Panitia bisa membuka kamera scanner sesi kehadiran
        $this->actingAs($this->committee)
            ->get(route('attendance.scanner', $this->generalSession))
            ->assertOk()
            ->assertSee('Presensi Registrasi Kedatangan')
            ->assertSee('ARAHKAN QR CODE KE SINI');

        // Panitia bisa melakukan scan kehadiran
        $response = $this->actingAs($this->committee)
            ->postJson(route('attendance.scan', $this->generalSession), [
                'token' => $this->participant->qr_token,
                'device_info' => 'Panitia Device Test',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'code' => 'RECORDED',
                'attendee' => [
                    'name' => 'Budi Santoso',
                    'id_number' => 'ID-BUDI1234',
                ],
            ]);

        $this->assertDatabaseHas('attendances', [
            'attendance_session_id' => $this->generalSession->id,
            'participant_id' => $this->participant->id,
            'status' => 'present',
            'scanned_by_user_id' => $this->committee->id,
        ]);
    }

    public function test_committee_is_forbidden_from_competition_session(): void
    {
        // Panitia dilarang membuka scanner lomba
        $this->actingAs($this->committee)
            ->get(route('attendance.scanner', $this->activeSession))
            ->assertForbidden();

        // Panitia dilarang scan presensi lomba
        $this->actingAs($this->committee)
            ->postJson(route('attendance.scan', $this->activeSession), [
                'token' => $this->participant->qr_token,
            ])
            ->assertForbidden();

        // Panitia dilarang manual check-in lomba
        $this->actingAs($this->committee)
            ->postJson(route('attendance.manual', $this->activeSession), [
                'entity_type' => 'participant',
                'entity_id' => $this->participant->id,
            ])
            ->assertForbidden();

        // Panitia dilarang mencari kandidat di sesi lomba
        $this->actingAs($this->committee)
            ->getJson(route('attendance.search', ['session' => $this->activeSession, 'q' => 'Budi']))
            ->assertForbidden();

        // Panitia dilarang melihat riwayat scan sesi lomba
        $this->actingAs($this->committee)
            ->getJson(route('attendance.logs', $this->activeSession))
            ->assertForbidden();
    }

    public function test_assigned_judge_can_access_and_scan_assigned_competition_session(): void
    {
        // Juri yang ditugaskan bisa membuka scanner pos lombanya
        $this->actingAs($this->assignedJudge)
            ->get(route('attendance.scanner', $this->activeSession))
            ->assertOk()
            ->assertSee('Pos Registrasi Pionering')
            ->assertSee('ARAHKAN QR CODE KE SINI');

        // Juri yang ditugaskan bisa scan kehadiran pos lomba
        $response = $this->actingAs($this->assignedJudge)
            ->postJson(route('attendance.scan', $this->activeSession), [
                'token' => $this->participant->qr_token,
                'device_info' => 'Judge Device Test',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'code' => 'RECORDED',
            ]);

        $this->assertDatabaseHas('attendances', [
            'attendance_session_id' => $this->activeSession->id,
            'participant_id' => $this->participant->id,
            'scanned_by_user_id' => $this->assignedJudge->id,
        ]);
    }

    public function test_assigned_judge_is_forbidden_from_general_attendance_session(): void
    {
        // Juri dilarang mengakses scanner kehadiran umum
        $this->actingAs($this->assignedJudge)
            ->get(route('attendance.scanner', $this->generalSession))
            ->assertForbidden();

        // Juri dilarang scan presensi kehadiran umum
        $this->actingAs($this->assignedJudge)
            ->postJson(route('attendance.scan', $this->generalSession), [
                'token' => $this->participant->qr_token,
            ])
            ->assertForbidden();
    }

    public function test_unassigned_judge_is_forbidden_from_unassigned_competition_session(): void
    {
        // Juri yang tidak ditugaskan ke lomba ini dilarang mengakses
        $this->actingAs($this->unassignedJudge)
            ->get(route('attendance.scanner', $this->activeSession))
            ->assertForbidden();

        $this->actingAs($this->unassignedJudge)
            ->postJson(route('attendance.scan', $this->activeSession), [
                'token' => $this->participant->qr_token,
            ])
            ->assertForbidden();
    }

    public function test_admin_can_access_and_scan_both_general_and_competition_sessions(): void
    {
        // Admin bisa scan sesi umum
        $this->actingAs($this->admin)
            ->get(route('attendance.scanner', $this->generalSession))
            ->assertOk();

        // Admin bisa scan sesi lomba
        $this->actingAs($this->admin)
            ->get(route('attendance.scanner', $this->activeSession))
            ->assertOk();
    }

    public function test_scanner_page_blocks_camera_when_session_is_deactivated_by_admin(): void
    {
        $response = $this->actingAs($this->committee)
            ->get(route('attendance.scanner', $this->inactiveGeneralSession));

        $response->assertOk()
            ->assertSee('Akses Kamera Diblokir')
            ->assertSee('Sesi presensi ini sedang dinonaktifkan oleh Administrator')
            ->assertSee('Kamera Diblokir')
            ->assertDontSee('ARAHKAN QR CODE KE SINI');
    }

    public function test_duplicate_scan_in_same_session_returns_already_scanned_error(): void
    {
        // Pindai pertama kali
        $this->actingAs($this->committee)
            ->postJson(route('attendance.scan', $this->generalSession), [
                'token' => $this->participant->qr_token,
            ])
            ->assertOk();

        // Pindai kedua kali (duplikasi)
        $response = $this->actingAs($this->committee)
            ->postJson(route('attendance.scan', $this->generalSession), [
                'token' => $this->participant->qr_token,
            ]);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'code' => 'ALREADY_SCANNED',
            ]);
    }

    public function test_scanning_on_inactive_session_is_rejected(): void
    {
        $response = $this->actingAs($this->committee)
            ->postJson(route('attendance.scan', $this->inactiveGeneralSession), [
                'token' => $this->participant->qr_token,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'code' => 'SESSION_INACTIVE',
            ]);
    }

    public function test_scanning_invalid_token_is_rejected(): void
    {
        $response = $this->actingAs($this->committee)
            ->postJson(route('attendance.scan', $this->generalSession), [
                'token' => 'invalid-fake-token-12345',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'code' => 'INVALID_TOKEN',
            ]);
    }

    public function test_manual_check_in_successfully_records_attendance(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('attendance.manual', $this->generalSession), [
                'entity_type' => 'participant',
                'entity_id' => $this->participant->id,
                'notes' => 'Input Manual Pos Lapangan',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'code' => 'RECORDED',
            ]);

        $this->assertDatabaseHas('attendances', [
            'attendance_session_id' => $this->generalSession->id,
            'participant_id' => $this->participant->id,
            'notes' => 'Input Manual Pos Lapangan',
        ]);
    }

    public function test_search_candidates_finds_eligible_attendees(): void
    {
        $response = $this->actingAs($this->committee)
            ->getJson(route('attendance.search', [
                'session' => $this->generalSession,
                'q' => 'Budi',
            ]));

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonFragment([
                'id' => $this->participant->id,
                'name' => 'Budi Santoso',
                'is_attended' => false,
            ]);
    }

    public function test_logs_endpoint_returns_realtime_attendance_stream(): void
    {
        Attendance::create([
            'attendance_session_id' => $this->generalSession->id,
            'entity_type' => 'participant',
            'participant_id' => $this->participant->id,
            'scanned_by_user_id' => $this->committee->id,
            'scanned_at' => now(),
            'status' => 'present',
        ]);

        $response = $this->actingAs($this->committee)
            ->getJson(route('attendance.logs', $this->generalSession));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'total' => 1,
            ])
            ->assertJsonFragment([
                'name' => 'Budi Santoso',
            ]);
    }

    public function test_competition_schedule_creation_automatically_creates_active_attendance_session(): void
    {
        $competition = Competition::first();

        $schedule = CompetitionSchedule::create([
            'competition_id' => $competition->id,
            'session_name' => 'Sesi Penjelajahan Siang',
            'arena_location' => 'Pos Hutan Lindung',
            'start_time' => now()->addHours(2),
            'end_time' => now()->addHours(5),
            'status' => 'scheduled',
            'is_published' => true,
        ]);

        $this->assertDatabaseHas('attendance_sessions', [
            'competition_schedule_id' => $schedule->id,
            'is_active' => true,
            'is_competition' => true,
        ]);

        $session = $schedule->attendanceSessions()->first();
        $this->assertNotNull($session);
        $this->assertTrue($session->is_active);
        $this->assertTrue($session->is_competition);
        $this->assertStringContainsString('Presensi:', $session->title);
    }

    public function test_patrol_competition_rejects_ordinary_member_attendance(): void
    {
        $member = Participant::create([
            'patrol_id' => $this->participant->patrol_id,
            'full_name' => 'Andi Anggota',
            'role_in_patrol' => \App\Enums\PatrolRoleEnum::Member,
            'gender' => 'male',
            'birth_place' => 'Padangsidimpuan',
            'birth_date' => '2011-06-15',
            'qr_token' => (string) Str::uuid(),
            'id_number' => 'ID-ANDI5678',
        ]);

        $response = $this->actingAs($this->assignedJudge)
            ->postJson(route('attendance.scan', $this->activeSession), [
                'token' => $member->qr_token,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'code' => 'LEADER_REQUIRED',
            ]);
    }

    public function test_patrol_competition_rejects_second_leader_scan_when_patrol_already_attended(): void
    {
        // 1. PINRU scan presensi pertama kali -> Berhasil
        $this->actingAs($this->assignedJudge)
            ->postJson(route('attendance.scan', $this->activeSession), [
                'token' => $this->participant->qr_token,
            ])
            ->assertOk();

        // 2. Buat WAPINRU dari regu yang sama
        $assistantLeader = Participant::create([
            'patrol_id' => $this->participant->patrol_id,
            'full_name' => 'Doni Wapinru',
            'role_in_patrol' => \App\Enums\PatrolRoleEnum::AssistantPatrolLeader,
            'gender' => 'male',
            'birth_place' => 'Padangsidimpuan',
            'birth_date' => '2011-07-20',
            'qr_token' => (string) Str::uuid(),
            'id_number' => 'ID-DONI9999',
        ]);

        // 3. WAPINRU mencoba scan untuk regu yang sama -> Ditolak (Regu sudah hadir)
        $response = $this->actingAs($this->assignedJudge)
            ->postJson(route('attendance.scan', $this->activeSession), [
                'token' => $assistantLeader->qr_token,
            ]);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'code' => 'ALREADY_SCANNED',
            ]);
    }

    public function test_individual_competition_accepts_any_patrol_representative_and_rejects_second_representative(): void
    {
        // Buat lomba tipe individu (contoh Pidato)
        $event = Event::first();
        $indivCompetition = Competition::create([
            'event_id' => $event->id,
            'title' => 'Pidato Kebangsaan',
            'code' => 'PID-01',
            'category' => 'all',
            'participant_type' => 'individual',
            'scoring_formula' => 'average',
            'is_published' => true,
        ]);

        $indivSchedule = CompetitionSchedule::create([
            'competition_id' => $indivCompetition->id,
            'session_name' => 'Babak Final',
            'arena_location' => 'Aula Kwarcab',
            'participant_type' => 'individual',
            'start_time' => now()->addMinutes(15),
            'end_time' => now()->addHours(3),
            'status' => 'scheduled',
            'is_published' => true,
        ]);

        // Tugaskan juri ke jadwal individu ini
        JudgeAssignment::create([
            'competition_schedule_id' => $indivSchedule->id,
            'user_id' => $this->assignedJudge->id,
            'role' => 'lead_judge',
            'is_confirmed' => true,
        ]);

        $indivSession = $indivSchedule->attendanceSessions()->first();
        $this->assertNotNull($indivSession);
        $this->assertTrue($indivSession->is_active);

        // Buat anggota biasa dari regu Rajawali sebagai perwakilan lomba pidato
        $memberRep = Participant::create([
            'patrol_id' => $this->participant->patrol_id,
            'full_name' => 'Fajar Orator',
            'role_in_patrol' => \App\Enums\PatrolRoleEnum::Member,
            'gender' => 'male',
            'birth_place' => 'Padangsidimpuan',
            'birth_date' => '2011-08-10',
            'qr_token' => (string) Str::uuid(),
            'id_number' => 'ID-FAJAR001',
        ]);

        // 1. Anggota biasa memindai presensi sebagai perwakilan regu melalui juri yang ditugaskan -> Berhasil!
        $response = $this->actingAs($this->assignedJudge)
            ->postJson(route('attendance.scan', $indivSession), [
                'token' => $memberRep->qr_token,
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'code' => 'RECORDED',
            ]);

        // 2. Anggota lain (atau PINRU) dari regu yang sama mencoba scan untuk sesi individu yang sama -> Ditolak
        $response2 = $this->actingAs($this->assignedJudge)
            ->postJson(route('attendance.scan', $indivSession), [
                'token' => $this->participant->qr_token,
            ]);

        $response2->assertStatus(409)
            ->assertJson([
                'success' => false,
                'code' => 'ALREADY_SCANNED',
            ]);
    }

    public function test_group_competition_requires_each_group_participant_to_check_in_and_enforces_quota(): void
    {
        // Buat lomba tipe kelompok (misal Pionering: 2 orang)
        $event = Event::first();
        $groupCompetition = Competition::create([
            'event_id' => $event->id,
            'title' => 'Pionering Meja Makan Darurat',
            'code' => 'PION-GRP',
            'category' => 'all',
            'participant_type' => 'group',
            'min_participants' => 2,
            'max_participants' => 2,
            'scoring_formula' => 'average',
            'is_published' => true,
        ]);

        $groupSchedule = CompetitionSchedule::create([
            'competition_id' => $groupCompetition->id,
            'session_name' => 'Sesi Siang',
            'arena_location' => 'Zona Barat',
            'participant_type' => 'group',
            'start_time' => now()->addMinutes(15),
            'end_time' => now()->addHours(3),
            'status' => 'scheduled',
            'is_published' => true,
        ]);

        // Tugaskan juri ke jadwal kelompok ini
        JudgeAssignment::create([
            'competition_schedule_id' => $groupSchedule->id,
            'user_id' => $this->assignedJudge->id,
            'role' => 'lead_judge',
            'is_confirmed' => true,
        ]);

        $groupSession = $groupSchedule->attendanceSessions()->first();
        $this->assertNotNull($groupSession);

        // Buat 3 anggota dalam regu yang sama
        $patrolId = $this->participant->patrol_id;
        $member1 = $this->participant; // PINRU (bisa ikut kelompok)
        $member2 = Participant::create([
            'patrol_id' => $patrolId,
            'full_name' => 'Peserta Kelompok Dua',
            'role_in_patrol' => \App\Enums\PatrolRoleEnum::Member,
            'gender' => 'male',
            'birth_place' => 'Padangsidimpuan',
            'birth_date' => '2011-04-12',
            'qr_token' => (string) Str::uuid(),
            'id_number' => 'ID-GRP-002',
        ]);
        $member3 = Participant::create([
            'patrol_id' => $patrolId,
            'full_name' => 'Peserta Kelompok Tiga',
            'role_in_patrol' => \App\Enums\PatrolRoleEnum::Member,
            'gender' => 'male',
            'birth_place' => 'Padangsidimpuan',
            'birth_date' => '2011-05-15',
            'qr_token' => (string) Str::uuid(),
            'id_number' => 'ID-GRP-003',
        ]);

        // 1. Peserta 1 scan via Juri -> Berhasil (1/2 hadir)
        $res1 = $this->actingAs($this->assignedJudge)
            ->postJson(route('attendance.scan', $groupSession), [
                'token' => $member1->qr_token,
            ]);
        $res1->assertOk()
            ->assertJson([
                'success' => true,
                'code' => 'RECORDED',
            ]);

        // 2. Peserta 1 scan ulang -> Ditolak (ALREADY_SCANNED perorangan)
        $resRepeat = $this->actingAs($this->assignedJudge)
            ->postJson(route('attendance.scan', $groupSession), [
                'token' => $member1->qr_token,
            ]);
        $resRepeat->assertStatus(409)
            ->assertJson([
                'success' => false,
                'code' => 'ALREADY_SCANNED',
            ]);

        // 3. Peserta 2 scan -> Berhasil (2/2 hadir - Kelompok Lengkap!)
        $res2 = $this->actingAs($this->assignedJudge)
            ->postJson(route('attendance.scan', $groupSession), [
                'token' => $member2->qr_token,
            ]);
        $res2->assertOk()
            ->assertJson([
                'success' => true,
                'code' => 'RECORDED',
            ]);

        // 4. Peserta 3 (anggota ke-3 dari regu yang sama) mencoba scan -> Ditolak karena kuota kelompok (2 orang) sudah penuh
        $res3 = $this->actingAs($this->assignedJudge)
            ->postJson(route('attendance.scan', $groupSession), [
                'token' => $member3->qr_token,
            ]);
        $res3->assertStatus(422)
            ->assertJson([
                'success' => false,
                'code' => 'GROUP_QUOTA_EXCEEDED',
            ]);
    }

    public function test_scan_fails_when_attendance_window_not_yet_open(): void
    {
        $competition = Competition::first();

        // Jadwal lomba 2 jam ke depan -> Presensi baru buka 90 menit lagi (H-30m)
        $futureSchedule = CompetitionSchedule::create([
            'competition_id' => $competition->id,
            'session_name' => 'Sesi Sore Mendatang',
            'arena_location' => 'Pos Lapangan',
            'start_time' => now()->addHours(2),
            'end_time' => now()->addHours(4),
            'status' => 'scheduled',
            'is_published' => true,
        ]);

        JudgeAssignment::create([
            'competition_schedule_id' => $futureSchedule->id,
            'user_id' => $this->assignedJudge->id,
            'role' => 'lead_judge',
            'is_confirmed' => true,
        ]);

        $session = $futureSchedule->attendanceSessions()->first();

        $response = $this->actingAs($this->assignedJudge)
            ->postJson(route('attendance.scan', $session), [
                'token' => $this->participant->qr_token,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'code' => 'SCHEDULE_NOT_OPEN',
            ]);
    }

    public function test_scan_fails_when_schedule_has_ended(): void
    {
        $competition = Competition::first();

        // Jadwal lomba yang sudah berakhir 1 jam lalu
        $pastSchedule = CompetitionSchedule::create([
            'competition_id' => $competition->id,
            'session_name' => 'Sesi Kemarin',
            'arena_location' => 'Pos Lapangan',
            'start_time' => now()->subHours(3),
            'end_time' => now()->subHour(),
            'status' => 'completed',
            'is_published' => true,
        ]);

        JudgeAssignment::create([
            'competition_schedule_id' => $pastSchedule->id,
            'user_id' => $this->assignedJudge->id,
            'role' => 'lead_judge',
            'is_confirmed' => true,
        ]);

        $session = $pastSchedule->attendanceSessions()->first();

        $response = $this->actingAs($this->assignedJudge)
            ->postJson(route('attendance.scan', $session), [
                'token' => $this->participant->qr_token,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'code' => 'SCHEDULE_CLOSED',
            ]);
    }

    public function test_scanner_view_blocks_camera_when_attendance_not_yet_open(): void
    {
        $competition = Competition::first();

        // Jadwal lomba besok -> presensi belum terbuka
        $futureSchedule = CompetitionSchedule::create([
            'competition_id' => $competition->id,
            'session_name' => 'Sesi Lomba Besok',
            'arena_location' => 'Pos Rimba',
            'start_time' => now()->addDay()->setTime(9, 0),
            'end_time' => now()->addDay()->setTime(12, 0),
            'status' => 'scheduled',
            'is_published' => true,
        ]);

        JudgeAssignment::create([
            'competition_schedule_id' => $futureSchedule->id,
            'user_id' => $this->assignedJudge->id,
            'role' => 'lead_judge',
            'is_confirmed' => true,
        ]);

        $session = $futureSchedule->attendanceSessions()->first();

        $response = $this->actingAs($this->assignedJudge)
            ->get(route('attendance.scanner', $session));

        $response->assertOk()
            ->assertSee('Kamera Scanner Diblokir')
            ->assertSee('Presensi Belum Terbuka')
            ->assertSee('Jadwal Sesi Lomba:')
            ->assertSee('Presensi Dibuka Paling Cepat:')
            ->assertSee('WIB')
            ->assertDontSee('<div id="qr-reader"', false);
    }

    public function test_boys_patrol_cannot_scan_into_girls_schedule_session(): void
    {
        $competition = Competition::first();

        // Jadwal khusus Putri
        $girlsSchedule = CompetitionSchedule::create([
            'competition_id' => $competition->id,
            'session_name' => 'Sesi Khusus Putri',
            'gender_category' => 'girls',
            'participant_type' => 'patrol',
            'arena_location' => 'Pos Rimba Putri',
            'start_time' => now()->addMinutes(10),
            'end_time' => now()->addHours(2),
            'status' => 'scheduled',
            'is_published' => true,
        ]);

        JudgeAssignment::create([
            'competition_schedule_id' => $girlsSchedule->id,
            'user_id' => $this->assignedJudge->id,
            'role' => 'lead_judge',
            'is_confirmed' => true,
        ]);

        $session = $girlsSchedule->attendanceSessions()->first();

        // $this->participant terdaftar di regu Putra (Rajawali)
        $response = $this->actingAs($this->assignedJudge)
            ->postJson(route('attendance.scan', $session), [
                'token' => $this->participant->qr_token,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'code' => 'CATEGORY_MISMATCH',
            ]);

        $this->assertStringContainsString('khusus bagi Regu Putri', $response->json('message'));
        $this->assertStringContainsString('Regu Putra', $response->json('message'));
        $this->assertEquals(0, Attendance::where('attendance_session_id', $session->id)->count());
    }

    public function test_girls_patrol_cannot_scan_into_boys_schedule_session(): void
    {
        $competition = Competition::first();

        // Jadwal khusus Putra
        $boysSchedule = CompetitionSchedule::create([
            'competition_id' => $competition->id,
            'session_name' => 'Sesi Khusus Putra',
            'gender_category' => 'boys',
            'participant_type' => 'patrol',
            'arena_location' => 'Pos Rimba Putra',
            'start_time' => now()->addMinutes(10),
            'end_time' => now()->addHours(2),
            'status' => 'scheduled',
            'is_published' => true,
        ]);

        JudgeAssignment::create([
            'competition_schedule_id' => $boysSchedule->id,
            'user_id' => $this->assignedJudge->id,
            'role' => 'lead_judge',
            'is_confirmed' => true,
        ]);

        $session = $boysSchedule->attendanceSessions()->first();

        // Buat Regu Putri & Peserta Putri
        $scoutGroup = ScoutGroup::first();
        $girlsPatrol = Patrol::create([
            'scout_group_id' => $scoutGroup->id,
            'patrol_name' => 'Melati',
            'gender_category' => \App\Enums\PatrolGenderEnum::Girls,
            'group_number' => '01.002',
        ]);

        $girlParticipant = Participant::create([
            'patrol_id' => $girlsPatrol->id,
            'full_name' => 'Siti Aminah',
            'gender' => 'female',
            'role_in_patrol' => \App\Enums\PatrolRoleEnum::PatrolLeader,
            'birth_place' => 'Padangsidimpuan',
            'birth_date' => '2012-05-10',
            'qr_token' => (string) Str::uuid(),
            'status' => 'verified',
        ]);

        $response = $this->actingAs($this->assignedJudge)
            ->postJson(route('attendance.scan', $session), [
                'token' => $girlParticipant->qr_token,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'code' => 'CATEGORY_MISMATCH',
            ]);

        $this->assertStringContainsString('khusus bagi Regu Putra', $response->json('message'));
        $this->assertStringContainsString('Regu Putri', $response->json('message'));
        $this->assertEquals(0, Attendance::where('attendance_session_id', $session->id)->count());
    }

    public function test_boys_patrol_can_scan_into_boys_schedule_session(): void
    {
        $competition = Competition::first();

        $boysSchedule = CompetitionSchedule::create([
            'competition_id' => $competition->id,
            'session_name' => 'Sesi Lomba Khusus Putra',
            'gender_category' => 'boys',
            'participant_type' => 'patrol',
            'arena_location' => 'Pos Rimba Putra',
            'start_time' => now()->addMinutes(10),
            'end_time' => now()->addHours(2),
            'status' => 'scheduled',
            'is_published' => true,
        ]);

        JudgeAssignment::create([
            'competition_schedule_id' => $boysSchedule->id,
            'user_id' => $this->assignedJudge->id,
            'role' => 'lead_judge',
            'is_confirmed' => true,
        ]);

        $session = $boysSchedule->attendanceSessions()->first();

        $response = $this->actingAs($this->assignedJudge)
            ->postJson(route('attendance.scan', $session), [
                'token' => $this->participant->qr_token,
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertEquals(1, Attendance::where('attendance_session_id', $session->id)->count());
    }
}

<?php

namespace Tests\Feature;

use App\Enums\PatrolGenderEnum;
use App\Enums\PatrolRoleEnum;
use App\Enums\RoleEnum;
use App\Livewire\Portal\AttendanceMatrix;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Chaperone;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use App\Models\User;
use Database\Seeders\EventSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AttendanceMatrixTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $committee;

    protected User $scoutUser;

    protected ScoutGroup $scoutGroup;

    protected Patrol $patrolBoys;

    protected Patrol $patrolGirls;

    protected Participant $participantBoys;

    protected Chaperone $chaperone;

    protected AttendanceSession $session1;

    protected AttendanceSession $session2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(EventSeeder::class);

        $this->admin = User::factory()->create(['is_active' => true]);
        $this->admin->assignRole(RoleEnum::Admin);

        $this->committee = User::factory()->create(['is_active' => true]);
        $this->committee->assignRole(RoleEnum::Committee);

        $this->scoutUser = User::factory()->create(['is_active' => true]);
        $this->scoutUser->assignRole(RoleEnum::ScoutGroup);

        $this->scoutGroup = ScoutGroup::create([
            'user_id' => $this->scoutUser->id,
            'registration_code' => 'GDP-TEST-01',
            'school_name' => 'SMP Negeri 1 Padangsidimpuan',
            'group_number' => '01-001 & 01-002',
            'kwarran_name' => 'Padangsidimpuan Utara',
            'address' => 'Jl. Teuku Umar No. 12',
            'contact_person' => 'Kak Pembina',
            'contact_phone' => '081234567890',
        ]);

        $this->patrolBoys = Patrol::create([
            'scout_group_id' => $this->scoutGroup->id,
            'patrol_name' => 'Rajawali',
            'npp' => 'PA-01',
            'gender_category' => PatrolGenderEnum::Boys,
        ]);

        $this->patrolGirls = Patrol::create([
            'scout_group_id' => $this->scoutGroup->id,
            'patrol_name' => 'Melati',
            'npp' => 'PI-01',
            'gender_category' => PatrolGenderEnum::Girls,
        ]);

        $this->participantBoys = Participant::factory()->create([
            'patrol_id' => $this->patrolBoys->id,
            'full_name' => 'Ahmad Fadhil',
            'gender' => 'male',
            'id_number' => 'ID-PST-001',
            'role_in_patrol' => PatrolRoleEnum::PatrolLeader,
        ]);

        $this->chaperone = Chaperone::factory()->create([
            'scout_group_id' => $this->scoutGroup->id,
            'full_name' => 'Kak Pembina',
            'gender' => 'male',
            'id_number' => 'ID-CHP-001',
        ]);

        $this->session1 = AttendanceSession::create([
            'title' => 'Sesi Apel Pagi Hari 1',
            'session_type' => 'participant',
            'is_competition' => false,
            'opened_at' => now()->subHour(),
            'closed_at' => now()->addHours(2),
            'is_active' => true,
        ]);

        $this->session2 = AttendanceSession::create([
            'title' => 'Sesi Penjelajahan Rimba',
            'session_type' => 'participant',
            'is_competition' => false,
            'opened_at' => now()->subHour(),
            'closed_at' => now()->addHours(2),
            'is_active' => true,
        ]);
    }

    /**
     * Memastikan tamu (guest) diarahkan ke halaman login
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('attendance.matrix'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Memastikan non-admin (misal panitia atau gugus depan) dilarang mengakses matriks
     */
    public function test_non_admin_cannot_access_attendance_matrix(): void
    {
        $this->actingAs($this->committee)
            ->get(route('attendance.matrix'))
            ->assertForbidden();

        $this->actingAs($this->scoutUser)
            ->get(route('attendance.matrix'))
            ->assertForbidden();
    }

    /**
     * Memastikan administrator dapat membuka matriks penyesuaian presensi
     */
    public function test_admin_can_access_attendance_matrix(): void
    {
        $this->actingAs($this->admin)
            ->get(route('attendance.matrix'))
            ->assertOk()
            ->assertSee('Matriks Penyesuaian Presensi');

        Livewire::actingAs($this->admin)
            ->test(AttendanceMatrix::class)
            ->assertOk()
            ->assertSee('Rajawali')
            ->assertSee('Melati')
            ->assertSee('Sesi Apel Pagi Hari 1');
    }

    /**
     * Memastikan administrator dapat mengganti scope (Per Regu, Per Peserta, Pembina)
     */
    public function test_admin_can_switch_scopes_in_matrix(): void
    {
        Livewire::actingAs($this->admin)
            ->test(AttendanceMatrix::class)
            ->assertSet('scope', 'patrol')
            ->assertSee('Rajawali')
            ->set('scope', 'participant')
            ->assertSet('scope', 'participant')
            ->assertSee('Ahmad Fadhil')
            ->set('scope', 'chaperone')
            ->assertSet('scope', 'chaperone')
            ->assertSee('Kak Pembina');
    }

    /**
     * Memastikan admin dapat menandai status secara cepat (1-klik quickSetStatus)
     */
    public function test_admin_can_quick_set_status_on_cell(): void
    {
        Livewire::actingAs($this->admin)
            ->test(AttendanceMatrix::class)
            ->call('quickSetStatus', (string) $this->patrolBoys->id, (string) $this->session1->id, 'present');

        $this->assertDatabaseHas('attendances', [
            'attendance_session_id' => $this->session1->id,
            'patrol_id' => $this->patrolBoys->id,
            'status' => 'present',
        ]);
    }

    /**
     * Memastikan admin dapat membuka modal sel dan menyimpan penyesuaian status, catatan, dan waktu
     */
    public function test_admin_can_open_cell_modal_and_save_adjustment(): void
    {
        Livewire::actingAs($this->admin)
            ->test(AttendanceMatrix::class)
            ->call('openCellModal', (string) $this->patrolBoys->id, (string) $this->session1->id)
            ->assertSet('showCellModal', true)
            ->assertSet('cellEntityName', 'Rajawali')
            ->set('cellStatus', 'late')
            ->set('cellNotes', 'Terlambat karena kendala perlengkapan')
            ->call('saveCellAdjustment')
            ->assertSet('showCellModal', false);

        $this->assertDatabaseHas('attendances', [
            'attendance_session_id' => $this->session1->id,
            'patrol_id' => $this->patrolBoys->id,
            'status' => 'late',
            'notes' => 'Terlambat karena kendala perlengkapan',
        ]);
    }

    /**
     * Memastikan admin dapat menghapus catatan presensi (mereset ke status belum presensi)
     */
    public function test_admin_can_delete_attendance_via_cell_modal(): void
    {
        // Buat presensi awal
        Attendance::create([
            'attendance_session_id' => $this->session1->id,
            'entity_type' => 'participant',
            'patrol_id' => $this->patrolBoys->id,
            'scanned_at' => now(),
            'status' => 'present',
        ]);

        Livewire::actingAs($this->admin)
            ->test(AttendanceMatrix::class)
            ->call('openCellModal', (string) $this->patrolBoys->id, (string) $this->session1->id)
            ->assertSet('cellHasRecord', true)
            ->call('deleteCellAttendance')
            ->assertSet('showCellModal', false);

        $this->assertDatabaseMissing('attendances', [
            'attendance_session_id' => $this->session1->id,
            'patrol_id' => $this->patrolBoys->id,
        ]);
    }

    /**
     * Memastikan penyesuaian massal baris terpilih (batch rows) berhasil
     */
    public function test_admin_can_batch_adjust_selected_rows(): void
    {
        Livewire::actingAs($this->admin)
            ->test(AttendanceMatrix::class)
            ->set('selectedEntityIds', [(string) $this->patrolBoys->id, (string) $this->patrolGirls->id])
            ->call('openBatchModal')
            ->assertSet('showBatchModal', true)
            ->set('batchTargetSessionId', (string) $this->session2->id)
            ->set('batchStatus', 'present')
            ->set('batchNotes', 'Penyesuaian massal regu putra & putri')
            ->call('executeBatchAdjustment')
            ->assertSet('showBatchModal', false)
            ->assertSet('selectedEntityIds', []);

        $this->assertDatabaseHas('attendances', [
            'attendance_session_id' => $this->session2->id,
            'patrol_id' => $this->patrolBoys->id,
            'status' => 'present',
        ]);

        $this->assertDatabaseHas('attendances', [
            'attendance_session_id' => $this->session2->id,
            'patrol_id' => $this->patrolGirls->id,
            'status' => 'present',
        ]);
    }

    /**
     * Memastikan tindakan massal seluruh kolom sesi (session bulk action) berhasil
     */
    public function test_admin_can_bulk_adjust_entire_session_column(): void
    {
        Livewire::actingAs($this->admin)
            ->test(AttendanceMatrix::class)
            ->call('openSessionBulkModal', (string) $this->session1->id)
            ->assertSet('showSessionBulkModal', true)
            ->set('sessionBulkStatus', 'present')
            ->set('sessionBulkOnlyUnrecorded', true)
            ->call('executeSessionBulkAction')
            ->assertSet('showSessionBulkModal', false);

        // Seluruh regu otomatis tercatat hadir pada sesi 1
        $this->assertDatabaseHas('attendances', [
            'attendance_session_id' => $this->session1->id,
            'patrol_id' => $this->patrolBoys->id,
            'status' => 'present',
        ]);

        $this->assertDatabaseHas('attendances', [
            'attendance_session_id' => $this->session1->id,
            'patrol_id' => $this->patrolGirls->id,
            'status' => 'present',
        ]);
    }

    /**
     * Memastikan admin dapat melihat dan menyesuaikan presensi Panitia Pelaksana
     */
    public function test_admin_can_adjust_committee_attendance(): void
    {
        $committeeUser = User::factory()->create([
            'name' => 'Kak Panitia Lapangan',
            'is_active' => true,
            'committee_position' => 'Koordinator Lapangan',
            'id_number' => 'A-990001',
        ]);
        $committeeUser->assignRole(RoleEnum::Committee);

        $committeeSession = AttendanceSession::create([
            'title' => 'Briefing Panitia Harian',
            'session_type' => 'committee',
            'is_competition' => false,
            'is_active' => true,
            'opened_at' => now()->subHour(),
            'closed_at' => now()->addHours(2),
        ]);

        Livewire::actingAs($this->admin)
            ->test(AttendanceMatrix::class)
            ->set('scope', 'committee')
            ->assertSee('Kak Panitia Lapangan')
            ->assertSee('Briefing Panitia Harian')
            ->call('quickSetStatus', (string) $committeeUser->id, (string) $committeeSession->id, 'present');

        $this->assertDatabaseHas('attendances', [
            'attendance_session_id' => $committeeSession->id,
            'user_id' => $committeeUser->id,
            'status' => 'present',
            'entity_type' => 'user',
        ]);

        // Uji perubahan status lewat Cell Modal
        Livewire::actingAs($this->admin)
            ->test(AttendanceMatrix::class)
            ->set('scope', 'committee')
            ->call('openCellModal', (string) $committeeUser->id, (string) $committeeSession->id)
            ->assertSet('showCellModal', true)
            ->assertSet('cellEntityName', 'Kak Panitia Lapangan')
            ->set('cellStatus', 'late')
            ->set('cellNotes', 'Menyiapkan tenda panitia')
            ->call('saveCellAdjustment')
            ->assertSet('showCellModal', false);

        $this->assertDatabaseHas('attendances', [
            'attendance_session_id' => $committeeSession->id,
            'user_id' => $committeeUser->id,
            'status' => 'late',
            'notes' => 'Menyiapkan tenda panitia',
        ]);
    }

    /**
     * Memastikan admin dapat melihat dan menyesuaikan presensi Dewan Juri
     */
    public function test_admin_can_adjust_judge_attendance(): void
    {
        $judgeUser = User::factory()->create([
            'name' => 'Kak Dewan Juri Utama',
            'is_active' => true,
            'position' => 'Juri PBB & Semaphore',
            'institution' => 'Kwarcab Padangsidimpuan',
            'id_number' => 'A-880001',
        ]);
        $judgeUser->assignRole(RoleEnum::Judge);

        $judgeSession = AttendanceSession::create([
            'title' => 'Briefing & Kalibrasi Penilaian Juri',
            'session_type' => 'judge',
            'is_competition' => false,
            'is_active' => true,
            'opened_at' => now()->subHour(),
            'closed_at' => now()->addHours(2),
        ]);

        Livewire::actingAs($this->admin)
            ->test(AttendanceMatrix::class)
            ->set('scope', 'judge')
            ->assertSee('Kak Dewan Juri Utama')
            ->assertSee('Briefing & Kalibrasi Penilaian Juri')
            ->call('quickSetStatus', (string) $judgeUser->id, (string) $judgeSession->id, 'present');

        $this->assertDatabaseHas('attendances', [
            'attendance_session_id' => $judgeSession->id,
            'user_id' => $judgeUser->id,
            'status' => 'present',
            'entity_type' => 'user',
        ]);

        // Uji penyesuaian massal sesi untuk juri
        Livewire::actingAs($this->admin)
            ->test(AttendanceMatrix::class)
            ->set('scope', 'judge')
            ->call('openSessionBulkModal', (string) $judgeSession->id)
            ->assertSet('showSessionBulkModal', true)
            ->set('sessionBulkStatus', 'present')
            ->set('sessionBulkOnlyUnrecorded', false)
            ->call('executeSessionBulkAction')
            ->assertSet('showSessionBulkModal', false);

        $this->assertDatabaseHas('attendances', [
            'attendance_session_id' => $judgeSession->id,
            'user_id' => $judgeUser->id,
            'status' => 'present',
        ]);
    }
}

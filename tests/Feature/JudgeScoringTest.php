<?php

namespace Tests\Feature;

use App\Enums\PatrolGenderEnum;
use App\Enums\RegistrationStatusEnum;
use App\Livewire\Portal\JudgeCockpit;
use App\Livewire\Portal\JudgeScoring;
use App\Models\Assessment;
use App\Models\AssessmentCriterion;
use App\Models\AuditLog;
use App\Models\Competition;
use App\Models\CompetitionSchedule;
use App\Models\CompetitionScore;
use App\Models\JudgeAssignment;
use App\Models\Patrol;
use App\Models\Role;
use App\Models\ScoutGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class JudgeScoringTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $judge1;
    protected User $judge2;
    protected User $unassignedJudge;
    protected User $scoutUser;
    protected ScoutGroup $scoutGroup;
    protected Patrol $patrol1;
    protected Patrol $patrol2;
    protected Competition $competition;
    protected CompetitionSchedule $schedule;
    protected AssessmentCriterion $criterion1;
    protected AssessmentCriterion $criterion2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        $adminRole = Role::where('name', 'admin')->first();
        $judgeRole = Role::where('name', 'judge')->first();
        $scoutRole = Role::where('name', 'scout_group')->first();

        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($adminRole);

        $this->judge1 = User::factory()->create(['name' => 'Kak Juri Satu']);
        $this->judge1->roles()->attach($judgeRole);

        $this->judge2 = User::factory()->create(['name' => 'Kak Juri Dua']);
        $this->judge2->roles()->attach($judgeRole);

        $this->unassignedJudge = User::factory()->create(['name' => 'Kak Juri Lepas']);
        $this->unassignedJudge->roles()->attach($judgeRole);

        $this->scoutUser = User::factory()->create();
        $this->scoutUser->roles()->attach($scoutRole);

        $this->scoutGroup = ScoutGroup::create([
            'user_id' => $this->scoutUser->id,
            'registration_code' => 'GDP-TEST-01',
            'school_name' => 'SMP Negeri 1 Padangsidimpuan',
            'group_number' => '01-001 & 01-002',
            'address' => 'Jl. Sudirman No. 10',
            'contact_person' => 'Pembina',
            'contact_phone' => '081234567890',
            'verification_status' => RegistrationStatusEnum::Verified,
        ]);

        $this->patrol1 = Patrol::create([
            'scout_group_id' => $this->scoutGroup->id,
            'patrol_name' => 'Rajawali',
            'gender_category' => PatrolGenderEnum::Boys,
            'status' => RegistrationStatusEnum::Verified,
            'draw_number' => 1,
        ]);

        $this->patrol2 = Patrol::create([
            'scout_group_id' => $this->scoutGroup->id,
            'patrol_name' => 'Garuda',
            'gender_category' => PatrolGenderEnum::Boys,
            'status' => RegistrationStatusEnum::Verified,
            'draw_number' => 2,
        ]);

        $this->competition = Competition::create([
            'code' => 'PBB-01',
            'title' => 'PBB Berisyarat dan Formasi Barisan',
            'category' => 'all',
            'participant_type' => 'patrol',
            'scoring_formula' => 'sum',
            'is_published' => true,
        ]);

        $this->criterion1 = AssessmentCriterion::create([
            'competition_id' => $this->competition->id,
            'name' => 'Kerapian & Keseragaman Gerakan',
            'code' => 'KRP-01',
            'min_score' => 50.00,
            'max_score' => 100.00,
            'weight_percentage' => 40.00,
            'order_index' => 1,
        ]);

        $this->criterion2 = AssessmentCriterion::create([
            'competition_id' => $this->competition->id,
            'name' => 'Ketepatan Waktu & Kekompakan',
            'code' => 'WKT-02',
            'min_score' => 50.00,
            'max_score' => 100.00,
            'weight_percentage' => 60.00,
            'order_index' => 2,
        ]);

        $this->schedule = CompetitionSchedule::create([
            'competition_id' => $this->competition->id,
            'session_name' => 'Sesi A - Lapangan Utama',
            'gender_category' => 'boys',
            'arena_location' => 'Lapangan Alun-Alun',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHours(2),
            'status' => 'in_progress',
            'is_published' => true,
        ]);

        // Tugaskan Juri 1 dan Juri 2 ke Jadwal ini
        JudgeAssignment::create([
            'competition_schedule_id' => $this->schedule->id,
            'user_id' => $this->judge1->id,
            'role' => 'lead_judge',
            'is_confirmed' => true,
        ]);

        JudgeAssignment::create([
            'competition_schedule_id' => $this->schedule->id,
            'user_id' => $this->judge2->id,
            'role' => 'scoring_judge',
            'is_confirmed' => true,
        ]);
    }

    /**
     * Memastikan guest diarahkan ke halaman login saat membuka cockpit juri
     */
    public function test_guest_is_redirected_from_judge_cockpit(): void
    {
        $response = $this->get(route('judge.index'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Memastikan juri yang tidak ditugaskan ditolak saat mengakses scoring sheet sesi tersebut
     */
    public function test_unassigned_judge_is_forbidden_from_scoring_sheet(): void
    {
        $response = $this->actingAs($this->unassignedJudge)->get(route('judge.scoring', $this->schedule->id));
        $response->assertForbidden();
    }

    /**
     * Memastikan juri yang ditugaskan dapat membuka cockpit dan lembar penilaian
     */
    public function test_assigned_judge_can_access_cockpit_and_view_blind_scoring_roster(): void
    {
        $response = $this->actingAs($this->judge1)->get(route('judge.index'));
        $response->assertOk();
        $response->assertSee('Cockpit Penjurian');
        $response->assertSee('PBB Berisyarat dan Formasi Barisan');

        // Buka lembar scoring juri
        $responseScoring = $this->actingAs($this->judge1)->get(route('judge.scoring', $this->schedule->id));
        $responseScoring->assertOk();
        $responseScoring->assertSee('Nomor Undian #01');
        $responseScoring->assertSee('UND-01');

        // PROTOKOL BLIND SCORING: Nama sekolah asli dan nama regu asli TIDAK BOLEH muncul pada tampilan juri!
        $responseScoring->assertDontSee('SMP Negeri 1 Padangsidimpuan');
        $responseScoring->assertDontSee('Rajawali');
    }

    /**
     * Memastikan juri dapat menyimpan draf penilaian kriteria dengan bobot terhitung
     */
    public function test_judge_can_save_draft_score(): void
    {
        // Kriteria 1: 80 (Bobot 40% = 32), Kriteria 2: 90 (Bobot 60% = 54) -> Total = 86.00
        Livewire::actingAs($this->judge1, 'web')
            ->test(JudgeScoring::class, ['scheduleId' => $this->schedule->id])
            ->set('selectedPatrolId', $this->patrol1->id)
            ->set('criteriaScores.' . $this->criterion1->id, 80.00)
            ->set('criteriaScores.' . $this->criterion2->id, 90.00)
            ->set('notes', 'Langkah tegap sempurna.')
            ->call('saveDraft')
            ->assertDispatched('alert');

        $assessment = Assessment::where('competition_schedule_id', $this->schedule->id)
            ->where('judge_id', $this->judge1->id)
            ->where('patrol_id', $this->patrol1->id)
            ->first();

        $this->assertNotNull($assessment);
        $this->assertEquals('draft', $assessment->status);
        $this->assertFalse($assessment->is_locked);
        $this->assertEquals(86.00, (float) $assessment->final_score);
        $this->assertEquals('Langkah tegap sempurna.', $assessment->notes);

        // Periksa audit log pencatatan draf
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'score_draft_created',
            'model_id' => $assessment->id,
            'user_id' => $this->judge1->id,
        ]);
    }

    /**
     * Memastikan juri dapat memfinalisasi dan mengunci nilai resmi
     */
    public function test_judge_can_finalize_and_lock_score(): void
    {
        Livewire::actingAs($this->judge1, 'web')
            ->test(JudgeScoring::class, ['scheduleId' => $this->schedule->id])
            ->set('selectedPatrolId', $this->patrol1->id)
            ->set('criteriaScores.' . $this->criterion1->id, 85.00)
            ->set('criteriaScores.' . $this->criterion2->id, 95.00)
            ->call('finalize')
            ->assertDispatched('alert');

        $assessment = Assessment::where('competition_schedule_id', $this->schedule->id)
            ->where('judge_id', $this->judge1->id)
            ->where('patrol_id', $this->patrol1->id)
            ->first();

        $this->assertEquals('finalized', $assessment->status);
        $this->assertTrue($assessment->is_locked);
        $this->assertNotNull($assessment->finalized_at);

        // Periksa audit log finalisasi
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'score_finalized',
            'model_id' => $assessment->id,
            'user_id' => $this->judge1->id,
        ]);

        // Periksa skor otomatis terakumulasi ke CompetitionScore
        $compScore = CompetitionScore::where('competition_id', $this->competition->id)
            ->where('patrol_id', $this->patrol1->id)
            ->first();

        $this->assertNotNull($compScore);
        $this->assertEquals(91.00, (float) $compScore->final_score); // (85*0.4) + (95*0.6) = 34 + 57 = 91.00
    }

    /**
     * Memastikan administrator dapat membuka kunci nilai dengan catatan alasan
     */
    public function test_admin_can_unlock_finalized_score_with_reason_audit(): void
    {
        // 1. Buat penilaian terkunci
        $assessment = Assessment::create([
            'competition_id' => $this->competition->id,
            'competition_schedule_id' => $this->schedule->id,
            'patrol_id' => $this->patrol1->id,
            'judge_id' => $this->judge1->id,
            'criteria_scores' => [
                $this->criterion1->id => 80.00,
                $this->criterion2->id => 80.00,
            ],
            'raw_total_score' => 160.00,
            'final_score' => 80.00,
            'status' => 'finalized',
            'is_locked' => true,
            'finalized_at' => now(),
        ]);

        // 2. Administrator membuka kunci
        Livewire::actingAs($this->admin, 'web')
            ->test(JudgeScoring::class, ['scheduleId' => $this->schedule->id])
            ->set('selectedPatrolId', $this->patrol1->id)
            ->set('unlockReason', 'Revisi kriteria kerapian berdasarkan verifikasi panitia pos')
            ->call('unlock')
            ->assertDispatched('alert');

        $assessment->refresh();
        $this->assertEquals('draft', $assessment->status);
        $this->assertFalse($assessment->is_locked);

        // Audit log harus mencatat pembukaan kunci beserta alasan
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'score_unlocked',
            'model_id' => $assessment->id,
            'user_id' => $this->admin->id,
            'reason' => 'Revisi kriteria kerapian berdasarkan verifikasi panitia pos',
        ]);
    }

    /**
     * Memastikan kalkulasi akumulasi penjumlahan skor multi-juri (Sum Scoring) bekerja akurat
     */
    public function test_multi_judge_sum_scoring_aggregation(): void
    {
        // Juri 1 memberi nilai 90.00 (terfinalisasi)
        Livewire::actingAs($this->judge1, 'web')
            ->test(JudgeScoring::class, ['scheduleId' => $this->schedule->id])
            ->set('selectedPatrolId', $this->patrol1->id)
            ->set('criteriaScores.' . $this->criterion1->id, 90.00)
            ->set('criteriaScores.' . $this->criterion2->id, 90.00)
            ->call('finalize');

        // Juri 2 memberi nilai 85.00 (terfinalisasi)
        Livewire::actingAs($this->judge2, 'web')
            ->test(JudgeScoring::class, ['scheduleId' => $this->schedule->id])
            ->set('selectedPatrolId', $this->patrol1->id)
            ->set('criteriaScores.' . $this->criterion1->id, 85.00)
            ->set('criteriaScores.' . $this->criterion2->id, 85.00)
            ->call('finalize');

        $compScore = CompetitionScore::where('competition_id', $this->competition->id)
            ->where('patrol_id', $this->patrol1->id)
            ->first();

        $this->assertNotNull($compScore);
        // Sum scoring formula: Juri 1 (90.00) + Juri 2 (85.00) = 175.00
        $this->assertEquals(175.00, (float) $compScore->final_score);
    }

    /**
     * Memastikan non-admin dilarang mengakses halaman audit penilaian
     */
    public function test_non_admin_is_forbidden_from_assessments_audit_page(): void
    {
        $response = $this->actingAs($this->judge1)->get(route('admin.assessments.index'));
        $response->assertForbidden();
    }

    /**
     * Memastikan administrator dapat mengakses halaman audit penilaian dan melihat riwayat log
     */
    public function test_admin_can_access_assessments_audit_and_view_logs(): void
    {
        $assessment = Assessment::create([
            'competition_id' => $this->competition->id,
            'competition_schedule_id' => $this->schedule->id,
            'patrol_id' => $this->patrol1->id,
            'judge_id' => $this->judge1->id,
            'criteria_scores' => [$this->criterion1->id => 90],
            'raw_total_score' => 90.00,
            'final_score' => 90.00,
            'status' => 'finalized',
            'is_locked' => true,
        ]);

        AuditLog::log(
            action: 'score_finalized',
            modelType: Assessment::class,
            modelId: $assessment->id,
            reason: 'Uji log audit',
            userId: $this->judge1->id
        );

        $response = $this->actingAs($this->admin)->get(route('admin.assessments.index'));
        $response->assertOk();
        $response->assertSee('Audit Penilaian & Log Sistem');
        $response->assertSee('Rajawali');
        $response->assertSee('SMP Negeri 1 Padangsidimpuan');

        // Beralih ke tab riwayat audit log
        Livewire::actingAs($this->admin, 'web')
            ->test(\App\Livewire\Admin\AssessmentsAudit::class)
            ->set('activeTab', 'audit_logs')
            ->assertSee('score_finalized')
            ->assertSee('Uji log audit');
    }

    /**
     * Memastikan Administrator dilarang keras memberikan nilai atau memfinalisasi nilai
     */
    public function test_admin_strictly_cannot_give_or_finalize_scores(): void
    {
        // 1. Tampilan Livewire JudgeScoring untuk Administrator berada dalam mode read-only
        $component = Livewire::actingAs($this->admin, 'web')
            ->test(JudgeScoring::class, ['scheduleId' => $this->schedule->id])
            ->assertSet('isAssignedJudge', false)
            ->assertSee('MODE TINJAU AUDIT ADMINISTRATOR (READ-ONLY)')
            ->assertDontSee('Simpan Draf')
            ->assertDontSee('Kunci & Finalisasi Nilai');

        // 2. Administrator mencoba memanggil saveDraft via Livewire
        $component->call('saveDraft')
            ->assertDispatched('alert');

        $this->assertDatabaseMissing('assessments', [
            'competition_schedule_id' => $this->schedule->id,
            'judge_id' => $this->admin->id,
        ]);

        // 3. Administrator mencoba memanggil finalize via Livewire
        $component->call('finalize')
            ->assertDispatched('alert');

        // 4. Memastikan service backend melempar InvalidArgumentException jika dicoba oleh Administrator
        $scoringService = app(\App\Services\AssessmentScoringService::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Administrator tidak diperkenankan memberikan penilaian lomba.');

        $scoringService->saveDraft([
            'competition_schedule_id' => $this->schedule->id,
            'patrol_id' => $this->patrol1->id,
            'criteria_scores' => [$this->criterion1->id => 90],
        ], $this->admin);
    }

    /**
     * Memastikan juri yang tidak ditugaskan pada jadwal dilarang memberikan nilai
     */
    public function test_unassigned_judge_cannot_give_scores(): void
    {
        $scoringService = app(\App\Services\AssessmentScoringService::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Pemberian nilai hanya dapat dilakukan oleh Juri yang telah ditunjuk pada sesi lomba ini.');

        $scoringService->saveDraft([
            'competition_schedule_id' => $this->schedule->id,
            'patrol_id' => $this->patrol1->id,
            'criteria_scores' => [$this->criterion1->id => 90],
        ], $this->unassignedJudge);
    }

    /**
     * Penilaian hanya dapat dilakukan sejak jadwal dimulai hingga 30 menit setelah berakhir
     */
    public function test_scoring_window_state_follows_start_and_end_plus_30_minutes(): void
    {
        $start = $this->schedule->start_time->copy();
        $end = $this->schedule->end_time->copy();

        $this->assertSame('upcoming', $this->schedule->scoringState($start->copy()->subSecond()));
        $this->assertSame('open', $this->schedule->scoringState($start->copy()));
        $this->assertSame('open', $this->schedule->scoringState($end->copy()->addMinutes(30)));
        $this->assertSame('closed', $this->schedule->scoringState($end->copy()->addMinutes(30)->addSecond()));
        $this->assertNull($this->schedule->scoringClosedMessage($start->copy()->addMinute()));
        $this->assertStringContainsString('setelah jadwal dimulai', $this->schedule->scoringClosedMessage($start->copy()->subMinute()));
        $this->assertStringContainsString('telah ditutup', $this->schedule->scoringClosedMessage($end->copy()->addHour()));
    }

    public function test_judge_cannot_save_or_finalize_before_schedule_starts(): void
    {
        $this->schedule->update(['start_time' => now()->addHour(), 'end_time' => now()->addHours(3)]);

        Livewire::actingAs($this->judge1)
            ->test(JudgeScoring::class, ['scheduleId' => $this->schedule->id])
            ->set('selectedPatrolId', $this->patrol1->id)
            ->call('saveDraft')
            ->assertDispatched('alert')
            ->call('openFinalizeModal')
            ->assertSet('isFinalizeModalOpen', false)
            ->assertSee('Penilaian baru dapat dilakukan setelah jadwal dimulai');

        $this->assertSame(0, Assessment::where('competition_schedule_id', $this->schedule->id)->count());
    }

    public function test_judge_cannot_score_after_30_minutes_past_schedule_end(): void
    {
        $this->schedule->update(['start_time' => now()->subHours(3), 'end_time' => now()->subMinutes(31)]);

        Livewire::actingAs($this->judge1)
            ->test(JudgeScoring::class, ['scheduleId' => $this->schedule->id])
            ->set('selectedPatrolId', $this->patrol1->id)
            ->call('saveDraft')
            ->assertSee('Penilaian telah ditutup');

        $this->assertSame(0, Assessment::where('competition_schedule_id', $this->schedule->id)->count());

        // Dalam toleransi 30 menit setelah berakhir masih dapat dinilai
        $this->schedule->update(['end_time' => now()->subMinutes(29)]);

        Livewire::actingAs($this->judge1)
            ->test(JudgeScoring::class, ['scheduleId' => $this->schedule->id])
            ->set('selectedPatrolId', $this->patrol1->id)
            ->call('saveDraft');

        $this->assertSame(1, Assessment::where('competition_schedule_id', $this->schedule->id)->count());
    }

    public function test_service_rejects_scoring_outside_window(): void
    {
        $this->schedule->update(['start_time' => now()->addHour(), 'end_time' => now()->addHours(3)]);

        $this->expectException(\InvalidArgumentException::class);

        app(\App\Services\AssessmentScoringService::class)->saveDraft([
            'competition_schedule_id' => $this->schedule->id,
            'patrol_id' => $this->patrol1->id,
            'criteria_scores' => [$this->criterion1->id => 80, $this->criterion2->id => 90],
        ], $this->judge1);
    }
}

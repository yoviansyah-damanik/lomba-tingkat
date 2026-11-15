<?php

namespace Tests\Feature;

use App\Enums\PatrolGenderEnum;
use App\Enums\RegistrationStatusEnum;
use App\Livewire\Admin\AssessmentsAudit;
use App\Livewire\Portal\JudgeScoring;
use App\Models\Assessment;
use App\Models\AssessmentCriterion;
use App\Models\Competition;
use App\Models\CompetitionSchedule;
use App\Models\CompetitionScore;
use App\Models\JudgeAssignment;
use App\Models\Patrol;
use App\Models\Role;
use App\Models\ScoutGroup;
use App\Models\User;
use App\Services\AssessmentScoringService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AssessmentsAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $superadmin;

    protected User $judge1;

    protected User $judge2;

    protected User $scoutUser;

    protected ScoutGroup $scoutGroup;

    protected Patrol $patrol1;

    protected Patrol $patrol2;

    protected Competition $competition;

    protected CompetitionSchedule $schedule;

    protected AssessmentCriterion $criterion1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $adminRole = Role::where('name', 'admin')->first();
        $superadminRole = Role::where('name', 'superadmin')->first();
        $judgeRole = Role::where('name', 'judge')->first();
        $scoutRole = Role::where('name', 'scout_group')->first();

        $this->admin = User::factory()->create(['name' => 'Kak Admin']);
        $this->admin->roles()->attach($adminRole);

        $this->superadmin = User::factory()->create(['name' => 'Kak Superadmin']);
        $this->superadmin->roles()->attach($superadminRole);

        $this->judge1 = User::factory()->create(['name' => 'Juri Ahmad']);
        $this->judge1->roles()->attach($judgeRole);

        $this->judge2 = User::factory()->create(['name' => 'Juri Budi']);
        $this->judge2->roles()->attach($judgeRole);

        $this->scoutUser = User::factory()->create();
        $this->scoutUser->roles()->attach($scoutRole);

        $this->scoutGroup = ScoutGroup::create([
            'user_id' => $this->scoutUser->id,
            'registration_code' => 'GDP-TEST-AUDIT',
            'school_name' => 'SMP Negeri 1 Padangsidimpuan',
            'group_number' => '01-001 & 01-002',
            'address' => 'Jl. Merdeka No. 1',
            'contact_person' => 'Pembina Gugusdepan',
            'contact_phone' => '081234567890',
            'verification_status' => RegistrationStatusEnum::Verified,
        ]);

        $this->patrol1 = Patrol::create([
            'scout_group_id' => $this->scoutGroup->id,
            'patrol_name' => 'Elang Perkasa',
            'gender_category' => PatrolGenderEnum::Boys,
            'status' => RegistrationStatusEnum::Verified,
            'npp' => '01',
        ]);

        $this->patrol2 = Patrol::create([
            'scout_group_id' => $this->scoutGroup->id,
            'patrol_name' => 'Harimau Putih',
            'gender_category' => PatrolGenderEnum::Boys,
            'status' => RegistrationStatusEnum::Verified,
            'npp' => '02',
        ]);

        $this->competition = Competition::create([
            'code' => 'PBB-AUDIT',
            'title' => 'Lomba Formasi Baris Berbaris',
            'category' => 'all',
            'participant_type' => 'patrol',
            'scoring_formula' => 'sum',
            'is_published' => true,
        ]);

        $this->criterion1 = AssessmentCriterion::create([
            'competition_id' => $this->competition->id,
            'name' => 'Kekompakan & Disiplin',
            'code' => 'KMP-01',
            'min_score' => 50.00,
            'max_score' => 100.00,
            'weight_percentage' => 100.00,
            'order' => 1,
        ]);

        $this->schedule = CompetitionSchedule::create([
            'competition_id' => $this->competition->id,
            'session_name' => 'Sesi Pagi Babak Final',
            'scheduled_date' => now()->toDateString(),
            'start_time' => now()->subHour()->format('H:i'),
            'end_time' => now()->addHours(2)->format('H:i'),
            'arena_location' => 'Lapangan Utama',
            'gender_category' => PatrolGenderEnum::Boys,
            'status' => 'scheduled',
        ]);

        JudgeAssignment::create([
            'competition_schedule_id' => $this->schedule->id,
            'user_id' => $this->judge1->id,
            'role_in_jury' => 'Juri Utama',
        ]);

        JudgeAssignment::create([
            'competition_schedule_id' => $this->schedule->id,
            'user_id' => $this->judge2->id,
            'role_in_jury' => 'Juri Pendamping',
        ]);
    }

    /**
     * Memastikan Admin dan Superadmin dapat mengakses halaman audit penilaian
     */
    public function test_admin_and_superadmin_can_access_assessments_audit_page(): void
    {
        $this->actingAs($this->admin)->get(route('admin.assessments.index'))->assertOk();
        $this->actingAs($this->superadmin)->get(route('admin.assessments.index'))->assertOk();
    }

    /**
     * Memastikan juri biasa tidak dapat mengakses halaman audit penilaian
     */
    public function test_non_admin_cannot_access_assessments_audit_page(): void
    {
        $this->actingAs($this->judge1)->get(route('admin.assessments.index'))->assertForbidden();
    }

    /**
     * Memastikan admin dapat memfilter daftar penilaian berdasarkan juri
     */
    public function test_admin_can_filter_assessments_by_judge(): void
    {
        // Juri 1 menilai Patrol 1
        Assessment::create([
            'competition_id' => $this->competition->id,
            'competition_schedule_id' => $this->schedule->id,
            'patrol_id' => $this->patrol1->id,
            'judge_id' => $this->judge1->id,
            'criteria_scores' => [$this->criterion1->id => 85.00],
            'raw_total_score' => 85.00,
            'final_score' => 85.00,
            'status' => 'finalized',
            'is_locked' => true,
        ]);

        // Juri 2 menilai Patrol 2
        Assessment::create([
            'competition_id' => $this->competition->id,
            'competition_schedule_id' => $this->schedule->id,
            'patrol_id' => $this->patrol2->id,
            'judge_id' => $this->judge2->id,
            'criteria_scores' => [$this->criterion1->id => 90.00],
            'raw_total_score' => 90.00,
            'final_score' => 90.00,
            'status' => 'finalized',
            'is_locked' => true,
        ]);

        Livewire::actingAs($this->admin, 'web')
            ->test(AssessmentsAudit::class)
            ->assertSee('Elang Perkasa')
            ->assertSee('Harimau Putih')
            ->set('judgeFilter', $this->judge1->id)
            ->assertSee('Elang Perkasa')
            ->assertDontSee('Harimau Putih');
    }

    /**
     * Memastikan administrator dapat membuka seluruh penilaian terfinalisasi per juri (bulk per judge)
     */
    public function test_admin_can_unlock_all_finalized_assessments_for_specific_judge(): void
    {
        // Juri 1 memiliki 2 penilaian yang telah difinalisasi
        $ass1 = Assessment::create([
            'competition_id' => $this->competition->id,
            'competition_schedule_id' => $this->schedule->id,
            'patrol_id' => $this->patrol1->id,
            'judge_id' => $this->judge1->id,
            'criteria_scores' => [$this->criterion1->id => 85.00],
            'raw_total_score' => 85.00,
            'final_score' => 85.00,
            'status' => 'finalized',
            'is_locked' => true,
        ]);

        $ass2 = Assessment::create([
            'competition_id' => $this->competition->id,
            'competition_schedule_id' => $this->schedule->id,
            'patrol_id' => $this->patrol2->id,
            'judge_id' => $this->judge1->id,
            'criteria_scores' => [$this->criterion1->id => 88.00],
            'raw_total_score' => 88.00,
            'final_score' => 88.00,
            'status' => 'finalized',
            'is_locked' => true,
        ]);

        // Juri 2 memiliki 1 penilaian yang tetap terkunci
        $assOther = Assessment::create([
            'competition_id' => $this->competition->id,
            'competition_schedule_id' => $this->schedule->id,
            'patrol_id' => $this->patrol1->id,
            'judge_id' => $this->judge2->id,
            'criteria_scores' => [$this->criterion1->id => 92.00],
            'raw_total_score' => 92.00,
            'final_score' => 92.00,
            'status' => 'finalized',
            'is_locked' => true,
        ]);

        Livewire::actingAs($this->admin, 'web')
            ->test(AssessmentsAudit::class)
            ->call('openUnlockJudgeModal', $this->judge1->id, $this->competition->id)
            ->assertSet('unlockJudgeId', $this->judge1->id)
            ->assertSet('isUnlockJudgeModalOpen', true)
            ->set('unlockJudgeReason', 'Revisi rubrik kriteria sesuai rekomendasi Dewan Kehormatan Juri')
            ->call('unlockJudgeAssessments');

        // Pastikan kedua penilaian Juri 1 berubah menjadi draf dan tidak terkunci
        $ass1->refresh();
        $ass2->refresh();
        $this->assertEquals('draft', $ass1->status);
        $this->assertFalse($ass1->is_locked);
        $this->assertEquals('draft', $ass2->status);
        $this->assertFalse($ass2->is_locked);

        // Penilaian Juri 2 harus tetap finalized dan terkunci
        $assOther->refresh();
        $this->assertEquals('finalized', $assOther->status);
        $this->assertTrue($assOther->is_locked);

        // Audit Log harus mencatat 2 log pembukaan kunci dengan alasan
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'score_unlocked',
            'model_id' => $ass1->id,
            'user_id' => $this->admin->id,
            'reason' => 'Revisi rubrik kriteria sesuai rekomendasi Dewan Kehormatan Juri',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'score_unlocked',
            'model_id' => $ass2->id,
            'user_id' => $this->admin->id,
            'reason' => 'Revisi rubrik kriteria sesuai rekomendasi Dewan Kehormatan Juri',
        ]);
    }

    /**
     * Memastikan kalkulasi klasemen terupdate dan juri dapat mengedit serta memfinalisasi ulang setelah di-unlock
     */
    public function test_reopened_assessment_can_be_re_edited_and_refinalized_by_judge(): void
    {
        $service = app(AssessmentScoringService::class);

        // 1. Juri 1 memfinalisasi nilai untuk patrol 1
        $assessment = Assessment::create([
            'competition_id' => $this->competition->id,
            'competition_schedule_id' => $this->schedule->id,
            'patrol_id' => $this->patrol1->id,
            'judge_id' => $this->judge1->id,
            'criteria_scores' => [$this->criterion1->id => 70.00],
            'raw_total_score' => 70.00,
            'final_score' => 70.00,
            'status' => 'draft',
            'is_locked' => false,
        ]);
        $service->finalizeScore($assessment->id, $this->judge1);

        $compScore = CompetitionScore::where('competition_id', $this->competition->id)
            ->where('patrol_id', $this->patrol1->id)
            ->first();
        $this->assertNotNull($compScore);
        $this->assertEquals(70.00, (float) $compScore->final_score);
        $this->assertTrue($compScore->is_locked);

        // 2. Admin membuka kunci penilaian per juri
        $service->unlockScoresByJudge(
            judgeId: $this->judge1->id,
            reason: 'Revisi skor oleh juri bersangkutan',
            admin: $this->admin,
            competitionId: $this->competition->id
        );

        $assessment->refresh();
        $this->assertEquals('draft', $assessment->status);
        $this->assertFalse($assessment->is_locked);

        // Akumulasi skor pada klasemen sementara menjadi tidak terkunci
        $compScore->refresh();
        $this->assertFalse($compScore->is_locked);

        // 3. Juri 1 membuka kokpit penjurian, mengubah skor menjadi 95.00 dan memfinalisasi ulang
        Livewire::actingAs($this->judge1, 'web')
            ->test(JudgeScoring::class, ['scheduleId' => $this->schedule->id])
            ->set('selectedPatrolId', $this->patrol1->id)
            ->set('criteriaScores.'.$this->criterion1->id, 95.00)
            ->call('finalize');

        $assessment->refresh();
        $this->assertEquals('finalized', $assessment->status);
        $this->assertTrue($assessment->is_locked);
        $this->assertEquals(95.00, (float) $assessment->final_score);

        $compScore->refresh();
        $this->assertEquals(95.00, (float) $compScore->final_score);
        $this->assertTrue($compScore->is_locked);
    }

    /**
     * Memastikan opsi unlockScope 'judge_all' di JudgeScoring bekerja membuka semua nilai juri di sesi tersebut
     */
    public function test_judge_scoring_allows_admin_to_unlock_all_scores_for_judge_in_schedule(): void
    {
        $ass1 = Assessment::create([
            'competition_id' => $this->competition->id,
            'competition_schedule_id' => $this->schedule->id,
            'patrol_id' => $this->patrol1->id,
            'judge_id' => $this->judge1->id,
            'criteria_scores' => [$this->criterion1->id => 80.00],
            'raw_total_score' => 80.00,
            'final_score' => 80.00,
            'status' => 'finalized',
            'is_locked' => true,
        ]);

        $ass2 = Assessment::create([
            'competition_id' => $this->competition->id,
            'competition_schedule_id' => $this->schedule->id,
            'patrol_id' => $this->patrol2->id,
            'judge_id' => $this->judge1->id,
            'criteria_scores' => [$this->criterion1->id => 85.00],
            'raw_total_score' => 85.00,
            'final_score' => 85.00,
            'status' => 'finalized',
            'is_locked' => true,
        ]);

        Livewire::actingAs($this->admin, 'web')
            ->test(JudgeScoring::class, ['scheduleId' => $this->schedule->id])
            ->set('selectedPatrolId', $this->patrol1->id)
            ->set('unlockScope', 'judge_all')
            ->set('unlockReason', 'Pembukaan semua nilai juri 1 pada sesi ini untuk penyesuaian')
            ->call('unlock')
            ->assertDispatched('alert');

        $ass1->refresh();
        $ass2->refresh();
        $this->assertEquals('draft', $ass1->status);
        $this->assertFalse($ass1->is_locked);
        $this->assertEquals('draft', $ass2->status);
        $this->assertFalse($ass2->is_locked);
    }

    /**
     * Memastikan modal Buka Finalisasi Juri pada sesi ini di JudgeScoring dapat dibuka dan dieksekusi oleh Administrator
     */
    public function test_admin_can_unlock_judge_in_schedule_modal_from_judge_scoring(): void
    {
        $ass1 = Assessment::create([
            'competition_id' => $this->competition->id,
            'competition_schedule_id' => $this->schedule->id,
            'patrol_id' => $this->patrol1->id,
            'judge_id' => $this->judge1->id,
            'criteria_scores' => [$this->criterion1->id => 82.00],
            'raw_total_score' => 82.00,
            'final_score' => 82.00,
            'status' => 'finalized',
            'is_locked' => true,
        ]);

        $ass2 = Assessment::create([
            'competition_id' => $this->competition->id,
            'competition_schedule_id' => $this->schedule->id,
            'patrol_id' => $this->patrol2->id,
            'judge_id' => $this->judge1->id,
            'criteria_scores' => [$this->criterion1->id => 86.00],
            'raw_total_score' => 86.00,
            'final_score' => 86.00,
            'status' => 'finalized',
            'is_locked' => true,
        ]);

        Livewire::actingAs($this->admin, 'web')
            ->test(JudgeScoring::class, ['scheduleId' => $this->schedule->id])
            ->call('openUnlockJudgeModal', $this->judge1->id)
            ->assertSet('isUnlockJudgeModalOpen', true)
            ->assertSet('unlockJudgeId', $this->judge1->id)
            ->set('unlockJudgeReason', 'Instruksi ketua juri pembukaan nilai sesi pagi')
            ->call('unlockJudgeInSchedule')
            ->assertDispatched('alert');

        $ass1->refresh();
        $ass2->refresh();
        $this->assertEquals('draft', $ass1->status);
        $this->assertFalse($ass1->is_locked);
        $this->assertEquals('draft', $ass2->status);
        $this->assertFalse($ass2->is_locked);
    }

    /**
     * Memastikan administrator dapat berpindah antar juri penilai saat memeriksa satu regu di JudgeScoring
     */
    public function test_admin_can_switch_judges_when_inspecting_patrol_in_judge_scoring(): void
    {
        // Juri 1 menilai Patrol 1 dengan skor 75
        $ass1 = Assessment::create([
            'competition_id' => $this->competition->id,
            'competition_schedule_id' => $this->schedule->id,
            'patrol_id' => $this->patrol1->id,
            'judge_id' => $this->judge1->id,
            'criteria_scores' => [$this->criterion1->id => 75.00],
            'raw_total_score' => 75.00,
            'final_score' => 75.00,
            'status' => 'finalized',
            'is_locked' => true,
        ]);

        // Juri 2 menilai Patrol 1 dengan skor 88
        $ass2 = Assessment::create([
            'competition_id' => $this->competition->id,
            'competition_schedule_id' => $this->schedule->id,
            'patrol_id' => $this->patrol1->id,
            'judge_id' => $this->judge2->id,
            'criteria_scores' => [$this->criterion1->id => 88.00],
            'raw_total_score' => 88.00,
            'final_score' => 88.00,
            'status' => 'finalized',
            'is_locked' => true,
        ]);

        $test = Livewire::actingAs($this->admin, 'web')
            ->test(JudgeScoring::class, ['scheduleId' => $this->schedule->id])
            ->set('selectedPatrolId', $this->patrol1->id);

        // Pilih Juri 1
        $test->call('selectJudgeForPatrol', $this->judge1->id);
        $this->assertEquals($ass1->id, $test->get('activeAssessment')->id);

        // Pilih Juri 2
        $test->call('selectJudgeForPatrol', $this->judge2->id);
        $this->assertEquals($ass2->id, $test->get('activeAssessment')->id);
    }
}

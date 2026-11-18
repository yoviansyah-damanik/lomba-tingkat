<?php

namespace Tests\Feature;

use App\Livewire\Admin\QuestionnaireRecap;
use App\Livewire\Portal\QuestionnaireForm;
use App\Models\Event;
use App\Models\QuestionnaireResponse;
use App\Models\User;
use Database\Seeders\EventSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuestionnaireTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $committeeUser;

    protected User $judgeUser;

    protected User $participantUser;

    protected Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RoleSeeder::class,
            UserSeeder::class,
            EventSeeder::class,
        ]);

        $this->event = Event::where('is_active', true)->first() ?? Event::first();
        $this->admin = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->first();
        $this->committeeUser = User::where('username', 'panitia')->first();
        $this->judgeUser = User::whereHas('roles', fn ($q) => $q->where('name', 'judge'))->first();

        $this->participantUser = User::factory()->create();
        $this->participantUser->assignRole('participant');
    }

    /**
     * Memastikan panitia dan juri dapat membuka halaman kuisioner
     */
    public function test_committee_and_judge_can_access_questionnaire_form(): void
    {
        $this->actingAs($this->committeeUser)
            ->get(route('questionnaire.index'))
            ->assertOk()
            ->assertSee('Kuisioner Evaluasi Pengalaman Aplikasi')
            ->assertSee('Panitia Pelaksana');

        $this->actingAs($this->judgeUser)
            ->get(route('questionnaire.index'))
            ->assertOk()
            ->assertSee('Kuisioner Evaluasi Pengalaman Aplikasi')
            ->assertSee('Dewan Juri');
    }

    /**
     * Memastikan pengguna tanpa peran panitia/juri/admin ditolak (403)
     */
    public function test_unauthorized_users_cannot_access_questionnaire_form(): void
    {
        // Pengguna tamu (guest)
        $this->get(route('questionnaire.index'))
            ->assertRedirect(route('login'));

        // Peserta biasa
        $this->actingAs($this->participantUser)
            ->get(route('questionnaire.index'))
            ->assertForbidden();
    }

    /**
     * Memastikan panitia atau juri dapat mengirimkan tanggapan kuisioner
     */
    public function test_user_can_submit_questionnaire_response(): void
    {
        Livewire::actingAs($this->committeeUser)
            ->test(QuestionnaireForm::class)
            ->set('duty_assignment', 'Seksi Perlombaan & Arena')
            ->set('rating_overall', 5)
            ->set('rating_ui_ux', 4)
            ->set('rating_features', 5)
            ->set('rating_speed', 4)
            ->set('rating_transparency', 5)
            ->set('device_used', 'android')
            ->set('efficiency_level', 'very_efficient')
            ->set('main_obstacle', 'none')
            ->set('most_useful_feature', 'scoring_cockpit')
            ->set('future_readiness', 'mandatory')
            ->set('recommended_features', 'Fitur mode offline otomatis saat jaringan drop di buper')
            ->set('experience_feedback', 'Aplikasi sangat memudahkan pencatatan nilai tanpa hitung manual')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('hasSubmitted', true);

        $this->assertDatabaseHas('questionnaire_responses', [
            'user_id' => $this->committeeUser->id,
            'event_id' => $this->event->id,
            'duty_assignment' => 'Seksi Perlombaan & Arena',
            'rating_overall' => 5,
            'device_used' => 'android',
            'efficiency_level' => 'very_efficient',
        ]);
    }

    /**
     * Memastikan responden dapat memperbarui jawaban kuisioner yang telah ada
     */
    public function test_user_can_update_existing_questionnaire_response(): void
    {
        // Buat respon awal
        QuestionnaireResponse::create([
            'user_id' => $this->judgeUser->id,
            'event_id' => $this->event->id,
            'role' => 'judge',
            'duty_assignment' => 'Juri Pos Sandi',
            'rating_overall' => 4,
            'rating_ui_ux' => 4,
            'rating_features' => 4,
            'rating_speed' => 4,
            'rating_transparency' => 5,
            'device_used' => 'android',
            'efficiency_level' => 'helpful',
            'main_obstacle' => 'signal',
            'most_useful_feature' => 'blind_scoring',
            'rubric_clarity' => 'very_clear',
            'future_readiness' => 'continue_with_improvements',
            'recommended_features' => 'Masukan awal',
        ]);

        // Perbarui respon via Livewire
        Livewire::actingAs($this->judgeUser)
            ->test(QuestionnaireForm::class)
            ->assertSet('hasSubmitted', true)
            ->assertSet('rating_overall', 4)
            ->set('rating_overall', 5)
            ->set('recommended_features', 'Masukan yang telah diperbarui')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertEquals(1, QuestionnaireResponse::where('user_id', $this->judgeUser->id)->count());
        $this->assertDatabaseHas('questionnaire_responses', [
            'user_id' => $this->judgeUser->id,
            'rating_overall' => 5,
            'recommended_features' => 'Masukan yang telah diperbarui',
        ]);
    }

    /**
     * Memastikan administrator dapat mengakses halaman rekapitulasi kuisioner dan melihat agregat
     */
    public function test_admin_can_access_questionnaire_recap_and_see_aggregates(): void
    {
        // Buat data respon panitia
        QuestionnaireResponse::create([
            'user_id' => $this->committeeUser->id,
            'event_id' => $this->event->id,
            'role' => 'committee',
            'duty_assignment' => 'Pos Presensi Buper',
            'rating_overall' => 5,
            'rating_ui_ux' => 5,
            'rating_features' => 5,
            'rating_speed' => 4,
            'rating_transparency' => 5,
            'device_used' => 'android',
            'efficiency_level' => 'very_efficient',
            'main_obstacle' => 'none',
            'most_useful_feature' => 'qr_attendance',
            'future_readiness' => 'mandatory',
            'recommended_features' => 'Mode offline scanner',
        ]);

        // Buat data respon juri
        QuestionnaireResponse::create([
            'user_id' => $this->judgeUser->id,
            'event_id' => $this->event->id,
            'role' => 'judge',
            'duty_assignment' => 'Juri Pioneering',
            'rating_overall' => 4,
            'rating_ui_ux' => 4,
            'rating_features' => 5,
            'rating_speed' => 4,
            'rating_transparency' => 5,
            'device_used' => 'tablet',
            'efficiency_level' => 'very_efficient',
            'main_obstacle' => 'signal',
            'most_useful_feature' => 'scoring_cockpit',
            'future_readiness' => 'mandatory',
            'recommended_features' => 'Notifikasi panggilan regu',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.questionnaires.recap'))
            ->assertOk()
            ->assertSee('Rekapitulasi Kuisioner Panitia &amp; Dewan Juri', false)
            ->assertSee('Total Responden')
            ->assertSee('Mode offline scanner');

        // Uji komponen Livewire rekapitulasi
        Livewire::actingAs($this->admin)
            ->test(QuestionnaireRecap::class)
            ->assertSee('Pos Presensi Buper')
            ->assertSee('Juri Pioneering')
            ->set('roleFilter', 'judge')
            ->assertSee('Juri Pioneering')
            ->set('search', 'offline')
            ->set('roleFilter', 'all')
            ->assertSee('Mode offline scanner');
    }

    /**
     * Memastikan non-admin ditolak saat mengakses halaman rekapitulasi
     */
    public function test_non_admin_cannot_access_questionnaire_recap(): void
    {
        $this->actingAs($this->committeeUser)
            ->get(route('admin.questionnaires.recap'))
            ->assertForbidden();

        $this->actingAs($this->judgeUser)
            ->get(route('admin.questionnaires.recap'))
            ->assertForbidden();
    }

    /**
     * Memastikan administrator dapat mengatur jadwal waktu buka dan tutup kuisioner
     */
    public function test_admin_can_configure_questionnaire_open_and_close_window(): void
    {
        $start = now()->addHour()->format('Y-m-d\TH:i');
        $end = now()->addDays(2)->format('Y-m-d\TH:i');

        Livewire::actingAs($this->admin)
            ->test(QuestionnaireRecap::class)
            ->call('openPeriodModal')
            ->assertSet('showPeriodModal', true)
            ->set('is_questionnaire_open', true)
            ->set('questionnaire_start_at', $start)
            ->set('questionnaire_end_at', $end)
            ->call('savePeriodSettings')
            ->assertHasNoErrors()
            ->assertSet('showPeriodModal', false);

        $this->event->refresh();
        $this->assertTrue((bool) $this->event->is_questionnaire_open);
        $this->assertNotNull($this->event->questionnaire_start_at);
        $this->assertNotNull($this->event->questionnaire_end_at);
    }

    /**
     * Memastikan kuisioner terkunci dan menolak pengiriman jika waktu telah ditutup
     */
    public function test_questionnaire_form_is_locked_when_outside_open_window(): void
    {
        // Set waktu penutupan di masa lalu
        $this->event->update([
            'is_questionnaire_open' => true,
            'questionnaire_start_at' => now()->subDays(2),
            'questionnaire_end_at' => now()->subHour(),
        ]);

        $this->assertFalse($this->event->isQuestionnaireOpen());

        // Halaman form menampilkan informasi periode telah berakhir
        $this->actingAs($this->committeeUser)
            ->get(route('questionnaire.index'))
            ->assertOk()
            ->assertSee('Periode Kuisioner Telah Berakhir');

        // Pengiriman via Livewire ditolak
        Livewire::actingAs($this->committeeUser)
            ->test(QuestionnaireForm::class)
            ->set('duty_assignment', 'Seksi Lapangan')
            ->set('rating_overall', 5)
            ->call('submit')
            ->assertHasErrors(['general']);

        $this->assertDatabaseMissing('questionnaire_responses', [
            'user_id' => $this->committeeUser->id,
            'duty_assignment' => 'Seksi Lapangan',
        ]);
    }

    /**
     * Memastikan responden yang sudah pernah mengisi tetap dapat melihat jawabannya saat periode ditutup
     */
    public function test_user_who_already_submitted_can_view_responses_in_read_only_when_closed(): void
    {
        QuestionnaireResponse::create([
            'user_id' => $this->judgeUser->id,
            'event_id' => $this->event->id,
            'role' => 'judge',
            'duty_assignment' => 'Juri Semaphore',
            'rating_overall' => 4,
            'rating_ui_ux' => 4,
            'rating_features' => 4,
            'rating_speed' => 4,
            'rating_transparency' => 4,
            'device_used' => 'iphone',
            'efficiency_level' => 'helpful',
            'main_obstacle' => 'none',
            'most_useful_feature' => 'scoring_cockpit',
            'future_readiness' => 'mandatory',
            'recommended_features' => 'Catatan juri tersimpan aman',
        ]);

        // Tutup kuisioner
        $this->event->update([
            'is_questionnaire_open' => false,
        ]);

        $this->actingAs($this->judgeUser)
            ->get(route('questionnaire.index'))
            ->assertOk()
            ->assertSee('Periode Kuisioner Telah Berakhir')
            ->assertSee('Catatan juri tersimpan aman')
            ->assertSee('Form Terkunci (Periode Kuisioner Ditutup)');
    }
}

<?php

namespace Tests\Feature;

use App\Enums\RegistrationStatusEnum;
use App\Livewire\Admin\Certificates\Settings;
use App\Models\Chaperone;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use App\Models\User;
use App\Services\CertificateService;
use Database\Seeders\EventSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CertificateManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $participantUser;
    protected User $chaperoneUser;
    protected User $committeeUser;
    protected User $judgeUser;
    protected Event $event;
    protected Participant $participant;
    protected Chaperone $chaperone;
    protected ScoutGroup $scoutGroup;

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

        // Buat data pangkalan, regu, peserta terverifikasi, dan pembina terverifikasi
        $scoutGroupUser = User::factory()->create();
        $scoutGroupUser->assignRole('scout_group');

        $this->scoutGroup = ScoutGroup::factory()->create([
            'user_id' => $scoutGroupUser->id,
            'school_name' => 'SMP Negeri 1 Padangsidimpuan',
            'kwarran_name' => 'Padangsidimpuan Utara',
            'registration_code' => 'G-01001.01002',
            'verification_status' => RegistrationStatusEnum::Verified,
        ]);

        $patrol = Patrol::create([
            'scout_group_id' => $this->scoutGroup->id,
            'patrol_name' => 'Garuda',
            'gender_category' => 'boys',
            'status' => RegistrationStatusEnum::Verified,
            'npp' => '01-01',
        ]);

        $this->participant = Participant::factory()->create([
            'patrol_id' => $patrol->id,
            'full_name' => 'Ilham Daulay',
            'scout_id' => '02.21.01.001',
            'role_in_patrol' => 'patrol_leader',
            'status' => RegistrationStatusEnum::Verified,
        ]);

        $this->participantUser = User::factory()->create([
            'participant_id' => $this->participant->id,
            'name' => $this->participant->full_name,
        ]);
        $this->participantUser->assignRole('participant');

        $this->chaperone = Chaperone::factory()->create([
            'scout_group_id' => $this->scoutGroup->id,
            'full_name' => 'Kak Budi Siregar, S.Pd',
            'scout_id' => '02.21.01.00055',
            'status' => RegistrationStatusEnum::Verified,
        ]);

        $this->chaperoneUser = User::factory()->create([
            'name' => $this->chaperone->full_name,
        ]);
        $this->chaperoneUser->assignRole('chaperone');
        $this->chaperone->update(['user_id' => $this->chaperoneUser->id]);
    }

    /**
     * Memastikan administrator dapat mengakses halaman konfigurasi sertifikat
     */
    public function test_admin_can_access_certificate_settings_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.certificates.settings'))
            ->assertOk()
            ->assertSee('Pengaturan Template Sertifikat');
    }

    /**
     * Memastikan pengguna non-admin dilarang mengakses konfigurasi sertifikat
     */
    public function test_non_admin_cannot_access_certificate_settings_page(): void
    {
        $this->actingAs($this->participantUser)
            ->get(route('admin.certificates.settings'))
            ->assertForbidden();
    }

    /**
     * Memastikan admin dapat mengalihkan status penerbitan sertifikat (ON / OFF)
     */
    public function test_admin_can_toggle_certificate_publishing(): void
    {
        $this->assertFalse($this->event->fresh()->isCertificatePublished());

        Livewire::actingAs($this->admin)
            ->test(Settings::class)
            ->call('togglePublish')
            ->assertSet('is_certificate_published', true);

        $this->assertTrue($this->event->fresh()->isCertificatePublished());

        Livewire::actingAs($this->admin)
            ->test(Settings::class)
            ->call('togglePublish')
            ->assertSet('is_certificate_published', false);

        $this->assertFalse($this->event->fresh()->isCertificatePublished());
    }

    /**
     * Memastikan admin dapat menyimpan tata letak dan menerapkannya ke semua peran
     */
    public function test_admin_can_save_role_layout_and_apply_to_all(): void
    {
        Livewire::actingAs($this->admin)
            ->test(Settings::class)
            ->set('activeRole', 'participant')
            ->set('name_x', 52.5)
            ->set('name_y', 46.0)
            ->set('name_size', 30.0)
            ->set('name_color', '#1e3a8a')
            ->call('save')
            ->assertHasNoErrors()
            ->call('applyToAllRoles')
            ->assertHasNoErrors();

        $template = $this->event->certificateTemplates()->where('role', 'participant')->first();
        $this->assertNotNull($template);
        $this->assertEquals(52.5, $template->layout['full_name']['x']);
        $this->assertEquals('#1e3a8a', $template->layout['full_name']['color']);

        // Pastikan juga tersimpan untuk dewan juri
        $judgeTemplate = $this->event->certificateTemplates()->where('role', 'judge')->first();
        $this->assertNotNull($judgeTemplate);
        $this->assertEquals(52.5, $judgeTemplate->layout['full_name']['x']);
    }

    /**
     * Memastikan admin dapat melihat contoh PDF sertifikat
     */
    public function test_admin_can_preview_sample_pdf(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.certificates.preview', ['role' => 'participant']));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Memastikan halaman depan menampilkan pemberitahuan saat penerbitan ON dan menyembunyikannya saat OFF
     */
    public function test_landing_page_announcement_visibility_based_on_toggle(): void
    {
        // 1. Kondisi OFF
        $this->event->update(['is_certificate_published' => false]);
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Penerbitan E-Sertifikat Telah Dibuka');

        // 2. Kondisi ON
        $this->event->update(['is_certificate_published' => true]);
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Penerbitan E-Sertifikat Telah Dibuka');
    }

    /**
     * Memastikan dasbor menampilkan banner dan kartu sertifikat saat penerbitan ON
     */
    public function test_dashboard_shows_certificate_banner_when_published(): void
    {
        $this->event->update(['is_certificate_published' => true]);

        $this->actingAs($this->participantUser)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Penerbitan E-Sertifikat Resmi Telah Dibuka');
    }

    /**
     * Memastikan tidak ada laman /certificates untuk publik (404)
     */
    public function test_public_certificates_page_does_not_exist(): void
    {
        $this->get('/certificates')->assertNotFound();
    }

    /**
     * Memastikan seluruh jenis akun dapat mengakses sertifikat langsung dari dasbor masing-masing
     */
    public function test_all_account_types_can_access_certificates_from_dashboard(): void
    {
        $this->event->update(['is_certificate_published' => true]);

        // 1. Peserta
        $this->actingAs($this->participantUser)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Unduh Sertifikat Saya');

        // 2. Pembina Pendamping
        $this->actingAs($this->chaperoneUser)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Unduh Sertifikat Saya');

        // 3. Dewan Juri
        $this->actingAs($this->judgeUser)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Unduh Sertifikat Saya');

        // 4. Panitia
        $this->actingAs($this->committeeUser)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Unduh Sertifikat Saya');

        // 5. Gugus Depan / Kontingen
        $scoutGroupUser = $this->scoutGroup->user;
        $this->actingAs($scoutGroupUser)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('E-Sertifikat Kontingen Pangkalan')
            ->assertSee('Ilham Daulay')
            ->assertSee('Kak Budi Siregar, S.Pd');

        // 6. Admin
        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Pengaturan Template');
    }

    /**
     * Memastikan peserta terverifikasi dapat mengunduh e-sertifikatnya saat ON
     */
    public function test_verified_participant_can_download_certificate_when_published(): void
    {
        $this->event->update(['is_certificate_published' => true]);

        $response = $this->actingAs($this->participantUser)
            ->get(route('certificates.download.participant', $this->participant));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Memastikan pengunduhan sertifikat ditolak jika penerbitan belum dibuka (OFF)
     */
    public function test_download_rejected_when_publishing_is_off(): void
    {
        $this->event->update(['is_certificate_published' => false]);

        $this->actingAs($this->participantUser)
            ->get(route('certificates.download.participant', $this->participant))
            ->assertForbidden();
    }

    /**
     * Memastikan endpoint sertifikat pribadi (my certificate) bekerja bagi seluruh peran terkait
     */
    public function test_my_certificate_endpoint(): void
    {
        $this->event->update(['is_certificate_published' => true]);

        // Peserta
        $this->actingAs($this->participantUser)
            ->get(route('certificates.my'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        // Pembina Pendamping
        $this->actingAs($this->chaperoneUser)
            ->get(route('certificates.my'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        // Panitia
        $this->actingAs($this->committeeUser)
            ->get(route('certificates.my'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        // Dewan Juri
        $this->actingAs($this->judgeUser)
            ->get(route('certificates.my'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Memastikan laman verifikasi publik menampilkan status sah
     */
    public function test_certificate_public_verification_page(): void
    {
        $this->get(route('certificates.verify', ['id' => $this->participant->id_number]))
            ->assertOk()
            ->assertSee('E-Sertifikat Sah &amp; Terverifikasi', false)
            ->assertSee('Ilham Daulay');
    }

    /**
     * Memastikan service sertifikat menghasilkan data lengkap: Nama, NTA, Pangkalan, dan Peran
     */
    public function test_certificate_service_data_completeness(): void
    {
        $service = app(CertificateService::class);
        $data = $service->recipientDataForParticipant($this->participant, $this->event);

        $this->assertEquals('Ilham Daulay', $data['full_name']);
        $this->assertEquals('NTA: 02.21.01.001', $data['scout_id']);
        $this->assertStringContainsString('SMP Negeri 1 Padangsidimpuan', $data['institution']);
        $this->assertStringContainsString('Peserta', $data['role_label']);
        $this->assertNotEmpty($data['certificate_number']);
        $this->assertTrue($data['is_eligible']);
    }
}

<?php

namespace Tests\Feature;

use App\Enums\PatrolGenderEnum;
use App\Enums\RegistrationStatusEnum;
use App\Enums\RoleEnum;
use App\Livewire\Admin\ReportsCenter;
use App\Models\Competition;
use App\Models\CompetitionScore;
use App\Models\Event;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use App\Models\User;
use Database\Seeders\EventSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportsExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $scoutGroupUser;
    protected ScoutGroup $scoutGroup;
    protected Patrol $patrol;
    protected Competition $competition;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RoleSeeder::class,
            UserSeeder::class,
            EventSeeder::class,
        ]);

        $this->admin = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->first();
        $this->scoutGroupUser = User::whereHas('roles', fn ($q) => $q->where('name', 'scout_group'))->first();

        $event = Event::first();

        $this->scoutGroup = ScoutGroup::factory()->create([
            'user_id' => $this->scoutGroupUser->id,
            'school_name' => 'SMP Negeri 1 Padangsidimpuan',
            'group_number' => '01.001-01.002',
            'kwarran_name' => 'Padangsidimpuan Utara',
            'verification_status' => RegistrationStatusEnum::Verified,
        ]);

        $this->patrol = Patrol::create([
            'scout_group_id' => $this->scoutGroup->id,
            'patrol_name' => 'Rajawali',
            'gender_category' => PatrolGenderEnum::Boys,
            'status' => RegistrationStatusEnum::Verified,
        ]);

        $this->competition = Competition::create([
            'event_id' => $event->id,
            'code' => 'LKBB-01',
            'title' => 'Lomba Baris Berbaris',
            'category' => 'ketangkasan',
            'participant_type' => 'patrol',
            'min_participants' => 8,
            'max_participants' => 8,
            'is_published' => true,
        ]);
    }

    /**
     * Memastikan tamu (guest) tidak dapat mengakses pusat laporan
     */
    public function test_guest_cannot_access_reports_center(): void
    {
        $response = $this->get(route('admin.reports.index'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Memastikan admin dapat mengakses halaman pusat laporan
     */
    public function test_admin_can_access_reports_center(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.reports.index'));
        $response->assertStatus(200);
        $response->assertSee('Pusat Laporan & Ekspor Data Resmi');

        Livewire::actingAs($this->admin)
            ->test(ReportsCenter::class)
            ->assertStatus(200)
            ->assertSee('Gudep Terverifikasi')
            ->assertSee('Buku Induk Peserta');
    }

    /**
     * Memastikan ekspor CSV kontingen dan gugus depan berhasil
     */
    public function test_admin_can_export_contingents_csv(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.reports.export.contingents'));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('Nama Pangkalan / Sekolah', $content);
        $this->assertStringContainsString('SMP Negeri 1 Padangsidimpuan', $content);
    }

    /**
     * Memastikan ekspor CSV buku induk peserta berhasil
     */
    public function test_admin_can_export_participants_csv(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.reports.export.participants'));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('buku-induk', strtolower($response->headers->get('content-disposition')));
        $this->assertStringContainsString('Nama Lengkap', $content);
    }

    /**
     * Memastikan ekspor CSV rekap presensi pos berhasil
     */
    public function test_admin_can_export_attendance_csv(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.reports.export.attendance'));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('Waktu Pindai (WIB)', $content);
        $this->assertStringContainsString('Pos / Lokasi Kegiatan', $content);
    }

    /**
     * Memastikan ekspor CSV rekap nilai lomba berhasil
     */
    public function test_admin_can_export_scores_csv(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.reports.export.scores'));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('Kode Lomba', $content);
        $this->assertStringContainsString('Predikat Prestasi', $content);
    }

    /**
     * Memastikan ekspor CSV klasemen akumulasi medali berhasil
     */
    public function test_admin_can_export_standings_csv_with_gender_filter(): void
    {
        $responseAll = $this->actingAs($this->admin)->get(route('admin.reports.export.standings', ['gender' => 'all']));
        $responseAll->assertStatus(200);
        $this->assertStringContainsString('text/csv', $responseAll->headers->get('content-type'));

        $responseBoys = $this->actingAs($this->admin)->get(route('admin.reports.export.standings', ['gender' => 'boys']));
        $responseBoys->assertStatus(200);

        $responseGirls = $this->actingAs($this->admin)->get(route('admin.reports.export.standings', ['gender' => 'girls']));
        $responseGirls->assertStatus(200);
    }

    /**
     * Memastikan pratinjau Berita Acara Juara Umum dapat dibuka dan menampilkan format resmi
     */
    public function test_admin_can_open_official_berita_acara(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.reports.print.berita-acara'));

        $response->assertStatus(200);
        $response->assertSee('BERITA ACARA PENETAPAN HASIL AKHIR');
        $response->assertSee('Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan');
        $response->assertSee('Ketua Dewan Juri');
        $response->assertSee('SMP Negeri 1 Padangsidimpuan');
    }

    /**
     * Memastikan peran non-admin (misal Gugus Depan) dilarang mengakses ekspor laporan admin
     */
    public function test_non_admin_cannot_access_admin_reports(): void
    {
        $response = $this->actingAs($this->scoutGroupUser)->get(route('admin.reports.index'));
        $response->assertStatus(403);

        $responseExport = $this->actingAs($this->scoutGroupUser)->get(route('admin.reports.export.contingents'));
        $responseExport->assertStatus(403);
    }
}

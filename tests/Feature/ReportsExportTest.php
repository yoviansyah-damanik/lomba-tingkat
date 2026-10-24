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

        $response->assertStatus(200)
            ->assertSee('PRATINJAU PDF')
            ->assertSee('Unduh PDF')
            ->assertSee(route('admin.reports.pdf.berita-acara'), false)
            ->assertSee('download=1', false);
    }

    /**
     * Berita acara dan rekap regu berprestasi tersedia sebagai PDF (tampil inline, unduh dengan ?download=1)
     */
    public function test_reports_are_served_as_pdf_with_preview_first(): void
    {
        foreach (['admin.reports.pdf.berita-acara' => 'berita-acara-', 'standings.pdf' => 'rekap-regu-berprestasi-'] as $route => $prefix) {
            $inline = $this->actingAs($this->admin)->get(route($route, ['raw' => 1]));
            $inline->assertOk()->assertHeader('Content-Type', 'application/pdf');
            $this->assertStringStartsWith('%PDF', $inline->getContent());
            $this->assertStringContainsString('inline; filename="'.$prefix, $inline->headers->get('Content-Disposition'));

            $download = $this->actingAs($this->admin)->get(route($route, ['download' => 1]));
            $this->assertStringContainsString('attachment; filename="'.$prefix, $download->headers->get('Content-Disposition'));
        }

        $this->actingAs($this->admin)->get(route('standings.print'))
            ->assertOk()
            ->assertSee('PRATINJAU PDF')
            ->assertSee(route('standings.pdf'), false);
    }

    /**
     * URL PDF yang dibuka langsung menampilkan halaman pratinjau; ?raw=1 (isi iframe) dan ?download=1 tetap PDF
     */
    public function test_pdf_url_renders_preview_page_by_default(): void
    {
        $url = route('admin.scout-groups.patrols.form-01', [$this->scoutGroup->id, $this->patrol->id]);

        $this->actingAs($this->admin)->get($url)
            ->assertOk()
            ->assertSee('PRATINJAU PDF')
            ->assertSee('raw=1', false)
            ->assertSee('download=1', false);

        $this->actingAs($this->admin)->get($url.'?raw=1')
            ->assertHeader('Content-Type', 'application/pdf');

        $this->actingAs($this->admin)->get($url.'?download=1')
            ->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Seluruh dokumen PDF memakai kop seragam: logo kiri, tiga baris identitas kegiatan
     */
    public function test_pdf_templates_share_the_standard_letterhead(): void
    {
        $data = [
            'event' => \App\Models\Event::first(),
            'boysStandings' => collect(),
            'girlsStandings' => collect(),
            'generatedAt' => now(),
        ];

        foreach (['reports.berita-acara-pdf', 'reports.standings-recap-pdf'] as $view) {
            $html = view($view, $data)->render();

            $this->assertStringContainsString('Lomba Tingkat III', $html);
            $this->assertStringContainsString('Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan', $html);
            $this->assertStringContainsString('Tahun ', $html);
            $this->assertStringContainsString('logo-256.png', $html);
        }

        $berita = view('reports.berita-acara-pdf', $data)->render();
        $this->assertStringContainsString('Berita Acara Penetapan Hasil Akhir', $berita);
        $this->assertStringContainsString('Ketua Dewan Juri', $berita);
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

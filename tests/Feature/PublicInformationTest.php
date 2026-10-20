<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Competition;
use App\Models\CompetitionSchedule;
use App\Models\Event;
use Database\Seeders\AnnouncementSeeder;
use Database\Seeders\CompetitionScheduleSeeder;
use Database\Seeders\CompetitionSeeder;
use Database\Seeders\EventSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicInformationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RoleSeeder::class,
            UserSeeder::class,
            EventSeeder::class,
            AnnouncementSeeder::class,
            CompetitionSeeder::class,
            CompetitionScheduleSeeder::class,
        ]);
    }

    /**
     * Memastikan beranda publik menampilkan nama resmi kegiatan
     */
    public function test_home_page_displays_official_event_identity(): void
    {
        $event = Event::where('is_active', true)->first();
        if ($event) {
            $event->update(['description' => 'Sambutan dan Deskripsi Singkat Kegiatan LT III']);
        }

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Lomba Tingkat III Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan');
        $response->assertSee('Sambutan dan Deskripsi Singkat Kegiatan LT III');
        $response->assertSee('Mata Lomba');
        $response->assertSee('Juknis');
    }

    /**
     * Memastikan halaman pengumuman menampilkan daftar pengumuman yang dipublikasikan
     */
    public function test_announcements_index_and_detail_page_work(): void
    {
        $response = $this->get('/announcements');
        $response->assertStatus(200);

        $announcement = Announcement::published()->first();
        $this->assertNotNull($announcement);

        $response->assertSee($announcement->title);

        // Membuka detail pengumuman
        $detailResponse = $this->get('/announcements/'.$announcement->slug);
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee($announcement->title);
    }

    /**
     * Memastikan halaman mata lomba dan juknis menampilkan daftar cabang lomba
     */
    public function test_competitions_catalog_page_displays_scouting_competitions(): void
    {
        $response = $this->get('/competitions');

        $response->assertStatus(200);
        $response->assertSee('PBB-01');
        $response->assertSee('PBB Berisyarat dan Koloni Tongkat');
        $response->assertSee('Pionering Bangunan Darurat');
        $response->assertSee('Blind Scoring');
    }

    /**
     * Memastikan jadwal lomba publik dapat diakses dengan format waktu WIB
     */
    public function test_schedules_page_displays_published_sessions(): void
    {
        $response = $this->get('/schedules');

        $response->assertStatus(200);
        $response->assertSee('WIB');
        $response->assertSee('Lapangan Utama Bumi Perkemahan');
        $response->assertSee('Regu');
    }

    /**
     * Memastikan halaman panduan pendaftaran dapat diakses
     */
    public function test_guide_page_is_accessible(): void
    {
        $response = $this->get('/guide');

        $response->assertStatus(200);
        $response->assertSee('Panduan Pendaftaran');
        $response->assertSee('Gugus Depan');
        $response->assertSee('Kartu Digital');
    }

    /**
     * Memastikan halaman klasemen publik dapat diakses
     */
    public function test_leaderboard_page_is_accessible(): void
    {
        $response = $this->get('/leaderboard');

        $response->assertStatus(200);
        $response->assertSee('Klasemen Perolehan Prestasi');
        $response->assertSee('Prestasi Tinggi');
        $response->assertSee('Prestasi Baik');
        $response->assertSee('Prestasi Cukup');
    }

    /**
     * Memastikan halaman publik tidak menampilkan informasi pribadi (PII) sensitif
     */
    public function test_public_pages_do_not_leak_sensitive_personal_data(): void
    {
        $response = $this->get('/');

        // Memastikan tidak ada token acak QR atau data sensitif yang bocor ke landing page publik
        $response->assertDontSee('qr_token');
        $response->assertDontSee('password_hash');
    }

    /**
     * Memastikan kriteria mata lomba (Individu vs Regu dengan interval jumlah peserta) bekerja dengan benar
     */
    public function test_competition_model_handles_participant_range_and_validation(): void
    {
        // 1. Uji kompetisi regu interval (Pionering: 3-5 peserta)
        $pionering = Competition::where('code', 'PION-02')->first();
        $this->assertNotNull($pionering);
        $this->assertTrue($pionering->isGroup());
        $this->assertEquals('Kelompok (3 - 5 Peserta)', $pionering->participantRangeLabel());
        $this->assertTrue($pionering->validateParticipantCount(3));
        $this->assertTrue($pionering->validateParticipantCount(4));
        $this->assertTrue($pionering->validateParticipantCount(5));
        $this->assertFalse($pionering->validateParticipantCount(2));
        $this->assertFalse($pionering->validateParticipantCount(6));

        // 2. Uji kompetisi regu fixed (PBB: tepat 8 peserta)
        $pbb = Competition::where('code', 'PBB-01')->first();
        $this->assertNotNull($pbb);
        $this->assertEquals('Regu Utuh (Tepat 8 Peserta)', $pbb->participantRangeLabel());
        $this->assertTrue($pbb->validateParticipantCount(8));
        $this->assertFalse($pbb->validateParticipantCount(7));

        // 3. Uji kompetisi individu (Pidato: 1 peserta)
        $speech = Competition::where('code', 'PID-09')->first();
        $this->assertNotNull($speech);
        $this->assertTrue($speech->isIndividual());
        $this->assertEquals('Individu (1 Peserta)', $speech->participantRangeLabel());
        $this->assertTrue($speech->validateParticipantCount(1));
        $this->assertFalse($speech->validateParticipantCount(2));
    }

    /**
     * Memastikan filter tipe sesi pada jadwal lomba publik (Regu, Kelompok, Individu) berfungsi
     */
    public function test_schedules_calendar_filters_by_session_type(): void
    {
        $response = $this->get('/schedules');
        $response->assertStatus(200);
        $response->assertSee('Semua Tipe Sesi');
        $response->assertSee('Regu');
        $response->assertSee('Kelompok');
        $response->assertSee('Individu');

        \Livewire\Livewire::test(\App\Livewire\Public\SchedulesCalendar::class)
            ->set('typeFilter', 'patrol')
            ->assertSet('typeFilter', 'patrol')
            ->set('typeFilter', 'group')
            ->assertSet('typeFilter', 'group')
            ->set('typeFilter', 'individual')
            ->assertSet('typeFilter', 'individual');
    }
}


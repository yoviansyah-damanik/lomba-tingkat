<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * - semua lingkungan : peran, akun Superadmin & Administrator, serta data resmi Juknis LT-III 2026
     *                      (kegiatan, 23 mata lomba beserta rubrik, jadwal sesi, sesi presensi, dan pengumuman)
     * - local/dev        : ditambah akun contoh (panitia, juri, gudep) dan data sampel lengkap (gudep, penilaian)
     * - production       : hanya data resmi di atas, tanpa akun atau data contoh
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            AdministratorSeeder::class,
        ]);

        // Akun contoh (juri, panitia, gudep) dibuat lebih dulu agar penugasan juri awal pada jadwal terisi
        if (! app()->isProduction()) {
            $this->call(UserSeeder::class);
        }

        $this->call([
            EventSeeder::class,
            AnnouncementSeeder::class,
            CompetitionSeeder::class,
            CompetitionScheduleSeeder::class,
            AssessmentCriteriaSeeder::class,
        ]);

        if (app()->isProduction()) {
            return;
        }

        $this->call([
            ContingentSeeder::class,
            CompetitionScoreSeeder::class,
        ]);

        if (app()->environment(['local', 'dev', 'development'])) {
            $this->call(SampleContestSeeder::class);
        }
    }
}

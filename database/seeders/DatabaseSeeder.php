<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * - production : peran + akun Superadmin & Administrator saja
     * - local/dev  : seluruh seeder, termasuk data sampel lengkap (juri, panitia, gudep, penilaian)
     * - lainnya    : seluruh seeder dasar tanpa data sampel (mis. testing)
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            AdministratorSeeder::class,
        ]);

        if (app()->isProduction()) {
            return;
        }

        $this->call([
            UserSeeder::class,
            EventSeeder::class,
            AnnouncementSeeder::class,
            CompetitionSeeder::class,
            CompetitionScheduleSeeder::class,
            AssessmentCriteriaSeeder::class,
            ContingentSeeder::class,
            CompetitionScoreSeeder::class,
        ]);

        if (app()->environment(['local', 'dev', 'development'])) {
            $this->call(SampleContestSeeder::class);
        }
    }
}

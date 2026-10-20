<?php

namespace Database\Seeders;

use App\Enums\PatrolGenderEnum;
use App\Enums\PatrolRoleEnum;
use App\Enums\RegistrationStatusEnum;
use App\Enums\RoleEnum;
use App\Models\Assessment;
use App\Models\Chaperone;
use App\Models\Competition;
use App\Models\CompetitionSchedule;
use App\Models\JudgeAssignment;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use App\Models\User;
use App\Services\AssessmentScoringService;
use App\Services\StandingsService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder sampel lengkap: juri, panitia, gugus depan terverifikasi (regu, peserta, bindamping),
 * jadwal lomba yang sudah berlangsung, dan penilaian juri yang difinalisasi hingga terbentuk klasemen regu putra dan regu putri berprestasi (terpisah).
 *
 * Jalankan: php artisan db:seed --class=SampleContestSeeder
 * Idempoten: aman dijalankan ulang (data yang sudah ada dilewati).
 */
class SampleContestSeeder extends Seeder
{
    private const PASSWORD = 'password123';

    private const SURNAMES = ['Siregar', 'Harahap', 'Daulay', 'Nasution', 'Lubis', 'Batubara', 'Pulungan', 'Tanjung', 'Hasibuan', 'Rangkuti', 'Pane', 'Dalimunthe', 'Ritonga', 'Matondang', 'Hutasuhut', 'Parinduri'];

    private const MALE_NAMES = ['Fahri', 'Rizky', 'Ahmad', 'Bintang', 'Dimas', 'Farhan', 'Ilham', 'Yusuf', 'Rafi', 'Aldi', 'Zaki', 'Hafiz', 'Naufal', 'Rendi', 'Fajar', 'Galih', 'Habib', 'Imam', 'Jaka', 'Kevin', 'Luthfi', 'Maulana', 'Nanda', 'Oki'];

    private const FEMALE_NAMES = ['Aisyah', 'Nabila', 'Salsa', 'Putri', 'Rahma', 'Syifa', 'Zahra', 'Anisa', 'Dinda', 'Fitri', 'Hana', 'Intan', 'Jihan', 'Khalisa', 'Laila', 'Mutiara', 'Nadia', 'Olivia', 'Qonita', 'Rania', 'Sabrina', 'Tiara', 'Umi', 'Vina'];

    /** @var array<int, array{school: string, kwarran: string, boys: string, girls: string}> */
    private const GROUPS = [
        ['school' => 'SMP Negeri 2 Padangsidimpuan', 'kwarran' => 'Kwarran Padangsidimpuan Utara', 'boys' => 'Garuda', 'girls' => 'Melati'],
        ['school' => 'SMP Negeri 3 Padangsidimpuan', 'kwarran' => 'Kwarran Padangsidimpuan Selatan', 'boys' => 'Elang', 'girls' => 'Mawar'],
        ['school' => 'SMP Negeri 4 Padangsidimpuan', 'kwarran' => 'Kwarran Padangsidimpuan Batunadua', 'boys' => 'Singa', 'girls' => 'Anggrek'],
        ['school' => 'SMP Negeri 5 Padangsidimpuan', 'kwarran' => 'Kwarran Padangsidimpuan Hutaimbaru', 'boys' => 'Harimau', 'girls' => 'Dahlia'],
        ['school' => 'SMP Negeri 6 Padangsidimpuan', 'kwarran' => 'Kwarran Padangsidimpuan Tenggara', 'boys' => 'Kancil', 'girls' => 'Teratai'],
        ['school' => 'SMP Negeri 7 Padangsidimpuan', 'kwarran' => 'Kwarran Padangsidimpuan Angkola Julu', 'boys' => 'Cendrawasih', 'girls' => 'Tulip'],
        ['school' => 'MTs Negeri 1 Padangsidimpuan', 'kwarran' => 'Kwarran Padangsidimpuan Utara', 'boys' => 'Merpati', 'girls' => 'Kenanga'],
        ['school' => 'MTs Negeri 2 Padangsidimpuan', 'kwarran' => 'Kwarran Padangsidimpuan Selatan', 'boys' => 'Rajawali Hitam', 'girls' => 'Seruni'],
        ['school' => 'SMP Swasta Al-Azhar Padangsidimpuan', 'kwarran' => 'Kwarran Padangsidimpuan Batunadua', 'boys' => 'Macan Tutul', 'girls' => 'Cempaka'],
        ['school' => 'SMP Swasta Muhammadiyah Padangsidimpuan', 'kwarran' => 'Kwarran Padangsidimpuan Hutaimbaru', 'boys' => 'Banteng', 'girls' => 'Flamboyan'],
        ['school' => 'SMP Swasta Tri Dharma Padangsidimpuan', 'kwarran' => 'Kwarran Padangsidimpuan Tenggara', 'boys' => 'Beruang', 'girls' => 'Lavender'],
    ];

    private const JUDGES = [
        ['id' => 'ID-JURI0002', 'name' => 'Drs. Ahmad Fauzi Lubis'],
        ['id' => 'ID-JURI0003', 'name' => 'Hj. Rosmawati Harahap, S.Pd'],
        ['id' => 'ID-JURI0004', 'name' => 'Kak Bambang Siregar, M.Pd'],
        ['id' => 'ID-JURI0005', 'name' => 'Kak Nurhayati Nasution, S.Pd'],
        ['id' => 'ID-JURI0006', 'name' => 'Ir. Syahrul Daulay'],
        ['id' => 'ID-JURI0007', 'name' => 'Kak Marlina Pulungan, S.Pd'],
        ['id' => 'ID-JURI0008', 'name' => 'Kak Irwansyah Batubara, S.Kom'],
    ];

    private const COMMITTEES = [
        ['id' => 'ID-PANITIA2', 'name' => 'Kak Hendra Rangkuti'],
        ['id' => 'ID-PANITIA3', 'name' => 'Kak Sri Wahyuni Pane'],
        ['id' => 'ID-PANITIA4', 'name' => 'Kak Rudi Hasibuan, S.Pd'],
        ['id' => 'ID-PANITIA5', 'name' => 'Kak Dewi Sartika Ritonga'],
    ];

    private const ARENAS = [
        'Lapangan Utama Bumi Perkemahan',
        'Zona Ketangkasan Lapangan Barat',
        'Pos Semaphore & Morse Lapangan 1',
        'Gerbang Start & Pos Rute Alam Bebas',
        'Tenda Kesehatan Pos PPGD',
        'Panggung Terbuka Api Unggun',
        'Aula Utama Kwarcab Padangsidimpuan',
    ];

    public function run(): void
    {
        mt_srand(2026); // hasil nilai konsisten setiap dijalankan

        $admin = User::where('username', 'admin')->first();

        try {
            $judges = $this->seedJudges();
            $this->seedCommittees();
            $this->seedContingents($admin);
            $this->seedScoredSchedules($judges);
        } finally {
            mt_srand(); // kembalikan generator acak global agar tidak memengaruhi proses lain
        }

        $this->reportStandings();
    }

    /**
     * Juri contoh (ditambah juri yang sudah ada di UserSeeder)
     *
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function seedJudges()
    {
        foreach (self::JUDGES as $row) {
            $user = User::firstOrCreate(
                ['id_number' => $row['id']],
                ['name' => $row['name'], 'password' => Hash::make(self::PASSWORD), 'phone_number' => '0812'.random_int(10000000, 99999999), 'is_active' => true]
            );
            $user->assignRole(RoleEnum::Judge);
        }

        return User::whereHas('roles', fn ($q) => $q->where('name', RoleEnum::Judge->value))
            ->where('is_active', true)->orderBy('id_number')->get()
            ->filter(fn (User $u) => ! $u->isAdmin())->values();
    }

    private function seedCommittees(): void
    {
        foreach (self::COMMITTEES as $row) {
            $user = User::firstOrCreate(
                ['id_number' => $row['id']],
                ['name' => $row['name'], 'password' => Hash::make(self::PASSWORD), 'phone_number' => '0813'.random_int(10000000, 99999999), 'is_active' => true]
            );
            $user->assignRole(RoleEnum::Committee);
        }
    }

    /**
     * Gugus depan terverifikasi lengkap dengan regu putra/putri, 8 peserta per regu, dan bindamping
     */
    private function seedContingents(?User $admin): void
    {
        $this->verifyExistingContingent($admin);

        foreach (self::GROUPS as $i => $row) {
            $number = $i + 2; // gudep01 sudah ada di ContingentSeeder
            $username = 'gudep'.str_pad((string) $number, 2, '0', STR_PAD_LEFT);
            $male = '01-'.str_pad((string) (($number * 2) - 1), 3, '0', STR_PAD_LEFT);
            $female = '01-'.str_pad((string) ($number * 2), 3, '0', STR_PAD_LEFT);

            $user = User::firstOrCreate(
                ['username' => $username],
                ['name' => 'Gudep '.$row['school'], 'password' => Hash::make(self::PASSWORD), 'phone_number' => '0852'.random_int(10000000, 99999999), 'is_active' => true]
            );
            $user->assignRole(RoleEnum::ScoutGroup);

            $group = ScoutGroup::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'registration_code' => 'GDP-PSP-'.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                    'school_name' => $row['school'],
                    'group_number' => "{$male} & {$female}",
                    'male_group_number' => $male,
                    'female_group_number' => $female,
                    'kwarran_name' => $row['kwarran'],
                    'address' => 'Jl. Sudirman No. '.($number * 3).', '.$row['kwarran'].', Kota Padangsidimpuan',
                    'contact_person' => 'Kak '.self::MALE_NAMES[$i].' '.self::SURNAMES[$i % count(self::SURNAMES)].', S.Pd',
                    'contact_phone' => $user->phone_number,
                    'verification_status' => RegistrationStatusEnum::Verified,
                    'verified_by' => $admin?->id,
                    'verified_at' => now()->subDays(7),
                ]
            );

            $this->seedPatrol($group, PatrolGenderEnum::Boys, $row['boys'], $number, $male);
            $this->seedPatrol($group, PatrolGenderEnum::Girls, $row['girls'], $number, $female);
            $this->seedChaperones($group, $number);
        }
    }

    /**
     * Gudep contoh dari ContingentSeeder (SMP Negeri 1) ikut diverifikasi agar mengikuti klasemen
     */
    private function verifyExistingContingent(?User $admin): void
    {
        $group = ScoutGroup::where('registration_code', 'GDP-PSP-01')->first();

        if (! $group || $group->verification_status === RegistrationStatusEnum::Verified) {
            return;
        }

        // Tanpa event model agar tidak memicu notifikasi push hasil verifikasi
        ScoutGroup::withoutEvents(fn () => $group->update([
            'verification_status' => RegistrationStatusEnum::Verified,
            'verified_by' => $admin?->id,
            'verified_at' => now()->subDays(7),
        ]));

        Patrol::where('scout_group_id', $group->id)->update(['status' => RegistrationStatusEnum::Verified]);
        Participant::whereIn('patrol_id', Patrol::where('scout_group_id', $group->id)->pluck('id'))
            ->update(['status' => RegistrationStatusEnum::Verified]);
        Chaperone::where('scout_group_id', $group->id)->update(['status' => RegistrationStatusEnum::Verified]);
    }

    private function seedPatrol(ScoutGroup $group, PatrolGenderEnum $gender, string $patrolName, int $groupNumber, string $gudepNumber): void
    {
        $patrol = Patrol::firstOrCreate(
            ['scout_group_id' => $group->id, 'gender_category' => $gender],
            ['patrol_name' => $patrolName, 'status' => RegistrationStatusEnum::Verified]
        );

        $isBoys = $gender === PatrolGenderEnum::Boys;
        $names = $isBoys ? self::MALE_NAMES : self::FEMALE_NAMES;

        for ($m = 0; $m < 8; $m++) {
            $role = match ($m) {
                0 => PatrolRoleEnum::PatrolLeader,
                1 => PatrolRoleEnum::AssistantPatrolLeader,
                default => PatrolRoleEnum::Member,
            };
            $fullName = $names[($groupNumber * 3 + $m * 5) % count($names)].' '.self::SURNAMES[($groupNumber + $m * 7) % count(self::SURNAMES)];

            Participant::firstOrCreate(
                ['patrol_id' => $patrol->id, 'full_name' => $fullName],
                [
                    'scout_id' => sprintf('02.21.%02d.%03d', $groupNumber, ($isBoys ? 0 : 20) + $m + 1),
                    'role_in_patrol' => $role,
                    'class_grade' => $m % 2 === 0 ? 'VIII' : 'VII',
                    'gender' => $isBoys ? 'male' : 'female',
                    'birth_date' => Carbon::create(2011 + ($m % 2), random_int(1, 12), random_int(1, 28))->format('Y-m-d'),
                    'birth_place' => 'Padangsidimpuan',
                    'religion' => 'Islam',
                    'blood_type' => ['A', 'B', 'AB', 'O'][$m % 4],
                    'home_address' => 'Jl. Merdeka No. '.($m + 10).', Padangsidimpuan',
                    'city' => 'Padangsidimpuan',
                    'postal_code' => '22715',
                    'phone' => '0812'.random_int(10000000, 99999999),
                    'parent_name' => 'Orang Tua '.$fullName,
                    'parent_job' => ['PNS', 'Wiraswasta', 'Guru', 'Petani'][$m % 4],
                    'parent_phone' => '0813'.random_int(10000000, 99999999),
                    'status' => RegistrationStatusEnum::Verified,
                ]
            );
        }
    }

    private function seedChaperones(ScoutGroup $group, int $groupNumber): void
    {
        foreach (['male' => 'Putra', 'female' => 'Putri'] as $gender => $label) {
            $names = $gender === 'male' ? self::MALE_NAMES : self::FEMALE_NAMES;

            Chaperone::firstOrCreate(
                ['scout_group_id' => $group->id, 'gender' => $gender],
                [
                    'full_name' => 'Kak '.$names[($groupNumber + 11) % count($names)].' '.self::SURNAMES[($groupNumber + 3) % count(self::SURNAMES)].', S.Pd',
                    'scout_id' => sprintf('02.08.%02d.9%02d', $groupNumber, $gender === 'male' ? 1 : 2),
                    'national_identity_number' => '1277'.str_pad((string) random_int(0, 999999999999), 12, '0', STR_PAD_LEFT),
                    'phone_number' => '0812'.random_int(10000000, 99999999),
                    'birth_place' => 'Padangsidimpuan',
                    'birth_date' => Carbon::create(1985 + ($groupNumber % 10), random_int(1, 12), random_int(1, 28))->format('Y-m-d'),
                    'religion' => 'Islam',
                    'status' => RegistrationStatusEnum::Verified,
                ]
            );
        }
    }

    /**
     * Jadwal lomba yang sudah berlangsung (kemarin) per cabang berrubrik dan per kategori regu,
     * ditangani 3 juri, lalu dinilai dan difinalisasi lewat alur resmi AssessmentScoringService.
     *
     * @param  \Illuminate\Support\Collection<int, User>  $judges
     */
    private function seedScoredSchedules($judges): void
    {
        $scoring = app(AssessmentScoringService::class);
        $competitions = Competition::with('criteria')->whereHas('criteria')->orderBy('code')->get();

        if ($competitions->isEmpty() || $judges->count() < 3) {
            $this->command?->warn('Cabang lomba berrubrik atau juri belum tersedia. Jalankan CompetitionSeeder & AssessmentCriteriaSeeder terlebih dahulu.');

            return;
        }

        // Kekuatan dasar regu (acak terkendali) agar juara umum terbentuk wajar namun bervariasi
        $strength = Patrol::where('status', RegistrationStatusEnum::Verified)->pluck('id')
            ->mapWithKeys(fn ($id) => [$id => 66 + (mt_rand(0, 280) / 10)]);

        $day = Carbon::yesterday()->setTimezone('Asia/Jakarta');
        $slot = 0;

        try {
            foreach ($competitions as $ci => $competition) {
                foreach (['boys' => 'Penggalang Putra', 'girls' => 'Penggalang Putri'] as $gender => $label) {
                    $start = $day->copy()->setTime(7, 0)->addMinutes($slot * 100);
                    $schedule = CompetitionSchedule::updateOrCreate(
                        ['competition_id' => $competition->id, 'session_name' => "Final {$competition->code} — {$label}"],
                        [
                            'participant_type' => $competition->participant_type,
                            'gender_category' => $gender,
                            'arena_location' => self::ARENAS[$ci % count(self::ARENAS)],
                            'start_time' => $start,
                            'end_time' => $start->copy()->addMinutes(90),
                            'status' => 'completed',
                            'is_published' => true,
                        ]
                    );
                    $slot++;

                    $panel = collect(range(0, 2))->map(fn ($k) => $judges[($slot * 3 + $k) % $judges->count()]);
                    foreach ($panel as $k => $judge) {
                        JudgeAssignment::updateOrCreate(
                            ['competition_schedule_id' => $schedule->id, 'user_id' => $judge->id],
                            ['role' => $k === 0 ? 'lead_judge' : 'scoring_judge', 'is_confirmed' => true, 'notes' => 'Panel juri sesi final']
                        );
                    }

                    // Waktu sistem digeser ke tengah sesi agar lolos jendela penilaian juri
                    Carbon::setTestNow($start->copy()->addMinutes(60));

                    $patrols = Patrol::where('status', RegistrationStatusEnum::Verified)
                        ->where('gender_category', $gender)->get();

                    foreach ($patrols as $patrol) {
                        // Bonus/penalti spesifik cabang agar setiap lomba punya juara berbeda
                        $specialty = mt_rand(-80, 80) / 10;
                        foreach ($panel as $judge) {
                            $this->scoreAndFinalize($scoring, $schedule, $competition, $patrol, $judge, ($strength[$patrol->id] ?? 75) + $specialty);
                        }
                    }

                    Carbon::setTestNow();
                }
            }
        } finally {
            Carbon::setTestNow();
        }
    }

    private function scoreAndFinalize(AssessmentScoringService $scoring, CompetitionSchedule $schedule, Competition $competition, Patrol $patrol, User $judge, float $base): void
    {
        $exists = Assessment::where('competition_schedule_id', $schedule->id)
            ->where('patrol_id', $patrol->id)->where('judge_id', $judge->id)->exists();

        if ($exists) {
            return;
        }

        $scores = [];
        foreach ($competition->criteria as $criterion) {
            $value = $base + (mt_rand(-50, 50) / 10);
            $scores[$criterion->id] = round(max((float) $criterion->min_score, min((float) $criterion->max_score, $value)), 1);
        }

        $assessment = $scoring->saveDraft([
            'competition_schedule_id' => $schedule->id,
            'patrol_id' => $patrol->id,
            'criteria_scores' => $scores,
            'notes' => 'Penilaian resmi sesi final '.$competition->code.'.',
        ], $judge);

        $scoring->finalizeScore($assessment->id, $judge);
    }

    /**
     * Ringkasan klasemen regu berprestasi putra dan putri (terpisah) pada keluaran konsol
     */
    private function reportStandings(): void
    {
        $service = app(StandingsService::class);

        foreach (['boys' => 'Regu Putra Berprestasi', 'girls' => 'Regu Putri Berprestasi'] as $gender => $title) {
            $this->command?->info("Klasemen {$title} (5 teratas):");
            foreach ($service->getPatrolStandings($gender, null, true)->take(5) as $row) {
                $this->command?->line(sprintf(
                    ' #%d %-18s %-40s Emas %d  Perak %d  Perunggu %d',
                    $row['rank'], $row['real_patrol_name'], $row['real_school_name'], $row['gold'], $row['silver'], $row['bronze']
                ));
            }
        }
    }
}

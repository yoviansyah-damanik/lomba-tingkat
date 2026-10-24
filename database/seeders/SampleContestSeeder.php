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
use App\Services\ContingentAccountService;
use App\Models\ScoutGroup;
use App\Models\User;
use App\Services\AssessmentScoringService;
use App\Services\StandingsService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder sampel lengkap: juri, panitia, 11 gugus depan (regu, peserta, bindamping) beserta gudep01 (dari ContingentSeeder);
 * SEMUA gudep berstatus Diajukan dan tidak ada yang diverifikasi, agar alur verifikasi Administrator dapat dicoba langsung.
 * Penilaian juri contoh untuk 11 gudep sudah difinalisasi; klasemen regu putra dan putri terbentuk setelah gudepnya diverifikasi.
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

    /** @var array<int, array{boys?: array<int, string>, girls?: array<int, string>}> Regu tambahan (indeks GROUPS) untuk gudep yang mengirim lebih dari satu regu sekategori */
    private const EXTRA_PATROLS = [
        0 => ['boys' => ['Garuda Muda'], 'girls' => ['Melati Putih']],
        3 => ['girls' => ['Dahlia Putih']],
        5 => ['boys' => ['Cendrawasih Muda', 'Cendrawasih Jaya']],
    ];

    /** Indeks GROUPS gudep contoh yang sudah terverifikasi */
    private const VERIFIED_GROUP = 0;

    private const JUDGES = [
        ['id' => 'A-300002', 'name' => 'Drs. Ahmad Fauzi Lubis'],
        ['id' => 'A-300003', 'name' => 'Hj. Rosmawati Harahap, S.Pd'],
        ['id' => 'A-300004', 'name' => 'Kak Bambang Siregar, M.Pd'],
        ['id' => 'A-300005', 'name' => 'Kak Nurhayati Nasution, S.Pd'],
        ['id' => 'A-300006', 'name' => 'Ir. Syahrul Daulay'],
        ['id' => 'A-300007', 'name' => 'Kak Marlina Pulungan, S.Pd'],
        ['id' => 'A-300008', 'name' => 'Kak Irwansyah Batubara, S.Kom'],
    ];

    private const COMMITTEES = [
        ['id' => 'A-400002', 'name' => 'Kak Hendra Rangkuti'],
        ['id' => 'A-400003', 'name' => 'Kak Sri Wahyuni Pane'],
        ['id' => 'A-400004', 'name' => 'Kak Rudi Hasibuan, S.Pd'],
        ['id' => 'A-400005', 'name' => 'Kak Dewi Sartika Ritonga'],
    ];

    /** @var array<int, string> ID gudep sampel (di luar gudep01) yang menerima nilai contoh */
    private array $sampleGroupIds = [];

    public function run(): void
    {
        mt_srand(2026); // hasil nilai konsisten setiap dijalankan

        try {
            $judges = $this->seedJudges();
            $this->seedCommittees();
            $this->seedContingents();
            $this->verifySampleContingent();
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
            ->where('is_active', true)->orderBy('id_number')->get();
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
     * Gugus depan berstatus Diajukan (belum diverifikasi) lengkap dengan regu putra/putri, 8 peserta per regu, dan bindamping
     */
    private function seedContingents(): void
    {
        foreach (self::GROUPS as $i => $row) {
            $number = $i + 2; // gudep01 sudah ada di ContingentSeeder
            $male = '01-'.str_pad((string) (($number * 2) - 1), 3, '0', STR_PAD_LEFT);
            $female = '01-'.str_pad((string) ($number * 2), 3, '0', STR_PAD_LEFT);

            // ID Gudep G-{no_gudep}; username dan ID akun penanggung jawab persis sama dengan ID Gudep
            $gudepId = ScoutGroup::where('male_group_number', $male)->value('registration_code')
                ?? ScoutGroup::generateGudepId(['male' => $male, 'female' => $female]);

            $user = User::firstOrCreate(
                ['username' => $gudepId],
                ['id_number' => $gudepId, 'name' => 'Gudep '.$row['school'], 'password' => Hash::make(self::PASSWORD), 'phone_number' => '0852'.random_int(10000000, 99999999), 'is_active' => true]
            );
            $user->assignRole(RoleEnum::ScoutGroup);

            $group = ScoutGroup::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'registration_code' => $gudepId,
                    'school_name' => $row['school'],
                    'group_number' => "{$male} & {$female}",
                    'male_group_number' => $male,
                    'female_group_number' => $female,
                    'kwarran_name' => $row['kwarran'],
                    'address' => 'Jl. Sudirman No. '.($number * 3).', '.$row['kwarran'].', Kota Padangsidimpuan',
                    'contact_person' => 'Kak '.self::MALE_NAMES[$i].' '.self::SURNAMES[$i % count(self::SURNAMES)].', S.Pd',
                    'contact_phone' => $user->phone_number,
                    'verification_status' => RegistrationStatusEnum::Submitted,
                ]
            );

            $this->sampleGroupIds[] = $group->id;

            $this->seedPatrol($group, PatrolGenderEnum::Boys, $row['boys'], $number, $male);
            $this->seedPatrol($group, PatrolGenderEnum::Girls, $row['girls'], $number, $female);

            foreach (self::EXTRA_PATROLS[$i]['boys'] ?? [] as $v => $name) {
                $this->seedPatrol($group, PatrolGenderEnum::Boys, $name, $number, $male, $v + 1);
            }
            foreach (self::EXTRA_PATROLS[$i]['girls'] ?? [] as $v => $name) {
                $this->seedPatrol($group, PatrolGenderEnum::Girls, $name, $number, $female, $v + 1);
            }
            $this->seedChaperones($group, $number);
        }
    }

    private function seedPatrol(ScoutGroup $group, PatrolGenderEnum $gender, string $patrolName, int $groupNumber, string $gudepNumber, int $variant = 0): void
    {
        $patrol = Patrol::firstOrCreate(
            ['scout_group_id' => $group->id, 'patrol_name' => $patrolName],
            ['gender_category' => $gender, 'status' => RegistrationStatusEnum::Submitted]
        );

        $isBoys = $gender === PatrolGenderEnum::Boys;
        $names = $isBoys ? self::MALE_NAMES : self::FEMALE_NAMES;

        for ($m = 0; $m < 8; $m++) {
            $role = match ($m) {
                0 => PatrolRoleEnum::PatrolLeader,
                1 => PatrolRoleEnum::AssistantPatrolLeader,
                default => PatrolRoleEnum::Member,
            };
            $fullName = $names[($groupNumber * 3 + $m * 5 + $variant * 7) % count($names)].' '.self::SURNAMES[($groupNumber + $m * 7 + $variant * 3) % count(self::SURNAMES)];

            Participant::firstOrCreate(
                ['patrol_id' => $patrol->id, 'full_name' => $fullName],
                [
                    'scout_id' => sprintf('02.21.%02d.%03d', $groupNumber, ($isBoys ? 0 : 20) + ($variant * 40) + $m + 1),
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
                    'status' => RegistrationStatusEnum::Submitted,
                ]
            );
        }
    }

    private function seedChaperones(ScoutGroup $group, int $groupNumber): void
    {
        foreach (['male' => 'Putra', 'female' => 'Putri'] as $gender => $label) {
            $names = $gender === 'male' ? self::MALE_NAMES : self::FEMALE_NAMES;

            $chaperone = Chaperone::firstOrCreate(
                ['scout_group_id' => $group->id, 'gender' => $gender],
                [
                    'full_name' => 'Kak '.$names[($groupNumber + 11) % count($names)].' '.self::SURNAMES[($groupNumber + 3) % count(self::SURNAMES)].', S.Pd',
                    'scout_id' => sprintf('02.08.%02d.9%02d', $groupNumber, $gender === 'male' ? 1 : 2),
                    'national_identity_number' => '1277'.str_pad((string) random_int(0, 999999999999), 12, '0', STR_PAD_LEFT),
                    'phone_number' => '0812'.random_int(10000000, 99999999),
                    'birth_place' => 'Padangsidimpuan',
                    'birth_date' => Carbon::create(1985 + ($groupNumber % 10), random_int(1, 12), random_int(1, 28))->format('Y-m-d'),
                    'religion' => 'Islam',
                    'status' => RegistrationStatusEnum::Submitted,
                ]
            );

            // Satu bindamping mendampingi seluruh regu sekategori
            $group->syncChaperonePatrols($chaperone, $group->patrols()->where('gender_category', $gender === 'male' ? 'boys' : 'girls')->pluck('id')->all());
        }
    }

    /**
     * Satu gudep contoh (indeks VERIFIED_GROUP di GROUPS, memiliki beberapa regu sekategori) sudah diverifikasi
     * Administrator beserta akun peserta/bindamping, sehingga nilainya langsung masuk klasemen
     */
    private function verifySampleContingent(): void
    {
        $group = ScoutGroup::find($this->sampleGroupIds[self::VERIFIED_GROUP] ?? null);

        if (! $group || $group->verification_status === RegistrationStatusEnum::Verified) {
            return;
        }

        $group->update(['verification_status' => RegistrationStatusEnum::Verified, 'verified_at' => now(), 'revision_notes' => null]);
        $group->patrols()->update(['status' => RegistrationStatusEnum::Verified]);
        Participant::whereIn('patrol_id', $group->patrols()->pluck('id'))->update(['status' => RegistrationStatusEnum::Verified]);
        $group->chaperones()->update(['status' => RegistrationStatusEnum::Verified]);

        app(ContingentAccountService::class)->provision($group);
    }

    /**
     * Sesi lomba resmi (dari CompetitionScheduleSeeder) per mata lomba berrubrik dan per kategori regu dinilai
     * oleh panel 3 juri, lalu difinalisasi lewat alur resmi AssessmentScoringService. Waktu sistem digeser ke
     * dalam jendela penilaian masing-masing sesi, sehingga tanggal penilaian mengikuti jadwal Juknis.
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

        // Kekuatan dasar regu (acak terkendali) agar peringkat terbentuk wajar namun bervariasi
        $strength = Patrol::whereIn('scout_group_id', $this->sampleGroupIds)->pluck('id')
            ->mapWithKeys(fn ($id) => [$id => 66 + (mt_rand(0, 280) / 10)]);

        $slot = 0;

        try {
            foreach ($competitions as $competition) {
                foreach (['boys' => 'Penggalang Putra', 'girls' => 'Penggalang Putri'] as $gender => $label) {
                    $schedule = $this->scheduleFor($competition, $gender, $label, $slot++);
                    $panel = $this->panelFor($schedule, $judges, $slot);

                    // Waktu sistem digeser ke dalam sesi agar lolos jendela penilaian juri
                    Carbon::setTestNow($schedule->start_time->copy()->addMinutes(30));

                    $patrols = Patrol::whereIn('scout_group_id', $this->sampleGroupIds)
                        ->where('gender_category', $gender)->get();

                    foreach ($patrols as $patrol) {
                        // Bonus/penalti spesifik cabang agar setiap lomba punya juara berbeda
                        $specialty = mt_rand(-80, 80) / 10;
                        foreach ($panel as $judge) {
                            $this->scoreAndFinalize($scoring, $schedule, $competition, $patrol, $judge, ($strength[$patrol->id] ?? 75) + $specialty);
                        }
                    }

                    Carbon::setTestNow();

                    // Nilai contoh sudah terisi, tetapi status "Selesai" hanya bila waktu sesi memang sudah berakhir
                    if ($schedule->end_time->isPast()) {
                        $schedule->update(['status' => 'completed']);
                    }
                }
            }
        } finally {
            Carbon::setTestNow();
        }
    }

    /**
     * Sesi resmi dari seeder jadwal; bila belum ada dibuatkan sesi cadangan pada hari pertama kegiatan
     */
    private function scheduleFor(Competition $competition, string $gender, string $label, int $slot): CompetitionSchedule
    {
        $schedule = CompetitionSchedule::where('competition_id', $competition->id)->where('gender_category', $gender)->first();

        if ($schedule) {
            return $schedule;
        }

        $start = Carbon::parse('2026-11-14 07:00', 'Asia/Jakarta')->addMinutes($slot * 100);

        return CompetitionSchedule::create([
            'competition_id' => $competition->id,
            'session_name' => "{$competition->title} — {$label}",
            'participant_type' => $competition->participant_type,
            'gender_category' => $gender,
            'arena_location' => 'Lapangan Pulo Baok, Padangsidimpuan Tenggara',
            'start_time' => $start,
            'end_time' => $start->copy()->addMinutes(90),
            'status' => 'scheduled',
            'is_published' => true,
        ]);
    }

    /**
     * Panel 3 juri: juri yang sudah ditugaskan pada sesi, dilengkapi juri lain secara bergilir
     *
     * @param  \Illuminate\Support\Collection<int, User>  $judges
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function panelFor(CompetitionSchedule $schedule, $judges, int $slot)
    {
        $panel = $judges->whereIn('id', $schedule->judgeAssignments()->pluck('user_id'))->take(3)->values();

        for ($k = 0; $panel->count() < 3; $k++) {
            $candidate = $judges[($slot * 3 + $k) % $judges->count()];

            if (! $panel->contains('id', $candidate->id)) {
                $panel->push($candidate);
            }
        }

        foreach ($panel as $k => $judge) {
            JudgeAssignment::updateOrCreate(
                ['competition_schedule_id' => $schedule->id, 'user_id' => $judge->id],
                ['role' => $k === 0 ? 'lead_judge' : 'scoring_judge', 'is_confirmed' => true, 'notes' => 'Panel juri sesi lomba']
            );
        }

        return $panel;
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

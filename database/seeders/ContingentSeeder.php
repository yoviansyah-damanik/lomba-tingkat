<?php

namespace Database\Seeders;

use App\Enums\PatrolGenderEnum;
use App\Enums\PatrolRoleEnum;
use App\Enums\RegistrationStatusEnum;
use App\Enums\RoleEnum;
use App\Models\Chaperone;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder data kontingen contoh: Gugus Depan, Regu Putra & Putri, Peserta, dan Bindamping
 */
class ContingentSeeder extends Seeder
{
    public function run(): void
    {
        $gudepUser = User::where('username', 'gudep01')->first();

        if (! $gudepUser) {
            return;
        }

        // 1. Gugus Depan
        $scoutGroup = ScoutGroup::firstOrCreate(
            ['user_id' => $gudepUser->id],
            [
                'registration_code' => 'GDP-PSP-01',
                'school_name' => 'SMP Negeri 1 Padangsidimpuan',
                'group_number' => '01-001 & 01-002',
                'male_group_number' => '01-001',
                'female_group_number' => '01-002',
                'address' => 'Jl. Teuku Umar No. 12, Padangsidimpuan Selatan',
                'contact_person' => 'Drs. H. M. Siregar',
                'contact_phone' => '081234567894',
                'verification_status' => RegistrationStatusEnum::Submitted,
            ]
        );

        // 2. Regu Putra: Rajawali
        $boysPatrol = Patrol::firstOrCreate(
            [
                'scout_group_id' => $scoutGroup->id,
                'gender_category' => PatrolGenderEnum::Boys,
            ],
            [
                'patrol_name' => 'Rajawali',
                'status' => RegistrationStatusEnum::Submitted,
            ]
        );

        // Peserta Regu Putra (8 Orang)
        $boysMembers = [
            ['name' => 'Fahri Siregar', 'role' => PatrolRoleEnum::PatrolLeader],
            ['name' => 'Rizky Harahap', 'role' => PatrolRoleEnum::AssistantPatrolLeader],
            ['name' => 'Ahmad Daulay', 'role' => PatrolRoleEnum::Member],
            ['name' => 'Bintang Nasution', 'role' => PatrolRoleEnum::Member],
            ['name' => 'Dimas Lubis', 'role' => PatrolRoleEnum::Member],
            ['name' => 'Farhan Batubara', 'role' => PatrolRoleEnum::Member],
            ['name' => 'Ilham Pulungan', 'role' => PatrolRoleEnum::Member],
            ['name' => 'Yusuf Tanjung', 'role' => PatrolRoleEnum::Member],
        ];

        foreach ($boysMembers as $idx => $m) {
            $participant = Participant::firstOrCreate(
                [
                    'patrol_id' => $boysPatrol->id,
                    'full_name' => $m['name'],
                ],
                [
                    'scout_id' => '02.21.01.'.str_pad($idx + 1, 3, '0', STR_PAD_LEFT),
                    'role_in_patrol' => $m['role'],
                    'gender' => 'male',
                    'birth_date' => '2011-05-15',
                    'birth_place' => 'Padangsidimpuan',
                    'religion' => 'Islam',
                    'blood_type' => 'O',
                    'home_address' => 'Jl. Merdeka No. 45, Wek II',
                    'city' => 'Padangsidimpuan',
                    'postal_code' => '22711',
                    'home_phone' => '0634-21980',
                    'phone' => '0812654320'.str_pad($idx + 1, 2, '0', STR_PAD_LEFT),
                    'parent_name' => 'Baharuddin Siregar',
                    'parent_job' => 'PNS',
                    'parent_address' => 'Jl. Merdeka No. 45, Wek II',
                    'parent_city' => 'Padangsidimpuan',
                    'parent_postal_code' => '22711',
                    'parent_home_phone' => '0634-21980',
                    'parent_phone' => '081398765401',
                    'formal_education' => [
                        ['level' => 'SD', 'school_name' => 'SD Negeri 200101 Padangsidimpuan', 'city' => 'Padangsidimpuan', 'graduation_year' => '2023', 'notes' => 'Lulus'],
                        ['level' => 'SMP', 'school_name' => 'SMP Negeri 1 Padangsidimpuan', 'city' => 'Padangsidimpuan', 'graduation_year' => '2026', 'notes' => 'Kelas VIII'],
                    ],
                    'scout_siaga' => [
                        'year' => '2019 - 2023',
                        'group_number' => '01-093',
                        'pangkalan' => 'SD Negeri 200101',
                        'levels' => ['mula' => '2020', 'bantu' => '2021', 'tata' => '2022', 'garuda' => '2023'],
                        'activities' => ['Pesta Siaga Ranting', 'Pesta Siaga Cabang 2022', 'Bazar Siaga', '', '', ''],
                        'tkk' => ['Menabung', 'Sholat', 'Pengamat', 'Juru Masak', 'P3K', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''],
                    ],
                    'scout_penggalang' => [
                        'year' => '2023 - Sekarang',
                        'group_number' => '01-093',
                        'pangkalan' => 'SMP Negeri 1 Padangsidimpuan',
                        'levels' => ['ramu' => '2024', 'rakit' => '2025', 'terap' => '2026', 'garuda' => ''],
                        'activities' => ['LT-I Gudep 2024', 'LT-II Kwarran Padangsidimpuan Utara 2025', 'Jambore Ranting', 'Kemah Bakti', '', '', ''],
                        'tkk_purwa' => ['P3K', 'Pengatur Meja Makan', 'Pengamat', 'Juru Kebun', '', '', '', '', '', ''],
                        'tkk_madya' => ['Sholat', 'Gerak Jalan', 'Berkemah', '', '', '', '', '', '', ''],
                        'tkk_utama' => ['P3K', '', '', '', '', '', '', '', '', ''],
                    ],
                    'status' => RegistrationStatusEnum::Submitted,
                ]
            );

            // Update if existing record
            $participant->update([
                'religion' => 'Islam',
                'home_address' => 'Jl. Merdeka No. 45, Wek II',
                'city' => 'Padangsidimpuan',
                'postal_code' => '22711',
                'home_phone' => '0634-21980',
                'phone' => '0812654320'.str_pad($idx + 1, 2, '0', STR_PAD_LEFT),
                'parent_name' => 'Baharuddin Siregar',
                'parent_job' => 'PNS',
                'parent_address' => 'Jl. Merdeka No. 45, Wek II',
                'parent_city' => 'Padangsidimpuan',
                'parent_postal_code' => '22711',
                'parent_home_phone' => '0634-21980',
                'parent_phone' => '081398765401',
                'formal_education' => [
                    ['level' => 'SD', 'school_name' => 'SD Negeri 200101 Padangsidimpuan', 'city' => 'Padangsidimpuan', 'graduation_year' => '2023', 'notes' => 'Lulus'],
                    ['level' => 'SMP', 'school_name' => 'SMP Negeri 1 Padangsidimpuan', 'city' => 'Padangsidimpuan', 'graduation_year' => '2026', 'notes' => 'Kelas VIII'],
                ],
                'scout_siaga' => [
                    'year' => '2019 - 2023',
                    'group_number' => '01-093',
                    'pangkalan' => 'SD Negeri 200101',
                    'levels' => ['mula' => '2020', 'bantu' => '2021', 'tata' => '2022', 'garuda' => '2023'],
                    'activities' => ['Pesta Siaga Ranting', 'Pesta Siaga Cabang 2022', 'Bazar Siaga', '', '', ''],
                    'tkk' => ['Menabung', 'Sholat', 'Pengamat', 'Juru Masak', 'P3K', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''],
                ],
                'scout_penggalang' => [
                    'year' => '2023 - Sekarang',
                    'group_number' => '01-093',
                    'pangkalan' => 'SMP Negeri 1 Padangsidimpuan',
                    'levels' => ['ramu' => '2024', 'rakit' => '2025', 'terap' => '2026', 'garuda' => ''],
                    'activities' => ['LT-I Gudep 2024', 'LT-II Kwarran Padangsidimpuan Utara 2025', 'Jambore Ranting', 'Kemah Bakti', '', '', ''],
                    'tkk_purwa' => ['P3K', 'Pengatur Meja Makan', 'Pengamat', 'Juru Kebun', '', '', '', '', '', ''],
                    'tkk_madya' => ['Sholat', 'Gerak Jalan', 'Berkemah', '', '', '', '', '', '', ''],
                    'tkk_utama' => ['P3K', '', '', '', '', '', '', '', '', ''],
                ],
            ]);
        }

        // 3. Regu Putri: Melati
        $girlsPatrol = Patrol::firstOrCreate(
            [
                'scout_group_id' => $scoutGroup->id,
                'gender_category' => PatrolGenderEnum::Girls,
            ],
            [
                'patrol_name' => 'Melati',
                'status' => RegistrationStatusEnum::Submitted,
            ]
        );

        // Peserta Regu Putri (8 Orang)
        $girlsMembers = [
            ['name' => 'Aisyah Harahap', 'role' => PatrolRoleEnum::PatrolLeader],
            ['name' => 'Nurul Hasibuan', 'role' => PatrolRoleEnum::AssistantPatrolLeader],
            ['name' => 'Fatimah Siregar', 'role' => PatrolRoleEnum::Member],
            ['name' => 'Putri Batubara', 'role' => PatrolRoleEnum::Member],
            ['name' => 'Zahra Daulay', 'role' => PatrolRoleEnum::Member],
            ['name' => 'Siti Nasution', 'role' => PatrolRoleEnum::Member],
            ['name' => 'Rania Pulungan', 'role' => PatrolRoleEnum::Member],
            ['name' => 'Dinda Tanjung', 'role' => PatrolRoleEnum::Member],
        ];

        foreach ($girlsMembers as $idx => $m) {
            $participant = Participant::firstOrCreate(
                [
                    'patrol_id' => $girlsPatrol->id,
                    'full_name' => $m['name'],
                ],
                [
                    'scout_id' => '02.21.02.'.str_pad($idx + 1, 3, '0', STR_PAD_LEFT),
                    'role_in_patrol' => $m['role'],
                    'gender' => 'female',
                    'birth_date' => '2011-08-20',
                    'birth_place' => 'Padangsidimpuan',
                    'religion' => 'Islam',
                    'blood_type' => 'A',
                    'home_address' => 'Jl. Sudirman No. 12, Losung',
                    'city' => 'Padangsidimpuan',
                    'postal_code' => '22721',
                    'home_phone' => '0634-22110',
                    'phone' => '0812998877'.str_pad($idx + 1, 2, '0', STR_PAD_LEFT),
                    'parent_name' => 'Syamsul Harahap',
                    'parent_job' => 'Wiraswasta',
                    'parent_address' => 'Jl. Sudirman No. 12, Losung',
                    'parent_city' => 'Padangsidimpuan',
                    'parent_postal_code' => '22721',
                    'parent_home_phone' => '0634-22110',
                    'parent_phone' => '081233445566',
                    'formal_education' => [
                        ['level' => 'SD', 'school_name' => 'SD Negeri 200102 Padangsidimpuan', 'city' => 'Padangsidimpuan', 'graduation_year' => '2023', 'notes' => 'Lulus'],
                        ['level' => 'SMP', 'school_name' => 'SMP Negeri 1 Padangsidimpuan', 'city' => 'Padangsidimpuan', 'graduation_year' => '2026', 'notes' => 'Kelas VIII'],
                    ],
                    'scout_siaga' => [
                        'year' => '2019 - 2023',
                        'group_number' => '01-094',
                        'pangkalan' => 'SD Negeri 200102',
                        'levels' => ['mula' => '2020', 'bantu' => '2021', 'tata' => '2022', 'garuda' => ''],
                        'activities' => ['Pesta Siaga Cabang', '', '', '', '', ''],
                        'tkk' => ['Menabung', 'Sholat', 'Juru Masak', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''],
                    ],
                    'scout_penggalang' => [
                        'year' => '2023 - Sekarang',
                        'group_number' => '01-094',
                        'pangkalan' => 'SMP Negeri 1 Padangsidimpuan',
                        'levels' => ['ramu' => '2024', 'rakit' => '2025', 'terap' => '2026', 'garuda' => ''],
                        'activities' => ['LT-I Gudep 2024', 'LT-II Kwarran Padangsidimpuan Utara 2025', 'Jambore Ranting', '', '', '', ''],
                        'tkk_purwa' => ['P3K', 'Pengatur Ruangan', 'Menjahit', '', '', '', '', '', '', ''],
                        'tkk_madya' => ['Sholat', 'Juru Masak', 'Berkemah', '', '', '', '', '', '', ''],
                        'tkk_utama' => ['P3K', '', '', '', '', '', '', '', '', ''],
                    ],
                    'status' => RegistrationStatusEnum::Submitted,
                ]
            );

            $participant->update([
                'religion' => 'Islam',
                'home_address' => 'Jl. Sudirman No. 12, Losung',
                'city' => 'Padangsidimpuan',
                'postal_code' => '22721',
                'home_phone' => '0634-22110',
                'phone' => '0812998877'.str_pad($idx + 1, 2, '0', STR_PAD_LEFT),
                'parent_name' => 'Syamsul Harahap',
                'parent_job' => 'Wiraswasta',
                'parent_address' => 'Jl. Sudirman No. 12, Losung',
                'parent_city' => 'Padangsidimpuan',
                'parent_postal_code' => '22721',
                'parent_home_phone' => '0634-22110',
                'parent_phone' => '081233445566',
                'formal_education' => [
                    ['level' => 'SD', 'school_name' => 'SD Negeri 200102 Padangsidimpuan', 'city' => 'Padangsidimpuan', 'graduation_year' => '2023', 'notes' => 'Lulus'],
                    ['level' => 'SMP', 'school_name' => 'SMP Negeri 1 Padangsidimpuan', 'city' => 'Padangsidimpuan', 'graduation_year' => '2026', 'notes' => 'Kelas VIII'],
                ],
                'scout_siaga' => [
                    'year' => '2019 - 2023',
                    'group_number' => '01-094',
                    'pangkalan' => 'SD Negeri 200102',
                    'levels' => ['mula' => '2020', 'bantu' => '2021', 'tata' => '2022', 'garuda' => ''],
                    'activities' => ['Pesta Siaga Cabang', '', '', '', '', ''],
                    'tkk' => ['Menabung', 'Sholat', 'Juru Masak', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''],
                ],
                'scout_penggalang' => [
                    'year' => '2023 - Sekarang',
                    'group_number' => '01-094',
                    'pangkalan' => 'SMP Negeri 1 Padangsidimpuan',
                    'levels' => ['ramu' => '2024', 'rakit' => '2025', 'terap' => '2026', 'garuda' => ''],
                    'activities' => ['LT-I Gudep 2024', 'LT-II Kwarran Padangsidimpuan Utara 2025', 'Jambore Ranting', '', '', '', ''],
                    'tkk_purwa' => ['P3K', 'Pengatur Ruangan', 'Menjahit', '', '', '', '', '', '', ''],
                    'tkk_madya' => ['Sholat', 'Juru Masak', 'Berkemah', '', '', '', '', '', '', ''],
                    'tkk_utama' => ['P3K', '', '', '', '', '', '', '', '', ''],
                ],
            ]);
        }

        // 4. Bindamping (Pembina Pendamping)
        Chaperone::firstOrCreate(
            [
                'scout_group_id' => $scoutGroup->id,
                'full_name' => 'Rahmat Hidayat, S.Pd',
            ],
            [
                'scout_id' => '02.21.10.001',
                'national_identity_number' => '1277011505850001',
                'gender' => 'male',
                'birth_place' => 'Padangsidimpuan',
                'birth_date' => '1985-05-15',
                'religion' => 'Islam',
                'blood_type' => 'O',
                'home_address' => 'Jl. Teuku Umar No. 12, Kel. Losung',
                'city' => 'Kota Padangsidimpuan',
                'postal_code' => '22727',
                'home_phone' => '0634-21001',
                'phone_number' => '081377889900',
                'formal_education' => [
                    ['level' => 'SD', 'school_name' => 'SD Negeri 1 Padangsidimpuan', 'city' => 'Padangsidimpuan', 'graduation_year' => '1997', 'notes' => 'Lulus'],
                    ['level' => 'SMP', 'school_name' => 'SMP Negeri 1 Padangsidimpuan', 'city' => 'Padangsidimpuan', 'graduation_year' => '2000', 'notes' => 'Lulus'],
                    ['level' => 'SMA', 'school_name' => 'SMA Negeri 1 Padangsidimpuan', 'city' => 'Padangsidimpuan', 'graduation_year' => '2003', 'notes' => 'IPA'],
                    ['level' => 'S1', 'school_name' => 'Universitas Negeri Medan', 'city' => 'Medan', 'graduation_year' => '2008', 'notes' => 'Pendidikan Bahasa'],
                    ['level' => 'S2', 'school_name' => '', 'city' => '', 'graduation_year' => '', 'notes' => ''],
                ],
                'scout_experiences' => [
                    ['level' => 'Siaga', 'year' => '1995', 'group_number' => '01-001', 'base' => 'SDN 1 Padangsidimpuan'],
                    ['level' => 'Penggalang', 'year' => '1998', 'group_number' => '01-001', 'base' => 'SMPN 1 Padangsidimpuan'],
                    ['level' => 'Penegak', 'year' => '2001', 'group_number' => '01-001', 'base' => 'SMAN 1 Padangsidimpuan'],
                    ['level' => 'Pandega', 'year' => '2005', 'group_number' => '01-055', 'base' => 'Racana Unimed'],
                    ['level' => 'Pembina', 'year' => '2015', 'group_number' => '01-001', 'base' => 'SMPN 1 Padangsidimpuan'],
                ],
                'scout_trainings' => [
                    ['course' => 'KMD', 'year' => '2016', 'location' => 'Kwarcab Padangsidimpuan', 'notes' => 'Lulus Berkualifikasi'],
                    ['course' => 'KML', 'year' => '2019', 'location' => 'Kwarda Sumatera Utara', 'notes' => 'Lulus Mahir Penggalang'],
                    ['course' => 'KPD', 'year' => '', 'location' => '', 'notes' => ''],
                    ['course' => 'KPL', 'year' => '', 'location' => '', 'notes' => ''],
                ],
                'other_trainings' => [
                    ['name' => 'Pelatihan Pertolongan Pertama Gawat Darurat (PPGD)', 'year' => '2021', 'location' => 'PMI Padangsidimpuan', 'notes' => 'Sertifikat Kompetensi'],
                ],
                'status' => RegistrationStatusEnum::Submitted,
            ]
        );

        Chaperone::firstOrCreate(
            [
                'scout_group_id' => $scoutGroup->id,
                'full_name' => 'Sri Wahyuni, S.Pd',
            ],
            [
                'scout_id' => '02.21.10.002',
                'national_identity_number' => '1277015508870002',
                'gender' => 'female',
                'birth_place' => 'Padangsidimpuan',
                'birth_date' => '1987-08-20',
                'religion' => 'Islam',
                'blood_type' => 'A',
                'home_address' => 'Jl. Kenanga No. 8, Padangsidimpuan',
                'city' => 'Kota Padangsidimpuan',
                'postal_code' => '22727',
                'home_phone' => '',
                'phone_number' => '081377889901',
                'formal_education' => [
                    ['level' => 'SD', 'school_name' => 'SD Negeri 3 Padangsidimpuan', 'city' => 'Padangsidimpuan', 'graduation_year' => '1999', 'notes' => 'Lulus'],
                    ['level' => 'SMP', 'school_name' => 'SMP Negeri 1 Padangsidimpuan', 'city' => 'Padangsidimpuan', 'graduation_year' => '2002', 'notes' => 'Lulus'],
                    ['level' => 'SMA', 'school_name' => 'SMA Negeri 2 Padangsidimpuan', 'city' => 'Padangsidimpuan', 'graduation_year' => '2005', 'notes' => 'IPS'],
                    ['level' => 'S1', 'school_name' => 'Universitas Graha Nusantara', 'city' => 'Padangsidimpuan', 'graduation_year' => '2010', 'notes' => 'FKIP'],
                    ['level' => 'S2', 'school_name' => '', 'city' => '', 'graduation_year' => '', 'notes' => ''],
                ],
                'scout_experiences' => [
                    ['level' => 'Siaga', 'year' => '1997', 'group_number' => '01-002', 'base' => 'SDN 3 Padangsidimpuan'],
                    ['level' => 'Penggalang', 'year' => '2000', 'group_number' => '01-002', 'base' => 'SMPN 1 Padangsidimpuan'],
                    ['level' => 'Penegak', 'year' => '2003', 'group_number' => '01-002', 'base' => 'SMAN 2 Padangsidimpuan'],
                    ['level' => 'Pandega', 'year' => '', 'group_number' => '', 'base' => ''],
                    ['level' => 'Pembina', 'year' => '2018', 'group_number' => '01-002', 'base' => 'SMPN 1 Padangsidimpuan'],
                ],
                'scout_trainings' => [
                    ['course' => 'KMD', 'year' => '2018', 'location' => 'Kwarcab Padangsidimpuan', 'notes' => 'Lulus Mahir Dasar'],
                    ['course' => 'KML', 'year' => '', 'location' => '', 'notes' => ''],
                    ['course' => 'KPD', 'year' => '', 'location' => '', 'notes' => ''],
                    ['course' => 'KPL', 'year' => '', 'location' => '', 'notes' => ''],
                ],
                'status' => RegistrationStatusEnum::Submitted,
            ]
        );

        // 5. Akun contoh untuk pengujian & dev (bindamping putra, bindamping putri, dan peserta)
        $defaultPassword = Hash::make('password123');

        $maleChaperone = Chaperone::where('scout_group_id', $scoutGroup->id)->where('gender', 'male')->first();
        if ($maleChaperone) {
            $userPutra = User::firstOrCreate(
                ['username' => 'bindamping'],
                [
                    'name' => $maleChaperone->full_name,
                    'password' => $defaultPassword,
                    'phone_number' => $maleChaperone->phone_number,
                    'is_active' => true,
                ]
            );
            $userPutra->assignRole(RoleEnum::Chaperone);
            $maleChaperone->update(['user_id' => $userPutra->id]);
        }

        $femaleChaperone = Chaperone::where('scout_group_id', $scoutGroup->id)->where('gender', 'female')->first();
        if ($femaleChaperone) {
            $userPutri = User::firstOrCreate(
                ['username' => 'bindamping.putri'],
                [
                    'name' => $femaleChaperone->full_name,
                    'password' => $defaultPassword,
                    'phone_number' => $femaleChaperone->phone_number,
                    'is_active' => true,
                ]
            );
            $userPutri->assignRole(RoleEnum::Chaperone);
            $femaleChaperone->update(['user_id' => $userPutri->id]);
        }

        $firstParticipant = Participant::whereHas('patrol', fn ($q) => $q->where('scout_group_id', $scoutGroup->id))->first();
        if ($firstParticipant) {
            $userPeserta = User::firstOrCreate(
                ['username' => 'peserta'],
                [
                    'name' => $firstParticipant->full_name,
                    'password' => $defaultPassword,
                    'phone_number' => $firstParticipant->phone ?: '081234567895',
                    'participant_id' => $firstParticipant->id,
                    'is_active' => true,
                ]
            );
            $userPeserta->assignRole(RoleEnum::Participant);
        }
    }
}

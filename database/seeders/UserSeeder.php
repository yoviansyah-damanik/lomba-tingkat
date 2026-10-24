<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder akun pengguna representatif untuk setiap peran
 */
class UserSeeder extends Seeder
{
    /**
     * Menjalankan pengisian data pengguna
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('password123');

        // 1-2. Akun Superadmin & Administrator (Panitia yang ditunjuk; juga dipakai pada production)
        $this->call(AdministratorSeeder::class);

        // 3. Akun Panitia murni (Monitoring Operasional Lapangan); dapat ditunjuk Superadmin menjadi Administrator
        $committee = User::firstOrCreate(
            ['username' => 'panitia'],
            [
                'id_number' => 'A-100001',
                'name' => 'Panitia Lapangan',
                'password' => $defaultPassword,
                'phone_number' => '081234567891',
                'is_active' => true,
            ]
        );
        $committee->assignRole(RoleEnum::Committee);

        // 4. Akun Juri Penilai
        $judge = User::firstOrCreate(
            ['username' => 'juri'],
            [
                'id_number' => 'A-100002',
                'name' => 'Dewan Juri LT III',
                'password' => $defaultPassword,
                'phone_number' => '081234567892',
                'is_active' => true,
            ]
        );
        $judge->assignRole(RoleEnum::Judge);

        // 5. Akun Panitia Merangkap Juri (Dual Role Operasional Sah)
        $dualRole = User::firstOrCreate(
            ['username' => 'panitia.juri'],
            [
                'id_number' => 'A-100003',
                'name' => 'Panitia Merangkap Juri',
                'password' => $defaultPassword,
                'phone_number' => '081234567893',
                'is_active' => true,
            ]
        );
        $dualRole->assignRole(RoleEnum::Committee);
        $dualRole->assignRole(RoleEnum::Judge);

        // 6. Akun Penanggung Jawab Gugus Depan (Sekolah / Pangkalan): username dan ID persis sama dengan ID Gudep
        $scoutGroup = User::firstOrCreate(
            ['username' => ContingentSeeder::GUDEP_ID],
            [
                'id_number' => ContingentSeeder::GUDEP_ID,
                'name' => 'Gudep SMP Negeri 1 Padangsidimpuan',
                'password' => $defaultPassword,
                'phone_number' => '081234567894',
                'is_active' => true,
            ]
        );
        $scoutGroup->assignRole(RoleEnum::ScoutGroup);

        // Akun Peserta dan Bindamping tidak di-seed: dibuat otomatis (username ID, kata sandi tanggal lahir)
        // saat Administrator memverifikasi gugus depan.
    }
}

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

        // 1. Akun Super Administrator
        $admin = User::firstOrCreate(
            ['email' => 'admin@lt3padangsidimpuan.id'],
            [
                'name' => 'Administrator LT III',
                'password' => $defaultPassword,
                'phone_number' => '081234567890',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole(RoleEnum::Admin);

        // 2. Akun Panitia (Monitoring Operasional Lapangan)
        $committee = User::firstOrCreate(
            ['email' => 'panitia@lt3padangsidimpuan.id'],
            [
                'name' => 'Panitia Lapangan',
                'password' => $defaultPassword,
                'phone_number' => '081234567891',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $committee->assignRole(RoleEnum::Committee);

        // 3. Akun Juri Penilai
        $judge = User::firstOrCreate(
            ['email' => 'juri@lt3padangsidimpuan.id'],
            [
                'name' => 'Dewan Juri LT III',
                'password' => $defaultPassword,
                'phone_number' => '081234567892',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $judge->assignRole(RoleEnum::Judge);

        // 4. Akun Panitia Merangkap Juri (Dual Role Operasional Sah)
        $dualRole = User::firstOrCreate(
            ['email' => 'panitia.juri@lt3padangsidimpuan.id'],
            [
                'name' => 'Panitia Merangkap Juri',
                'password' => $defaultPassword,
                'phone_number' => '081234567893',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $dualRole->assignRole(RoleEnum::Committee);
        $dualRole->assignRole(RoleEnum::Judge);

        // 5. Akun Penanggung Jawab Gugus Depan (Sekolah / Pangkalan)
        $scoutGroup = User::firstOrCreate(
            ['email' => 'gudep01@lt3padangsidimpuan.id'],
            [
                'name' => 'Gudep SMP Negeri 1 Padangsidimpuan',
                'password' => $defaultPassword,
                'phone_number' => '081234567894',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $scoutGroup->assignRole(RoleEnum::ScoutGroup);

        // 6. Akun Peserta / Pimpinan Regu
        $participant = User::firstOrCreate(
            ['email' => 'peserta@lt3padangsidimpuan.id'],
            [
                'name' => 'Pinru Regu Rajawali',
                'password' => $defaultPassword,
                'phone_number' => '081234567895',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $participant->assignRole(RoleEnum::Participant);
    }
}

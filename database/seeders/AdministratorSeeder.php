<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder akun Superadmin dan Administrator (satu-satunya data akun pada lingkungan production)
 *
 * Administrator adalah Panitia yang ditunjuk: akun memegang peran Panitia ditambah Administrator.
 * Kata sandi awal "password123"; akun yang sudah ada tidak diubah, hanya perannya dilengkapi.
 */
class AdministratorSeeder extends Seeder
{
    private const DEFAULT_PASSWORD = 'password123';

    public function run(): void
    {
        $this->seedAccount('superadmin', 'Superadmin LT III', '081234567899', [RoleEnum::Superadmin], 'A-000001');
        $this->seedAccount('admin', 'Administrator LT III', '081234567890', [RoleEnum::Committee, RoleEnum::Admin], 'A-000002');
    }

    /**
     * @param  array<int, RoleEnum>  $roles
     */
    private function seedAccount(string $username, string $name, string $phone, array $roles, string $idNumber): void
    {
        $user = User::firstOrCreate(
            ['username' => $username],
            [
                'id_number' => $idNumber,
                'name' => $name,
                'password' => Hash::make(self::DEFAULT_PASSWORD),
                'phone_number' => $phone,
                'is_active' => true,
            ]
        );

        foreach ($roles as $role) {
            $user->assignRole($role);
        }
    }
}

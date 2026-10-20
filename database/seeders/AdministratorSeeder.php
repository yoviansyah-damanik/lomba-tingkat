<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder akun Superadmin dan Administrator (satu-satunya data akun pada lingkungan production)
 *
 * Kata sandi awal "password123"; akun yang sudah ada tidak diubah.
 */
class AdministratorSeeder extends Seeder
{
    private const DEFAULT_PASSWORD = 'password123';

    public function run(): void
    {
        $this->seedAccount('superadmin', 'Superadmin LT III', '081234567899', RoleEnum::Superadmin);
        $this->seedAccount('admin', 'Administrator LT III', '081234567890', RoleEnum::Admin);
    }

    private function seedAccount(string $username, string $name, string $phone, RoleEnum $role): void
    {
        $user = User::firstOrCreate(
            ['username' => $username],
            [
                'name' => $name,
                'password' => Hash::make(self::DEFAULT_PASSWORD),
                'phone_number' => $phone,
                'is_active' => true,
            ]
        );

        $user->assignRole($role);
    }
}

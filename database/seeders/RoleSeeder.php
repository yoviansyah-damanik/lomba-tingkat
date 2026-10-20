<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Seeder untuk menginisialisasi seluruh peran (Role) sistem
 */
class RoleSeeder extends Seeder
{
    /**
     * Menjalankan pengisian data peran
     */
    public function run(): void
    {
        foreach (RoleEnum::cases() as $roleEnum) {
            Role::firstOrCreate(
                ['name' => $roleEnum->value],
                [
                    'label' => $roleEnum->label(),
                    'description' => $roleEnum->description(),
                ]
            );
        }
    }
}

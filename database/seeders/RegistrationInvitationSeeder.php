<?php

namespace Database\Seeders;

use App\Models\RegistrationInvitation;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder data resmi pangkalan Gugus Depan (23 sekolah) ke Kode Undangan Registrasi.
 * Dijalankan pada seluruh lingkungan termasuk production agar seluruh sekolah di Kota Padangsidimpuan
 * memiliki kode undangan resmi sekali pakai yang siap dicetak dan didistribusikan.
 */
class RegistrationInvitationSeeder extends Seeder
{
    public function run(): void
    {
        $schoolsFile = __DIR__.'/data/schools.php';

        if (! file_exists($schoolsFile)) {
            return;
        }

        /** @var array<int, array{school: string, male: string, female: string, kwarran: string, address: string}> $schools */
        $schools = require $schoolsFile;

        // Administrator penanggung jawab pencatatan undangan
        $admin = User::where('username', 'admin')->first()
            ?? User::where('username', 'superadmin')->first();

        foreach ($schools as $school) {
            $combinedNumber = "{$school['male']} & {$school['female']}";

            RegistrationInvitation::firstOrCreate(
                ['group_number' => $combinedNumber],
                [
                    'school_name' => $school['school'],
                    'kwarran_name' => $school['kwarran'],
                    'is_active' => true,
                    'created_by' => $admin?->id,
                ]
            );
        }
    }
}


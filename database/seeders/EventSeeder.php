<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;

/**
 * Seeder untuk data resmi kegiatan LT III Kwarcab Padangsidimpuan
 */
class EventSeeder extends Seeder
{
    public function run(): void
    {
        Event::firstOrCreate(
            ['title' => 'Lomba Tingkat III Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan'],
            [
                'theme' => 'Tangkas, Berkarakter, Bersaudara, dan Berprestasi Menuju Generasi Indonesia Emas',
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date' => now()->addDays(8)->toDateString(),
                'registration_start_at' => now()->subDays(5),
                'registration_end_at' => now()->addDays(20)->setTime(23, 59, 59),
                'is_registration_open' => true,
                'location' => 'Bumi Perkemahan Kwarcab Kota Padangsidimpuan, Sumatera Utara',
                'description' => 'Lomba Tingkat Regu Pramuka Penggalang Tiga (LT III) Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan merupakan ajang kompetisi prestasi regu penggalang tingkat pangkalan SMP/MTs se-Kota Padangsidimpuan untuk menentukan regu berprestasi tinggi yang berhak mewakili Kwarcab Padangsidimpuan ke ajang Lomba Tingkat IV (LT IV) Kwarda Sumatera Utara.',
                'contact_email' => 'humas@kwarcab-padangsidimpuan.or.id',
                'contact_phone' => '+62 821-6543-2100',
                'is_active' => true,
            ]
        );
    }
}

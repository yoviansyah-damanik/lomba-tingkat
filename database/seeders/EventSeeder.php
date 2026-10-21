<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeder data resmi kegiatan LT-III Tahun 2026 (bersumber dari Petunjuk Teknis)
 */
class EventSeeder extends Seeder
{
    public function run(): void
    {
        $data = (require __DIR__.'/data/lt3_2026.php')['event'];

        // Batas registrasi tidak boleh sudah lewat saat seeding (lingkungan pengembangan / pengujian)
        $registrationEnd = Carbon::parse($data['registration_end_at']);
        if ($registrationEnd->isPast()) {
            $registrationEnd = now()->addDays(14)->setTime(23, 59, 59);
        }

        // Satu kegiatan aktif: perbarui bila sudah ada agar seeding ulang tidak menggandakan data
        $event = Event::first() ?? new Event;
        $event->fill([
            'title' => $data['title'],
            'theme' => $data['theme'],
            'description' => $data['description'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'registration_start_at' => $data['registration_start_at'],
            'registration_end_at' => $registrationEnd,
            'is_registration_open' => true,
            'location' => $data['location'],
            'contact_email' => $data['contact_email'],
            'contact_phone' => $data['contact_phone'],
            'is_active' => true,
        ])->save();
    }
}

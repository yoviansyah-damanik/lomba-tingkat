<?php

namespace Database\Seeders;

use App\Models\Event;
use Database\Seeders\Support\LiveContest;
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

        // Local/dev: pendaftaran ditutup hari ini agar NPP dapat dibuat dan penjurian langsung dicoba
        $registrationEnd = LiveContest::registrationEnd($registrationEnd);

        // Satu kegiatan aktif: perbarui bila sudah ada agar seeding ulang tidak menggandakan data
        $event = Event::first() ?? new Event;
        $event->fill([
            'title' => $data['title'],
            'theme' => $data['theme'],
            'description' => $data['description'],
            'start_date' => LiveContest::shift($data['start_date'])->toDateString(),
            'end_date' => LiveContest::shift($data['end_date'])->toDateString(),
            'registration_start_at' => $data['registration_start_at'],
            'registration_end_at' => $registrationEnd,
            'is_registration_open' => ! LiveContest::active(),
            'registration_fee_per_patrol' => $data['registration_fee_per_patrol'],
            'payment_bank_name' => $data['payment_bank_name'],
            'payment_account_number' => $data['payment_account_number'],
            'payment_account_holder' => $data['payment_account_holder'],
            'payment_instructions' => $data['payment_instructions'],
            'location' => $data['location'],
            'contact_email' => $data['contact_email'],
            'contact_phone' => $data['contact_phone'],
            'is_active' => true,
        ])->save();
    }
}

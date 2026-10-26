<?php

namespace Database\Seeders;

use App\Models\Competition;
use App\Models\CompetitionSchedule;
use Database\Seeders\Support\LiveContest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeder jadwal sesi lomba LT-III Tahun 2026: setiap mata lomba dijadwalkan terpisah untuk regu putra dan putri.
 * Waktu yang tidak ditetapkan Juknis berupa usulan dan dapat disesuaikan panitia.
 */
class CompetitionScheduleSeeder extends Seeder
{
    public function run(): void
    {
        foreach ((require __DIR__.'/data/lt3_2026.php')['competitions'] as $item) {
            $competition = Competition::where('code', $item['code'])->first();

            if (! $competition) {
                continue;
            }

            $slot = $item['schedule'];
            $start = Carbon::parse("{$slot['day']} {$slot['start']}", 'Asia/Jakarta');
            $end = Carbon::parse("{$slot['day']} {$slot['end']}", 'Asia/Jakarta');
            [$start, $end] = LiveContest::window($start, $end);

            foreach (['boys' => 'Penggalang Putra', 'girls' => 'Penggalang Putri'] as $gender => $label) {
                CompetitionSchedule::updateOrCreate(
                    [
                        'competition_id' => $competition->id,
                        'session_name' => "{$item['title']} — {$label}",
                    ],
                    [
                        'participant_type' => $competition->participant_type,
                        'gender_category' => $gender,
                        'arena_location' => $slot['venue'],
                        'start_time' => $start,
                        'end_time' => $end,
                        'status' => 'scheduled',
                        'is_published' => true,
                    ]
                );
            }
        }
    }
}

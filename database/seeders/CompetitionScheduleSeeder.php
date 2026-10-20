<?php

namespace Database\Seeders;

use App\Models\Competition;
use App\Models\CompetitionSchedule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeder untuk jadwal kegiatan dan sesi lomba LT III
 */
class CompetitionScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $baseDate = Carbon::today()->addDays(5)->setTimezone('Asia/Jakarta');

        $schedules = [
            [
                'competition_code' => 'PBB-01',
                'session_name' => 'Sesi 1 — Penggalang Putra',
                'arena_location' => 'Lapangan Utama Bumi Perkemahan',
                'start_time' => $baseDate->copy()->setTime(8, 0),
                'end_time' => $baseDate->copy()->setTime(11, 30),
                'status' => 'scheduled',
            ],
            [
                'competition_code' => 'PBB-01',
                'session_name' => 'Sesi 2 — Penggalang Putri',
                'arena_location' => 'Lapangan Utama Bumi Perkemahan',
                'start_time' => $baseDate->copy()->setTime(14, 0),
                'end_time' => $baseDate->copy()->setTime(17, 30),
                'status' => 'scheduled',
            ],
            [
                'competition_code' => 'PION-02',
                'session_name' => 'Sesi Serentak — Putra & Putri',
                'arena_location' => 'Zona Ketangkasan Lapangan Barat',
                'start_time' => $baseDate->copy()->addDay()->setTime(8, 30),
                'end_time' => $baseDate->copy()->addDay()->setTime(11, 0),
                'status' => 'scheduled',
            ],
            [
                'competition_code' => 'NAV-04',
                'session_name' => 'Pemberangkatan Penjelajahan',
                'arena_location' => 'Gerbang Start & Pos Rute Alam Bebas',
                'start_time' => $baseDate->copy()->addDays(2)->setTime(7, 30),
                'end_time' => $baseDate->copy()->addDays(2)->setTime(13, 0),
                'status' => 'scheduled',
            ],
            [
                'competition_code' => 'SENI-06',
                'session_name' => 'Malam Pentas Kreasi Seni Budaya',
                'participant_type' => 'patrol',
                'arena_location' => 'Panggung Terbuka Api Unggun',
                'start_time' => $baseDate->copy()->addDays(2)->setTime(20, 0),
                'end_time' => $baseDate->copy()->addDays(2)->setTime(23, 0),
                'status' => 'scheduled',
            ],
            [
                'competition_code' => 'PID-09',
                'session_name' => 'Sesi Pidato Kebangsaan & Bela Negara',
                'participant_type' => 'individual',
                'arena_location' => 'Aula Utama Kwarcab Padangsidimpuan',
                'start_time' => $baseDate->copy()->addDays(1)->setTime(13, 30),
                'end_time' => $baseDate->copy()->addDays(1)->setTime(16, 0),
                'status' => 'scheduled',
            ],
        ];

        foreach ($schedules as $item) {
            $competition = Competition::where('code', $item['competition_code'])->first();
            if ($competition) {
                CompetitionSchedule::updateOrCreate(
                    [
                        'competition_id' => $competition->id,
                        'session_name' => $item['session_name'],
                    ],
                    [
                        'participant_type' => $item['participant_type'] ?? $competition->participant_type,
                        'arena_location' => $item['arena_location'],
                        'start_time' => $item['start_time'],
                        'end_time' => $item['end_time'],
                        'checkin_open_time' => $item['start_time']->copy()->subMinutes(30),
                        'checkin_close_time' => $item['end_time'],
                        'status' => $item['status'],
                        'is_published' => true,
                    ]
                );
            }
        }
    }
}

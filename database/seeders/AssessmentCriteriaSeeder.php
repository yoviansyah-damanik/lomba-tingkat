<?php

namespace Database\Seeders;

use App\Models\AssessmentCriterion;
use App\Models\AttendanceSession;
use App\Models\Competition;
use App\Models\CompetitionSchedule;
use App\Models\JudgeAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder kriteria rubrik penilaian lomba, penugasan juri awal, dan sesi presensi pos
 */
class AssessmentCriteriaSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Data Rubrik Penilaian untuk cabang lomba utama
        $criteriaMap = [
            'PBB-01' => [
                ['name' => 'Kerapian & Keseragaman Atribut Seragam Pramuka', 'code' => 'PBB-KRP', 'weight_percentage' => 20, 'order_index' => 1],
                ['name' => 'Ketepatan Aba-aba & Gerakan Dasar PBB', 'code' => 'PBB-DSR', 'weight_percentage' => 30, 'order_index' => 2],
                ['name' => 'Kreativitas & Sinkronisasi Formasi Koloni Tongkat', 'code' => 'PBB-KLN', 'weight_percentage' => 30, 'order_index' => 3],
                ['name' => 'Disiplin, Ketegasan & Kekompakan Pasukan', 'code' => 'PBB-DSP', 'weight_percentage' => 20, 'order_index' => 4],
            ],
            'PION-02' => [
                ['name' => 'Kekuatan & Kekokohan Konstruksi Menara', 'code' => 'PION-KKT', 'weight_percentage' => 35, 'order_index' => 1],
                ['name' => 'Ketepatan Simpul & Ikatan Palang/Silang/Kaki Tiga', 'code' => 'PION-SMP', 'weight_percentage' => 30, 'order_index' => 2],
                ['name' => 'Estetika, Kerapian Pemotongan & Fungsi Praktis', 'code' => 'PION-EST', 'weight_percentage' => 20, 'order_index' => 3],
                ['name' => 'Ketepatan Waktu Perakitan (Sesuai Limit Waktu)', 'code' => 'PION-WKT', 'weight_percentage' => 15, 'order_index' => 4],
            ],
            'SEM-03' => [
                ['name' => 'Kecepatan & Ketepatan Penerimaan Semaphore', 'code' => 'SEM-SMP', 'weight_percentage' => 35, 'order_index' => 1],
                ['name' => 'Ketepatan Penerimaan Isyarat Peluit Morse', 'code' => 'SEM-MRS', 'weight_percentage' => 35, 'order_index' => 2],
                ['name' => 'Akurasi Pemecahan Pesan Sandi Rahasia', 'code' => 'SEM-SND', 'weight_percentage' => 30, 'order_index' => 3],
            ],
            'NAV-04' => [
                ['name' => 'Ketepatan Sudut Bidik Kompas Prisma (Azimut / Back-Azimut)', 'code' => 'NAV-AZM', 'weight_percentage' => 30, 'order_index' => 1],
                ['name' => 'Akurasi Skala, Kerapian & Legenda Peta Pita', 'code' => 'NAV-PTA', 'weight_percentage' => 40, 'order_index' => 2],
                ['name' => 'Ketepatan Waktu & Kelengkapan Laporan di Pos Kontrol', 'code' => 'NAV-POS', 'weight_percentage' => 30, 'order_index' => 3],
            ],
            'PPGD-05' => [
                ['name' => 'Ketepatan Diagnosa & Penanganan Cepat Tanggap Cedera', 'code' => 'PPGD-DGS', 'weight_percentage' => 30, 'order_index' => 1],
                ['name' => 'Keahlian Pembidaian, Pembalutan Mitela & RJP', 'code' => 'PPGD-BDN', 'weight_percentage' => 30, 'order_index' => 2],
                ['name' => 'Kecepatan, Kestabilan & Keamanan Evakuasi Tandu', 'code' => 'PPGD-TND', 'weight_percentage' => 25, 'order_index' => 3],
                ['name' => 'Ketertiban & Kerjasama Tim Penolong', 'code' => 'PPGD-KRP', 'weight_percentage' => 15, 'order_index' => 4],
            ],
            'SENI-06' => [
                ['name' => 'Harmonisasi Gerak & Musikalitas Etnik Tapanuli Bagian Selatan', 'code' => 'SNI-HRM', 'weight_percentage' => 35, 'order_index' => 1],
                ['name' => 'Penghayatan Budaya & Kreativitas Koreografi', 'code' => 'SNI-KRE', 'weight_percentage' => 35, 'order_index' => 2],
                ['name' => 'Keserasian Busana Tradisional & Properti Panggung', 'code' => 'SNI-BSN', 'weight_percentage' => 30, 'order_index' => 3],
            ],
            'PID-09' => [
                ['name' => 'Kedalaman Materi Kepramukaan & Semangat Kebangsaan', 'code' => 'PID-MTR', 'weight_percentage' => 35, 'order_index' => 1],
                ['name' => 'Intonasi, Artikulasi, Vokal & Gesture Tubuh', 'code' => 'PID-INT', 'weight_percentage' => 35, 'order_index' => 2],
                ['name' => 'Penguasaan Panggung & Ketepatan Durasi Pidato', 'code' => 'PID-WKT', 'weight_percentage' => 30, 'order_index' => 3],
            ],
        ];

        foreach ($criteriaMap as $competitionCode => $criteriaList) {
            $competition = Competition::where('code', $competitionCode)->first();
            if (! $competition) {
                continue;
            }

            foreach ($criteriaList as $item) {
                AssessmentCriterion::updateOrCreate(
                    [
                        'competition_id' => $competition->id,
                        'name' => $item['name'],
                    ],
                    [
                        'code' => $item['code'],
                        'description' => 'Parameter penilaian standar rubrik untuk ' . $competition->title,
                        'min_score' => 0.00,
                        'max_score' => 100.00,
                        'weight_percentage' => $item['weight_percentage'],
                        'order_index' => $item['order_index'],
                    ]
                );
            }
        }

        // 2. Data Penugasan Juri untuk jadwal lomba yang sudah ada
        $judge1 = User::where('username', 'juri')->first();
        $judge2 = User::where('username', 'panitia.juri')->first();

        $schedules = CompetitionSchedule::all();

        foreach ($schedules as $schedule) {
            // Setup sesi presensi pos default
            AttendanceSession::firstOrCreate(
                [
                    'competition_schedule_id' => $schedule->id,
                    'session_type' => 'participant',
                ],
                [
                    'title' => 'Presensi Peserta: ' . $schedule->session_name,
                    'is_competition' => true,
                    'opened_at' => $schedule->checkin_open_time ?? now(),
                    'closed_at' => $schedule->checkin_close_time ?? now()->addHours(2),
                    'is_active' => true,
                ]
            );

            // Penugasan Juri Utama
            if ($judge1) {
                JudgeAssignment::updateOrCreate(
                    [
                        'competition_schedule_id' => $schedule->id,
                        'user_id' => $judge1->id,
                    ],
                    [
                        'role' => 'lead_judge',
                        'is_confirmed' => true,
                        'notes' => 'Koordinator Dewan Juri Lapangan',
                    ]
                );
            }

            // Penugasan Juri Penilai Kedua
            if ($judge2) {
                JudgeAssignment::updateOrCreate(
                    [
                        'competition_schedule_id' => $schedule->id,
                        'user_id' => $judge2->id,
                    ],
                    [
                        'role' => 'scoring_judge',
                        'is_confirmed' => true,
                        'notes' => 'Juri Teknis Operasional',
                    ]
                );
            }
        }

        // 3. Data Sesi Presensi Kehadiran Umum (Khusus Panitia Lapangan)
        $generalSessions = [
            [
                'title' => 'Presensi Registrasi Kedatangan Kontingen',
                'session_type' => 'participant',
                'opened_at' => now()->startOfDay()->addHours(7),
                'closed_at' => now()->startOfDay()->addHours(12),
                'is_active' => true,
            ],
            [
                'title' => 'Presensi Apel Pembukaan & Upacara LT III',
                'session_type' => 'participant',
                'opened_at' => now()->startOfDay()->addHours(13),
                'closed_at' => now()->startOfDay()->addHours(16),
                'is_active' => true,
            ],
            [
                'title' => 'Presensi Kehadiran Pembina Pendamping',
                'session_type' => 'chaperone',
                'opened_at' => now()->startOfDay()->addHours(8),
                'closed_at' => now()->startOfDay()->addHours(17),
                'is_active' => true,
            ],
        ];

        foreach ($generalSessions as $genSession) {
            AttendanceSession::firstOrCreate(
                [
                    'competition_schedule_id' => null,
                    'title' => $genSession['title'],
                ],
                [
                    'session_type' => $genSession['session_type'],
                    'is_competition' => false,
                    'opened_at' => $genSession['opened_at'],
                    'closed_at' => $genSession['closed_at'],
                    'is_active' => $genSession['is_active'],
                ]
            );
        }
    }
}

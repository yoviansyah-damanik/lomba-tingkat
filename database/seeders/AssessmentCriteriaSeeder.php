<?php

namespace Database\Seeders;

use App\Models\AssessmentCriterion;
use App\Models\AttendanceSession;
use App\Models\Competition;
use App\Models\CompetitionSchedule;
use App\Models\JudgeAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeder rubrik penilaian per mata lomba (aspek penilaian Juknis), penugasan juri awal,
 * dan sesi presensi kegiatan perkemahan
 */
class AssessmentCriteriaSeeder extends Seeder
{
    public function run(): void
    {
        $data = require __DIR__.'/data/lt3_2026.php';

        $this->seedCriteria($data['competitions']);
        $this->seedJudgeAssignmentsAndPostSessions();
        $this->seedActivitySessions($data['activities']);
    }

    /**
     * Rubrik: bobot tiap aspek berjumlah 100% pada setiap mata lomba
     */
    private function seedCriteria(array $competitions): void
    {
        foreach ($competitions as $item) {
            $competition = Competition::where('code', $item['code'])->first();

            if (! $competition) {
                continue;
            }

            foreach ($item['criteria'] as $index => [$name, $weight]) {
                AssessmentCriterion::updateOrCreate(
                    ['competition_id' => $competition->id, 'name' => $name],
                    [
                        'code' => sprintf('%s-%d', $item['code'], $index + 1),
                        'description' => "Aspek penilaian {$competition->title} sesuai Petunjuk Teknis LT-III Tahun 2026",
                        'min_score' => 0.00,
                        'max_score' => 100.00,
                        'weight_percentage' => $weight,
                        'order_index' => $index + 1,
                    ]
                );
            }
        }
    }

    /**
     * Penugasan juri utama dan juri penilai pada setiap sesi lomba serta sesi presensi posnya
     */
    private function seedJudgeAssignmentsAndPostSessions(): void
    {
        $leadJudge = User::where('id_number', 'ID-JURI0001')->first();
        $secondJudge = User::where('id_number', 'ID-PANJURI1')->first();

        foreach (CompetitionSchedule::all() as $schedule) {
            AttendanceSession::firstOrCreate(
                ['competition_schedule_id' => $schedule->id, 'session_type' => 'participant'],
                [
                    'title' => 'Presensi Peserta: '.$schedule->session_name,
                    'is_competition' => true,
                    'opened_at' => $schedule->checkin_open_time ?? $schedule->start_time?->copy()->subMinutes(30),
                    'closed_at' => $schedule->checkin_close_time ?? $schedule->end_time,
                    'is_active' => true,
                ]
            );

            foreach ([[$leadJudge, 'lead_judge', 'Koordinator Dewan Juri Lapangan'], [$secondJudge, 'scoring_judge', 'Juri Teknis Operasional']] as [$judge, $role, $notes]) {
                if (! $judge) {
                    continue;
                }

                JudgeAssignment::updateOrCreate(
                    ['competition_schedule_id' => $schedule->id, 'user_id' => $judge->id],
                    ['role' => $role, 'is_confirmed' => true, 'notes' => $notes]
                );
            }
        }
    }

    /**
     * Sesi presensi kegiatan perkemahan (upacara, apel, permainan, kedatangan, dan bindamping)
     */
    private function seedActivitySessions(array $activities): void
    {
        foreach ($activities as $activity) {
            AttendanceSession::updateOrCreate(
                ['competition_schedule_id' => null, 'title' => $activity['title']],
                [
                    'session_type' => $activity['type'],
                    'is_competition' => false,
                    'opened_at' => Carbon::parse($activity['start'], 'Asia/Jakarta'),
                    'closed_at' => Carbon::parse($activity['end'], 'Asia/Jakarta'),
                    'is_active' => true,
                ]
            );
        }
    }
}

<?php

namespace App\Services;

use App\Enums\RoleEnum;
use App\Models\CompetitionSchedule;
use App\Models\JudgeAssignment;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Menentukan panel juri tiap sesi lomba secara otomatis: beban penugasan dibagi rata antarjuri
 * dan juri tidak ditugaskan pada dua sesi yang waktunya bertumpuk selama masih ada pilihan lain.
 */
class JudgeAutoAssignmentService
{
    public const DEFAULT_PANEL_SIZE = 3;

    /**
     * Lengkapi panel juri setiap sesi hingga $panelSize. Penugasan yang sudah ada tidak diubah.
     *
     * @return array{schedules: int, assignments: int, skipped: int} sesi yang dilengkapi, penugasan baru, sesi yang kekurangan juri
     */
    public function assign(int $panelSize = self::DEFAULT_PANEL_SIZE): array
    {
        $judges = User::whereHas('roles', fn ($q) => $q->where('name', RoleEnum::Judge->value))->where('is_active', true)->orderBy('id_number')->get();

        if ($judges->isEmpty()) {
            return ['schedules' => 0, 'assignments' => 0, 'skipped' => 0];
        }

        $schedules = CompetitionSchedule::with('judgeAssignments')->orderBy('start_time')->orderBy('id')->get();

        // Beban awal dan jadwal yang sudah dipegang tiap juri
        $load = $judges->mapWithKeys(fn (User $judge) => [$judge->id => 0])->all();
        $busy = $judges->mapWithKeys(fn (User $judge) => [$judge->id => collect()])->all();

        foreach ($schedules as $schedule) {
            foreach ($schedule->judgeAssignments as $assignment) {
                if (isset($load[$assignment->user_id])) {
                    $load[$assignment->user_id]++;
                    $busy[$assignment->user_id]->push($schedule);
                }
            }
        }

        $touched = 0;
        $created = 0;
        $skipped = 0;

        foreach ($schedules as $schedule) {
            $missing = $panelSize - $schedule->judgeAssignments->count();

            if ($missing <= 0) {
                continue;
            }

            $taken = $schedule->judgeAssignments->pluck('user_id')->all();
            $hasLead = $schedule->judgeAssignments->contains('role', 'lead_judge');
            $added = 0;

            for ($i = 0; $i < $missing; $i++) {
                $judge = $this->pickJudge($judges, $taken, $load, $busy, $schedule);

                if (! $judge) {
                    break;
                }

                JudgeAssignment::create([
                    'competition_schedule_id' => $schedule->id,
                    'user_id' => $judge->id,
                    'role' => $hasLead ? 'scoring_judge' : 'lead_judge',
                    'is_confirmed' => true,
                    'notes' => 'Ditentukan otomatis',
                ]);

                $hasLead = true;
                $taken[] = $judge->id;
                $load[$judge->id]++;
                $busy[$judge->id]->push($schedule);
                $added++;
            }

            $created += $added;
            $touched += $added > 0 ? 1 : 0;
            $skipped += $added < $missing ? 1 : 0;
        }

        return ['schedules' => $touched, 'assignments' => $created, 'skipped' => $skipped];
    }

    /**
     * Juri dengan beban paling sedikit; mengutamakan yang jadwalnya tidak bertumpuk dengan sesi ini
     *
     * @param  Collection<int, User>  $judges
     * @param  array<int, string|int>  $taken
     * @param  array<string|int, int>  $load
     * @param  array<string|int, Collection<int, CompetitionSchedule>>  $busy
     */
    private function pickJudge(Collection $judges, array $taken, array $load, array $busy, CompetitionSchedule $schedule): ?User
    {
        $candidates = $judges->reject(fn (User $judge) => in_array($judge->id, $taken, true));

        $free = $candidates->reject(fn (User $judge) => $busy[$judge->id]->contains(fn (CompetitionSchedule $other) => $this->overlaps($schedule, $other)));

        return ($free->isNotEmpty() ? $free : $candidates)
            ->sortBy(fn (User $judge) => $load[$judge->id])
            ->first();
    }

    private function overlaps(CompetitionSchedule $a, CompetitionSchedule $b): bool
    {
        return $a->start_time && $a->end_time && $b->start_time && $b->end_time
            && $a->start_time < $b->end_time && $b->start_time < $a->end_time;
    }
}

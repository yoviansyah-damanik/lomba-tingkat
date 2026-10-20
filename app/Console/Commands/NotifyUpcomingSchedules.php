<?php

namespace App\Console\Commands;

use App\Jobs\SendScheduleReminderPush;
use App\Models\CompetitionSchedule;
use Illuminate\Console\Command;

/**
 * Mengantrekan notifikasi push untuk sesi lomba yang dimulai dalam 30 menit
 */
class NotifyUpcomingSchedules extends Command
{
    protected $signature = 'schedules:notify-upcoming {--minutes=30 : Jendela waktu sebelum lomba dimulai}';

    protected $description = 'Antrekan notifikasi push pengingat untuk sesi lomba yang segera dimulai';

    public function handle(): int
    {
        $schedules = CompetitionSchedule::query()
            ->where('is_published', true)
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->whereNull('reminder_sent_at')
            ->whereBetween('start_time', [now(), now()->addMinutes((int) $this->option('minutes'))])
            ->get();

        foreach ($schedules as $schedule) {
            // Tandai lebih dulu agar scheduler tidak mengantrekan sesi yang sama dua kali
            $schedule->forceFill(['reminder_sent_at' => now()])->saveQuietly();
            SendScheduleReminderPush::dispatch($schedule->id);
        }

        $this->info("{$schedules->count()} pengingat diantrekan.");

        return self::SUCCESS;
    }
}

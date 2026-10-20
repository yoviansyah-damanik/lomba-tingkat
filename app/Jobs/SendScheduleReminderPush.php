<?php

namespace App\Jobs;

use App\Models\CompetitionSchedule;
use App\Services\WebPushService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Job pengiriman notifikasi push pengingat sesi lomba (H-30 menit)
 */
class SendScheduleReminderPush implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(public string $scheduleId)
    {
        $this->onQueue('notifications');
    }

    public function handle(WebPushService $webPush): void
    {
        $schedule = CompetitionSchedule::with('competition')->find($this->scheduleId);

        if (! $schedule || ! $schedule->is_published || in_array($schedule->status, ['cancelled', 'completed'], true)) {
            return;
        }

        $webPush->broadcastNotification([
            'title' => 'Lomba dimulai 30 menit lagi',
            'body' => sprintf(
                '%s (%s) di %s, pukul %s WIB. Presensi pos sudah dibuka.',
                $schedule->competition?->title ?: 'Lomba',
                $schedule->session_name,
                $schedule->arena_location,
                $schedule->start_time->copy()->setTimezone('Asia/Jakarta')->format('H:i')
            ),
            'url' => url('/schedules'),
            'tag' => 'lt3-reminder-'.$schedule->id,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\CompetitionSchedule;
use App\Services\CalendarLinkService;
use Illuminate\Http\Response;

/**
 * Unduhan berkas kalender (.ics) untuk jadwal lomba yang dipublikasikan
 */
class ScheduleCalendarController extends Controller
{
    public function __construct(private CalendarLinkService $calendar) {}

    /**
     * Satu sesi lomba
     */
    public function show(string $scheduleId): Response
    {
        $schedule = CompetitionSchedule::published()->findOrFail($scheduleId);

        return $this->download(collect([$schedule]), 'jadwal-'.$schedule->id.'.ics');
    }

    /**
     * Seluruh sesi lomba yang belum selesai
     */
    public function all(): Response
    {
        $schedules = CompetitionSchedule::with('competition')
            ->published()
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->where('end_time', '>=', now())
            ->get();

        return $this->download($schedules, 'jadwal-lomba-tingkat.ics');
    }

    private function download($schedules, string $filename): Response
    {
        return response($this->calendar->ics($schedules), 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}

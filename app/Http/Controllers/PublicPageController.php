<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Competition;
use App\Models\CompetitionSchedule;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller untuk menangani halaman publik dan informasi kegiatan LT III
 */
class PublicPageController extends Controller
{
    /**
     * Halaman depan (Landing page) utama kegiatan LT III
     */
    public function home(): View
    {
        $event = Event::where('is_active', true)->first();
        $latestAnnouncements = Announcement::published()->take(3)->get();
        $competitions = Competition::published()->take(6)->get();
        $upcomingSchedules = CompetitionSchedule::with('competition')
            ->published()
            ->take(5)
            ->get();

        $stats = [
            'total_competitions' => Competition::published()->count(),
            'total_schedules' => CompetitionSchedule::published()->count(),
            'total_announcements' => Announcement::published()->count(),
        ];

        return view('welcome', compact('event', 'latestAnnouncements', 'competitions', 'upcomingSchedules', 'stats'));
    }

    /**
     * Halaman daftar seluruh pengumuman resmi
     */
    public function announcements(Request $request): View
    {
        $query = Announcement::published();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        $announcements = $query->paginate(9)->withQueryString();

        return view('pages.announcements.index', compact('announcements'));
    }

    /**
     * Halaman detail pengumuman resmi
     */
    public function announcementShow(string $slug): View
    {
        $announcement = Announcement::published()->where('slug', $slug)->firstOrFail();
        $recentAnnouncements = Announcement::published()->where('id', '!=', $announcement->id)->take(3)->get();

        return view('pages.announcements.show', compact('announcement', 'recentAnnouncements'));
    }

    /**
     * Halaman katalog mata lomba & petunjuk teknis (Juknis)
     */
    public function competitions(): View
    {
        $competitions = Competition::published()->with('schedules')->get();

        return view('pages.competitions.index', compact('competitions'));
    }

    /**
     * Halaman jadwal publik kegiatan & arena lomba
     */
    public function schedules(Request $request): View
    {
        $query = CompetitionSchedule::with('competition')->published();

        if ($request->filled('day')) {
            $query->whereDate('start_time', $request->day);
        }

        $schedules = $query->paginate(10)->withQueryString();
        $dates = CompetitionSchedule::published()
            ->selectRaw('DATE(start_time) as schedule_date')
            ->distinct()
            ->pluck('schedule_date');

        return view('pages.schedules.index', compact('schedules', 'dates'));
    }

    /**
     * Halaman panduan pendaftaran kontingen dan penggunaan sistem
     */
    public function guide(): View
    {
        return view('pages.guide');
    }

    /**
     * Halaman klasemen resmi (Live Rank Preview)
     */
    public function leaderboard(): View
    {
        return view('pages.leaderboard');
    }
}

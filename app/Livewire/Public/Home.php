<?php

namespace App\Livewire\Public;

use App\Enums\RegistrationStatusEnum;
use App\Models\Announcement;
use App\Models\Competition;
use App\Models\CompetitionSchedule;
use App\Models\Event;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Halaman depan (landing page) kegiatan LT III
 */
#[Layout('layouts.public', ['title' => 'Beranda Resmi LT III Kwarcab Padangsidimpuan'])]
#[Title('Beranda Resmi LT III Kwarcab Padangsidimpuan')]
class Home extends Component
{
    public function render()
    {
        return view('livewire.public.home', [
            'event' => Event::where('is_active', true)->first(),
            'latestAnnouncements' => Announcement::published()->take(3)->get(),
            'competitions' => Competition::published()->take(6)->get(),
            'upcomingSchedules' => CompetitionSchedule::with('competition')->published()->take(5)->get(),
            'stats' => [
                'total_competitions' => Competition::published()->count(),
                'total_schedules' => CompetitionSchedule::published()->count(),
                'total_announcements' => Announcement::published()->count(),
                'total_groups' => ScoutGroup::where('verification_status', RegistrationStatusEnum::Verified)->count(),
                'total_patrols' => Patrol::where('status', RegistrationStatusEnum::Verified)->count(),
            ],
        ]);
    }
}

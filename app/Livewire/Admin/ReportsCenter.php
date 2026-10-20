<?php

namespace App\Livewire\Admin;

use App\Enums\RegistrationStatusEnum;
use App\Models\Attendance;
use App\Models\Competition;
use App\Models\CompetitionScore;
use App\Models\Participant;
use App\Models\ScoutGroup;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Komponen Livewire 4 Pusat Ekspor & Pelaporan Resmi Kegiatan (Phase 10)
 */
#[Layout('layouts.app')]
#[Title('Pusat Laporan & Ekspor Data Resmi — LT III 2026')]
class ReportsCenter extends Component
{
    public string $standingsGender = 'all';

    #[Computed]
    public function totalScoutGroups(): int
    {
        return ScoutGroup::where('verification_status', RegistrationStatusEnum::Verified)->count();
    }

    #[Computed]
    public function totalParticipants(): int
    {
        return Participant::count();
    }

    #[Computed]
    public function totalAttendances(): int
    {
        return Attendance::count();
    }

    #[Computed]
    public function totalCompetitions(): int
    {
        return Competition::where('is_published', true)->count();
    }

    #[Computed]
    public function totalScoresEntered(): int
    {
        return CompetitionScore::count();
    }

    public function render()
    {
        return view('livewire.admin.reports-center', [
            'totalScoutGroups' => $this->totalScoutGroups,
            'totalParticipants' => $this->totalParticipants,
            'totalAttendances' => $this->totalAttendances,
            'totalCompetitions' => $this->totalCompetitions,
            'totalScoresEntered' => $this->totalScoresEntered,
        ]);
    }
}

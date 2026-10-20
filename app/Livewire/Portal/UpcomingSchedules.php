<?php

namespace App\Livewire\Portal;

use App\Models\CompetitionSchedule;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Halaman agenda kegiatan untuk seluruh pengguna: sesi yang sedang berlangsung dan terdekat, berurutan
 */
#[Layout('layouts.app')]
#[Title('Jadwal Kegiatan')]
class UpcomingSchedules extends Component
{
    /**
     * Sesi yang belum selesai (berlangsung atau akan datang), dikelompokkan per tanggal
     */
    #[Computed]
    public function groupedSchedules(): Collection
    {
        return CompetitionSchedule::with('competition')
            ->published()
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->where('end_time', '>=', now())
            ->limit(100)
            ->get()
            ->groupBy(fn (CompetitionSchedule $schedule) => $schedule->start_time->toDateString());
    }

    public function render()
    {
        return view('livewire.portal.upcoming-schedules', [
            'groups' => $this->groupedSchedules,
        ]);
    }
}

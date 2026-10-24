<?php

namespace App\Livewire\Portal;

use App\Models\CompetitionSchedule;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Halaman agenda kegiatan untuk seluruh pengguna: seluruh sesi terbit (termasuk yang sudah selesai), berurutan waktu
 */
#[Layout('layouts.app')]
#[Title('Jadwal Kegiatan')]
class UpcomingSchedules extends Component
{
    /** Filter tanggal (Y-m-d) atau 'all'; kosong = belum dipilih, otomatis hari ini bila ada jadwal */
    #[Url(as: 'tanggal')]
    public string $date = '';

    public function mount(): void
    {
        if ($this->date === '') {
            $this->date = $this->availableDates->contains(now()->toDateString()) ? now()->toDateString() : 'all';
        }
    }

    /**
     * Tanggal yang memiliki sesi terbit (tidak dibatalkan), berurutan
     */
    #[Computed]
    public function availableDates(): Collection
    {
        return CompetitionSchedule::published()
            ->where('status', '!=', 'cancelled')
            ->pluck('start_time')
            ->map(fn ($time) => $time->toDateString())
            ->unique()
            ->values();
    }

    public function filterDate(string $date): void
    {
        $this->date = $date;
    }

    /**
     * Seluruh sesi terbit selain yang dibatalkan (selesai tetap tampil), dikelompokkan per tanggal
     */
    #[Computed]
    public function groupedSchedules(): Collection
    {
        return CompetitionSchedule::with('competition')
            ->published()
            ->where('status', '!=', 'cancelled')
            ->when($this->date !== 'all', fn ($query) => $query->whereDate('start_time', $this->date))
            ->get()
            ->groupBy(fn (CompetitionSchedule $schedule) => $schedule->start_time->toDateString());
    }

    public function render()
    {
        return view('livewire.portal.upcoming-schedules', [
            'groups' => $this->groupedSchedules,
            'dates' => $this->availableDates,
        ]);
    }
}

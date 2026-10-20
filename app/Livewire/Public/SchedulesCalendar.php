<?php

namespace App\Livewire\Public;

use App\Models\CompetitionSchedule;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Komponen Livewire 4 Jadwal Publik & Kalender Pertandingan LT III
 */
#[Layout('layouts.public', ['title' => 'Jadwal Kegiatan & Lomba'])]
#[Title('Jadwal Kegiatan & Lomba — LT III')]
class SchedulesCalendar extends Component
{
    use WithPagination;

    public string $selectedDate = '';
    public string $search = '';
    public string $typeFilter = 'all';
    public string $genderFilter = 'all';

    public function selectDate(string $date): void
    {
        $this->selectedDate = $date;
        $this->resetPage();
    }

    public function clearDate(): void
    {
        $this->selectedDate = '';
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedGenderFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function availableDates()
    {
        return CompetitionSchedule::published()
            ->selectRaw('DATE(start_time) as schedule_date')
            ->distinct()
            ->orderBy('schedule_date')
            ->pluck('schedule_date');
    }

    public function render()
    {
        $query = CompetitionSchedule::with('competition')->published();

        if ($this->selectedDate !== '') {
            $query->whereDate('start_time', $this->selectedDate);
        }

        if ($this->typeFilter !== 'all') {
            if ($this->typeFilter === 'patrol') {
                $query->where(function ($q) {
                    $q->whereIn('participant_type', ['patrol', 'full_patrol'])
                        ->orWhere(function ($sub) {
                            $sub->whereNull('participant_type')
                                ->whereHas('competition', function ($cq) {
                                    $cq->whereIn('participant_type', ['patrol', 'full_patrol']);
                                });
                        });
                });
            } elseif ($this->typeFilter === 'group') {
                $query->where(function ($q) {
                    $q->where('participant_type', 'group')
                        ->orWhere(function ($sub) {
                            $sub->whereNull('participant_type')
                                ->whereHas('competition', function ($cq) {
                                    $cq->where('participant_type', 'group');
                                });
                        });
                });
            } elseif ($this->typeFilter === 'individual') {
                $query->where(function ($q) {
                    $q->where('participant_type', 'individual')
                        ->orWhere(function ($sub) {
                            $sub->whereNull('participant_type')
                                ->whereHas('competition', function ($cq) {
                                    $cq->where('participant_type', 'individual');
                                });
                        });
                });
            }
        }

        if ($this->genderFilter !== 'all') {
            if ($this->genderFilter === 'boys') {
                $query->where(function ($q) {
                    $q->where('gender_category', 'boys')
                        ->orWhere(function ($sub) {
                            $sub->where(function ($fallback) {
                                $fallback->where('gender_category', 'all')->orWhereNull('gender_category');
                            })->whereHas('competition', fn ($cq) => $cq->where('category', 'boys'));
                        });
                });
            } elseif ($this->genderFilter === 'girls') {
                $query->where(function ($q) {
                    $q->where('gender_category', 'girls')
                        ->orWhere(function ($sub) {
                            $sub->where(function ($fallback) {
                                $fallback->where('gender_category', 'all')->orWhereNull('gender_category');
                            })->whereHas('competition', fn ($cq) => $cq->where('category', 'girls'));
                        });
                });
            }
        }

        if ($this->search !== '') {
            $term = '%'.$this->search.'%';
            $query->where(function ($q) use ($term) {
                $q->where('session_name', 'like', $term)
                    ->orWhere('arena_location', 'like', $term)
                    ->orWhereHas('competition', function ($cq) use ($term) {
                        $cq->where('title', 'like', $term)
                            ->orWhere('code', 'like', $term);
                    });
            });
        }

        $schedules = $query->orderBy('start_time')->paginate(10);

        return view('livewire.public.schedules-calendar', [
            'schedules' => $schedules,
            'dates' => $this->availableDates,
        ]);
    }
}

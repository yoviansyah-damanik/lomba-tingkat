<?php

namespace App\Livewire\Home;

use App\Models\CompetitionSchedule;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Component;

/**
 * Panel agenda dasbor: sesi hari ini, atau sesi terdekat bila hari ini tidak ada jadwal.
 * Juri hanya melihat sesi yang ditugaskan kepadanya.
 */
#[Lazy]
class AgendaPanel extends Component
{
    private const UPCOMING_LIMIT = 5;

    public function placeholder(): string
    {
        return '<div class="h-56 animate-pulse rounded-2xl border border-zinc-200 bg-zinc-100 dark:border-zinc-800 dark:bg-zinc-800/60"></div>';
    }

    private function baseQuery()
    {
        $user = auth()->user();

        return CompetitionSchedule::published()
            ->where('status', '!=', 'cancelled')
            ->when($user->isJudge() && ! $user->isAdmin() && ! $user->isCommittee(), fn ($q) => $q->whereHas('judgeAssignments', fn ($a) => $a->where('user_id', $user->id)));
    }

    /**
     * Sesi hari ini (termasuk yang sudah selesai), berurutan waktu
     */
    #[Computed]
    public function today(): Collection
    {
        return $this->baseQuery()->whereDate('start_time', today())->get();
    }

    /**
     * Sesi yang belum selesai pada hari-hari berikutnya (dipakai saat hari ini tidak ada jadwal)
     */
    #[Computed]
    public function upcoming(): Collection
    {
        return $this->baseQuery()
            ->where('end_time', '>=', now())
            ->whereDate('start_time', '>', today())
            ->limit(self::UPCOMING_LIMIT)
            ->get();
    }

    public function render()
    {
        return view('livewire.home.agenda-panel', [
            'today' => $this->today,
            'upcoming' => $this->upcoming,
        ]);
    }
}

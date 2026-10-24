<?php

namespace App\Livewire\Home;

use App\Models\CompetitionSchedule;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Component;

/**
 * Panel dasbor Dewan Juri: lomba yang akan datang pada penugasan juri bersangkutan
 */
#[Lazy]
class JudgeUpcomingPanel extends Component
{
    private const LIMIT = 8;

    public function placeholder(): string
    {
        return '<div class="h-56 animate-pulse rounded-2xl border border-zinc-200 bg-zinc-100 dark:border-zinc-800 dark:bg-zinc-800/60"></div>';
    }

    /**
     * Sesi ditugaskan yang belum selesai (sedang berlangsung atau akan datang), berurutan waktu
     */
    #[Computed]
    public function sessions(): Collection
    {
        return CompetitionSchedule::with('competition')
            ->published()
            ->where('status', '!=', 'cancelled')
            ->where('end_time', '>=', now())
            ->whereHas('judgeAssignments', fn ($q) => $q->where('user_id', auth()->id()))
            ->limit(self::LIMIT)
            ->get();
    }

    public function render()
    {
        return view('livewire.home.judge-upcoming-panel', ['sessions' => $this->sessions]);
    }
}

<?php

namespace App\Livewire\Home;

use App\Models\Announcement;
use Livewire\Attributes\Lazy;
use Livewire\Component;

/**
 * Panel dasbor: pengumuman resmi terbaru
 */
#[Lazy]
class AnnouncementsPanel extends Component
{
    public function placeholder(): string
    {
        return '<div class="h-56 animate-pulse rounded-2xl border border-zinc-200 bg-zinc-100 dark:border-zinc-800 dark:bg-zinc-800/60"></div>';
    }

    public function render()
    {
        return view('livewire.home.announcements-panel', [
            'announcements' => Announcement::published()->limit(4)->get(),
        ]);
    }
}

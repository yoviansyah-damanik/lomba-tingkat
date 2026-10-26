<?php

namespace App\Livewire\Public;

use App\Models\Announcement;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Halaman detail pengumuman resmi
 */
#[Layout('layouts.public')]
class AnnouncementShow extends Component
{
    public Announcement $announcement;

    public function mount(string $slug): void
    {
        $this->announcement = Announcement::published()->where('slug', $slug)->firstOrFail();
    }

    public function render()
    {
        return view('livewire.public.announcement-show', [
            'recentAnnouncements' => Announcement::published()->where('id', '!=', $this->announcement->id)->take(3)->get(),
        ])->title($this->announcement->title);
    }
}

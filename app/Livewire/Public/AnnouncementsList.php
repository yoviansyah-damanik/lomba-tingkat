<?php

namespace App\Livewire\Public;

use App\Models\Announcement;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Komponen Livewire 4 Pengumuman Resmi & Surat Edaran Publik
 */
#[Layout('layouts.public', ['title' => 'Pengumuman Resmi'])]
#[Title('Pengumuman Resmi — LT III')]
class AnnouncementsList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $category = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategory(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Announcement::published();

        if ($this->category !== '') {
            $query->where('category', $this->category);
        }

        if ($this->search !== '') {
            $query->where(function ($q) {
                $term = '%'.$this->search.'%';
                $q->where('title', 'like', $term)
                    ->orWhere('summary', 'like', $term)
                    ->orWhere('content', 'like', $term);
            });
        }

        $announcements = $query->latest('published_at')->paginate(9);

        return view('livewire.public.announcements-list', [
            'announcements' => $announcements,
        ]);
    }
}

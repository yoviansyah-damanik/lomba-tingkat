<?php

namespace App\Livewire\Notifications;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Component;

/**
 * Dropdown lonceng notifikasi: daftar pemberitahuan, jumlah belum dibaca, dan penandaan dibaca
 */
#[Lazy]
class Bell extends Component
{
    /** end: dropdown rata kanan tombol (dasbor); header: melebar penuh di mobile pada header publik */
    public string $placement = 'end';

    public function placeholder(): string
    {
        return '<div class="size-8 animate-pulse rounded-lg bg-zinc-200 dark:bg-zinc-700"></div>';
    }

    #[Computed]
    public function unreadCount(): int
    {
        return auth()->user()->unreadNotifications()->count();
    }

    #[Computed]
    public function notifications(): Collection
    {
        return auth()->user()->notifications()->latest()->limit(15)->get();
    }

    /**
     * Tandai dibaca lalu buka tautan tujuan (jika ada)
     */
    public function open(string $id): void
    {
        $notification = $this->findOwned($id);
        $notification->markAsRead();

        if ($url = $notification->data['url'] ?? null) {
            $this->redirect($url, navigate: true);
        }
    }

    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
    }

    protected function findOwned(string $id): DatabaseNotification
    {
        return auth()->user()->notifications()->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.notifications.bell');
    }
}

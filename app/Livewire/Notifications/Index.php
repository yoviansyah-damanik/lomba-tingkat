<?php

namespace App\Livewire\Notifications;

use Illuminate\Notifications\DatabaseNotification;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Halaman daftar seluruh notifikasi dalam aplikasi milik pengguna
 */
#[Layout('layouts.app')]
#[Title('Notifikasi')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'filter')]
    public string $filter = 'all';

    public function updatedFilter(): void
    {
        if (! in_array($this->filter, ['all', 'unread'], true)) {
            $this->filter = 'all';
        }

        $this->resetPage();
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

    public function markAsRead(string $id): void
    {
        $this->findOwned($id)->markAsRead();
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
        $user = auth()->user();
        $query = $this->filter === 'unread' ? $user->unreadNotifications() : $user->notifications();

        return view('livewire.notifications.index', [
            'notifications' => $query->latest()->paginate(15),
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }
}

<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\PushSubscription;
use App\Services\WebPushService;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Komponen Livewire 4 Manajemen Siaran Web Push Notification (VAPID)
 */
#[Layout('layouts.app')]
#[Title('Siaran Notifikasi Web Push — PWA')]
class PushBroadcastManagement extends Component
{
    public string $title = '';
    public string $body = '';
    public string $targetRole = ''; // empty for all
    public string $actionUrl = '/announcements';
    public string $urgencyTag = 'announcement';

    public bool $isSending = false;

    protected array $rules = [
        'title' => 'required|string|min:3|max:100',
        'body' => 'required|string|min:5|max:300',
        'actionUrl' => 'required|string|max:255',
        'targetRole' => 'nullable|string',
    ];

    protected array $messages = [
        'title.required' => 'Judul notifikasi wajib diisi.',
        'title.min' => 'Judul minimal 3 karakter.',
        'body.required' => 'Isi pesan notifikasi wajib diisi.',
        'body.min' => 'Isi pesan minimal 5 karakter.',
        'actionUrl.required' => 'Tautan tujuan wajib diisi.',
    ];

    #[Computed]
    public function totalSubscribers(): int
    {
        return PushSubscription::count();
    }

    #[Computed]
    public function deviceBreakdown(): Collection
    {
        return PushSubscription::selectRaw('device_label, count(*) as count')
            ->groupBy('device_label')
            ->orderByDesc('count')
            ->get();
    }

    #[Computed]
    public function recentBroadcasts(): Collection
    {
        return AuditLog::with('user')
            ->where('action', 'push_broadcast_sent')
            ->latest()
            ->take(10)
            ->get();
    }

    /**
     * Mengirim siaran notifikasi web push
     */
    public function sendBroadcast(WebPushService $webPushService): void
    {
        $this->validate();

        $this->isSending = true;

        $payload = [
            'title' => $this->title,
            'body' => $this->body,
            'url' => $this->actionUrl,
            'tag' => 'lt3-' . $this->urgencyTag . '-' . time(),
            'icon' => '/logo.png',
            'badge' => '/favicon.png',
        ];

        $target = $this->targetRole ?: null;

        $result = $webPushService->broadcastNotification(
            payload: $payload,
            targetRole: $target,
            sender: auth()->user()
        );

        $this->isSending = false;

        // Reset form
        $this->title = '';
        $this->body = '';
        $this->actionUrl = '/announcements';

        if ($result['total'] === 0) {
            session()->flash('warning', 'Belum ada perangkat yang terdaftar untuk target audiens ini.');
            Flux::toast(variant: 'warning', heading: 'Tidak Ada Penerima', text: 'Belum ada perangkat yang terdaftar untuk target audiens ini.');
        } elseif ($result['failed'] > 0) {
            $message = "Terkirim ke {$result['sent']} dari {$result['total']} perangkat. {$result['failed']} gagal (kemungkinan langganan kedaluwarsa atau kunci VAPID belum diatur).";
            session()->flash('warning', $message);
            Flux::toast(variant: 'warning', heading: 'Siaran Terkirim Sebagian', text: $message);
        } else {
            $message = "Siaran notifikasi berhasil dikirimkan ke seluruh {$result['sent']} perangkat aktif.";
            session()->flash('success', $message);
            Flux::toast(variant: 'success', heading: 'Siaran Terkirim', text: $message);
        }
    }

    public function render()
    {
        return view('livewire.admin.push-broadcast-management', [
            'totalSubscribers' => $this->totalSubscribers,
            'deviceBreakdown' => $this->deviceBreakdown,
            'recentBroadcasts' => $this->recentBroadcasts,
        ]);
    }
}

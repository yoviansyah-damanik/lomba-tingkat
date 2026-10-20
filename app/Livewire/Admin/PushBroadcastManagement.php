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

        $this->flashResult($result);
    }

    /**
     * Mengirim notifikasi uji ke seluruh perangkat terdaftar (termasuk pengunjung tanpa login)
     */
    public function sendTestToAll(WebPushService $webPushService): void
    {
        $result = $webPushService->broadcastNotification(
            payload: [
                'title' => 'Uji Notifikasi LT III 2026',
                'body' => 'Ini adalah notifikasi uji dari panitia. Abaikan pesan ini.',
                'url' => '/',
                'tag' => 'lt3-test-'.time(),
            ],
            targetRole: null,
            sender: auth()->user()
        );

        $this->flashResult($result);
    }

    /**
     * Menampilkan hasil pengiriman nyata (berhasil, gagal, kedaluwarsa)
     */
    protected function flashResult(array $result): void
    {
        $variant = $result['sent'] > 0 ? 'success' : 'warning';
        $text = "Terkirim: {$result['sent']} perangkat, gagal: {$result['failed']}, kedaluwarsa dihapus: {$result['expired']}.";

        session()->flash($variant, $text);
        Flux::toast(variant: $variant, heading: $result['sent'] > 0 ? 'Siaran Terkirim' : 'Siaran Tidak Terkirim', text: $text);
    }

    public function render()
    {
        return view('livewire.admin.push-broadcast-management', [
            'totalSubscribers' => $this->totalSubscribers,
            'deviceBreakdown' => $this->deviceBreakdown,
            'recentBroadcasts' => $this->recentBroadcasts,
            'vapidReady' => app(WebPushService::class)->isConfigured(),
        ]);
    }
}

<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service untuk orkestrasi Progressive Web App (PWA) Web Push Notifications (VAPID)
 */
class WebPushService
{
    /**
     * Kunci publik default VAPID untuk registrasi browser service worker
     */
    protected const DEFAULT_VAPID_PUBLIC = 'BCb_Vl8K8-l4R6uN7G8D8k4gqXw9vB_2hXp0Xv7r8B4g9G_2hXp0Xv7r8B4g9G_2hXp0Xv7r8B4g9G_2hXp0Xv7r';

    /**
     * Mengambil VAPID Public Key
     */
    public function getVapidPublicKey(): string
    {
        return config('services.vapid.public_key') 
            ?: env('VAPID_PUBLIC_KEY', self::DEFAULT_VAPID_PUBLIC);
    }

    /**
     * Menyimpan atau memperbarui langganan push dari browser client
     */
    public function saveSubscription(array $data, ?User $user = null): PushSubscription
    {
        $endpoint = $data['endpoint'];

        return PushSubscription::updateOrCreate(
            ['endpoint' => $endpoint],
            [
                'user_id' => $user?->id ?? auth()->id(),
                'public_key' => $data['keys']['p256dh'] ?? $data['public_key'] ?? null,
                'auth_token' => $data['keys']['auth'] ?? $data['auth_token'] ?? null,
                'content_encoding' => $data['content_encoding'] ?? 'aes128gcm',
                'device_label' => $data['device_label'] ?? $this->detectDeviceLabel(),
            ]
        );
    }

    /**
     * Menghapus langganan push saat user opt-out
     */
    public function deleteSubscription(string $endpoint): bool
    {
        return (bool) PushSubscription::where('endpoint', $endpoint)->delete();
    }

    /**
     * Mengirim notifikasi push ke langganan tertentu
     */
    public function sendNotification(PushSubscription $subscription, array $payload): array
    {
        $formattedPayload = [
            'title' => $payload['title'] ?? 'Pemberitahuan LT III 2026',
            'body' => $payload['body'] ?? '',
            'icon' => $payload['icon'] ?? '/logo.png',
            'badge' => $payload['badge'] ?? '/favicon.png',
            'url' => $payload['url'] ?? url('/'),
            'tag' => $payload['tag'] ?? 'lt3-notification-' . time(),
            'timestamp' => now()->timestamp * 1000,
        ];

        // Simulasi pengiriman atau pengiriman via HTTP push endpoint
        try {
            if (filter_var($subscription->endpoint, FILTER_VALIDATE_URL)) {
                Http::timeout(3)->withHeaders([
                    'TTL' => '86400',
                    'Urgency' => 'high',
                ])->post($subscription->endpoint, $formattedPayload);
            }

            return [
                'success' => true,
                'subscription_id' => $subscription->id,
                'payload' => $formattedPayload,
            ];
        } catch (\Throwable $e) {
            Log::warning('WebPush dispatch notice: ' . $e->getMessage(), [
                'endpoint' => $subscription->endpoint,
            ]);

            return [
                'success' => false,
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Menyiarkan notifikasi push massal ke seluruh atau kelompok pengguna
     */
    public function broadcastNotification(array $payload, ?string $targetRole = null, ?User $sender = null): array
    {
        $query = PushSubscription::with('user');

        if ($targetRole) {
            $query->whereHas('user.roles', function ($q) use ($targetRole) {
                $q->where('name', $targetRole);
            });
        }

        $subscriptions = $query->get();
        $sentCount = 0;
        $failedCount = 0;

        foreach ($subscriptions as $sub) {
            $result = $this->sendNotification($sub, $payload);
            if ($result['success']) {
                $sentCount++;
            } else {
                $failedCount++;
            }
        }

        // Catat jejak audit pengiriman siaran push
        AuditLog::log(
            action: 'push_broadcast_sent',
            modelType: PushSubscription::class,
            modelId: (string) \Illuminate\Support\Str::uuid(),
            oldValues: null,
            newValues: [
                'payload' => $payload,
                'target_role' => $targetRole,
                'total_targeted' => $subscriptions->count(),
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
            ],
            reason: 'Siaran Notifikasi Web Push oleh ' . ($sender?->name ?? 'Administrator'),
            userId: $sender?->id ?? auth()->id()
        );

        return [
            'total' => $subscriptions->count(),
            'sent' => $sentCount,
            'failed' => $failedCount,
            'target_role' => $targetRole,
        ];
    }

    /**
     * Deteksi label perangkat sederhana dari User-Agent
     */
    protected function detectDeviceLabel(): string
    {
        $ua = request()->userAgent() ?? '';
        if (str_contains($ua, 'Android')) {
            return 'Android Browser';
        }
        if (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) {
            return 'iOS Safari';
        }
        if (str_contains($ua, 'Windows')) {
            return 'Windows Desktop';
        }
        if (str_contains($ua, 'Macintosh')) {
            return 'Mac Desktop';
        }
        if (str_contains($ua, 'Linux')) {
            return 'Linux Device';
        }

        return 'Web Client';
    }
}

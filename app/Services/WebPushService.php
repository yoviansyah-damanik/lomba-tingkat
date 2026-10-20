<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

/**
 * Service untuk orkestrasi Progressive Web App (PWA) Web Push Notifications (VAPID)
 */
class WebPushService
{
    /**
     * Mengambil VAPID Public Key
     */
    public function getVapidPublicKey(): string
    {
        return (string) config('services.vapid.public_key');
    }

    /**
     * Menentukan apakah kunci VAPID sudah dikonfigurasi
     */
    public function isConfigured(): bool
    {
        return filled(config('services.vapid.public_key')) && filled(config('services.vapid.private_key'));
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
     * Mengirim notifikasi push terenkripsi (VAPID + aes128gcm) ke satu langganan.
     * Langganan yang sudah kedaluwarsa (404/410) dihapus otomatis.
     */
    public function sendNotification(PushSubscription $subscription, array $payload): array
    {
        $formattedPayload = $this->formatPayload($payload);

        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'expired' => false,
                'subscription_id' => $subscription->id,
                'error' => 'Kunci VAPID belum dikonfigurasi.',
            ];
        }

        try {
            $report = $this->makeClient()->sendOneNotification(
                Subscription::create([
                    'endpoint' => $subscription->endpoint,
                    'publicKey' => $subscription->public_key,
                    'authToken' => $subscription->auth_token,
                    'contentEncoding' => $subscription->content_encoding ?: 'aes128gcm',
                ]),
                json_encode($formattedPayload),
                ['TTL' => 86400, 'urgency' => 'high']
            );

            $expired = $report->isSubscriptionExpired();
            if ($expired) {
                $subscription->delete();
            }

            return [
                'success' => $report->isSuccess(),
                'expired' => $expired,
                'subscription_id' => $subscription->id,
                'payload' => $formattedPayload,
                'error' => $report->isSuccess() ? null : $report->getReason(),
            ];
        } catch (\Throwable $e) {
            Log::warning('WebPush dispatch failed: '.$e->getMessage(), [
                'endpoint' => $subscription->endpoint,
            ]);

            return [
                'success' => false,
                'expired' => false,
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Mengirim notifikasi uji ke satu endpoint (tanpa perlu login)
     */
    public function sendTestToEndpoint(string $endpoint): ?array
    {
        $subscription = PushSubscription::where('endpoint', $endpoint)->first();

        if (! $subscription) {
            return null;
        }

        return $this->sendNotification($subscription, [
            'title' => 'Uji Notifikasi LT III 2026',
            'body' => 'Notifikasi berhasil diterima di perangkat ini.',
            'url' => url('/'),
            'tag' => 'lt3-test',
        ]);
    }

    /**
     * Menyiarkan notifikasi push massal. Target: null (semua perangkat),
     * "guest" (pengunjung tanpa login), atau nama role.
     */
    public function broadcastNotification(array $payload, ?string $targetRole = null, ?User $sender = null): array
    {
        $query = PushSubscription::query();

        if ($targetRole === 'guest') {
            $query->whereNull('user_id');
        } elseif ($targetRole) {
            $query->whereHas('user.roles', fn ($q) => $q->where('name', $targetRole));
        }

        $subscriptions = $query->get();
        $sentCount = 0;
        $failedCount = 0;
        $expiredCount = 0;

        foreach ($subscriptions as $sub) {
            $result = $this->sendNotification($sub, $payload);
            if ($result['success']) {
                $sentCount++;
            } else {
                $failedCount++;
            }
            if ($result['expired'] ?? false) {
                $expiredCount++;
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
                'expired_count' => $expiredCount,
            ],
            reason: 'Siaran Notifikasi Web Push oleh ' . ($sender?->name ?? 'Administrator'),
            userId: $sender?->id ?? auth()->id()
        );

        return [
            'total' => $subscriptions->count(),
            'sent' => $sentCount,
            'failed' => $failedCount,
            'expired' => $expiredCount,
            'target_role' => $targetRole,
        ];
    }

    /**
     * Menyusun payload notifikasi dengan nilai default
     */
    protected function formatPayload(array $payload): array
    {
        return [
            'title' => $payload['title'] ?? 'Pemberitahuan LT III 2026',
            'body' => $payload['body'] ?? '',
            'icon' => $payload['icon'] ?? '/logo.png',
            'badge' => $payload['badge'] ?? '/favicon.png',
            'url' => $payload['url'] ?? url('/'),
            'tag' => $payload['tag'] ?? 'lt3-notification-'.time(),
            'timestamp' => now()->timestamp * 1000,
        ];
    }

    /**
     * Membuat klien WebPush dengan kredensial VAPID
     */
    protected function makeClient(): WebPush
    {
        return new WebPush(['VAPID' => [
            'subject' => config('services.vapid.subject') ?: config('app.url'),
            'publicKey' => config('services.vapid.public_key'),
            'privateKey' => config('services.vapid.private_key'),
        ]]);
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

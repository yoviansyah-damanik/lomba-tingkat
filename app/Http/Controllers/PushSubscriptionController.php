<?php

namespace App\Http\Controllers;

use App\Services\WebPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function __construct(
        protected WebPushService $webPushService
    ) {}

    /**
     * Mendapatkan VAPID public key untuk registrasi browser service worker
     */
    public function vapidPublicKey(): JsonResponse
    {
        $publicKey = $this->webPushService->getVapidPublicKey();

        if ($publicKey === '') {
            return response()->json([
                'publicKey' => null,
                'message' => 'Kunci VAPID belum dikonfigurasi di server. Hubungi Administrator.',
            ], 503);
        }

        return response()->json([
            'publicKey' => $publicKey,
        ]);
    }

    /**
     * Mendaftarkan atau memperbarui langganan web push
     */
    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
            'keys.p256dh' => 'nullable|string',
            'keys.auth' => 'nullable|string',
            'content_encoding' => 'nullable|string',
            'device_label' => 'nullable|string',
        ]);

        $subscription = $this->webPushService->saveSubscription($validated, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Langganan notifikasi berhasil didaftarkan.',
            'subscription_id' => $subscription->id,
        ]);
    }

    /**
     * Menghapus langganan web push
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
        ]);

        $deleted = $this->webPushService->deleteSubscription($validated['endpoint']);

        return response()->json([
            'success' => $deleted,
            'message' => $deleted ? 'Langganan notifikasi berhasil dihentikan.' : 'Langganan tidak ditemukan.',
        ]);
    }
}

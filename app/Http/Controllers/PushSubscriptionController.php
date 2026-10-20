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
        return response()->json([
            'publicKey' => $this->webPushService->getVapidPublicKey(),
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
     * Mengirim notifikasi uji hanya ke perangkat pemanggil (tanpa login)
     */
    public function test(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
        ]);

        $result = $this->webPushService->sendTestToEndpoint($validated['endpoint']);

        if ($result === null) {
            return response()->json([
                'success' => false,
                'message' => 'Perangkat belum terdaftar. Aktifkan notifikasi terlebih dahulu.',
            ], 404);
        }

        return response()->json([
            'success' => $result['success'],
            'message' => $result['success']
                ? 'Notifikasi uji dikirim. Periksa tray notifikasi perangkat Anda.'
                : 'Gagal mengirim notifikasi uji: '.($result['error'] ?? 'tidak diketahui'),
        ], $result['success'] ? 200 : 502);
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

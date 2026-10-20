<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Service untuk orkestrasi Progressive Web App (PWA) Web Push Notifications.
 *
 * Mengimplementasikan standar Web Push secara native (tanpa dependensi composer
 * tambahan) mengikuti:
 * - RFC 8291 (Message Encryption for Web Push, "aes128gcm")
 * - RFC 8188 (Encrypted Content-Encoding for HTTP, content coding aes128gcm)
 * - RFC 8292 (Voluntary Application Server Identification / VAPID, JWS ES256)
 */
class WebPushService
{
    protected const EC_CURVE = 'prime256v1';

    /**
     * Mengambil VAPID Public Key (dibagikan ke browser sebagai applicationServerKey)
     */
    public function getVapidPublicKey(): string
    {
        return (string) config('services.vapid.public_key');
    }

    /**
     * Mengambil VAPID Private Key (hanya dipakai di server untuk menandatangani JWT)
     */
    protected function getVapidPrivateKey(): string
    {
        return (string) config('services.vapid.private_key');
    }

    /**
     * Mengambil subjek kontak VAPID (mailto: atau URL) yang disertakan dalam JWT
     */
    protected function getVapidSubject(): string
    {
        $subject = (string) config('services.vapid.subject');

        if ($subject !== '') {
            return $subject;
        }

        $host = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'localhost';

        return 'mailto:admin@' . $host;
    }

    /**
     * Cek apakah kunci VAPID sudah dikonfigurasi dengan benar
     */
    public function isConfigured(): bool
    {
        return $this->getVapidPublicKey() !== '' && $this->getVapidPrivateKey() !== '';
    }

    /**
     * Membuat pasangan kunci VAPID (P-256) baru untuk perintah `artisan vapid:generate`
     */
    public function generateVapidKeypair(): array
    {
        [$privateKeyRaw, $publicKeyRaw] = $this->generateEcKeypair();

        return [
            'publicKey' => $this->base64UrlEncode($publicKeyRaw),
            'privateKey' => $this->base64UrlEncode($privateKeyRaw),
        ];
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
     * Mengirim notifikasi push terenkripsi ke satu langganan tertentu
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

        if (! $this->isConfigured()) {
            Log::warning('WebPush: kunci VAPID belum dikonfigurasi. Jalankan `php artisan vapid:generate`.');

            return [
                'success' => false,
                'subscription_id' => $subscription->id,
                'error' => 'Kunci VAPID belum dikonfigurasi di server.',
            ];
        }

        if (! $subscription->public_key || ! $subscription->auth_token) {
            return [
                'success' => false,
                'subscription_id' => $subscription->id,
                'error' => 'Langganan tidak memiliki kunci enkripsi (p256dh/auth) yang valid.',
            ];
        }

        try {
            $encryptedBody = $this->encryptPayload(
                json_encode($formattedPayload),
                $subscription->public_key,
                $subscription->auth_token
            );

            $audience = $this->originOf($subscription->endpoint);
            $vapidJwt = $this->buildVapidJwt($audience);

            $response = Http::timeout(8)
                ->withHeaders([
                    'TTL' => '86400',
                    'Urgency' => 'high',
                    'Content-Encoding' => 'aes128gcm',
                    'Authorization' => 'vapid t=' . $vapidJwt . ', k=' . $this->getVapidPublicKey(),
                ])
                ->withBody($encryptedBody, 'application/octet-stream')
                ->post($subscription->endpoint);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'subscription_id' => $subscription->id,
                    'payload' => $formattedPayload,
                ];
            }

            // Endpoint push sudah kedaluwarsa / dicabut oleh browser -> bersihkan dari basis data
            if (in_array($response->status(), [404, 410], true)) {
                $subscription->delete();
            }

            Log::warning('WebPush: pengiriman ditolak oleh push service', [
                'endpoint' => $subscription->endpoint,
                'status' => $response->status(),
                'body' => Str::limit($response->body(), 300),
            ]);

            return [
                'success' => false,
                'subscription_id' => $subscription->id,
                'error' => 'Push service menolak permintaan (HTTP ' . $response->status() . ').',
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
     * Menyiarkan notifikasi push massal ke seluruh atau kelompok pengguna.
     * Memberikan `targetRole = null` akan mengirimkan ke SELURUH pengguna aplikasi
     * yang terdaftar langganan push (tidak dibatasi peran).
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

    // =========================================================================
    // Internal: enkripsi payload (RFC 8291 / RFC 8188 "aes128gcm") & VAPID JWT
    // =========================================================================

    /**
     * Mengenkripsi payload notifikasi untuk satu langganan sesuai RFC 8291,
     * dibungkus dalam content-coding "aes128gcm" sesuai RFC 8188.
     */
    protected function encryptPayload(string $plaintext, string $clientPublicKeyB64, string $clientAuthSecretB64): string
    {
        $uaPublicKey = $this->base64UrlDecode($clientPublicKeyB64);
        $authSecret = $this->base64UrlDecode($clientAuthSecretB64);

        if (strlen($uaPublicKey) !== 65) {
            throw new RuntimeException('Kunci publik klien (p256dh) tidak valid.');
        }
        if (strlen($authSecret) !== 16) {
            throw new RuntimeException('Auth secret klien tidak valid.');
        }

        // Kunci EC sementara (ephemeral) milik application server, dibuat baru setiap pengiriman
        [$asPrivateKeyRaw, $asPublicKeyRaw] = $this->generateEcKeypair();

        $ecdhSecret = openssl_pkey_derive(
            openssl_pkey_get_public($this->ecPublicPemFromRaw($uaPublicKey)),
            openssl_pkey_get_private($this->ecPrivatePemFromRaw($asPrivateKeyRaw, $asPublicKeyRaw)),
            32
        );

        if ($ecdhSecret === false) {
            throw new RuntimeException('Gagal menghitung ECDH shared secret: ' . openssl_error_string());
        }

        $prk = $this->hkdfExtract($authSecret, $ecdhSecret);
        $keyInfo = "WebPush: info\x00" . $uaPublicKey . $asPublicKeyRaw;
        $inputKeyingMaterial = $this->hkdfExpand($prk, $keyInfo, 32);

        $salt = random_bytes(16);
        $contentPrk = $this->hkdfExtract($salt, $inputKeyingMaterial);
        $contentEncryptionKey = $this->hkdfExpand($contentPrk, "Content-Encoding: aes128gcm\x00", 16);
        $nonce = $this->hkdfExpand($contentPrk, "Content-Encoding: nonce\x00", 12);

        // Penanda akhir record tunggal (0x02) sesuai RFC 8188, tanpa padding tambahan
        $paddedPlaintext = $plaintext . "\x02";

        $ciphertext = openssl_encrypt($paddedPlaintext, 'aes-128-gcm', $contentEncryptionKey, OPENSSL_RAW_DATA, $nonce, $tag);

        if ($ciphertext === false) {
            throw new RuntimeException('Gagal mengenkripsi payload notifikasi: ' . openssl_error_string());
        }

        $recordSize = 4096;
        $header = $salt . pack('N', $recordSize) . chr(strlen($asPublicKeyRaw)) . $asPublicKeyRaw;

        return $header . $ciphertext . $tag;
    }

    /**
     * Membuat JWT VAPID (ES256) yang ditandatangani kunci privat VAPID server
     */
    protected function buildVapidJwt(string $audience): string
    {
        $header = $this->base64UrlEncode(json_encode(['typ' => 'JWT', 'alg' => 'ES256']));
        $payload = $this->base64UrlEncode(json_encode([
            'aud' => $audience,
            'exp' => now()->addHours(12)->timestamp,
            'sub' => $this->getVapidSubject(),
        ]));

        $signingInput = $header . '.' . $payload;

        $privateKeyRaw = $this->base64UrlDecode($this->getVapidPrivateKey());
        $publicKeyRaw = $this->base64UrlDecode($this->getVapidPublicKey());

        $privateKeyResource = openssl_pkey_get_private($this->ecPrivatePemFromRaw($privateKeyRaw, $publicKeyRaw));

        if ($privateKeyResource === false) {
            throw new RuntimeException('Kunci privat VAPID tidak valid: ' . openssl_error_string());
        }

        if (! openssl_sign($signingInput, $derSignature, $privateKeyResource, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Gagal menandatangani token VAPID: ' . openssl_error_string());
        }

        $rawSignature = $this->derSignatureToRaw($derSignature);

        return $signingInput . '.' . $this->base64UrlEncode($rawSignature);
    }

    /**
     * Mengambil origin (scheme://host[:port]) dari URL endpoint push
     */
    protected function originOf(string $url): string
    {
        $parts = parse_url($url);
        $scheme = $parts['scheme'] ?? 'https';
        $host = $parts['host'] ?? '';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';

        return "{$scheme}://{$host}{$port}";
    }

    /**
     * Membuat pasangan kunci EC P-256 baru, dikembalikan sebagai [privateKeyRaw32, publicKeyRaw65]
     */
    protected function generateEcKeypair(): array
    {
        $resource = openssl_pkey_new([
            'curve_name' => self::EC_CURVE,
            'private_key_type' => OPENSSL_KEYTYPE_EC,
        ]);

        if ($resource === false) {
            throw new RuntimeException('Gagal membuat kunci EC sementara: ' . openssl_error_string());
        }

        $details = openssl_pkey_get_details($resource);

        $privateKeyRaw = str_pad($details['ec']['d'], 32, "\x00", STR_PAD_LEFT);
        $publicKeyRaw = "\x04"
            . str_pad($details['ec']['x'], 32, "\x00", STR_PAD_LEFT)
            . str_pad($details['ec']['y'], 32, "\x00", STR_PAD_LEFT);

        return [$privateKeyRaw, $publicKeyRaw];
    }

    /**
     * Membangun PEM "EC PRIVATE KEY" (SEC1, RFC 5915) dari skalar privat mentah (32 byte)
     * dan titik publik mentah tidak terkompresi (65 byte), agar bisa dipakai fungsi openssl_*.
     */
    protected function ecPrivatePemFromRaw(string $rawPrivateKey32, string $rawPublicKey65): string
    {
        if (strlen($rawPrivateKey32) !== 32) {
            throw new RuntimeException('Panjang kunci privat EC harus 32 byte.');
        }
        if (strlen($rawPublicKey65) !== 65) {
            throw new RuntimeException('Panjang kunci publik EC harus 65 byte.');
        }

        $version = "\x02\x01\x01";
        $privateKeyOctet = "\x04\x20" . $rawPrivateKey32;
        $curveOid = "\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07"; // OID 1.2.840.10045.3.1.7 (prime256v1)
        $curveParam = "\xa0" . $this->asn1Length(strlen($curveOid)) . $curveOid;
        $bitString = "\x03" . $this->asn1Length(1 + strlen($rawPublicKey65)) . "\x00" . $rawPublicKey65;
        $publicKeyParam = "\xa1" . $this->asn1Length(strlen($bitString)) . $bitString;

        $inner = $version . $privateKeyOctet . $curveParam . $publicKeyParam;
        $der = "\x30" . $this->asn1Length(strlen($inner)) . $inner;

        return "-----BEGIN EC PRIVATE KEY-----\n" . chunk_split(base64_encode($der), 64, "\n") . "-----END EC PRIVATE KEY-----\n";
    }

    /**
     * Membangun PEM "PUBLIC KEY" (SubjectPublicKeyInfo, X.509) dari titik publik
     * EC mentah tidak terkompresi (65 byte).
     */
    protected function ecPublicPemFromRaw(string $rawPublicKey65): string
    {
        if (strlen($rawPublicKey65) !== 65) {
            throw new RuntimeException('Panjang kunci publik EC harus 65 byte.');
        }

        $idEcPublicKeyOid = "\x06\x07\x2a\x86\x48\xce\x3d\x02\x01"; // OID 1.2.840.10045.2.1 (id-ecPublicKey)
        $curveOid = "\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07"; // OID 1.2.840.10045.3.1.7 (prime256v1)
        $algorithmId = $idEcPublicKeyOid . $curveOid;
        $algorithmSeq = "\x30" . $this->asn1Length(strlen($algorithmId)) . $algorithmId;
        $bitString = "\x03" . $this->asn1Length(1 + strlen($rawPublicKey65)) . "\x00" . $rawPublicKey65;

        $inner = $algorithmSeq . $bitString;
        $der = "\x30" . $this->asn1Length(strlen($inner)) . $inner;

        return "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($der), 64, "\n") . "-----END PUBLIC KEY-----\n";
    }

    /**
     * Mengubah panjang DER ASN.1 menjadi encoding BER/DER length byte(s)
     */
    protected function asn1Length(int $length): string
    {
        if ($length < 128) {
            return chr($length);
        }

        $bytes = '';
        while ($length > 0) {
            $bytes = chr($length & 0xff) . $bytes;
            $length >>= 8;
        }

        return chr(0x80 | strlen($bytes)) . $bytes;
    }

    /**
     * Membaca panjang (length) ASN.1 BER/DER pada posisi $offset, mengembalikan [panjang, offsetBaru]
     */
    protected function asn1ReadLength(string $der, int $offset): array
    {
        $first = ord($der[$offset]);
        $offset++;

        if ($first < 128) {
            return [$first, $offset];
        }

        $numBytes = $first & 0x7f;
        $length = 0;
        for ($i = 0; $i < $numBytes; $i++) {
            $length = ($length << 8) | ord($der[$offset]);
            $offset++;
        }

        return [$length, $offset];
    }

    /**
     * Mengubah tanda tangan ECDSA format DER (hasil openssl_sign) menjadi format
     * raw r||s berukuran tetap sesuai yang disyaratkan JWS ES256 (RFC 7518).
     */
    protected function derSignatureToRaw(string $der, int $partLength = 32): string
    {
        $offset = 0;
        if (ord($der[$offset]) !== 0x30) {
            throw new RuntimeException('Format tanda tangan DER tidak valid.');
        }
        $offset++;
        [, $offset] = $this->asn1ReadLength($der, $offset);

        if (ord($der[$offset]) !== 0x02) {
            throw new RuntimeException('Format tanda tangan DER tidak valid (r).');
        }
        $offset++;
        [$rLength, $offset] = $this->asn1ReadLength($der, $offset);
        $r = substr($der, $offset, $rLength);
        $offset += $rLength;

        if (ord($der[$offset]) !== 0x02) {
            throw new RuntimeException('Format tanda tangan DER tidak valid (s).');
        }
        $offset++;
        [$sLength, $offset] = $this->asn1ReadLength($der, $offset);
        $s = substr($der, $offset, $sLength);

        $r = str_pad(ltrim($r, "\x00"), $partLength, "\x00", STR_PAD_LEFT);
        $s = str_pad(ltrim($s, "\x00"), $partLength, "\x00", STR_PAD_LEFT);

        return $r . $s;
    }

    /**
     * HKDF-Extract (RFC 5869)
     */
    protected function hkdfExtract(string $salt, string $inputKeyingMaterial): string
    {
        return hash_hmac('sha256', $inputKeyingMaterial, $salt, true);
    }

    /**
     * HKDF-Expand (RFC 5869)
     */
    protected function hkdfExpand(string $pseudoRandomKey, string $info, int $length): string
    {
        $t = '';
        $outputKeyingMaterial = '';
        $counter = 1;

        while (strlen($outputKeyingMaterial) < $length) {
            $t = hash_hmac('sha256', $t . $info . chr($counter), $pseudoRandomKey, true);
            $outputKeyingMaterial .= $t;
            $counter++;
        }

        return substr($outputKeyingMaterial, 0, $length);
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return (string) base64_decode(strtr($data, '-_', '+/'));
    }
}

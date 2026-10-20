<?php

namespace App\Console\Commands;

use App\Services\WebPushService;
use Illuminate\Console\Command;

/**
 * Perintah artisan untuk membuat pasangan kunci VAPID (P-256) yang
 * dipakai untuk menandatangani dan mengenkripsi Web Push Notification.
 */
class GenerateVapidKeys extends Command
{
    protected $signature = 'vapid:generate';

    protected $description = 'Membuat pasangan kunci VAPID baru untuk Web Push Notification (PWA)';

    public function handle(WebPushService $webPushService): int
    {
        $keys = $webPushService->generateVapidKeypair();

        $this->components->info('Pasangan kunci VAPID berhasil dibuat.');

        $this->line('');
        $this->line('Tambahkan baris berikut ke berkas .env Anda:');
        $this->line('');
        $this->line("VAPID_PUBLIC_KEY={$keys['publicKey']}");
        $this->line("VAPID_PRIVATE_KEY={$keys['privateKey']}");
        $this->line('VAPID_SUBJECT=mailto:admin@' . parse_url(config('app.url'), PHP_URL_HOST));
        $this->line('');
        $this->components->warn('Jaga kerahasiaan VAPID_PRIVATE_KEY — jangan pernah membagikannya atau mengirimkannya ke klien/browser.');

        return self::SUCCESS;
    }
}

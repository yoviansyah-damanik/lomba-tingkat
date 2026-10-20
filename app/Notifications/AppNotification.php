<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * Notifikasi dalam aplikasi (database) yang tampil pada dropdown lonceng dasbor
 */
class AppNotification extends Notification
{
    public function __construct(protected array $payload) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->payload['title'] ?? 'Pemberitahuan LT III 2026',
            'body' => $this->payload['body'] ?? '',
            'url' => $this->payload['url'] ?? null,
            'tag' => $this->payload['tag'] ?? null,
        ];
    }

    /**
     * Ikon Flux untuk notifikasi berdasarkan awalan tag
     */
    public static function iconFor(?string $tag): string
    {
        $tag = (string) $tag;

        return match (true) {
            str_starts_with($tag, 'lt3-registration') => 'building-library',
            str_starts_with($tag, 'lt3-verification') => 'shield-check',
            str_starts_with($tag, 'lt3-reminder') => 'calendar',
            default => 'bell',
        };
    }
}

<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\AppNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

/**
 * Service pengiriman notifikasi dalam aplikasi kepada pengguna, peran, atau seluruh akun aktif
 */
class InAppNotificationService
{
    /**
     * Kirim ke satu atau beberapa pengguna
     */
    public function toUsers(User|Collection $users, array $payload): int
    {
        $users = $users instanceof User ? collect([$users]) : $users;

        if ($users->isEmpty()) {
            return 0;
        }

        Notification::send($users, new AppNotification($payload));

        return $users->count();
    }

    /**
     * Kirim berdasarkan sasaran: null (semua akun aktif), "guest" (tidak ada akun), atau nama peran
     */
    public function toTarget(?string $targetRole, array $payload): int
    {
        if ($targetRole === 'guest') {
            return 0;
        }

        $query = User::query()->where('is_active', true);

        if ($targetRole) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $targetRole));
        }

        return $this->toUsers($query->get(), $payload);
    }
}

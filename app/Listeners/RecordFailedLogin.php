<?php

namespace App\Listeners;

use App\Models\LoginHistory;
use Illuminate\Auth\Events\Failed;

/**
 * Mencatat percobaan login gagal pada jalur autentikasi Fortify
 */
class RecordFailedLogin
{
    public function handle(Failed $event): void
    {
        LoginHistory::record($event->user?->getAuthIdentifier(), $event->credentials['username'] ?? null, 'failed');
    }
}

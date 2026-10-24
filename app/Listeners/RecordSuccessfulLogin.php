<?php

namespace App\Listeners;

use App\Models\LoginHistory;
use Illuminate\Auth\Events\Login;

/**
 * Mencatat setiap login berhasil (formulir Livewire, Fortify, maupun login otomatis setelah registrasi)
 */
class RecordSuccessfulLogin
{
    public function handle(Login $event): void
    {
        LoginHistory::record($event->user->getAuthIdentifier(), $event->user->username ?? null, 'success');
    }
}

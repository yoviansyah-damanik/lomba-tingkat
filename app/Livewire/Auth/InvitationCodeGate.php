<?php

namespace App\Livewire\Auth;

use App\Models\Event;
use App\Models\RegistrationInvitation;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Gerbang registrasi pangkalan: pendaftar wajib memasukkan kode undangan dari panitia
 */
#[Layout('layouts.public', ['title' => 'Registrasi Akun Pangkalan Gugus Depan'])]
#[Title('Registrasi Akun Pangkalan Gugus Depan')]
class InvitationCodeGate extends Component
{
    /** Kunci sesi berisi id undangan dan waktu verifikasi kode */
    public const SESSION_KEY = 'registration_invitation';

    public string $code = '';

    public function verifyCode()
    {
        $this->validate(
            ['code' => ['required', 'string', 'max:32']],
            ['code.required' => 'Kode undangan wajib diisi.'],
        );

        if ($seconds = $this->throttleSeconds()) {
            $this->addError('code', "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.");

            return;
        }

        $this->hitThrottle();

        $invitation = RegistrationInvitation::findByCode($this->code);

        if (! $invitation || ! $invitation->isUsable()) {
            $this->addError('code', 'Kode undangan tidak valid atau sudah tidak berlaku. Hubungi panitia untuk memastikan kode gugus depan Anda.');

            return;
        }

        session([self::SESSION_KEY => ['id' => $invitation->id, 'at' => time()]]);

        return $this->redirect(route('register.scout-group.form'), navigate: true);
    }

    /**
     * Sisa detik pemblokiran bila batas percobaan per menit atau per hari terlampaui
     */
    private function throttleSeconds(): int
    {
        foreach ($this->throttleKeys() as $key => $max) {
            if (RateLimiter::tooManyAttempts($key, $max)) {
                return RateLimiter::availableIn($key);
            }
        }

        return 0;
    }

    private function hitThrottle(): void
    {
        $keys = $this->throttleKeys();

        RateLimiter::hit(array_key_first($keys), 60);
        RateLimiter::hit(array_key_last($keys), 86400);
    }

    /**
     * @return array<string, int> kunci limiter => batas percobaan (5 per menit dan 20 per hari per IP)
     */
    private function throttleKeys(): array
    {
        $ip = request()->ip();

        return [
            "register-code:minute:{$ip}" => 5,
            "register-code:day:{$ip}" => 20,
        ];
    }

    public function render()
    {
        $event = Event::where('is_active', true)->first();

        return view('livewire.auth.invitation-code-gate', [
            'event' => $event,
            'isRegistrationOpen' => $event ? $event->isRegistrationOpen() : config('registration.is_enabled', true),
        ]);
    }
}

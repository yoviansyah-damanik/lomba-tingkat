<?php

namespace App\Livewire\Auth;

use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Livewire\Component;

/**
 * Formulir login Livewire: validasi, pembatasan percobaan, dan tantangan 2FA (setara alur Fortify)
 */
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    protected function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    protected function messages(): array
    {
        return [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ];
    }

    public function login()
    {
        $this->validate();

        $throttleKey = Str::transliterate(Str::lower($this->email).'|'.request()->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->toast('danger', 'Terlalu Banyak Percobaan', "Coba lagi dalam {$seconds} detik.");

            return;
        }

        $provider = Auth::guard(config('fortify.guard'))->getProvider();
        $user = $provider->retrieveByCredentials([Fortify::username() => Str::lower($this->email)]);

        if (! $user || ! $provider->validateCredentials($user, ['password' => $this->password])) {
            RateLimiter::hit($throttleKey);
            $this->reset('password');
            $this->toast('danger', 'Gagal Masuk', 'Email atau kata sandi tidak sesuai.');

            return;
        }

        RateLimiter::clear($throttleKey);

        if ($this->requiresTwoFactor($user)) {
            session()->put(['login.id' => $user->getKey(), 'login.remember' => $this->remember]);

            return $this->redirectRoute('two-factor.login');
        }

        Auth::guard(config('fortify.guard'))->login($user, $this->remember);
        session()->regenerate();

        return $this->redirectIntended(default: config('fortify.home'));
    }

    protected function requiresTwoFactor($user): bool
    {
        if (! $user->two_factor_secret) {
            return false;
        }

        return ! Fortify::confirmsTwoFactorAuthentication() || $user->two_factor_confirmed_at !== null;
    }

    protected function toast(string $variant, string $heading, string $text): void
    {
        Flux::toast(variant: $variant, heading: $heading, text: $text);
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}

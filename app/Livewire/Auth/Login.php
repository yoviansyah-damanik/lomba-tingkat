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
    public string $username = '';

    public string $password = '';

    public bool $remember = false;

    protected function rules(): array
    {
        return [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    protected function messages(): array
    {
        return [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ];
    }

    public function login()
    {
        $this->validate();

        $throttleKey = Str::transliterate(Str::lower(trim($this->username)).'|'.request()->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->toast('danger', 'Terlalu Banyak Percobaan', "Coba lagi dalam {$seconds} detik.");

            return;
        }

        $provider = Auth::guard(config('fortify.guard'))->getProvider();
        $user = $provider->retrieveByCredentials([Fortify::username() => trim($this->username)]);

        // Basis data membandingkan tanpa membedakan huruf besar/kecil, maka username dicocokkan persis di sini
        $exactUsername = $user && hash_equals((string) $user->username, trim($this->username));

        if (! $exactUsername || ! $provider->validateCredentials($user, ['password' => $this->password])) {
            RateLimiter::hit($throttleKey);
            \App\Models\LoginHistory::record($exactUsername ? $user->getKey() : null, trim($this->username), 'failed');
            $this->reset('password');
            $this->toast('danger', 'Gagal Masuk', 'Username atau kata sandi tidak sesuai.');

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

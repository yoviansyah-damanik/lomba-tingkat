<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        // Username dicocokkan persis (case-sensitive) karena basis data membandingkan tanpa membedakan huruf
        Fortify::authenticateUsing(function (Request $request) {
            $username = trim((string) $request->input(Fortify::username()));
            $user = User::where(Fortify::username(), $username)->first();

            if ($user && hash_equals((string) $user->username, $username) && Hash::check((string) $request->input('password'), $user->password)) {
                return $user;
            }

            return null;
        });
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(fn () => view('pages::auth.login'));
        Fortify::twoFactorChallengeView(fn () => view('pages::auth.two-factor-challenge'));
        Fortify::confirmPasswordView(fn () => view('pages::auth.confirm-password'));
        Fortify::registerView(function () {
            $event = \App\Models\Event::where('is_active', true)->first();
            $isRegistrationOpen = $event ? $event->isRegistrationOpen() : config('registration.is_enabled', true);

            return view('pages.auth.register-scout-group', [
                'kwarrans' => [
                    'Kwarran Padangsidimpuan Utara',
                    'Kwarran Padangsidimpuan Selatan',
                    'Kwarran Padangsidimpuan Batunadua',
                    'Kwarran Padangsidimpuan Hutaimbaru',
                    'Kwarran Padangsidimpuan Tenggara',
                    'Kwarran Padangsidimpuan Angkola Julu',
                ],
                'event' => $event,
                'isRegistrationOpen' => $isRegistrationOpen,
            ]);
        });
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('passkeys', function (Request $request) {
            $credentialId = $request->input('credential.id');

            return Limit::perMinute(10)->by(
                ($credentialId ?: $request->session()->getId()).'|'.$request->ip(),
            );
        });
    }
}

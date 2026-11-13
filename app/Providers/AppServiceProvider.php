<?php

namespace App\Providers;

use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
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
        $this->configureDefaults();
        $this->registerAuthorizationGates();
    }

    /**
     * Mendaftarkan gerbang otorisasi peran (Gates)
     */
    protected function registerAuthorizationGates(): void
    {
        // Gerbang khusus Administrator penuh
        Gate::define('admin', fn (User $user): bool => $user->isAdmin());

        // Gerbang Panitia (monitoring operasional)
        Gate::define('committee', fn (User $user): bool => $user->isCommittee());

        // Gerbang Juri (penilaian)
        Gate::define('judge', fn (User $user): bool => $user->isJudge());

        // Gerbang Gugus Depan
        Gate::define('scout_group', fn (User $user): bool => $user->isScoutGroup());

        // Gerbang Peserta
        Gate::define('participant', fn (User $user): bool => $user->isParticipant());

        // Otorisasi spesifik: Verifikasi pendaftaran hanya oleh Admin
        Gate::define('verify-registrations', fn (User $user): bool => $user->isAdmin());

        // Otorisasi spesifik: Eksekusi scanner absensi pos kegiatan hanya oleh Admin
        Gate::define('execute-pos-scanning', fn (User $user): bool => $user->isAdmin());

        // Otorisasi spesifik: Monitoring operasional lapangan oleh Admin & Panitia
        Gate::define('monitor-operations', fn (User $user): bool => $user->isAdmin() || $user->isCommittee());
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);
        Carbon::setLocale('id');
        CarbonImmutable::setLocale('id');
        \Illuminate\Support\Carbon::setLocale('id');

        // DB::prohibitDestructiveCommands(
        //     app()->isProduction(),
        // );

        Password::defaults(
            fn (): Password => app()->isProduction()
                ? Password::min(8)
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
                : Password::min(8),
        );
    }
}

<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
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
        \Illuminate\Support\Facades\Gate::define('admin', fn (\App\Models\User $user): bool => $user->isAdmin());

        // Gerbang Panitia (monitoring operasional)
        \Illuminate\Support\Facades\Gate::define('committee', fn (\App\Models\User $user): bool => $user->isCommittee());

        // Gerbang Juri (penilaian)
        \Illuminate\Support\Facades\Gate::define('judge', fn (\App\Models\User $user): bool => $user->isJudge());

        // Gerbang Gugus Depan
        \Illuminate\Support\Facades\Gate::define('scout_group', fn (\App\Models\User $user): bool => $user->isScoutGroup());

        // Gerbang Peserta
        \Illuminate\Support\Facades\Gate::define('participant', fn (\App\Models\User $user): bool => $user->isParticipant());

        // Otorisasi spesifik: Verifikasi pendaftaran hanya oleh Admin
        \Illuminate\Support\Facades\Gate::define('verify-registrations', fn (\App\Models\User $user): bool => $user->isAdmin());

        // Otorisasi spesifik: Eksekusi scanner absensi pos kegiatan hanya oleh Admin
        \Illuminate\Support\Facades\Gate::define('execute-pos-scanning', fn (\App\Models\User $user): bool => $user->isAdmin());

        // Otorisasi spesifik: Monitoring operasional lapangan oleh Admin & Panitia
        \Illuminate\Support\Facades\Gate::define('monitor-operations', fn (\App\Models\User $user): bool => $user->isAdmin() || $user->isCommittee());
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);
        \Carbon\Carbon::setLocale('id');
        \Carbon\CarbonImmutable::setLocale('id');
        \Illuminate\Support\Carbon::setLocale('id');

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}

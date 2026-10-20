<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property string $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password', 'phone_number', 'avatar_path', 'is_active', 'scout_id', 'institution', 'position', 'id_number', 'qr_token', 'qr_generated_at'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuids, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'qr_generated_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (User $user) {
            if (empty($user->id_number)) {
                do {
                    $code = 'ID-' . strtoupper(Str::random(8));
                } while (static::where('id_number', $code)->exists());
                $user->id_number = $code;
            }

            if (empty($user->qr_token)) {
                $user->qr_token = (string) Str::uuid();
                $user->qr_generated_at = now();
            }
        });
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }

    /**
     * Relasi many-to-many ke peran (Role)
     */
    public function roles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Role::class)->using(RoleUser::class)->withTimestamps();
    }

    /**
     * Memeriksa apakah pengguna memiliki peran tertentu
     */
    public function hasRole(string|\App\Enums\RoleEnum $role): bool
    {
        $roleName = $role instanceof \App\Enums\RoleEnum ? $role->value : $role;

        return $this->roles->contains('name', $roleName);
    }

    /**
     * Memeriksa apakah pengguna memiliki salah satu dari daftar peran
     */
    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Menetapkan peran ke pengguna
     */
    public function assignRole(string|\App\Enums\RoleEnum $role): void
    {
        $roleName = $role instanceof \App\Enums\RoleEnum ? $role->value : $role;
        $roleModel = Role::firstOrCreate(
            ['name' => $roleName],
            ['label' => ucfirst(str_replace('_', ' ', $roleName))]
        );

        $this->roles()->syncWithoutDetaching([$roleModel->id]);
    }

    /**
     * Melepas peran dari pengguna
     */
    public function removeRole(string|\App\Enums\RoleEnum $role): void
    {
        $roleName = $role instanceof \App\Enums\RoleEnum ? $role->value : $role;
        $roleModel = Role::where('name', $roleName)->first();

        if ($roleModel) {
            $this->roles()->detach($roleModel->id);
        }
    }

    /**
     * Helper cepat pengecekan role Administrator
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(\App\Enums\RoleEnum::Admin);
    }

    /**
     * Helper cepat pengecekan role Panitia
     */
    public function isCommittee(): bool
    {
        return $this->hasRole(\App\Enums\RoleEnum::Committee);
    }

    /**
     * Helper cepat pengecekan role Juri
     */
    public function isJudge(): bool
    {
        return $this->hasRole(\App\Enums\RoleEnum::Judge);
    }

    /**
     * Helper cepat pengecekan rangkap Panitia + Juri
     */
    public function isCommitteeAndJudge(): bool
    {
        return $this->hasRole(\App\Enums\RoleEnum::Committee) && $this->hasRole(\App\Enums\RoleEnum::Judge);
    }

    /**
     * Helper cepat pengecekan akun Gugus Depan
     */
    public function isScoutGroup(): bool
    {
        return $this->hasRole(\App\Enums\RoleEnum::ScoutGroup);
    }

    /**
     * Helper cepat pengecekan akun Peserta
     */
    public function isParticipant(): bool
    {
        return $this->hasRole(\App\Enums\RoleEnum::Participant);
    }

    /**
     * Relasi penugasan juri ke jadwal sesi lomba
     */
    public function judgeAssignments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(JudgeAssignment::class, 'user_id');
    }

    /**
     * Relasi ke pangkalan Gugus Depan
     */
    public function scoutGroup(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ScoutGroup::class, 'user_id');
    }

    /**
     * Relasi penilaian juri
     */
    public function assessments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Assessment::class, 'judge_id');
    }

    /**
     * Relasi langganan Web Push Notification
     */
    public function pushSubscriptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PushSubscription::class);
    }

    /**
     * Mendapatkan label peran pengguna yang login
     */
    public function roleLabel(): string
    {
        if ($this->isAdmin()) {
            return 'Administrator';
        }

        if ($this->isCommitteeAndJudge()) {
            return 'Panitia & Juri';
        }

        if ($this->isCommittee()) {
            return 'Panitia';
        }

        if ($this->isJudge()) {
            return 'Juri';
        }

        if ($this->isScoutGroup()) {
            return 'Gugus Depan';
        }

        if ($this->isParticipant()) {
            return 'Peserta';
        }

        $labels = $this->roles->pluck('label')->filter()->values();

        if ($labels->isNotEmpty()) {
            return $labels->join(', ');
        }

        return 'Pengguna';
    }
}

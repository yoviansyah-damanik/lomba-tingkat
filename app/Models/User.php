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
 * @property string $username
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'username', 'password', 'must_change_password', 'phone_number', 'avatar_path', 'is_active', 'scout_id', 'institution', 'position', 'committee_position', 'id_number', 'qr_token', 'qr_generated_at', 'participant_id'])]
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
            'password' => 'hashed',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
            'qr_generated_at' => 'datetime',
        ];
    }

    /**
     * Menghasilkan ID unik resmi Aparatur / Pengguna Sistem: A-{6 digit acak}
     */
    public static function generateOfficialIdNumber(): string
    {
        do {
            $digits = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $code = 'A-' . $digits;
        } while (
            static::where('id_number', $code)->exists()
            || Participant::where('id_number', $code)->exists()
            || Chaperone::where('id_number', $code)->exists()
            || ScoutGroup::where('registration_code', $code)->exists()
        );

        return $code;
    }

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (User $user) {
            // Username dipertahankan persis (case-sensitive), hanya spasi tepi dibuang
            if ($user->username !== null) {
                $user->username = trim($user->username);
            }
        });

        static::creating(function (User $user) {
            if (empty($user->id_number)) {
                $user->id_number = static::generateOfficialIdNumber();
            }

            // Username aparatur/peserta/bindamping otomatis mengikuti ID
            if (empty($user->username)) {
                $user->username = $user->id_number;
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

        // Superadmin mewarisi seluruh wewenang Administrator
        if ($roleName === \App\Enums\RoleEnum::Admin->value && $this->roles->contains('name', \App\Enums\RoleEnum::Superadmin->value)) {
            return true;
        }

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
     * Helper cepat pengecekan role Superadmin
     */
    public function isSuperadmin(): bool
    {
        return $this->hasRole(\App\Enums\RoleEnum::Superadmin);
    }

    /**
     * Helper cepat pengecekan role Administrator (termasuk Superadmin)
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(\App\Enums\RoleEnum::Admin);
    }

    /**
     * Pengecekan penunjukan Administrator secara eksplisit (tanpa pewarisan dari Superadmin)
     */
    public function isAdministrator(): bool
    {
        return $this->roles->contains('name', \App\Enums\RoleEnum::Admin->value);
    }

    /**
     * ID untuk kolom pelaku pada data non-audit (penulis, pembuat): null bila bertindak sebagai Administrator
     */
    public function recordableId(): ?string
    {
        return $this->isAdministrator() ? null : $this->id;
    }

    /**
     * Administrator yang tidak merangkap Juri: hanya memantau, tidak menilai
     */
    public function isAdminOnly(): bool
    {
        return $this->isAdmin() && ! $this->isJudge();
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
     * Teks jabatan pada ID Card aparatur: Juri saja "Dewan Juri", Panitia memakai posisi kepanitiaan,
     * rangkap Panitia + Juri "Posisi Kepanitiaan / Dewan Juri"
     */
    public function cardPositionLabel(): string
    {
        $committee = $this->committee_position ?: 'Panitia Pelaksana';

        return match (true) {
            $this->isCommittee() && $this->isJudge() => $committee.' / Dewan Juri',
            $this->isJudge() => 'Dewan Juri',
            $this->isCommittee() => $committee,
            default => $this->position ?: $this->roleLabel(),
        };
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
     * Aturan validasi username (huruf, angka, titik, strip, garis bawah)
     */
    public static function usernameRules(?string $ignoreId = null): array
    {
        return [
            'required',
            'string',
            'regex:/^[A-Za-z0-9._-]+$/',
            'min:3',
            'max:50',
            \Illuminate\Validation\Rule::unique('users', 'username')->ignore($ignoreId),
        ];
    }

    /**
     * Pesan validasi username (bahasa Indonesia)
     */
    public static function usernameMessages(): array
    {
        return [
            'username.required' => 'Username wajib diisi.',
            'username.regex' => 'Username hanya boleh berisi huruf, angka, titik, strip, dan garis bawah (tanpa spasi).',
            'username.min' => 'Username minimal 3 karakter.',
            'username.max' => 'Username maksimal 50 karakter.',
            'username.unique' => 'Username ini telah digunakan. Silakan pilih username lain.',
        ];
    }

    /**
     * Nama pengguna untuk tampilan WebAuthn (passkey)
     */
    public function getPasskeyUsername(): string
    {
        return $this->username;
    }

    /**
     * Helper cepat pengecekan akun Bindamping
     */
    public function isChaperone(): bool
    {
        return $this->hasRole(\App\Enums\RoleEnum::Chaperone);
    }

    /**
     * Akun peserta/bindamping dikelola otomatis dari data gudep terverifikasi,
     * sehingga profil dan kata sandinya tidak dapat diubah oleh pemiliknya sendiri
     */
    public function isManagedAccount(): bool
    {
        return $this->isParticipant() || $this->isChaperone();
    }

    /**
     * Data peserta yang ditautkan ke akun peserta
     */
    public function participant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Participant::class, 'participant_id');
    }

    /**
     * Data bindamping yang ditautkan ke akun bindamping
     */
    public function chaperone(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Chaperone::class, 'user_id');
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
        if ($this->isSuperadmin()) {
            return 'Superadmin';
        }

        if ($this->isAdmin()) {
            $duties = array_filter([
                $this->isCommittee() ? 'Panitia' : null,
                $this->isJudge() ? 'Juri' : null,
            ]);

            return $duties === [] ? 'Administrator' : implode(', ', $duties).' & Administrator';
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

        if ($this->isChaperone()) {
            return 'Bindamping';
        }

        $labels = $this->roles->pluck('label')->filter()->values();

        if ($labels->isNotEmpty()) {
            return $labels->join(', ');
        }

        return 'Pengguna';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Undangan registrasi: gugus depan yang diizinkan mendaftar beserta kode sekali pakainya
 */
class RegistrationInvitation extends Model
{
    use HasUuids;

    /** Tanpa 0/O/1/I agar kode mudah dibaca dan diketik */
    private const CODE_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public const CODE_LENGTH = 8;

    protected $fillable = [
        'code',
        'group_number',
        'school_name',
        'kwarran_name',
        'is_active',
        'created_by',
        'used_at',
        'scout_group_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'used_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (RegistrationInvitation $invitation) {
            if (empty($invitation->code)) {
                $invitation->code = static::generateCode();
            }
        });
    }

    public function scoutGroup(): BelongsTo
    {
        return $this->belongsTo(ScoutGroup::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Undangan yang masih dapat dipakai: saklar On dan belum terpakai
     */
    public function scopeUsable(Builder $query): Builder
    {
        return $query->where('is_active', true)->whereNull('used_at');
    }

    public function isUsable(): bool
    {
        return $this->is_active && $this->used_at === null;
    }

    /**
     * Cari undangan dari kode yang diketik pengguna (tanpa membedakan huruf besar/kecil dan spasi)
     */
    public static function findByCode(string $input): ?self
    {
        $code = Str::upper(preg_replace('/[\s-]+/', '', $input));

        return $code === '' ? null : static::where('code', $code)->first();
    }

    public function markUsed(ScoutGroup $scoutGroup): void
    {
        $this->forceFill([
            'used_at' => now(),
            'scout_group_id' => $scoutGroup->id,
        ])->save();
    }

    public static function generateCode(): string
    {
        do {
            $code = '';
            for ($i = 0; $i < self::CODE_LENGTH; $i++) {
                $code .= self::CODE_ALPHABET[random_int(0, strlen(self::CODE_ALPHABET) - 1)];
            }
        } while (static::where('code', $code)->exists());

        return $code;
    }

    /**
     * Kode tampil dikelompokkan, misal ABCD-EFGH
     */
    public function formattedCode(): string
    {
        return substr($this->code, 0, 4).'-'.substr($this->code, 4);
    }
}

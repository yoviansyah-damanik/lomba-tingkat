<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Riwayat login pengguna (berhasil atau gagal)
 */
class LoginHistory extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = ['user_id', 'username', 'status', 'ip_address', 'user_agent', 'created_at'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Catat satu percobaan login beserta IP dan perangkat dari permintaan saat ini
     */
    public static function record(?string $userId, ?string $username, string $status): self
    {
        return static::create([
            'user_id' => $userId,
            'username' => $username,
            'status' => $status,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'created_at' => now(),
        ]);
    }
}

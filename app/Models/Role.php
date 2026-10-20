<?php

namespace App\Models;

use App\Enums\RoleEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Model peran sistem (Role)
 */
class Role extends Model
{
    use HasFactory, HasUuids;

    /**
     * Kolom yang dapat diisi secara massal
     */
    protected $fillable = [
        'name',
        'label',
        'description',
    ];

    /**
     * Casting tipe data atribut
     */
    protected function casts(): array
    {
        return [
            'name' => 'string',
        ];
    }

    /**
     * Relasi ke pengguna yang memiliki peran ini
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->using(RoleUser::class)->withTimestamps();
    }
}

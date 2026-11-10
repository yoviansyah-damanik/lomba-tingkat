<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model template dan tata letak sertifikat kegiatan
 */
class CertificateTemplate extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'event_id',
        'role',
        'template_path',
        'layout',
    ];

    protected function casts(): array
    {
        return [
            'layout' => 'array',
        ];
    }

    /**
     * Relasi ke kegiatan induk
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}


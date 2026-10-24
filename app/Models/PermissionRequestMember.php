<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Peserta atau bindamping yang tercantum pada permohonan izin
 */
class PermissionRequestMember extends Model
{
    use HasUuids;

    protected $fillable = ['permission_request_id', 'participant_id', 'chaperone_id', 'returned_at'];

    protected function casts(): array
    {
        return ['returned_at' => 'datetime'];
    }

    public function permissionRequest(): BelongsTo
    {
        return $this->belongsTo(PermissionRequest::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function chaperone(): BelongsTo
    {
        return $this->belongsTo(Chaperone::class);
    }

    public function displayName(): string
    {
        return $this->participant?->full_name ?? $this->chaperone?->full_name ?? '-';
    }
}

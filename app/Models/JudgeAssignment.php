<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JudgeAssignment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'competition_schedule_id',
        'user_id',
        'role',
        'is_confirmed',
        'notes',
    ];

    protected $casts = [
        'is_confirmed' => 'boolean',
    ];

    /**
     * Relasi ke jadwal sesi lomba
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(CompetitionSchedule::class, 'competition_schedule_id');
    }

    /**
     * Relasi ke user juri
     */
    public function judge(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Label role juri
     */
    public function roleLabel(): string
    {
        return match ($this->role) {
            'lead_judge' => 'Ketua Juri',
            'scoring_judge' => 'Juri Penilai',
            'companion_judge' => 'Juri Pendamping',
            default => 'Juri',
        };
    }
}

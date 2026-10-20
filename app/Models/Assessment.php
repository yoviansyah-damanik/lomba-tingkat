<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model penilaian mandiri juri per regu dan jadwal sesi lomba
 */
class Assessment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'competition_id',
        'competition_schedule_id',
        'patrol_id',
        'participant_id',
        'judge_id',
        'criteria_scores',
        'raw_total_score',
        'final_score',
        'status',
        'notes',
        'is_locked',
        'finalized_at',
    ];

    protected function casts(): array
    {
        return [
            'criteria_scores' => 'array',
            'raw_total_score' => 'decimal:2',
            'final_score' => 'decimal:2',
            'is_locked' => 'boolean',
            'finalized_at' => 'datetime',
        ];
    }

    /**
     * Cabang mata lomba yang dinilai
     */
    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    /**
     * Jadwal sesi lomba yang dinilai
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(CompetitionSchedule::class, 'competition_schedule_id');
    }

    /**
     * Regu penggalang yang dinilai
     */
    public function patrol(): BelongsTo
    {
        return $this->belongsTo(Patrol::class);
    }

    /**
     * Peserta individual (opsional jika format perorangan)
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    /**
     * User juri yang memberikan nilai
     */
    public function judge(): BelongsTo
    {
        return $this->belongsTo(User::class, 'judge_id');
    }

    /**
     * Cek apakah penilaian berstatus draf
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Cek apakah penilaian telah difinalisasi
     */
    public function isFinalized(): bool
    {
        return $this->status === 'finalized';
    }

    /**
     * Cek apakah nilai terkunci
     */
    public function isLocked(): bool
    {
        return (bool) $this->is_locked;
    }
}

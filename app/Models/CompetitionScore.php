<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model rekapitulasi penilaian lomba regu
 */
class CompetitionScore extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'competition_id',
        'patrol_id',
        'participant_id',
        'judge_id',
        'competition_schedule_id',
        'final_score',
        'criteria_scores',
        'predicate',
        'rank',
        'notes',
        'is_locked',
    ];

    protected function casts(): array
    {
        return [
            'criteria_scores' => 'array',
            'final_score' => 'decimal:2',
            'rank' => 'integer',
            'is_locked' => 'boolean',
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
     * Regu penggalang yang dinilai
     */
    public function patrol(): BelongsTo
    {
        return $this->belongsTo(Patrol::class);
    }

    /**
     * Peserta individual (jika format lomba perorangan)
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    /**
     * Juri penilai
     */
    public function judge(): BelongsTo
    {
        return $this->belongsTo(User::class, 'judge_id');
    }

    /**
     * Jadwal pelaksanaan lomba terkait
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(CompetitionSchedule::class, 'competition_schedule_id');
    }

    /**
     * Label predikat prestasi resmi
     */
    public function predicateLabel(): string
    {
        return match ($this->predicate) {
            'prestasi_tinggi' => 'Prestasi Tinggi',
            'prestasi_baik' => 'Prestasi Baik',
            'prestasi_cukup' => 'Prestasi Cukup',
            'partisipasi' => 'Peserta Terdaftar',
            default => '-',
        };
    }

    /**
     * Poin klasemen berdasarkan medali / predikat
     */
    public function predicatePoints(): int
    {
        return match ($this->predicate) {
            'prestasi_tinggi' => 5,
            'prestasi_baik' => 3,
            'prestasi_cukup' => 1,
            default => 0,
        };
    }

    /**
     * Badge visual predikat
     */
    public function predicateBadgeClasses(): string
    {
        return match ($this->predicate) {
            'prestasi_tinggi' => 'bg-amber-500/15 text-amber-500 border-amber-500/30',
            'prestasi_baik' => 'bg-slate-400/15 text-slate-300 border-slate-400/30',
            'prestasi_cukup' => 'bg-amber-700/15 text-amber-600 border-amber-700/30',
            default => 'bg-zinc-500/15 text-zinc-400 border-zinc-500/30',
        };
    }
}

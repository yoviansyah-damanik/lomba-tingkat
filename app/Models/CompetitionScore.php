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
     * Nilai bobot (poin) per peringkat pada satu lomba; peringkat 21 ke atas memakai bobot terendah.
     * Tabel: 1=100, 2=80, 3=70, 4=65, 5=60, 6=55, 7=54, 8=53, 9=52, 10=51, 11-20=50, 21-33=45.
     */
    public static function rankWeight(?int $rank, ?string $predicate = null): int
    {
        if ($rank === null || $rank < 1) {
            return match ($predicate) {
                'prestasi_tinggi' => 100,
                'prestasi_baik' => 80,
                'prestasi_cukup' => 70,
                default => 0,
            };
        }

        return match (true) {
            $rank === 1 => 100,
            $rank === 2 => 80,
            $rank === 3 => 70,
            $rank === 4 => 65,
            $rank === 5 => 60,
            $rank === 6 => 55,
            $rank === 7 => 54,
            $rank === 8 => 53,
            $rank === 9 => 52,
            $rank === 10 => 51,
            $rank <= 20 => 50,
            $rank <= 33 => 45,
            default => 0,
        };
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
     * Predikat prestasi hanya untuk peringkat 1-3 (Tinggi, Baik, Cukup); peringkat 4 dan seterusnya tanpa predikat
     */
    public function hasAchievement(): bool
    {
        return in_array($this->predicate, ['prestasi_tinggi', 'prestasi_baik', 'prestasi_cukup'], true);
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
            default => '-',
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

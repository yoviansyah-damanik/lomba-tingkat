<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model cabang mata lomba
 */
class Competition extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'event_id',
        'code',
        'title',
        'category',
        'participant_type',
        'min_participants',
        'max_participants',
        'description',
        'guidelines_pdf_path',
        'guidelines_text',
        'scoring_formula',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'min_participants' => 'integer',
            'max_participants' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    /**
     * Scope untuk mata lomba yang aktif dipublikasikan
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)->orderBy('code');
    }

    /**
     * Relasi ke kegiatan induk
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Relasi ke seluruh jadwal sesi lomba
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(CompetitionSchedule::class);
    }

    /**
     * Relasi ke kriteria penilaian rubrik lomba
     */
    public function criteria(): HasMany
    {
        return $this->hasMany(AssessmentCriterion::class)->orderBy('order_index');
    }

    /**
     * Relasi ke seluruh skor rekapitulasi penilaian lomba
     */
    public function scores(): HasMany
    {
        return $this->hasMany(CompetitionScore::class)->orderBy('rank');
    }

    /**
     * Helper cek apakah lomba bertipe individu
     */
    public function isIndividual(): bool
    {
        return $this->participant_type === 'individual';
    }

    /**
     * Helper cek apakah lomba bertipe kelompok (beberapa orang)
     */
    public function isGroup(): bool
    {
        return $this->participant_type === 'group';
    }

    /**
     * Helper cek apakah lomba bertipe regu utuh
     */
    public function isPatrol(): bool
    {
        return in_array($this->participant_type, ['patrol', 'full_patrol']);
    }

    /**
     * Label tipe format lomba
     */
    public function formatTypeLabel(): string
    {
        if ($this->isGroup()) {
            return 'Kelompok';
        }
        if ($this->isIndividual()) {
            return 'Individu';
        }
        return 'Regu Utuh';
    }

    /**
     * Format label interval jumlah peserta regu, kelompok, atau individu
     */
    public function participantRangeLabel(): string
    {
        if ($this->isIndividual()) {
            return 'Individu (1 Peserta)';
        }

        if ($this->isGroup()) {
            if ($this->min_participants === $this->max_participants) {
                return "Kelompok (Tepat {$this->min_participants} Peserta)";
            }
            return "Kelompok ({$this->min_participants} - {$this->max_participants} Peserta)";
        }

        if ($this->min_participants === $this->max_participants) {
            return "Regu Utuh (Tepat {$this->min_participants} Peserta)";
        }

        return "Regu Utuh ({$this->min_participants} - {$this->max_participants} Peserta)";
    }

    /**
     * Memvalidasi apakah jumlah peserta memenuhi interval lomba
     */
    public function validateParticipantCount(int $count): bool
    {
        return $count >= $this->min_participants && $count <= $this->max_participants;
    }

    /**
     * Label kategori golongan lomba
     */
    public function categoryLabel(): string
    {
        return match ($this->category) {
            'boys' => 'Penggalang Putra',
            'girls' => 'Penggalang Putri',
            default => 'Putra & Putri (Terpisah)',
        };
    }
}

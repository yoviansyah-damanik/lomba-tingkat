<?php

namespace App\Models;

use App\Enums\PatrolGenderEnum;
use App\Enums\PatrolRoleEnum;
use App\Enums\RegistrationStatusEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Model regu penggalang (Patrol)
 */
class Patrol extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'scout_group_id',
        'patrol_name',
        'gender_category',
        'draw_number',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'gender_category' => PatrolGenderEnum::class,
            'status' => RegistrationStatusEnum::class,
            'draw_number' => 'integer',
        ];
    }

    /**
     * Gugus Depan pangkalan induk
     */
    public function scoutGroup(): BelongsTo
    {
        return $this->belongsTo(ScoutGroup::class);
    }

    /**
     * Seluruh peserta anggota regu
     */
    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    /**
     * Pimpinan Regu (Pinru)
     */
    public function leader(): HasOne
    {
        return $this->hasOne(Participant::class)->where('role_in_patrol', PatrolRoleEnum::PatrolLeader->value);
    }

    /**
     * Wakil Pimpinan Regu (Wapinru)
     */
    public function assistantLeader(): HasOne
    {
        return $this->hasOne(Participant::class)->where('role_in_patrol', PatrolRoleEnum::AssistantPatrolLeader->value);
    }

    /**
     * Anggota regu reguler
     */
    public function members(): HasMany
    {
        return $this->hasMany(Participant::class)->where('role_in_patrol', PatrolRoleEnum::Member->value);
    }

    /**
     * Rekapitulasi nilai lomba regu ini
     */
    public function competitionScores(): HasMany
    {
        return $this->hasMany(CompetitionScore::class);
    }

    /**
     * Seluruh penilaian juri (assessments) untuk regu ini
     */
    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    /**
     * Kode acak/nomor undian blind scoring resmi
     */
    public function blindScoringCode(): string
    {
        $num = $this->draw_number ?: 1;
        return 'UND-' . str_pad((string) $num, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Label nomor undian peserta untuk tampilan blind scoring juri
     */
    public function blindScoringLabel(): string
    {
        $num = $this->draw_number ?: 1;
        return 'Nomor Undian #' . str_pad((string) $num, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Nama tampilan regu yang menghormati kebijakan privasi / blind mode
     */
    public function displayName(bool $unmask = false): string
    {
        if ($unmask || ! \App\Models\Event::first()?->hide_patrol_identities) {
            return $this->patrol_name;
        }

        $num = $this->draw_number ?: 1;
        return 'Regu #' . str_pad((string) $num, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Nama tampilan pangkalan sekolah yang menghormati kebijakan privasi / blind mode
     */
    public function displaySchool(bool $unmask = false): string
    {
        if ($unmask || ! \App\Models\Event::first()?->hide_patrol_identities) {
            return $this->scoutGroup?->school_name ?? '-';
        }

        return 'Pangkalan Dirahasiakan';
    }

    /**
     * Helper cek apakah regu sudah memiliki Pinru
     */
    public function hasLeader(): bool
    {
        return $this->participants()->where('role_in_patrol', PatrolRoleEnum::PatrolLeader->value)->exists();
    }

    /**
     * Helper cek apakah regu sudah memiliki Wapinru
     */
    public function hasAssistantLeader(): bool
    {
        return $this->participants()->where('role_in_patrol', PatrolRoleEnum::AssistantPatrolLeader->value)->exists();
    }

    /**
     * Helper cek apakah regu putra
     */
    public function isBoys(): bool
    {
        return $this->gender_category === PatrolGenderEnum::Boys || $this->gender_category === 'boys';
    }

    /**
     * Helper cek apakah regu putri
     */
    public function isGirls(): bool
    {
        return $this->gender_category === PatrolGenderEnum::Girls || $this->gender_category === 'girls';
    }

    /**
     * Jumlah total peserta terdaftar dalam regu
     */
    public function participantCount(): int
    {
        return $this->participants()->count();
    }

    /**
     * Nomor Gugus Depan spesifik regu (Ganjil untuk Putra, Genap untuk Putri)
     */
    public function getGroupNumberAttribute(): string
    {
        if (! $this->scoutGroup) {
            return '';
        }

        return $this->isBoys()
            ? ($this->scoutGroup->male_group_number ?? ScoutGroup::formatGroupNumbers($this->scoutGroup->group_number)['male'])
            : ($this->scoutGroup->female_group_number ?? ScoutGroup::formatGroupNumbers($this->scoutGroup->group_number)['female']);
    }

    /**
     * Pembina Pendamping yang mendampingi regu ini sesuai kategori gender
     */
    public function chaperone(): ?Chaperone
    {
        if (! $this->scoutGroup) {
            return null;
        }

        return $this->isBoys() ? $this->scoutGroup->maleChaperone : $this->scoutGroup->femaleChaperone;
    }

    /**
     * Mengurutkan peserta regu: Pinru (1), Wapinru (2), lalu Anggota (3..8)
     */
    public function orderedParticipants(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->participants->sortBy(function ($participant) {
            $role = $participant->role_in_patrol instanceof PatrolRoleEnum
                ? $participant->role_in_patrol->value
                : $participant->role_in_patrol;

            return match ($role) {
                'patrol_leader' => 1,
                'assistant_patrol_leader' => 2,
                default => 3,
            };
        })->values();
    }
}


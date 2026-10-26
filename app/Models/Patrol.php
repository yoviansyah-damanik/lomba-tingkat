<?php

namespace App\Models;

use App\Enums\PatrolGenderEnum;
use App\Enums\PatrolRoleEnum;
use App\Enums\RegistrationStatusEnum;
use Illuminate\Database\Eloquent\Collection;
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

    /** Jumlah anggota wajib setiap regu */
    public const REQUIRED_MEMBERS = 8;

    protected $fillable = [
        'scout_group_id',
        'patrol_name',
        'gender_category',
        'npp',
        'chaperone_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'gender_category' => PatrolGenderEnum::class,
            'status' => RegistrationStatusEnum::class,
        ];
    }

    protected static function booted(): void
    {
        // Cascade DB tidak memicu event model, maka peserta dihapus satu per satu agar akun dan berkasnya ikut terhapus
        static::deleting(function (Patrol $patrol) {
            $patrol->participants()->get()->each(fn (Participant $participant) => $participant->delete());
        });
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
     * Label Nomor Pendaftaran Peserta (NPP); tanda strip bila NPP belum digenerate
     */
    public function nppLabel(): string
    {
        return $this->npp ?: '—';
    }

    /**
     * Nama samaran regu: NPP dilengkapi kategori karena NPP putra dan putri sama-sama dimulai dari 01
     */
    public function maskedName(): string
    {
        return 'Regu '.$this->nppLabel().' '.($this->isBoys() ? 'Putra' : 'Putri');
    }

    /**
     * Nama tampilan regu yang menghormati kebijakan privasi / blind mode (disamarkan menjadi NPP)
     */
    public function displayName(bool $unmask = false): string
    {
        if ($unmask || ! Event::first()?->hide_patrol_identities) {
            return $this->patrol_name;
        }

        return $this->maskedName();
    }

    /**
     * Nama tampilan pangkalan sekolah yang menghormati kebijakan privasi / blind mode
     */
    public function displaySchool(bool $unmask = false): string
    {
        if ($unmask || ! Event::first()?->hide_patrol_identities) {
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
     * Pembina Pendamping (bindamping) yang dipilih mendampingi regu ini; harus sekategori dan segudep
     */
    public function chaperone(): BelongsTo
    {
        return $this->belongsTo(Chaperone::class);
    }

    /**
     * Mengurutkan peserta regu: Pinru di urutan pertama (1), Anggota di tengah (2..7), Wapinru di urutan terakhir (8)
     */
    public function orderedParticipants(): Collection
    {
        return $this->participants->sortBy(function ($participant) {
            $role = $participant->role_in_patrol instanceof PatrolRoleEnum
                ? $participant->role_in_patrol->value
                : $participant->role_in_patrol;

            return match ($role) {
                'patrol_leader' => 1,
                'assistant_patrol_leader' => 3,
                default => 2,
            };
        })->values();
    }
}

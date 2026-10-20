<?php

namespace App\Models;

use App\Enums\PatrolRoleEnum;
use App\Enums\RegistrationStatusEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Model peserta regu lomba tingkat III
 */
class Participant extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'patrol_id',
        'full_name',
        'scout_id',
        'role_in_patrol',
        'class_grade',
        'gender',
        'birth_date',
        'birth_place',
        'religion',
        'blood_type',
        'home_address',
        'city',
        'postal_code',
        'home_phone',
        'phone',
        'parent_name',
        'parent_job',
        'parent_address',
        'parent_city',
        'parent_postal_code',
        'parent_home_phone',
        'parent_phone',
        'formal_education',
        'scout_siaga',
        'scout_penggalang',
        'photo_path',
        'id_number',
        'qr_token',
        'qr_generated_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'qr_generated_at' => 'datetime',
            'role_in_patrol' => PatrolRoleEnum::class,
            'status' => RegistrationStatusEnum::class,
            'formal_education' => 'array',
            'scout_siaga' => 'array',
            'scout_penggalang' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Participant $participant) {
            if (empty($participant->id_number)) {
                do {
                    $code = 'ID-' . strtoupper(Str::random(8));
                } while (static::where('id_number', $code)->exists());
                $participant->id_number = $code;
            }

            if (empty($participant->qr_token)) {
                $participant->qr_token = (string) Str::uuid();
                $participant->qr_generated_at = now();
            }
        });
    }

    /**
     * Mendapatkan tingkatan SKU Penggalang tertinggi yang telah dicapai
     */
    public function highestSkuLevel(): string
    {
        $penggalang = $this->scout_penggalang;
        if (is_array($penggalang) && isset($penggalang['levels'])) {
            $levels = $penggalang['levels'];
            if (!empty($levels['garuda'])) return 'Garuda';
            if (!empty($levels['terap'])) return 'Terap';
            if (!empty($levels['rakit'])) return 'Rakit';
            if (!empty($levels['ramu'])) return 'Ramu';
        }
        return 'Penggalang';
    }

    /**
     * Label kelas peserta (VII, VIII, IX)
     */
    public function getGradeLabelAttribute(): string
    {
        if (!empty($this->class_grade)) {
            return $this->class_grade;
        }

        if (is_array($this->formal_education)) {
            foreach ($this->formal_education as $edu) {
                if (($edu['level'] ?? '') === 'SMP' && !empty($edu['notes'])) {
                    return $edu['notes'];
                }
            }
        }

        return 'VIII';
    }

    /**
     * Template standar riwayat pendidikan formal peserta (SD dan SMP)
     */
    public static function defaultFormalEducation(): array
    {
        return [
            ['level' => 'SD', 'school_name' => '', 'city' => 'Padangsidimpuan', 'graduation_year' => '', 'notes' => ''],
            ['level' => 'SMP', 'school_name' => '', 'city' => 'Padangsidimpuan', 'graduation_year' => '', 'notes' => ''],
        ];
    }

    /**
     * Template standar riwayat kepramukaan tingkat Siaga
     */
    public static function defaultScoutSiaga(): array
    {
        return [
            'year' => '',
            'group_number' => '',
            'pangkalan' => '',
            'levels' => [
                'mula' => '',
                'bantu' => '',
                'tata' => '',
                'garuda' => '',
            ],
            'activities' => array_fill(0, 6, ''),
            'tkk' => array_fill(0, 20, ''),
        ];
    }

    /**
     * Template standar riwayat kepramukaan tingkat Penggalang
     */
    public static function defaultScoutPenggalang(): array
    {
        return [
            'year' => '',
            'group_number' => '',
            'pangkalan' => '',
            'levels' => [
                'ramu' => '',
                'rakit' => '',
                'terap' => '',
                'garuda' => '',
            ],
            'activities' => array_fill(0, 7, ''),
            'tkk_purwa' => array_fill(0, 10, ''),
            'tkk_madya' => array_fill(0, 10, ''),
            'tkk_utama' => array_fill(0, 10, ''),
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        // Otomatis membuat secure random UUID token saat peserta didaftarkan
        static::creating(function (Participant $participant) {
            if (empty($participant->qr_token)) {
                $participant->qr_token = (string) Str::uuid();
                $participant->qr_generated_at = now();
            }
        });
    }

    /**
     * Regu tempat peserta bernaung
     */
    public function patrol(): BelongsTo
    {
        return $this->belongsTo(Patrol::class);
    }

    /**
     * Helper cek apakah jabatan adalah Pinru
     */
    public function isLeader(): bool
    {
        return $this->role_in_patrol === PatrolRoleEnum::PatrolLeader;
    }

    /**
     * Helper cek apakah jabatan adalah Wapinru
     */
    public function isAssistantLeader(): bool
    {
        return $this->role_in_patrol === PatrolRoleEnum::AssistantPatrolLeader;
    }
}

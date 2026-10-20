<?php

namespace App\Models;

use App\Enums\RegistrationStatusEnum;
use App\Services\ContingentAccountService;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Model Pembina Pendamping (Bindamping)
 */
class Chaperone extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'scout_group_id',
        'user_id',
        'full_name',
        'scout_id',
        'national_identity_number',
        'gender',
        'birth_place',
        'birth_date',
        'religion',
        'blood_type',
        'home_address',
        'city',
        'postal_code',
        'home_phone',
        'phone_number',
        'photo_path',
        'formal_education',
        'scout_experiences',
        'scout_trainings',
        'other_trainings',
        'id_number',
        'qr_token',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'formal_education' => 'array',
            'scout_experiences' => 'array',
            'scout_trainings' => 'array',
            'other_trainings' => 'array',
            'status' => RegistrationStatusEnum::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Chaperone $chaperone) {
            if (empty($chaperone->id_number)) {
                do {
                    $code = 'ID-' . strtoupper(Str::random(8));
                } while (static::where('id_number', $code)->exists());
                $chaperone->id_number = $code;
            }

            if (empty($chaperone->qr_token)) {
                $chaperone->qr_token = (string) Str::uuid();
            }
        });

        // Akun bindamping mengikuti data bindamping: diperbarui saat berubah, dihapus saat bindamping dihapus
        static::saved(fn (Chaperone $chaperone) => app(ContingentAccountService::class)->syncChaperone($chaperone));
        static::deleting(fn (Chaperone $chaperone) => app(ContingentAccountService::class)->deleteChaperoneAccount($chaperone));
    }

    /**
     * Template standar tingkat pendidikan formal
     */
    public static function defaultFormalEducation(): array
    {
        return [
            ['level' => 'SD', 'school_name' => '', 'city' => '', 'graduation_year' => '', 'notes' => ''],
            ['level' => 'SMP', 'school_name' => '', 'city' => '', 'graduation_year' => '', 'notes' => ''],
            ['level' => 'SMA', 'school_name' => '', 'city' => '', 'graduation_year' => '', 'notes' => ''],
            ['level' => 'S1', 'school_name' => '', 'city' => '', 'graduation_year' => '', 'notes' => ''],
            ['level' => 'S2', 'school_name' => '', 'city' => '', 'graduation_year' => '', 'notes' => ''],
        ];
    }

    /**
     * Template standar riwayat golongan kepramukaan
     */
    public static function defaultScoutExperiences(): array
    {
        return [
            ['level' => 'Siaga', 'year' => '', 'group_number' => '', 'base' => ''],
            ['level' => 'Penggalang', 'year' => '', 'group_number' => '', 'base' => ''],
            ['level' => 'Penegak', 'year' => '', 'group_number' => '', 'base' => ''],
            ['level' => 'Pandega', 'year' => '', 'group_number' => '', 'base' => ''],
            ['level' => 'Pembina', 'year' => '', 'group_number' => '', 'base' => ''],
        ];
    }

    /**
     * Template standar riwayat kursus kepramukaan
     */
    public static function defaultScoutTrainings(): array
    {
        return [
            ['course' => 'KMD', 'year' => '', 'location' => '', 'notes' => ''],
            ['course' => 'KML', 'year' => '', 'location' => '', 'notes' => ''],
            ['course' => 'KPD', 'year' => '', 'location' => '', 'notes' => ''],
            ['course' => 'KPL', 'year' => '', 'location' => '', 'notes' => ''],
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        // Otomatis generate UUID token aman
        static::creating(function (Chaperone $chaperone) {
            if (empty($chaperone->qr_token)) {
                $chaperone->qr_token = (string) Str::uuid();
            }
        });
    }

    /**
     * Gugus Depan yang didampingi
     */
    public function scoutGroup(): BelongsTo
    {
        return $this->belongsTo(ScoutGroup::class);
    }

    /**
     * Akun pengguna terkait (opsional)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Apakah pembina pendamping putra
     */
    public function isMale(): bool
    {
        return $this->gender === 'male';
    }

    /**
     * Apakah pembina pendamping putri
     */
    public function isFemale(): bool
    {
        return $this->gender === 'female';
    }

    /**
     * Nomor Gugus Depan spesifik Pembina Pendamping (Ganjil untuk Putra, Genap untuk Putri)
     */
    public function getGroupNumberAttribute(): string
    {
        if (! $this->scoutGroup) {
            return '';
        }

        $sg = $this->scoutGroup;
        if ($this->isMale()) {
            return $sg->male_group_number ?? (ScoutGroup::formatGroupNumbers($sg->group_number)['male'] ?? $sg->group_number);
        }

        return $sg->female_group_number ?? (ScoutGroup::formatGroupNumbers($sg->group_number)['female'] ?? $sg->group_number);
    }
}

<?php

namespace App\Models;

use App\Enums\PatrolGenderEnum;
use App\Enums\RegistrationStatusEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Model pangkalan Gugus Depan resmi
 */
class ScoutGroup extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'registration_code',
        'school_name',
        'group_number',
        'male_group_number',
        'female_group_number',
        'kwarran_name',
        'address',
        'contact_person',
        'contact_phone',
        'verification_status',
        'revision_notes',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'verification_status' => RegistrationStatusEnum::class,
            'verified_at' => 'datetime',
        ];
    }

    /**
     * Akun pengguna perwakilan resmi Gudep
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Administrator yang memverifikasi
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Seluruh regu di bawah naungan Gugus Depan
     */
    public function patrols(): HasMany
    {
        return $this->hasMany(Patrol::class);
    }

    /**
     * Regu Penggalang Putra
     */
    public function boysPatrol(): HasOne
    {
        return $this->hasOne(Patrol::class)->where('gender_category', PatrolGenderEnum::Boys->value);
    }

    /**
     * Regu Penggalang Putri
     */
    public function girlsPatrol(): HasOne
    {
        return $this->hasOne(Patrol::class)->where('gender_category', PatrolGenderEnum::Girls->value);
    }

    /**
     * Seluruh Pembina Pendamping (Bindamping)
     */
    public function chaperones(): HasMany
    {
        return $this->hasMany(Chaperone::class);
    }

    /**
     * Seluruh peserta regu kontingen
     */
    public function participants(): HasManyThrough
    {
        return $this->hasManyThrough(Participant::class, Patrol::class);
    }

    /**
     * Apakah pendaftaran masih berstatus draft atau perbaikan
     */
    public function isEditable(): bool
    {
        return $this->verification_status?->isEditable() ?? true;
    }

    /**
     * Apakah pendaftaran telah disetujui resmi oleh Admin
     */
    public function isVerified(): bool
    {
        return $this->verification_status === RegistrationStatusEnum::Verified;
    }

    /**
     * Pembina Pendamping Putra (Bindamping Putra)
     */
    public function maleChaperone(): HasOne
    {
        return $this->hasOne(Chaperone::class)->where('gender', 'male');
    }

    /**
     * Pembina Pendamping Putri (Bindamping Putri)
     */
    public function femaleChaperone(): HasOne
    {
        return $this->hasOne(Chaperone::class)->where('gender', 'female');
    }

    /**
     * Otomatis memformat nomor Gudep ganjil (putra) dan menghitung nomor genap (putri)
     * Contoh input: "01-093" -> menghasilkan ['male' => '01-093', 'female' => '01-094', 'combined' => '01-093 & 01-094']
     */
    public static function formatGroupNumbers(string $oddInput): array
    {
        $clean = trim($oddInput);

        // Jika terinput format gabungan (misal "01-093 & 01-094" atau "01.001 - 01.002"), ambil bagian pertama
        if (str_contains($clean, '&')) {
            $parts = explode('&', $clean);
            $clean = trim($parts[0]);
        } elseif (str_contains($clean, ' - ')) {
            $parts = explode(' - ', $clean);
            $clean = trim($parts[0]);
        }

        // Pola dengan kode kwarcab: misal "01-093" atau "01.093" atau "01-1"
        if (preg_match('/^(\d{2})[-.]?(\d+)$/', $clean, $matches)) {
            $prefix = $matches[1];
            $digits = $matches[2];
            $num = (int) $digits;
            $length = max(3, strlen($digits));

            // Pastikan bilangan ganjil: jika terinput genap, kurangi 1
            $oddNum = ($num % 2 === 0) ? $num - 1 : $num;
            $evenNum = $oddNum + 1;

            $oddPadded = str_pad((string) $oddNum, $length, '0', STR_PAD_LEFT);
            $evenPadded = str_pad((string) $evenNum, $length, '0', STR_PAD_LEFT);

            $male = "{$prefix}-{$oddPadded}";
            $female = "{$prefix}-{$evenPadded}";

            return [
                'male' => $male,
                'female' => $female,
                'combined' => "{$male} & {$female}",
            ];
        }

        // Jika hanya angka saja (misal "093" atau "93")
        $digits = preg_replace('/[^\d]/', '', $clean);
        $num = (int) ($digits ?: '1');
        $oddNum = ($num % 2 === 0) ? $num - 1 : $num;
        $evenNum = $oddNum + 1;
        $length = max(3, strlen((string) $digits));

        $male = '01-'.str_pad((string) $oddNum, $length, '0', STR_PAD_LEFT);
        $female = '01-'.str_pad((string) $evenNum, $length, '0', STR_PAD_LEFT);

        return [
            'male' => $male,
            'female' => $female,
            'combined' => "{$male} & {$female}",
        ];
    }

    /**
     * Menghasilkan kode unik registrasi pangkalan (format: GDP-PSP-XX)
     */
    public static function generateRegistrationCode(): string
    {
        $count = static::count() + 1;
        do {
            $code = 'GDP-PSP-'.str_pad((string) $count, 2, '0', STR_PAD_LEFT);
            $count++;
        } while (static::where('registration_code', $code)->exists());

        return $code;
    }

    /**
     * Nama Kwartir Ranting resmi Gugus Depan
     */
    public function getKwarranNameAttribute(): string
    {
        $raw = $this->attributes['kwarran_name'] ?? null;
        if (! empty($raw)) {
            return $this->formatKwarranName($raw);
        }

        $address = $this->attributes['address'] ?? '';
        $knownKwarrans = [
            'Padangsidimpuan Utara',
            'Padangsidimpuan Selatan',
            'Padangsidimpuan Batunadua',
            'Padangsidimpuan Hutaimbaru',
            'Padangsidimpuan Tenggara',
            'Padangsidimpuan Angkola Julu',
        ];

        foreach ($knownKwarrans as $k) {
            if (stripos($address, $k) !== false) {
                return $k;
            }
        }

        if (preg_match('/Kwarran\s+([A-Za-z\s]+?)(?:,|$)/i', $address, $matches)) {
            return trim($matches[1]);
        }

        return 'Padangsidimpuan';
    }

    /**
     * Bersihkan prefix 'Kwarran' agar rapi pada ID card dan dokumen resmi
     */
    protected function formatKwarranName(string $name): string
    {
        $cleaned = trim(preg_replace('/^Kwarran\s+/i', '', $name));
        return $cleaned ?: 'Padangsidimpuan';
    }
}




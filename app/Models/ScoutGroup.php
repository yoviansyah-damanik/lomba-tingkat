<?php

namespace App\Models;

use App\Enums\PatrolGenderEnum;
use App\Enums\RegistrationStatusEnum;
use App\Jobs\SendVerificationResultPush;
use App\Services\ContingentAccountService;
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
        'mandate_letter_path',
        'payment_proof_path',
        'invitation_id',
        'registered_ip',
        'registered_user_agent',
    ];

    /**
     * Undangan registrasi yang dipakai untuk mendaftarkan pangkalan ini
     */
    public function invitation(): BelongsTo
    {
        return $this->belongsTo(RegistrationInvitation::class, 'invitation_id');
    }

    /**
     * Boot model: beri tahu akun Gudep saat hasil verifikasi ditetapkan (disetujui, revisi, atau ditolak)
     */
    protected static function booted(): void
    {
        static::creating(function (ScoutGroup $group) {
            if (empty($group->registration_code)) {
                $group->registration_code = static::generateGudepId([
                    'male' => $group->male_group_number ?? $group->group_number,
                    'female' => $group->female_group_number ?? $group->group_number,
                ]);
            }
        });

        // Pangkalan pada akun peserta/bindamping mengikuti nama dan nomor gudep
        static::updated(function (ScoutGroup $group) {
            if ($group->wasChanged(['school_name', 'group_number', 'male_group_number', 'female_group_number'])) {
                app(ContingentAccountService::class)->syncGroup($group);
            }
        });

        // Akun peserta/bindamping ikut dihapus saat gugus depan dihapus (data anak terhapus lewat cascade DB)
        static::deleting(function (ScoutGroup $group) {
            $service = app(ContingentAccountService::class);

            $group->chaperones()->get()->each(fn (Chaperone $chaperone) => $service->deleteChaperoneAccount($chaperone));
            $group->participants()->get()->each(fn (Participant $participant) => $service->deleteParticipantAccount($participant));
        });

        static::updated(function (ScoutGroup $group) {
            $status = $group->verification_status;

            if (! $group->wasChanged('verification_status') || ! $status) {
                return;
            }

            if (in_array($status, [RegistrationStatusEnum::Verified, RegistrationStatusEnum::NeedsRevision, RegistrationStatusEnum::Rejected], true)) {
                SendVerificationResultPush::dispatch($group->id, $status->value)->afterCommit();
            }
        });
    }

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
     * Pelanggaran susunan regu yang menghalangi pengajuan: jumlah peserta harus tepat 8 serta wajib ada Pinru dan Wapinru
     *
     * @return array<int, string>
     */
    public function patrolRosterProblems(): array
    {
        $problems = [];
        $size = Patrol::REQUIRED_MEMBERS;

        foreach ($this->patrols()->with('participants')->get() as $patrol) {
            foreach ($this->rosterProblemsFor($patrol) as $problem) {
                $problems[] = "Regu {$patrol->patrol_name}: {$problem}";
            }
        }

        return $problems;
    }

    /**
     * Pelanggaran susunan satu regu (memakai relasi participants yang sudah dimuat bila ada)
     *
     * @return array<int, string>
     */
    public function rosterProblemsFor(Patrol $patrol): array
    {
        $problems = [];
        $size = Patrol::REQUIRED_MEMBERS;
        $participants = $patrol->participants;
        $count = $participants->count();

        if ($count !== $size) {
            $problems[] = "berisi {$count} peserta; wajib tepat {$size} peserta.";
        }

        foreach ([
            [\App\Enums\PatrolRoleEnum::PatrolLeader, 'Pimpinan Regu (Pinru)'],
            [\App\Enums\PatrolRoleEnum::AssistantPatrolLeader, 'Wakil Pimpinan Regu (Wapinru)'],
        ] as [$role, $label]) {
            if ($participants->where('role_in_patrol', $role)->isEmpty()) {
                $problems[] = "belum memiliki {$label}.";
            }
        }

        return $problems;
    }

    /**
     * Gender bindamping yang wajib ada ('male'/'female'), mengikuti regu putra/putri yang terdaftar
     *
     * @return array<int, string>
     */
    public function requiredChaperoneGenders(): array
    {
        $patrols = $this->patrols()->get(['id', 'gender_category']);
        $genders = [];

        if ($patrols->contains(fn (Patrol $p) => $p->isBoys())) {
            $genders[] = 'male';
        }

        if ($patrols->contains(fn (Patrol $p) => $p->isGirls())) {
            $genders[] = 'female';
        }

        return $genders;
    }

    /**
     * Bindamping hanya dapat didaftarkan untuk gender yang memiliki regu terlebih dahulu
     */
    public function canRegisterChaperone(string $gender): bool
    {
        return in_array($gender, $this->requiredChaperoneGenders(), true);
    }

    /**
     * Pesan penolakan bila bindamping didaftarkan sebelum regu putra/putri yang bersangkutan ada
     */
    public static function chaperoneRequiresPatrolMessage(string $gender): string
    {
        $label = $gender === 'male' ? 'Putra' : 'Putri';

        return "Tambahkan Regu {$label} terlebih dahulu. Bindamping {$label} hanya dapat didaftarkan setelah Regu {$label} ada.";
    }

    /**
     * Apakah pendaftaran telah disetujui resmi oleh Admin
     */
    public function isVerified(): bool
    {
        return $this->verification_status === RegistrationStatusEnum::Verified;
    }

    /**
     * ID Card dan Form 01/02/03 hanya dapat diunduh setelah kontingen diverifikasi Administrator
     */
    public function canDownloadDocuments(): bool
    {
        return $this->isVerified();
    }

    public const DOWNLOAD_LOCKED_MESSAGE = 'ID Card dan formulir hanya dapat diunduh setelah pendaftaran kontingen diverifikasi Administrator.';

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
     * Menghasilkan ID unik Gugus Depan resmi: G-{no_gudep}, mis. G-01123.01124
     */
    public static function generateGudepId(string|array $groupNumberData, ?string $ignoreId = null): string
    {
        if (is_string($groupNumberData)) {
            $formatted = static::formatGroupNumbers($groupNumberData);
        } else {
            $formatted = $groupNumberData;
        }

        $male = $formatted['male'] ?? '';
        $female = $formatted['female'] ?? '';

        $maleDigits = preg_replace('/\D/', '', (string) $male);
        $femaleDigits = preg_replace('/\D/', '', (string) $female);

        if (! empty($maleDigits) && ! empty($femaleDigits)) {
            $noGudep = "{$maleDigits}.{$femaleDigits}";
        } elseif (! empty($maleDigits)) {
            $noGudep = $maleDigits;
        } elseif (! empty($femaleDigits)) {
            $noGudep = $femaleDigits;
        } else {
            $noGudep = str_pad((string) (static::count() + 1), 5, '0', STR_PAD_LEFT);
        }

        $baseId = "G-{$noGudep}";
        $code = $baseId;
        $suffix = 2;

        while (
            static::where('registration_code', $code)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
            || User::where('id_number', $code)->exists()
            || Participant::where('id_number', $code)->exists()
            || Chaperone::where('id_number', $code)->exists()
        ) {
            $code = "{$baseId}-{$suffix}";
            $suffix++;
        }

        return $code;
    }

    /**
     * Menghasilkan kode unik registrasi pangkalan resmi (G-{no_gudep})
     */
    public static function generateRegistrationCode(?string $groupNumber = null): string
    {
        if ($groupNumber !== null) {
            return static::generateGudepId($groupNumber);
        }

        $count = static::count() + 1;
        $dummy = '01-' . str_pad((string) $count, 3, '0', STR_PAD_LEFT);

        return static::generateGudepId($dummy);
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




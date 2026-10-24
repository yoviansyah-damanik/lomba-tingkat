<?php

namespace App\Models;

use App\Enums\PermissionTypeEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Permohonan izin yang diproses Administrator: meninggalkan perkemahan (permanen/sementara) atau kunjungan tamu
 */
class PermissionRequest extends Model
{
    use HasUuids;

    protected $fillable = ['letter_number', 'type', 'reason', 'guest_name', 'scout_group_id', 'status', 'created_by', 'completed_at'];

    protected function casts(): array
    {
        return [
            'type' => PermissionTypeEnum::class,
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Nomor surat dibuat otomatis dan disimpan saat permohonan ditambahkan
        static::creating(function (PermissionRequest $request) {
            $request->letter_number ??= static::nextLetterNumber($request->created_at ?? now());
        });
    }

    /**
     * Nomor urut berikutnya pada tahun penerbitan: {urut 3 digit}/IZIN/LT3/KWARCAB-PSP/{tahun}
     */
    public static function nextLetterNumber(\DateTimeInterface $issuedAt): string
    {
        $year = $issuedAt->format('Y');
        $suffix = "/IZIN/LT3/KWARCAB-PSP/{$year}";

        $last = static::where('letter_number', 'like', '%'.$suffix)
            ->pluck('letter_number')
            ->map(fn (string $number) => (int) strtok($number, '/'))
            ->max() ?? 0;

        return str_pad((string) ($last + 1), 3, '0', STR_PAD_LEFT).$suffix;
    }

    /**
     * Nomor surat izin yang tersimpan
     */
    public function letterNumber(): string
    {
        return $this->letter_number ?? '-';
    }

    public function scoutGroup(): BelongsTo
    {
        return $this->belongsTo(ScoutGroup::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(PermissionRequestMember::class);
    }

    /**
     * Izin sementara yang masih menunggu konfirmasi kembali peserta
     */
    public function isOutstanding(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Tandai selesai bila seluruh peserta pada izin sementara sudah kembali
     */
    public function completeIfAllReturned(): void
    {
        $pending = $this->members()->whereNotNull('participant_id')->whereNull('returned_at')->exists();

        if (! $pending && $this->status !== 'completed') {
            $this->update(['status' => 'completed', 'completed_at' => now()]);
        }
    }
}

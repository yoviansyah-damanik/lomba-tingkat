<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model kegiatan utama LT III
 */
class Event extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'theme',
        'start_date',
        'end_date',
        'registration_start_at',
        'registration_end_at',
        'is_registration_open',
        'registration_fee_per_patrol',
        'payment_bank_name',
        'payment_account_number',
        'payment_account_holder',
        'payment_instructions',
        'location',
        'description',
        'contact_email',
        'contact_phone',
        'committee_chair_name',
        'committee_chair_nta',
        'judge_chair_name',
        'judge_chair_nta',
        'kwarcab_chair_name',
        'kwarcab_chair_nta',
        'is_active',
        'hide_patrol_identities',
    ];

    /**
     * Penandatangan dokumen resmi: committee (Ketua Panitia Pelaksana LT III), judge (Ketua Dewan Juri), kwarcab (Ketua Kwartir Cabang)
     *
     * @return array{name: ?string, nta: ?string}
     */
    public function signatory(string $role): array
    {
        return [
            'name' => $this->{$role.'_chair_name'} ?: null,
            'nta' => $this->{$role.'_chair_nta'} ?: null,
        ];
    }

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'registration_start_at' => 'datetime',
            'registration_end_at' => 'datetime',
            'is_registration_open' => 'boolean',
            'registration_fee_per_patrol' => 'integer',
            'is_active' => 'boolean',
            'hide_patrol_identities' => 'boolean',
        ];
    }

    /**
     * Mengambil biaya pendaftaran per regu (prioritas kolom database, fallback ke konfigurasi/env)
     */
    public function getFeePerPatrol(): int
    {
        return $this->registration_fee_per_patrol ?? (int) config('registration.fee_per_patrol', 250000);
    }

    /**
     * Format rupiah biaya pendaftaran per regu
     */
    public function getFormattedFeePerPatrol(): string
    {
        return 'Rp ' . number_format($this->getFeePerPatrol(), 0, ',', '.');
    }

    /**
     * Mengambil nama bank tujuan pembayaran
     */
    public function getPaymentBank(): string
    {
        return $this->payment_bank_name ?? config('registration.payment_bank', 'Bank Sumut');
    }

    /**
     * Mengambil nomor rekening resmi Kwarcab
     */
    public function getPaymentAccountNumber(): string
    {
        return $this->payment_account_number ?? config('registration.payment_account_number', '101-02-04-001234-5');
    }

    /**
     * Mengambil nama pemilik rekening resmi Kwarcab
     */
    public function getPaymentAccountHolder(): string
    {
        return $this->payment_account_holder ?? config('registration.payment_account_holder', 'Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan');
    }

    /**
     * Mengambil petunjuk / instruksi pembayaran
     */
    public function getPaymentInstructions(): string
    {
        return $this->payment_instructions ?? config('registration.payment_instructions', 'Pembayaran biaya pendaftaran kontingen dihitung per regu yang didaftarkan. Harap mencantumkan nama pangkalan pada struk/berita transfer.');
    }

    /**
     * Helper statis mengambil seluruh konfigurasi pembayaran event aktif
     */
    public static function activePaymentConfig(): array
    {
        $event = static::where('is_active', true)->first() ?? static::first();
        $feePerPatrol = $event ? $event->getFeePerPatrol() : (int) config('registration.fee_per_patrol', 250000);

        return [
            'fee_per_patrol' => $feePerPatrol,
            'formatted_fee_per_patrol' => 'Rp ' . number_format($feePerPatrol, 0, ',', '.'),
            'bank_name' => $event ? $event->getPaymentBank() : config('registration.payment_bank', 'Bank Sumut'),
            'account_number' => $event ? $event->getPaymentAccountNumber() : config('registration.payment_account_number', '101-02-04-001234-5'),
            'account_holder' => $event ? $event->getPaymentAccountHolder() : config('registration.payment_account_holder', 'Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan'),
            'instructions' => $event ? $event->getPaymentInstructions() : config('registration.payment_instructions', 'Pembayaran biaya pendaftaran kontingen dihitung per regu yang didaftarkan.'),
        ];
    }

    /**
     * Memeriksa apakah identitas regu dan pangkalan disembunyikan untuk pengguna tertentu
     */
    public static function shouldHideIdentities(?User $user = null): bool
    {
        $event = static::first();
        if (! $event || ! $event->hide_patrol_identities) {
            return false;
        }

        // Hanya Administrator yang dapat melihat atau mengintip nama asli
        if ($user && $user->isAdmin()) {
            return false;
        }

        return true;
    }

    /**
     * Memeriksa apakah pendaftaran akun & kontingen Gudep saat ini sedang dibuka
     */
    public function isRegistrationOpen(): bool
    {
        if (! $this->is_active || ! $this->is_registration_open) {
            return false;
        }

        $now = now();

        if ($this->registration_start_at && $now->lt($this->registration_start_at)) {
            return false;
        }

        if ($this->registration_end_at && $now->gt($this->registration_end_at)) {
            return false;
        }

        return true;
    }

    /**
     * Mengambil tanggal batas akhir pendaftaran resmi
     */
    public function getRegistrationDeadlineAttribute(): ?\Illuminate\Support\Carbon
    {
        return $this->registration_end_at;
    }

    /**
     * Memeriksa apakah batas waktu registrasi telah terlampaui
     */
    public function hasRegistrationDeadlinePassed(): bool
    {
        if (! $this->registration_end_at) {
            return false;
        }

        return now()->gt($this->registration_end_at);
    }

    /**
     * Relasi ke seluruh mata lomba kegiatan
     */
    public function competitions(): HasMany
    {
        return $this->hasMany(Competition::class);
    }
}

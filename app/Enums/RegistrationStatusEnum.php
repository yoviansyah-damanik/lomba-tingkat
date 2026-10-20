<?php

namespace App\Enums;

/**
 * Enum untuk siklus status verifikasi pendaftaran kontingen
 */
enum RegistrationStatusEnum: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Verified = 'verified';
    case NeedsRevision = 'needs_revision';
    case Rejected = 'rejected';

    /**
     * Label status pendaftaran
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft (Belum Diajukan)',
            self::Submitted => 'Diajukan (Menunggu Verifikasi Admin)',
            self::Verified => 'Disetujui & Terverifikasi',
            self::NeedsRevision => 'Perlu Perbaikan',
            self::Rejected => 'Ditolak',
        };
    }

    /**
     * Badge CSS kelas Tailwind
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Draft => 'bg-zinc-100 text-zinc-700 border-zinc-300 dark:bg-zinc-800 dark:text-zinc-300',
            self::Submitted => 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-950/60 dark:text-amber-300 animate-pulse',
            self::Verified => 'bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300',
            self::NeedsRevision => 'bg-orange-100 text-orange-800 border-orange-300 dark:bg-orange-950/60 dark:text-orange-300',
            self::Rejected => 'bg-red-100 text-red-800 border-red-300 dark:bg-red-950/60 dark:text-red-300',
        };
    }

    /**
     * Apakah data kontingen masih dapat diedit oleh Gudep
     */
    public function isEditable(): bool
    {
        return in_array($this, [self::Draft, self::NeedsRevision]);
    }
}

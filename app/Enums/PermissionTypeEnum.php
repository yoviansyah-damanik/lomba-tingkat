<?php

namespace App\Enums;

/**
 * Jenis permohonan izin yang diproses Administrator
 */
enum PermissionTypeEnum: string
{
    case LeavePermanent = 'leave_permanent';
    case LeaveTemporary = 'leave_temporary';
    case GuestVisit = 'guest_visit';

    public function label(): string
    {
        return match ($this) {
            self::LeavePermanent => 'Izin meninggalkan perkemahan (Permanen)',
            self::LeaveTemporary => 'Izin meninggalkan perkemahan (Sementara)',
            self::GuestVisit => 'Kunjungan Tamu',
        };
    }

    /**
     * Izin meninggalkan perkemahan wajib memindai QR peserta dan bindamping
     */
    public function requiresScan(): bool
    {
        return $this !== self::GuestVisit;
    }

    /**
     * Hanya izin sementara yang wajib dikonfirmasi kembali (pindai QR peserta saat kembali)
     */
    public function requiresReturn(): bool
    {
        return $this === self::LeaveTemporary;
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::LeavePermanent => 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300',
            self::LeaveTemporary => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300',
            self::GuestVisit => 'bg-sky-100 text-sky-800 dark:bg-sky-950/60 dark:text-sky-300',
        };
    }
}

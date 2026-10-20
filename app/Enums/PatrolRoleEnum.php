<?php

namespace App\Enums;

/**
 * Enum untuk jabatan anggota dalam regu penggalang
 */
enum PatrolRoleEnum: string
{
    case PatrolLeader = 'patrol_leader';
    case AssistantPatrolLeader = 'assistant_patrol_leader';
    case Member = 'member';

    /**
     * Label jabatan resmi Gerakan Pramuka
     */
    public function label(): string
    {
        return match ($this) {
            self::PatrolLeader => 'Pimpinan Regu (Pinru)',
            self::AssistantPatrolLeader => 'Wakil Pimpinan Regu (Wapinru)',
            self::Member => 'Anggota Regu',
        };
    }

    /**
     * Label singkat untuk badge UI (PINRU, WAPINRU, Anggota)
     */
    public function shortLabel(): string
    {
        return match ($this) {
            self::PatrolLeader => 'PINRU',
            self::AssistantPatrolLeader => 'WAPINRU',
            self::Member => 'Anggota',
        };
    }

    /**
     * Nama icon tanda jabatan (bars-3 untuk PINRU / balok 3, bars-2 untuk WAPINRU / balok 2)
     */
    public function iconName(): ?string
    {
        return match ($this) {
            self::PatrolLeader => 'bars-3',
            self::AssistantPatrolLeader => 'bars-2',
            self::Member => null,
        };
    }

    /**
     * Styling badge kelas Tailwind CSS berdasarkan jabatan di regu
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::PatrolLeader => 'border-amber-300 bg-amber-50 text-amber-800 dark:border-amber-700/60 dark:bg-amber-950/60 dark:text-amber-300',
            self::AssistantPatrolLeader => 'border-blue-300 bg-blue-50 text-blue-800 dark:border-blue-700/60 dark:bg-blue-950/60 dark:text-blue-300',
            self::Member => 'border-zinc-200 bg-zinc-50 text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800/80 dark:text-zinc-400',
        };
    }
}

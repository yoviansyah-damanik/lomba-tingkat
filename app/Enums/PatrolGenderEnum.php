<?php

namespace App\Enums;

/**
 * Enum untuk golongan kategori regu penggalang
 */
enum PatrolGenderEnum: string
{
    case Boys = 'boys';
    case Girls = 'girls';

    /**
     * Label kategori regu
     */
    public function label(): string
    {
        return match ($this) {
            self::Boys => 'Penggalang Putra',
            self::Girls => 'Penggalang Putri',
        };
    }

    public function isBoys(): bool
    {
        return $this === self::Boys;
    }

    public function isGirls(): bool
    {
        return $this === self::Girls;
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Boys => 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800/40',
            self::Girls => 'bg-rose-100 text-rose-800 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800/40',
        };
    }
}

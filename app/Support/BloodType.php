<?php

namespace App\Support;

/**
 * Pilihan golongan darah beserta resus; "-" berarti belum mengetahui golongan darah (nilai valid)
 */
class BloodType
{
    /** @var array<string, string> nilai tersimpan => label pilihan */
    public const OPTIONS = [
        '-' => '- (Belum mengetahui)',
        'A' => 'A',
        'A+' => 'A+',
        'A-' => 'A-',
        'B' => 'B',
        'B+' => 'B+',
        'B-' => 'B-',
        'AB' => 'AB',
        'AB+' => 'AB+',
        'AB-' => 'AB-',
        'O' => 'O',
        'O+' => 'O+',
        'O-' => 'O-',
    ];

    /**
     * Aturan validasi nilai golongan darah
     */
    public static function rule(): string
    {
        return 'in:'.implode(',', array_keys(self::OPTIONS));
    }
}
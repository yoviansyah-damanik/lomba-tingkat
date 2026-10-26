<?php

namespace Database\Seeders\Support;

use Illuminate\Support\Carbon;

/**
 * Menggeser jadwal resmi LT-III pada lingkungan local/dev agar lomba terasa baru dimulai hari ini:
 * hari pertama kegiatan menjadi hari ini dan sesi lomba hari pertama sudah terbuka untuk dinilai juri.
 * Lingkungan lain (production, testing) memakai jadwal resmi apa adanya.
 */
class LiveContest
{
    private const TIMEZONE = 'Asia/Jakarta';

    /** Hari pertama kegiatan menurut Juknis */
    private const OFFICIAL_FIRST_DAY = '2026-11-14';

    public static function active(): bool
    {
        return app()->environment(['local', 'dev', 'development']);
    }

    /**
     * Selisih hari antara hari pertama resmi dan hari ini (0 bila tidak aktif)
     */
    public static function dayOffset(): int
    {
        if (! self::active()) {
            return 0;
        }

        $first = Carbon::parse(self::OFFICIAL_FIRST_DAY, self::TIMEZONE)->startOfDay();

        return (int) $first->diffInDays(Carbon::now(self::TIMEZONE)->startOfDay(), false);
    }

    public static function shift(Carbon|string $time): Carbon
    {
        $time = $time instanceof Carbon ? $time->copy() : Carbon::parse($time, self::TIMEZONE);

        return $time->addDays(self::dayOffset());
    }

    /** Lama kegiatan pada lingkungan local/dev (hari) */
    public const DURATION_DAYS = 3;

    /**
     * Jendela sesi lomba. Pada local/dev seluruh lomba dimulai hari ini dan berlangsung penuh
     * selama 3 hari (00:00 hari ini s.d. 23:59 hari ketiga), sehingga juri dapat menilai kapan pun
     * seeder dijalankan. Lingkungan lain memakai jadwal resmi.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function window(Carbon $start, Carbon $end): array
    {
        if (! self::active()) {
            return [$start, $end];
        }

        $today = Carbon::now(self::TIMEZONE)->startOfDay();

        return [$today, $today->copy()->addDays(self::DURATION_DAYS - 1)->endOfDay()->startOfMinute()];
    }

    /**
     * Batas akhir pendaftaran: pada local/dev pendaftaran ditutup hari ini
     */
    public static function registrationEnd(Carbon $official): Carbon
    {
        return self::active() ? Carbon::now(self::TIMEZONE)->subMinute() : $official;
    }

    /**
     * Label hari ("Sabtu 14 November") mengikuti tanggal yang sudah digeser
     */
    public static function dayLabel(string $officialDate, string $officialLabel): string
    {
        if (! self::active()) {
            return $officialLabel;
        }

        return self::shift($officialDate)->locale('id')->translatedFormat('l j F');
    }
}

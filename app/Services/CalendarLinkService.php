<?php

namespace App\Services;

use App\Models\CompetitionSchedule;
use Illuminate\Support\Collection;

/**
 * Pembuat tautan Google Calendar dan berkas iCalendar (.ics) dari jadwal lomba
 */
class CalendarLinkService
{
    /**
     * Pengingat sebelum sesi dimulai (menit), selaras dengan pembukaan presensi
     */
    private const REMINDER_MINUTES = 30;

    /**
     * Tautan "Tambah ke Google Calendar" untuk satu jadwal
     */
    public function googleUrl(CompetitionSchedule $schedule): string
    {
        $schedule->loadMissing('competition');

        return 'https://calendar.google.com/calendar/render?'.http_build_query([
            'action' => 'TEMPLATE',
            'text' => $this->title($schedule),
            'dates' => $this->utc($schedule->start_time).'/'.$this->utc($schedule->end_time),
            'details' => $this->description($schedule),
            'location' => (string) $schedule->arena_location,
            'ctz' => 'Asia/Jakarta',
        ], '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Isi berkas .ics untuk satu atau beberapa jadwal
     *
     * @param  Collection<int, CompetitionSchedule>  $schedules
     */
    public function ics(Collection $schedules): string
    {
        $schedules->each(fn (CompetitionSchedule $schedule) => $schedule->loadMissing('competition'));
        $host = parse_url(config('app.url'), PHP_URL_HOST) ?: 'lomba-tingkat.local';

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Lomba Tingkat//Jadwal Lomba//ID',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:Jadwal Lomba Tingkat',
        ];

        foreach ($schedules as $schedule) {
            array_push(
                $lines,
                'BEGIN:VEVENT',
                'UID:'.$schedule->id.'@'.$host,
                'DTSTAMP:'.$this->utc(now()),
                'LAST-MODIFIED:'.$this->utc($schedule->updated_at ?? now()),
                'DTSTART:'.$this->utc($schedule->start_time),
                'DTEND:'.$this->utc($schedule->end_time),
                'SUMMARY:'.$this->escape($this->title($schedule)),
                'DESCRIPTION:'.$this->escape($this->description($schedule)),
                'LOCATION:'.$this->escape((string) $schedule->arena_location),
                'STATUS:'.($schedule->status === 'cancelled' ? 'CANCELLED' : 'CONFIRMED'),
                'BEGIN:VALARM',
                'ACTION:DISPLAY',
                'DESCRIPTION:'.$this->escape('Segera dimulai: '.$this->title($schedule)),
                'TRIGGER:-PT'.self::REMINDER_MINUTES.'M',
                'END:VALARM',
                'END:VEVENT',
            );
        }

        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", array_map([$this, 'fold'], $lines))."\r\n";
    }

    private function title(CompetitionSchedule $schedule): string
    {
        return trim(($schedule->competition?->title ?: 'Lomba').' - '.$schedule->session_name);
    }

    private function description(CompetitionSchedule $schedule): string
    {
        return implode("\n", [
            'Kategori: '.$schedule->genderLabel(),
            'Peserta: '.$schedule->participantTypeLabel(),
            'Presensi dibuka: '.$schedule->start_time->copy()->subMinutes(30)->timezone('Asia/Jakarta')->format('H:i').' WIB',
        ]);
    }

    private function utc(\DateTimeInterface $time): string
    {
        return \Carbon\Carbon::instance($time)->utc()->format('Ymd\THis\Z');
    }

    private function escape(string $text): string
    {
        return str_replace(['\\', ';', ',', "\r\n", "\n"], ['\\\\', '\;', '\,', '\n', '\n'], $text);
    }

    /**
     * Lipat baris panjang (maks 75 oktet) sesuai RFC 5545
     */
    private function fold(string $line): string
    {
        if (strlen($line) <= 75) {
            return $line;
        }

        $chunks = [];
        $rest = $line;
        $limit = 75;

        while (strlen($rest) > $limit) {
            $cut = $limit;
            while ($cut > 0 && (ord($rest[$cut]) & 0xC0) === 0x80) {
                $cut--;
            }
            $chunks[] = substr($rest, 0, $cut);
            $rest = substr($rest, $cut);
            $limit = 74;
        }
        $chunks[] = $rest;

        return implode("\r\n ", $chunks);
    }
}

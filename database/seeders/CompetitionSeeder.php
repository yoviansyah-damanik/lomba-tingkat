<?php

namespace Database\Seeders;

use App\Models\Competition;
use App\Models\Event;
use Illuminate\Database\Seeder;

/**
 * Seeder 18 mata lomba resmi LT-III Tahun 2026 (Juknis Bab III: kegiatan berawalan "Lomba" yang dinilai juri)
 * beserta ketentuan teknisnya
 */
class CompetitionSeeder extends Seeder
{
    public function run(): void
    {
        $event = Event::first();

        foreach ((require __DIR__.'/data/lt3_2026.php')['competitions'] as $item) {
            Competition::updateOrCreate(
                ['code' => $item['code']],
                [
                    'event_id' => $event?->id,
                    'title' => $item['title'],
                    'category' => 'all',
                    'participant_type' => $item['type'],
                    'min_participants' => $item['min'],
                    'max_participants' => $item['max'],
                    'description' => "Kelompok Kegiatan: {$item['group']} (kegiatan nomor {$item['no']}). {$item['goal']}",
                    'guidelines_text' => $this->guidelines($item),
                    'scoring_formula' => 'average',
                    'is_published' => true,
                ]
            );
        }
    }

    /**
     * Ketentuan teknis lengkap sesuai format tabel Juknis
     */
    private function guidelines(array $item): string
    {
        $steps = collect($item['steps'])
            ->map(fn (string $step, int $i) => chr(97 + $i).'. '.$step)
            ->implode("\n");

        $criteria = collect($item['criteria'])
            ->map(fn (array $c) => "- {$c[0]} ({$c[1]}%)")
            ->implode("\n");

        return collect([
            "Tujuan: {$item['goal']}",
            "Waktu: {$item['time']}",
            "Tempat: {$item['place']}",
            'Peserta: '.$this->participantsText($item),
            "Tahap & Prosedur:\n{$steps}",
            "Aspek Penilaian:\n{$criteria}",
            "Pakaian: {$item['attire']}",
            $item['gear'] ? "Perlengkapan peserta: {$item['gear']}" : null,
            $item['committee_gear'] ? "Perlengkapan panitia: {$item['committee_gear']}" : null,
            $item['note'] ? "Keterangan: {$item['note']}" : null,
        ])->filter()->implode("\n\n");
    }

    private function participantsText(array $item): string
    {
        return match ($item['type']) {
            'individual' => '1 orang per regu',
            'patrol' => $item['min'].' orang (seluruh anggota regu)',
            default => $item['min'].' orang per regu',
        };
    }
}

<?php

namespace App\Services;

use App\Enums\PatrolGenderEnum;
use App\Enums\RegistrationStatusEnum;
use App\Models\Assessment;
use App\Models\Patrol;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Pembuat Nomor Pendaftaran Peserta (NPP) regu: 2 digit (01, 02, dst.), diundi acak, dan putra serta putri
 * dinomori terpisah sehingga masing-masing dimulai dari 01
 */
class PatrolNumberService
{
    public const GENDERS = ['boys', 'girls'];

    /**
     * Format NPP dari kategori dan nomor urut
     */
    public static function format(int $sequence): string
    {
        return str_pad((string) $sequence, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Normalisasi masukan juri (mis. "1" atau " 01 ") menjadi bentuk baku NPP 2 digit
     */
    public static function normalize(string $input): string
    {
        $clean = trim($input);

        return preg_match('/^\d{1,3}$/', $clean) ? self::format((int) $clean) : strtoupper($clean);
    }

    /**
     * Regu terverifikasi yang berhak mendapat NPP pada kategori tertentu
     */
    public function eligiblePatrols(string $gender): Collection
    {
        return Patrol::where('gender_category', $gender)
            ->where('status', RegistrationStatusEnum::Verified)
            ->get();
    }

    /**
     * NPP tidak boleh diundi ulang bila sudah ada penilaian juri
     */
    public function hasAssessments(): bool
    {
        return Assessment::query()->exists();
    }

    /**
     * Membuat NPP. Tanpa $reshuffle hanya regu yang belum bernomor yang diberi nomor lanjutan;
     * dengan $reshuffle seluruh nomor diundi ulang (ditolak bila sudah ada penilaian).
     *
     * @return array<string, int> jumlah NPP yang diberikan per kategori
     */
    public function generate(bool $reshuffle = false): array
    {
        if ($reshuffle && $this->hasAssessments()) {
            throw new \RuntimeException('NPP tidak dapat diundi ulang karena penilaian juri sudah masuk.');
        }

        return DB::transaction(function () use ($reshuffle) {
            $result = [];

            foreach (self::GENDERS as $gender) {
                if ($reshuffle) {
                    Patrol::where('gender_category', $gender)->update(['npp' => null]);
                }

                $pending = $this->eligiblePatrols($gender)->whereNull('npp')->shuffle();
                $sequence = $this->lastSequence($gender);

                foreach ($pending as $patrol) {
                    $patrol->update(['npp' => self::format(++$sequence)]);
                }

                $result[$gender] = $pending->count();
            }

            return $result;
        });
    }

    /**
     * Nomor urut NPP terakhir pada kategori
     */
    private function lastSequence(string $gender): int
    {
        return (int) Patrol::where('gender_category', $gender)
            ->whereNotNull('npp')
            ->pluck('npp')
            ->map(fn (string $npp) => (int) $npp)
            ->max();
    }

    /**
     * Jumlah regu terverifikasi, sudah bernomor, dan belum bernomor per kategori
     *
     * @return array<string, array{eligible: int, numbered: int, pending: int}>
     */
    public function summary(): array
    {
        $summary = [];

        foreach (PatrolGenderEnum::cases() as $gender) {
            $eligible = $this->eligiblePatrols($gender->value);
            $numbered = $eligible->whereNotNull('npp')->count();

            $summary[$gender->value] = [
                'eligible' => $eligible->count(),
                'numbered' => $numbered,
                'pending' => $eligible->count() - $numbered,
            ];
        }

        return $summary;
    }
}

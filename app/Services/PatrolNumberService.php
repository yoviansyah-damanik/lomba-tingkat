<?php

namespace App\Services;

use App\Enums\PatrolGenderEnum;
use App\Enums\RegistrationStatusEnum;
use App\Models\Assessment;
use App\Models\Patrol;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Pembuat Nomor Pendaftaran Peserta (NPP) regu:
 * - Putra: LT-PA-{noUrut} (LT-PA-01, LT-PA-02, dst.)
 * - Putri: LT-PI-{noUrut} (LT-PI-01, LT-PI-02, dst.)
 * Diundi acak dan putra serta putri dinomori terpisah masing-masing dimulai dari 01.
 */
class PatrolNumberService
{
    public const GENDERS = ['boys', 'girls'];

    /**
     * Format NPP dari kategori dan nomor urut
     */
    public static function format(int $sequence, ?string $gender = null): string
    {
        $padded = str_pad((string) $sequence, 2, '0', STR_PAD_LEFT);

        if ($gender) {
            $g = strtolower(trim($gender));
            if ($g === 'boys' || $g === 'male' || $g === 'pa' || $g === PatrolGenderEnum::Boys->value) {
                return 'LT-PA-' . $padded;
            }
            if ($g === 'girls' || $g === 'female' || $g === 'pi' || $g === PatrolGenderEnum::Girls->value) {
                return 'LT-PI-' . $padded;
            }
        }

        return $padded;
    }

    /**
     * Normalisasi masukan juri (mis. "1", " 01 ", "pa-1", "LT-PA-01") menjadi bentuk baku NPP
     */
    public static function normalize(string $input, ?string $gender = null): string
    {
        $clean = trim($input);

        if ($clean === '') {
            return '';
        }

        // Jika sudah berbentuk LT-PA-01, PA-01, LT-PI-02, PI-2, dll.
        if (preg_match('/^(?:LT-)?(PA|PI)-?(\d+)$/i', $clean, $matches)) {
            $prefix = strtoupper($matches[1]);
            $seq = (int) $matches[2];

            return sprintf('LT-%s-%02d', $prefix, $seq);
        }

        // Jika hanya angka murni (mis. "1" atau "01")
        if (preg_match('/^\d+$/', $clean)) {
            $seq = (int) $clean;
            if ($gender) {
                return self::format($seq, $gender);
            }

            return str_pad((string) $seq, 2, '0', STR_PAD_LEFT);
        }

        return strtoupper($clean);
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
                    $patrol->update(['npp' => self::format(++$sequence, $gender)]);
                }

                $result[$gender] = $pending->count();
            }

            return $result;
        });
    }

    /**
     * Nomor urut NPP terakhir pada kategori (mengekstrak angka dari akhiran string NPP)
     */
    private function lastSequence(string $gender): int
    {
        return (int) Patrol::where('gender_category', $gender)
            ->whereNotNull('npp')
            ->pluck('npp')
            ->map(function (string $npp) {
                return preg_match('/(\d+)$/', trim($npp), $matches) ? (int) $matches[1] : 0;
            })
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

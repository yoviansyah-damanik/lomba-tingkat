<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Patrol;
use App\Services\PdfService;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Controller cetak dokumen Nomor Punggung Regu (Bibs A5) berdasarkan Nomor Peserta Perlombaan (NPP)
 */
class PatrolBibPrintController extends Controller
{
    /**
     * Cetak berkas Nomor Punggung Regu dalam format A5 Landscape
     */
    public function printBibs(Request $request, PdfService $pdf, QrCodeService $qrCodeService, ?string $patrolId = null): Response
    {
        $user = $request->user();
        $targetPatrolId = $patrolId ?? $request->route('patrolId') ?? $request->query('patrol_id');
        $gender = $request->query('gender');

        // Jika mencetak nomor punggung regu tunggal
        if ($targetPatrolId) {
            $patrol = Patrol::with(['scoutGroup'])->find($targetPatrolId);

            abort_if(! $patrol, 404, 'Data regu tidak ditemukan.');

            // Batasi akses pangkalan gugus depan hanya pada regu miliknya
            if ($user && $user->isScoutGroup()) {
                $scoutGroup = $user->scoutGroup;
                abort_if(! $scoutGroup || $patrol->scout_group_id !== $scoutGroup->id, 403, 'Anda tidak berwenang mencetak nomor punggung regu pangkalan lain.');
            }

            abort_if(
                ! $patrol->npp,
                404,
                'Regu "'.$patrol->patrol_name.'" belum memiliki Nomor Peserta Perlombaan (NPP). Nomor punggung hanya dapat dicetak setelah masa pendaftaran ditutup dan panitia melakukan undi/generate NPP.'
            );

            $patrols = collect([$patrol]);
        } else {
            // Pencetakan massal (seluruh regu atau filter per kategori putra/putri)
            $query = Patrol::with(['scoutGroup'])->whereNotNull('npp');

            if ($user && $user->isScoutGroup()) {
                $scoutGroup = $user->scoutGroup;
                abort_if(! $scoutGroup, 403, 'Profil pangkalan tidak ditemukan.');
                $query->where('scout_group_id', $scoutGroup->id);
            }

            if ($gender && in_array($gender, ['boys', 'girls'], true)) {
                $query->where('gender_category', $gender);
            }

            $patrols = $query->orderBy('gender_category')->orderBy('npp')->get();

            abort_if(
                $patrols->isEmpty(),
                404,
                'Belum ada regu dengan Nomor Peserta Perlombaan (NPP) yang siap dicetak. Pastikan nomor NPP telah digenerate pada menu Generate NPP.'
            );
        }

        $event = Event::where('is_active', true)->first();

        $items = $patrols->map(function (Patrol $patrol) use ($qrCodeService) {
            $verifyUrl = route('leaderboard');
            $qrDataUri = $qrCodeService->generateDataUri($verifyUrl, 140);

            return [
                'patrol' => $patrol,
                'npp' => (string) $patrol->npp,
                'is_boys' => $patrol->isBoys(),
                'gender_label' => $patrol->isBoys() ? 'PUTRA' : 'PUTRI',
                'patrol_name' => $patrol->patrol_name,
                'school_name' => $patrol->scoutGroup?->school_name ?? 'Kwarcab Kota Padangsidimpuan',
                'group_number' => $patrol->group_number ?: ($patrol->scoutGroup?->group_number ?? '-'),
                'qr_data_uri' => $qrDataUri,
            ];
        });

        $filename = $patrols->count() === 1
            ? 'nomor-punggung-' . $patrols->first()->npp . '-' . \Illuminate\Support\Str::slug($patrols->first()->patrol_name) . '.pdf'
            : 'nomor-punggung-regu-' . ($gender ?: 'semua') . '.pdf';

        return $pdf->response(
            'reports.patrol-bibs-pdf',
            [
                'title' => 'Nomor Punggung Regu (NPP) — LT III 2026',
                'items' => $items,
                'event' => $event,
                'generatedAt' => now()->timezone('Asia/Jakarta'),
            ],
            $filename,
            $request->boolean('download'),
            'landscape',
            'A5',
            false // Tidak menggunakan footer dokumen standar agar nomor punggung bersih
        );
    }
}


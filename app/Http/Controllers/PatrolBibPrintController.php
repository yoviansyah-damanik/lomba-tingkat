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
    public function printBibs(Request $request, PdfService $pdf, QrCodeService $qrCodeService): Response
    {
        $user = $request->user();
        $patrolId = $request->query('patrol_id');
        $gender = $request->query('gender');

        $query = Patrol::with(['scoutGroup'])
            ->whereNotNull('npp');

        // Batasi akses pangkalan gugus depan hanya pada regu miliknya
        if ($user && $user->isScoutGroup()) {
            $scoutGroup = $user->scoutGroup;
            abort_if(!$scoutGroup, 403, 'Profil pangkalan tidak ditemukan.');
            $query->where('scout_group_id', $scoutGroup->id);
        }

        if ($patrolId) {
            $query->where('id', $patrolId);
        } elseif ($gender && in_array($gender, ['boys', 'girls'], true)) {
            $query->where('gender_category', $gender);
        }

        $patrols = $query->orderBy('gender_category')->orderBy('npp')->get();

        abort_if($patrols->isEmpty(), 404, 'Data regu dengan NPP tidak ditemukan.');

        $event = Event::where('is_active', true)->first();

        $items = $patrols->map(function (Patrol $patrol) use ($qrCodeService) {
            $verifyUrl = route('leaderboard');
            $qrDataUri = $qrCodeService->generateDataUri($verifyUrl, 140);

            return [
                'patrol' => $patrol,
                'npp' => str_pad((string) $patrol->npp, 2, '0', STR_PAD_LEFT),
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


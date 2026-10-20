<?php

namespace App\Http\Controllers;

use App\Models\Chaperone;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use App\Models\User;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Controller untuk pengelolaan, pencetakan, dan verifikasi Kartu Tanda Pengenal (ID Card Digital QR)
 */
class DigitalCardController extends Controller
{
    /**
     * Tampilkan ID card digital individual peserta
     */
    public function participantCard(Request $request, string $id, QrCodeService $qrService): View
    {
        $participant = Participant::with(['patrol.scoutGroup'])->findOrFail($id);
        $scoutGroup = $participant->patrol?->scoutGroup;

        // Otorisasi: Admin, Panitia, Dewan Juri, atau Gudep pemilik kontingen
        if ($request->user() && ! $request->user()->isAdmin() && ! $request->user()->isCommittee() && ! $request->user()->isJudge()) {
            if ($scoutGroup && $scoutGroup->user_id !== $request->user()->id) {
                abort(403, 'Anda tidak berhak mengakses ID Card peserta dari pangkalan lain.');
            }
        }

        $qrDataUri = $qrService->generateDataUri(route('digital-cards.verify', $participant->qr_token), 220);

        return view('digital-cards.participant', compact('participant', 'scoutGroup', 'qrDataUri'));
    }

    /**
     * Tampilkan ID card digital pembina pendamping
     */
    public function chaperoneCard(Request $request, string $id, QrCodeService $qrService): View
    {
        $chaperone = Chaperone::with('scoutGroup')->findOrFail($id);
        $scoutGroup = $chaperone->scoutGroup;

        if ($request->user() && ! $request->user()->isAdmin() && ! $request->user()->isCommittee() && ! $request->user()->isJudge()) {
            if ($scoutGroup && $scoutGroup->user_id !== $request->user()->id) {
                abort(403, 'Anda tidak berhak mengakses ID Card pendamping dari pangkalan lain.');
            }
        }

        $qrDataUri = $qrService->generateDataUri(route('digital-cards.verify', $chaperone->qr_token), 220);

        return view('digital-cards.chaperone', compact('chaperone', 'scoutGroup', 'qrDataUri'));
    }

    /**
     * Tampilkan ID card aparatur staf (Panitia / Dewan Juri)
     */
    public function staffCard(Request $request, string $id, QrCodeService $qrService): View
    {
        $staff = User::with('roles')->findOrFail($id);

        if (! $staff->isCommittee() && ! $staff->isJudge() && ! $staff->isAdmin()) {
            abort(404, 'Pengguna bukan aparatur kepanitiaan atau dewan juri.');
        }

        if ($request->user() && ! $request->user()->isAdmin() && $request->user()->id !== $staff->id) {
            abort(403, 'Anda hanya dapat mengakses ID Card staf milik sendiri.');
        }

        $qrDataUri = $qrService->generateDataUri(route('digital-cards.verify', $staff->qr_token), 220);

        return view('digital-cards.staff', compact('staff', 'qrDataUri'));
    }

    /**
     * Cetak kolektif seluruh ID Card regu (A4 Batch)
     */
    public function patrolBatchCards(Request $request, string $patrolId, QrCodeService $qrService): View
    {
        $patrol = Patrol::with(['scoutGroup', 'participants'])->findOrFail($patrolId);
        $scoutGroup = $patrol->scoutGroup;

        if ($request->user() && ! $request->user()->isAdmin() && ! $request->user()->isCommittee() && ! $request->user()->isJudge()) {
            if ($scoutGroup && $scoutGroup->user_id !== $request->user()->id) {
                abort(403, 'Akses ditolak.');
            }
        }

        // Generate QR code data URI untuk setiap peserta di regu
        $participantsWithQr = $patrol->participants->map(function ($p) use ($qrService) {
            $p->qrDataUri = $qrService->generateDataUri(route('digital-cards.verify', $p->qr_token), 180);
            return $p;
        });

        return view('digital-cards.patrol-batch', compact('patrol', 'scoutGroup', 'participantsWithQr'));
    }

    /**
     * Cetak kolektif seluruh ID Card Pembina Pendamping (A4 Batch)
     */
    public function chaperoneBatchCards(Request $request, string $scoutGroupId, QrCodeService $qrService): View
    {
        $scoutGroup = ScoutGroup::with('chaperones')->findOrFail($scoutGroupId);

        if ($request->user() && ! $request->user()->isAdmin() && ! $request->user()->isCommittee() && ! $request->user()->isJudge()) {
            if ($scoutGroup->user_id !== $request->user()->id) {
                abort(403, 'Akses ditolak.');
            }
        }

        $chaperonesWithQr = $scoutGroup->chaperones->map(function ($c) use ($qrService) {
            $c->qrDataUri = $qrService->generateDataUri(route('digital-cards.verify', $c->qr_token), 180);
            return $c;
        });

        return view('digital-cards.chaperone-batch', compact('scoutGroup', 'chaperonesWithQr'));
    }

    /**
     * Cetak & Simpan Kolektif Seluruh ID Card Kontingen Gudep (Semua Regu & Bindamping)
     */
    public function scoutGroupBatchCards(Request $request, string $scoutGroupId, QrCodeService $qrService): View
    {
        $scoutGroup = ScoutGroup::with(['patrols.participants', 'chaperones'])->findOrFail($scoutGroupId);

        if ($request->user() && ! $request->user()->isAdmin() && ! $request->user()->isCommittee() && ! $request->user()->isJudge()) {
            if ($scoutGroup->user_id !== $request->user()->id) {
                abort(403, 'Akses ditolak.');
            }
        }

        foreach ($scoutGroup->patrols as $patrol) {
            $patrol->participants->transform(function ($p) use ($qrService) {
                $p->qrDataUri = $qrService->generateDataUri(route('digital-cards.verify', $p->qr_token), 180);
                return $p;
            });
        }

        $scoutGroup->chaperones->transform(function ($c) use ($qrService) {
            $c->qrDataUri = $qrService->generateDataUri(route('digital-cards.verify', $c->qr_token), 180);
            return $c;
        });

        return view('digital-cards.scout-group-batch', compact('scoutGroup'));
    }


    /**
     * Halaman publik verifikasi akreditasi saat QR Code dipindai
     */
    public function verify(string $token, QrCodeService $qrService): View
    {
        $data = $qrService->resolveToken($token);

        $qrDataUri = $data ? $qrService->generateDataUri(route('digital-cards.verify', $token), 160) : null;

        return view('digital-cards.verify', [
            'data' => $data,
            'token' => $token,
            'qrDataUri' => $qrDataUri,
        ]);
    }
}

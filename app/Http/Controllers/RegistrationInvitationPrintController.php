<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\RegistrationInvitation;
use App\Services\PdfService;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Controller cetak dokumen berkas Kode Undangan Registrasi Gugus Depan
 */
class RegistrationInvitationPrintController extends Controller
{
    /**
     * Cetak berkas surat slip kode undangan (satuan atau seluruhnya)
     */
    public function printSlips(Request $request, PdfService $pdf, QrCodeService $qrCodeService): Response
    {
        $id = $request->query('id');

        $invitations = RegistrationInvitation::query()
            ->when($id, fn ($q) => $q->whereKey($id))
            ->orderBy('school_name')
            ->get();

        abort_if($invitations->isEmpty(), 404, 'Kode undangan tidak ditemukan.');

        $event = Event::where('is_active', true)->first();

        // Tambahkan QR Code data URI untuk setiap undangan
        $items = $invitations->map(function (RegistrationInvitation $invitation) use ($qrCodeService) {
            $registrationUrl = route('register.scout-group', ['code' => $invitation->code]);
            $qrDataUri = $qrCodeService->generateDataUri($registrationUrl, 120);

            return [
                'invitation' => $invitation,
                'qr_data_uri' => $qrDataUri,
                'registration_url' => $registrationUrl,
            ];
        });

        $filename = $invitations->count() === 1
            ? 'surat-kode-undangan-'.\Illuminate\Support\Str::slug($invitations->first()->school_name).'.pdf'
            : 'berkas-kode-undangan-seluruh-gudep.pdf';

        return $pdf->response(
            'reports.invitation-slips-pdf',
            [
                'title' => 'Surat Kode Undangan Registrasi Kontingen',
                'items' => $items,
                'event' => $event,
                'generatedAt' => now()->timezone('Asia/Jakarta'),
            ],
            $filename,
            $request->boolean('download')
        );
    }

    /**
     * Cetak satu berkas slip kode undangan pangkalan
     */
    public function printSingle(string $id, Request $request, PdfService $pdf, QrCodeService $qrCodeService): Response
    {
        $request->merge(['id' => $id]);

        return $this->printSlips($request, $pdf, $qrCodeService);
    }

    /**
     * Cetak rekapitulasi daftar kode undangan & lembar tanda terima distribusi
     */
    public function printRecap(Request $request, PdfService $pdf): Response
    {
        $invitations = RegistrationInvitation::with('scoutGroup:id,school_name')
            ->orderBy('school_name')
            ->get();

        $event = Event::where('is_active', true)->first();

        return $pdf->response(
            'reports.invitation-recap-pdf',
            [
                'title' => 'Rekapitulasi Kode Undangan Registrasi Kontingen',
                'invitations' => $invitations,
                'event' => $event,
                'generatedAt' => now()->timezone('Asia/Jakarta'),
            ],
            'rekap-kode-undangan-registrasi-gudep.pdf',
            $request->boolean('download')
        );
    }
}


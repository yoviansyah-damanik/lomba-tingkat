<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\PermissionRequest;
use App\Services\PdfService;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

/**
 * Surat izin (PDF) untuk permohonan izin beserta halaman verifikasi tanda tangan digital (QR)
 */
class PermissionLetterController extends Controller
{
    /**
     * Surat izin PDF dengan tanda tangan QR Ketua Panitia dan bindamping (penanggung jawab)
     */
    public function show(Request $request, string $id, PdfService $pdf, QrCodeService $qr): Response
    {
        $permission = PermissionRequest::with(['scoutGroup', 'members.participant.patrol', 'members.chaperone'])->findOrFail($id);
        $event = Event::first();

        $chaperones = $permission->members->pluck('chaperone')->filter()->values();
        $participants = $permission->members->pluck('participant')->filter()->values();
        $responsible = $chaperones->first();

        return $pdf->response('permissions.letter-pdf', [
            'permission' => $permission,
            'event' => $event,
            'participants' => $participants,
            'chaperones' => $chaperones,
            'responsible' => $responsible,
            // QR tanda tangan penanggung jawab selalu dibuat dari token bindamping (tidak bergantung status verifikasi gudep)
            'responsibleQr' => $responsible?->qr_token ? $qr->generateDataUri(route('digital-cards.verify', $responsible->qr_token), 90) : null,
            'chairQr' => $qr->generateDataUri(route('permission-letters.verify', $permission->id), 90),
            'chair' => $event?->signatory('committee') ?? ['name' => null, 'nta' => null],
            'issuedAt' => $permission->created_at->timezone('Asia/Jakarta'),
        ], 'Surat_Izin_'.Str::slug($permission->type->label()).'_'.$permission->created_at->format('Ymd').'.pdf', $request->boolean('download'));
    }
}

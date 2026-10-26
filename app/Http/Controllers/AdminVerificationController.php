<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\ScoutGroup;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

/**
 * Controller verifikasi pendaftaran kontingen khusus Administrator
 */
class AdminVerificationController extends Controller
{
    public function __construct(
        private readonly PdfService $pdf,
    ) {}

    /**
     * Cetak lembar formulir biodata resmi pembina pendamping (PDF)
     */
    public function chaperonePrint(Request $request, string $scoutGroupId, string $chaperoneId): Response
    {
        $scoutGroup = ScoutGroup::findOrFail($scoutGroupId);
        $chaperone = $scoutGroup->chaperones()->findOrFail($chaperoneId);
        $chaperone->setRelation('scoutGroup', $scoutGroup);

        $filename = 'Form_03_Biodata_'.Str::slug($chaperone->full_name).'.pdf';

        return $this->pdf->response(
            'portal.scout-group.chaperones.print',
            compact('scoutGroup', 'chaperone'),
            $filename,
            $request->boolean('download')
        );
    }

    /**
     * Cetak lembar formulir biodata resmi peserta lomba (PDF)
     */
    public function participantPrint(Request $request, string $scoutGroupId, string $participantId): Response
    {
        $scoutGroup = ScoutGroup::findOrFail($scoutGroupId);
        $participant = Participant::whereHas('patrol', function ($q) use ($scoutGroupId) {
            $q->where('scout_group_id', $scoutGroupId);
        })->with('patrol')->findOrFail($participantId);

        $filename = 'Form_02_Biodata_'.Str::slug($participant->full_name).'.pdf';

        return $this->pdf->response(
            'portal.scout-group.participants.print',
            compact('scoutGroup', 'participant'),
            $filename,
            $request->boolean('download')
        );
    }

    /**
     * Cetak formulir rekap pendaftaran kontingen (FORM : 01) (PDF)
     */
    public function form01Print(Request $request, string $scoutGroupId, string $patrolId): Response
    {
        $scoutGroup = ScoutGroup::with(['chaperones', 'patrols.participants'])->findOrFail($scoutGroupId);
        $patrol = $scoutGroup->patrols()->with('participants')->findOrFail($patrolId);
        $patrol->setRelation('scoutGroup', $scoutGroup);

        $chaperone = $patrol->chaperone;
        $orderedParticipants = $patrol->orderedParticipants();

        $filename = 'Form_01_Rekap_Regu_'.Str::slug($patrol->patrol_name).'.pdf';

        return $this->pdf->response(
            'portal.scout-group.patrols.form-01',
            compact('scoutGroup', 'patrol', 'chaperone', 'orderedParticipants'),
            $filename,
            $request->boolean('download')
        );
    }

    /**
     * Cetak dokumen resmi Bukti Registrasi Kontingen Gugusdepan (PDF) untuk Administrator
     */
    public function registrationCertificatePrint(Request $request, string $id): Response
    {
        $scoutGroup = ScoutGroup::with(['patrols.participants', 'chaperones', 'user'])->findOrFail($id);
        $filename = 'Bukti_Registrasi_'.Str::slug($scoutGroup->school_name).'.pdf';

        return $this->pdf->response(
            'admin.scout-groups.registration-certificate',
            compact('scoutGroup'),
            $filename,
            $request->boolean('download')
        );
    }
}

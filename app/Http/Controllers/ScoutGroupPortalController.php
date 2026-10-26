<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\ScoutGroup;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

/**
 * Dokumen cetak (PDF) portal kontingen Gugus Depan. Halaman portal adalah komponen Livewire (App\Livewire\Portal).
 */
class ScoutGroupPortalController extends Controller
{
    public function __construct(
        private readonly PdfService $pdf,
    ) {}

    /**
     * Gugus depan milik akun yang login (dibuat sebagai draf bila belum ada)
     */
    protected function getOrCreateScoutGroup(Request $request): ScoutGroup
    {
        return ScoutGroup::forUser($request->user());
    }

    /**
     * Cetak formulir rekap pendaftaran kontingen (FORM : 01) (PDF)
     */
    public function form01Print(Request $request, string $patrolId): Response
    {
        $scoutGroup = $this->getOrCreateScoutGroup($request);
        abort_unless($scoutGroup->canDownloadDocuments(), 403, ScoutGroup::DOWNLOAD_LOCKED_MESSAGE);

        $scoutGroup->load(['chaperones', 'patrols.participants']);
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
     * Cetak formulir resmi biodata peserta (FORM : 02) (PDF)
     */
    public function participantPrint(Request $request, string $id): Response
    {
        $scoutGroup = $this->getOrCreateScoutGroup($request);
        abort_unless($scoutGroup->canDownloadDocuments(), 403, ScoutGroup::DOWNLOAD_LOCKED_MESSAGE);

        $participant = Participant::whereHas('patrol', function ($q) use ($scoutGroup) {
            $q->where('scout_group_id', $scoutGroup->id);
        })->with('patrol')->findOrFail($id);

        $filename = 'Form_02_Biodata_'.Str::slug($participant->full_name).'.pdf';

        return $this->pdf->response(
            'portal.scout-group.participants.print',
            compact('scoutGroup', 'participant'),
            $filename,
            $request->boolean('download')
        );
    }

    /**
     * Cetak lembar formulir biodata resmi pembina pendamping (FORM : 03) (PDF)
     */
    public function chaperonePrint(Request $request, string $id): Response
    {
        $scoutGroup = $this->getOrCreateScoutGroup($request);
        abort_unless($scoutGroup->canDownloadDocuments(), 403, ScoutGroup::DOWNLOAD_LOCKED_MESSAGE);

        $chaperone = $scoutGroup->chaperones()->findOrFail($id);
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
     * Cetak dokumen resmi Bukti Registrasi Kontingen Gugusdepan (PDF)
     */
    public function registrationCertificatePrint(Request $request): Response
    {
        $scoutGroup = $this->getOrCreateScoutGroup($request);
        abort_unless($scoutGroup->canDownloadDocuments(), 403, ScoutGroup::DOWNLOAD_LOCKED_MESSAGE);

        $scoutGroup->load(['patrols.participants', 'chaperones', 'user']);
        $filename = 'Bukti_Registrasi_'.Str::slug($scoutGroup->school_name).'.pdf';

        return $this->pdf->response(
            'admin.scout-groups.registration-certificate',
            compact('scoutGroup'),
            $filename,
            $request->boolean('download')
        );
    }
}

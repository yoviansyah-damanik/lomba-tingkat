<?php

namespace App\Http\Controllers;

use App\Enums\RegistrationStatusEnum;
use App\Models\AuditLog;
use App\Models\Chaperone;
use App\Models\Participant;
use App\Models\ScoutGroup;
use App\Services\ContingentAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller verifikasi pendaftaran kontingen khusus Administrator
 */
class AdminVerificationController extends Controller
{
    /**
     * Antrean verifikasi seluruh kontingen pangkalan Gugus Depan
     */
    public function index(Request $request): View
    {
        $query = ScoutGroup::with(['user', 'patrols.participants', 'chaperones']);

        if ($request->filled('status')) {
            $query->where('verification_status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('school_name', 'like', '%'.$request->search.'%')
                    ->orWhere('group_number', 'like', '%'.$request->search.'%')
                    ->orWhere('registration_code', 'like', '%'.$request->search.'%');
            });
        }

        $scoutGroups = $query->latest()->paginate(10)->withQueryString();

        $statusCounts = [
            'all' => ScoutGroup::count(),
            'submitted' => ScoutGroup::where('verification_status', RegistrationStatusEnum::Submitted)->count(),
            'verified' => ScoutGroup::where('verification_status', RegistrationStatusEnum::Verified)->count(),
            'needs_revision' => ScoutGroup::where('verification_status', RegistrationStatusEnum::NeedsRevision)->count(),
            'rejected' => ScoutGroup::where('verification_status', RegistrationStatusEnum::Rejected)->count(),
        ];

        return view('admin.scout-groups.index', compact('scoutGroups', 'statusCounts'));
    }

    public function __construct(
        private readonly \App\Services\PdfService $pdf,
    ) {}

    /**
     * Cetak lembar formulir biodata resmi pembina pendamping (PDF)
     */
    public function chaperonePrint(Request $request, string $scoutGroupId, string $chaperoneId): \Illuminate\Http\Response
    {
        $scoutGroup = ScoutGroup::findOrFail($scoutGroupId);
        $chaperone = $scoutGroup->chaperones()->findOrFail($chaperoneId);
        $chaperone->setRelation('scoutGroup', $scoutGroup);

        $filename = 'Form_03_Biodata_' . \Illuminate\Support\Str::slug($chaperone->full_name) . '.pdf';

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
    public function participantPrint(Request $request, string $scoutGroupId, string $participantId): \Illuminate\Http\Response
    {
        $scoutGroup = ScoutGroup::findOrFail($scoutGroupId);
        $participant = Participant::whereHas('patrol', function ($q) use ($scoutGroupId) {
            $q->where('scout_group_id', $scoutGroupId);
        })->with('patrol')->findOrFail($participantId);

        $filename = 'Form_02_Biodata_' . \Illuminate\Support\Str::slug($participant->full_name) . '.pdf';

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
    public function form01Print(Request $request, string $scoutGroupId, string $patrolId): \Illuminate\Http\Response
    {
        $scoutGroup = ScoutGroup::with(['chaperones', 'patrols.participants'])->findOrFail($scoutGroupId);
        $patrol = $scoutGroup->patrols()->with('participants')->findOrFail($patrolId);
        $patrol->setRelation('scoutGroup', $scoutGroup);

        $chaperone = $patrol->isBoys()
            ? $scoutGroup->chaperones->firstWhere('gender', 'male')
            : $scoutGroup->chaperones->firstWhere('gender', 'female');
        $orderedParticipants = $patrol->orderedParticipants();

        $filename = 'Form_01_Rekap_Regu_' . \Illuminate\Support\Str::slug($patrol->patrol_name) . '.pdf';

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
    public function registrationCertificatePrint(Request $request, string $id): \Illuminate\Http\Response
    {
        $scoutGroup = ScoutGroup::with(['patrols.participants', 'chaperones', 'user'])->findOrFail($id);
        $filename = 'Bukti_Registrasi_' . \Illuminate\Support\Str::slug($scoutGroup->school_name) . '.pdf';

        return $this->pdf->response(
            'admin.scout-groups.registration-certificate',
            compact('scoutGroup'),
            $filename,
            $request->boolean('download')
        );
    }

    /**
     * Halaman detail lengkap data peserta untuk Administrator / Panitia / Juri
     */
    public function participantShow(Request $request, string $id, ?string $participantId = null): View
    {
        $targetId = $participantId ?? $id;
        $participant = Participant::with(['patrol.scoutGroup'])->findOrFail($targetId);
        $scoutGroup = $participant->patrol?->scoutGroup;

        return view('admin.participants.show', compact('participant', 'scoutGroup'));
    }

    /**
     * Halaman detail lengkap data pembina pendamping untuk Administrator / Panitia / Juri
     */
    public function chaperoneShow(Request $request, string $id, ?string $chaperoneId = null): View
    {
        $targetId = $chaperoneId ?? $id;
        $chaperone = Chaperone::with('scoutGroup')->findOrFail($targetId);
        $scoutGroup = $chaperone->scoutGroup;

        return view('admin.chaperones.show', compact('chaperone', 'scoutGroup'));
    }
}

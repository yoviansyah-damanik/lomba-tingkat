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

    /**
     * Halaman detail verifikasi berkas kontingen pangkalan
     */
    public function show(string $id): View
    {
        $scoutGroup = ScoutGroup::with(['patrols.participants', 'chaperones', 'verifier', 'user'])->findOrFail($id);

        return view('admin.scout-groups.show', compact('scoutGroup'));
    }

    /**
     * Menyetujui (Approve & Verify) pendaftaran kontingen
     */
    public function verify(Request $request, string $id): RedirectResponse
    {
        if ($request->user()->isSuperadmin()) {
            return redirect()->route('admin.scout-groups.show', $id)
                ->with('error', 'Superadmin tidak berwenang melakukan aksi verifikasi. Gunakan fitur Batalkan Verifikasi jika diperlukan.');
        }

        $scoutGroup = ScoutGroup::findOrFail($id);

        if ($scoutGroup->verification_status === RegistrationStatusEnum::Verified) {
            return redirect()->route('admin.scout-groups.show', $scoutGroup->id)
                ->with('error', 'Status kontingen telah terverifikasi. Keputusan verifikasi tidak dapat diubah.');
        }

        $scoutGroup->update([
            'verification_status' => RegistrationStatusEnum::Verified,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'revision_notes' => null,
        ]);

        $scoutGroup->patrols()->update(['status' => RegistrationStatusEnum::Verified]);
        Participant::whereIn('patrol_id', $scoutGroup->patrols()->pluck('id'))
            ->update(['status' => RegistrationStatusEnum::Verified]);
        $scoutGroup->chaperones()->update(['status' => RegistrationStatusEnum::Verified]);

        $accounts = app(ContingentAccountService::class)->provision($scoutGroup);

        return redirect()->route('admin.scout-groups.show', $scoutGroup->id)
            ->with('success', "Pendaftaran kontingen {$scoutGroup->school_name} berhasil diverifikasi dan disetujui. Akun dibuat: {$accounts['chaperones']} bindamping, {$accounts['participants']} peserta.");
    }

    /**
     * Meminta perbaikan data (Needs Revision) kepada Gugus Depan
     */
    public function requestRevision(Request $request, string $id): RedirectResponse
    {
        if ($request->user()->isSuperadmin()) {
            return redirect()->route('admin.scout-groups.show', $id)
                ->with('error', 'Superadmin tidak berwenang melakukan aksi verifikasi. Gunakan fitur Batalkan Verifikasi jika diperlukan.');
        }

        $scoutGroup = ScoutGroup::findOrFail($id);

        if ($scoutGroup->verification_status === RegistrationStatusEnum::Verified) {
            return redirect()->route('admin.scout-groups.show', $scoutGroup->id)
                ->with('error', 'Status kontingen telah terverifikasi. Keputusan verifikasi tidak dapat diubah.');
        }

        $validated = $request->validate([
            'revision_notes' => ['required', 'string', 'min:5'],
        ]);

        $scoutGroup->update([
            'verification_status' => RegistrationStatusEnum::NeedsRevision,
            'revision_notes' => $validated['revision_notes'],
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        $scoutGroup->patrols()->update(['status' => RegistrationStatusEnum::NeedsRevision]);
        Participant::whereIn('patrol_id', $scoutGroup->patrols()->pluck('id'))
            ->update(['status' => RegistrationStatusEnum::NeedsRevision]);
        $scoutGroup->chaperones()->update(['status' => RegistrationStatusEnum::NeedsRevision]);

        return redirect()->route('admin.scout-groups.show', $scoutGroup->id)
            ->with('warning', "Pendaftaran {$scoutGroup->school_name} dikembalikan ke Gugus Depan dengan catatan perbaikan.");
    }

    /**
     * Menolak pendaftaran kontingen (Reject)
     */
    public function reject(Request $request, string $id): RedirectResponse
    {
        if ($request->user()->isSuperadmin()) {
            return redirect()->route('admin.scout-groups.show', $id)
                ->with('error', 'Superadmin tidak berwenang melakukan aksi verifikasi. Gunakan fitur Batalkan Verifikasi jika diperlukan.');
        }

        $scoutGroup = ScoutGroup::findOrFail($id);

        if ($scoutGroup->verification_status === RegistrationStatusEnum::Verified) {
            return redirect()->route('admin.scout-groups.show', $scoutGroup->id)
                ->with('error', 'Status kontingen telah terverifikasi. Keputusan verifikasi tidak dapat diubah.');
        }

        $validated = $request->validate([
            'revision_notes' => ['required', 'string', 'min:5'],
        ]);

        $scoutGroup->update([
            'verification_status' => RegistrationStatusEnum::Rejected,
            'revision_notes' => $validated['revision_notes'],
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        $scoutGroup->patrols()->update(['status' => RegistrationStatusEnum::Rejected]);
        Participant::whereIn('patrol_id', $scoutGroup->patrols()->pluck('id'))
            ->update(['status' => RegistrationStatusEnum::Rejected]);
        $scoutGroup->chaperones()->update(['status' => RegistrationStatusEnum::Rejected]);

        return redirect()->route('admin.scout-groups.show', $scoutGroup->id)
            ->with('error', "Pendaftaran {$scoutGroup->school_name} telah ditolak.");
    }

    /**
     * Membatalkan verifikasi kontingen dan mencabut seluruh akun digital (Khusus Superadmin)
     */
    public function cancelVerification(Request $request, string $id): RedirectResponse
    {
        if (! $request->user()->isSuperadmin()) {
            abort(403, 'Hanya Superadmin yang berwenang membatalkan verifikasi kontingen.');
        }

        $validated = $request->validate([
            'cancellation_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $scoutGroup = ScoutGroup::with(['patrols.participants', 'chaperones'])->findOrFail($id);

        if ($scoutGroup->verification_status !== RegistrationStatusEnum::Verified) {
            return redirect()->route('admin.scout-groups.show', $scoutGroup->id)
                ->with('error', 'Hanya kontingen dengan status terverifikasi yang dapat dibatalkan verifikasinya.');
        }

        // 1. Cabut dan hapus seluruh akun digital peserta dan bindamping
        $revoked = app(ContingentAccountService::class)->revokeAccounts($scoutGroup);

        $reason = ! empty($validated['cancellation_reason'])
            ? 'Verifikasi dibatalkan oleh Superadmin: ' . $validated['cancellation_reason']
            : 'Verifikasi dibatalkan oleh Superadmin.';

        // 2. Batalkan status verifikasi pada kontingen, regu, peserta, dan bindamping
        $scoutGroup->update([
            'verification_status' => RegistrationStatusEnum::NeedsRevision,
            'revision_notes' => $reason,
            'verified_by' => null,
            'verified_at' => null,
        ]);

        $scoutGroup->patrols()->update(['status' => RegistrationStatusEnum::NeedsRevision]);
        Participant::whereIn('patrol_id', $scoutGroup->patrols()->pluck('id'))
            ->update(['status' => RegistrationStatusEnum::NeedsRevision]);
        $scoutGroup->chaperones()->update(['status' => RegistrationStatusEnum::NeedsRevision]);

        AuditLog::log(
            action: 'contingent_verification_cancelled',
            modelType: ScoutGroup::class,
            modelId: $scoutGroup->id,
            oldValues: ['verification_status' => RegistrationStatusEnum::Verified->value],
            newValues: ['verification_status' => RegistrationStatusEnum::NeedsRevision->value],
            reason: $reason
        );

        return redirect()->route('admin.scout-groups.show', $scoutGroup->id)
            ->with('warning', "Verifikasi kontingen {$scoutGroup->school_name} berhasil dibatalkan. Seluruh akun peserta ({$revoked['participants']}) dan bindamping ({$revoked['chaperones']}) telah dicabut dan status kontingen dikembalikan.");
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
        $scoutGroup = ScoutGroup::with(['patrols.participants', 'chaperones', 'user', 'verifier'])->findOrFail($id);
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

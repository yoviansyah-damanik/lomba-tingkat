<?php

namespace App\Http\Controllers;

use App\Enums\RegistrationStatusEnum;
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
        $scoutGroup = ScoutGroup::with(['patrols.participants', 'chaperones', 'verifier'])->findOrFail($id);

        return view('admin.scout-groups.show', compact('scoutGroup'));
    }

    /**
     * Menyetujui (Approve & Verify) pendaftaran kontingen
     */
    public function verify(Request $request, string $id): RedirectResponse
    {
        $scoutGroup = ScoutGroup::findOrFail($id);

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
        $validated = $request->validate([
            'revision_notes' => ['required', 'string', 'min:5'],
        ]);

        $scoutGroup = ScoutGroup::findOrFail($id);

        $scoutGroup->update([
            'verification_status' => RegistrationStatusEnum::NeedsRevision,
            'revision_notes' => $validated['revision_notes'],
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        $scoutGroup->patrols()->update(['status' => RegistrationStatusEnum::NeedsRevision]);

        return redirect()->route('admin.scout-groups.show', $scoutGroup->id)
            ->with('warning', "Pendaftaran {$scoutGroup->school_name} dikembalikan ke Gugus Depan dengan catatan perbaikan.");
    }

    /**
     * Menolak pendaftaran kontingen (Reject)
     */
    public function reject(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'revision_notes' => ['required', 'string', 'min:5'],
        ]);

        $scoutGroup = ScoutGroup::findOrFail($id);

        $scoutGroup->update([
            'verification_status' => RegistrationStatusEnum::Rejected,
            'revision_notes' => $validated['revision_notes'],
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        return redirect()->route('admin.scout-groups.show', $scoutGroup->id)
            ->with('error', "Pendaftaran {$scoutGroup->school_name} telah ditolak.");
    }

    /**
     * Cetak lembar formulir biodata resmi pembina pendamping
     */
    public function chaperonePrint(string $scoutGroupId, string $chaperoneId): View
    {
        $scoutGroup = ScoutGroup::findOrFail($scoutGroupId);
        abort_unless($scoutGroup->canDownloadDocuments(), 403, ScoutGroup::DOWNLOAD_LOCKED_MESSAGE);

        $chaperone = $scoutGroup->chaperones()->findOrFail($chaperoneId);

        return view('portal.scout-group.chaperones.print', compact('scoutGroup', 'chaperone'));
    }

    /**
     * Cetak lembar formulir biodata resmi peserta lomba
     */
    public function participantPrint(string $scoutGroupId, string $participantId): View
    {
        $scoutGroup = ScoutGroup::findOrFail($scoutGroupId);
        abort_unless($scoutGroup->canDownloadDocuments(), 403, ScoutGroup::DOWNLOAD_LOCKED_MESSAGE);

        $participant = Participant::whereHas('patrol', function ($q) use ($scoutGroupId) {
            $q->where('scout_group_id', $scoutGroupId);
        })->with('patrol')->findOrFail($participantId);

        return view('portal.scout-group.participants.print', compact('scoutGroup', 'participant'));
    }

    /**
     * Cetak formulir rekap pendaftaran kontingen (FORM : 01)
     */
    public function form01Print(string $scoutGroupId, string $patrolId): View
    {
        $scoutGroup = ScoutGroup::with(['chaperones', 'patrols'])->findOrFail($scoutGroupId);
        abort_unless($scoutGroup->canDownloadDocuments(), 403, ScoutGroup::DOWNLOAD_LOCKED_MESSAGE);

        $patrol = $scoutGroup->patrols()->with('participants')->findOrFail($patrolId);
        $chaperone = $patrol->chaperone();
        $orderedParticipants = $patrol->orderedParticipants();

        return view('portal.scout-group.patrols.form-01', compact('scoutGroup', 'patrol', 'chaperone', 'orderedParticipants'));
    }
}

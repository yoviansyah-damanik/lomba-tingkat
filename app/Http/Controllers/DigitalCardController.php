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
use Illuminate\Support\Str;
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

        // Otorisasi: Admin, Panitia, Dewan Juri, atau Gudep pemilik kontingen (setelah diverifikasi)
        if ($request->user() && ! $request->user()->isAdmin() && ! $request->user()->isCommittee() && ! $request->user()->isJudge()) {
            if ($scoutGroup && $scoutGroup->user_id !== $request->user()->id) {
                abort(403, 'Anda tidak berhak mengakses ID Card peserta dari pangkalan lain.');
            }
        }

        $this->ensureDownloadable($request, $scoutGroup);

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

        $this->ensureDownloadable($request, $scoutGroup);

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
     * Tampilkan seluruh ID Card regu dan simpan sebagai ZIP berisi PNG tiap anggota
     */
    public function patrolBatchCards(Request $request, string $patrolId, QrCodeService $qrService): View
    {
        $patrol = Patrol::with(['scoutGroup', 'participants'])->findOrFail($patrolId);
        $scoutGroup = $patrol->scoutGroup;

        $this->authorizeScoutGroup($request, $scoutGroup);

        $cards = $patrol->orderedParticipants()->map(fn ($p) => $this->participantItem($p, $patrol, $qrService));

        return view('digital-cards.batch', [
            'title' => 'ID Card Regu '.$patrol->patrol_name,
            'subtitle' => $scoutGroup->school_name,
            'zipName' => 'KARTU_REGU_'.Str::slug($scoutGroup->school_name.' '.$patrol->patrol_name),
            'emptyMessage' => 'Belum ada anggota terdaftar dalam regu ini.',
            'backUrl' => 'javascript:window.history.back()',
            'scoutGroup' => $scoutGroup,
            'cards' => $cards->values(),
        ]);
    }

    /**
     * Rute admin (membawa scoutGroupId): parameter rute diteruskan berurutan sehingga perlu penerus khusus
     */
    public function adminPatrolBatchCards(Request $request, string $scoutGroupId, string $patrolId, QrCodeService $qrService): View
    {
        return $this->patrolBatchCards($request, $patrolId, $qrService);
    }

    /**
     * Tampilkan seluruh ID Card Pembina Pendamping dan simpan sebagai ZIP
     */
    public function chaperoneBatchCards(Request $request, string $scoutGroupId, QrCodeService $qrService): View
    {
        $scoutGroup = ScoutGroup::with('chaperones')->findOrFail($scoutGroupId);

        $this->authorizeScoutGroup($request, $scoutGroup);

        $cards = $scoutGroup->chaperones->map(fn ($c) => $this->chaperoneItem($c, $scoutGroup, $qrService));

        return view('digital-cards.batch', [
            'title' => 'ID Card Pembina Pendamping',
            'subtitle' => $scoutGroup->school_name,
            'zipName' => 'KARTU_BINDAMPING_'.Str::slug($scoutGroup->school_name),
            'emptyMessage' => 'Belum ada pembina pendamping terdaftar.',
            'backUrl' => 'javascript:window.history.back()',
            'scoutGroup' => $scoutGroup,
            'cards' => $cards->values(),
        ]);
    }

    /**
     * Tampilkan seluruh ID Card kontingen (semua regu dan bindamping); ZIP dipisah subfolder per regu
     */
    public function scoutGroupBatchCards(Request $request, string $scoutGroupId, QrCodeService $qrService): View
    {
        $scoutGroup = ScoutGroup::with(['patrols.participants', 'chaperones'])->findOrFail($scoutGroupId);

        $this->authorizeScoutGroup($request, $scoutGroup);

        $cards = collect();

        foreach ($scoutGroup->patrols as $patrol) {
            foreach ($patrol->orderedParticipants() as $participant) {
                $cards->push($this->participantItem($participant, $patrol, $qrService));
            }
        }

        foreach ($scoutGroup->chaperones as $chaperone) {
            $cards->push($this->chaperoneItem($chaperone, $scoutGroup, $qrService));
        }

        return view('digital-cards.batch', [
            'title' => 'ID Card Kontingen',
            'subtitle' => $scoutGroup->school_name.' • '.$scoutGroup->patrols->count().' regu, '.$scoutGroup->chaperones->count().' pembina',
            'zipName' => 'KARTU_KONTINGEN_'.Str::slug($scoutGroup->school_name),
            'emptyMessage' => 'Belum ada kartu anggota atau pembina yang terdaftar dalam kontingen ini.',
            'backUrl' => 'javascript:window.history.back()',
            'scoutGroup' => $scoutGroup,
            'cards' => $cards,
        ]);
    }

    /**
     * Akses batch: Admin, Panitia, Dewan Juri, atau Gudep pemilik kontingen
     */
    protected function authorizeScoutGroup(Request $request, ?ScoutGroup $scoutGroup): void
    {
        $user = $request->user();

        if ($user && ! $user->isAdmin() && ! $user->isCommittee() && ! $user->isJudge() && $scoutGroup && $scoutGroup->user_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        $this->ensureDownloadable($request, $scoutGroup);
    }

    /**
     * ID Card peserta/bindamping hanya dapat diakses setelah kontingen diverifikasi (berlaku untuk semua pengguna)
     */
    protected function ensureDownloadable(Request $request, ?ScoutGroup $scoutGroup): void
    {
        abort_unless($scoutGroup?->canDownloadDocuments(), 403, ScoutGroup::DOWNLOAD_LOCKED_MESSAGE);
    }

    /**
     * Data satu kartu peserta untuk tampilan batch
     */
    protected function participantItem(Participant $participant, Patrol $patrol, QrCodeService $qrService): array
    {
        $isBoys = $patrol->isBoys();

        return [
            'group' => 'Regu '.$patrol->patrol_name.' ('.($isBoys ? 'Putra' : 'Putri').')',
            'theme' => $isBoys ? 'boys' : 'girls',
            'patrol_name' => $patrol->patrol_name,
            'name' => $participant->full_name,
            'id_number' => $participant->id_number ?: 'ID-PESERTA',
            'scout_id' => $participant->scout_id,
            'photo_path' => $participant->photo_path,
            'qr_token' => $participant->qr_token,
            'qrDataUri' => $qrService->generateDataUri(route('digital-cards.verify', $participant->qr_token), 180),
            'role' => $participant->isLeader() ? 'leader' : ($participant->isAssistantLeader() ? 'assistant_leader' : 'member'),
            'category_label' => $isBoys ? 'PESERTA PENGGALANG PUTRA' : 'PESERTA PENGGALANG PUTRI',
            'group_number' => $patrol->group_number,
            'contact' => $participant->phone ?: ($participant->parent_phone ?: '-'),
        ];
    }

    /**
     * Data satu kartu bindamping untuk tampilan batch
     */
    protected function chaperoneItem(Chaperone $chaperone, ScoutGroup $scoutGroup, QrCodeService $qrService): array
    {
        $isMale = $chaperone->gender === 'male';

        return [
            'group' => 'Pembina Pendamping',
            'theme' => $isMale ? 'chaperone-male' : 'chaperone-female',
            'patrol_name' => null,
            'name' => $chaperone->full_name,
            'id_number' => $chaperone->id_number ?: 'ID-BINDAMPING',
            'scout_id' => $chaperone->scout_id,
            'photo_path' => $chaperone->photo_path,
            'qr_token' => $chaperone->qr_token,
            'qrDataUri' => $qrService->generateDataUri(route('digital-cards.verify', $chaperone->qr_token), 180),
            'role' => null,
            'category_label' => 'BINDAMPING '.($isMale ? 'PUTRA' : 'PUTRI'),
            'group_number' => $chaperone->group_number ?: ($scoutGroup->group_number ?? '-'),
            'contact' => $chaperone->phone_number ?: '-',
        ];
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

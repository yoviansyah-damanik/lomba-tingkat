<?php

namespace App\Http\Controllers;

use App\Enums\RegistrationStatusEnum;
use App\Models\Chaperone;
use App\Models\Event;
use App\Models\Participant;
use App\Models\User;
use App\Services\CertificateService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Controller untuk pratinjau dan pengunduhan berkas e-sertifikat resmi
 */
class CertificateController extends Controller
{
    /**
     * Pratinjau contoh sertifikat berdasarkan peran (khusus Administrator)
     */
    public function previewSample(Request $request, string $role, CertificateService $certificateService): Response
    {
        $event = Event::where('is_active', true)->first() ?? Event::first();
        if (!$event) {
            abort(404, 'Data kegiatan tidak ditemukan.');
        }

        if (!array_key_exists($role, CertificateService::ROLES)) {
            $role = CertificateService::ROLE_PARTICIPANT;
        }

        $sample = $certificateService->getSampleRecipient($role, $event);
        $download = $request->boolean('download');

        return $certificateService->responsePdf($event, $role, $sample, $download);
    }

    /**
     * Unduh e-sertifikat peserta terverifikasi
     */
    public function downloadParticipant(Request $request, Participant $participant, CertificateService $certificateService): Response
    {
        $event = Event::where('is_active', true)->first() ?? Event::first();
        $user = $request->user();

        // Pengecekan publikasi sertifikat (admin dapat bypass)
        if (!$user?->isAdmin() && !$event?->isCertificatePublished()) {
            abort(403, 'Penerbitan e-sertifikat kegiatan belum dibuka oleh panitia pelaksana.');
        }

        // Pengecekan status verifikasi peserta
        if ($participant->status !== RegistrationStatusEnum::Verified) {
            abort(403, 'E-sertifikat hanya dapat diterbitkan bagi peserta yang telah berstatus Terverifikasi.');
        }

        // Hak akses: admin, pangkalan gudep pemilik, bindamping regu, atau peserta itu sendiri
        if (!$this->canAccessParticipantCertificate($user, $participant)) {
            abort(403, 'Anda tidak berwenang mengunduh sertifikat peserta ini.');
        }

        $data = $certificateService->recipientDataForParticipant($participant, $event);
        $download = $request->boolean('download', true);

        return $certificateService->responsePdf($event, CertificateService::ROLE_PARTICIPANT, $data, $download);
    }

    /**
     * Unduh e-sertifikat pembina pendamping terverifikasi
     */
    public function downloadChaperone(Request $request, Chaperone $chaperone, CertificateService $certificateService): Response
    {
        $event = Event::where('is_active', true)->first() ?? Event::first();
        $user = $request->user();

        if (!$user?->isAdmin() && !$event?->isCertificatePublished()) {
            abort(403, 'Penerbitan e-sertifikat kegiatan belum dibuka oleh panitia pelaksana.');
        }

        if ($chaperone->status !== RegistrationStatusEnum::Verified) {
            abort(403, 'E-sertifikat hanya dapat diterbitkan bagi pembina yang telah berstatus Terverifikasi.');
        }

        if (!$this->canAccessChaperoneCertificate($user, $chaperone)) {
            abort(403, 'Anda tidak berwenang mengunduh sertifikat pembina ini.');
        }

        $data = $certificateService->recipientDataForChaperone($chaperone, $event);
        $download = $request->boolean('download', true);

        return $certificateService->responsePdf($event, CertificateService::ROLE_CHAPERONE, $data, $download);
    }

    /**
     * Unduh e-sertifikat aparatur (Panitia atau Dewan Juri)
     */
    public function downloadOfficial(Request $request, User $official, string $role, CertificateService $certificateService): Response
    {
        $event = Event::where('is_active', true)->first() ?? Event::first();
        $currentUser = $request->user();

        if (!$currentUser?->isAdmin() && !$event?->isCertificatePublished()) {
            abort(403, 'Penerbitan e-sertifikat kegiatan belum dibuka oleh panitia pelaksana.');
        }

        if (!$official->is_active) {
            abort(403, 'E-sertifikat hanya diterbitkan bagi akun aparatur yang berstatus aktif.');
        }

        if (!$currentUser?->isAdmin() && $currentUser?->id !== $official->id) {
            abort(403, 'Anda tidak berwenang mengunduh sertifikat aparatur ini.');
        }

        if ($role === CertificateService::ROLE_JUDGE && !$official->isJudge()) {
            abort(404, 'Pengguna tidak memiliki peran Dewan Juri.');
        }

        if ($role === CertificateService::ROLE_COMMITTEE && !$official->isCommittee()) {
            abort(404, 'Pengguna tidak memiliki peran Panitia.');
        }

        $data = $certificateService->recipientDataForOfficial($official, $role, $event);
        $download = $request->boolean('download', true);

        return $certificateService->responsePdf($event, $role, $data, $download);
    }

    /**
     * Mengunduh sertifikat pribadi akun yang sedang login
     */
    public function myCertificate(Request $request, CertificateService $certificateService): Response
    {
        $event = Event::where('is_active', true)->first() ?? Event::first();
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        if (!$user->isAdmin() && !$event?->isCertificatePublished()) {
            abort(403, 'Penerbitan e-sertifikat kegiatan belum dibuka oleh panitia pelaksana.');
        }

        $certificates = $certificateService->getCertificatesForUser($user, $event);

        if (empty($certificates)) {
            abort(404, 'Tidak ada e-sertifikat yang memenuhi syarat untuk akun Anda.');
        }

        $target = $certificates[0];
        $download = $request->boolean('download', true);

        return $certificateService->responsePdf($event, $target['role'], $target, $download);
    }

    /**
     * Validasi hak akses dokumen peserta
     */
    protected function canAccessParticipantCertificate(?User $user, Participant $participant): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->isAdmin() || $user->isSuperadmin()) {
            return true;
        }

        if ($user->participant_id === $participant->id) {
            return true;
        }

        if ($user->isScoutGroup() && $user->scoutGroup && $participant->patrol?->scout_group_id === $user->scoutGroup->id) {
            return true;
        }

        if ($user->isChaperone() && $user->chaperone && $participant->patrol?->scout_group_id === $user->chaperone->scout_group_id) {
            return true;
        }

        return false;
    }

    /**
     * Validasi hak akses dokumen pembina pendamping
     */
    protected function canAccessChaperoneCertificate(?User $user, Chaperone $chaperone): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->isAdmin() || $user->isSuperadmin()) {
            return true;
        }

        if ($chaperone->user_id && $user->id === $chaperone->user_id) {
            return true;
        }

        if ($user->isScoutGroup() && $user->scoutGroup && $chaperone->scout_group_id === $user->scoutGroup->id) {
            return true;
        }

        return false;
    }
}


<?php

namespace App\Services;

use App\Models\Chaperone;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use App\Models\User;

/**
 * Otorisasi dan penyusunan data ID Card digital (peserta, bindamping, dan aparatur)
 */
class DigitalCardService
{
    public function __construct(private readonly QrCodeService $qrService) {}

    /**
     * Admin, Panitia, atau Dewan Juri boleh membuka kartu seluruh kontingen
     */
    private static function isOfficial(?User $user): bool
    {
        return $user && ($user->isAdmin() || $user->isCommittee() || $user->isJudge());
    }

    /**
     * ID Card peserta/bindamping hanya dapat diakses setelah kontingen diverifikasi (berlaku untuk semua pengguna)
     */
    public function ensureDownloadable(?ScoutGroup $scoutGroup): void
    {
        abort_unless($scoutGroup?->canDownloadDocuments(), 403, ScoutGroup::DOWNLOAD_LOCKED_MESSAGE);
    }

    /**
     * Akses kartu satu kontingen: petugas, atau gugus depan pemilik kontingen (setelah diverifikasi)
     */
    public function authorizeScoutGroup(?User $user, ?ScoutGroup $scoutGroup, string $message = 'Akses ditolak.'): void
    {
        if ($user && ! self::isOfficial($user) && $scoutGroup && $scoutGroup->user_id !== $user->id) {
            abort(403, $message);
        }

        $this->ensureDownloadable($scoutGroup);
    }

    public function qr(string $token, int $size = 220): string
    {
        return $this->qrService->generateDataUri(route('digital-cards.verify', $token), $size);
    }

    /**
     * Data satu kartu peserta untuk tampilan batch
     */
    public function participantItem(Participant $participant, Patrol $patrol): array
    {
        $isBoys = $patrol->isBoys();

        return [
            'group' => 'Regu '.$patrol->patrol_name.' ('.($isBoys ? 'Putra' : 'Putri').')',
            'theme' => $isBoys ? 'boys' : 'girls',
            'patrol_name' => $patrol->patrol_name,
            'npp' => $patrol->npp,
            'name' => $participant->full_name,
            'id_number' => $participant->id_number ?: 'ID-PESERTA',
            'scout_id' => $participant->scout_id,
            'photo_path' => $participant->photo_path,
            'qr_token' => $participant->qr_token,
            'qrDataUri' => $this->qr($participant->qr_token, 180),
            'role' => $participant->isLeader() ? 'leader' : ($participant->isAssistantLeader() ? 'assistant_leader' : 'member'),
            'category_label' => $isBoys ? 'PESERTA PENGGALANG PUTRA' : 'PESERTA PENGGALANG PUTRI',
            'group_number' => $patrol->group_number,
            'contact' => $participant->phone ?: ($participant->parent_phone ?: '-'),
        ];
    }

    /**
     * Data satu kartu bindamping untuk tampilan batch
     */
    public function chaperoneItem(Chaperone $chaperone, ScoutGroup $scoutGroup): array
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
            'qrDataUri' => $this->qr($chaperone->qr_token, 180),
            'role' => null,
            'category_label' => 'BINDAMPING '.($isMale ? 'PUTRA' : 'PUTRI'),
            'group_number' => $chaperone->group_number ?: ($scoutGroup->group_number ?? '-'),
            'contact' => $chaperone->phone_number ?: '-',
        ];
    }
}

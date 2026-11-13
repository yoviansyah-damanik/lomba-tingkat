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
    public static function isOfficial(?User $user): bool
    {
        return (bool) ($user && ($user->isAdmin() || $user->isCommittee() || $user->isJudge()));
    }

    /**
     * Pastikan pengguna adalah aparatur resmi (Admin, Panitia, atau Dewan Juri)
     */
    public function authorizeOfficial(?User $user, string $message = 'Akses ditolak.'): void
    {
        abort_unless(self::isOfficial($user), 403, $message);
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
    public function participantItem(Participant $participant, Patrol $patrol, ?ScoutGroup $scoutGroup = null): array
    {
        $isBoys = $patrol->isBoys();
        $sg = $scoutGroup ?? $patrol->scoutGroup;
        $schoolName = $sg?->school_name ?? '-';
        $kwarranName = $sg?->kwarran_name ?? 'Padangsidimpuan';
        $groupNumber = $patrol->group_number ?: ($sg?->group_number ?? '-');
        $contact = $participant->phone ?: ($participant->parent_phone ?: '-');

        return [
            'type' => 'participant',
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
            'group_number' => $groupNumber,
            'school_name' => $schoolName,
            'kwarran_name' => $kwarranName,
            'contact' => $contact,
            'rows' => [
                'Nomor Gudep' => $groupNumber,
                'Pangkalan' => $schoolName,
                'Kwartir Ranting' => $kwarranName,
                'Kontak/WA' => $contact,
            ],
        ];
    }

    /**
     * Data satu kartu bindamping untuk tampilan batch
     */
    public function chaperoneItem(Chaperone $chaperone, ScoutGroup $scoutGroup): array
    {
        $isMale = $chaperone->gender === 'male';
        $groupNumber = $chaperone->group_number ?: ($scoutGroup->group_number ?? '-');
        $schoolName = $scoutGroup->school_name ?? '-';
        $kwarranName = $scoutGroup->kwarran_name ?? 'Padangsidimpuan';
        $contact = $chaperone->phone_number ?: '-';

        return [
            'type' => 'chaperone',
            'group' => 'Pembina Pendamping',
            'theme' => $isMale ? 'chaperone-male' : 'chaperone-female',
            'patrol_name' => null,
            'npp' => null,
            'name' => $chaperone->full_name,
            'id_number' => $chaperone->id_number ?: 'ID-BINDAMPING',
            'scout_id' => $chaperone->scout_id,
            'photo_path' => $chaperone->photo_path,
            'qr_token' => $chaperone->qr_token,
            'qrDataUri' => $this->qr($chaperone->qr_token, 180),
            'role' => null,
            'category_label' => 'PEMBINA PENDAMPING '.($isMale ? 'PUTRA' : 'PUTRI'),
            'group_number' => $groupNumber,
            'school_name' => $schoolName,
            'kwarran_name' => $kwarranName,
            'contact' => $contact,
            'rows' => [
                'Nomor Gudep' => $groupNumber,
                'Pangkalan' => $schoolName,
                'Kwartir Ranting' => $kwarranName,
                'Kontak/WA' => $contact,
            ],
        ];
    }

    /**
     * Data satu kartu aparatur (Panitia Pelaksana / Dewan Juri) untuk tampilan batch A4
     */
    public function staffItem(User $staff): array
    {
        $isJudge = $staff->isJudge();
        $isCommittee = $staff->isCommittee();
        $roleName = $isJudge && $isCommittee
            ? 'PANITIA PELAKSANA & DEWAN JURI'
            : ($isJudge ? 'DEWAN JURI PERLOMBAAN' : 'PANITIA PELAKSANA');
        $groupName = $isJudge && $isCommittee
            ? 'Panitia Pelaksana & Dewan Juri'
            : ($isJudge ? 'Dewan Juri Perlombaan' : 'Panitia Pelaksana');
        $institution = $staff->institution ?? 'Kwartir Cabang Kota Padangsidimpuan';
        $kwarran = $staff->kwarran ?? 'Padangsidimpuan';
        $contact = $staff->phone_number ?: '-';

        return [
            'type' => 'staff',
            'group' => $groupName,
            'theme' => $isJudge ? 'judge' : 'staff',
            'patrol_name' => null,
            'npp' => null,
            'name' => $staff->name,
            'id_number' => $staff->id_number ?: '-',
            'scout_id' => $staff->scout_id,
            'photo_path' => $staff->avatar_path,
            'qr_token' => $staff->qr_token,
            'qrDataUri' => $this->qr($staff->qr_token, 180),
            'role' => null,
            'category_label' => $roleName,
            'group_number' => '-',
            'school_name' => $institution,
            'kwarran_name' => $kwarran,
            'contact' => $contact,
            'rows' => [
                'Jabatan/Tugas' => $staff->cardPositionLabel(),
                'Pangkalan/Instansi' => $institution,
                'Kwartir Ranting' => $kwarran,
                'Kontak/WA' => $contact,
            ],
        ];
    }
}

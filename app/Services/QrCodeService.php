<?php

namespace App\Services;

use App\Models\Chaperone;
use App\Models\Participant;
use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * Service untuk pembuatan dan resolusi QR Code digital kredensial kegiatan LT III
 */
class QrCodeService
{
    /**
     * Membuat string SVG QR Code dari teks atau token data
     */
    public function generateSvg(string $data, int $size = 180): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size, 1),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);

        return $writer->writeString($data);
    }

    /**
     * Membuat data URI base64 dari SVG QR Code untuk kemudahan rendering pada tag <img>
     */
    public function generateDataUri(string $data, int $size = 180): string
    {
        $svg = $this->generateSvg($data, $size);

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Menyelesaikan (resolve) token QR menjadi entitas terakreditasi resmi
     */
    public function resolveToken(string $token): ?array
    {
        // 1. Cek Peserta (Participant)
        $participant = Participant::with(['patrol.scoutGroup'])->where('qr_token', $token)->first();
        if ($participant) {
            $scoutGroup = $participant->patrol?->scoutGroup;
            $isVerified = $scoutGroup && $scoutGroup->isVerified();
            $groupNumber = $participant->patrol?->isBoys()
                ? $scoutGroup?->male_group_number
                : $scoutGroup?->female_group_number;

            return [
                'type' => 'participant',
                'title' => 'KARTU TANDA PESERTA',
                'category_label' => $participant->patrol?->isBoys() ? 'Penggalang Putra' : 'Penggalang Putri',
                'entity' => $participant,
                'id_number' => $participant->id_number,
                'name' => $participant->full_name,
                'role_label' => $participant->role_in_patrol?->label() ?? 'Anggota',
                'school_name' => $scoutGroup?->school_name ?? '-',
                'kwarran_name' => $scoutGroup?->kwarran_name ?? 'Kota Padangsidimpuan',
                'patrol_name' => $participant->patrol?->patrol_name ?? '-',
                'group_number' => $groupNumber ?: ($scoutGroup?->group_number ?? '-'),
                'scout_id' => $participant->scout_id ?: '-',
                'blood_type' => $participant->blood_type ?: '-',
                'gender' => $participant->gender === 'male' ? 'Laki-laki' : 'Perempuan',
                'birth_info' => $participant->birth_place . ', ' . ($participant->birth_date?->format('d/m/Y') ?? '-'),
                'photo_path' => $participant->photo_path,
                'token' => $participant->qr_token,
                'generated_at' => $participant->qr_generated_at ?? $participant->created_at,
                'is_verified' => $isVerified,
                'status_label' => $isVerified ? 'Terakreditasi & Terverifikasi' : 'Menunggu Verifikasi Administrator',
            ];
        }

        // 2. Cek Pembina Pendamping (Chaperone)
        $chaperone = Chaperone::with('scoutGroup')->where('qr_token', $token)->first();
        if ($chaperone) {
            $scoutGroup = $chaperone->scoutGroup;
            $isVerified = $scoutGroup && $scoutGroup->isVerified();

            return [
                'type' => 'chaperone',
                'title' => 'KARTU PEMBINA PENDAMPING',
                'category_label' => 'Pembina Pendamping (' . ($chaperone->gender === 'male' ? 'Putra' : 'Putri') . ')',
                'entity' => $chaperone,
                'id_number' => $chaperone->id_number,
                'name' => $chaperone->full_name,
                'role_label' => 'Pembina Pendamping',
                'school_name' => $scoutGroup?->school_name ?? '-',
                'kwarran_name' => $scoutGroup?->kwarran_name ?? 'Kota Padangsidimpuan',
                'patrol_name' => 'Pendamping Kontingen',
                'group_number' => $chaperone->group_number ?: ($scoutGroup?->group_number ?? '-'),
                'scout_id' => $chaperone->scout_id ?: '-',
                'blood_type' => $chaperone->blood_type ?: '-',
                'gender' => $chaperone->gender === 'male' ? 'Laki-laki' : 'Perempuan',
                'birth_info' => $chaperone->birth_place ? ($chaperone->birth_place . ', ' . $chaperone->birth_date?->format('d/m/Y')) : '-',
                'photo_path' => $chaperone->photo_path,
                'token' => $chaperone->qr_token,
                'generated_at' => $chaperone->created_at,
                'is_verified' => $isVerified,
                'status_label' => $isVerified ? 'Terakreditasi & Terverifikasi' : 'Menunggu Verifikasi Administrator',
            ];
        }

        // 3. Cek Staf Panitia / Juri (User)
        $user = User::with('roles')->where('qr_token', $token)->first();
        if ($user) {
            $roles = $user->roles->pluck('label')->join(' & ');

            return [
                'type' => 'staff',
                'title' => 'KARTU TANDA PANITIA / JURI',
                'category_label' => $roles ?: 'Aparatur Penyelenggara',
                'entity' => $user,
                'id_number' => $user->id_number,
                'name' => $user->name,
                'role_label' => $roles ?: 'Staf Penyelenggara',
                'school_name' => $user->institution ?: 'Kwarcab Gerakan Pramuka Kota Padangsidimpuan',
                'kwarran_name' => 'Kwartir Cabang Kota Padangsidimpuan',
                'patrol_name' => $user->position ?: 'Aparatur Resmi',
                'group_number' => 'Kwarcab',
                'scout_id' => $user->scout_id ?: '-',
                'blood_type' => '-',
                'gender' => '-',
                'birth_info' => '-',
                'photo_path' => $user->avatar_path,
                'token' => $user->qr_token,
                'generated_at' => $user->qr_generated_at ?? $user->created_at,
                'is_verified' => $user->is_active,
                'status_label' => $user->is_active ? 'Aparatur Aktif Terakreditasi' : 'Non-Aktif',
            ];
        }

        return null;
    }
}

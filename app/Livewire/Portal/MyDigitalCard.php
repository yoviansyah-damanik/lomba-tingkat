<?php

namespace App\Livewire\Portal;

use App\Services\QrCodeService;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Halaman Kartu Digital: kartu ID milik pengguna yang login (peserta, bindamping, panitia, atau juri)
 */
#[Layout('layouts.app')]
#[Title('Kartu Digital')]
class MyDigitalCard extends Component
{
    public function render(QrCodeService $qrService)
    {
        $user = auth()->user();

        $type = null;
        $card = [];
        $display = [];
        $lockedMessage = null;

        if ($user->isParticipant() && $participant = $user->participant) {
            $participant->loadMissing('patrol.scoutGroup');
            $scoutGroup = $participant->patrol?->scoutGroup;
            $type = 'participant';
            $card = ['participant' => $participant, 'scoutGroup' => $scoutGroup, 'token' => $participant->qr_token, 'name' => $participant->full_name];
            $display = [
                'theme' => $participant->patrol?->isBoys() ? 'boys' : 'girls',
                'category' => $participant->patrol?->isBoys() ? 'PESERTA PENGGALANG PUTRA' : 'PESERTA PENGGALANG PUTRI',
                'badge' => $participant->role_in_patrol?->label(),
                'name' => $participant->full_name,
                'id_number' => $participant->id_number,
                'scout_id' => $participant->scout_id,
                'photo' => $participant->photo_path,
                'rows' => [
                    'Regu' => $participant->patrol?->patrol_name,
                    'Nomor Gudep' => $participant->patrol?->group_number,
                    'Pangkalan' => $scoutGroup?->school_name,
                    'Kwartir Ranting' => $scoutGroup?->kwarran_name,
                    'Kontak/WA' => $participant->phone ?: $participant->parent_phone,
                ],
            ];
            $lockedMessage = $scoutGroup?->canDownloadDocuments() ? null : \App\Models\ScoutGroup::DOWNLOAD_LOCKED_MESSAGE;
        } elseif ($user->isChaperone() && $chaperone = $user->chaperone) {
            $chaperone->loadMissing('scoutGroup');
            $scoutGroup = $chaperone->scoutGroup;
            $type = 'chaperone';
            $card = ['chaperone' => $chaperone, 'scoutGroup' => $scoutGroup, 'token' => $chaperone->qr_token, 'name' => $chaperone->full_name];
            $display = [
                'theme' => $chaperone->gender === 'male' ? 'chaperone-male' : 'chaperone-female',
                'category' => $chaperone->gender === 'male' ? 'PEMBINA PENDAMPING PUTRA' : 'PEMBINA PENDAMPING PUTRI',
                'badge' => 'Pembina Pendamping',
                'name' => $chaperone->full_name,
                'id_number' => $chaperone->id_number,
                'scout_id' => $chaperone->scout_id,
                'photo' => $chaperone->photo_path,
                'rows' => [
                    'Nomor Gudep' => $chaperone->group_number ?: $scoutGroup?->group_number,
                    'Pangkalan' => $scoutGroup?->school_name,
                    'Kwartir Ranting' => $scoutGroup?->kwarran_name,
                    'Kontak/WA' => $chaperone->phone_number,
                ],
            ];
            $lockedMessage = $scoutGroup?->canDownloadDocuments() ? null : \App\Models\ScoutGroup::DOWNLOAD_LOCKED_MESSAGE;
        } elseif ($user->isCommittee() || $user->isJudge()) {
            $type = 'staff';
            $card = ['staff' => $user, 'token' => $user->qr_token, 'name' => $user->name];
            $display = [
                'theme' => $user->isJudge() ? 'judge' : 'staff',
                'category' => $user->isJudge() && $user->isCommittee()
                    ? 'PANITIA PELAKSANA & DEWAN JURI'
                    : ($user->isJudge() ? 'DEWAN JURI PERLOMBAAN' : 'PANITIA PELAKSANA'),
                'badge' => $user->cardPositionLabel(),
                'name' => $user->name,
                'id_number' => $user->id_number,
                'scout_id' => $user->scout_id,
                'photo' => $user->avatar_path,
                'rows' => [
                    'Jabatan/Tugas' => $user->cardPositionLabel(),
                    'Instansi' => $user->institution ?? 'Kwartir Cabang Kota Padangsidimpuan',
                    'Kontak/WA' => $user->phone_number,
                ],
            ];
        }

        if ($type && ! $lockedMessage && ! empty($card['token'])) {
            $card['qrDataUri'] = $qrService->generateDataUri(route('digital-cards.verify', $card['token']), 400);
        }

        return view('livewire.portal.my-digital-card', [
            'type' => $type,
            'card' => $card,
            'display' => $display,
            'lockedMessage' => $lockedMessage,
            'filename' => 'KARTU_'.Str::upper(Str::slug($card['name'] ?? 'DIGITAL')),
        ]);
    }
}
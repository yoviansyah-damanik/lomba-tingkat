<?php

namespace App\Livewire\Public;

use App\Enums\RegistrationStatusEnum;
use App\Models\Chaperone;
use App\Models\Event;
use App\Models\Participant;
use App\Models\User;
use App\Services\CertificateService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Laman verifikasi keaslian dokumen e-sertifikat (dari scan QR Code)
 */
#[Layout('layouts.public')]
#[Title('Verifikasi E-Sertifikat')]
class CertificateVerification extends Component
{
    public string $id;

    public function mount(string $id): void
    {
        $this->id = trim($id);
    }

    public function render(CertificateService $certificateService)
    {
        $event = Event::where('is_active', true)->first() ?? Event::first();
        $record = null;
        $role = null;
        $downloadUrl = null;

        // Cari di peserta
        $participant = Participant::with('patrol.scoutGroup')
            ->where('id_number', $this->id)
            ->orWhere('id', $this->id)
            ->first();

        if ($participant) {
            $record = $certificateService->recipientDataForParticipant($participant, $event);
            $role = CertificateService::ROLE_PARTICIPANT;
            $downloadUrl = route('certificates.download.participant', $participant);
        }

        // Cari di pembina
        if (!$record) {
            $chaperone = Chaperone::with('scoutGroup')
                ->where('id_number', $this->id)
                ->orWhere('id', $this->id)
                ->first();

            if ($chaperone) {
                $record = $certificateService->recipientDataForChaperone($chaperone, $event);
                $role = CertificateService::ROLE_CHAPERONE;
                $downloadUrl = route('certificates.download.chaperone', $chaperone);
            }
        }

        // Cari di aparatur (panitia/juri)
        if (!$record) {
            $official = User::where('id_number', $this->id)
                ->orWhere('id', $this->id)
                ->first();

            if ($official) {
                $roleType = $official->isJudge() ? CertificateService::ROLE_JUDGE : CertificateService::ROLE_COMMITTEE;
                $record = $certificateService->recipientDataForOfficial($official, $roleType, $event);
                $role = $roleType;
                $downloadUrl = route('certificates.download.official', ['official' => $official->id, 'role' => $roleType]);
            }
        }

        return view('livewire.public.certificate-verification', [
            'event' => $event,
            'record' => $record,
            'role' => $role,
            'downloadUrl' => $downloadUrl,
            'isPublished' => (bool) $event?->isCertificatePublished(),
        ]);
    }
}


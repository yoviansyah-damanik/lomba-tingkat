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
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Laman publik khusus penerbitan, pencarian, dan verifikasi e-sertifikat kegiatan
 */
#[Layout('layouts.public')]
#[Title('Penerbitan E-Sertifikat Resmi')]
class CertificatesPage extends Component
{
    #[Url]
    public string $search = '';

    #[Url]
    public string $role = 'all';

    public function render(CertificateService $certificateService)
    {
        $event = Event::where('is_active', true)->first() ?? Event::first();
        $isPublished = (bool) $event?->isCertificatePublished();

        $user = auth()->user();
        $myCertificates = [];

        if ($user && $isPublished) {
            $myCertificates = $certificateService->getCertificatesForUser($user, $event);
        }

        $results = [];
        $trimmed = trim($this->search);

        if ($isPublished && strlen($trimmed) >= 3) {
            // 1. Peserta terverifikasi
            if ($this->role === 'all' || $this->role === CertificateService::ROLE_PARTICIPANT) {
                $participants = Participant::with(['patrol.scoutGroup'])
                    ->where('status', RegistrationStatusEnum::Verified)
                    ->where(function ($q) use ($trimmed) {
                        $q->where('full_name', 'like', "%{$trimmed}%")
                            ->orWhere('id_number', 'like', "%{$trimmed}%")
                            ->orWhere('scout_id', 'like', "%{$trimmed}%");
                    })
                    ->take(15)
                    ->get();

                foreach ($participants as $p) {
                    $results[] = [
                        'type' => 'participant',
                        'data' => $certificateService->recipientDataForParticipant($p, $event),
                        'model_id' => $p->id,
                        'download_url' => route('certificates.download.participant', $p),
                    ];
                }
            }

            // 2. Pembina pendamping terverifikasi
            if ($this->role === 'all' || $this->role === CertificateService::ROLE_CHAPERONE) {
                $chaperones = Chaperone::with('scoutGroup')
                    ->where('status', RegistrationStatusEnum::Verified)
                    ->where(function ($q) use ($trimmed) {
                        $q->where('full_name', 'like', "%{$trimmed}%")
                            ->orWhere('id_number', 'like', "%{$trimmed}%")
                            ->orWhere('scout_id', 'like', "%{$trimmed}%");
                    })
                    ->take(15)
                    ->get();

                foreach ($chaperones as $c) {
                    $results[] = [
                        'type' => 'chaperone',
                        'data' => $certificateService->recipientDataForChaperone($c, $event),
                        'model_id' => $c->id,
                        'download_url' => route('certificates.download.chaperone', $c),
                    ];
                }
            }

            // 3. Panitia aktif
            if ($this->role === 'all' || $this->role === CertificateService::ROLE_COMMITTEE) {
                $committeeUsers = User::where('is_active', true)
                    ->whereHas('roles', fn($q) => $q->where('name', 'committee'))
                    ->where(function ($q) use ($trimmed) {
                        $q->where('name', 'like', "%{$trimmed}%")
                            ->orWhere('id_number', 'like', "%{$trimmed}%")
                            ->orWhere('scout_id', 'like', "%{$trimmed}%");
                    })
                    ->take(15)
                    ->get();

                foreach ($committeeUsers as $u) {
                    $results[] = [
                        'type' => 'committee',
                        'data' => $certificateService->recipientDataForOfficial($u, CertificateService::ROLE_COMMITTEE, $event),
                        'model_id' => $u->id,
                        'download_url' => route('certificates.download.official', ['official' => $u->id, 'role' => CertificateService::ROLE_COMMITTEE]),
                    ];
                }
            }

            // 4. Dewan Juri aktif
            if ($this->role === 'all' || $this->role === CertificateService::ROLE_JUDGE) {
                $judges = User::where('is_active', true)
                    ->whereHas('roles', fn($q) => $q->where('name', 'judge'))
                    ->where(function ($q) use ($trimmed) {
                        $q->where('name', 'like', "%{$trimmed}%")
                            ->orWhere('id_number', 'like', "%{$trimmed}%")
                            ->orWhere('scout_id', 'like', "%{$trimmed}%");
                    })
                    ->take(15)
                    ->get();

                foreach ($judges as $j) {
                    $results[] = [
                        'type' => 'judge',
                        'data' => $certificateService->recipientDataForOfficial($j, CertificateService::ROLE_JUDGE, $event),
                        'model_id' => $j->id,
                        'download_url' => route('certificates.download.official', ['official' => $j->id, 'role' => CertificateService::ROLE_JUDGE]),
                    ];
                }
            }
        }

        // Jika pangkalan gudep login, siapkan daftar kontingen
        $scoutGroupRoster = [];
        if ($user?->isScoutGroup() && $user->scoutGroup && $isPublished) {
            $gudep = $user->scoutGroup;
            $gudepParticipants = Participant::whereHas('patrol', fn($q) => $q->where('scout_group_id', $gudep->id))
                ->where('status', RegistrationStatusEnum::Verified)
                ->get();
            $gudepChaperones = Chaperone::where('scout_group_id', $gudep->id)
                ->where('status', RegistrationStatusEnum::Verified)
                ->get();

            foreach ($gudepParticipants as $p) {
                $scoutGroupRoster[] = [
                    'category' => 'Peserta',
                    'data' => $certificateService->recipientDataForParticipant($p, $event),
                    'download_url' => route('certificates.download.participant', $p),
                ];
            }

            foreach ($gudepChaperones as $c) {
                $scoutGroupRoster[] = [
                    'category' => 'Pembina Pendamping',
                    'data' => $certificateService->recipientDataForChaperone($c, $event),
                    'download_url' => route('certificates.download.chaperone', $c),
                ];
            }
        }

        return view('livewire.public.certificates-page', [
            'event' => $event,
            'isPublished' => $isPublished,
            'myCertificates' => $myCertificates,
            'results' => $results,
            'scoutGroupRoster' => $scoutGroupRoster,
        ]);
    }
}


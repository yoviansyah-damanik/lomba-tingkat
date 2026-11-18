<?php

namespace App\Livewire\Public;

use App\Enums\RegistrationStatusEnum;
use App\Models\Chaperone;
use App\Models\CompetitionScore;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\User;
use App\Services\CertificateService;
use App\Services\StandingsService;
use Illuminate\Support\Str;
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

        // Cari di regu berprestasi kejuaraan umum
        if (!$record) {
            $cleanPatrolId = Str::after($this->id, 'GEN-');
            $patrol = Patrol::with(['scoutGroup'])
                ->where('id', $cleanPatrolId)
                ->orWhere('id', $this->id)
                ->orWhere('npp_code', $cleanPatrolId)
                ->orWhere('npp_code', $this->id)
                ->first();

            if ($patrol) {
                $standingsService = app(StandingsService::class);
                $gender = $patrol->gender_category instanceof \BackedEnum ? $patrol->gender_category->value : $patrol->gender_category;
                $row = $standingsService->getPatrolStandings($gender, null, true)->firstWhere('id', $patrol->id);

                if ($row && !empty($row['general_rank_title']) && ($row['rank'] ?? 99) <= 3) {
                    $record = $certificateService->recipientDataForWinningPatrolGeneral($patrol, $row['general_rank_title'], (int) $row['rank'], $event);
                    $role = $record['role'];
                    $downloadUrl = route('certificates.download.patrol.general', $patrol);
                }
            }
        }

        // Cari di regu berprestasi cabang lomba
        if (!$record) {
            $cleanScoreId = Str::after($this->id, 'LMB-');
            $score = CompetitionScore::with(['competition', 'patrol.scoutGroup'])
                ->where('id', $cleanScoreId)
                ->orWhere('id', $this->id)
                ->first();

            if ($score && $score->is_locked && in_array($score->predicate, ['prestasi_tinggi', 'prestasi_baik', 'prestasi_cukup'], true)) {
                $record = $certificateService->recipientDataForWinningPatrolCompetition($score, $event);
                $role = $record['role'];
                $downloadUrl = route('certificates.download.competition-score', $score);
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


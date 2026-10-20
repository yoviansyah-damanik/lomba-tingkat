<?php

namespace App\Jobs;

use App\Enums\RegistrationStatusEnum;
use App\Models\ScoutGroup;
use App\Services\WebPushService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Job pengiriman notifikasi push hasil verifikasi pendaftaran kepada akun Gugus Depan
 */
class SendVerificationResultPush implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public string $scoutGroupId, public string $status)
    {
        $this->onQueue('notifications');
    }

    public function handle(WebPushService $webPush): void
    {
        $scoutGroup = ScoutGroup::with('user')->find($this->scoutGroupId);
        $status = RegistrationStatusEnum::tryFrom($this->status);

        if (! $scoutGroup?->user || ! $status) {
            return;
        }

        [$title, $body] = match ($status) {
            RegistrationStatusEnum::Verified => [
                'Pendaftaran Disetujui',
                "Pendaftaran kontingen {$scoutGroup->school_name} telah diverifikasi dan disetujui.",
            ],
            RegistrationStatusEnum::NeedsRevision => [
                'Pendaftaran Perlu Perbaikan',
                "Pendaftaran {$scoutGroup->school_name} perlu diperbaiki: {$scoutGroup->revision_notes}",
            ],
            RegistrationStatusEnum::Rejected => [
                'Pendaftaran Ditolak',
                "Pendaftaran {$scoutGroup->school_name} ditolak: {$scoutGroup->revision_notes}",
            ],
            default => [null, null],
        };

        if (! $title) {
            return;
        }

        $webPush->notifyUser($scoutGroup->user, [
            'title' => $title,
            'body' => str($body)->limit(180)->toString(),
            'url' => route('scout-group.index'),
            'tag' => 'lt3-verification-'.$scoutGroup->id,
        ]);
    }
}

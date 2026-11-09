<?php

namespace App\Jobs;

use App\Enums\RoleEnum;
use App\Models\ScoutGroup;
use App\Services\WebPushService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Job pengiriman notifikasi push ke Superadmin dan Administrator saat ada pendaftaran Gugus Depan baru
 */
class SendScoutGroupRegisteredPush implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public string $scoutGroupId)
    {
        $this->onQueue('notifications');
    }

    public function handle(WebPushService $webPush): void
    {
        $scoutGroup = ScoutGroup::find($this->scoutGroupId);

        if (! $scoutGroup) {
            return;
        }

        $payload = [
            'title' => 'Pendaftaran Gugus Depan Baru',
            'body' => sprintf(
                '%s (%s) baru mendaftar. Kode: %s.',
                $scoutGroup->school_name,
                $scoutGroup->kwarran_name,
                $scoutGroup->registration_code
            ),
            'url' => route('admin.scout-groups.show', $scoutGroup->id),
            'tag' => 'lt3-registration-'.$scoutGroup->id,
        ];

        foreach ([RoleEnum::Superadmin, RoleEnum::Admin] as $role) {
            $webPush->broadcastNotification($payload, $role->value);
        }
    }
}

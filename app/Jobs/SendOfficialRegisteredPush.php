<?php

namespace App\Jobs;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Services\WebPushService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Job pengiriman notifikasi (push dan lonceng dasbor) ke Superadmin dan Administrator saat aparatur berhasil mendaftar
 */
class SendOfficialRegisteredPush implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public string $userId)
    {
        $this->onQueue('notifications');
    }

    public function handle(WebPushService $webPush): void
    {
        $official = User::with('roles')->find($this->userId);

        if (! $official) {
            return;
        }

        $roles = $official->roles->pluck('name')
            ->map(fn (string $role) => match ($role) {
                RoleEnum::Committee->value => 'Panitia',
                RoleEnum::Judge->value => 'Dewan Juri',
                default => $role,
            })
            ->implode(' & ');

        $payload = [
            'title' => 'Pendaftaran Aparatur Baru',
            'body' => sprintf('%s (%s) mendaftar sebagai %s dan menunggu persetujuan.', $official->name, $official->id_number, $roles),
            'url' => route('admin.officials.index'),
            'tag' => 'lt3-official-registration-'.$official->id,
        ];

        foreach ([RoleEnum::Superadmin, RoleEnum::Admin] as $role) {
            $webPush->broadcastNotification($payload, $role->value);
        }
    }
}

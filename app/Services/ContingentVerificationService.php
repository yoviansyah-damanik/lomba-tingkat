<?php

namespace App\Services;

use App\Enums\RegistrationStatusEnum;
use App\Models\AuditLog;
use App\Models\Participant;
use App\Models\ScoutGroup;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use InvalidArgumentException;

/**
 * Keputusan verifikasi pendaftaran kontingen: setujui, minta perbaikan, tolak, dan batalkan verifikasi.
 * Pengecekan wewenang dan status ada di sini agar berlaku seragam di mana pun aksi dipanggil.
 */
class ContingentVerificationService
{
    /**
     * Setujui kontingen dan terbitkan akun peserta/bindamping.
     *
     * @param  (callable(int, string): void)|null  $onProgress  kemajuan 0-100 beserta keterangan tahap
     * @return array{participants: int, chaperones: int}
     */
    public function verify(ScoutGroup $scoutGroup, User $actor, ?callable $onProgress = null): array
    {
        $report = function (int $percent, string $message) use ($onProgress) {
            if ($onProgress) {
                $onProgress($percent, $message);
            }
        };

        $this->ensureDecisionAllowed($scoutGroup, $actor);

        $report(10, 'Menetapkan status terverifikasi...');
        $scoutGroup->update([
            'verification_status' => RegistrationStatusEnum::Verified,
            'verified_at' => now(),
            'revision_notes' => null,
        ]);

        $report(25, 'Mengesahkan regu, peserta, dan bindamping...');
        $this->cascadeStatus($scoutGroup, RegistrationStatusEnum::Verified);

        $report(30, 'Menyiapkan pembuatan akun...');
        $accounts = app(ContingentAccountService::class)->provision(
            $scoutGroup,
            fn (int $done, int $total) => $report(30 + (int) floor(65 * $done / max(1, $total)), "Membuat akun {$done} dari {$total}...")
        );

        $report(100, 'Verifikasi selesai.');

        return $accounts;
    }

    public function requestRevision(ScoutGroup $scoutGroup, User $actor, string $notes): void
    {
        $this->ensureDecisionAllowed($scoutGroup, $actor);

        $scoutGroup->update([
            'verification_status' => RegistrationStatusEnum::NeedsRevision,
            'revision_notes' => $notes,
            'verified_at' => now(),
        ]);

        $this->cascadeStatus($scoutGroup, RegistrationStatusEnum::NeedsRevision);
    }

    public function reject(ScoutGroup $scoutGroup, User $actor, string $notes): void
    {
        $this->ensureDecisionAllowed($scoutGroup, $actor);

        $scoutGroup->update([
            'verification_status' => RegistrationStatusEnum::Rejected,
            'revision_notes' => $notes,
            'verified_at' => now(),
        ]);

        $this->cascadeStatus($scoutGroup, RegistrationStatusEnum::Rejected);
    }

    /**
     * Batalkan verifikasi dan cabut seluruh akun digital (khusus Superadmin)
     *
     * @return array{participants: int, chaperones: int}
     */
    public function cancelVerification(ScoutGroup $scoutGroup, User $actor, ?string $reason): array
    {
        if (! $actor->isSuperadmin()) {
            throw new AuthorizationException('Hanya Superadmin yang berwenang membatalkan verifikasi kontingen.');
        }

        if ($scoutGroup->verification_status !== RegistrationStatusEnum::Verified) {
            throw new InvalidArgumentException('Hanya kontingen dengan status terverifikasi yang dapat dibatalkan verifikasinya.');
        }

        $scoutGroup->loadMissing(['patrols.participants', 'chaperones']);

        $revoked = app(ContingentAccountService::class)->revokeAccounts($scoutGroup);

        $note = filled($reason)
            ? 'Verifikasi dibatalkan oleh Superadmin: '.$reason
            : 'Verifikasi dibatalkan oleh Superadmin.';

        $scoutGroup->update([
            'verification_status' => RegistrationStatusEnum::NeedsRevision,
            'revision_notes' => $note,
            'verified_at' => null,
        ]);

        $this->cascadeStatus($scoutGroup, RegistrationStatusEnum::NeedsRevision);

        AuditLog::log(
            action: 'contingent_verification_cancelled',
            modelType: ScoutGroup::class,
            modelId: $scoutGroup->id,
            oldValues: ['verification_status' => RegistrationStatusEnum::Verified->value],
            newValues: ['verification_status' => RegistrationStatusEnum::NeedsRevision->value],
            reason: $note
        );

        return $revoked;
    }

    /**
     * Keputusan hanya oleh Administrator (bukan Superadmin) dan tidak dapat mengubah kontingen yang sudah terverifikasi
     */
    private function ensureDecisionAllowed(ScoutGroup $scoutGroup, User $actor): void
    {
        if (! $actor->isAdmin() || $actor->isSuperadmin()) {
            throw new AuthorizationException('Superadmin tidak berwenang melakukan aksi verifikasi. Gunakan fitur Batalkan Verifikasi jika diperlukan.');
        }

        if ($scoutGroup->verification_status === RegistrationStatusEnum::Verified) {
            throw new InvalidArgumentException('Status kontingen telah terverifikasi. Keputusan verifikasi tidak dapat diubah.');
        }
    }

    private function cascadeStatus(ScoutGroup $scoutGroup, RegistrationStatusEnum $status): void
    {
        $scoutGroup->patrols()->update(['status' => $status]);
        Participant::whereIn('patrol_id', $scoutGroup->patrols()->pluck('id'))->update(['status' => $status]);
        $scoutGroup->chaperones()->update(['status' => $status]);
    }
}

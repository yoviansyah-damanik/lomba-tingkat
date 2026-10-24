<?php

namespace App\Services;

use App\Enums\PatrolGenderEnum;
use App\Enums\PatrolRoleEnum;
use App\Enums\RoleEnum;
use App\Models\AuditLog;
use App\Models\Chaperone;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Pembuatan akun otomatis bindamping dan peserta saat kontingen gugus depan diverifikasi.
 *
 * Username bindamping & peserta : ID masing-masing (contoh id-ab12cd34, huruf kecil)
 * Kata sandi awal               : tanggal lahir format Ymd
 */
class ContingentAccountService
{
    /**
     * Buat akun yang belum ada untuk seluruh bindamping dan peserta gugus depan.
     *
     * @param  (callable(int, int): void)|null  $onProgress  dipanggil setelah tiap akun diproses: (selesai, total)
     * @return array{chaperones: int, participants: int, skipped: int}
     */
    public function provision(ScoutGroup $group, ?callable $onProgress = null): array
    {
        $result = ['chaperones' => 0, 'participants' => 0, 'skipped' => 0];

        $chaperones = $group->chaperones()->get();
        $patrols = $group->patrols()->with('participants')->get();

        $total = $chaperones->count() + $patrols->sum(fn ($patrol) => $patrol->participants->count());
        $done = 0;
        $advance = function () use (&$done, $total, $onProgress) {
            $done++;

            if ($onProgress) {
                $onProgress($done, $total);
            }
        };

        foreach ($chaperones as $chaperone) {
            match ($this->provisionChaperone($group, $chaperone)) {
                'created' => $result['chaperones']++,
                'skipped' => $result['skipped']++,
                default => null,
            };
            $advance();
        }

        foreach ($patrols as $patrol) {
            foreach ($patrol->participants as $participant) {
                match ($this->provisionParticipant($participant)) {
                    'created' => $result['participants']++,
                    'skipped' => $result['skipped']++,
                    default => null,
                };
                $advance();
            }
        }

        if ($result['chaperones'] + $result['participants'] > 0) {
            AuditLog::log(
                action: 'contingent_accounts_provisioned',
                modelType: ScoutGroup::class,
                modelId: $group->id,
                oldValues: null,
                newValues: $result,
                reason: 'Pembuatan akun otomatis saat verifikasi kontingen '.$group->school_name
            );
        }

        return $result;
    }

    /**
     * Nomor gudep berformat (contoh 01-093) sesuai gender (male: ganjil, female: genap)
     */
    public function groupNumber(ScoutGroup $group, string $gender): string
    {
        $numbers = ScoutGroup::formatGroupNumbers($group->group_number);

        return $gender === 'female'
            ? ($group->female_group_number ?: $numbers['female'])
            : ($group->male_group_number ?: $numbers['male']);
    }

    /**
     * Teks pangkalan pada data user: "{no_gudep} {nama pangkalan}" (contoh 01-093 SMP Negeri 1 Padangsidimpuan)
     */
    public function institutionLabel(ScoutGroup $group, string $gender): string
    {
        return trim($this->groupNumber($group, $gender).' '.$group->school_name);
    }

    /**
     * Selaraskan nama, pangkalan, dan jabatan akun peserta dengan data peserta terverifikasi
     */
    public function syncParticipant(Participant $participant): void
    {
        $user = User::where('participant_id', $participant->id)->first();

        if (! $user) {
            return;
        }

        $patrol = $participant->patrol()->with('scoutGroup')->first();

        if (! $patrol?->scoutGroup) {
            return;
        }

        $gender = $patrol->gender_category === PatrolGenderEnum::Girls ? 'female' : 'male';

        $this->updateUser($user, [
            'name' => $participant->full_name,
            'institution' => $this->institutionLabel($patrol->scoutGroup, $gender),
            'position' => $this->participantPosition($participant),
        ]);
    }

    /**
     * Selaraskan nama, pangkalan, dan jabatan akun bindamping dengan data bindamping terverifikasi
     */
    public function syncChaperone(Chaperone $chaperone): void
    {
        $user = $chaperone->user_id ? User::find($chaperone->user_id) : null;
        $group = $chaperone->scoutGroup;

        if (! $user || ! $group) {
            return;
        }

        $this->updateUser($user, [
            'name' => $chaperone->full_name,
            'institution' => $this->institutionLabel($group, $chaperone->gender === 'female' ? 'female' : 'male'),
            'position' => 'Bindamping',
        ]);
    }

    /**
     * Selaraskan seluruh akun milik satu gugus depan (dipakai saat nama/nomor gudep berubah)
     */
    public function syncGroup(ScoutGroup $group): void
    {
        $group->chaperones()->get()->each(fn (Chaperone $chaperone) => $this->syncChaperone($chaperone));
        $group->participants()->get()->each(fn (Participant $participant) => $this->syncParticipant($participant));
    }

    /**
     * Cabut dan hapus seluruh akun peserta dan bindamping saat verifikasi dibatalkan
     *
     * @return array{chaperones: int, participants: int}
     */
    public function revokeAccounts(ScoutGroup $group): array
    {
        $revoked = ['chaperones' => 0, 'participants' => 0];

        foreach ($group->chaperones()->get() as $chaperone) {
            $user = $chaperone->user_id ? User::find($chaperone->user_id) : null;
            if ($user?->isChaperone()) {
                $user->delete();
                $revoked['chaperones']++;
            }
            if ($chaperone->id_number) {
                User::where('id_number', $chaperone->id_number)
                    ->orWhere('username', $chaperone->id_number)
                    ->delete();
            }
            $chaperone->update(['user_id' => null]);
        }

        foreach ($group->patrols()->with('participants')->get() as $patrol) {
            foreach ($patrol->participants as $participant) {
                $users = User::where('participant_id', $participant->id)
                    ->when($participant->id_number, function ($q, $idNum) {
                        $q->orWhere('id_number', $idNum)
                            ->orWhere('username', $idNum);
                    })
                    ->get();

                foreach ($users as $user) {
                    if ($user->isParticipant()) {
                        $user->delete();
                        $revoked['participants']++;
                    }
                }
            }
        }

        if ($revoked['chaperones'] + $revoked['participants'] > 0) {
            AuditLog::log(
                action: 'contingent_accounts_revoked',
                modelType: ScoutGroup::class,
                modelId: $group->id,
                oldValues: null,
                newValues: $revoked,
                reason: 'Pencabutan seluruh akun digital karena pembatalan verifikasi kontingen '.$group->school_name
            );
        }

        return $revoked;
    }

    /**
     * Hapus akun peserta yang ditautkan ke data peserta
     */
    public function deleteParticipantAccount(Participant $participant): void
    {
        User::where('participant_id', $participant->id)->get()
            ->filter(fn (User $user) => $user->isParticipant())
            ->each->delete();
    }

    /**
     * Hapus akun bindamping yang ditautkan ke data bindamping
     */
    public function deleteChaperoneAccount(Chaperone $chaperone): void
    {
        $user = $chaperone->user_id ? User::find($chaperone->user_id) : null;

        if ($user?->isChaperone()) {
            $user->delete();
        }

        $chaperone->update(['user_id' => null]);
    }

    protected function participantPosition(Participant $participant): string
    {
        return match ($participant->role_in_patrol) {
            PatrolRoleEnum::PatrolLeader => 'PINRU',
            PatrolRoleEnum::AssistantPatrolLeader => 'WAPINRU',
            default => 'Anggota Regu',
        };
    }

    protected function updateUser(User $user, array $attributes): void
    {
        $user->fill($attributes);

        if ($user->isDirty()) {
            $user->save();
        }
    }

    protected function provisionChaperone(ScoutGroup $group, Chaperone $chaperone): string
    {
        if ($chaperone->user_id) {
            $this->syncChaperone($chaperone);

            return 'exists';
        }

        if (! $chaperone->birth_date) {
            return 'skipped';
        }

        $user = $this->firstOrCreateUser($chaperone->id_number, $chaperone->full_name, $chaperone->phone_number, $chaperone->birth_date->format('Ymd'), RoleEnum::Chaperone);

        $chaperone->update(['user_id' => $user->id]);
        $this->syncChaperone($chaperone);

        return 'created';
    }

    protected function provisionParticipant(Participant $participant): string
    {
        if (User::where('participant_id', $participant->id)->exists()) {
            $this->syncParticipant($participant);

            return 'exists';
        }

        if (! $participant->birth_date) {
            return 'skipped';
        }

        $user = $this->firstOrCreateUser($participant->id_number, $participant->full_name, $participant->phone, $participant->birth_date->format('Ymd'), RoleEnum::Participant);

        $user->update(['participant_id' => $participant->id]);
        $this->syncParticipant($participant);

        return 'created';
    }

    /**
     * Akun dibuat dengan username dan ID kartu yang sama dengan ID peserta/bindamping
     */
    protected function firstOrCreateUser(string $idNumber, string $name, ?string $phone, string $password, RoleEnum $role): User
    {
        $user = User::firstOrCreate(
            ['username' => $idNumber],
            [
                'id_number' => $idNumber,
                'name' => $name,
                'password' => $password,
                'phone_number' => $phone,
                'is_active' => true,
            ]
        );

        $user->assignRole($role);

        return $user;
    }
}

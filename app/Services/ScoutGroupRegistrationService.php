<?php

namespace App\Services;

use App\Enums\RegistrationStatusEnum;
use App\Enums\RoleEnum;
use App\Jobs\SendScoutGroupRegisteredPush;
use App\Models\RegistrationInvitation;
use App\Models\ScoutGroup;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Pembuatan akun pangkalan Gugus Depan dari undangan registrasi yang sah
 */
class ScoutGroupRegistrationService
{
    /**
     * Daftar 6 Kwartir Ranting resmi di bawah naungan Kwarcab Kota Padangsidimpuan
     */
    public const KWARRANS = [
        'Kwarran Padangsidimpuan Utara',
        'Kwarran Padangsidimpuan Selatan',
        'Kwarran Padangsidimpuan Batunadua',
        'Kwarran Padangsidimpuan Hutaimbaru',
        'Kwarran Padangsidimpuan Tenggara',
        'Kwarran Padangsidimpuan Angkola Julu',
    ];

    /**
     * Daftarkan pangkalan memakai undangan; identitas pangkalan selalu diambil dari undangan.
     *
     * @param  array{address: string, pic_name: string, phone_number: string}  $data
     * @return array{scoutGroup: ScoutGroup, username: string, password: string}
     */
    public function register(string $invitationId, array $data, ?string $ip, ?string $userAgent): array
    {
        $plainPassword = Str::password(10, letters: true, numbers: true, symbols: false, spaces: false);

        $scoutGroup = DB::transaction(function () use ($invitationId, $data, $ip, $userAgent, $plainPassword) {
            $invitation = RegistrationInvitation::whereKey($invitationId)->lockForUpdate()->first();

            if (! $invitation || ! $invitation->isUsable()) {
                throw ValidationException::withMessages([
                    'invitation' => 'Kode undangan sudah tidak berlaku. Hubungi panitia untuk kode baru.',
                ]);
            }

            $groupNumberData = ScoutGroup::formatGroupNumbers($invitation->group_number);

            // Jaring pengaman: nomor gudep yang sama tidak boleh terdaftar dua kali
            $duplicate = ScoutGroup::where('male_group_number', $groupNumberData['male'])
                ->orWhere('female_group_number', $groupNumberData['female'])
                ->exists();

            if ($duplicate) {
                throw ValidationException::withMessages([
                    'invitation' => 'Nomor gugus depan ini sudah terdaftar. Hubungi panitia bila ini kekeliruan.',
                ]);
            }

            $gudepId = ScoutGroup::generateGudepId($groupNumberData);

            $user = User::create([
                'name' => $data['pic_name'],
                'username' => $gudepId,
                'id_number' => $gudepId,
                'phone_number' => $data['phone_number'],
                'password' => Hash::make($plainPassword),
                'must_change_password' => true,
                'is_active' => true,
            ]);

            $user->assignRole(RoleEnum::ScoutGroup);

            $scoutGroup = ScoutGroup::create([
                'user_id' => $user->id,
                'registration_code' => $gudepId,
                'school_name' => $invitation->school_name,
                'group_number' => $groupNumberData['combined'],
                'male_group_number' => $groupNumberData['male'],
                'female_group_number' => $groupNumberData['female'],
                'kwarran_name' => $invitation->kwarran_name,
                'address' => trim($data['address']).', '.$invitation->kwarran_name.', Kota Padangsidimpuan',
                'contact_person' => $data['pic_name'],
                'contact_phone' => $data['phone_number'],
                'verification_status' => RegistrationStatusEnum::Draft,
                'invitation_id' => $invitation->id,
                'registered_ip' => $ip,
                'registered_user_agent' => $userAgent ? Str::limit($userAgent, 500, '') : null,
            ]);

            $invitation->markUsed($scoutGroup);

            return $scoutGroup;
        });

        // Beritahu Admin lewat push; kegagalan notifikasi tidak boleh menggagalkan pendaftaran
        try {
            SendScoutGroupRegisteredPush::dispatch($scoutGroup->id);
        } catch (Throwable $e) {
            Log::warning('Gagal mengantrekan notifikasi pendaftaran gudep: '.$e->getMessage());
        }

        return [
            'scoutGroup' => $scoutGroup,
            'username' => $scoutGroup->registration_code,
            'password' => $plainPassword,
        ];
    }
}

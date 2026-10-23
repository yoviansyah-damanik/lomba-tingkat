<?php

use App\Models\Chaperone;
use App\Models\Participant;
use App\Models\ScoutGroup;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Standarisasi format ID unik untuk Gudep, Peserta, Bindamping, dan Aparatur.
     */
    public function up(): void
    {
        // 1. Gudep: G-{no_gudep}, mis. G-01123.01124
        foreach (ScoutGroup::all() as $group) {
            $currentCode = (string) $group->registration_code;
            if (! str_starts_with($currentCode, 'G-')) {
                $newGudepId = ScoutGroup::generateGudepId([
                    'male' => $group->male_group_number ?? $group->group_number,
                    'female' => $group->female_group_number ?? $group->group_number,
                ], $group->id);

                $group->update(['registration_code' => $newGudepId]);

                if ($group->user_id) {
                    User::where('id', $group->user_id)->update(['id_number' => $newGudepId]);
                }
            }
        }

        // 2. Peserta: P-{6 digit acak}
        foreach (Participant::all() as $participant) {
            $currentId = (string) $participant->id_number;
            if (! preg_match('/^P-\d{6}$/', $currentId)) {
                $newPid = Participant::generateIdNumber();
                $participant->update(['id_number' => $newPid]);

                // Sinkronkan akun user peserta jika ada
                User::where('participant_id', $participant->id)->update([
                    'id_number' => $newPid,
                    'username' => Str::lower($newPid),
                ]);
            }
        }

        // 3. Bindamping: B-{6 digit acak}
        foreach (Chaperone::all() as $chaperone) {
            $currentId = (string) $chaperone->id_number;
            if (! preg_match('/^B-\d{6}$/', $currentId)) {
                $newBid = Chaperone::generateIdNumber();
                $chaperone->update(['id_number' => $newBid]);

                // Sinkronkan akun user bindamping jika ada
                if ($chaperone->user_id) {
                    User::where('id', $chaperone->user_id)->update([
                        'id_number' => $newBid,
                        'username' => Str::lower($newBid),
                    ]);
                }
            }
        }

        // 4. Aparatur: A-{6 digit acak}
        $chaperoneUserIds = Chaperone::whereNotNull('user_id')->pluck('user_id')->toArray();
        $scoutGroupUserIds = ScoutGroup::whereNotNull('user_id')->pluck('user_id')->toArray();
        $excludedUserIds = array_unique(array_merge($chaperoneUserIds, $scoutGroupUserIds));

        $aparaturUsers = User::whereNull('participant_id')
            ->whereNotIn('id', $excludedUserIds)
            ->get();

        foreach ($aparaturUsers as $user) {
            $currentId = (string) $user->id_number;
            if (! preg_match('/^A-\d{6}$/', $currentId)) {
                $newAid = User::generateOfficialIdNumber();
                $user->update(['id_number' => $newAid]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op rollback
    }
};

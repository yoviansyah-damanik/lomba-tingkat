<?php

namespace Database\Seeders;

use App\Enums\PermissionTypeEnum;
use App\Models\PermissionRequest;
use App\Models\ScoutGroup;
use Illuminate\Database\Seeder;

/**
 * Contoh permohonan izin: meninggalkan perkemahan (permanen dan sementara, sebagian sudah kembali) dan kunjungan tamu.
 *
 * Memakai gugus depan contoh yang sudah memiliki peserta dan bindamping; dilewati bila data izin sudah ada.
 */
class PermissionRequestSeeder extends Seeder
{
    public function run(): void
    {
        if (PermissionRequest::exists()) {
            return;
        }

        $groups = ScoutGroup::with(['patrols.participants', 'chaperones'])
            ->orderBy('registration_code')
            ->get()
            ->filter(fn (ScoutGroup $group) => $group->chaperones->isNotEmpty() && $group->patrols->flatMap->participants->count() >= 3)
            ->values();

        if ($groups->count() < 3) {
            $this->command?->warn('Gugus depan contoh belum cukup untuk membuat sampel permohonan izin.');

            return;
        }

        // 1. Izin meninggalkan perkemahan (permanen): langsung selesai, tanpa konfirmasi kembali
        $this->leave($groups[0], PermissionTypeEnum::LeavePermanent, 'Peserta sakit dan dijemput orang tua untuk perawatan di rumah.', 2, now()->subDays(2));

        // 2. Izin sementara yang masih menunggu: satu peserta sudah kembali, satu belum
        $this->leave($groups[1], PermissionTypeEnum::LeaveTemporary, 'Keperluan keluarga: menghadiri acara pernikahan kerabat di Padangsidimpuan.', 2, now()->subHours(5), returned: 1);

        // 3. Izin sementara yang sudah selesai: seluruh peserta sudah kembali
        $this->leave($groups[2], PermissionTypeEnum::LeaveTemporary, 'Berobat ke klinik di luar perkemahan.', 2, now()->subDay(), returned: 2);

        // 4. Kunjungan tamu
        foreach ([
            ['Bapak Ahmad Siregar (orang tua peserta)', 'Menjenguk anak dan mengantar perlengkapan tambahan.', now()->subHours(3)],
            ['Ibu Ratna Dewi (pembina sekolah)', 'Kunjungan pembina sekolah untuk memantau kontingen.', now()->subDays(1)],
        ] as [$guest, $reason, $when]) {
            PermissionRequest::create([
                'type' => PermissionTypeEnum::GuestVisit,
                'reason' => $reason,
                'guest_name' => $guest,
                'status' => 'completed',
                'completed_at' => $when,
                'created_at' => $when,
                'updated_at' => $when,
            ]);
        }

        $this->command?->info('Sampel permohonan izin dibuat.');
    }

    /**
     * Izin meninggalkan perkemahan: satu bindamping (penanggung jawab) dan beberapa peserta dari gudep yang sama
     */
    private function leave(ScoutGroup $group, PermissionTypeEnum $type, string $reason, int $participants, \DateTimeInterface $at, int $returned = 0): void
    {
        $request = PermissionRequest::create([
            'type' => $type,
            'reason' => $reason,
            'scout_group_id' => $group->id,
            'status' => $type->requiresReturn() ? 'active' : 'completed',
            'completed_at' => $type->requiresReturn() ? null : $at,
            'created_at' => $at,
            'updated_at' => $at,
        ]);

        $request->members()->create(['chaperone_id' => $group->chaperones->first()->id]);

        $group->patrols->flatMap->participants->take($participants)->values()->each(function ($participant, int $index) use ($request, $returned, $at) {
            $request->members()->create([
                'participant_id' => $participant->id,
                'returned_at' => $index < $returned ? \Illuminate\Support\Carbon::instance($at)->addHours(3) : null,
            ]);
        });

        if ($type->requiresReturn()) {
            $request->completeIfAllReturned();
        }
    }
}

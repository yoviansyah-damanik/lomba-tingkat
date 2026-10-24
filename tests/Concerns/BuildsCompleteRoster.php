<?php

namespace Tests\Concerns;

use App\Enums\PatrolRoleEnum;
use App\Models\Participant;
use App\Models\Patrol;

trait BuildsCompleteRoster
{
    /**
     * Lengkapi regu menjadi susunan sah untuk pengajuan: tepat 8 peserta, termasuk Pinru dan Wapinru.
     * $existingMembers = jumlah peserta anggota biasa yang sudah ada pada regu.
     *
     * @return array<int, Participant> peserta yang dibuat
     */
    protected function fillPatrolRoster(Patrol $patrol, int $existingMembers = 0): array
    {
        $roles = array_slice([
            PatrolRoleEnum::PatrolLeader,
            PatrolRoleEnum::AssistantPatrolLeader,
            ...array_fill(0, Patrol::REQUIRED_MEMBERS - 2, PatrolRoleEnum::Member),
        ], 0, Patrol::REQUIRED_MEMBERS - $existingMembers);

        return array_map(
            fn (PatrolRoleEnum $role) => Participant::factory()->create([
                'patrol_id' => $patrol->id,
                'role_in_patrol' => $role,
                'gender' => $patrol->isBoys() ? 'male' : 'female',
            ]),
            $roles
        );
    }
}

<?php

namespace Tests\Feature;

use App\Enums\RegistrationStatusEnum;
use App\Enums\RoleEnum;
use App\Livewire\Portal\PatrolsManagement;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PatrolDeletionRemovesAccountsTest extends TestCase
{
    use RefreshDatabase;

    private function participantWithAccount(Patrol $patrol, string $suffix): array
    {
        $participant = Participant::factory()->create([
            'patrol_id' => $patrol->id,
            'photo_path' => "participants/foto-{$suffix}.jpg",
            'doc_kta_path' => "participants/kta-{$suffix}.pdf",
        ]);

        Storage::disk('public')->put($participant->photo_path, 'foto');
        Storage::disk('public')->put($participant->doc_kta_path, 'kta');

        $account = User::factory()->create(['participant_id' => $participant->id]);
        $account->assignRole(RoleEnum::Participant);

        return [$participant, $account];
    }

    public function test_deleting_a_patrol_deletes_participant_accounts_and_uploaded_files(): void
    {
        Storage::fake('public');
        $this->seed(RoleSeeder::class);

        $gudepUser = User::factory()->create();
        $gudepUser->assignRole(RoleEnum::ScoutGroup);
        $group = ScoutGroup::factory()->create(['user_id' => $gudepUser->id, 'verification_status' => RegistrationStatusEnum::Draft]);

        $patrol = Patrol::factory()->create(['scout_group_id' => $group->id]);
        $otherPatrol = Patrol::factory()->create(['scout_group_id' => $group->id]);

        [$participantA, $accountA] = $this->participantWithAccount($patrol, 'a');
        [$participantB, $accountB] = $this->participantWithAccount($patrol, 'b');
        [$otherParticipant, $otherAccount] = $this->participantWithAccount($otherPatrol, 'c');

        Livewire::actingAs($gudepUser)
            ->test(PatrolsManagement::class)
            ->call('deletePatrol', $patrol->id);

        $this->assertModelMissing($patrol);
        $this->assertModelMissing($participantA);
        $this->assertModelMissing($accountA);
        $this->assertModelMissing($accountB);

        Storage::disk('public')->assertMissing($participantA->photo_path);
        Storage::disk('public')->assertMissing($participantA->doc_kta_path);
        Storage::disk('public')->assertMissing($participantB->photo_path);

        // Regu lain tidak terpengaruh
        $this->assertModelExists($otherParticipant);
        $this->assertModelExists($otherAccount);
        Storage::disk('public')->assertExists($otherParticipant->photo_path);
    }
}

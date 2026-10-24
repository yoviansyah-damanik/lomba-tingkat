<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipantScoutHistoryTest extends TestCase
{
    use RefreshDatabase;

    private function participantWithHistory(): array
    {
        $this->seed(RoleSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole(RoleEnum::Admin);

        $group = ScoutGroup::factory()->create();
        $patrol = Patrol::factory()->create(['scout_group_id' => $group->id]);

        $siaga = Participant::defaultScoutSiaga();
        $siaga['year'] = '2020';
        $siaga['levels']['mula'] = '2020';
        $siaga['activities'][0] = 'Gladian Siaga Kota';
        $siaga['tkk'][0] = 'TKK Pengamat Burung';
        $siaga['tkk'][4] = 'TKK Penjahit Rapi';

        $penggalang = Participant::defaultScoutPenggalang();
        $penggalang['levels']['ramu'] = '2023';
        $penggalang['activities'][1] = 'Jambore Cabang 2024';
        $penggalang['tkk_purwa'][0] = 'TKK Purwa Pioneering';
        $penggalang['tkk_madya'][2] = 'TKK Madya Navigasi';
        $penggalang['tkk_utama'][9] = 'TKK Utama Survival';

        $participant = Participant::factory()->create([
            'patrol_id' => $patrol->id,
            'scout_siaga' => $siaga,
            'scout_penggalang' => $penggalang,
        ]);

        return [$admin, $group, $participant];
    }

    public function test_contingent_detail_modal_shows_full_scout_history(): void
    {
        [$admin, $group] = $this->participantWithHistory();

        $this->actingAs($admin)
            ->get(route('admin.scout-groups.show', $group->id))
            ->assertOk()
            ->assertSee('Giat Siaga yang Telah Diikuti')
            ->assertSee('Gladian Siaga Kota')
            ->assertSee('TKK Pengamat Burung')
            ->assertSee('TKK Penjahit Rapi')
            ->assertSee('Giat Penggalang yang Telah Diikuti')
            ->assertSee('Jambore Cabang 2024')
            ->assertSee('TKK Purwa Pioneering')
            ->assertSee('TKK Madya Navigasi')
            ->assertSee('TKK Utama Survival');
    }

    public function test_participant_detail_page_shows_full_scout_history(): void
    {
        [$admin, , $participant] = $this->participantWithHistory();

        $this->actingAs($admin)
            ->get(route('admin.participants.show', $participant->id))
            ->assertOk()
            ->assertSee('Gladian Siaga Kota')
            ->assertSee('TKK Penjahit Rapi')
            ->assertSee('Jambore Cabang 2024')
            ->assertSee('TKK Utama Survival');
    }
    public function test_contingent_detail_chaperone_modal_shows_other_trainings(): void
    {
        $this->seed(RoleSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole(RoleEnum::Admin);

        $group = ScoutGroup::factory()->create();
        \App\Models\Chaperone::factory()->create([
            'scout_group_id' => $group->id,
            'other_trainings' => [['name' => 'Pelatihan Pertolongan Pertama', 'year' => '2022', 'location' => 'PMI Kota', 'notes' => 'Sertifikat']],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.scout-groups.show', $group->id))
            ->assertOk()
            ->assertSee('Kursus / Pelatihan di Luar Kepramukaan')
            ->assertSee('Pelatihan Pertolongan Pertama')
            ->assertSee('PMI Kota');
    }
}

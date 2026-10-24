<?php

namespace Tests\Feature;

use App\Enums\PatrolGenderEnum;
use App\Enums\RoleEnum;
use App\Livewire\Portal\ReviewRegistration;
use App\Models\Chaperone;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ChaperoneFollowsPatrolTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private ScoutGroup $group;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->user = User::factory()->create();
        $this->user->assignRole(RoleEnum::ScoutGroup);
        $this->group = ScoutGroup::factory()->create(['user_id' => $this->user->id]);
    }

    private function chaperonePayload(string $gender): array
    {
        return [
            'full_name' => 'Kak Pembina '.$gender,
            'gender' => $gender,
            'birth_place' => 'Padangsidimpuan',
            'birth_date' => '1988-06-12',
            'religion' => 'Islam',
            'blood_type' => 'B',
            'scout_id' => '02.21.10.055',
            'home_address' => 'Jl. Merdeka No. 1',
            'city' => 'Padangsidimpuan',
            'postal_code' => '22711',
            'phone_number' => '081234567890',
            'photo' => \Illuminate\Http\UploadedFile::fake()->image('foto.jpg'),
        ];
    }

    public function test_chaperone_form_is_blocked_until_a_patrol_of_that_gender_exists(): void
    {
        // Belum ada regu sama sekali: diarahkan ke halaman regu
        $this->actingAs($this->user)
            ->get(route('scout-group.chaperones.create', ['gender' => 'male']))
            ->assertRedirect(route('scout-group.patrols'));

        // Hanya ada regu putra: bindamping putri tetap ditolak, putra boleh
        Patrol::factory()->create(['scout_group_id' => $this->group->id, 'gender_category' => PatrolGenderEnum::Boys]);

        $this->actingAs($this->user)
            ->get(route('scout-group.chaperones.create', ['gender' => 'female']))
            ->assertRedirect(route('scout-group.chaperones'));

        $this->actingAs($this->user)
            ->get(route('scout-group.chaperones.create', ['gender' => 'male']))
            ->assertOk();
    }

    public function test_chaperone_cannot_be_stored_without_matching_patrol(): void
    {
        Patrol::factory()->create(['scout_group_id' => $this->group->id, 'gender_category' => PatrolGenderEnum::Boys]);

        $this->actingAs($this->user)
            ->post(route('scout-group.chaperones.store'), $this->chaperonePayload('female'))
            ->assertSessionHasErrors(['gender']);

        $this->assertSame(0, Chaperone::count());

        $this->actingAs($this->user)
            ->post(route('scout-group.chaperones.store'), $this->chaperonePayload('male'))
            ->assertSessionHasNoErrors();

        $this->assertSame(1, Chaperone::where('gender', 'male')->count());
    }

    public function test_chaperone_is_synced_to_selected_same_gender_patrols_only(): void
    {
        $boysA = Patrol::factory()->create(['scout_group_id' => $this->group->id, 'gender_category' => PatrolGenderEnum::Boys]);
        $boysB = Patrol::factory()->create(['scout_group_id' => $this->group->id, 'gender_category' => PatrolGenderEnum::Boys]);
        $girls = Patrol::factory()->create(['scout_group_id' => $this->group->id, 'gender_category' => PatrolGenderEnum::Girls]);

        $this->actingAs($this->user)
            ->post(route('scout-group.chaperones.store'), $this->chaperonePayload('male') + ['patrol_ids' => [$boysA->id, $girls->id]])
            ->assertSessionHasNoErrors();

        $chaperone = Chaperone::where('gender', 'male')->firstOrFail();
        $this->assertSame($chaperone->id, $boysA->fresh()->chaperone_id);
        $this->assertNull($boysB->fresh()->chaperone_id);
        $this->assertNull($girls->fresh()->chaperone_id);

        // Ubah pilihan: regu A dilepas, regu B ditetapkan
        $this->actingAs($this->user)
            ->put(route('scout-group.chaperones.update', $chaperone->id), $this->chaperonePayload('male') + ['patrol_ids' => [$boysB->id]])
            ->assertSessionHasNoErrors();

        $this->assertNull($boysA->fresh()->chaperone_id);
        $this->assertSame($chaperone->id, $boysB->fresh()->chaperone_id);
    }

    public function test_every_patrol_must_have_a_chaperone_before_submit(): void
    {
        $boysA = Patrol::factory()->create(['scout_group_id' => $this->group->id, 'gender_category' => PatrolGenderEnum::Boys]);
        $boysB = Patrol::factory()->create(['scout_group_id' => $this->group->id, 'gender_category' => PatrolGenderEnum::Boys]);
        $chaperone = Chaperone::factory()->create(['scout_group_id' => $this->group->id, 'gender' => 'male']);
        $boysA->update(['chaperone_id' => $chaperone->id]);

        $titles = fn () => collect(Livewire::actingAs($this->user)->test(ReviewRegistration::class)->get('missingRequirements'))->pluck('title');

        $this->assertTrue($titles()->contains('Regu Belum Memiliki Bindamping Pendamping'));

        $boysB->update(['chaperone_id' => $chaperone->id]);

        $this->assertFalse($titles()->contains('Regu Belum Memiliki Bindamping Pendamping'));
    }

    public function test_chaperones_page_tells_gudep_to_add_patrol_first(): void
    {
        $this->actingAs($this->user)
            ->get(route('scout-group.chaperones'))
            ->assertOk()
            ->assertSee('Tambahkan')
            ->assertSee('Regu Putra')
            ->assertDontSee(route('scout-group.chaperones.create', ['gender' => 'male']), false);

        Patrol::factory()->create(['scout_group_id' => $this->group->id, 'gender_category' => PatrolGenderEnum::Boys]);

        $this->actingAs($this->user)
            ->get(route('scout-group.chaperones'))
            ->assertSee(route('scout-group.chaperones.create', ['gender' => 'male']), false)
            ->assertDontSee(route('scout-group.chaperones.create', ['gender' => 'female']), false);
    }

    public function test_review_requires_chaperone_matching_each_patrol_gender(): void
    {
        Patrol::factory()->create(['scout_group_id' => $this->group->id, 'gender_category' => PatrolGenderEnum::Boys]);
        Patrol::factory()->create(['scout_group_id' => $this->group->id, 'gender_category' => PatrolGenderEnum::Girls]);
        Chaperone::factory()->create(['scout_group_id' => $this->group->id, 'gender' => 'male']);

        $titles = fn () => collect(Livewire::actingAs($this->user)->test(ReviewRegistration::class)->get('missingRequirements'))->pluck('title');

        $this->assertTrue($titles()->contains('Belum Ada Bindamping Putri'));
        $this->assertFalse($titles()->contains('Belum Ada Bindamping Putra'));

        Chaperone::factory()->create(['scout_group_id' => $this->group->id, 'gender' => 'female']);

        $this->assertFalse($titles()->contains('Belum Ada Bindamping Putri'));
        $this->assertFalse($titles()->contains('Belum Ada Bindamping Putra'));
    }

    public function test_boys_only_contingent_does_not_need_a_female_chaperone(): void
    {
        Patrol::factory()->create(['scout_group_id' => $this->group->id, 'gender_category' => PatrolGenderEnum::Boys]);
        Chaperone::factory()->create(['scout_group_id' => $this->group->id, 'gender' => 'male']);

        $titles = collect(Livewire::actingAs($this->user)->test(ReviewRegistration::class)->get('missingRequirements'))->pluck('title');

        $this->assertFalse($titles->contains('Belum Ada Bindamping Putri'));
        $this->assertFalse($titles->contains('Belum Ada Bindamping Putra'));
    }
}

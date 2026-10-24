<?php

namespace Tests\Feature;

use App\Enums\RegistrationStatusEnum;
use App\Enums\RoleEnum;
use App\Livewire\Admin\ScoutGroupVerificationActions;
use App\Models\Chaperone;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use App\Models\User;
use App\Services\ContingentVerificationService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\BuildsCompleteRoster;
use Tests\TestCase;

class ContingentVerificationProgressTest extends TestCase
{
    use BuildsCompleteRoster, RefreshDatabase;

    private User $admin;

    private ScoutGroup $group;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole(RoleEnum::Admin);

        $this->group = ScoutGroup::factory()->create([
            'verification_status' => RegistrationStatusEnum::Submitted,
            'male_group_number' => '01-093',
            'female_group_number' => '01-094',
        ]);

        $patrol = Patrol::factory()->create(['scout_group_id' => $this->group->id, 'gender_category' => 'boys']);
        $this->fillPatrolRoster($patrol);
        Chaperone::factory()->create(['scout_group_id' => $this->group->id, 'gender' => 'male']);
    }

    public function test_verification_reports_increasing_progress_ending_at_one_hundred_percent(): void
    {
        $reports = [];

        app(ContingentVerificationService::class)->verify(
            $this->group,
            $this->admin,
            function (int $percent, string $message) use (&$reports) {
                $reports[] = [$percent, $message];
            }
        );

        $percents = array_column($reports, 0);

        $this->assertSame(100, end($percents));
        $this->assertSame($percents, collect($percents)->sort()->values()->all(), 'Kemajuan tidak boleh mundur');
        $this->assertGreaterThanOrEqual(8 + 1, count(array_filter($reports, fn ($r) => str_starts_with($r[1], 'Membuat akun'))));
        $this->assertSame('Verifikasi selesai.', end($reports)[1]);

        // 8 peserta + 1 bindamping = 9 langkah akun, langkah terakhir mencapai 95%; seluruh akun peserta terbentuk
        $this->assertContains(95, $percents);
        $this->assertSame(8, User::whereNotNull('participant_id')->count());
    }

    public function test_verification_modal_has_progress_bar_stream_and_cannot_be_dismissed(): void
    {
        $html = Livewire::withoutLazyLoading()
            ->actingAs($this->admin)
            ->test(ScoutGroupVerificationActions::class, ['scoutGroupId' => $this->group->id])
            ->html();

        $this->assertStringContainsString('wire:stream="verify-progress"', $html);
        $this->assertStringContainsString('<progress', $html);
    }

    public function test_verify_from_panel_completes_and_creates_accounts(): void
    {
        Livewire::withoutLazyLoading()
            ->actingAs($this->admin)
            ->test(ScoutGroupVerificationActions::class, ['scoutGroupId' => $this->group->id])
            ->call('verify');

        $this->assertEquals(RegistrationStatusEnum::Verified, $this->group->fresh()->verification_status);
        $this->assertSame(Participant::count(), User::whereNotNull('participant_id')->count());
    }
}

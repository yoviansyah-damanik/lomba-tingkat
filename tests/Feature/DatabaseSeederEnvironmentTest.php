<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\AssessmentCriterion;
use App\Models\AttendanceSession;
use App\Models\Chaperone;
use App\Models\Competition;
use App\Models\CompetitionSchedule;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\Role;
use App\Models\ScoutGroup;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DatabaseSeederEnvironmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_seeds_admin_accounts_and_official_juknis_data_only(): void
    {
        $this->app['env'] = 'production';

        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true])->assertSuccessful();

        $this->assertSame(['admin', 'superadmin'], User::orderBy('username')->pluck('username')->all());
        $this->assertGreaterThan(0, Role::count());
        $this->assertSame(0, Patrol::count());

        // Data resmi Juknis tetap ada di production
        $this->assertSame(1, Event::count());
        $this->assertSame(18, Competition::count());
        $this->assertSame(36, CompetitionSchedule::count());
        $this->assertGreaterThan(0, AssessmentCriterion::count());
        $this->assertGreaterThan(0, AttendanceSession::count());
        $this->assertGreaterThan(0, Announcement::count());

        // Administrator adalah Panitia yang ditunjuk; Superadmin berdiri sendiri
        $admin = User::where('username', 'admin')->first();
        $this->assertTrue($admin->isCommittee() && $admin->isAdministrator() && ! $admin->isSuperadmin());
        $this->assertTrue(User::where('username', 'superadmin')->first()->isSuperadmin());

        foreach (['superadmin', 'admin'] as $username) {
            $this->assertTrue(Hash::check('password123', User::where('username', $username)->first()->password));
        }
    }

    public function test_local_seeds_complete_sample_data(): void
    {
        $this->app['env'] = 'local';
        Storage::fake('public');

        $this->seed(DatabaseSeeder::class);

        $this->assertGreaterThan(2, User::count());
        $this->assertSame(1, Event::count());
        $this->assertSame(4, Patrol::where('status', 'verified')->count());
        $this->assertSame(25, Patrol::where('status', 'submitted')->count());
        $this->assertSame(1, ScoutGroup::where('verification_status', 'verified')->count());
        $this->assertTrue(Hash::check('password123', User::where('username', 'admin')->first()->password));

        // Seluruh ID seed mengikuti format resmi: A- aparatur, G- gudep, P- peserta, B- bindamping (akun contoh bisa ber-ID aparatur)
        foreach (User::all() as $user) {
            $pattern = $user->isScoutGroup() ? '/^G-\d{5}\.\d{5}$/' : '/^[ABP]-\d{6}$/';
            $this->assertMatchesRegularExpression($pattern, (string) $user->id_number, "ID akun {$user->username}");
        }
        foreach (ScoutGroup::all() as $group) {
            $this->assertMatchesRegularExpression('/^G-\d{5}\.\d{5}$/', $group->registration_code);
            $this->assertSame($group->registration_code, $group->user->username);
            $this->assertSame($group->registration_code, $group->user->id_number);
        }
        $this->assertSame(0, Participant::where('id_number', 'not like', 'P-%')->count());
        $this->assertSame(0, Chaperone::where('id_number', 'not like', 'B-%')->count());
    }

    public function test_other_environments_seed_base_data_without_samples(): void
    {
        $this->seed(DatabaseSeeder::class); // testing

        $this->assertSame(1, Event::count());
        $this->assertSame(0, Patrol::where('status', 'verified')->count());
    }

    public function test_dev_environment_also_seeds_sample_data(): void
    {
        $this->app['env'] = 'dev';
        Storage::fake('public');

        $this->seed(DatabaseSeeder::class);

        $this->assertSame(4, Patrol::where('status', 'verified')->count());
        $this->assertSame(25, Patrol::where('status', 'submitted')->count());
        $this->assertSame(1, ScoutGroup::where('verification_status', 'verified')->count());
    }
}

<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Patrol;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederEnvironmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_seeds_only_roles_superadmin_and_administrator_with_default_password(): void
    {
        $this->app['env'] = 'production';

        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true])->assertSuccessful();

        $this->assertSame(['admin', 'superadmin'], User::orderBy('username')->pluck('username')->all());
        $this->assertGreaterThan(0, Role::count());
        $this->assertSame(0, Event::count());
        $this->assertSame(0, Patrol::count());

        foreach (['superadmin', 'admin'] as $username) {
            $this->assertTrue(Hash::check('password123', User::where('username', $username)->first()->password));
        }
    }

    public function test_local_seeds_complete_sample_data(): void
    {
        $this->app['env'] = 'local';

        $this->seed(DatabaseSeeder::class);

        $this->assertGreaterThan(2, User::count());
        $this->assertSame(1, Event::count());
        $this->assertSame(22, Patrol::where('status', 'verified')->count());
        $this->assertTrue(Hash::check('password123', User::where('username', 'admin')->first()->password));
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

        $this->seed(DatabaseSeeder::class);

        $this->assertSame(22, Patrol::where('status', 'verified')->count());
    }
}

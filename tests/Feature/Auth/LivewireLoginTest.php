<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_livewire_form(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSeeLivewire(Login::class);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create();

        Livewire::test(Login::class)
            ->set('username', $user->username)
            ->set('password', 'password')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(config('fortify.home'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_credentials_do_not_authenticate_and_clear_password(): void
    {
        $user = User::factory()->create();

        Livewire::test(Login::class)
            ->set('username', $user->username)
            ->set('password', 'salah')
            ->call('login')
            ->assertSet('password', '')
            ->assertNoRedirect();

        $this->assertGuest();
    }

    public function test_required_fields_are_validated(): void
    {
        Livewire::test(Login::class)
            ->call('login')
            ->assertHasErrors(['username' => 'required', 'password' => 'required']);
    }

    public function test_login_is_rate_limited_after_five_failed_attempts(): void
    {
        $user = User::factory()->create();
        $component = Livewire::test(Login::class)->set('username', $user->username);

        foreach (range(1, 5) as $i) {
            $component->set('password', 'salah')->call('login');
        }

        $component->set('password', 'password')->call('login')->assertNoRedirect();
        $this->assertGuest();
    }

    public function test_user_with_two_factor_is_redirected_to_challenge(): void
    {
        $user = User::factory()->create([
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['code'])),
            'two_factor_confirmed_at' => now(),
        ]);

        Livewire::test(Login::class)
            ->set('username', $user->username)
            ->set('password', 'password')
            ->call('login')
            ->assertRedirect(route('two-factor.login'));

        $this->assertGuest();
        $this->assertSame($user->id, session('login.id'));
    }

    public function test_username_login_is_case_insensitive_and_needs_no_email(): void
    {
        $user = User::factory()->create(['username' => 'pembina.satu']);

        $this->assertFalse(Schema::hasColumn('users', 'email'));

        Livewire::test(Login::class)
            ->set('username', '  Pembina.Satu ')
            ->set('password', 'password')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(config('fortify.home'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_password_reset_by_email_is_not_available(): void
    {
        $this->assertFalse(Route::has('password.request'));
        $this->assertFalse(Route::has('verification.notice'));
    }
}

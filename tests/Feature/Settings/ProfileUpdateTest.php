<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $this->actingAs($user = User::factory()->create());

        $this->get(route('profile.edit'))->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();
        $username = $user->username;

        $this->actingAs($user);

        $response = Livewire::test('pages::settings.profile')
            ->set('name', 'Test User')
            ->call('updateProfileInformation');

        $response->assertHasNoErrors();

        $user->refresh();

        $this->assertEquals('Test User', $user->name);
        $this->assertEquals($username, $user->username);
    }

    public function test_username_cannot_be_changed_from_profile(): void
    {
        $user = User::factory()->create();
        $username = $user->username;

        $this->actingAs($user);

        Livewire::test('pages::settings.profile')
            ->set('name', 'Test User')
            ->call('updateProfileInformation');

        $this->assertSame($username, $user->refresh()->username);
    }

    public function test_profile_page_has_no_delete_account_option(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('profile.edit'))
            ->assertOk()
            ->assertDontSee(__('Delete account'));
    }
}

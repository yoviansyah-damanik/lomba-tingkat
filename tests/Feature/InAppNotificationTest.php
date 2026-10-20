<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Livewire\Notifications\Bell;
use App\Models\User;
use App\Services\InAppNotificationService;
use App\Services\WebPushService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InAppNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $gudep;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->admin = User::factory()->create(['is_active' => true]);
        $this->admin->assignRole(RoleEnum::Admin);

        $this->gudep = User::factory()->create(['is_active' => true]);
        $this->gudep->assignRole(RoleEnum::ScoutGroup);
    }

    public function test_notification_is_delivered_only_to_targeted_role(): void
    {
        app(InAppNotificationService::class)->toTarget(RoleEnum::Admin->value, [
            'title' => 'Pendaftaran Gugus Depan Baru',
            'body' => 'SMP Uji baru mendaftar.',
            'url' => '/admin/scout-groups',
        ]);

        $this->assertSame(1, $this->admin->unreadNotifications()->count());
        $this->assertSame(0, $this->gudep->unreadNotifications()->count());
    }

    public function test_broadcast_without_target_reaches_all_active_accounts_but_not_guest_target(): void
    {
        $inactive = User::factory()->create(['is_active' => false]);

        $service = app(InAppNotificationService::class);
        $this->assertSame(0, $service->toTarget('guest', ['title' => 'Uji', 'body' => 'x']));
        $service->toTarget(null, ['title' => 'Pengumuman', 'body' => 'Semua akun']);

        $this->assertSame(1, $this->admin->unreadNotifications()->count());
        $this->assertSame(1, $this->gudep->unreadNotifications()->count());
        $this->assertSame(0, $inactive->unreadNotifications()->count());
    }

    public function test_web_push_broadcast_and_direct_message_also_create_in_app_notifications(): void
    {
        $webPush = app(WebPushService::class);

        $webPush->broadcastNotification(['title' => 'Siaran', 'body' => 'Untuk juri dan admin'], RoleEnum::Admin->value);
        $webPush->notifyUser($this->gudep, ['title' => 'Pendaftaran Disetujui', 'body' => 'Selamat.']);

        $this->assertSame('Siaran', $this->admin->notifications()->first()->data['title']);
        $this->assertSame('Pendaftaran Disetujui', $this->gudep->notifications()->first()->data['title']);
    }

    public function test_bell_shows_unread_count_and_marks_notifications_as_read(): void
    {
        $service = app(InAppNotificationService::class);
        $service->toUsers($this->gudep, ['title' => 'Satu', 'body' => 'a', 'url' => '/dashboard']);
        $service->toUsers($this->gudep, ['title' => 'Dua', 'body' => 'b']);

        $first = $this->gudep->notifications()->where('data->title', 'Satu')->first();

        $component = Livewire::actingAs($this->gudep)->test(Bell::class, ['lazy' => false])
            ->assertSee('Satu')
            ->assertSee('Dua')
            ->assertSee('2 baru');

        $component->call('open', $first->id)->assertRedirect('/dashboard');
        $this->assertSame(1, $this->gudep->unreadNotifications()->count());

        $component->call('markAllAsRead');
        $this->assertSame(0, $this->gudep->fresh()->unreadNotifications()->count());
    }

    public function test_user_cannot_open_other_users_notification(): void
    {
        app(InAppNotificationService::class)->toUsers($this->admin, ['title' => 'Rahasia', 'body' => 'x']);
        $notification = $this->admin->notifications()->first();

        Livewire::actingAs($this->gudep)->test(Bell::class, ['lazy' => false])
            ->call('open', $notification->id)
            ->assertStatus(404);
    }

    public function test_dashboard_renders_notification_bell(): void
    {
        $this->actingAs($this->admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('notifications.bell', false);
    }

    public function test_public_header_shows_bell_only_for_logged_in_users(): void
    {
        $this->get(route('schedules'))->assertOk()->assertDontSee('notifications.bell', false);

        $this->actingAs($this->admin)->get(route('schedules'))
            ->assertOk()
            ->assertSee('notifications.bell', false);
    }
}

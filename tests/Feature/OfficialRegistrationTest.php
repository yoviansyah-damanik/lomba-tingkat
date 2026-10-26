<?php

namespace Tests\Feature;

use App\Livewire\Admin\OfficialRegistrationLink;
use App\Livewire\Auth\OfficialRegistrationForm;
use App\Models\Event;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class OfficialRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'rahasia-token-uji-1234567890abcdef';

    private function event(array $overrides = []): Event
    {
        return Event::create(array_merge([
            'title' => 'LT-III 2026',
            'start_date' => '2026-11-14',
            'end_date' => '2026-11-17',
            'location' => 'Pulo Baok',
            'is_active' => true,
            'official_registration_token' => self::TOKEN,
        ], $overrides));
    }

    private function fillForm(): Testable
    {
        Storage::fake('public');

        return Livewire::test(OfficialRegistrationForm::class, ['token' => self::TOKEN])
            ->set('name', 'Kak Budi')
            ->set('phone_number', '081234567890')
            ->set('scout_id', '01.02.03.04')
            ->set('position', 'Anggota Dewan Juri')
            ->set('photo', UploadedFile::fake()->image('foto.jpg', 300, 400))
            ->set('institution', 'Kwarran Padangsidimpuan Utara')
            ->set('roles', ['judge']);
    }

    public function test_link_is_closed_by_default(): void
    {
        $this->seed(RoleSeeder::class);
        $this->event();

        $this->get(route('register.official', ['token' => self::TOKEN]))->assertOk()->assertSee('Pendaftaran Aparatur Sedang Ditutup');

        $this->fillForm()->call('register')->assertHasErrors('name');
        $this->assertSame(0, User::whereHas('roles', fn ($q) => $q->where('name', 'judge'))->count());
    }

    public function test_open_link_creates_inactive_official_account_with_chosen_role(): void
    {
        $this->seed(RoleSeeder::class);
        $this->event(['is_official_registration_open' => true]);

        $this->get(route('register.official', ['token' => self::TOKEN]))->assertOk()->assertSee('Kirim Pendaftaran');

        $component = $this->fillForm()->call('register')->assertHasNoErrors();

        $user = User::where('name', 'Kak Budi')->firstOrFail();
        $this->assertSame($user->id_number, $component->get('registeredId'));
        $this->assertSame($user->id_number, $user->username);
        $this->assertFalse((bool) $user->is_active);
        $this->assertTrue($user->hasRole('judge'));
        $this->assertFalse($user->hasRole('committee'));
        $this->assertTrue($user->must_change_password);
        $this->assertNotEmpty($component->get('registeredPassword'));
        $this->assertTrue(Hash::check($component->get('registeredPassword'), $user->password));
        $this->assertSame('Anggota Dewan Juri', $user->position);
        $this->assertSame('01.02.03.04', $user->scout_id);
        Storage::disk('public')->assertExists($user->avatar_path);
    }

    public function test_position_nta_and_photo_are_required_and_photo_must_be_an_image(): void
    {
        $this->seed(RoleSeeder::class);
        $this->event(['is_official_registration_open' => true]);

        $this->fillForm()->set('position', '')->call('register')->assertHasErrors(['position' => 'required']);
        $this->fillForm()->set('scout_id', '')->call('register')->assertHasErrors(['scout_id' => 'required']);
        $this->fillForm()->set('photo', null)->call('register')->assertHasErrors(['photo' => 'required']);
        $this->fillForm()->set('photo', UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'))->call('register')->assertHasErrors('photo');
        $this->assertSame(0, User::where('name', 'Kak Budi')->count());
    }

    public function test_registration_window_is_respected(): void
    {
        $this->seed(RoleSeeder::class);
        $event = $this->event(['is_official_registration_open' => true, 'official_registration_end_at' => now()->subMinute()]);

        $this->assertFalse($event->isOfficialRegistrationOpen());

        $event->update(['official_registration_end_at' => now()->addDay(), 'official_registration_start_at' => now()->addHour()]);
        $this->assertFalse($event->fresh()->isOfficialRegistrationOpen());

        $event->update(['official_registration_start_at' => now()->subHour()]);
        $this->assertTrue($event->fresh()->isOfficialRegistrationOpen());
    }

    public function test_committee_position_is_required_for_committee_and_shown_on_card_label(): void
    {
        $this->seed(RoleSeeder::class);
        $this->event(['is_official_registration_open' => true]);

        $this->fillForm()->set('roles', ['committee'])->call('register')->assertHasErrors(['committee_position' => 'required']);

        $this->fillForm()->set('roles', ['committee', 'judge'])->set('committee_position', 'Sekretaris')->call('register')->assertHasNoErrors();
        $user = User::where('name', 'Kak Budi')->firstOrFail();
        $this->assertSame('Sekretaris / Dewan Juri', $user->cardPositionLabel());

        $user->roles()->sync(\App\Models\Role::where('name', 'committee')->pluck('id'));
        $this->assertSame('Sekretaris', $user->fresh()->cardPositionLabel());

        $user->roles()->sync(\App\Models\Role::where('name', 'judge')->pluck('id'));
        $this->assertSame('Dewan Juri', $user->fresh()->cardPositionLabel());
    }

    public function test_role_is_validated(): void
    {
        $this->seed(RoleSeeder::class);
        $this->event(['is_official_registration_open' => true]);

        $this->fillForm()->set('roles', ['admin'])->call('register')->assertHasErrors('roles.0');
        $this->fillForm()->set('roles', [])->call('register')->assertHasErrors('roles');
        $this->assertSame(0, User::where('name', 'Kak Budi')->count());
    }

    public function test_admin_and_superadmin_can_configure_the_gate_but_others_cannot(): void
    {
        $this->seed(RoleSeeder::class);
        $event = $this->event();

        foreach (['admin', 'superadmin'] as $role) {
            $event->update(['is_official_registration_open' => false, 'official_registration_end_at' => null]);
            $user = User::factory()->create();
            $user->assignRole($role);

            Livewire::actingAs($user)
                ->test(OfficialRegistrationLink::class)
                ->set('is_official_registration_open', true)
                ->set('official_registration_end_at', now()->addWeek()->format('Y-m-d\TH:i'))
                ->call('saveSettings')
                ->assertHasNoErrors();

            $event->refresh();
            $this->assertTrue($event->is_official_registration_open);
            $this->assertTrue($event->isOfficialRegistrationOpen());
        }

        $this->assertDatabaseHas('audit_logs', ['action' => 'official_registration_settings_updated']);

        $judge = User::factory()->create();
        $judge->assignRole('judge');
        Livewire::actingAs($judge)->test(OfficialRegistrationLink::class)->call('saveSettings')->assertForbidden();
    }

    public function test_wrong_or_missing_token_returns_not_found_and_login_page_hides_the_link(): void
    {
        $this->seed(RoleSeeder::class);
        $this->event(['is_official_registration_open' => true]);

        $this->get(route('register.official', ['token' => 'salah']))->assertNotFound();
        $this->get('/register/aparatur')->assertNotFound();

        $this->get(route('login'))->assertOk()->assertDontSee('aparatur', false)->assertDontSee(self::TOKEN);

        Event::first()->update(['official_registration_token' => null]);
        $this->get(route('register.official', ['token' => self::TOKEN]))->assertNotFound();
    }

    public function test_token_regeneration_invalidates_old_link_and_is_admin_only(): void
    {
        $this->seed(RoleSeeder::class);
        $this->event();
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Livewire::actingAs($admin)->test(OfficialRegistrationLink::class)->call('regenerate');

        Auth::logout(); // rute pendaftaran khusus tamu

        $this->assertNotSame(self::TOKEN, Event::first()->official_registration_token);
        $this->get(route('register.official', ['token' => self::TOKEN]))->assertNotFound();
        $this->get(Event::first()->officialRegistrationUrl())->assertOk();
        $this->assertDatabaseHas('audit_logs', ['action' => 'official_registration_token_regenerated']);

        $judge = User::factory()->create();
        $judge->assignRole('judge');
        Livewire::actingAs($judge)->test(OfficialRegistrationLink::class)->call('regenerate')->assertForbidden();
    }
    public function test_successful_registration_notifies_superadmin_and_administrator(): void
    {
        $this->seed(RoleSeeder::class);
        $this->event(['is_official_registration_open' => true]);

        $superadmin = User::factory()->create(['is_active' => true]);
        $superadmin->assignRole('superadmin');
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('admin');

        // Pendaftaran gagal validasi tidak mengirim notifikasi
        $this->fillForm()->set('position', '')->call('register')->assertHasErrors('position');
        $this->assertSame(0, $superadmin->notifications()->count());

        $this->fillForm()->call('register')->assertHasNoErrors();

        $notification = $superadmin->notifications()->firstOrFail();
        $this->assertSame('Pendaftaran Aparatur Baru', $notification->data['title']);
        $this->assertStringContainsString('Kak Budi', $notification->data['body']);
        $this->assertStringContainsString('Dewan Juri', $notification->data['body']);
        $this->assertSame(route('admin.officials.index'), $notification->data['url']);
        $this->assertSame('Pendaftaran Aparatur Baru', $admin->notifications()->firstOrFail()->data['title']);

        // Akun biasa (mis. juri) tidak ikut menerima
        $judge = User::factory()->create(['is_active' => true]);
        $judge->assignRole('judge');
        $this->fillForm()->set('name', 'Kak Siti')->call('register')->assertHasNoErrors();
        $this->assertSame(0, $judge->notifications()->count());
        $this->assertSame(2, $admin->notifications()->count());
    }
}
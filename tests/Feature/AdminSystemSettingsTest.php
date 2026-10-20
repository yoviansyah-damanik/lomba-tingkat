<?php

namespace Tests\Feature;

use App\Livewire\Admin\SystemSettingsManagement;
use App\Models\AuditLog;
use App\Models\Event;
use App\Models\User;
use Database\Seeders\EventSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminSystemSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $regularUser;
    protected Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RoleSeeder::class,
            UserSeeder::class,
            EventSeeder::class,
        ]);

        $this->admin = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->first();
        $this->regularUser = User::whereHas('roles', fn ($q) => $q->where('name', 'committee'))->first();
        $this->event = Event::first();
    }

    /**
     * Memastikan tamu (guest) tidak dapat mengakses pengaturan sistem
     */
    public function test_guest_cannot_access_system_settings(): void
    {
        $response = $this->get(route('admin.settings.system'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Memastikan pengguna non-admin dilarang mengakses pengaturan sistem
     */
    public function test_non_admin_cannot_access_system_settings(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('admin.settings.system'));
        $response->assertStatus(403);
    }

    /**
     * Memastikan admin dapat membuka halaman konfigurasi sistem
     */
    public function test_admin_can_access_system_settings(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.settings.system'));
        $response->assertStatus(200);
        $response->assertSee('Konfigurasi Sistem & Master Kegiatan');

        Livewire::actingAs($this->admin)
            ->test(SystemSettingsManagement::class)
            ->assertStatus(200)
            ->assertSet('title', $this->event->title)
            ->assertSet('location', $this->event->location);
    }

    /**
     * Memastikan admin dapat memperbarui pengaturan master kegiatan
     */
    public function test_admin_can_update_event_settings(): void
    {
        Livewire::actingAs($this->admin)
            ->test(SystemSettingsManagement::class)
            ->set('title', 'Lomba Tingkat III Kwarcab Padangsidimpuan 2026 - Edisi Juara')
            ->set('theme', 'Pramuka Penggalang Tangguh Memimpin Bangsa')
            ->set('location', 'Bumi Perkemahan Tor Simarsayang Indah')
            ->set('start_date', '2026-11-01')
            ->set('end_date', '2026-11-05')
            ->set('is_registration_open', false)
            ->set('contact_phone', '081234567899')
            ->call('saveSettings')
            ->assertHasNoErrors()
            ->assertDispatched('toast-show');

        $this->event->refresh();
        $this->assertEquals('Lomba Tingkat III Kwarcab Padangsidimpuan 2026 - Edisi Juara', $this->event->title);
        $this->assertEquals('Pramuka Penggalang Tangguh Memimpin Bangsa', $this->event->theme);
        $this->assertFalse((bool) $this->event->is_registration_open);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'event_settings_updated',
            'model_id' => $this->event->id,
        ]);
    }

    /**
     * Memastikan admin dapat mengaktifkan konfigurasi sembunyikan identitas regu & pangkalan
     */
    public function test_admin_can_toggle_hide_patrol_identities_setting(): void
    {
        $this->assertFalse((bool) $this->event->hide_patrol_identities);

        Livewire::actingAs($this->admin)
            ->test(SystemSettingsManagement::class)
            ->set('hide_patrol_identities', true)
            ->call('saveSettings')
            ->assertHasNoErrors()
            ->assertDispatched('toast-show');

        $this->event->refresh();
        $this->assertTrue((bool) $this->event->hide_patrol_identities);
    }

    /**
     * Memastikan nama dan NTA penandatangan dokumen resmi dapat disimpan dan dibaca model Event
     */
    public function test_admin_can_save_official_signatories(): void
    {
        Livewire::actingAs($this->admin)
            ->test(SystemSettingsManagement::class)
            ->set('committee_chair_name', 'Drs. Ahmad Siregar')
            ->set('committee_chair_nta', '02.08.01.001')
            ->set('judge_chair_name', 'Hj. Rosmawati Harahap')
            ->set('kwarcab_chair_name', 'H. Syahrul Daulay')
            ->call('saveSettings')
            ->assertHasNoErrors();

        $event = Event::first();
        $this->assertSame(['name' => 'Drs. Ahmad Siregar', 'nta' => '02.08.01.001'], $event->signatory('committee'));
        $this->assertSame(['name' => 'Hj. Rosmawati Harahap', 'nta' => null], $event->signatory('judge'));
        $this->assertSame(['name' => 'H. Syahrul Daulay', 'nta' => null], $event->signatory('kwarcab'));
    }
}

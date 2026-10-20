<?php

namespace Tests\Feature;

use App\Livewire\Admin\PushBroadcastManagement;
use App\Models\AuditLog;
use App\Models\PushSubscription;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PwaAndWebPushTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RoleSeeder::class,
            UserSeeder::class,
        ]);
    }

    /**
     * Memastikan file manifest PWA tersedia dan format JSON valid
     */
    public function test_pwa_manifest_exists_and_is_valid_json(): void
    {
        $manifestPath = public_path('manifest.json');
        $this->assertFileExists($manifestPath);

        $content = file_get_contents($manifestPath);
        $json = json_decode($content, true);

        $this->assertIsArray($json);
        $this->assertEquals('Lomba Tingkat III Kwarcab Padangsidimpuan 2026', $json['name']);
        $this->assertEquals('LT III 2026', $json['short_name']);
        $this->assertEquals('standalone', $json['display']);
        $this->assertEquals('#d97706', $json['theme_color']);
        $this->assertNotEmpty($json['icons']);
    }

    /**
     * Memastikan file service worker tersedia dengan event listener wajib
     */
    public function test_pwa_service_worker_exists_and_has_push_handlers(): void
    {
        $swPath = public_path('sw.js');
        $this->assertFileExists($swPath);

        $content = file_get_contents($swPath);
        $this->assertStringContainsString("addEventListener('install'", $content);
        $this->assertStringContainsString("addEventListener('fetch'", $content);
        $this->assertStringContainsString("addEventListener('push'", $content);
        $this->assertStringContainsString("addEventListener('notificationclick'", $content);
    }

    /**
     * Memastikan halaman fallback offline HTML tersedia
     */
    public function test_offline_fallback_page_exists(): void
    {
        $offlinePath = public_path('offline.html');
        $this->assertFileExists($offlinePath);

        $content = file_get_contents($offlinePath);
        $this->assertStringContainsString('Mode Luar Jaringan', $content);
    }

    /**
     * Memastikan API VAPID public key dapat diakses publik
     */
    public function test_vapid_public_key_endpoint_returns_valid_key(): void
    {
        $response = $this->getJson(route('push.vapid'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['publicKey']);
        $this->assertNotEmpty($response->json('publicKey'));
    }

    /**
     * Memastikan pendaftaran subscription web push berhasil
     */
    public function test_push_subscription_store_and_unsubscribe(): void
    {
        $payload = [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/sample-token-12345',
            'keys' => [
                'p256dh' => 'sample-p256dh-key',
                'auth' => 'sample-auth-secret',
            ],
            'content_encoding' => 'aes128gcm',
            'device_label' => 'Android Browser',
        ];

        // Registrasi subscription
        $response = $this->postJson(route('push.subscribe'), $payload);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('push_subscriptions', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/sample-token-12345',
            'content_encoding' => 'aes128gcm',
        ]);

        // Unsubscribe
        $delResponse = $this->deleteJson(route('push.unsubscribe'), [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/sample-token-12345',
        ]);
        $delResponse->assertStatus(200);
        $delResponse->assertJson(['success' => true]);

        $this->assertDatabaseMissing('push_subscriptions', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/sample-token-12345',
        ]);
    }

    /**
     * Memastikan tamu (guest) tidak dapat mengakses halaman manajemen siaran
     */
    public function test_guest_cannot_access_push_broadcast_management(): void
    {
        $response = $this->get(route('admin.push-broadcast.index'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Memastikan admin dapat mengakses halaman siaran notifikasi
     */
    public function test_admin_can_access_push_broadcast_management(): void
    {
        $admin = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->first();

        $response = $this->actingAs($admin)->get(route('admin.push-broadcast.index'));
        $response->assertStatus(200);
        $response->assertSee('Siaran Notifikasi Web Push');
    }

    /**
     * Memastikan admin dapat mengirim siaran notifikasi web push via Livewire
     */
    public function test_admin_can_send_push_broadcast(): void
    {
        $admin = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->first();

        // Buat dummy subscription
        PushSubscription::create([
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-sub-1',
            'public_key' => 'key1',
            'auth_token' => 'token1',
            'content_encoding' => 'aes128gcm',
            'device_label' => 'Android Browser',
        ]);

        Livewire::actingAs($admin)
            ->test(PushBroadcastManagement::class)
            ->set('title', 'Panggilan Sesi 2 Pos Penjelajahan')
            ->set('body', 'Seluruh regu penggalang segera memasuki gerbang pos penjelajahan sektor barat.')
            ->set('actionUrl', '/schedules')
            ->set('urgencyTag', 'urgent')
            ->call('sendBroadcast')
            ->assertHasNoErrors()
            ->assertSee('Siaran notifikasi berhasil dikirimkan')
            ->assertSet('title', '');

        // Audit log tercatat
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'push_broadcast_sent',
            'user_id' => $admin->id,
        ]);
    }

    /**
     * Memastikan notifikasi mengambang instalasi PWA terpasang pada layout publik dan dasbor
     */
    public function test_floating_pwa_install_banner_is_rendered_in_public_and_admin_layouts(): void
    {
        // 1. Tampilan publik (beranda)
        $publicResponse = $this->get('/');
        $publicResponse->assertOk();
        $publicResponse->assertSee('Pasang Aplikasi LT III di Layar Utama (Homescreen)');
        $publicResponse->assertSee('Akses lebih cepat dengan mode offline terintegrasi tanpa perlu membuka browser berulang kali.');
        $publicResponse->assertSee('fixed bottom-4 right-4');

        // 2. Tampilan dasbor terotentikasi
        $admin = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->first();
        $adminResponse = $this->actingAs($admin)->get(route('dashboard'));
        $adminResponse->assertOk();
        $adminResponse->assertSee('Pasang Aplikasi LT III di Layar Utama (Homescreen)');
        $adminResponse->assertSee('Akses lebih cepat dengan mode offline terintegrasi tanpa perlu membuka browser berulang kali.');
    }
}

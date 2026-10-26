<?php

namespace Tests\Feature;

use App\Livewire\Admin\AnnouncementForm;
use App\Livewire\Admin\AnnouncementsManagement;
use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\User;
use Database\Seeders\AnnouncementSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminAnnouncementsManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RoleSeeder::class,
            UserSeeder::class,
            AnnouncementSeeder::class,
        ]);

        $this->admin = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->first();
        $this->regularUser = User::where('username', 'panitia')->first();
    }

    /**
     * Memastikan tamu tidak dapat mengakses manajemen pengumuman admin
     */
    public function test_guest_cannot_access_announcements_management(): void
    {
        $response = $this->get(route('admin.announcements.index'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Memastikan pengguna non-admin dilarang mengakses
     */
    public function test_non_admin_cannot_access_announcements_management(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('admin.announcements.index'));
        $response->assertStatus(403);
    }

    /**
     * Memastikan admin dapat mengakses halaman manajemen pengumuman
     */
    public function test_admin_can_access_announcements_management(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.announcements.index'));
        $response->assertStatus(200);
        $response->assertSee('Manajemen Pengumuman Kegiatan');

        Livewire::actingAs($this->admin)
            ->test(AnnouncementsManagement::class)
            ->assertStatus(200);
    }

    /**
     * Memastikan admin dapat membuat pengumuman baru
     */
    public function test_admin_can_create_announcement(): void
    {
        Livewire::actingAs($this->admin)
            ->test(AnnouncementForm::class)
            ->set('title', 'Instruksi Pemasangan Tenda Sektor Barat')
            ->set('category', 'technical')
            ->set('summary', 'Seluruh kontingen dipersilakan memulai pendirian tenda regu mulai pukul 08:00 WIB.')
            ->set('content', 'Materi instruksi lengkap mengenai standar tapak perkemahan, batas patok, dan sanitasi lingkungan.')
            ->set('is_published', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.announcements.index'));

        $announcement = Announcement::where('title', 'Instruksi Pemasangan Tenda Sektor Barat')->first();
        $this->assertNotNull($announcement);
        $this->assertEquals('technical', $announcement->category);
        $this->assertTrue($announcement->is_published);
        $this->assertNotEmpty($announcement->slug);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'announcement_created',
            'model_id' => $announcement->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'push_broadcast_sent',
        ]);
    }

    /**
     * Memastikan admin dapat memperbarui pengumuman
     */
    public function test_admin_can_update_announcement(): void
    {
        $announcement = Announcement::first();

        Livewire::actingAs($this->admin)
            ->test(AnnouncementForm::class, ['id' => $announcement->id])
            ->set('title', 'Judul Pengumuman Diperbarui')
            ->set('category', 'urgent')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.announcements.index'));

        $announcement->refresh();
        $this->assertEquals('Judul Pengumuman Diperbarui', $announcement->title);
        $this->assertEquals('urgent', $announcement->category);
    }

    /**
     * Memastikan admin dapat mengubah status publikasi (terbitkan / jadikan draf)
     */
    public function test_admin_can_toggle_publish_status(): void
    {
        $announcement = Announcement::first();
        $this->assertTrue($announcement->is_published);

        Livewire::actingAs($this->admin)
            ->test(AnnouncementsManagement::class)
            ->call('togglePublish', $announcement->id)
            ->assertDispatched('toast-show');

        $announcement->refresh();
        $this->assertFalse($announcement->is_published);

        Livewire::actingAs($this->admin)
            ->test(AnnouncementsManagement::class)
            ->call('togglePublish', $announcement->id)
            ->assertDispatched('toast-show');

        $announcement->refresh();
        $this->assertTrue($announcement->is_published);
    }

    /**
     * Memastikan admin dapat menghapus pengumuman
     */
    public function test_admin_can_delete_announcement(): void
    {
        $announcement = Announcement::first();
        $id = $announcement->id;

        Livewire::actingAs($this->admin)
            ->test(AnnouncementsManagement::class)
            ->call('openDeleteModal', $id)
            ->call('deleteAnnouncement')
            ->assertDispatched('toast-show');

        $this->assertDatabaseMissing('announcements', ['id' => $id]);
    }
    /**
     * Halaman tulis dan edit pengumuman berdiri sendiri (bukan modal) dan hanya untuk admin
     */
    public function test_create_and_edit_pages_are_standalone_and_admin_only(): void
    {
        $announcement = Announcement::first();

        $this->get(route('admin.announcements.create'))->assertRedirect(route('login'));
        $this->actingAs($this->regularUser)->get(route('admin.announcements.create'))->assertForbidden();
        $this->actingAs($this->regularUser)->get(route('admin.announcements.edit', $announcement->id))->assertForbidden();

        $this->actingAs($this->admin)->get(route('admin.announcements.create'))
            ->assertOk()->assertSee('Tulis Pengumuman Baru')->assertSee('Terbitkan Pengumuman');

        $this->actingAs($this->admin)->get(route('admin.announcements.edit', $announcement->id))
            ->assertOk()->assertSee('Edit Pengumuman')->assertSee($announcement->title);

        $this->actingAs($this->admin)->get(route('admin.announcements.edit', 'tidak-ada'))->assertNotFound();
    }

    public function test_list_links_to_form_pages_and_validation_blocks_invalid_input(): void
    {
        $announcement = Announcement::first();

        $this->actingAs($this->admin)->get(route('admin.announcements.index'))
            ->assertSee(route('admin.announcements.create'), false)
            ->assertSee(route('admin.announcements.edit', $announcement->id), false);

        Livewire::actingAs($this->admin)->test(AnnouncementForm::class)
            ->set('title', 'abc')
            ->set('content', 'pendek')
            ->call('save')
            ->assertHasErrors(['title', 'content']);
    }
}
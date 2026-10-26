<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Livewire\Admin\AnnouncementForm;
use App\Livewire\Admin\AnnouncementsManagement;
use App\Models\Announcement;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AnnouncementAttachmentsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        Storage::fake('public');

        $this->admin = User::factory()->create();
        $this->admin->assignRole(RoleEnum::Admin);
    }

    private function form(array $files = []): \Livewire\Features\SupportTesting\Testable
    {
        return Livewire::actingAs($this->admin)->test(AnnouncementForm::class)
            ->set('title', 'Juknis Lomba Pionering Terbaru')
            ->set('content', 'Silakan unduh petunjuk teknis terbaru pada lampiran.')
            ->set('broadcastPush', false)
            ->set('newFiles', $files);
    }

    public function test_admin_can_publish_announcement_with_downloadable_files(): void
    {
        $this->form([
            UploadedFile::fake()->create('Juknis Pionering.pdf', 300, 'application/pdf'),
            UploadedFile::fake()->create('Formulir Pendaftaran.docx', 120, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
        ])->call('save')->assertHasNoErrors();

        $announcement = Announcement::firstOrFail();
        $files = $announcement->attachmentFiles();

        $this->assertCount(2, $files);
        $this->assertSame('Juknis Pionering.pdf', $files[0]['name']);
        Storage::disk('public')->assertExists($files[0]['path']);
        Storage::disk('public')->assertExists($files[1]['path']);
    }

    public function test_public_detail_lists_files_and_downloads_them_with_original_name(): void
    {
        $this->form([UploadedFile::fake()->create('Juknis Pionering.pdf', 300, 'application/pdf')])
            ->call('save');

        $announcement = Announcement::firstOrFail();

        $this->get(route('announcements.show', $announcement->slug))
            ->assertOk()
            ->assertSee('Berkas Unduhan (1)')
            ->assertSee('Juknis Pionering.pdf')
            ->assertSee(route('announcements.download', [$announcement->slug, 0]), false);

        $response = $this->get(route('announcements.download', [$announcement->slug, 0]));
        $response->assertOk();
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('Juknis Pionering.pdf', $response->headers->get('Content-Disposition'));

        // Indeks yang tidak ada ditolak
        $this->get(route('announcements.download', [$announcement->slug, 5]))->assertNotFound();
    }

    public function test_files_of_unpublished_announcements_cannot_be_downloaded(): void
    {
        $this->form([UploadedFile::fake()->create('rahasia.pdf', 50, 'application/pdf')])
            ->set('is_published', false)
            ->call('save');

        $announcement = Announcement::firstOrFail();

        $this->get(route('announcements.download', [$announcement->slug, 0]))->assertNotFound();
    }

    public function test_invalid_file_type_and_size_are_rejected(): void
    {
        $this->form([UploadedFile::fake()->create('virus.exe', 10, 'application/x-msdownload')])
            ->call('save')
            ->assertHasErrors(['newFiles.0']);

        $this->form([UploadedFile::fake()->create('besar.pdf', 11000, 'application/pdf')])
            ->call('save')
            ->assertHasErrors(['newFiles.0']);

        $this->assertSame(0, Announcement::count());
    }

    public function test_edit_can_remove_old_files_add_new_ones_and_delete_cleans_storage(): void
    {
        $this->form([
            UploadedFile::fake()->create('lama-1.pdf', 50, 'application/pdf'),
            UploadedFile::fake()->create('lama-2.pdf', 50, 'application/pdf'),
        ])->call('save');

        $announcement = Announcement::firstOrFail();
        [$first, $second] = $announcement->attachmentFiles();

        $component = Livewire::actingAs($this->admin)->test(AnnouncementForm::class, ['id' => $announcement->id])
            ->call('removeAttachment', 0)
            ->set('newFiles', [UploadedFile::fake()->create('baru.xlsx', 40, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')])
            ->call('save')
            ->assertHasNoErrors();

        $names = array_column($announcement->fresh()->attachmentFiles(), 'name');
        $this->assertSame(['lama-2.pdf', 'baru.xlsx'], $names);

        Storage::disk('public')->assertMissing($first['path']);
        Storage::disk('public')->assertExists($second['path']);

        // Menghapus pengumuman ikut menghapus seluruh berkas
        $paths = array_column($announcement->fresh()->attachmentFiles(), 'path');
        Livewire::actingAs($this->admin)->test(AnnouncementsManagement::class)->call('openDeleteModal', $announcement->id)->call('deleteAnnouncement');

        foreach ($paths as $path) {
            Storage::disk('public')->assertMissing($path);
        }
    }

    public function test_maximum_number_of_files_is_enforced(): void
    {
        $files = array_map(fn ($i) => UploadedFile::fake()->create("berkas-{$i}.pdf", 10, 'application/pdf'), range(1, 11));

        $this->form($files)
            ->call('save')
            ->assertHasErrors(['newFiles']);

        $this->assertSame(0, Announcement::count());
    }
}

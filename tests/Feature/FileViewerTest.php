<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Livewire\Portal\ReviewRegistration;
use App\Models\ScoutGroup;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FileViewerTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_page_opens_uploaded_files_in_fullscreen_viewer(): void
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create();
        $user->assignRole(RoleEnum::ScoutGroup);

        ScoutGroup::factory()->create([
            'user_id' => $user->id,
            'mandate_letter_path' => 'documents/surat-tugas.pdf',
            'payment_proof_path' => 'documents/bukti-bayar.jpg',
        ]);

        Livewire::actingAs($user)
            ->test(ReviewRegistration::class)
            ->assertSee('open-file-viewer', false)
            ->assertSee('surat-tugas.pdf', false)
            ->assertSee('bukti-bayar.jpg', false)
            ->assertDontSee('@js', false)
            ->assertSee('<iframe src="'.asset('storage/documents/surat-tugas.pdf').'#view=FitH"', false)
            ->assertSee('<img src="'.asset('storage/documents/bukti-bayar.jpg').'"', false)
            ->assertSee('Buka Tab Baru');
    }
    public function test_admin_detail_previews_pdf_files_in_fullscreen_modal_instead_of_new_tab(): void
    {
        $this->seed(RoleSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole(RoleEnum::Admin);

        $group = ScoutGroup::factory()->create([
            'mandate_letter_path' => 'documents/surat-tugas.pdf',
            'payment_proof_path' => 'documents/bukti-bayar.pdf',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.scout-groups.show', $group->id))
            ->assertOk()
            ->assertSee('open-file-viewer', false)
            ->assertSee('surat-tugas.pdf', false)
            ->assertSee('Pratinjau Berkas')
            ->assertDontSee('Buka Berkas');
    }
}

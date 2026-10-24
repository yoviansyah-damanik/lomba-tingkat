<?php

namespace Tests\Feature;

use App\Models\Chaperone;
use App\Models\Participant;
use App\Models\ScoutGroup;
use Database\Seeders\ContingentDocumentSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\SampleContestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContingentDocumentSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_completes_biodata_and_uploads_for_all_contingents_and_is_idempotent(): void
    {
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);
        $this->seed(SampleContestSeeder::class);

        $this->seed(ContingentDocumentSeeder::class);

        foreach (ScoutGroup::all() as $group) {
            Storage::disk('public')->assertExists($group->mandate_letter_path);
            Storage::disk('public')->assertExists($group->payment_proof_path);
            $this->assertStringEndsWith('.pdf', $group->mandate_letter_path);
        }

        $participant = Participant::whereNotNull('photo_path')->first();
        $this->assertNotNull($participant);
        Storage::disk('public')->assertExists($participant->photo_path);
        foreach (Participant::DOCUMENTS as [$column]) {
            $this->assertNotNull($participant->{$column});
            Storage::disk('public')->assertExists($participant->{$column});
            $this->assertStringEndsWith('.pdf', $participant->{$column});
        }
        $this->assertNotEmpty($participant->formal_education);
        $this->assertNotEmpty($participant->parent_address);
        $this->assertSame(0, Participant::whereNull('photo_path')->count());

        $chaperone = Chaperone::first();
        Storage::disk('public')->assertExists($chaperone->photo_path);
        $this->assertNotEmpty($chaperone->scout_trainings);
        $this->assertNotEmpty($chaperone->other_trainings);
        $this->assertSame(0, Chaperone::whereNull('photo_path')->count());

        // Bukti transfer bergantian PDF dan gambar
        $extensions = ScoutGroup::all()->map(fn ($g) => strtolower(pathinfo($g->payment_proof_path, PATHINFO_EXTENSION)))->unique();
        $this->assertTrue($extensions->contains('pdf') && $extensions->contains('jpg'));

        // Berkas lama non-PDF pada kolom wajib-PDF diganti dan file lamanya dihapus
        $old = Storage::disk('public')->put('participants/lama.jpg', 'x');
        $participant->updateQuietly(['doc_kta_path' => 'participants/lama.jpg']);
        $this->seed(ContingentDocumentSeeder::class);
        $this->assertStringEndsWith('.pdf', $participant->fresh()->doc_kta_path);
        Storage::disk('public')->assertMissing('participants/lama.jpg');

        $before = Storage::disk('public')->allFiles();
        $this->seed(ContingentDocumentSeeder::class);
        $this->assertSame(count($before), count(Storage::disk('public')->allFiles()));
    }
}

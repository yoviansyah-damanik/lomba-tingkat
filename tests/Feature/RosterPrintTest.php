<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Http\Controllers\RosterPrintController;
use App\Models\Participant;
use App\Models\ScoutGroup;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RosterPrintTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole(RoleEnum::Admin);

        return $admin;
    }

    public function test_each_document_renders_a_pdf_and_a_preview_for_admin(): void
    {
        $admin = $this->admin();
        $this->seed(DatabaseSeeder::class);

        foreach (['scout-groups', 'participants', 'chaperones'] as $document) {
            $this->actingAs($admin)->get(route('admin.print.preview', $document))->assertOk()->assertSee('PRATINJAU PDF');

            $response = $this->actingAs($admin)->get(route('admin.print.pdf', [$document, 'raw' => 1]));
            $response->assertOk();
            $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
            $this->assertStringStartsWith('%PDF', $response->getContent());
        }

        $this->actingAs($admin)->get(route('admin.print.pdf', ['download' => 1, 'document' => 'participants']))
            ->assertOk()->assertHeader('Content-Disposition');
    }

    public function test_participants_are_grouped_one_scout_group_per_section(): void
    {
        $this->admin();
        $this->app['env'] = 'local';
        $this->seed(DatabaseSeeder::class);

        $html = view('reports.participants-pdf', [
            'title' => 'Daftar Peserta per Gugus Depan',
            'generatedAt' => now(),
            'groups' => (function () {
                $controller = new RosterPrintController;
                $method = new \ReflectionMethod($controller, 'participantsByGroup');

                return $method->invoke($controller, null);
            })(),
        ])->render();

        $groups = (int) substr_count($html, '<div class="doc-title">');
        $withParticipants = Participant::whereHas('patrol')->with('patrol')->get()->pluck('patrol.scout_group_id')->unique()->count();
        $this->assertGreaterThan(0, $groups);
        $this->assertLessThanOrEqual($withParticipants, $groups);
        $this->assertSame($groups - 1, substr_count($html, 'class="page-break"'));
    }

    public function test_single_scout_group_can_be_selected_and_access_is_restricted(): void
    {
        $admin = $this->admin();
        $this->seed(DatabaseSeeder::class);
        $group = ScoutGroup::first();

        $this->actingAs($admin)->get(route('admin.print.pdf', ['scouts', 'gudep' => $group->id]))->assertNotFound();
        $this->actingAs($admin)->get(route('admin.print.pdf', ['participants', 'gudep' => $group->id]))->assertOk();

        $judge = User::factory()->create();
        $judge->assignRole(RoleEnum::Judge);
        $this->actingAs($judge)->get(route('admin.print.pdf', 'participants'))->assertForbidden();

        auth()->logout();
        $this->get(route('admin.print.pdf', 'participants'))->assertRedirect();
    }
}

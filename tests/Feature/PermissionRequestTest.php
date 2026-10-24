<?php

namespace Tests\Feature;

use App\Enums\PatrolGenderEnum;
use App\Enums\RegistrationStatusEnum;
use App\Enums\RoleEnum;
use App\Livewire\Admin\PermissionRequests;
use App\Models\Chaperone;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\PermissionRequest;
use App\Models\ScoutGroup;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PermissionRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->admin = User::factory()->create(['is_active' => true]);
        $this->admin->assignRole(RoleEnum::Admin);
    }

    /**
     * @return array{participants: array<int, Participant>, chaperone: Chaperone, group: ScoutGroup}
     */
    private function contingent(int $participants = 2): array
    {
        $group = ScoutGroup::factory()->create(['verification_status' => RegistrationStatusEnum::Verified]);
        $patrol = Patrol::factory()->create(['scout_group_id' => $group->id, 'gender_category' => PatrolGenderEnum::Boys]);

        return [
            'group' => $group,
            'participants' => Participant::factory()->count($participants)->create(['patrol_id' => $patrol->id])->all(),
            'chaperone' => Chaperone::factory()->create(['scout_group_id' => $group->id, 'gender' => 'male']),
        ];
    }

    private function page()
    {
        return Livewire::actingAs($this->admin)->test(PermissionRequests::class);
    }

    public function test_only_administrator_can_open_the_page(): void
    {
        $committee = User::factory()->create(['is_active' => true]);
        $committee->assignRole(RoleEnum::Committee);

        $this->actingAs($committee)->get(route('admin.permission-requests.index'))->assertForbidden();
        $this->actingAs($this->admin)->get(route('admin.permission-requests.index'))
            ->assertOk()
            ->assertSee('Permohonan Izin')
            ->assertSee('Izin meninggalkan perkemahan (Permanen)')
            ->assertSee('Izin meninggalkan perkemahan (Sementara)')
            ->assertSee('Kunjungan Tamu');
    }

    public function test_leave_requires_chaperone_and_participant_from_the_same_group(): void
    {
        $a = $this->contingent();
        $b = $this->contingent(1);

        $component = $this->page()
            ->set('type', 'leave_permanent')
            ->set('reason', 'Peserta dijemput orang tua');

        // Tanpa QR sama sekali ditolak
        $component->call('submit')->assertHasErrors('members');

        // Hanya peserta (tanpa bindamping) ditolak
        $component->call('handleScan', $a['participants'][0]->qr_token)->call('submit')->assertHasErrors('members');

        // Gudep berbeda ditolak
        $component->call('handleScan', route('digital-cards.verify', $b['participants'][0]->qr_token))
            ->assertSet('feedback.type', 'error');
        $this->assertCount(1, $component->get('members'));

        // Bindamping + peserta lain gudep sama diterima
        $component->call('handleScan', $a['chaperone']->qr_token)
            ->call('handleScan', $a['participants'][1]->qr_token)
            ->call('submit')
            ->assertHasNoErrors();

        $request = PermissionRequest::first();
        $this->assertSame('completed', $request->status);
        $this->assertSame($a['group']->id, $request->scout_group_id);
        $this->assertCount(3, $request->members);

        // Peserta yang sudah izin permanen tidak dapat diberi izin lagi
        $this->page()->set('type', 'leave_temporary')
            ->call('handleScan', $a['participants'][0]->qr_token)
            ->assertSet('feedback.type', 'error');
    }

    public function test_temporary_leave_must_be_confirmed_back_by_scanning_participants_only(): void
    {
        $c = $this->contingent();

        $this->page()
            ->set('type', 'leave_temporary')
            ->set('reason', 'Keperluan keluarga sementara')
            ->call('handleScan', $c['chaperone']->qr_token)
            ->call('handleScan', $c['participants'][0]->qr_token)
            ->call('handleScan', $c['participants'][1]->qr_token)
            ->call('submit')
            ->assertHasNoErrors();

        $request = PermissionRequest::first();
        $this->assertSame('active', $request->status);

        // Peserta yang sedang izin sementara tidak dapat diberi izin ganda
        $this->page()->set('type', 'leave_temporary')
            ->call('handleScan', $c['participants'][0]->qr_token)
            ->assertSet('feedback.type', 'error');

        $return = $this->page()->call('setTab', 'return');

        // Bindamping tidak perlu konfirmasi kembali
        $return->call('handleScan', $c['chaperone']->qr_token)->assertSet('feedback.type', 'error');
        $this->assertSame('active', $request->fresh()->status);

        // Peserta pertama kembali: izin masih aktif
        $return->call('handleScan', $c['participants'][0]->qr_token)->assertSet('feedback.type', 'success');
        $this->assertSame('active', $request->fresh()->status);

        // Konfirmasi ulang peserta yang sama ditolak
        $return->call('handleScan', $c['participants'][0]->qr_token)->assertSet('feedback.type', 'error');

        // Peserta terakhir kembali: izin selesai
        $return->call('handleScan', $c['participants'][1]->qr_token)->assertSet('feedback.type', 'success');
        $this->assertSame('completed', $request->fresh()->status);
        $this->assertNotNull($request->fresh()->completed_at);
    }

    public function test_guest_visit_needs_only_type_and_reason(): void
    {
        $this->page()
            ->set('type', 'guest_visit')
            ->call('submit')
            ->assertHasErrors('reason');

        $this->page()
            ->set('type', 'guest_visit')
            ->set('reason', 'Kunjungan orang tua peserta')
            ->set('guest_name', 'Bapak Ahmad')
            ->call('submit')
            ->assertHasNoErrors();

        $request = PermissionRequest::first();
        $this->assertSame('guest_visit', $request->type->value);
        $this->assertSame('Bapak Ahmad', $request->guest_name);
        $this->assertSame('completed', $request->status);
        $this->assertNull($request->created_by);
    }

    public function test_letter_is_printable_with_qr_signatures_and_verifiable_publicly(): void
    {
        $c = $this->contingent();

        $this->page()
            ->set('type', 'leave_permanent')
            ->set('reason', 'Peserta sakit dan dijemput orang tua')
            ->call('handleScan', $c['chaperone']->qr_token)
            ->call('handleScan', $c['participants'][0]->qr_token)
            ->call('submit')
            ->assertSet('lastLetterUrl', fn ($url) => str_contains((string) $url, '/letter'))
            ->assertDispatched('permission-letter-ready');

        $request = PermissionRequest::first();

        // Pratinjau (bawaan) dan PDF mentah
        $this->actingAs($this->admin)->get(route('admin.permission-requests.letter', $request->id))
            ->assertOk()->assertSee('PRATINJAU PDF');

        $pdf = $this->actingAs($this->admin)->get(route('admin.permission-requests.letter', $request->id).'?raw=1');
        $pdf->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $pdf->getContent());

        // Isi surat (render view): kop, nomor, penanggung jawab, dan dua QR tanda tangan
        $html = view('permissions.letter-pdf', [
            'permission' => $request->load(['scoutGroup', 'members.participant.patrol', 'members.chaperone']),
            'event' => null,
            'participants' => $request->members->pluck('participant')->filter()->values(),
            'chaperones' => $request->members->pluck('chaperone')->filter()->values(),
            'responsible' => $c['chaperone'],
            'responsibleQr' => 'data:image/svg+xml;base64,AAA',
            'chairQr' => 'data:image/svg+xml;base64,BBB',
            'chair' => ['name' => 'Ketua Uji', 'nta' => '123'],
            'issuedAt' => $request->created_at,
        ])->render();
        $this->assertStringContainsString($request->letterNumber(), $html);
        $this->assertStringContainsString($c['chaperone']->full_name, $html);
        $this->assertStringContainsString((string) $request->members->pluck('participant')->filter()->first()->scout_id, $html);
        $this->assertStringContainsString('Ketua Uji', $html);
        $this->assertStringContainsString('base64,AAA', $html);
        $this->assertStringContainsString('base64,BBB', $html);

        // Verifikasi publik tanpa login
        auth()->logout();
        $this->get(route('permission-letters.verify', $request->id))->assertOk()->assertSee('Surat Izin Sah')->assertSee($request->letterNumber());
        $this->get(route('permission-letters.verify', '00000000-0000-0000-0000-000000000000'))->assertOk()->assertSee('Surat Tidak Ditemukan');
    }

    public function test_letter_is_admin_only(): void
    {
        $committee = User::factory()->create(['is_active' => true]);
        $committee->assignRole(RoleEnum::Committee);
        $request = PermissionRequest::create(['type' => 'guest_visit', 'reason' => 'Kunjungan uji', 'status' => 'completed']);

        $this->actingAs($committee)->get(route('admin.permission-requests.letter', $request->id))->assertForbidden();
    }

    public function test_sample_seeder_creates_leave_and_guest_requests(): void
    {
        $this->contingent(3);
        $this->contingent(3);
        $this->contingent(3);

        $this->seed(\Database\Seeders\PermissionRequestSeeder::class);

        $this->assertSame(5, PermissionRequest::count());
        $this->assertSame(2, PermissionRequest::where('type', 'guest_visit')->count());
        $this->assertSame(1, PermissionRequest::where('type', 'leave_permanent')->count());
        $this->assertSame(1, PermissionRequest::where('type', 'leave_temporary')->where('status', 'active')->count());
        $this->assertSame(1, PermissionRequest::where('type', 'leave_temporary')->where('status', 'completed')->count());

        // Aman dijalankan ulang
        $this->seed(\Database\Seeders\PermissionRequestSeeder::class);
        $this->assertSame(5, PermissionRequest::count());
    }

    public function test_letter_number_is_generated_and_stored_sequentially_per_year(): void
    {
        $first = PermissionRequest::create(['type' => 'guest_visit', 'reason' => 'Kunjungan pertama', 'status' => 'completed']);
        $second = PermissionRequest::create(['type' => 'guest_visit', 'reason' => 'Kunjungan kedua', 'status' => 'completed']);

        $year = now()->format('Y');
        $this->assertSame("001/IZIN/LT3/KWARCAB-PSP/{$year}", $first->fresh()->letter_number);
        $this->assertSame("002/IZIN/LT3/KWARCAB-PSP/{$year}", $second->fresh()->letter_number);
        $this->assertSame($first->letter_number, $first->letterNumber());

        // Nomor tidak berubah saat data diperbarui, dan nomor manual tidak ditimpa
        $first->update(['reason' => 'Diubah']);
        $this->assertSame("001/IZIN/LT3/KWARCAB-PSP/{$year}", $first->fresh()->letter_number);

        // Permohonan dari form juga bernomor otomatis
        $c = $this->contingent();
        $this->page()->set('type', 'leave_permanent')->set('reason', 'Dijemput orang tua')
            ->call('handleScan', $c['chaperone']->qr_token)
            ->call('handleScan', $c['participants'][0]->qr_token)
            ->call('submit');
        $this->assertSame("003/IZIN/LT3/KWARCAB-PSP/{$year}", PermissionRequest::where('type', 'leave_permanent')->first()->letter_number);
    }

    public function test_letter_always_embeds_the_responsible_chaperone_qr_even_for_unverified_group(): void
    {
        $c = $this->contingent();
        $c['group']->update(['verification_status' => RegistrationStatusEnum::Submitted]);

        $request = PermissionRequest::create(['type' => 'leave_permanent', 'reason' => 'Dijemput orang tua', 'scout_group_id' => $c['group']->id, 'status' => 'completed']);
        $request->members()->create(['chaperone_id' => $c['chaperone']->id]);
        $request->members()->create(['participant_id' => $c['participants'][0]->id]);

        $captured = null;
        \Illuminate\Support\Facades\View::composer('permissions.letter-pdf', function ($view) use (&$captured) {
            $captured = $view->getData();
        });

        $this->actingAs($this->admin)->get(route('admin.permission-requests.letter', $request->id).'?raw=1')->assertOk();

        $this->assertNotNull($captured);
        $this->assertStringStartsWith('data:image/svg+xml;base64,', $captured['responsibleQr']);
        $this->assertStringStartsWith('data:image/svg+xml;base64,', $captured['chairQr']);
        $this->assertSame($c['chaperone']->id, $captured['responsible']->id);
        // QR sama persis dengan QR tautan verifikasi kartu bindamping
        $this->assertSame(app(\App\Services\QrCodeService::class)->generateDataUri(route('digital-cards.verify', $c['chaperone']->qr_token), 90), $captured['responsibleQr']);
    }
}
<?php

namespace Tests\Feature\Auth;

use App\Enums\RoleEnum;
use App\Livewire\Admin\RegistrationInvitationsList;
use App\Livewire\Auth\InvitationCodeGate;
use App\Livewire\Auth\ScoutGroupRegistrationForm;
use App\Models\Event;
use App\Models\RegistrationInvitation;
use App\Models\ScoutGroup;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\Concerns\RegistersViaInvitation;
use Tests\TestCase;

class RegistrationInvitationTest extends TestCase
{
    use RefreshDatabase, RegistersViaInvitation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        Event::create([
            'title' => 'LT III Kwarcab Padangsidimpuan',
            'theme' => 'Tangkas, Berkarakter, Bersaudara, dan Berprestasi',
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(14),
            'registration_start_at' => now()->subDays(5),
            'registration_end_at' => now()->addDays(10)->setTime(23, 59, 59),
            'is_registration_open' => true,
            'location' => 'Bumi Perkemahan Kwarcab Padangsidimpuan',
            'is_active' => true,
        ]);
    }

    private function formPayload(array $overrides = []): array
    {
        return $overrides + [
            'address' => 'Jl. Merdeka No. 10',
            'pic_name' => 'Kak Marwan Daulay, S.Pd',
            'phone_number' => '081299887766',
        ];
    }

    public function test_fortify_default_registration_route_is_closed(): void
    {
        $this->get('/register')->assertNotFound();

        $this->post('/register', [
            'name' => 'Penyusup',
            'username' => 'penyusup',
            'password' => 'password-kuat-123',
            'password_confirmation' => 'password-kuat-123',
        ])->assertNotFound();

        $this->assertDatabaseMissing('users', ['username' => 'penyusup']);
    }

    public function test_form_page_redirects_to_code_gate_without_verified_code(): void
    {
        Livewire::test(ScoutGroupRegistrationForm::class)
            ->assertRedirect(route('register.scout-group'));
    }

    public function test_valid_code_opens_form_with_locked_identity(): void
    {
        $invitation = $this->makeInvitation([], verified: false);

        Livewire::test(InvitationCodeGate::class)
            ->set('code', strtolower($invitation->formattedCode()))
            ->call('verifyCode')
            ->assertHasNoErrors()
            ->assertRedirect(route('register.scout-group.form'));

        $this->get(route('register.scout-group.form'))
            ->assertOk()
            ->assertSee('SMP Negeri 1 Padangsidimpuan')
            ->assertSee('01-093')
            ->assertSee($invitation->formattedCode());
    }

    public function test_invalid_switched_off_or_used_codes_are_rejected(): void
    {
        $off = $this->makeInvitation(['group_number' => '03-001', 'is_active' => false], verified: false);
        $used = $this->makeInvitation(['group_number' => '05-001', 'used_at' => now()], verified: false);

        foreach (['SALAH123', $off->code, $used->code] as $code) {
            Livewire::test(InvitationCodeGate::class)
                ->set('code', $code)
                ->call('verifyCode')
                ->assertHasErrors(['code'])
                ->assertNoRedirect();
        }

        $this->assertNull(session(InvitationCodeGate::SESSION_KEY));
    }

    public function test_code_attempts_are_rate_limited_per_minute(): void
    {
        RateLimiter::clear('register-code:minute:127.0.0.1');

        $component = Livewire::test(InvitationCodeGate::class);

        foreach (range(1, 5) as $i) {
            $component->set('code', "SALAH00{$i}")->call('verifyCode')->assertHasErrors(['code']);
        }

        $valid = $this->makeInvitation(['group_number' => '07-001'], verified: false);

        $component->set('code', $valid->code)->call('verifyCode')
            ->assertHasErrors(['code'])
            ->assertNoRedirect();

        $this->assertNull(session(InvitationCodeGate::SESSION_KEY));
    }

    public function test_registration_takes_identity_from_invitation_and_marks_it_used(): void
    {
        $invitation = $this->makeInvitation([
            'group_number' => '01-093 & 01-094',
            'school_name' => 'SMP Negeri 2 Padangsidimpuan',
            'kwarran_name' => 'Kwarran Padangsidimpuan Selatan',
        ]);

        Livewire::test(ScoutGroupRegistrationForm::class)
            ->set('address', 'Jl. Merdeka No. 10')
            ->set('pic_name', 'Kak Marwan Daulay, S.Pd')
            ->set('phone_number', '081299887766')
            ->set('terms', true)
            ->call('register')
            ->assertHasNoErrors()
            ->assertRedirect(route('register.scout-group.success'));

        $group = ScoutGroup::where('school_name', 'SMP Negeri 2 Padangsidimpuan')->firstOrFail();
        $this->assertSame('01-093', $group->male_group_number);
        $this->assertSame('Padangsidimpuan Selatan', $group->kwarran_name);
        $this->assertSame($invitation->id, $group->invitation_id);
        $this->assertNotNull($group->registered_ip);

        $invitation->refresh();
        $this->assertNotNull($invitation->used_at);
        $this->assertSame($group->id, $invitation->scout_group_id);
        $this->assertNull(session(InvitationCodeGate::SESSION_KEY));
        $this->assertAuthenticatedAs($group->user);
    }

    public function test_used_invitation_cannot_register_twice(): void
    {
        $this->registerGudep($this->formPayload(['school_name' => 'SMP A', 'group_number' => '01-093', 'terms' => '1']))
            ->assertHasNoErrors();

        // Sesi lama masih menyimpan kode yang sama, tetapi undangan sudah terpakai
        $invitation = RegistrationInvitation::firstOrFail();
        session([InvitationCodeGate::SESSION_KEY => ['id' => $invitation->id, 'at' => time()]]);
        auth()->logout();

        Livewire::test(ScoutGroupRegistrationForm::class)
            ->assertRedirect(route('register.scout-group'));

        $this->assertSame(1, ScoutGroup::count());
    }

    public function test_switching_invitation_off_blocks_registration_even_after_code_was_verified(): void
    {
        $invitation = $this->makeInvitation();
        $component = Livewire::test(ScoutGroupRegistrationForm::class);

        $invitation->update(['is_active' => false]);

        $component
            ->set('address', 'Jl. Merdeka')
            ->set('pic_name', 'Kak Marwan')
            ->set('phone_number', '081299887766')
            ->set('terms', true)
            ->call('register')
            ->assertRedirect(route('register.scout-group'));

        $this->assertSame(0, ScoutGroup::count());
        $this->assertGuest();
    }

    public function test_form_session_expires_after_thirty_minutes(): void
    {
        $invitation = $this->makeInvitation();
        session([InvitationCodeGate::SESSION_KEY => ['id' => $invitation->id, 'at' => time() - 1801]]);

        Livewire::test(ScoutGroupRegistrationForm::class)
            ->assertRedirect(route('register.scout-group'));
    }

    public function test_phone_number_must_be_indonesian_format(): void
    {
        $this->makeInvitation();

        Livewire::test(ScoutGroupRegistrationForm::class)
            ->set('address', 'Jl. Merdeka')
            ->set('pic_name', 'Kak Marwan')
            ->set('phone_number', '12345')
            ->set('terms', true)
            ->call('register')
            ->assertHasErrors(['phone_number' => 'regex']);
    }

    public function test_admin_can_create_toggle_and_delete_invitations(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(RoleEnum::Admin);

        $this->actingAs($admin)->get(route('admin.registration-invitations.index'))->assertOk();

        $list = Livewire::actingAs($admin)->test(RegistrationInvitationsList::class)
            ->call('openForm')
            ->set('group_number', '01-093')
            ->set('school_name', 'SMP Negeri 1 Padangsidimpuan')
            ->set('kwarran_name', 'Kwarran Padangsidimpuan Utara')
            ->call('save')
            ->assertHasNoErrors();

        $invitation = RegistrationInvitation::firstOrFail();
        $this->assertSame('01-093 & 01-094', $invitation->group_number);
        $this->assertSame(8, strlen($invitation->code));
        $this->assertTrue($invitation->is_active);

        // Nomor gudep yang sama tidak boleh dibuatkan kode dua kali
        $list->call('openForm')
            ->set('group_number', '01-093')
            ->set('school_name', 'SMP Lain')
            ->set('kwarran_name', 'Kwarran Padangsidimpuan Utara')
            ->call('save')
            ->assertHasErrors(['group_number']);

        $list->call('toggleActive', $invitation->id);
        $this->assertFalse($invitation->fresh()->is_active);

        $list->call('delete', $invitation->id);
        $this->assertSame(0, RegistrationInvitation::count());
    }

    public function test_admin_can_bulk_import_invitations(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(RoleEnum::Admin);

        Livewire::actingAs($admin)->test(RegistrationInvitationsList::class)
            ->call('openForm')
            ->set('bulkMode', true)
            ->set('bulkText', "01-093; SMP Negeri 1 Padangsidimpuan; Utara\n01-095; SMP Negeri 2 Padangsidimpuan; Angkola Julu\nsalah format")
            ->call('save')
            ->assertHasErrors(['bulkText']);

        $this->assertSame(2, RegistrationInvitation::count());
    }

    public function test_non_admin_cannot_open_invitation_management(): void
    {
        $user = User::factory()->create();
        $user->assignRole(RoleEnum::ScoutGroup);

        $this->actingAs($user)->get(route('admin.registration-invitations.index'))->assertForbidden();
    }
}

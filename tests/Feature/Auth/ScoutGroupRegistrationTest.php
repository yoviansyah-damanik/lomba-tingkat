<?php

namespace Tests\Feature\Auth;

use App\Enums\RegistrationStatusEnum;
use App\Enums\RoleEnum;
use App\Models\Event;
use App\Models\ScoutGroup;
use App\Jobs\SendScoutGroupRegisteredPush;
use App\Models\User;
use App\Services\WebPushService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ScoutGroupRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        // Buat data event resmi LT III dengan batas waktu registrasi masih aktif
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

    public function test_scout_group_registration_screen_can_be_rendered(): void
    {
        $response = $this->get(route('register.scout-group'));

        $response->assertOk();
        $response->assertSee('Registrasi Akun Pangkalan Gugus Depan');
        $response->assertSee('Nomor Gudep Putra');
        $response->assertSee('Nomor Gudep Putri (Otomatis)');
        $response->assertSee('Kwartir Ranting (Kwarran)');
        $response->assertSee('Kwarran Padangsidimpuan Utara');
        $response->assertSee('Batas Waktu Registrasi Pangkalan');
        $response->assertSee('Verifikasi Berkas oleh Administrator');
        $response->assertSee('tidak memerlukan email');
    }

    public function test_scout_group_can_register_and_create_contingent_account_with_auto_generated_credentials(): void
    {
        $response = $this->post(route('register.scout-group.store'), [
            'school_name' => 'SMP Negeri 2 Padangsidimpuan',
            'group_number' => '01-093', // User hanya input nomor depan ganjil
            'kwarran' => 'Kwarran Padangsidimpuan Utara',
            'address' => 'Jl. Merdeka No. 10',
            'pic_name' => 'Kak Marwan Daulay, S.Pd',
            'phone_number' => '081299887766',
            'terms' => '1',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('register.scout-group.success'));
        $response->assertSessionHas('newly_registered_credentials');

        // Pastikan entitas ScoutGroup terbuat dengan format ID resmi G-{no_gudep}
        $scoutGroup = ScoutGroup::where('school_name', 'SMP Negeri 2 Padangsidimpuan')->first();
        $this->assertNotNull($scoutGroup);
        $this->assertEquals('SMP Negeri 2 Padangsidimpuan', $scoutGroup->school_name);
        $this->assertEquals('01-093 & 01-094', $scoutGroup->group_number);
        $this->assertEquals('01-093', $scoutGroup->male_group_number);
        $this->assertEquals('01-094', $scoutGroup->female_group_number);
        $this->assertStringContainsString('Kwarran Padangsidimpuan Utara', $scoutGroup->address);
        $this->assertEquals(RegistrationStatusEnum::Draft, $scoutGroup->verification_status);
        $this->assertEquals('G-01093.01094', $scoutGroup->registration_code);

        // Pastikan user terbuat dengan username dan ID yang sinkron dengan ID Gudep
        $user = $scoutGroup->user;
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole(RoleEnum::ScoutGroup));
        $this->assertEquals('Kak Marwan Daulay, S.Pd', $user->name);
        $this->assertEquals('081299887766', $user->phone_number);
        $this->assertEquals('G-01093.01094', $user->username);
        $this->assertEquals('G-01093.01094', $user->id_number);

        // Pastikan user langsung login
        $this->assertAuthenticatedAs($user);

        // Akses halaman sukses menampilkan kredensial dan opsi ubah kata sandi
        $successResponse = $this->get(route('register.scout-group.success'));
        $successResponse->assertOk();
        $successResponse->assertSee('Pendaftaran Pangkalan Berhasil');
        $successResponse->assertSee('G-01093.01094');
        $successResponse->assertSee('Ubah Kata Sandi Sekarang');
    }

    public function test_registration_fails_when_registration_deadline_has_passed(): void
    {
        // Ubah batas akhir registrasi menjadi sudah lewat kemarin
        Event::where('is_active', true)->update([
            'registration_end_at' => now()->subDay(),
        ]);

        $response = $this->post(route('register.scout-group.store'), [
            'school_name' => 'SMP Negeri 4 Padangsidimpuan',
            'group_number' => '04.001 - 04.002',
            'kwarran' => 'Kwarran Padangsidimpuan Selatan',
            'address' => 'Jl. Imam Bonjol No. 12',
            'pic_name' => 'Kak Syahrul Siregar',
            'phone_number' => '081211223344',
            'terms' => '1',
        ]);

        $response->assertSessionHasErrors(['registration_closed']);
        $this->assertGuest();
        $this->assertDatabaseMissing('scout_groups', ['school_name' => 'SMP Negeri 4 Padangsidimpuan']);
    }

    public function test_registration_fails_when_admin_closes_registration(): void
    {
        // Nonaktifkan registrasi manual oleh admin
        Event::where('is_active', true)->update([
            'is_registration_open' => false,
        ]);

        $response = $this->post(route('register.scout-group.store'), [
            'school_name' => 'SMP Negeri 5 Padangsidimpuan',
            'group_number' => '05.001 - 05.002',
            'kwarran' => 'Kwarran Padangsidimpuan Batunadua',
            'address' => 'Jl. Raja Inal Siregar',
            'pic_name' => 'Kak Budianto',
            'phone_number' => '081255667788',
            'terms' => '1',
        ]);

        $response->assertSessionHasErrors(['registration_closed']);
        $this->assertGuest();
    }

    public function test_registration_username_is_exactly_the_gudep_id(): void
    {
        $response = $this->post(route('register.scout-group.store'), [
            'school_name' => 'SMP Negeri 3 Padangsidimpuan',
            'group_number' => '03.001 - 03.002',
            'kwarran' => 'Kwarran Padangsidimpuan Selatan',
            'address' => 'Jl. Imam Bonjol',
            'pic_name' => 'Kak Syahrul Siregar',
            'phone_number' => '081211223344',
            'terms' => '1',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('register.scout-group.success'));

        $createdUser = User::where('name', 'Kak Syahrul Siregar')->first();
        $this->assertNotNull($createdUser);
        $this->assertSame('G-03001.03002', $createdUser->username);
        $this->assertSame($createdUser->id_number, $createdUser->username);
    }

    public function test_registration_requires_terms_acceptance(): void
    {
        $response = $this->post(route('register.scout-group.store'), [
            'school_name' => 'MTs Negeri 1 Padangsidimpuan',
            'group_number' => '05.001 - 05.002',
            'kwarran' => 'Kwarran Padangsidimpuan Batunadua',
            'address' => 'Jl. Raja Inal Siregar',
            'pic_name' => 'Kak Fauzi Lubis',
            'phone_number' => '081244556677',
            // terms tidak disertakan
        ]);

        $response->assertSessionHasErrors(['terms']);
        $this->assertGuest();
    }

    public function test_odd_gudep_number_attaches_to_boys_patrol_and_even_to_girls_patrol(): void
    {
        // 1. Registrasi Gudep dengan nomor depan ganjil: 01-093
        $this->post(route('register.scout-group.store'), [
            'school_name' => 'SMP Negeri 1 Padangsidimpuan',
            'group_number' => '01-093',
            'kwarran' => 'Kwarran Padangsidimpuan Utara',
            'address' => 'Jl. Sudirman No. 1',
            'pic_name' => 'Kak Syahrul',
            'phone_number' => '081234567890',
            'terms' => '1',
        ]);

        $scoutGroup = ScoutGroup::where('school_name', 'SMP Negeri 1 Padangsidimpuan')->first();
        $this->assertEquals('01-093', $scoutGroup->male_group_number);
        $this->assertEquals('01-094', $scoutGroup->female_group_number);

        // 2. Buat Regu Putra: Rajawali
        $boysPatrol = $scoutGroup->patrols()->create([
            'patrol_name' => 'Rajawali',
            'gender_category' => \App\Enums\PatrolGenderEnum::Boys,
        ]);

        // 3. Buat Regu Putri: Melati
        $girlsPatrol = $scoutGroup->patrols()->create([
            'patrol_name' => 'Melati',
            'gender_category' => \App\Enums\PatrolGenderEnum::Girls,
        ]);

        // Pastikan nomor ganjil melekat pada regu putra, dan nomor genap pada regu putri
        $this->assertEquals('01-093', $boysPatrol->group_number);
        $this->assertEquals('01-094', $girlsPatrol->group_number);
    }

    public function test_contingent_can_have_distinct_male_and_female_chaperones_only(): void
    {
        $user = User::factory()->create();
        $user->assignRole(RoleEnum::ScoutGroup);

        $scoutGroup = ScoutGroup::create([
            'user_id' => $user->id,
            'registration_code' => 'GDP-PSP-99',
            'school_name' => 'SMP Swasta Al-Azhar',
            'group_number' => '01-095 & 01-096',
            'male_group_number' => '01-095',
            'female_group_number' => '01-096',
            'address' => 'Padangsidimpuan',
            'contact_person' => 'Kak Pembina',
            'contact_phone' => '081234567899',
        ]);

        $this->actingAs($user);

        $fakePhoto = \Illuminate\Http\UploadedFile::fake()->image('photo.jpg');

        $baseChaperonePayload = [
            'birth_place' => 'Padangsidimpuan',
            'birth_date' => '1990-01-01',
            'religion' => 'Islam',
            'blood_type' => 'O',
            'scout_id' => '02.17.001.001',
            'home_address' => 'Jl. Merdeka No. 1',
            'city' => 'Padangsidimpuan',
            'postal_code' => '22711',
            'photo' => $fakePhoto,
        ];

        // 1. Tambah Bindamping Putra berhasil
        $resMale = $this->post(route('scout-group.chaperones.store'), array_merge($baseChaperonePayload, [
            'full_name' => 'Kak Rahmat Hidayat, S.Pd',
            'gender' => 'male',
            'phone_number' => '081377889901',
        ]));
        $resMale->assertSessionHasNoErrors();
        $this->assertEquals(1, $scoutGroup->chaperones()->where('gender', 'male')->count());
        $this->assertEquals('Kak Rahmat Hidayat, S.Pd', $scoutGroup->fresh()->maleChaperone?->full_name);

        // 2. Tambah Bindamping Putra kedua ditolak (karena sudah ada)
        $resMale2 = $this->post(route('scout-group.chaperones.store'), array_merge($baseChaperonePayload, [
            'full_name' => 'Kak Syarifuddin, S.Pd',
            'gender' => 'male',
            'phone_number' => '081377889902',
        ]));
        $resMale2->assertSessionHasErrors(['gender']);

        // 3. Tambah Bindamping Putri berhasil
        $resFemale = $this->post(route('scout-group.chaperones.store'), array_merge($baseChaperonePayload, [
            'full_name' => 'Kak Sri Wahyuni, S.Pd',
            'gender' => 'female',
            'phone_number' => '081377889903',
        ]));
        $resFemale->assertSessionHasNoErrors();
        $this->assertEquals(1, $scoutGroup->chaperones()->where('gender', 'female')->count());
        $this->assertEquals('Kak Sri Wahyuni, S.Pd', $scoutGroup->fresh()->femaleChaperone?->full_name);
    }

    public function test_registration_automatically_formats_group_number_when_input_without_symbols(): void
    {
        // Pengguna menginput murni 5 digit tanpa simbol: 01093
        $response = $this->post(route('register.scout-group.store'), [
            'school_name' => 'SMP Negeri 7 Padangsidimpuan',
            'group_number' => '01093',
            'kwarran' => 'Kwarran Padangsidimpuan Tenggara',
            'address' => 'Jl. BM Muda',
            'pic_name' => 'Kak Ridwan',
            'phone_number' => '081233445566',
            'terms' => '1',
        ]);

        $response->assertSessionHasNoErrors();

        $scoutGroup = ScoutGroup::where('school_name', 'SMP Negeri 7 Padangsidimpuan')->first();
        $this->assertNotNull($scoutGroup);
        $this->assertEquals('01-093', $scoutGroup->male_group_number);
        $this->assertEquals('01-094', $scoutGroup->female_group_number);
        $this->assertEquals('01-093 & 01-094', $scoutGroup->group_number);
    }

    public function test_registration_does_not_require_email_verification_and_admin_verifies_contingent(): void
    {
        // 1. Registrasi pangkalan tanpa verifikasi email
        $this->post(route('register.scout-group.store'), [
            'school_name' => 'SMP Negeri 8 Padangsidimpuan',
            'group_number' => '01-081',
            'kwarran' => 'Kwarran Padangsidimpuan Hutaimbaru',
            'address' => 'Jl. Sabungan No. 3',
            'pic_name' => 'Kak Zulham Efendi',
            'phone_number' => '081299112233',
            'terms' => '1',
        ]);

        $scoutGroup = ScoutGroup::where('school_name', 'SMP Negeri 8 Padangsidimpuan')->first();
        $user = $scoutGroup->user;

        // User dapat langsung mengakses dashboard tanpa hambatan email verification
        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();

        // 2. Kontingen diisi dan diajukan (Submit)
        $patrol = $scoutGroup->patrols()->create([
            'patrol_name' => 'Harimau',
            'gender_category' => \App\Enums\PatrolGenderEnum::Boys,
        ]);
        $scoutGroup->chaperones()->create([
            'full_name' => 'Kak Zulham Efendi',
            'gender' => 'male',
            'phone_number' => '081299112233',
        ]);

        $this->actingAs($user)->post(route('scout-group.submit'))->assertRedirect(route('scout-group.index'));
        $this->assertEquals(RegistrationStatusEnum::Submitted, $scoutGroup->fresh()->verification_status);

        // 3. Panitia biasa ditolak memverifikasi (Strictly Administrator)
        $committee = User::factory()->create();
        $committee->assignRole(RoleEnum::Committee);
        $this->actingAs($committee)
            ->post(route('admin.scout-groups.verify', $scoutGroup->id))
            ->assertForbidden();

        // 4. Administrator memverifikasi dan menyetujui pendaftaran kontingen
        $admin = User::factory()->create();
        $admin->assignRole(RoleEnum::Admin);
        $this->actingAs($admin)
            ->post(route('admin.scout-groups.verify', $scoutGroup->id))
            ->assertRedirect(route('admin.scout-groups.show', $scoutGroup->id));

        $this->assertEquals(RegistrationStatusEnum::Verified, $scoutGroup->fresh()->verification_status);
        $this->assertEquals($admin->id, $scoutGroup->fresh()->verified_by);
    }

    public function test_registration_queues_push_notification_for_admins(): void
    {
        Queue::fake();

        $this->post(route('register.scout-group.store'), [
            'school_name' => 'SMP Negeri 5 Padangsidimpuan',
            'group_number' => '01-101',
            'kwarran' => 'Kwarran Padangsidimpuan Utara',
            'address' => 'Jl. Sudirman No. 5',
            'pic_name' => 'Kak Rahmat',
            'phone_number' => '081211112222',
            'terms' => '1',
        ])->assertSessionHasNoErrors();

        $group = ScoutGroup::where('school_name', 'SMP Negeri 5 Padangsidimpuan')->firstOrFail();

        Queue::assertPushed(SendScoutGroupRegisteredPush::class, fn ($job) => $job->scoutGroupId === $group->id);
    }

    public function test_registered_push_job_targets_admin_role_only(): void
    {
        $user = User::factory()->create();
        $group = ScoutGroup::create([
            'user_id' => $user->id,
            'registration_code' => ScoutGroup::generateRegistrationCode(),
            'school_name' => 'SMP Uji Push',
            'group_number' => '01-201',
            'kwarran_name' => 'Kwarran Padangsidimpuan Utara',
            'address' => 'Jl. Uji',
            'contact_person' => 'Kak Uji',
            'contact_phone' => '081200000000',
            'verification_status' => RegistrationStatusEnum::Draft,
        ]);

        $this->mock(WebPushService::class, function ($mock) {
            $mock->shouldReceive('broadcastNotification')
                ->once()
                ->withArgs(fn ($payload, $role) => $role === RoleEnum::Admin->value
                    && $payload['title'] === 'Pendaftaran Gugus Depan Baru')
                ->andReturn(['total' => 1, 'sent' => 1, 'failed' => 0, 'expired' => 0, 'target_role' => 'admin']);
        });

        (new SendScoutGroupRegisteredPush($group->id))->handle(app(WebPushService::class));
    }
}

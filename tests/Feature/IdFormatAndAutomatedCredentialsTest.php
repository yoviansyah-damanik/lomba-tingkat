<?php

namespace Tests\Feature;

use App\Enums\PatrolRoleEnum;
use App\Enums\RegistrationStatusEnum;
use App\Enums\RoleEnum;
use App\Models\Chaperone;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class IdFormatAndAutomatedCredentialsTest extends TestCase
{
    use RefreshDatabase;

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

    /**
     * Memastikan format ID Gudep adalah G-{no_gudep} dan tidak pernah duplikat
     */
    public function test_gudep_id_format_and_uniqueness(): void
    {
        $id1 = ScoutGroup::generateGudepId('01-123 & 01-124');
        $this->assertEquals('G-01123.01124', $id1);

        $user = User::factory()->create();
        $user->assignRole(RoleEnum::ScoutGroup);

        $group1 = ScoutGroup::create([
            'user_id' => $user->id,
            'registration_code' => $id1,
            'school_name' => 'SMP Negeri 1 Padangsidimpuan',
            'group_number' => '01-123 & 01-124',
            'male_group_number' => '01-123',
            'female_group_number' => '01-124',
            'address' => 'Jl. Sudirman',
            'contact_person' => 'Kak Budi',
            'contact_phone' => '081234567890',
        ]);

        $this->assertEquals('G-01123.01124', $group1->registration_code);

        // Jika pangkalan lain mendaftar dengan nomor gudep yang sama, ID otomatis diberi suffix unik
        $id2 = ScoutGroup::generateGudepId('01-123 & 01-124');
        $this->assertEquals('G-01123.01124-2', $id2);

        $user2 = User::factory()->create();
        $user2->assignRole(RoleEnum::ScoutGroup);

        $group2 = ScoutGroup::create([
            'user_id' => $user2->id,
            'registration_code' => $id2,
            'school_name' => 'SMP Swasta Al-Azhar',
            'group_number' => '01-123 & 01-124',
            'address' => 'Padangsidimpuan',
            'contact_person' => 'Kak Ridwan',
            'contact_phone' => '081234567891',
        ]);

        $this->assertEquals('G-01123.01124-2', $group2->registration_code);
        $this->assertNotEquals($group1->registration_code, $group2->registration_code);
    }

    /**
     * Memastikan format ID Peserta adalah P-{6 digit acak} dan unik
     */
    public function test_participant_id_format_and_uniqueness(): void
    {
        $user = User::factory()->create();
        $user->assignRole(RoleEnum::ScoutGroup);

        $group = ScoutGroup::create([
            'user_id' => $user->id,
            'school_name' => 'SMP Negeri 3 Padangsidimpuan',
            'group_number' => '01-001 & 01-002',
            'address' => 'Jl. Merdeka',
            'contact_person' => 'Kak Syahrul',
            'contact_phone' => '081234567890',
        ]);

        $patrol = Patrol::create([
            'scout_group_id' => $group->id,
            'patrol_name' => 'Rajawali',
            'gender_category' => \App\Enums\PatrolGenderEnum::Boys,
        ]);

        $participants = [];
        for ($i = 0; $i < 10; $i++) {
            $p = Participant::create([
                'patrol_id' => $patrol->id,
                'full_name' => 'Peserta ' . $i,
                'gender' => 'male',
                'birth_date' => '2012-05-10',
                'birth_place' => 'Padangsidimpuan',
                'role_in_patrol' => PatrolRoleEnum::Member,
            ]);

            $this->assertMatchesRegularExpression('/^P-\d{6}$/', $p->id_number);
            $participants[] = $p->id_number;
        }

        // Pastikan seluruh ID yang dibuat tidak ada duplikasi
        $this->assertCount(10, array_unique($participants));
    }

    /**
     * Memastikan format ID Bindamping adalah B-{6 digit acak} dan unik
     */
    public function test_chaperone_id_format_and_uniqueness(): void
    {
        $user = User::factory()->create();
        $user->assignRole(RoleEnum::ScoutGroup);

        $group = ScoutGroup::create([
            'user_id' => $user->id,
            'school_name' => 'SMP Negeri 4 Padangsidimpuan',
            'group_number' => '01-003 & 01-004',
            'address' => 'Padangsidimpuan',
            'contact_person' => 'Kak Syahrul',
            'contact_phone' => '081234567890',
        ]);

        $chaperoneMale = Chaperone::create([
            'scout_group_id' => $group->id,
            'full_name' => 'Kak Rahmat Hidayat',
            'gender' => 'male',
            'phone_number' => '081234567891',
        ]);

        $chaperoneFemale = Chaperone::create([
            'scout_group_id' => $group->id,
            'full_name' => 'Kak Nurhasanah',
            'gender' => 'female',
            'phone_number' => '081234567892',
        ]);

        $this->assertMatchesRegularExpression('/^B-\d{6}$/', $chaperoneMale->id_number);
        $this->assertMatchesRegularExpression('/^B-\d{6}$/', $chaperoneFemale->id_number);
        $this->assertNotEquals($chaperoneMale->id_number, $chaperoneFemale->id_number);
    }

    /**
     * Memastikan format ID Aparatur adalah A-{6 digit acak} dan unik
     */
    public function test_aparatur_id_format_and_uniqueness(): void
    {
        $committee = User::create([
            'name' => 'Panitia Lapangan 1',
            'password' => bcrypt('password123'),
        ]);
        $committee->assignRole(RoleEnum::Committee);

        $judge = User::create([
            'name' => 'Dewan Juri 1',
            'password' => bcrypt('password123'),
        ]);
        $judge->assignRole(RoleEnum::Judge);

        $this->assertMatchesRegularExpression('/^A-\d{6}$/', $committee->id_number);
        $this->assertMatchesRegularExpression('/^A-\d{6}$/', $judge->id_number);
        $this->assertNotEquals($committee->id_number, $judge->id_number);
    }

    /**
     * Menguji alur registrasi Gudep: tanpa memasukkan username/password,
     * diarahkan ke halaman sukses dengan tampilan kredensial, dan opsi ubah password
     */
    public function test_scout_group_registration_flow_without_username_and_password(): void
    {
        $response = $this->post(route('register.scout-group.store'), [
            'school_name' => 'SMP Negeri 10 Padangsidimpuan',
            'group_number' => '01-155',
            'kwarran' => 'Kwarran Padangsidimpuan Selatan',
            'address' => 'Jl. Imam Bonjol',
            'pic_name' => 'Kak Faisal Siregar, S.Pd',
            'phone_number' => '081234567899',
            'terms' => '1',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('register.scout-group.success'));

        $group = ScoutGroup::where('school_name', 'SMP Negeri 10 Padangsidimpuan')->firstOrFail();
        $this->assertEquals('G-01155.01156', $group->registration_code);

        $user = $group->user;
        $this->assertNotNull($user);
        $this->assertEquals('G-01155.01156', $user->username);
        $this->assertEquals('G-01155.01156', $user->id_number);
        $this->assertAuthenticatedAs($user);

        // Kunjungi halaman sukses pendaftaran
        $successRes = $this->get(route('register.scout-group.success'));
        $successRes->assertOk();
        $successRes->assertSee('Selamat Datang, SMP Negeri 10 Padangsidimpuan!');
        $successRes->assertSee('G-01155.01156');
        $successRes->assertSee('Ubah Kata Sandi Sekarang');
        $successRes->assertSee('Lanjut ke Dasbor Kontingen');

        // Pengguna dapat langsung mengakses halaman ubah password keamanan tanpa terblokir
        $securityRes = $this->get(route('security.edit'));
        $securityRes->assertOk();
    }
}

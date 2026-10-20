<?php

namespace Tests\Feature;

use App\Enums\PatrolGenderEnum;
use App\Enums\PatrolRoleEnum;
use App\Enums\RegistrationStatusEnum;
use App\Livewire\Public\ParticipantsPage;
use App\Models\Chaperone;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PublicParticipantsTest extends TestCase
{
    use RefreshDatabase;

    protected ScoutGroup $scoutGroup;
    protected Patrol $boysPatrol;
    protected Patrol $girlsPatrol;
    protected Participant $participantBoy;
    protected Participant $participantGirl;
    protected Chaperone $chaperone;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create();

        $this->scoutGroup = ScoutGroup::create([
            'user_id' => $user->id,
            'registration_code' => 'REG-SMPN1',
            'school_name' => 'SMP Negeri 1 Padangsidimpuan',
            'group_number' => '01.001-01.002',
            'male_group_number' => '01.001',
            'female_group_number' => '01.002',
            'kwarran_name' => 'Padangsidimpuan Utara',
            'address' => 'Jl. Kenanga No. 10',
            'contact_person' => 'Drs. H. Ahmad Fauzi',
            'contact_phone' => '081298765432',
            'verification_status' => RegistrationStatusEnum::Verified,
        ]);

        $this->boysPatrol = Patrol::create([
            'scout_group_id' => $this->scoutGroup->id,
            'patrol_name' => 'Rajawali',
            'gender_category' => PatrolGenderEnum::Boys,
            'status' => RegistrationStatusEnum::Verified,
        ]);

        $this->girlsPatrol = Patrol::create([
            'scout_group_id' => $this->scoutGroup->id,
            'patrol_name' => 'Melati',
            'gender_category' => PatrolGenderEnum::Girls,
            'status' => RegistrationStatusEnum::Verified,
        ]);

        $this->participantBoy = Participant::create([
            'patrol_id' => $this->boysPatrol->id,
            'full_name' => 'Muhammad Budi Pratama',
            'role_in_patrol' => PatrolRoleEnum::PatrolLeader,
            'gender' => 'male',
            'birth_place' => 'Padangsidimpuan',
            'birth_date' => '2012-05-10',
            'class_grade' => 'Kelas 8',
            'scout_penggalang' => 'Penggalang Ramu',
            'status' => RegistrationStatusEnum::Verified,
        ]);

        $this->participantGirl = Participant::create([
            'patrol_id' => $this->girlsPatrol->id,
            'full_name' => 'Siti Aisyah Siregar',
            'role_in_patrol' => PatrolRoleEnum::PatrolLeader,
            'gender' => 'female',
            'birth_place' => 'Padangsidimpuan',
            'birth_date' => '2012-07-15',
            'class_grade' => 'Kelas 8',
            'scout_penggalang' => 'Penggalang Rakit',
            'status' => RegistrationStatusEnum::Verified,
        ]);

        $this->chaperone = Chaperone::create([
            'scout_group_id' => $this->scoutGroup->id,
            'full_name' => 'Hendra Syahputra, S.Pd',
            'gender' => 'male',
            'scout_id' => '02.01.1990.001',
            'phone_number' => '081234567890',
            'status' => RegistrationStatusEnum::Verified,
        ]);
    }

    /**
     * Memastikan halaman publik direktori peserta dapat diakses oleh guest tanpa login
     */
    public function test_guest_can_access_public_participants_page(): void
    {
        $response = $this->get(route('participants'));
        $response->assertOk();
        $response->assertSee('Peserta & Pangkalan Kontingen');
        $response->assertSee('SMP Negeri 1 Padangsidimpuan');
    }

    /**
     * Memastikan direktori menampilkan pangkalan gugus depan yang terdaftar
     */
    public function test_page_displays_registered_scout_groups(): void
    {
        Livewire::test(ParticipantsPage::class)
            ->assertSee('SMP Negeri 1 Padangsidimpuan')
            ->assertSee('01.001')
            ->assertSee('Padangsidimpuan Utara')
            ->assertSee('Rajawali')
            ->assertSee('Melati');
    }

    /**
     * Memastikan ketika gugus depan dipilih, menampilkan nama seluruh peserta dan bindamping
     */
    public function test_selecting_scout_group_displays_all_participants_and_chaperones(): void
    {
        Livewire::test(ParticipantsPage::class)
            ->call('selectScoutGroup', $this->scoutGroup->id)
            ->assertSet('showDetailModal', true)
            ->assertSee('Gugus Depan Kontingen')
            ->assertSee('Muhammad Budi Pratama')
            ->assertSee('Siti Aisyah Siregar')
            ->assertSee('Hendra Syahputra, S.Pd')
            ->assertSee('Pimpinan Regu (Pinru)')
            ->assertSee('Bindamping Putra')
            ->assertSee('NTA:')
            ->assertDontSee('Siswa Penggalang')
            ->assertDontSee('Siswi Penggalang')
            ->assertDontSee('Kontak:')
            ->assertDontSee('081234567890');
    }

    /**
     * Memastikan hanya gugus depan terverifikasi yang ditampilkan dan tanpa melabeli telah diverifikasi
     */
    public function test_only_verified_scout_groups_displayed_without_verification_label(): void
    {
        // Buat gugus depan draft / belum diverifikasi
        $unverifiedUser = User::factory()->create();
        $unverifiedGroup = ScoutGroup::create([
            'user_id' => $unverifiedUser->id,
            'registration_code' => 'REG-DRAFT',
            'school_name' => 'SMP Swasta Belum Verifikasi',
            'group_number' => '05.001-05.002',
            'male_group_number' => '05.001',
            'female_group_number' => '05.002',
            'kwarran_name' => 'Padangsidimpuan Selatan',
            'address' => 'Jl. Merdeka No. 99',
            'contact_person' => 'Pembina Draft',
            'contact_phone' => '081234567899',
            'verification_status' => RegistrationStatusEnum::Draft,
        ]);

        Livewire::test(ParticipantsPage::class)
            // Gugus depan terverifikasi tampil
            ->assertSee('SMP Negeri 1 Padangsidimpuan')
            // Gugus depan draft tidak tampil
            ->assertDontSee('SMP Swasta Belum Verifikasi')
            // Tanpa melabeli status verifikasi
            ->assertDontSee('Disetujui & Terverifikasi')
            ->assertDontSee('Telah Diverifikasi')
            ->assertDontSee('Terverifikasi');
    }
}


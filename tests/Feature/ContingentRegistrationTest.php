<?php

namespace Tests\Feature;

use App\Enums\PatrolGenderEnum;
use App\Enums\PatrolRoleEnum;
use App\Enums\RegistrationStatusEnum;
use App\Enums\RoleEnum;
use App\Models\Chaperone;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use App\Jobs\SendVerificationResultPush;
use App\Models\User;
use App\Services\WebPushService;
use App\Livewire\Portal\PatrolsManagement;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class ContingentRegistrationTest extends TestCase
{
    use RefreshDatabase, \Tests\Concerns\BuildsCompleteRoster;

    protected User $adminUser;
    protected User $committeeUser;
    protected User $scoutGroupUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->adminUser = User::factory()->create([
            'name' => 'Admin Kwarcab',
            'username' => 'admin.test',
            'is_active' => true,
        ]);
        $this->adminUser->assignRole(RoleEnum::Admin);

        $this->committeeUser = User::factory()->create([
            'name' => 'Panitia Lapangan',
            'username' => 'committee.test',
            'is_active' => true,
        ]);
        $this->committeeUser->assignRole(RoleEnum::Committee);

        $this->scoutGroupUser = User::factory()->create([
            'name' => 'Pangkalan SMPN 1 Padangsidimpuan',
            'username' => 'gudep.smpn1',
            'phone_number' => '08126001001',
            'is_active' => true,
        ]);
        $this->scoutGroupUser->assignRole(RoleEnum::ScoutGroup);
    }

    /**
     * Menguji pangkalan dapat mengakses beranda portal dan memperbarui profil
     */
    public function test_scout_group_user_can_access_portal_and_update_profile(): void
    {
        $response = $this->actingAs($this->scoutGroupUser)->get(route('scout-group.index'));
        $response->assertOk();
        $response->assertSee('Portal Gugus Depan');

        // Update profil pangkalan
        $updateResponse = $this->actingAs($this->scoutGroupUser)->post(route('scout-group.profile.update'), [
            'school_name' => 'SMP Negeri 1 Padangsidimpuan',
            'group_number' => '01-093',
            'kwarran' => 'Kwarran Padangsidimpuan Utara',
            'address' => 'Jl. Kenanga No. 10',
            'pic_name' => 'Budi Santoso, S.Pd',
            'phone_number' => '08126001001',
            'username' => $this->scoutGroupUser->username,
        ]);

        $updateResponse->assertRedirect(route('scout-group.index'));

        $this->assertDatabaseHas('scout_groups', [
            'user_id' => $this->scoutGroupUser->id,
            'school_name' => 'SMP Negeri 1 Padangsidimpuan',
            'group_number' => '01-093 & 01-094',
            'male_group_number' => '01-093',
            'female_group_number' => '01-094',
            'contact_person' => 'Budi Santoso, S.Pd',
            'kwarran_name' => 'Kwarran Padangsidimpuan Utara',
            'address' => 'Jl. Kenanga No. 10, Kwarran Padangsidimpuan Utara, Kota Padangsidimpuan',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->scoutGroupUser->id,
            'name' => 'Budi Santoso, S.Pd',
            'phone_number' => '08126001001',
        ]);

        // Halaman profil menampilkan form identik registrasi
        $this->actingAs($this->scoutGroupUser)->get(route('scout-group.profile'))
            ->assertOk()
            ->assertSee('Kwartir Ranting (Kwarran)')
            ->assertSee('Jl. Kenanga No. 10');
    }

    /**
     * Menguji profil terkunci setelah pendaftaran diajukan atau diverifikasi
     */
    public function test_scout_group_cannot_update_profile_after_submission_or_verification(): void
    {
        foreach ([RegistrationStatusEnum::Submitted, RegistrationStatusEnum::Verified] as $status) {
            $scoutGroup = ScoutGroup::factory()->create([
                'user_id' => $this->scoutGroupUser->id,
                'school_name' => 'SMP Terkunci',
                'verification_status' => $status,
            ]);

            $this->actingAs($this->scoutGroupUser)->get(route('scout-group.profile'))
                ->assertOk()
                ->assertSee('Data Terkunci')
                ->assertDontSee('Simpan Profil');

            $this->actingAs($this->scoutGroupUser)->post(route('scout-group.profile.update'), [
                'school_name' => 'Nama Diubah',
                'group_number' => '01-093',
                'kwarran' => 'Kwarran Padangsidimpuan Utara',
                'address' => 'Jl. Baru',
                'pic_name' => 'Nama Baru',
                'phone_number' => '0800000000',
                'username' => 'baru',
                'password' => 'password-baru-123',
                'password_confirmation' => 'password-baru-123',
            ])->assertSessionHasErrors('status');

            $this->assertDatabaseHas('scout_groups', ['id' => $scoutGroup->id, 'school_name' => 'SMP Terkunci']);
            $this->assertDatabaseMissing('users', ['username' => 'baru']);

            $scoutGroup->delete();
        }
    }

    /**
     * Menguji akun Gudep dinotifikasi saat pendaftaran disetujui, diminta revisi, atau ditolak
     */
    public function test_verification_result_dispatches_push_to_scout_group_account(): void
    {
        Queue::fake();

        $scoutGroup = ScoutGroup::factory()->create([
            'user_id' => $this->scoutGroupUser->id,
            'verification_status' => RegistrationStatusEnum::Submitted,
        ]);

        // Perubahan ke Submitted / field lain tidak memicu notifikasi
        $scoutGroup->update(['school_name' => 'SMP Nama Lain']);
        Queue::assertNothingPushed();

        foreach ([RegistrationStatusEnum::Verified, RegistrationStatusEnum::NeedsRevision, RegistrationStatusEnum::Rejected] as $status) {
            $scoutGroup->update(['verification_status' => $status, 'revision_notes' => 'Lengkapi berkas peserta.']);

            Queue::assertPushed(SendVerificationResultPush::class,
                fn ($job) => $job->scoutGroupId === $scoutGroup->id && $job->status === $status->value);
        }
    }

    /**
     * Menguji job hanya mengirim push ke perangkat milik akun Gudep terkait
     */
    public function test_verification_result_job_notifies_owner_account(): void
    {
        $scoutGroup = ScoutGroup::factory()->create([
            'user_id' => $this->scoutGroupUser->id,
            'verification_status' => RegistrationStatusEnum::NeedsRevision,
            'revision_notes' => 'Foto peserta belum jelas.',
        ]);

        $this->mock(WebPushService::class, function ($mock) {
            $mock->shouldReceive('notifyUser')
                ->once()
                ->withArgs(fn ($user, $payload) => $user->id === $this->scoutGroupUser->id
                    && $payload['title'] === 'Pendaftaran Perlu Perbaikan'
                    && str_contains($payload['body'], 'Foto peserta belum jelas.'))
                ->andReturn(['sent' => 1, 'failed' => 0]);
        });

        (new SendVerificationResultPush($scoutGroup->id, RegistrationStatusEnum::NeedsRevision->value))
            ->handle(app(WebPushService::class));
    }

    /**
     * Menguji pembentukan regu putra & putri tanpa batas jumlah regu per kategori, nama regu unik per gudep
     */
    public function test_scout_group_can_create_multiple_patrols_per_category_with_unique_names(): void
    {
        $scoutGroup = ScoutGroup::factory()->create(['user_id' => $this->scoutGroupUser->id]);

        // Buat regu putra
        $resBoys = $this->actingAs($this->scoutGroupUser)->post(route('scout-group.patrols.store'), [
            'patrol_name' => 'Rajawali',
            'gender_category' => 'boys',
        ]);
        $resBoys->assertRedirect(route('scout-group.patrols'));

        $this->assertDatabaseHas('patrols', [
            'scout_group_id' => $scoutGroup->id,
            'patrol_name' => 'Rajawali',
            'gender_category' => PatrolGenderEnum::Boys->value,
        ]);

        // Buat regu putri
        $resGirls = $this->actingAs($this->scoutGroupUser)->post(route('scout-group.patrols.store'), [
            'patrol_name' => 'Melati',
            'gender_category' => 'girls',
        ]);
        $resGirls->assertRedirect(route('scout-group.patrols'));

        $this->assertDatabaseHas('patrols', [
            'scout_group_id' => $scoutGroup->id,
            'patrol_name' => 'Melati',
            'gender_category' => PatrolGenderEnum::Girls->value,
        ]);

        // Regu putra kedua diperbolehkan
        $resBoysSecond = $this->actingAs($this->scoutGroupUser)->post(route('scout-group.patrols.store'), [
            'patrol_name' => 'Garuda',
            'gender_category' => 'boys',
        ]);
        $resBoysSecond->assertRedirect(route('scout-group.patrols'));
        $this->assertEquals(2, $scoutGroup->patrols()->where('gender_category', PatrolGenderEnum::Boys->value)->count());

        // Nama regu yang sama dalam satu gudep ditolak
        $resDuplicate = $this->actingAs($this->scoutGroupUser)->post(route('scout-group.patrols.store'), [
            'patrol_name' => 'Garuda',
            'gender_category' => 'boys',
        ]);
        $resDuplicate->assertSessionHasErrors('patrol_name');
        $this->assertEquals(1, $scoutGroup->patrols()->where('patrol_name', 'Garuda')->count());
    }

    /**
     * Menguji validasi penambahan peserta regu dengan pembatasan 1 Pinru dan 1 Wapinru
     */
    public function test_scout_group_can_add_participants_with_leader_rules(): void
    {
        $scoutGroup = ScoutGroup::factory()->create(['user_id' => $this->scoutGroupUser->id]);
        $patrol = Patrol::factory()->create([
            'scout_group_id' => $scoutGroup->id,
            'gender_category' => PatrolGenderEnum::Boys,
            'patrol_name' => 'Elang',
        ]);

        // Tambah Pinru pertama
        $resPinru1 = $this->actingAs($this->scoutGroupUser)->post(route('scout-group.participants.store', $patrol->id), $this->participantPayload([
            'full_name' => 'Ahmad Fauzi',
            'scout_id' => '02.08.01.001',
            'role_in_patrol' => PatrolRoleEnum::PatrolLeader->value,
            'gender' => 'male',
            'birth_date' => '2011-05-12',
            'birth_place' => 'Padangsidimpuan',
            'blood_type' => 'O',
        ]));
        $resPinru1->assertRedirect(route('scout-group.patrols'));
        $this->assertDatabaseHas('participants', ['full_name' => 'Ahmad Fauzi']);

        // Coba tambah Pinru kedua ke regu yang sama (harus ditolak)
        $resPinru2 = $this->actingAs($this->scoutGroupUser)->post(route('scout-group.participants.store', $patrol->id), $this->participantPayload([
            'full_name' => 'Bambang Irawan',
            'role_in_patrol' => PatrolRoleEnum::PatrolLeader->value,
            'gender' => 'male',
            'birth_date' => '2011-06-14',
            'birth_place' => 'Padangsidimpuan',
        ]));
        $resPinru2->assertSessionHasErrors('role_in_patrol');
        $this->assertDatabaseMissing('participants', ['full_name' => 'Bambang Irawan']);

        // Tambah Wapinru
        $resWapinru = $this->actingAs($this->scoutGroupUser)->post(route('scout-group.participants.store', $patrol->id), $this->participantPayload([
            'full_name' => 'Candra Wijaya',
            'role_in_patrol' => PatrolRoleEnum::AssistantPatrolLeader->value,
            'gender' => 'male',
            'birth_date' => '2011-08-20',
            'birth_place' => 'Padangsidimpuan',
        ]));
        $resWapinru->assertRedirect(route('scout-group.patrols'));
        $this->assertDatabaseHas('participants', ['full_name' => 'Candra Wijaya']);

        // Tambah Anggota biasa
        $resMember = $this->actingAs($this->scoutGroupUser)->post(route('scout-group.participants.store', $patrol->id), $this->participantPayload([
            'full_name' => 'Dedi Gunawan',
            'role_in_patrol' => PatrolRoleEnum::Member->value,
            'gender' => 'male',
            'birth_date' => '2011-09-01',
            'birth_place' => 'Padangsidimpuan',
        ]));
        $resMember->assertRedirect(route('scout-group.patrols'));

        // Uji hapus peserta
        $participantToDelete = Participant::where('full_name', 'Dedi Gunawan')->first();
        $delRes = $this->actingAs($this->scoutGroupUser)->delete(route('scout-group.participants.destroy', $participantToDelete->id));
        $delRes->assertRedirect();
        $this->assertDatabaseMissing('participants', ['full_name' => 'Dedi Gunawan']);
    }

    /**
     * Menguji pengelolaan pembina pendamping (Bindamping)
     */
    public function test_scout_group_can_manage_chaperones(): void
    {
        $scoutGroup = ScoutGroup::factory()->create(['user_id' => $this->scoutGroupUser->id]);
        Patrol::factory()->create(['scout_group_id' => $scoutGroup->id, 'gender_category' => PatrolGenderEnum::Boys]);

        $res = $this->actingAs($this->scoutGroupUser)->post(route('scout-group.chaperones.store'), [
            'full_name' => 'Rahmat Hidayat, S.Pd',
            'scout_id' => '02.08.01.901',
            'national_identity_number' => '1277010101850001',
            'gender' => 'male',
            'phone_number' => '08126111222',
            'birth_place' => 'Padangsidimpuan',
            'birth_date' => '1985-01-01',
            'religion' => 'Islam',
            'blood_type' => 'O',
            'home_address' => 'Jl. Merdeka No. 10',
            'city' => 'Padangsidimpuan',
            'postal_code' => '22727',
            'photo' => \Illuminate\Http\UploadedFile::fake()->image('foto_chaperone.jpg', 300, 400),
        ]);

        $res->assertRedirect();
        $this->assertDatabaseHas('chaperones', [
            'scout_group_id' => $scoutGroup->id,
            'full_name' => 'Rahmat Hidayat, S.Pd',
            'phone_number' => '08126111222',
        ]);

        $chaperone = Chaperone::where('full_name', 'Rahmat Hidayat, S.Pd')->first();
        $delRes = $this->actingAs($this->scoutGroupUser)->delete(route('scout-group.chaperones.destroy', $chaperone->id));
        $delRes->assertRedirect();
        $this->assertDatabaseMissing('chaperones', ['id' => $chaperone->id]);
    }

    /**
     * Menguji pengisian formulir biodata lengkap pembina pendamping dan pencetakan lembar resmi
     */
    public function test_scout_group_can_fill_and_print_official_chaperone_biodata_form(): void
    {
        $scoutGroup = ScoutGroup::factory()->create([
            'user_id' => $this->scoutGroupUser->id,
            'male_group_number' => '01-093',
            'female_group_number' => '01-094',
        ]);
        Patrol::factory()->create(['scout_group_id' => $scoutGroup->id, 'gender_category' => PatrolGenderEnum::Boys]);

        // 1. Buka formulir tambah bindamping
        $createRes = $this->actingAs($this->scoutGroupUser)->get(route('scout-group.chaperones.create', ['gender' => 'male']));
        $createRes->assertOk();
        $createRes->assertSee('Formulir Biodata Pembina Pendamping');
        $createRes->assertSee('SUMATERA UTARA');
        $createRes->assertSee('KOTA Padangsidimpuan');

        // 2. Simpan formulir lengkap dengan pendidikan, pengalaman, dan pelatihan
        $storeRes = $this->actingAs($this->scoutGroupUser)->post(route('scout-group.chaperones.store'), [
            'full_name' => 'Kak Mahran Harahap, S.Pd',
            'gender' => 'male',
            'birth_place' => 'Padangsidimpuan',
            'birth_date' => '1988-06-12',
            'religion' => 'Islam',
            'blood_type' => 'B',
            'scout_id' => '02.21.10.055',
            'national_identity_number' => '1277011206880001',
            'home_address' => 'Jl. Merdeka No. 45',
            'city' => 'Kota Padangsidimpuan',
            'postal_code' => '22727',
            'home_phone' => '0634-21999',
            'phone_number' => '081266554433',
            'photo' => \Illuminate\Http\UploadedFile::fake()->image('foto_bindamping.jpg', 300, 400),
            'formal_education' => [
                ['level' => 'SD', 'school_name' => 'SDN 1 Padangsidimpuan', 'city' => 'Padangsidimpuan', 'graduation_year' => '2000', 'notes' => 'Lulus'],
                ['level' => 'SMP', 'school_name' => 'SMPN 1 Padangsidimpuan', 'city' => 'Padangsidimpuan', 'graduation_year' => '2003', 'notes' => 'Lulus'],
                ['level' => 'SMA', 'school_name' => 'SMAN 1 Padangsidimpuan', 'city' => 'Padangsidimpuan', 'graduation_year' => '2006', 'notes' => 'Lulus'],
                ['level' => 'S1', 'school_name' => 'UGN Padangsidimpuan', 'city' => 'Padangsidimpuan', 'graduation_year' => '2011', 'notes' => 'S.Pd'],
            ],
            'scout_experiences' => [
                ['level' => 'Siaga', 'year' => '1998', 'group_number' => '01-001', 'base' => 'SDN 1'],
                ['level' => 'Penggalang', 'year' => '2001', 'group_number' => '01-001', 'base' => 'SMPN 1'],
                ['level' => 'Penegak', 'year' => '2004', 'group_number' => '01-001', 'base' => 'SMAN 1'],
                ['level' => 'Pandega', 'year' => '', 'group_number' => '', 'base' => ''],
                ['level' => 'Pembina', 'year' => '2015', 'group_number' => '01-093', 'base' => 'SMPN 2'],
            ],
            'scout_trainings' => [
                ['course' => 'KMD', 'year' => '2016', 'location' => 'Kwarcab Padangsidimpuan', 'notes' => 'Lulus'],
                ['course' => 'KML', 'year' => '2020', 'location' => 'Kwarda Sumut', 'notes' => 'Lulus'],
            ],
            'other_trainings' => [
                ['name' => 'Pelatihan Manajemen Regu', 'year' => '2022', 'location' => 'Medan', 'notes' => 'Sertifikat'],
            ],
        ]);

        $storeRes->assertRedirect(route('scout-group.chaperones'));
        $chaperone = Chaperone::where('full_name', 'Kak Mahran Harahap, S.Pd')->first();
        $this->assertNotNull($chaperone);
        $this->assertEquals('Padangsidimpuan', $chaperone->birth_place);
        $this->assertEquals('Islam', $chaperone->religion);
        $this->assertEquals('B', $chaperone->blood_type);
        $this->assertCount(4, $chaperone->formal_education);

        // 3. Edit data bindamping
        $editRes = $this->actingAs($this->scoutGroupUser)->get(route('scout-group.chaperones.edit', $chaperone->id));
        $editRes->assertOk();
        $editRes->assertSee('Kak Mahran Harahap, S.Pd');

        // 4. Update data bindamping
        $updateRes = $this->actingAs($this->scoutGroupUser)->put(route('scout-group.chaperones.update', $chaperone->id), [
            'full_name' => 'Kak Mahran Harahap, S.Pd, M.Pd',
            'gender' => 'male',
            'birth_place' => 'Padangsidimpuan',
            'birth_date' => '1988-06-12',
            'religion' => 'Islam',
            'blood_type' => 'AB',
            'scout_id' => '02.21.10.055',
            'national_identity_number' => '1277011206880001',
            'home_address' => 'Jl. Merdeka No. 45',
            'city' => 'Kota Padangsidimpuan',
            'postal_code' => '22727',
            'phone_number' => '081266554433',
        ]);
        $updateRes->assertRedirect(route('scout-group.chaperones'));
        $this->assertEquals('Kak Mahran Harahap, S.Pd, M.Pd', $chaperone->fresh()->full_name);
        $this->assertEquals('AB', $chaperone->fresh()->blood_type);

        // 5. Cetak lembar formulir biodata bindamping (oleh Gudep dalam format PDF)
        $scoutGroup->update(['verification_status' => RegistrationStatusEnum::Verified]);
        $printRes = $this->actingAs($this->scoutGroupUser)->get(route('scout-group.chaperones.print', $chaperone->id).'?raw=1');
        $printRes->assertOk();
        $printRes->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('Form_03_Biodata_', (string) $printRes->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF', $printRes->getContent());

        // 6. Cetak lembar formulir biodata bindamping (oleh Admin dalam format PDF)
        $adminPrintRes = $this->actingAs($this->adminUser)->get(route('admin.scout-groups.chaperones.print', [$scoutGroup->id, $chaperone->id]).'?raw=1');
        $adminPrintRes->assertOk();
        $adminPrintRes->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('Form_03_Biodata_', (string) $adminPrintRes->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF', $adminPrintRes->getContent());
    }

    /**
     * Menguji pengajuan pendaftaran (Submit) mengunci data dan mensyaratkan minimal 1 regu & 1 bindamping
     */
    public function test_scout_group_submission_validation_and_locking(): void
    {
        $scoutGroup = ScoutGroup::factory()->create([
            'user_id' => $this->scoutGroupUser->id,
            'verification_status' => RegistrationStatusEnum::Draft,
        ]);

        // Coba submit tanpa regu & bindamping (harus gagal)
        $failSubmit = $this->actingAs($this->scoutGroupUser)->post(route('scout-group.submit'));
        $failSubmit->assertSessionHasErrors();

        // Buat regu lengkap (8 peserta, Pinru & Wapinru) dan bindamping sesuai gender regu
        $patrol = Patrol::factory()->create(['scout_group_id' => $scoutGroup->id, 'gender_category' => PatrolGenderEnum::Boys]);
        $this->fillPatrolRoster($patrol);
        $patrol->update(['chaperone_id' => Chaperone::factory()->create(['scout_group_id' => $scoutGroup->id, 'gender' => 'male'])->id]);

        // Submit sekarang harus berhasil
        $successSubmit = $this->actingAs($this->scoutGroupUser)->post(route('scout-group.submit'));
        $successSubmit->assertRedirect(route('scout-group.index'));

        $scoutGroup->refresh();
        $this->assertEquals(RegistrationStatusEnum::Submitted, $scoutGroup->verification_status);
        $this->assertFalse($scoutGroup->isEditable());

        // Karena terkunci, coba perbarui profil atau tambah regu harus ditolak
        $lockRes = $this->actingAs($this->scoutGroupUser)->post(route('scout-group.patrols.store'), [
            'patrol_name' => 'Mawar',
            'gender_category' => 'girls',
        ]);
        $lockRes->assertSessionHasErrors('status');
    }

    /**
     * Menguji Administrator, Panitia, dan Dewan Juri dapat melihat halaman kontingen, sedangkan Gudep ditolak
     */
    public function test_admin_and_committee_can_access_scout_groups_directory_and_scout_group_is_forbidden(): void
    {
        // Admin bisa akses direktori kontingen
        $adminRes = $this->actingAs($this->adminUser)->get(route('admin.scout-groups.index'));
        $adminRes->assertOk();
        $adminRes->assertSee('Verifikasi Pendaftaran Kontingen');

        // Panitia kini diizinkan akses halaman kontingen
        $committeeRes = $this->actingAs($this->committeeUser)->get(route('admin.scout-groups.index'));
        $committeeRes->assertOk();

        // Akun Gudep ditolak dari halaman admin direktori kontingen
        $scoutGroupRes = $this->actingAs($this->scoutGroupUser)->get(route('admin.scout-groups.index'));
        $scoutGroupRes->assertForbidden();
    }

    /**
     * Menguji Administrator dapat membuka detail verifikasi pangkalan beserta regu putra dan putri
     */
    public function test_admin_can_view_scout_group_show_page_with_patrol_badges(): void
    {
        $scoutGroup = ScoutGroup::factory()->create(['user_id' => $this->scoutGroupUser->id]);
        $boysPatrol = Patrol::factory()->create([
            'scout_group_id' => $scoutGroup->id,
            'patrol_name' => 'Elang Sakti',
            'gender_category' => PatrolGenderEnum::Boys,
        ]);
        $girlsPatrol = Patrol::factory()->create([
            'scout_group_id' => $scoutGroup->id,
            'patrol_name' => 'Mawar Putih',
            'gender_category' => PatrolGenderEnum::Girls,
        ]);

        $this->assertTrue($boysPatrol->isBoys());
        $this->assertFalse($boysPatrol->isGirls());
        $this->assertTrue($girlsPatrol->isGirls());
        $this->assertFalse($girlsPatrol->isBoys());

        $res = $this->actingAs($this->adminUser)->get(route('admin.scout-groups.show', $scoutGroup->id));
        $res->assertOk();
        $res->assertSee('Elang Sakti');
        $res->assertSee('Mawar Putih');
        $res->assertSee('Penggalang Putra');
        $res->assertSee('Penggalang Putri');
    }

    /**
     * Menguji alur verifikasi lengkap oleh Admin: Approve, Request Revision, dan Reject
     */
    public function test_admin_verification_workflow_approve_revision_and_reject(): void
    {
        $scoutGroup = ScoutGroup::factory()->create([
            'user_id' => $this->scoutGroupUser->id,
            'verification_status' => RegistrationStatusEnum::Submitted,
        ]);
        $patrol = Patrol::factory()->create([
            'scout_group_id' => $scoutGroup->id,
            'status' => RegistrationStatusEnum::Submitted,
        ]);
        $participant = Participant::factory()->create([
            'patrol_id' => $patrol->id,
            'status' => RegistrationStatusEnum::Submitted,
        ]);
        $chaperone = Chaperone::factory()->create([
            'scout_group_id' => $scoutGroup->id,
            'status' => RegistrationStatusEnum::Submitted,
        ]);

        // 1. Admin Menyetujui (Approve / Verify)
        \Livewire\Livewire::withoutLazyLoading()->actingAs($this->adminUser)
            ->test(\App\Livewire\Admin\ScoutGroupVerificationActions::class, ['scoutGroupId' => $scoutGroup->id])
            ->call('verify');

        $scoutGroup->refresh();
        $patrol->refresh();
        $participant->refresh();
        $chaperone->refresh();

        $this->assertEquals(RegistrationStatusEnum::Verified, $scoutGroup->verification_status);
        $this->assertEquals(RegistrationStatusEnum::Verified, $patrol->status);
        $this->assertEquals(RegistrationStatusEnum::Verified, $participant->status);
        $this->assertEquals(RegistrationStatusEnum::Verified, $chaperone->status);
        $this->assertNull($scoutGroup->verified_by);
        $this->assertNotNull($scoutGroup->verified_at);

        // 2. Admin Meminta Revisi (Needs Revision)
        $scoutGroup->update(['verification_status' => RegistrationStatusEnum::Submitted]);
        \Livewire\Livewire::withoutLazyLoading()->actingAs($this->adminUser)
            ->test(\App\Livewire\Admin\ScoutGroupVerificationActions::class, ['scoutGroupId' => $scoutGroup->id])
            ->set('revisionNotes', 'Tolong lengkapi NTA Pramuka pada seluruh peserta dan bindamping.')
            ->call('requestRevision');

        $scoutGroup->refresh();
        $this->assertEquals(RegistrationStatusEnum::NeedsRevision, $scoutGroup->verification_status);
        $this->assertEquals('Tolong lengkapi NTA Pramuka pada seluruh peserta dan bindamping.', $scoutGroup->revision_notes);
        // Dalam status NeedsRevision, pangkalan diizinkan mengedit kembali
        $this->assertTrue($scoutGroup->isEditable());

        // 3. Admin Menolak Pendaftaran (Reject)
        $scoutGroup->update(['verification_status' => RegistrationStatusEnum::Submitted]);
        \Livewire\Livewire::withoutLazyLoading()->actingAs($this->adminUser)
            ->test(\App\Livewire\Admin\ScoutGroupVerificationActions::class, ['scoutGroupId' => $scoutGroup->id])
            ->set('rejectNotes', 'Tidak memenuhi persyaratan usia penggalang SMP/MTs.')
            ->call('reject');

        $scoutGroup->refresh();
        $this->assertEquals(RegistrationStatusEnum::Rejected, $scoutGroup->verification_status);
        $this->assertFalse($scoutGroup->isEditable());
    }

    /**
     * Menguji pengisian biodata resmi peserta, edit data, dan cetak lembar fisik
     */
    public function test_scout_group_can_fill_edit_and_print_official_participant_biodata_form(): void
    {
        $scoutGroup = ScoutGroup::factory()->create([
            'user_id' => $this->scoutGroupUser->id,
            'male_group_number' => '01-093',
            'female_group_number' => '01-094',
        ]);

        $patrol = Patrol::create([
            'scout_group_id' => $scoutGroup->id,
            'patrol_name' => 'Elang Jawa',
            'gender_category' => PatrolGenderEnum::Boys,
            'status' => RegistrationStatusEnum::Draft,
        ]);

        // 1. Tampilkan form create
        $createRes = $this->actingAs($this->scoutGroupUser)->get(route('scout-group.participants.create', $patrol->id));
        $createRes->assertOk();
        $createRes->assertSee('Formulir Biodata Peserta: Elang Jawa');
        $createRes->assertSee('Riwayat Kepramukaan: SIAGA');
        $createRes->assertSee('Riwayat Kepramukaan: PENGGALANG');
        $createRes->assertDontSee('name="national_student_id"', false);

        // 2. Simpan peserta dengan biodata lengkap tanpa NISN
        $storeRes = $this->actingAs($this->scoutGroupUser)->post(route('scout-group.participants.store', $patrol->id), $this->participantPayload([
            'full_name' => 'Muhammad Zaki',
            'role_in_patrol' => 'patrol_leader',
            'gender' => 'male',
            'scout_id' => '02.21.01.100',
            'birth_place' => 'Padangsidimpuan',
            'birth_date' => '2011-04-10',
            'religion' => 'Islam',
            'blood_type' => 'O',
            'home_address' => 'Jl. Imam Bonjol No. 10',
            'city' => 'Padangsidimpuan',
            'postal_code' => '22719',
            'home_phone' => '0634-21111',
            'phone' => '081234567890',
            'parent_name' => 'H. Zulkifli',
            'parent_job' => 'PNS Kemenag',
            'parent_address' => 'Jl. Imam Bonjol No. 10',
            'parent_city' => 'Padangsidimpuan',
            'parent_postal_code' => '22719',
            'parent_home_phone' => '0634-21111',
            'parent_phone' => '081377889900',
            'formal_education' => [
                ['level' => 'SD', 'school_name' => 'SDN 2 Padangsidimpuan', 'city' => 'Padangsidimpuan', 'graduation_year' => '2023', 'notes' => 'Lulus'],
                ['level' => 'SMP', 'school_name' => 'SMPN 1 Padangsidimpuan', 'city' => 'Padangsidimpuan', 'graduation_year' => '2026', 'notes' => 'Siswa Aktif'],
            ],
            'scout_siaga' => [
                'year' => '2019-2023',
                'group_number' => '01-093',
                'pangkalan' => 'SDN 2',
                'levels' => ['mula' => '2020', 'bantu' => '2021', 'tata' => '2022', 'garuda' => ''],
                'activities' => ['Pesta Siaga Cabang', '', '', '', '', ''],
                'tkk' => ['Menabung', 'Sholat', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''],
            ],
            'scout_penggalang' => [
                'year' => '2023-2026',
                'group_number' => '01-093',
                'pangkalan' => 'SMPN 1',
                'levels' => ['ramu' => '2024', 'rakit' => '2025', 'terap' => '', 'garuda' => ''],
                'activities' => ['LT-I', 'LT-II', '', '', '', '', ''],
                'tkk_purwa' => ['P3K', '', '', '', '', '', '', '', '', ''],
                'tkk_madya' => ['Sholat', '', '', '', '', '', '', '', '', ''],
                'tkk_utama' => ['', '', '', '', '', '', '', '', '', ''],
            ],
        ]));

        $storeRes->assertRedirect(route('scout-group.patrols'));
        $participant = Participant::where('full_name', 'Muhammad Zaki')->first();
        $this->assertNotNull($participant);
        $this->assertEquals('H. Zulkifli', $participant->parent_name);
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('participants', 'national_student_id'));
        $this->assertCount(2, $participant->formal_education);

        // 3. Tampilkan form edit
        $editRes = $this->actingAs($this->scoutGroupUser)->get(route('scout-group.participants.edit', $participant->id));
        $editRes->assertOk();
        $editRes->assertSee('Edit Biodata Peserta: Muhammad Zaki');
        $editRes->assertSee('H. Zulkifli');
        $editRes->assertDontSee('name="national_student_id"', false);

        // 4. Update data peserta
        $updateRes = $this->actingAs($this->scoutGroupUser)->put(route('scout-group.participants.update', $participant->id), $this->participantPayload([
            'full_name' => 'Muhammad Zaki Harahap',
            'role_in_patrol' => 'patrol_leader',
            'gender' => 'male',
            'birth_place' => 'Padangsidimpuan',
            'birth_date' => '2011-04-10',
            'religion' => 'Islam',
            'parent_name' => 'H. Zulkifli Harahap',
        ]));
        $updateRes->assertRedirect(route('scout-group.patrols'));
        $participant->refresh();
        $this->assertEquals('Muhammad Zaki Harahap', $participant->full_name);
        $this->assertEquals('H. Zulkifli Harahap', $participant->parent_name);

        // 5. Cetak lembar resmi oleh Gudep (dalam format PDF)
        $scoutGroup->update(['verification_status' => RegistrationStatusEnum::Verified]);
        $printRes = $this->actingAs($this->scoutGroupUser)->get(route('scout-group.participants.print', $participant->id).'?raw=1');
        $printRes->assertOk();
        $printRes->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('Form_02_Biodata_', (string) $printRes->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF', $printRes->getContent());

        // 6. Cetak lembar resmi oleh Admin (dalam format PDF)
        $adminPrintRes = $this->actingAs($this->adminUser)->get(route('admin.scout-groups.participants.print', [$scoutGroup->id, $participant->id]).'?raw=1');
        $adminPrintRes->assertOk();
        $adminPrintRes->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('Form_02_Biodata_', (string) $adminPrintRes->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF', $adminPrintRes->getContent());
    }

    /**
     * Menguji pencetakan FORM : 01 (Rekap Pendaftaran Gudep - Peserta & Bindamping)
     */
    public function test_scout_group_and_admin_can_view_and_print_form_01_recap(): void
    {
        $scoutGroup = ScoutGroup::factory()->create([
            'user_id' => $this->scoutGroupUser->id,
            'school_name' => 'SMP Negeri 1 Padangsidimpuan',
            'address' => 'Jl. Kenanga No. 2, Padangsidimpuan',
            'male_group_number' => '01-093',
            'female_group_number' => '01-094',
        ]);

        $chaperone = Chaperone::create([
            'scout_group_id' => $scoutGroup->id,
            'full_name' => 'Kak Rahmat Hidayat, S.Pd',
            'gender' => 'male',
            'phone_number' => '081234567890',
            'status' => RegistrationStatusEnum::Draft,
        ]);

        $patrol = Patrol::create([
            'scout_group_id' => $scoutGroup->id,
            'patrol_name' => 'Rajawali',
            'gender_category' => PatrolGenderEnum::Boys,
            'status' => RegistrationStatusEnum::Draft,
        ]);

        // Tambah Pinru & Anggota
        Participant::create([
            'patrol_id' => $patrol->id,
            'full_name' => 'Fahri Siregar',
            'role_in_patrol' => 'patrol_leader',
            'gender' => 'male',
            'birth_place' => 'Padangsidimpuan',
            'birth_date' => '2011-05-15',
            'class_grade' => 'VIII-A',
            'scout_penggalang' => [
                'levels' => ['ramu' => '2024', 'rakit' => '2025', 'terap' => '2026', 'garuda' => ''],
            ],
            'status' => RegistrationStatusEnum::Draft,
        ]);

        Participant::create([
            'patrol_id' => $patrol->id,
            'full_name' => 'Rizky Harahap',
            'role_in_patrol' => 'assistant_patrol_leader',
            'gender' => 'male',
            'birth_place' => 'Padangsidimpuan',
            'birth_date' => '2011-06-20',
            'class_grade' => 'VIII-B',
            'scout_penggalang' => [
                'levels' => ['ramu' => '2024', 'rakit' => '2025', 'terap' => '', 'garuda' => ''],
            ],
            'status' => RegistrationStatusEnum::Draft,
        ]);

        // 1. Gudep mencetak FORM : 01 (dalam format PDF)
        $scoutGroup->update(['verification_status' => RegistrationStatusEnum::Verified]);
        $gudepRes = $this->actingAs($this->scoutGroupUser)->get(route('scout-group.patrols.form-01', $patrol->id).'?raw=1');
        $gudepRes->assertOk();
        $gudepRes->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('Form_01_Rekap_Regu_', (string) $gudepRes->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF', $gudepRes->getContent());

        // 2. Admin mencetak FORM : 01 (dalam format PDF)
        $adminRes = $this->actingAs($this->adminUser)->get(route('admin.scout-groups.patrols.form-01', [$scoutGroup->id, $patrol->id]).'?raw=1');
        $adminRes->assertOk();
        $adminRes->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('Form_01_Rekap_Regu_', (string) $adminRes->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF', $adminRes->getContent());
    }

    /**
     * Memastikan pembuatan regu putra lalu regu putri via Livewire berjalan sukses tanpa error kuota
     */
    public function test_scout_group_can_create_boys_and_then_girls_patrol_via_livewire(): void
    {
        $scoutGroup = ScoutGroup::factory()->create(['user_id' => $this->scoutGroupUser->id]);

        // 1. Buat regu putra
        $component = Livewire::actingAs($this->scoutGroupUser)
            ->test(PatrolsManagement::class)
            ->set('patrol_name', 'Rajawali')
            ->set('gender_category', 'boys')
            ->call('storePatrol')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('patrols', [
            'scout_group_id' => $scoutGroup->id,
            'patrol_name' => 'Rajawali',
            'gender_category' => PatrolGenderEnum::Boys->value,
        ]);

        // 2. Buat regu putri; kategori dipilih langsung karena satu gudep boleh memiliki beberapa regu per gender
        $component
            ->set('patrol_name', 'Mawar')
            ->set('gender_category', 'girls')
            ->call('storePatrol')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('patrols', [
            'scout_group_id' => $scoutGroup->id,
            'patrol_name' => 'Mawar',
            'gender_category' => PatrolGenderEnum::Girls->value,
        ]);

        // 3. Regu putra kedua diperbolehkan, nama ganda ditolak
        $component
            ->set('patrol_name', 'Elang')
            ->set('gender_category', 'boys')
            ->call('storePatrol')
            ->assertHasNoErrors();
        $component
            ->set('patrol_name', 'Elang')
            ->set('gender_category', 'boys')
            ->call('storePatrol')
            ->assertHasErrors(['patrol_name']);

        $scoutGroup->refresh();
        $this->assertCount(2, $scoutGroup->boysPatrols);
        $this->assertCount(1, $scoutGroup->girlsPatrols);
        $this->assertEquals(3, $scoutGroup->patrols()->count());
    }

    /**
     * Memastikan pangkalan dapat menghapus data regu beserta anggotanya via Livewire & HTTP
     */
    public function test_scout_group_can_delete_patrol(): void
    {
        $scoutGroup = ScoutGroup::factory()->create(['user_id' => $this->scoutGroupUser->id]);

        $patrol = Patrol::create([
            'scout_group_id' => $scoutGroup->id,
            'patrol_name' => 'Rajawali',
            'gender_category' => PatrolGenderEnum::Boys,
            'status' => RegistrationStatusEnum::Draft,
        ]);

        $participant = Participant::create([
            'patrol_id' => $patrol->id,
            'full_name' => 'Ahmad Subari',
            'gender' => 'male',
            'role_in_patrol' => PatrolRoleEnum::PatrolLeader,
            'birth_date' => '2011-05-15',
            'birth_place' => 'Padangsidimpuan',
            'status' => RegistrationStatusEnum::Draft,
        ]);

        $this->assertDatabaseHas('patrols', ['id' => $patrol->id]);
        $this->assertDatabaseHas('participants', ['id' => $participant->id]);

        // Hapus regu via Livewire
        Livewire::actingAs($this->scoutGroupUser)
            ->test(PatrolsManagement::class)
            ->call('deletePatrol', $patrol->id)
            ->assertHasNoErrors()
            ->assertDispatched('toast-show');

        // Pastikan regu dan anggotanya terhapus
        $this->assertDatabaseMissing('patrols', ['id' => $patrol->id]);
        $this->assertDatabaseMissing('participants', ['id' => $participant->id]);

        // Uji hapus via HTTP DELETE route
        $patrolGirls = Patrol::create([
            'scout_group_id' => $scoutGroup->id,
            'patrol_name' => 'Mawar',
            'gender_category' => PatrolGenderEnum::Girls,
            'status' => RegistrationStatusEnum::Draft,
        ]);

        $deleteRes = $this->actingAs($this->scoutGroupUser)->delete(route('scout-group.patrols.destroy', $patrolGirls->id));
        $deleteRes->assertRedirect(route('scout-group.patrols'));
        $this->assertDatabaseMissing('patrols', ['id' => $patrolGirls->id]);
    }

    /**
     * Payload peserta lengkap (Bagian 1, 2, dan 3 wajib diisi); nilai dapat ditimpa per kasus uji
     */
    protected function participantPayload(array $overrides = []): array
    {
        return array_merge([
            'photo' => \Illuminate\Http\UploadedFile::fake()->image('foto.jpg', 300, 400),
            'full_name' => 'Peserta Uji',
            'role_in_patrol' => 'member',
            'gender' => 'male',
            'religion' => 'Islam',
            'blood_type' => 'O',
            'scout_id' => '02.21.01.999',
            'birth_place' => 'Padangsidimpuan',
            'birth_date' => '2011-05-12',
            'home_address' => 'Jl. Imam Bonjol No. 10',
            'city' => 'Padangsidimpuan',
            'postal_code' => '22719',
            'home_phone' => '0634-21111',
            'phone' => '081234567890',
            'parent_name' => 'Orang Tua Uji',
            'parent_job' => 'Wiraswasta',
            'parent_address' => 'Jl. Imam Bonjol No. 10',
            'parent_city' => 'Padangsidimpuan',
            'parent_postal_code' => '22719',
            'parent_home_phone' => '0634-21111',
            'parent_phone' => '081377889900',
            'formal_education' => [
                ['level' => 'SD', 'school_name' => 'SDN 2 Padangsidimpuan', 'city' => 'Padangsidimpuan', 'graduation_year' => '2023', 'notes' => 'Lulus'],
                ['level' => 'SMP', 'school_name' => 'SMPN 1 Padangsidimpuan', 'city' => 'Padangsidimpuan', 'graduation_year' => '2026', 'notes' => 'Siswa Aktif'],
            ],
        ], $overrides);
    }

    /**
     * Bagian 1, 2, dan 3 formulir peserta wajib diisi seluruhnya
     */
    public function test_participant_form_sections_one_to_three_are_all_required(): void
    {
        $scoutGroup = ScoutGroup::factory()->create(['user_id' => $this->scoutGroupUser->id]);
        $patrol = Patrol::factory()->create(['scout_group_id' => $scoutGroup->id, 'gender_category' => PatrolGenderEnum::Boys]);

        $required = [
            'photo', 'full_name', 'religion', 'blood_type', 'scout_id', 'birth_place', 'birth_date', 'home_address', 'city', 'postal_code', 'phone',
            'parent_name', 'parent_job', 'parent_address', 'parent_city', 'parent_postal_code', 'parent_phone',
            'formal_education',
        ];

        $payload = $this->participantPayload();
        foreach ($required as $field) {
            unset($payload[$field]);
        }

        $this->actingAs($this->scoutGroupUser)
            ->post(route('scout-group.participants.store', $patrol->id), $payload)
            ->assertSessionHasErrors($required);

        foreach (['school_name', 'city'] as $column) {
            $payload = $this->participantPayload();
            $payload['formal_education'][1][$column] = '';

            $this->actingAs($this->scoutGroupUser)
                ->post(route('scout-group.participants.store', $patrol->id), $payload)
                ->assertSessionHasErrors("formal_education.1.$column");
        }

        // Tahun lulus wajib untuk SD, tetapi boleh kosong untuk SMP (peserta masih bersekolah)
        $payload = $this->participantPayload();
        $payload['formal_education'][0]['graduation_year'] = '';
        $this->actingAs($this->scoutGroupUser)
            ->post(route('scout-group.participants.store', $patrol->id), $payload)
            ->assertSessionHasErrors('formal_education.0.graduation_year');

        $payload = $this->participantPayload();
        $payload['formal_education'][1]['graduation_year'] = '';
        $this->actingAs($this->scoutGroupUser)
            ->post(route('scout-group.participants.store', $patrol->id), $payload)
            ->assertSessionDoesntHaveErrors('formal_education.1.graduation_year');
        Participant::query()->delete();

        $this->assertDatabaseCount('participants', 0);

        // Telepon rumah (Bagian 1 dan 2) dan keterangan pendidikan (Bagian 3) opsional
        $optional = $this->participantPayload(['full_name' => 'Tanpa Telepon Rumah', 'home_phone' => '', 'parent_home_phone' => '', 'formal_education' => [
            ['level' => 'SD', 'school_name' => 'SDN 2', 'city' => 'Padangsidimpuan', 'graduation_year' => '2023', 'notes' => ''],
            ['level' => 'SMP', 'school_name' => 'SMPN 1', 'city' => 'Padangsidimpuan', 'graduation_year' => '2026', 'notes' => ''],
        ]]);
        $this->actingAs($this->scoutGroupUser)
            ->post(route('scout-group.participants.store', $patrol->id), $optional)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('scout-group.patrols'));

        $this->actingAs($this->scoutGroupUser)
            ->get(route('scout-group.participants.create', $patrol->id))
            ->assertOk()
            ->assertSee('name="parent_phone" required', false)
            ->assertSee('name="photo" required', false);
    }

    /**
     * Menguji upload berkas persyaratan peserta (7 dokumen) dan berkas pangkalan (Surat Tugas + Bukti Bayar)
     */
    public function test_upload_participant_documents_and_scout_group_administrative_documents(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $scoutGroup = ScoutGroup::factory()->create([
            'user_id' => $this->scoutGroupUser->id,
            'verification_status' => RegistrationStatusEnum::Draft,
        ]);

        $patrol = Patrol::create([
            'scout_group_id' => $scoutGroup->id,
            'patrol_name' => 'Garuda Perkasa',
            'gender_category' => PatrolGenderEnum::Boys,
            'status' => RegistrationStatusEnum::Draft,
        ]);

        $patrol->update(['chaperone_id' => Chaperone::create([
            'scout_group_id' => $scoutGroup->id,
            'full_name' => 'Kak Pembina',
            'gender' => 'male',
            'phone_number' => '08123456789',
            'birth_place' => 'Padangsidimpuan',
            'birth_date' => '1990-01-01',
            'religion' => 'Islam',
            'blood_type' => 'O',
            'scout_id' => '02.21.01.001',
            'home_address' => 'Jl. Pramuka',
            'city' => 'Padangsidimpuan',
            'postal_code' => '22727',
            'status' => RegistrationStatusEnum::Draft,
        ])->id]);

        // 1. Simpan peserta beserta 7 berkas persyaratan
        $fileKta = \Illuminate\Http\UploadedFile::fake()->create('kta.pdf', 500, 'application/pdf');
        $fileBpjs = \Illuminate\Http\UploadedFile::fake()->create('bpjs.pdf', 500, 'application/pdf');
        $fileSehat = \Illuminate\Http\UploadedFile::fake()->create('surat_sehat.pdf', 500, 'application/pdf');
        $fileAkta = \Illuminate\Http\UploadedFile::fake()->create('akta.pdf', 500, 'application/pdf');
        $fileKk = \Illuminate\Http\UploadedFile::fake()->create('kk.pdf', 500, 'application/pdf');
        $fileSku = \Illuminate\Http\UploadedFile::fake()->create('sku.pdf', 500, 'application/pdf');
        $fileSkk = \Illuminate\Http\UploadedFile::fake()->create('skk.pdf', 500, 'application/pdf');

        $payload = $this->participantPayload([
            'full_name' => 'Ahmad Fauzi',
            'doc_kta' => $fileKta,
            'doc_bpjs' => $fileBpjs,
            'doc_health_certificate' => $fileSehat,
            'doc_birth_certificate' => $fileAkta,
            'doc_family_card' => $fileKk,
            'doc_sku' => $fileSku,
            'doc_skk' => $fileSkk,
        ]);

        $res = $this->actingAs($this->scoutGroupUser)->post(route('scout-group.participants.store', $patrol->id), $payload);
        $res->assertRedirect(route('scout-group.patrols'));

        $participant = Participant::where('full_name', 'Ahmad Fauzi')->first();
        $this->assertNotNull($participant);

        // Verifikasi seluruh path berkas tersimpan di database dan storage
        $this->assertNotNull($participant->doc_kta_path);
        $this->assertNotNull($participant->doc_bpjs_path);
        $this->assertNotNull($participant->doc_health_certificate_path);
        $this->assertNotNull($participant->doc_birth_certificate_path);
        $this->assertNotNull($participant->doc_family_card_path);
        $this->assertNotNull($participant->doc_sku_path);
        $this->assertNotNull($participant->doc_skk_path);

        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($participant->doc_kta_path);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($participant->doc_bpjs_path);

        // 2. Update peserta dengan berkas KTA baru (berkas lama harus terhapus)
        $oldKtaPath = $participant->doc_kta_path;
        $newFileKta = \Illuminate\Http\UploadedFile::fake()->create('kta_baru.pdf', 600, 'application/pdf');

        $updatePayload = $this->participantPayload([
            'full_name' => 'Ahmad Fauzi Updated',
            'doc_kta' => $newFileKta,
        ]);

        $updateRes = $this->actingAs($this->scoutGroupUser)->put(route('scout-group.participants.update', $participant->id), $updatePayload);
        $updateRes->assertRedirect(route('scout-group.patrols'));

        $participant->refresh();
        $this->assertNotEquals($oldKtaPath, $participant->doc_kta_path);
        \Illuminate\Support\Facades\Storage::disk('public')->assertMissing($oldKtaPath);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($participant->doc_kta_path);

        // Regu harus tepat 8 peserta (Pinru & Wapinru) sebelum diajukan; satu anggota sudah ada
        $this->fillPatrolRoster($patrol, existingMembers: 1);

        // 3. Uji pengunggahan Berkas Administrasi Pangkalan & Submit via Livewire
        $mandateLetter = \Illuminate\Http\UploadedFile::fake()->create('surat_tugas.pdf', 1024, 'application/pdf');
        $paymentProof = \Illuminate\Http\UploadedFile::fake()->image('bukti_transfer.jpg', 600, 800);

        \Livewire\Livewire::actingAs($this->scoutGroupUser)
            ->test(\App\Livewire\Portal\ReviewRegistration::class)
            ->set('mandate_letter', $mandateLetter)
            ->set('payment_proof', $paymentProof)
            ->call('submitRegistration')
            ->assertHasNoErrors()
            ->assertRedirect(route('scout-group.index'));

        $scoutGroup->refresh();
        $this->assertNotNull($scoutGroup->mandate_letter_path);
        $this->assertNotNull($scoutGroup->payment_proof_path);
        $this->assertEquals(RegistrationStatusEnum::Submitted, $scoutGroup->verification_status);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($scoutGroup->mandate_letter_path);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($scoutGroup->payment_proof_path);

        // 4. Hapus peserta dan pastikan file berkas dan foto dibersihkan dari storage
        // Buka kunci status untuk pengujian destroy
        $scoutGroup->update(['verification_status' => RegistrationStatusEnum::Draft]);
        $allDocPaths = [
            $participant->photo_path,
            $participant->doc_kta_path,
            $participant->doc_bpjs_path,
            $participant->doc_health_certificate_path,
            $participant->doc_birth_certificate_path,
            $participant->doc_family_card_path,
            $participant->doc_sku_path,
            $participant->doc_skk_path,
        ];

        $delRes = $this->actingAs($this->scoutGroupUser)->delete(route('scout-group.participants.destroy', $participant->id));
        $delRes->assertRedirect();
        $this->assertDatabaseMissing('participants', ['id' => $participant->id]);

        foreach ($allDocPaths as $path) {
            if ($path) {
                \Illuminate\Support\Facades\Storage::disk('public')->assertMissing($path);
            }
        }
    }

    /**
     * Menguji tombol ajukan di review pendaftaran memunculkan modal jika persyaratan belum lengkap
     */
    public function test_review_registration_shows_incomplete_modal_when_requirements_are_missing(): void
    {
        $scoutGroup = ScoutGroup::factory()->create([
            'user_id' => $this->scoutGroupUser->id,
            'school_name' => 'SMP N 1',
            'group_number' => '01-093',
            'verification_status' => RegistrationStatusEnum::Draft,
        ]);

        // Skenario 1: Belum ada regu, belum ada bindamping, belum ada berkas
        \Livewire\Livewire::actingAs($this->scoutGroupUser)
            ->test(\App\Livewire\Portal\ReviewRegistration::class)
            ->call('attemptSubmit')
            ->assertSet('showIncompleteModal', true)
            ->assertSet('showSubmitModal', false)
            ->assertSee('Persyaratan Belum Lengkap')
            ->assertSee('Belum Ada Regu Terdaftar')
            ->assertSee('Belum Ada Bindamping Terdaftar')
            ->assertSee('Surat Tugas / Mandat Belum Diunggah')
            ->assertSee('Bukti Pembayaran Belum Diunggah');

        // Skenario 2: Tambah regu tanpa peserta, modal harus memperingatkan regu masih kosong
        $patrol = Patrol::create([
            'scout_group_id' => $scoutGroup->id,
            'patrol_name' => 'Harimau',
            'gender_category' => PatrolGenderEnum::Boys,
            'status' => RegistrationStatusEnum::Draft,
        ]);

        \Livewire\Livewire::actingAs($this->scoutGroupUser)
            ->test(\App\Livewire\Portal\ReviewRegistration::class)
            ->call('attemptSubmit')
            ->assertSet('showIncompleteModal', true)
            ->assertSee('Jumlah Anggota Regu Belum 8 Peserta');

        // Skenario 3: Lengkapi seluruh persyaratan (peserta, bindamping, surat tugas, bukti bayar)
        $this->fillPatrolRoster($patrol);

        $patrol->update(['chaperone_id' => Chaperone::factory()->create([
            'scout_group_id' => $scoutGroup->id,
            'gender' => 'male',
        ])->id]);

        $mandateLetter = \Illuminate\Http\UploadedFile::fake()->create('mandat.pdf', 100, 'application/pdf');
        $paymentProof = \Illuminate\Http\UploadedFile::fake()->image('bukti.jpg', 300, 300);

        \Livewire\Livewire::actingAs($this->scoutGroupUser)
            ->test(\App\Livewire\Portal\ReviewRegistration::class)
            ->set('mandate_letter', $mandateLetter)
            ->set('payment_proof', $paymentProof)
            ->call('attemptSubmit')
            ->assertSet('showIncompleteModal', false)
            ->assertSet('showSubmitModal', true)
            ->assertSee('Konfirmasi Pengajuan Pendaftaran')
            ->call('submitRegistration')
            ->assertHasNoErrors()
            ->assertRedirect(route('scout-group.index'));

        $scoutGroup->refresh();
        $this->assertEquals(RegistrationStatusEnum::Submitted, $scoutGroup->verification_status);
    }

    /**
     * Memastikan Bukti Registrasi (PDF) hanya dapat diunduh oleh Gudep saat telah terverifikasi
     */
    public function test_registration_certificate_pdf_access_and_permissions(): void
    {
        $scoutGroup = ScoutGroup::factory()->create([
            'user_id' => $this->scoutGroupUser->id,
            'verification_status' => RegistrationStatusEnum::Draft,
        ]);

        // 1. Gudep tidak dapat mengunduh saat berstatus Draft (403)
        $draftRes = $this->actingAs($this->scoutGroupUser)->get(route('scout-group.registration-certificate').'?raw=1');
        $draftRes->assertForbidden();

        // 2. Dasbor & Review tidak menampilkan tombol unduh saat belum terverifikasi
        $dashDraft = $this->actingAs($this->scoutGroupUser)->get(route('scout-group.index'));
        $dashDraft->assertOk()->assertDontSee(route('scout-group.registration-certificate', ['download' => 1]));

        // 3. Verifikasi kontingen
        $scoutGroup->update(['verification_status' => RegistrationStatusEnum::Verified]);

        // 4. Gudep sukses mengunduh Bukti Registrasi dalam format PDF
        $verifiedRes = $this->actingAs($this->scoutGroupUser)->get(route('scout-group.registration-certificate').'?raw=1');
        $verifiedRes->assertOk();
        $verifiedRes->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('Bukti_Registrasi_', (string) $verifiedRes->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF', $verifiedRes->getContent());

        // 5. Admin juga dapat mengunduh Bukti Registrasi
        $adminRes = $this->actingAs($this->adminUser)->get(route('admin.scout-groups.registration-certificate', $scoutGroup->id).'?raw=1');
        $adminRes->assertOk();
        $adminRes->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $adminRes->getContent());

        // 6. Tombol tampil di dasbor gudep dan review registration
        $dashRes = $this->actingAs($this->scoutGroupUser)->get(route('scout-group.index'));
        $dashRes->assertOk()->assertSee(route('scout-group.registration-certificate', ['download' => 1]));

        $reviewRes = $this->actingAs($this->scoutGroupUser)->get(route('scout-group.review'));
        $reviewRes->assertOk()->assertSee(route('scout-group.registration-certificate', ['download' => 1]));
    }

    /**
     * Memastikan alur streaming real-time ekspor ZIP kontingen mengembalikan stream SSE
     */
    public function test_admin_can_stream_zip_export(): void
    {
        $scoutGroup = ScoutGroup::factory()->create([
            'verification_status' => RegistrationStatusEnum::Verified,
        ]);

        $streamRes = $this->actingAs($this->adminUser)->get(route('admin.scout-groups.download-zip-stream', $scoutGroup->id));
        $streamRes->assertOk();
        $this->assertStringContainsString('text/event-stream', (string) $streamRes->headers->get('Content-Type'));
    }
}

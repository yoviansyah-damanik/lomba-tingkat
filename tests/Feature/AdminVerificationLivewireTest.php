<?php

namespace Tests\Feature;

use App\Enums\RegistrationStatusEnum;
use App\Enums\RoleEnum;
use App\Livewire\Admin\VerificationManagement;
use App\Models\ScoutGroup;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminVerificationLivewireTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(\Database\Seeders\EventSeeder::class);

        $this->admin = User::factory()->create([
            'is_active' => true,
        ]);
        $this->admin->assignRole(RoleEnum::Admin);
    }

    public function test_verification_livewire_component_renders_correctly(): void
    {
        $groupUser = User::factory()->create();
        $scoutGroup = ScoutGroup::create([
            'user_id' => $groupUser->id,
            'registration_code' => 'LT3-2026-TEST',
            'school_name' => 'SMP Negeri 1 Merdeka Beraksi',
            'group_number' => '01.001 - 01.002',
            'kwarran_name' => 'Cibinong',
            'address' => 'Jl. Pramuka Raya No. 10',
            'contact_person' => 'Kak Pembina Utama',
            'contact_phone' => '081234567890',
            'verification_status' => RegistrationStatusEnum::Submitted,
        ]);

        Livewire::actingAs($this->admin)
            ->test(VerificationManagement::class)
            ->assertStatus(200)
            ->assertSee('SMP Negeri 1 Merdeka Beraksi')
            ->assertSee('LT3-2026-TEST');
    }

    public function test_admin_can_verify_contingent(): void
    {
        $groupUser = User::factory()->create();
        $scoutGroup = ScoutGroup::create([
            'user_id' => $groupUser->id,
            'registration_code' => 'LT3-2026-APPROVE',
            'school_name' => 'SMP Juara Terpadu',
            'group_number' => '02.001 - 02.002',
            'kwarran_name' => 'Citeureup',
            'address' => 'Jl. Pahlawan No. 45',
            'contact_person' => 'Kak Pembina Putri',
            'contact_phone' => '081298765432',
            'verification_status' => RegistrationStatusEnum::Submitted,
        ]);

        \Livewire\Livewire::withoutLazyLoading()->actingAs($this->admin)
            ->test(\App\Livewire\Admin\ScoutGroupVerificationActions::class, ['scoutGroupId' => $scoutGroup->id])
            ->call('verify');

        $this->assertEquals(RegistrationStatusEnum::Verified, $scoutGroup->fresh()->verification_status);
        $this->assertNull($scoutGroup->fresh()->verified_by);
    }

    public function test_admin_can_request_revision_with_notes(): void
    {
        $groupUser = User::factory()->create();
        $scoutGroup = ScoutGroup::create([
            'user_id' => $groupUser->id,
            'registration_code' => 'LT3-2026-REVISE',
            'school_name' => 'SMP Pelita Bangsa',
            'group_number' => '03.001 - 03.002',
            'kwarran_name' => 'Babakan Madang',
            'address' => 'Jl. Raya Sentul No. 1',
            'contact_person' => 'Kak Surya',
            'contact_phone' => '081300001111',
            'verification_status' => RegistrationStatusEnum::Submitted,
        ]);

        \Livewire\Livewire::withoutLazyLoading()->actingAs($this->admin)
            ->test(\App\Livewire\Admin\ScoutGroupVerificationActions::class, ['scoutGroupId' => $scoutGroup->id])
            ->set('revisionNotes', 'Mohon lengkapi NTA seluruh peserta regu putri.')
            ->call('requestRevision')
            ->assertHasNoErrors();

        $this->assertEquals(RegistrationStatusEnum::NeedsRevision, $scoutGroup->fresh()->verification_status);
        $this->assertStringContainsString('lengkapi NTA', $scoutGroup->fresh()->revision_notes);
    }

    public function test_verification_show_page_displays_patrol_participants_and_chaperones_correctly(): void
    {
        $groupUser = User::factory()->create();
        $scoutGroup = ScoutGroup::create([
            'user_id' => $groupUser->id,
            'registration_code' => 'LT3-2026-DETAIL',
            'school_name' => 'SMP Cendrawasih Emas',
            'group_number' => '04.001 - 04.002',
            'kwarran_name' => 'Bogor Timur',
            'address' => 'Jl. Pajajaran No. 88',
            'contact_person' => 'Kak Pembina Utama',
            'contact_phone' => '081234567890',
            'verification_status' => RegistrationStatusEnum::Submitted,
            'mandate_letter_path' => 'mandates/mandate_test.pdf',
            'payment_proof_path' => 'payments/payment_test.jpg',
        ]);

        $patrol = \App\Models\Patrol::create([
            'scout_group_id' => $scoutGroup->id,
            'patrol_name' => 'Rajawali Sakti',
            'gender_category' => \App\Enums\PatrolGenderEnum::Boys,
            'status' => RegistrationStatusEnum::Submitted,
        ]);

        $participant = \App\Models\Participant::create([
            'patrol_id' => $patrol->id,
            'full_name' => 'Bintang Alamsyah',
            'role_in_patrol' => \App\Enums\PatrolRoleEnum::PatrolLeader,
            'scout_id' => '02.08.01.123',
            'gender' => 'male',
            'class_grade' => 'VIII',
            'birth_place' => 'Bogor',
            'birth_date' => '2012-05-10',
            'photo_path' => 'participants/photo_bintang.jpg',
            'doc_kta_path' => 'participants/kta_bintang.pdf',
            'doc_bpjs_path' => 'participants/bpjs_bintang.pdf',
            'doc_health_certificate_path' => 'participants/health_bintang.pdf',
            'doc_parent_consent_path' => 'participants/consent_bintang.pdf',
            'doc_birth_certificate_path' => 'participants/birth_bintang.pdf',
            'doc_family_card_path' => 'participants/kk_bintang.pdf',
            'doc_sku_path' => 'participants/sku_bintang.pdf',
            'doc_skk_path' => 'participants/skk_bintang.pdf',
            'status' => RegistrationStatusEnum::Submitted,
        ]);

        $chaperone = \App\Models\Chaperone::create([
            'scout_group_id' => $scoutGroup->id,
            'full_name' => 'Kak Hendra Gunawan, u.Pd',
            'gender' => 'male',
            'scout_id' => '02.08.00.001',
            'national_identity_number' => '3201010101900001',
            'phone_number' => '081299887766',
            'photo_path' => 'chaperones/photo_hendra.jpg',
            'status' => RegistrationStatusEnum::Submitted,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.scout-groups.show', $scoutGroup->id));
        $response->assertOk()
            ->assertSee('SMP Cendrawasih Emas')
            ->assertSee('Rajawali Sakti')
            ->assertSee('Bintang Alamsyah')
            ->assertSee('Kak Hendra Gunawan, u.Pd')
            // Gudep uploads
            ->assertSee('Berkas Unggahan Gugus Depan')
            ->assertSee('Surat Tugas / Mandat Gugus Depan')
            ->assertSee('Bukti Pembayaran Pendaftaran')
            ->assertSee('mandate_test.pdf')
            ->assertSee('payment_test.jpg')
            // Participant uploads & review modal
            ->assertSee('9/9 Berkas')
            ->assertSee('review-participant-'.$participant->id)
            ->assertSee('Surat Izin Orang Tua')
            ->assertSee('Scan Kartu Keluarga (KK)')
            ->assertSee('Scan SKU Penggalang')
            // Chaperone uploads & review modal
            ->assertSee('review-chaperone-'.$chaperone->id)
            ->assertSee('photo_hendra.jpg')
            // Photo zoom in/out lightbox feature
            ->assertSee('imageZoomScale')
            ->assertSee('Gunakan roda mouse atau tombol kontrol untuk zoom in / out');

        // Test dedicated participant detail page
        $pResponse = $this->actingAs($this->admin)->get(route('admin.participants.show', $participant->id));
        $pResponse->assertOk()
            ->assertSee('Bintang Alamsyah')
            ->assertSee('Berkas & Dokumen Persyaratan Yang Diunggah')
            ->assertSee('Pratinjau File')
            ->assertSee('docModalOpen')
            ->assertSee('Scan Kartu Keluarga (KK)')
            ->assertSee('Scan SKU Penggalang');

        // Test dedicated chaperone detail page
        $cResponse = $this->actingAs($this->admin)->get(route('admin.chaperones.show', $chaperone->id));
        $cResponse->assertOk()
            ->assertSee('Kak Hendra Gunawan, u.Pd')
            ->assertSee('Berkas Unggahan Pembina Pendamping')
            ->assertSee('docModalOpen')
            ->assertSee('Pratinjau File');
    }

    public function test_admin_can_download_all_contingent_files_as_zip(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $groupUser = User::factory()->create();
        $scoutGroup = ScoutGroup::create([
            'user_id' => $groupUser->id,
            'registration_code' => 'LT3-2026-ZIP-TEST',
            'school_name' => 'SMP Negeri Unggulan 1',
            'group_number' => '01.001 - 01.002',
            'kwarran_name' => 'Padangsidimpuan Selatan',
            'address' => 'Jl. Protokol No. 1',
            'contact_person' => 'Kak Pembina',
            'contact_phone' => '08123456789',
            'verification_status' => RegistrationStatusEnum::Submitted,
            'mandate_letter_path' => 'mandates/mandate.pdf',
            'payment_proof_path' => 'payments/payment.jpg',
        ]);

        \Illuminate\Support\Facades\Storage::disk('public')->put('mandates/mandate.pdf', 'dummy-mandate-content');
        \Illuminate\Support\Facades\Storage::disk('public')->put('payments/payment.jpg', 'dummy-payment-content');

        $patrol = \App\Models\Patrol::create([
            'scout_group_id' => $scoutGroup->id,
            'gender_category' => \App\Enums\PatrolGenderEnum::Boys,
            'patrol_name' => 'Garuda Perkasa',
            'status' => RegistrationStatusEnum::Submitted,
        ]);

        $participant = \App\Models\Participant::create([
            'patrol_id' => $patrol->id,
            'full_name' => 'Budi Santoso',
            'birth_place' => 'Padangsidimpuan',
            'birth_date' => '2012-05-10',
            'gender' => 'male',
            'religion' => 'Islam',
            'scout_level' => 'rakit',
            'role_in_patrol' => \App\Enums\PatrolRoleEnum::PatrolLeader,
            'status' => RegistrationStatusEnum::Submitted,
            'photo_path' => 'participants/budi.jpg',
        ]);
        \Illuminate\Support\Facades\Storage::disk('public')->put('participants/budi.jpg', 'dummy-photo-content');

        $chaperone = \App\Models\Chaperone::create([
            'scout_group_id' => $scoutGroup->id,
            'full_name' => 'Ahmad Fauzi',
            'birth_place' => 'Medan',
            'birth_date' => '1985-04-12',
            'gender' => 'male',
            'scout_id' => '02.08.01.001',
            'national_identity_number' => '1234567890123456',
            'phone_number' => '081234567890',
            'photo_path' => 'chaperones/fauzi.jpg',
            'status' => RegistrationStatusEnum::Submitted,
        ]);
        \Illuminate\Support\Facades\Storage::disk('public')->put('chaperones/fauzi.jpg', 'dummy-chaperone-photo');

        // Check show page has the zip button
        $showResponse = $this->actingAs($this->admin)->get(route('admin.scout-groups.show', $scoutGroup->id));
        $showResponse->assertOk()
            ->assertSee('Unduh Semua Berkas (.ZIP)')
            ->assertSee(route('admin.scout-groups.download-zip', $scoutGroup->id));

        // Download ZIP
        $zipResponse = $this->actingAs($this->admin)->get(route('admin.scout-groups.download-zip', $scoutGroup->id));
        $zipResponse->assertOk();
        $zipResponse->assertHeader('Content-Type', 'application/zip');
        $this->assertStringContainsString('Berkas_Kontingen_SMP_Negeri_Unggulan_1_', (string) $zipResponse->headers->get('Content-Disposition'));

        // Inspect zip contents
        $zipFilePath = method_exists($zipResponse->baseResponse, 'getFile')
            ? $zipResponse->baseResponse->getFile()->getPathname()
            : null;

        $zip = new \ZipArchive;
        $this->assertTrue($zip->open($zipFilePath));

        $entryNames = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryNames[] = $zip->getNameIndex($i);
        }
        $zip->close();

        // Verify Bukti Registrasi
        $this->assertTrue(collect($entryNames)->contains(fn ($name) => str_starts_with($name, '00_Bukti_Registrasi_')));
        // Verify Gudep Uploads
        $this->assertContains('01_Gudep/Surat_Tugas_Mandat.pdf', $entryNames);
        $this->assertContains('01_Gudep/Bukti_Pembayaran.jpg', $entryNames);
        // Verify Patrol Form 01
        $this->assertTrue(collect($entryNames)->contains(fn ($name) => str_contains($name, 'Form_01_Rekap_Regu_')));
        // Verify Participant Form 02 and photo
        $this->assertTrue(collect($entryNames)->contains(fn ($name) => str_contains($name, 'Form_02_Biodata')));
        $this->assertTrue(collect($entryNames)->contains(fn ($name) => preg_match('/Pas_Foto_.+\.jpg$/', $name)));
        // Verify Chaperone Form 03 and photo
        $this->assertTrue(collect($entryNames)->contains(fn ($name) => str_contains($name, 'Form_03_Biodata')));

        // Test Form 01 print returns PDF
        $f01Response = $this->actingAs($this->admin)->get(route('admin.scout-groups.patrols.form-01', [$scoutGroup->id, $patrol->id]).'?raw=1');
        $f01Response->assertOk();
        $f01Response->assertHeader('Content-Type', 'application/pdf');

        // Test Form 02 print returns PDF
        $f02Response = $this->actingAs($this->admin)->get(route('admin.scout-groups.participants.print', [$scoutGroup->id, $participant->id]).'?raw=1');
        $f02Response->assertOk();
        $f02Response->assertHeader('Content-Type', 'application/pdf');

        // Test Form 03 print returns PDF
        $f03Response = $this->actingAs($this->admin)->get(route('admin.scout-groups.chaperones.print', [$scoutGroup->id, $chaperone->id]).'?raw=1');
        $f03Response->assertOk();
        $f03Response->assertHeader('Content-Type', 'application/pdf');
    }
}



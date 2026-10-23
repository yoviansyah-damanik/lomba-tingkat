<?php

namespace Tests\Feature;

use App\Enums\PatrolGenderEnum;
use App\Enums\PatrolRoleEnum;
use App\Enums\RoleEnum;
use App\Models\Chaperone;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DigitalCardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $scoutUser;
    protected ScoutGroup $scoutGroup;
    protected Patrol $patrol;
    protected Participant $participant;
    protected Chaperone $chaperone;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->admin = User::factory()->create([
            'is_active' => true,
        ]);
        $this->admin->assignRole(RoleEnum::Admin);

        $this->scoutUser = User::factory()->create([
            'is_active' => true,
        ]);
        $this->scoutUser->assignRole(RoleEnum::ScoutGroup);

        $this->scoutGroup = ScoutGroup::create([
            'user_id' => $this->scoutUser->id,
            'registration_code' => 'REG-SMP1-2026',
            'school_name' => 'SMP Negeri 1 Padangsidimpuan',
            'group_number' => '01.001 - 01.002',
            'male_group_number' => '01.001',
            'female_group_number' => '01.002',
            'kwarran_name' => 'Padangsidimpuan Utara',
            'address' => 'Jl. Merdeka No. 1 Padangsidimpuan',
            'contact_person' => 'Kak Budi Santoso',
            'contact_phone' => '081234567890',
            'verification_status' => 'verified',
        ]);

        $this->patrol = Patrol::create([
            'scout_group_id' => $this->scoutGroup->id,
            'patrol_name' => 'Rajawali',
            'gender_category' => PatrolGenderEnum::Boys,
            'group_number' => '01.001',
        ]);

        $this->participant = Participant::create([
            'patrol_id' => $this->patrol->id,
            'full_name' => 'Rizky Ramadhan',
            'gender' => 'male',
            'birth_place' => 'Padangsidimpuan',
            'birth_date' => '2012-05-10',
            'scout_id' => '02.17.001.045',
            'blood_type' => 'O',
        ]);

        $this->chaperone = Chaperone::create([
            'scout_group_id' => $this->scoutGroup->id,
            'full_name' => 'Kak Budi Santoso, S.Pd',
            'gender' => 'male',
            'phone_number' => '081234567890',
            'scout_id' => '02.17.001.001',
        ]);
    }

    /**
     * Memastikan ID card digital individual peserta dapat diakses dan memuat kode QR
     */
    public function test_participant_digital_id_card_can_be_viewed(): void
    {
        $response = $this->actingAs($this->scoutUser)
            ->get(route('digital-cards.participant', $this->participant->id));

        $response->assertOk();
        $response->assertSee('Rizky Ramadhan');
        $response->assertSee('Rajawali');
        $response->assertSee('Nomor Gudep');
        $response->assertSee('Pangkalan');
        $response->assertSee('Kwartir Ranting');
        $response->assertSee('Padangsidimpuan Utara');
        $response->assertSee('NTA:');
        $response->assertDontSee('Darah');
        $response->assertDontSee('SKU');
        $response->assertDontSee('KTA');
        $response->assertDontSee('Anggota Regu');
        $response->assertSee('photo-member');
        $response->assertSee('data:image/svg+xml;base64,', false);
    }

    /**
     * Memastikan kartu digital Pinru dan Wapinru menampilkan border dan pita vertikal khusus
     */
    public function test_pinru_and_wapinru_render_special_borders_and_vertical_text(): void
    {
        $this->participant->update(['role_in_patrol' => PatrolRoleEnum::PatrolLeader]);

        $response = $this->actingAs($this->scoutUser)
            ->get(route('digital-cards.participant', $this->participant->id));

        $response->assertOk();
        $response->assertSee('photo-pinru');
        $response->assertSee('badge-pinru');
        $response->assertSee('PINRU');
        $response->assertDontSee('Anggota Regu');

        $this->participant->update(['role_in_patrol' => PatrolRoleEnum::AssistantPatrolLeader]);

        $response = $this->actingAs($this->scoutUser)
            ->get(route('digital-cards.participant', $this->participant->id));

        $response->assertOk();
        $response->assertSee('photo-wapinru');
        $response->assertSee('badge-wapinru');
        $response->assertSee('WAPINRU');
        $response->assertDontSee('Anggota Regu');
    }

    /**
     * Memastikan ID card digital pembina pendamping dapat diakses dan memuat kode QR
     */
    public function test_chaperone_digital_id_card_can_be_viewed(): void
    {
        $response = $this->actingAs($this->scoutUser)
            ->get(route('digital-cards.chaperone', $this->chaperone->id));

        $response->assertOk();
        $response->assertSee('Kak Budi Santoso');
        $response->assertSee('SMP Negeri 1 Padangsidimpuan');
        $response->assertSee('01.001');
        $response->assertSee('Kwartir Ranting');
        $response->assertSee('Padangsidimpuan Utara');
        $response->assertDontSee('KTA');
        $response->assertSee('data:image/svg+xml;base64,', false);

        // Uji kartu pembina putri
        $femaleChaperone = Chaperone::create([
            'scout_group_id' => $this->scoutGroup->id,
            'full_name' => 'Kak Siti Rahma, S.Pd',
            'gender' => 'female',
            'phone_number' => '081234567899',
            'scout_id' => '02.17.001.002',
        ]);

        $femaleResponse = $this->actingAs($this->scoutUser)
            ->get(route('digital-cards.chaperone', $femaleChaperone->id));

        $femaleResponse->assertOk();
        $femaleResponse->assertSee('Kak Siti Rahma');
        $femaleResponse->assertSee('01.002');
    }

    /**
     * Memastikan ID card batch A4 regu dapat dirender dengan opsi ekspor .PNG
     */
    public function test_patrol_batch_cards_can_be_viewed(): void
    {
        $response = $this->actingAs($this->scoutUser)
            ->get(route('digital-cards.patrol.batch', $this->patrol->id));

        $response->assertOk();
        $response->assertSee('Rajawali');
        $response->assertSee('Rizky Ramadhan');
        $response->assertSee('Nomor Gudep');
        $response->assertSee('Pangkalan');
        $response->assertSee('Kwartir Ranting');
        $response->assertSee('Padangsidimpuan Utara');
        $response->assertSee('NTA:');
        $response->assertDontSee('Darah');
        $response->assertSee('ID Card Regu Rajawali');
        $response->assertSee('Simpan Semua (.ZIP)');
        $response->assertSee('Simpan .PNG');
    }

    /**
     * Memastikan ID card batch A4 pembina pendamping dapat dirender dengan opsi ekspor .PNG
     */
    public function test_chaperone_batch_cards_can_be_viewed(): void
    {
        $response = $this->actingAs($this->scoutUser)
            ->get(route('digital-cards.chaperone.batch', $this->scoutGroup->id));

        $response->assertOk();
        $response->assertSee('Kak Budi Santoso');
        $response->assertSee('ID Card Pembina Pendamping');
        $response->assertSee('Simpan Semua (.ZIP)');
        $response->assertSee('Simpan .PNG');
    }

    /**
     * Memastikan seluruh kartu kontingen (kumulatif regu & bindamping) dapat diakses Gugusdepan dan Administrator
     */
    public function test_scout_group_batch_cards_can_be_viewed_cumulatively(): void
    {
        // Akses oleh Gugusdepan pemilik kontingen
        $gudepResponse = $this->actingAs($this->scoutUser)
            ->get(route('digital-cards.scout-group.batch', $this->scoutGroup->id));

        $gudepResponse->assertOk()
            ->assertSee('ID Card Kontingen')
            ->assertSee('Rizky Ramadhan')
            ->assertSee('Kak Budi Santoso')
            ->assertSee('Simpan Semua (.ZIP)');

        // Akses oleh Administrator
        $adminResponse = $this->actingAs($this->admin)
            ->get(route('admin.scout-groups.id-cards', $this->scoutGroup->id));

        $adminResponse->assertOk()
            ->assertSee('Rizky Ramadhan')
            ->assertSee('Kak Budi Santoso')
            ->assertSee('Simpan Semua (.ZIP)');

        // Pengguna pangkalan lain dilarang mengakses
        $otherUser = User::factory()->create();
        $otherUser->assignRole(\App\Enums\RoleEnum::ScoutGroup);

        $this->actingAs($otherUser)
            ->get(route('digital-cards.scout-group.batch', $this->scoutGroup->id))
            ->assertForbidden();
    }

    /**
     * Memastikan kartu individual memuat tombol simpan .PNG
     */
    public function test_individual_digital_cards_contain_png_save_button(): void
    {
        $response = $this->actingAs($this->scoutUser)
            ->get(route('digital-cards.participant', $this->participant->id));

        $response->assertOk()
            ->assertSee('Simpan .PNG');

        $chaperoneResponse = $this->actingAs($this->scoutUser)
            ->get(route('digital-cards.chaperone', $this->chaperone->id));

        $chaperoneResponse->assertOk()
            ->assertSee('Simpan .PNG');
    }

    /**
     * Memastikan endpoint publik verifikasi QR code bekerja saat discan
     */
    public function test_public_qr_verification_resolves_valid_token(): void
    {
        $response = $this->get(route('digital-cards.verify', $this->participant->qr_token));

        $response->assertOk();
        $response->assertSee('KARTU TANDA PESERTA');
        $response->assertSee('Rizky Ramadhan');
        $response->assertSee('SMP Negeri 1 Padangsidimpuan');
        $response->assertSee('Terakreditasi & Terverifikasi');
    }

    /**
     * Memastikan endpoint publik verifikasi QR menampilkan alert jika token tidak dikenal
     */
    public function test_public_qr_verification_handles_invalid_token(): void
    {
        $response = $this->get(route('digital-cards.verify', 'invalid-uuid-token-xyz'));

        $response->assertOk();
        $response->assertSee('QR Code Tidak Terdaftar');
        $response->assertSee('invalid-uuid-token-xyz');
    }

    /**
     * Memastikan administrator dapat mengelola aparatur panitia & juri
     */
    public function test_admin_can_manage_officials(): void
    {
        // 1. Tampilkan index
        $indexResponse = $this->actingAs($this->admin)->get(route('admin.officials.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Aparatur Panitia & Dewan Juri');

        // 2. Tampilkan form create
        $createResponse = $this->actingAs($this->admin)->get(route('admin.officials.create'));
        $createResponse->assertOk();

        // 3. Simpan official baru (username & password otomatis digenerate mengikuti ID resmi)
        $storeResponse = $this->actingAs($this->admin)->post(route('admin.officials.store'), [
            'name' => 'Kak Hasan Basri, S.Pd',
            'phone_number' => '085211223344',
            'scout_id' => '02.17.009.99',
            'institution' => 'Kwarcab Padangsidimpuan',
            'position' => 'Koordinator Pos Sandi & Isyarat',
            'roles' => ['committee', 'judge'],
        ]);

        $storeResponse->assertRedirect(route('admin.officials.index'));
        $this->assertDatabaseHas('users', [
            'name' => 'Kak Hasan Basri, S.Pd',
            'is_active' => true,
        ]);

        $newOfficial = User::where('name', 'Kak Hasan Basri, S.Pd')->first();
        $this->assertNotNull($newOfficial);
        $this->assertMatchesRegularExpression('/^A-\d{6}$/', $newOfficial->username);
        $this->assertMatchesRegularExpression('/^A-\d{6}$/', $newOfficial->id_number);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check($newOfficial->id_number, $newOfficial->password));
        $this->assertTrue($newOfficial->must_change_password);
        $this->assertNotNull($newOfficial->qr_token);
        $this->assertTrue($newOfficial->isCommittee());
        $this->assertTrue($newOfficial->isJudge());

        // 4. Akses ID card staf aparatur
        $staffCardResponse = $this->actingAs($this->admin)->get(route('digital-cards.staff', $newOfficial->id));
        $staffCardResponse->assertOk();
        $staffCardResponse->assertSee('Kak Hasan Basri, S.Pd');
        $staffCardResponse->assertSee('PANITIA PELAKSANA & DEWAN JURI', false);
        $staffCardResponse->assertDontSee('Akreditasi');
        $staffCardResponse->assertDontSee('Terverifikasi Aktif');
    }
}

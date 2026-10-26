<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Support\Navigation;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationMenuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    /**
     * @param  array<int, RoleEnum>  $roles
     */
    private function userWith(array $roles): User
    {
        $user = User::factory()->create();

        foreach ($roles as $role) {
            $user->assignRole($role);
        }

        return $user->fresh();
    }

    /**
     * @return array<string, array<int, string>> heading => daftar label item (berurutan)
     */
    private function menu(User $user): array
    {
        $menu = [];

        foreach (Navigation::groupsFor($user) as $group) {
            $menu[$group['heading']] = array_column($group['items'], 'label');
        }

        return $menu;
    }

    public function test_administrator_menu_is_grouped_by_function_in_workflow_order(): void
    {
        $this->assertSame([
            'Utama' => ['Dashboard', 'Jadwal Kegiatan'],
            'Pendaftaran & Verifikasi' => ['Kode Undangan Registrasi', 'Verifikasi Pendaftaran'],
            'Data Kontingen' => ['Pangkalan Gugusdepan', 'Direktori Peserta', 'Pembina Pendamping'],
            'Lomba & Jadwal' => ['Mata Lomba & Rubrik', 'Generate NPP', 'Jadwal & Penugasan Juri'],
            'Presensi & Verifikasi' => ['Portal Presensi', 'Verifikasi Identitas (Scan QR)', 'Permohonan Izin', 'Rekapitulasi Presensi'],
            'Penilaian & Klasemen' => ['Cockpit Penjurian', 'Rekap Nilai & Klasemen'],
            'Informasi & Laporan' => ['Pengumuman Kegiatan', 'Pusat Laporan & Ekspor'],
            'Pengguna & Akses' => ['Aparatur Panitia & Juri'],
        ], $this->menu($this->userWith([RoleEnum::Admin])));
    }

    public function test_superadmin_also_gets_user_management_and_system_groups(): void
    {
        $menu = $this->menu($this->userWith([RoleEnum::Superadmin]));

        $this->assertSame(['Aparatur Panitia & Juri', 'Pengguna & Hak Akses'], $menu['Pengguna & Akses']);
        $this->assertSame(['Konfigurasi Sistem', 'Siaran Notifikasi (PWA)', 'Audit Penilaian & Log'], $menu['Sistem & Audit']);
        $this->assertSame('Sistem & Audit', array_key_last($menu));
    }

    public function test_committee_sees_only_field_operations_menus(): void
    {
        $this->assertSame([
            'Utama' => ['Dashboard', 'Jadwal Kegiatan'],
            'Kegiatan Saya' => ['Presensi Saya', 'Kartu Digital'],
            'Data Kontingen' => ['Pangkalan Gugusdepan', 'Direktori Peserta', 'Pembina Pendamping'],
            'Presensi & Verifikasi' => ['Portal Presensi', 'Verifikasi Identitas (Scan QR)', 'Rekapitulasi Presensi'],
            'Penilaian & Klasemen' => ['Rekap Nilai & Klasemen'],
        ], $this->menu($this->userWith([RoleEnum::Committee])));
    }

    public function test_judge_menu_has_scoring_and_limited_data_access(): void
    {
        $judge = $this->userWith([RoleEnum::Judge]);

        $this->assertSame([
            'Utama' => ['Dashboard', 'Jadwal Kegiatan'],
            'Kegiatan Saya' => ['Presensi Saya', 'Kartu Digital'],
            'Data Kontingen' => ['Direktori Peserta'],
            'Presensi & Verifikasi' => ['Portal Presensi', 'Rekapitulasi Presensi'],
            'Penilaian & Klasemen' => ['Cockpit Penjurian', 'Rekap Nilai & Klasemen'],
        ], $this->menu($judge));

        // Juri langsung diarahkan ke tab klasemen per lomba
        $standings = collect(Navigation::groupsFor($judge))->pluck('items')->flatten(1)->firstWhere('label', 'Rekap Nilai & Klasemen');
        $this->assertStringContainsString('tab=competitions', $standings['href']);
    }

    public function test_dual_role_committee_and_judge_never_sees_duplicate_items(): void
    {
        $user = $this->userWith([RoleEnum::Committee, RoleEnum::Judge]);

        $labels = collect(Navigation::groupsFor($user))->pluck('items')->flatten(1)->pluck('label');

        $this->assertSame($labels->unique()->values()->all(), $labels->values()->all());
        $this->assertContains('Cockpit Penjurian', $labels->all());
        $this->assertContains('Pangkalan Gugusdepan', $labels->all());

        $standings = collect(Navigation::groupsFor($user))->pluck('items')->flatten(1)->firstWhere('label', 'Rekap Nilai & Klasemen');
        $this->assertStringNotContainsString('tab=', $standings['href']);
    }

    public function test_scout_group_participant_and_chaperone_menus(): void
    {
        $this->assertSame([
            'Utama' => ['Dashboard', 'Jadwal Kegiatan'],
            'Kontingen Saya' => ['Regu & Peserta', 'Bindamping', 'Periksa & Ajukan'],
            'Penilaian & Klasemen' => ['Penilaian Regu', 'Rekap Nilai & Klasemen'],
        ], $this->menu($this->userWith([RoleEnum::ScoutGroup])));

        foreach ([RoleEnum::Participant, RoleEnum::Chaperone] as $role) {
            $this->assertSame([
                'Utama' => ['Dashboard', 'Jadwal Kegiatan'],
                'Kegiatan Saya' => ['Presensi Saya', 'Kartu Digital'],
                'Penilaian & Klasemen' => ['Penilaian Regu', 'Rekap Nilai & Klasemen'],
            ], $this->menu($this->userWith([$role])), $role->value);
        }
    }

    public function test_sidebar_renders_group_headings_and_dashboard_shows_shortcuts_without_main_group(): void
    {
        $admin = $this->userWith([RoleEnum::Admin]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeInOrder(['Pendaftaran & Verifikasi', 'Data Kontingen', 'Lomba & Jadwal', 'Presensi & Verifikasi', 'Penilaian & Klasemen', 'Informasi & Laporan'])
            ->assertSee('Verifikasi Pendaftaran')
            ->assertSee('Kode undangan gugus depan yang boleh mendaftar');
    }
}

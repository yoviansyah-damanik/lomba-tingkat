<?php

namespace App\Support;

use App\Models\User;
use Closure;

/**
 * Definisi tunggal menu aplikasi: dikelompokkan menurut fungsi dan diurutkan menurut alur kerja.
 * Dipakai oleh sidebar dan kartu pintasan dasbor, sehingga tiap menu hanya didefinisikan sekali.
 */
class Navigation
{
    /** Grup yang tidak ditampilkan sebagai kartu pintasan dasbor (sudah ada di sidebar utama) */
    public const MAIN_GROUP = 'Utama';

    /**
     * Grup dan item menu yang boleh dilihat pengguna, tanpa duplikat.
     *
     * @return array<int, array{heading: string, items: array<int, array{label: string, icon: string, href: string, active: array<int, string>, description: string}>}>
     */
    public static function groupsFor(User $user): array
    {
        $groups = [];

        foreach (self::definition() as $group) {
            $items = [];

            foreach ($group['items'] as $item) {
                if (! self::visibleTo($user, $item['roles'])) {
                    continue;
                }

                $route = $item['route'] instanceof Closure ? ($item['route'])($user) : $item['route'];
                $params = ($item['params'] ?? null) instanceof Closure ? ($item['params'])($user) : ($item['params'] ?? []);

                $items[] = [
                    'label' => $item['label'],
                    'icon' => $item['icon'],
                    'href' => route($route, $params),
                    'active' => $item['active'],
                    'description' => $item['description'],
                ];
            }

            if ($items) {
                $groups[] = ['heading' => $group['heading'], 'items' => $items];
            }
        }

        return $groups;
    }

    /**
     * Peran yang diperbolehkan: "*" untuk semua pengguna login
     *
     * @param  array<int, string>  $roles
     */
    private static function visibleTo(User $user, array $roles): bool
    {
        foreach ($roles as $role) {
            $allowed = match ($role) {
                '*' => true,
                'admin' => $user->isAdmin(),
                'superadmin' => $user->isSuperadmin(),
                'committee' => $user->isCommittee(),
                'judge' => $user->isJudge(),
                'scout_group' => $user->isScoutGroup(),
                'participant' => $user->isParticipant(),
                'chaperone' => $user->isChaperone(),
                default => false,
            };

            if ($allowed) {
                return true;
            }
        }

        return false;
    }

    /**
     * Susunan grup (atas ke bawah) dan item (menurut alur kerja)
     *
     * @return array<int, array{heading: string, items: array<int, array<string, mixed>>}>
     */
    private static function definition(): array
    {
        $personal = ['participant', 'chaperone', 'committee', 'judge'];

        return [
            [
                'heading' => self::MAIN_GROUP,
                'items' => [
                    ['label' => 'Dashboard', 'icon' => 'home', 'route' => 'dashboard', 'active' => ['dashboard'], 'roles' => ['*'], 'description' => 'Ringkasan kegiatan Anda'],
                    ['label' => 'Jadwal Kegiatan', 'icon' => 'calendar-days', 'route' => 'agenda.index', 'active' => ['agenda.*'], 'roles' => ['*'], 'description' => 'Agenda sesi lomba dan kegiatan'],
                ],
            ],
            [
                'heading' => 'Kegiatan Saya',
                'items' => [
                    ['label' => 'Presensi Saya', 'icon' => 'clipboard-document-check', 'route' => 'my-attendance.index', 'active' => ['my-attendance.*'], 'roles' => $personal, 'description' => 'Jadwal yang Anda ikuti dan status kehadiran'],
                    ['label' => 'Kartu Digital', 'icon' => 'qr-code', 'route' => 'digital-card.index', 'active' => ['digital-card.*'], 'roles' => $personal, 'description' => 'Kartu identitas dengan QR Code'],
                ],
            ],
            [
                'heading' => 'Kontingen Saya',
                'items' => [
                    ['label' => 'Regu & Peserta', 'icon' => 'user-group', 'route' => 'scout-group.index', 'active' => ['scout-group.index', 'scout-group.patrols*', 'scout-group.participants*'], 'roles' => ['scout_group'], 'description' => 'Bentuk regu dan isi biodata peserta'],
                    ['label' => 'Bindamping', 'icon' => 'identification', 'route' => 'scout-group.chaperones', 'active' => ['scout-group.chaperones*'], 'roles' => ['scout_group'], 'description' => 'Daftarkan pembina pendamping putra/putri'],
                    ['label' => 'Periksa & Ajukan', 'icon' => 'paper-airplane', 'route' => 'scout-group.review', 'active' => ['scout-group.review'], 'roles' => ['scout_group'], 'description' => 'Periksa kelengkapan lalu ajukan pendaftaran'],
                ],
            ],
            [
                'heading' => 'Pendaftaran & Verifikasi',
                'items' => [
                    ['label' => 'Kode Undangan Registrasi', 'icon' => 'key', 'route' => 'admin.registration-invitations.index', 'active' => ['admin.registration-invitations.*'], 'roles' => ['admin'], 'description' => 'Kode undangan gugus depan yang boleh mendaftar'],
                    ['label' => 'Verifikasi Pendaftaran', 'icon' => 'shield-check', 'route' => 'admin.scout-groups.index', 'active' => ['admin.scout-groups.*'], 'roles' => ['admin'], 'description' => 'Antrean pendaftaran kontingen'],
                ],
            ],
            [
                'heading' => 'Data Kontingen',
                'items' => [
                    ['label' => 'Pangkalan Gugusdepan', 'icon' => 'building-library', 'route' => 'admin.gudep-directory', 'active' => ['admin.gudep-directory'], 'roles' => ['admin', 'committee'], 'description' => 'Direktori pangkalan peserta'],
                    ['label' => 'Direktori Peserta', 'icon' => 'academic-cap', 'route' => 'admin.participants.index', 'active' => ['admin.participants.*'], 'roles' => ['admin', 'committee', 'judge'], 'description' => 'Data seluruh peserta'],
                    ['label' => 'Pembina Pendamping', 'icon' => 'user-circle', 'route' => 'admin.chaperones.index', 'active' => ['admin.chaperones.*'], 'roles' => ['admin', 'committee'], 'description' => 'Data pembina pendamping'],
                ],
            ],
            [
                'heading' => 'Lomba & Jadwal',
                'items' => [
                    ['label' => 'Mata Lomba & Rubrik', 'icon' => 'trophy', 'route' => 'admin.competitions.index', 'active' => ['admin.competitions.*'], 'roles' => ['admin'], 'description' => 'Format lomba dan kriteria penilaian'],
                    ['label' => 'Jadwal & Penugasan Juri', 'icon' => 'calendar', 'route' => 'admin.schedules.index', 'active' => ['admin.schedules.*'], 'roles' => ['admin'], 'description' => 'Jadwal sesi lomba dan juri bertugas'],
                ],
            ],
            [
                'heading' => 'Presensi & Verifikasi',
                'items' => [
                    ['label' => 'Portal Presensi', 'icon' => 'qr-code', 'route' => 'attendance.index', 'active' => ['attendance.index', 'attendance.scanner'], 'roles' => ['admin', 'committee', 'judge'], 'description' => 'Pindai kehadiran di pos dan kegiatan'],
                    ['label' => 'Verifikasi Identitas (Scan QR)', 'icon' => 'viewfinder-circle', 'route' => 'admin.identity-verification.index', 'active' => ['admin.identity-verification.*'], 'roles' => ['admin', 'committee'], 'description' => 'Pindai QR kartu peserta, bindamping, panitia, dan juri'],
                    ['label' => 'Permohonan Izin', 'icon' => 'clipboard-document-check', 'route' => 'admin.permission-requests.index', 'active' => ['admin.permission-requests.*'], 'roles' => ['admin'], 'description' => 'Izin meninggalkan perkemahan dan kunjungan tamu'],
                    ['label' => 'Rekapitulasi Presensi', 'icon' => 'clipboard-document-list', 'route' => 'attendance.recap', 'active' => ['attendance.recap*'], 'roles' => ['admin', 'committee', 'judge'], 'description' => 'Rekap kehadiran per sesi'],
                ],
            ],
            [
                'heading' => 'Penilaian & Klasemen',
                'items' => [
                    ['label' => 'Cockpit Penjurian', 'icon' => 'pencil-square', 'route' => 'judge.index', 'active' => ['judge.*'], 'roles' => ['admin', 'judge'], 'description' => 'Input penilaian lomba'],
                    ['label' => 'Penilaian Regu', 'icon' => 'trophy', 'route' => 'team-assessment.index', 'active' => ['team-assessment.*'], 'roles' => ['scout_group', 'participant', 'chaperone'], 'description' => 'Nilai dan predikat regu Anda'],
                    [
                        'label' => 'Rekap Nilai & Klasemen',
                        'icon' => 'chart-bar',
                        'route' => fn (User $user) => $user->isAdmin() ? 'admin.standings.index' : 'standings',
                        'params' => fn (User $user) => (! $user->isAdmin() && $user->isJudge() && ! $user->isCommittee()) ? ['tab' => 'competitions'] : [],
                        'active' => ['standings', 'admin.standings.*'],
                        'roles' => ['admin', 'committee', 'judge', 'scout_group', 'participant', 'chaperone'],
                        'description' => 'Peringkat umum menurut jumlah medali',
                    ],
                ],
            ],
            [
                'heading' => 'Informasi & Laporan',
                'items' => [
                    ['label' => 'Pengumuman Kegiatan', 'icon' => 'megaphone', 'route' => 'admin.announcements.index', 'active' => ['admin.announcements.*'], 'roles' => ['admin'], 'description' => 'Terbitkan pengumuman resmi'],
                    ['label' => 'Pusat Laporan & Ekspor', 'icon' => 'document-text', 'route' => 'admin.reports.index', 'active' => ['admin.reports.*'], 'roles' => ['admin'], 'description' => 'Laporan dan ekspor data'],
                ],
            ],
            [
                'heading' => 'Pengguna & Akses',
                'items' => [
                    ['label' => 'Aparatur Panitia & Juri', 'icon' => 'user-group', 'route' => 'admin.officials.index', 'active' => ['admin.officials.*'], 'roles' => ['admin'], 'description' => 'Kelola aparatur dan kartu B2'],
                    ['label' => 'Pengguna & Hak Akses', 'icon' => 'users', 'route' => 'admin.users.index', 'active' => ['admin.users.*'], 'roles' => ['superadmin'], 'description' => 'Akun pengguna dan peran'],
                ],
            ],
            [
                'heading' => 'Sistem & Audit',
                'items' => [
                    ['label' => 'Konfigurasi Sistem', 'icon' => 'cog-6-tooth', 'route' => 'admin.settings.system', 'active' => ['admin.settings.system'], 'roles' => ['superadmin'], 'description' => 'Master kegiatan, biaya, dan pendaftaran'],
                    ['label' => 'Siaran Notifikasi (PWA)', 'icon' => 'bell', 'route' => 'admin.push-broadcast.index', 'active' => ['admin.push-broadcast.*'], 'roles' => ['superadmin'], 'description' => 'Kirim notifikasi ke pengguna'],
                    ['label' => 'Audit Penilaian & Log', 'icon' => 'clipboard-document-check', 'route' => 'admin.assessments.index', 'active' => ['admin.assessments.*'], 'roles' => ['superadmin'], 'description' => 'Jejak rekam penilaian juri'],
                ],
            ],
        ];
    }
}

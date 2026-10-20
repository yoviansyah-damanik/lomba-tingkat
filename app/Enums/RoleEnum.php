<?php

namespace App\Enums;

/**
 * Enum untuk mendefinisikan peran pengguna dalam sistem LT III
 */
enum RoleEnum: string
{
    case Superadmin = 'superadmin';
    case Admin = 'admin';
    case Committee = 'committee';
    case Judge = 'judge';
    case ScoutGroup = 'scout_group';
    case Participant = 'participant';
    case Chaperone = 'chaperone';

    /**
     * Mendapatkan label bahasa manusia untuk peran
     */
    public function label(): string
    {
        return match ($this) {
            self::Superadmin => 'Superadmin',
            self::Admin => 'Administrator',
            self::Committee => 'Panitia',
            self::Judge => 'Juri',
            self::ScoutGroup => 'Gugus Depan',
            self::Participant => 'Peserta',
            self::Chaperone => 'Bindamping',
        };
    }

    /**
     * Mendapatkan deskripsi singkat wewenang peran
     */
    public function description(): string
    {
        return match ($this) {
            self::Superadmin => 'Pemilik sistem: seluruh akses Administrator ditambah aparatur, pengguna & peran, konfigurasi sistem, dan audit penilaian',
            self::Admin => 'Verifikasi pendaftaran, peserta, bindamping, mata lomba, jadwal & penugasan, scan & rekap presensi, rekap nilai, laporan & ekspor, serta pengumuman',
            self::Committee => 'Pemantau operasional kegiatan di lapangan',
            self::Judge => 'Penilai mata lomba dan presensi pos yang ditugaskan',
            self::ScoutGroup => 'Penanggung jawab kontingen sekolah/pangkalan (regu, peserta, bindamping)',
            self::Participant => 'Anggota regu peserta lomba tingkat III',
            self::Chaperone => 'Pembina pendamping regu: memantau jadwal, nilai, dan pencapaian regu yang didampingi',
        };
    }
}

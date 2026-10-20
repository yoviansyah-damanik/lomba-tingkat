<?php

namespace App\Enums;

/**
 * Enum untuk mendefinisikan peran pengguna dalam sistem LT III
 */
enum RoleEnum: string
{
    case Admin = 'admin';
    case Committee = 'committee';
    case Judge = 'judge';
    case ScoutGroup = 'scout_group';
    case Participant = 'participant';

    /**
     * Mendapatkan label bahasa manusia untuk peran
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Committee => 'Panitia',
            self::Judge => 'Juri',
            self::ScoutGroup => 'Gugus Depan',
            self::Participant => 'Peserta',
        };
    }

    /**
     * Mendapatkan deskripsi singkat wewenang peran
     */
    public function description(): string
    {
        return match ($this) {
            self::Admin => 'Pengelola sistem, verifikator pendaftaran, dan pelaksana scan absensi pos kegiatan',
            self::Committee => 'Pemantau operasional kegiatan di lapangan',
            self::Judge => 'Penilai mata lomba dan presensi pos yang ditugaskan',
            self::ScoutGroup => 'Penanggung jawab kontingen sekolah/pangkalan (regu, peserta, bindamping)',
            self::Participant => 'Anggota regu peserta lomba tingkat III',
        };
    }
}

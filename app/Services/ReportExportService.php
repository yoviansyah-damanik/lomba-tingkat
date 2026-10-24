<?php

namespace App\Services;

use App\Enums\RegistrationStatusEnum;
use App\Models\Attendance;
use App\Models\CompetitionScore;
use App\Models\Participant;
use App\Models\ScoutGroup;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Service untuk penyusunan dan ekspor laporan resmi (CSV/Excel & Berkas Rekapitulasi)
 */
class ReportExportService
{
    public function __construct(
        protected StandingsService $standingsService
    ) {}

    /**
     * Menghasilkan StreamedResponse CSV dengan UTF-8 BOM untuk kompatibilitas Microsoft Excel
     */
    protected function streamCsv(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');

            // Tambahkan UTF-8 Byte Order Mark (BOM) untuk Excel
            fwrite($handle, "\xEF\xBB\xBF");

            // Tulis header CSV
            fputcsv($handle, $headers);

            // Tulis data baris
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    /**
     * Ekspor Data Kontingen & Gugus Depan Terdaftar
     */
    public function exportContingentsCsv(): StreamedResponse
    {
        $headers = [
            'No',
            'Nama Pangkalan / Sekolah',
            'Nomor Gudep',
            'Kwarran',
            'Alamat Pangkalan',
            'Nama Kamabigus',
            'No HP Kontak',
            'Username Akun',
            'Jumlah Regu',
            'Jumlah Peserta',
            'Jumlah Bindamping',
            'Status Verifikasi',
            'Tanggal Registrasi',
        ];

        $groups = ScoutGroup::with(['user', 'patrols', 'participants', 'chaperones'])
            ->latest()
            ->get();

        $rows = [];
        $no = 1;
        foreach ($groups as $group) {
            $rows[] = [
                $no++,
                $group->school_name,
                $group->group_number,
                $group->kwarran_name,
                $group->school_address ?? '-',
                $group->kamabigus_name ?? '-',
                $group->phone_number ?? $group->user?->phone_number ?? '-',
                $group->user?->username ?? '-',
                $group->patrols->count(),
                $group->participants->count(),
                $group->chaperones->count(),
                $group->verification_status instanceof RegistrationStatusEnum ? $group->verification_status->label() : ucfirst($group->verification_status),
                $group->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i') ?? '-',
            ];
        }

        $filename = 'rekap-kontingen-gudep-lt3-' . date('Ymd-His') . '.csv';

        return $this->streamCsv($filename, $headers, $rows);
    }

    /**
     * Ekspor Buku Induk Peserta Terverifikasi
     */
    public function exportParticipantsCsv(): StreamedResponse
    {
        $headers = [
            'No',
            'ID Peserta',
            'Nama Lengkap',
            'Jenis Kelamin',
            'Pangkalan / Gugus Depan',
            'Nomor Gudep',
            'Kwartir Ranting',
            'Nama Regu',
            'Posisi di Regu',
            'Nomor Tanda Anggota (NTA)',
            'NIK',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Golongan Darah',
            'Agama',
            'Tingkatan Pramuka',
            'Status Verifikasi',
        ];

        $participants = Participant::with(['scoutGroup', 'patrol'])
            ->orderBy('scout_group_id')
            ->orderBy('patrol_id')
            ->get();

        $rows = [];
        $no = 1;
        foreach ($participants as $p) {
            $gender = $p->gender === 'male' || $p->gender === 'L' ? 'Putra' : 'Putri';
            $roleLabel = match ($p->role_in_patrol) {
                'patrol_leader' => 'Pimpinan Regu (Pinru)',
                'assistant_patrol_leader' => 'Wakil Pimpinan (Wapinru)',
                default => 'Anggota Regu',
            };

            $rows[] = [
                $no++,
                $p->id_number ?? $p->id,
                $p->name,
                $gender,
                $p->scoutGroup?->school_name ?? '-',
                $p->scoutGroup?->group_number ?? '-',
                $p->scoutGroup?->kwarran_name ?? '-',
                $p->patrol?->patrol_name ?? '-',
                $roleLabel,
                $p->scout_id ?? '-',
                $p->nik ?? '-',
                $p->birth_place ?? '-',
                $p->birth_date?->format('d/m/Y') ?? '-',
                $p->blood_type ?? '-',
                $p->religion ?? '-',
                $p->scout_level ?? 'Penggalang Ramu',
                $p->verification_status instanceof RegistrationStatusEnum ? $p->verification_status->label() : ucfirst($p->verification_status ?? 'verified'),
            ];
        }

        $filename = 'buku-induk-peserta-lt3-' . date('Ymd-His') . '.csv';

        return $this->streamCsv($filename, $headers, $rows);
    }

    /**
     * Ekspor Rekapitulasi Presensi & Kehadiran Pos
     */
    public function exportAttendanceCsv(): StreamedResponse
    {
        $headers = [
            'No',
            'Waktu Pindai (WIB)',
            'Jenis Entitas',
            'Nama',
            'Asal Pangkalan / Gugus Depan',
            'Regu / Jabatan',
            'Sesi Presensi',
            'Pos / Lokasi Kegiatan',
            'Nama Petugas Scanner',
            'Status Presensi',
            'Catatan Lapangan',
        ];

        $attendances = Attendance::with(['session.schedule.competition', 'scanner'])
            ->latest('scanned_at')
            ->get();

        $rows = [];
        $no = 1;
        foreach ($attendances as $a) {
            $session = $a->session;
            $schedule = $session?->schedule;
            $competition = $schedule?->competition;

            $entityType = match ($a->attendee_type) {
                'participant' => 'Peserta',
                'chaperone' => 'Bindamping',
                'committee' => 'Panitia',
                'judge' => 'Dewan Juri',
                default => ucfirst($a->attendee_type ?? 'Umum'),
            };

            $posName = $schedule?->location ?? $competition?->title ?? $session?->session_name ?? 'Pos Lapangan';

            $rows[] = [
                $no++,
                $a->scanned_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') ?? '-',
                $entityType,
                $a->attendee_name,
                $a->scout_group_name ?? '-',
                $a->patrol_name ?? '-',
                $session?->session_name ?? '-',
                $posName,
                $a->scanner?->name ?? 'Sistem / Scanner',
                ucfirst($a->status ?? 'present'),
                $a->notes ?? '-',
            ];
        }

        $filename = 'rekap-kehadiran-pos-lt3-' . date('Ymd-His') . '.csv';

        return $this->streamCsv($filename, $headers, $rows);
    }

    /**
     * Ekspor Rekapitulasi Nilai Seluruh Mata Lomba
     */
    public function exportScoresCsv(): StreamedResponse
    {
        $headers = [
            'No',
            'Kode Lomba',
            'Nama Mata Lomba',
            'Kategori',
            'Nama Regu',
            'Kategori Regu',
            'Asal Pangkalan / Gudep',
            'Nama Juri',
            'Nilai Akhir',
            'Predikat Prestasi',
            'Peringkat Cabang',
            'Status Penilaian',
            'Catatan Dewan Juri',
        ];

        $scores = CompetitionScore::with(['competition', 'patrol.scoutGroup', 'judge'])
            ->orderBy('competition_id')
            ->orderByDesc('final_score')
            ->get();

        $rows = [];
        $no = 1;
        foreach ($scores as $s) {
            $competition = $s->competition;
            $patrol = $s->patrol;
            $group = $patrol?->scoutGroup;

            $gender = $patrol?->gender_category === 'boys' ? 'Regu Putra' : 'Regu Putri';
            $predicate = match ($s->achievement_predicate) {
                'highest_achievement' => 'Prestasi Tinggi',
                'good_achievement' => 'Prestasi Baik',
                'fair_achievement' => 'Prestasi Cukup',
                default => 'Peserta',
            };

            $rows[] = [
                $no++,
                $competition?->code ?? '-',
                $competition?->title ?? '-',
                ucfirst($competition?->category ?? '-'),
                $patrol?->patrol_name ?? '-',
                $gender,
                $group?->school_name ?? '-',
                $s->judge?->name ?? 'Dewan Juri',
                number_format((float) $s->final_score, 2),
                $predicate,
                $s->rank_position ? "Peringkat {$s->rank_position}" : '-',
                ucfirst($s->assessment_status ?? 'finalized'),
                $s->notes ?? '-',
            ];
        }

        $filename = 'rekap-nilai-lomba-lt3-' . date('Ymd-His') . '.csv';

        return $this->streamCsv($filename, $headers, $rows);
    }

    /**
     * Ekspor Rekap Klasemen Akhir menurut jumlah medali (Tinggi, Baik, Cukup)
     */
    public function exportStandingsCsv(string $gender = 'all'): StreamedResponse
    {
        $headers = [
            'Kategori',
            'Peringkat',
            'Nama Regu',
            'Pangkalan / Gugus Depan',
            'Nomor Gudep',
            'Kwartir Ranting',
            'Prestasi Tinggi',
            'Prestasi Baik',
            'Prestasi Cukup',
            'Total Prestasi',
            'Predikat',
        ];

        // Klasemen putra dan putri selalu terpisah; peringkat dihitung per kategori
        $rows = [];
        foreach ($this->standingsService->getPatrolStandings($gender) as $st) {
            $rows[] = [
                $st['gender_category'] === 'boys' ? 'Putra' : 'Putri',
                $st['rank'],
                $st['patrol_name'],
                $st['school_name'],
                $st['group_number'],
                $st['kwarran_name'],
                $st['gold'],
                $st['silver'],
                $st['bronze'],
                $st['total_medals'],
                $st['general_rank_title'] ?? 'Peringkat '.$st['rank'],
            ];
        }

        $genderLabel = match ($gender) {
            'boys' => 'putra',
            'girls' => 'putri',
            default => 'gabungan',
        };

        $filename = "klasemen-akhir-lt3-{$genderLabel}-" . date('Ymd-His') . '.csv';

        return $this->streamCsv($filename, $headers, $rows);
    }
}

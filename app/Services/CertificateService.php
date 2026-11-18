<?php

namespace App\Services;

use App\Enums\RegistrationStatusEnum;
use App\Models\CertificateTemplate;
use App\Models\Chaperone;
use App\Models\CompetitionScore;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use App\Models\User;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Service pengelolaan konfigurasi, data penerima, dan pembuatan e-sertifikat kegiatan
 */
class CertificateService
{
    public const ROLE_PARTICIPANT = 'participant';

    public const ROLE_CHAPERONE = 'chaperone';

    public const ROLE_COMMITTEE = 'committee';

    public const ROLE_JUDGE = 'judge';

    public const ROLE_PRESTASI_TINGGI = 'prestasi_tinggi';

    public const ROLE_PRESTASI_BAIK = 'prestasi_baik';

    public const ROLE_PRESTASI_CUKUP = 'prestasi_cukup';

    public const ROLES = [
        self::ROLE_PARTICIPANT => 'Peserta',
        self::ROLE_CHAPERONE => 'Pembina Pendamping',
        self::ROLE_COMMITTEE => 'Panitia',
        self::ROLE_JUDGE => 'Dewan Juri',
        self::ROLE_PRESTASI_TINGGI => 'Regu Berprestasi Tinggi',
        self::ROLE_PRESTASI_BAIK => 'Regu Berprestasi Baik',
        self::ROLE_PRESTASI_CUKUP => 'Regu Berprestasi Cukup',
    ];

    public function __construct(
        protected QrCodeService $qrCodeService
    ) {}

    /**
     * Mengambil susunan tata letak bawaan untuk elemen sertifikat
     *
     * @return array<string, array{x: float, y: float, font_size: float, color: string, align: string, bold: bool, size?: float, show?: bool}>
     */
    public function defaultLayout(string $role): array
    {
        $roleColor = match ($role) {
            self::ROLE_PRESTASI_TINGGI => '#b45309',
            self::ROLE_PRESTASI_BAIK => '#475569',
            self::ROLE_PRESTASI_CUKUP => '#9a3412',
            default => '#b45309',
        };

        return [
            'certificate_number' => [
                'x' => 50.0,
                'y' => 28.0,
                'font_size' => 11.0,
                'color' => '#78350f',
                'align' => 'center',
                'bold' => true,
            ],
            'full_name' => [
                'x' => 50.0,
                'y' => 42.0,
                'font_size' => 28.0,
                'color' => '#260c36',
                'align' => 'center',
                'bold' => true,
            ],
            'scout_id' => [
                'x' => 50.0,
                'y' => 51.5,
                'font_size' => 12.5,
                'color' => '#4b5563',
                'align' => 'center',
                'bold' => true,
            ],
            'institution' => [
                'x' => 50.0,
                'y' => 57.0,
                'font_size' => 14.5,
                'color' => '#1f2937',
                'align' => 'center',
                'bold' => true,
            ],
            'role_label' => [
                'x' => 50.0,
                'y' => 63.5,
                'font_size' => 16.0,
                'color' => $roleColor,
                'align' => 'center',
                'bold' => true,
            ],
        ];
    }

    /**
     * Mengambil model CertificateTemplate tersimpan untuk peran tertentu
     */
    public function getTemplate(Event $event, string $role): ?CertificateTemplate
    {
        return $event->certificateTemplates()->where('role', $role)->first();
    }

    /**
     * Mengambil tata letak sertifikat (tersimpan atau bawaan)
     */
    public function getLayout(Event $event, string $role): array
    {
        $template = $this->getTemplate($event, $role);
        $defaults = $this->defaultLayout($role);

        if (! $template || empty($template->layout)) {
            return $defaults;
        }

        $layout = $defaults;
        foreach ($template->layout as $key => $values) {
            if (isset($layout[$key]) && is_array($values)) {
                $layout[$key] = array_merge($layout[$key], $values);
            }
        }

        return $layout;
    }

    /**
     * Mengambil URL berkas gambar template
     */
    public function getTemplateUrl(Event $event, string $role): ?string
    {
        $template = $this->getTemplate($event, $role);
        if ($template && $template->template_path && Storage::disk('public')->exists($template->template_path)) {
            return Storage::disk('public')->url($template->template_path);
        }

        return null;
    }

    /**
     * Mengambil path absolut berkas gambar template di storage lokal
     */
    public function getTemplateAbsolutePath(Event $event, string $role): ?string
    {
        $template = $this->getTemplate($event, $role);
        if ($template && $template->template_path && Storage::disk('public')->exists($template->template_path)) {
            return Storage::disk('public')->path($template->template_path);
        }

        return null;
    }

    /**
     * Menghasilkan nomor sertifikat resmi
     */
    public function generateCertificateNumber(string $role, string $idNumber, ?Event $event = null): string
    {
        $year = $event?->start_date ? $event->start_date->year : now()->year;
        $roleCode = match ($role) {
            self::ROLE_PARTICIPANT => 'PST',
            self::ROLE_CHAPERONE => 'PBD',
            self::ROLE_COMMITTEE => 'PNT',
            self::ROLE_JUDGE => 'JUR',
            self::ROLE_PRESTASI_TINGGI => 'PRT',
            self::ROLE_PRESTASI_BAIK => 'PRB',
            self::ROLE_PRESTASI_CUKUP => 'PRC',
            default => 'UMM',
        };

        $cleanId = preg_replace('/[^A-Za-z0-9]/', '', $idNumber);

        return "CERT/LT3-PSP/{$year}/{$roleCode}/{$cleanId}";
    }

    /**
     * Data penerima sertifikat untuk pratinjau contoh
     */
    public function getSampleRecipient(string $role, ?Event $event = null): array
    {
        $year = $event?->start_date ? $event->start_date->year : now()->year;

        return match ($role) {
            self::ROLE_PARTICIPANT => [
                'id_number' => 'P-102948',
                'full_name' => 'Ahmad Dahlan Nasution',
                'scout_id' => 'NTA: 02.21.01.00142',
                'institution' => 'Gudep 01.001 - 01.002 • SMP Negeri 1 Padangsidimpuan',
                'role_label' => 'Sebagai: Peserta Regu Garuda',
                'certificate_number' => "CERT/LT3-PSP/{$year}/PST/P102948",
                'verification_url' => route('certificates.verify', ['id' => 'P-102948']),
                'role' => self::ROLE_PARTICIPANT,
                'is_eligible' => true,
            ],
            self::ROLE_CHAPERONE => [
                'id_number' => 'B-203819',
                'full_name' => 'Kak Siti Aminah, S.Pd',
                'scout_id' => 'NTA: 02.21.01.00089',
                'institution' => 'Gudep 01.001 - 01.002 • SMP Negeri 1 Padangsidimpuan',
                'role_label' => 'Sebagai: Pembina Pendamping',
                'certificate_number' => "CERT/LT3-PSP/{$year}/PBD/B203819",
                'verification_url' => route('certificates.verify', ['id' => 'B-203819']),
                'role' => self::ROLE_CHAPERONE,
                'is_eligible' => true,
            ],
            self::ROLE_COMMITTEE => [
                'id_number' => 'A-304726',
                'full_name' => 'Kak Fahri Siregar, S.Pd',
                'scout_id' => 'NTA: 02.21.01.00012',
                'institution' => 'Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan',
                'role_label' => 'Sebagai: Panitia Pelaksana',
                'certificate_number' => "CERT/LT3-PSP/{$year}/PNT/A304726",
                'verification_url' => route('certificates.verify', ['id' => 'A-304726']),
                'role' => self::ROLE_COMMITTEE,
                'is_eligible' => true,
            ],
            self::ROLE_JUDGE => [
                'id_number' => 'A-405837',
                'full_name' => 'Kak Rizky Pratama, M.Pd',
                'scout_id' => 'NTA: 02.21.01.00034',
                'institution' => 'Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan',
                'role_label' => 'Sebagai: Dewan Juri',
                'certificate_number' => "CERT/LT3-PSP/{$year}/JUR/A405837",
                'verification_url' => route('certificates.verify', ['id' => 'A-405837']),
                'role' => self::ROLE_JUDGE,
                'is_eligible' => true,
            ],
            self::ROLE_PRESTASI_TINGGI => [
                'id_number' => 'REG-01-PA',
                'full_name' => 'REGU RAJAWALI (PUTRA)',
                'scout_id' => 'NPP: PA-01 • Regu Penggalang Putra',
                'institution' => 'Gudep 01.001 • SMP Negeri 1 Padangsidimpuan',
                'role_label' => 'Sebagai: Regu Berprestasi Tinggi Putra (Kejuaraan Umum)',
                'certificate_number' => "CERT/LT3-PSP/{$year}/PRT/REG01PA",
                'verification_url' => route('certificates.verify', ['id' => 'REG-01-PA']),
                'role' => self::ROLE_PRESTASI_TINGGI,
                'is_eligible' => true,
            ],
            self::ROLE_PRESTASI_BAIK => [
                'id_number' => 'REG-02-PI',
                'full_name' => 'REGU MELATI (PUTRI)',
                'scout_id' => 'NPP: PI-02 • Regu Penggalang Putri',
                'institution' => 'Gudep 01.002 • SMP Negeri 2 Padangsidimpuan',
                'role_label' => 'Sebagai: Regu Berprestasi Baik Putri (Kejuaraan Umum)',
                'certificate_number' => "CERT/LT3-PSP/{$year}/PRB/REG02PI",
                'verification_url' => route('certificates.verify', ['id' => 'REG-02-PI']),
                'role' => self::ROLE_PRESTASI_BAIK,
                'is_eligible' => true,
            ],
            self::ROLE_PRESTASI_CUKUP => [
                'id_number' => 'REG-03-PA',
                'full_name' => 'REGU GARUDA (PUTRA)',
                'scout_id' => 'NPP: PA-03 • Regu Penggalang Putra',
                'institution' => 'Gudep 01.003 • SMP Negeri 3 Padangsidimpuan',
                'role_label' => 'Sebagai: Regu Berprestasi Cukup Putra (Kejuaraan Umum)',
                'certificate_number' => "CERT/LT3-PSP/{$year}/PRC/REG03PA",
                'verification_url' => route('certificates.verify', ['id' => 'REG-03-PA']),
                'role' => self::ROLE_PRESTASI_CUKUP,
                'is_eligible' => true,
            ],
            default => [
                'id_number' => 'S-000001',
                'full_name' => 'Nama Penerima Sertifikat',
                'scout_id' => 'NTA: 02.21.01.00000',
                'institution' => 'Pangkalan Gugusdepan Gerakan Pramuka',
                'role_label' => 'Sebagai: Peserta',
                'certificate_number' => "CERT/LT3-PSP/{$year}/UMM/S000001",
                'verification_url' => url('/'),
                'role' => self::ROLE_PARTICIPANT,
                'is_eligible' => true,
            ],
        };
    }

    /**
     * Format data penerima dari model Participant
     */
    public function recipientDataForParticipant(Participant $participant, ?Event $event = null): array
    {
        $schoolName = $participant->patrol?->scoutGroup?->school_name ?: 'Pangkalan Gugus Depan';
        $gudepNumber = $participant->patrol?->scoutGroup?->group_number;
        $patrolName = $participant->patrol?->patrol_name ?: $participant->patrol?->name;

        $institution = $gudepNumber ? "Gudep {$gudepNumber} • {$schoolName}" : $schoolName;
        $roleLabel = $patrolName ? "Sebagai: Peserta (Regu {$patrolName})" : 'Sebagai: Peserta';

        return [
            'id' => $participant->id,
            'id_number' => $participant->id_number,
            'full_name' => $participant->full_name,
            'scout_id' => $participant->scout_id ? 'NTA: '.$participant->scout_id : 'NTA: -',
            'institution' => $institution,
            'role_label' => $roleLabel,
            'certificate_number' => $this->generateCertificateNumber(self::ROLE_PARTICIPANT, $participant->id_number, $event),
            'verification_url' => route('certificates.verify', ['id' => $participant->id_number]),
            'role' => self::ROLE_PARTICIPANT,
            'is_eligible' => $participant->status === RegistrationStatusEnum::Verified,
        ];
    }

    /**
     * Format data penerima dari model Chaperone
     */
    public function recipientDataForChaperone(Chaperone $chaperone, ?Event $event = null): array
    {
        $schoolName = $chaperone->scoutGroup?->school_name ?: $chaperone->scoutGroup?->name ?: 'Pangkalan Gugus Depan';
        $gudepNumber = $chaperone->scoutGroup?->group_number;
        $institution = $gudepNumber ? "Gudep {$gudepNumber} • {$schoolName}" : $schoolName;

        return [
            'id' => $chaperone->id,
            'id_number' => $chaperone->id_number,
            'full_name' => $chaperone->full_name,
            'scout_id' => $chaperone->scout_id ? 'NTA: '.$chaperone->scout_id : 'NTA: -',
            'institution' => $institution,
            'role_label' => 'Sebagai: Pembina Pendamping',
            'certificate_number' => $this->generateCertificateNumber(self::ROLE_CHAPERONE, $chaperone->id_number, $event),
            'verification_url' => route('certificates.verify', ['id' => $chaperone->id_number]),
            'role' => self::ROLE_CHAPERONE,
            'is_eligible' => $chaperone->status === RegistrationStatusEnum::Verified,
        ];
    }

    /**
     * Format data penerima dari model User (Panitia atau Juri)
     */
    public function recipientDataForOfficial(User $user, string $role, ?Event $event = null): array
    {
        $institution = $user->institution ?: 'Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan';
        $roleLabel = match ($role) {
            self::ROLE_COMMITTEE => $user->committee_position ? 'Sebagai: Panitia ('.$user->committee_position.')' : 'Sebagai: Panitia Pelaksana',
            self::ROLE_JUDGE => 'Sebagai: Dewan Juri',
            default => 'Sebagai: Aparatur Kegiatan',
        };

        return [
            'id' => $user->id,
            'id_number' => $user->id_number,
            'full_name' => $user->name,
            'scout_id' => $user->scout_id ? 'NTA: '.$user->scout_id : 'NTA: -',
            'institution' => $institution,
            'role_label' => $roleLabel,
            'certificate_number' => $this->generateCertificateNumber($role, $user->id_number, $event),
            'verification_url' => route('certificates.verify', ['id' => $user->id_number]),
            'role' => $role,
            'is_eligible' => (bool) $user->is_active,
        ];
    }

    /**
     * Format data penerima sertifikat regu berprestasi kejuaraan umum
     */
    public function recipientDataForWinningPatrolGeneral(Patrol $patrol, string $generalRankTitle, int $rank, ?Event $event = null): array
    {
        $schoolName = $patrol->scoutGroup?->school_name ?: 'Pangkalan Gugus Depan';
        $gudepNumber = $patrol->scoutGroup?->group_number;
        $institution = $gudepNumber ? "Gudep {$gudepNumber} • {$schoolName}" : $schoolName;

        $genderLabel = $patrol->isBoys() ? 'Putra' : ($patrol->isGirls() ? 'Putri' : '');
        $patrolName = strtoupper('REGU ' . ($patrol->patrol_name ?: 'PRAMUKA')) . ($genderLabel ? " ({$genderLabel})" : '');
        $npp = $patrol->npp_code ?: $patrol->npp ?: '-';

        $role = match ($rank) {
            1 => self::ROLE_PRESTASI_TINGGI,
            2 => self::ROLE_PRESTASI_BAIK,
            3 => self::ROLE_PRESTASI_CUKUP,
            default => self::ROLE_PRESTASI_TINGGI,
        };

        $idNumber = "GEN-{$patrol->id}";
        $cleanNpp = preg_replace('/[^A-Za-z0-9]/', '', (string) $npp) ?: '01';

        return [
            'id' => $patrol->id,
            'id_number' => $idNumber,
            'full_name' => $patrolName,
            'scout_id' => "NPP: {$npp} • Regu Penggalang {$genderLabel}",
            'institution' => $institution,
            'role_label' => "Sebagai: {$generalRankTitle} (Kejuaraan Umum)",
            'certificate_number' => $this->generateCertificateNumber($role, "GEN{$cleanNpp}", $event),
            'verification_url' => route('certificates.verify', ['id' => $idNumber]),
            'role' => $role,
            'is_eligible' => $patrol->status === RegistrationStatusEnum::Verified,
        ];
    }

    /**
     * Format data penerima sertifikat regu berprestasi per mata lomba
     */
    public function recipientDataForWinningPatrolCompetition(CompetitionScore $score, ?Event $event = null): array
    {
        $patrol = $score->patrol;
        $schoolName = $patrol?->scoutGroup?->school_name ?: 'Pangkalan Gugus Depan';
        $gudepNumber = $patrol?->scoutGroup?->group_number;
        $institution = $gudepNumber ? "Gudep {$gudepNumber} • {$schoolName}" : $schoolName;

        $genderLabel = $patrol?->isBoys() ? 'Putra' : ($patrol?->isGirls() ? 'Putri' : '');
        $patrolName = strtoupper('REGU ' . ($patrol?->patrol_name ?: 'PRAMUKA')) . ($genderLabel ? " ({$genderLabel})" : '');
        $npp = $patrol?->npp_code ?: $patrol?->npp ?: '-';
        $competitionTitle = $score->competition?->title ?: 'Mata Lomba';
        $predicateLabel = $score->predicateLabel();

        $role = match ($score->predicate) {
            'prestasi_tinggi' => self::ROLE_PRESTASI_TINGGI,
            'prestasi_baik' => self::ROLE_PRESTASI_BAIK,
            'prestasi_cukup' => self::ROLE_PRESTASI_CUKUP,
            default => self::ROLE_PRESTASI_TINGGI,
        };

        $idNumber = "LMB-{$score->id}";
        $scoreIdShort = substr(preg_replace('/[^A-Za-z0-9]/', '', (string) $score->id), 0, 8);

        return [
            'id' => $score->id,
            'id_number' => $idNumber,
            'full_name' => $patrolName,
            'scout_id' => "NPP: {$npp} • Nilai Akhir: " . number_format((float) $score->final_score, 2, ',', '.'),
            'institution' => $institution,
            'role_label' => "Sebagai: {$predicateLabel} Cabang {$competitionTitle}",
            'certificate_number' => $this->generateCertificateNumber($role, "LMB{$scoreIdShort}", $event),
            'verification_url' => route('certificates.verify', ['id' => $idNumber]),
            'role' => $role,
            'is_eligible' => (bool) $score->is_locked,
        ];
    }

    /**
     * Mengambil daftar e-sertifikat regu berprestasi milik suatu pangkalan gugus depan
     *
     * @return array<int, array>
     */
    public function getWinningCertificatesForScoutGroup(ScoutGroup $scoutGroup, ?Event $event = null): array
    {
        $items = [];
        $standingsService = app(StandingsService::class);

        // 1. Kejuaraan Umum Regu (Peringkat 1 s.d. 3)
        $byGender = [];
        foreach ($scoutGroup->patrols as $patrol) {
            $gender = $patrol->gender_category instanceof \BackedEnum ? $patrol->gender_category->value : $patrol->gender_category;
            $byGender[$gender] ??= $standingsService->getPatrolStandings($gender, null, true);
            $row = $byGender[$gender]->firstWhere('id', $patrol->id);

            if ($row && !empty($row['general_rank_title']) && ($row['rank'] ?? 99) <= 3) {
                $role = match ($row['rank']) {
                    1 => self::ROLE_PRESTASI_TINGGI,
                    2 => self::ROLE_PRESTASI_BAIK,
                    3 => self::ROLE_PRESTASI_CUKUP,
                    default => self::ROLE_PRESTASI_TINGGI,
                };

                $recipient = $this->recipientDataForWinningPatrolGeneral($patrol, $row['general_rank_title'], $row['rank'], $event);

                $items[] = [
                    'category' => 'general',
                    'title' => $row['general_rank_title'],
                    'predicate' => match ($row['rank']) {
                        1 => 'Tinggi',
                        2 => 'Baik',
                        3 => 'Cukup',
                        default => '',
                    },
                    'role_key' => $role,
                    'patrol_name' => $patrol->patrol_name,
                    'patrol_id' => $patrol->id,
                    'npp' => $patrol->npp_code ?: $patrol->npp ?: '-',
                    'detail' => 'Kejuaraan Umum LT-III • ' . ($patrol->isBoys() ? 'Regu Putra' : 'Regu Putri'),
                    'certificate_number' => $recipient['certificate_number'],
                    'download_url' => route('certificates.download.patrol.general', $patrol),
                    'badge_class' => match ($row['rank']) {
                        1 => 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-700',
                        2 => 'bg-slate-100 text-slate-800 border-slate-300 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
                        3 => 'bg-orange-100 text-orange-800 border-orange-300 dark:bg-orange-950/60 dark:text-orange-300 dark:border-orange-700',
                        default => 'bg-zinc-100 text-zinc-800 border-zinc-300',
                    },
                    'icon' => 'trophy',
                ];
            }
        }

        // 2. Cabang Lomba (Mata Lomba)
        $patrolIds = $scoutGroup->patrols->pluck('id');
        $scores = CompetitionScore::with(['competition', 'patrol'])
            ->whereIn('patrol_id', $patrolIds)
            ->where('is_locked', true)
            ->whereIn('predicate', ['prestasi_tinggi', 'prestasi_baik', 'prestasi_cukup'])
            ->get();

        foreach ($scores as $score) {
            $role = match ($score->predicate) {
                'prestasi_tinggi' => self::ROLE_PRESTASI_TINGGI,
                'prestasi_baik' => self::ROLE_PRESTASI_BAIK,
                'prestasi_cukup' => self::ROLE_PRESTASI_CUKUP,
                default => self::ROLE_PRESTASI_TINGGI,
            };

            $recipient = $this->recipientDataForWinningPatrolCompetition($score, $event);

            $items[] = [
                'category' => 'competition',
                'title' => $score->predicateLabel() . ' Cabang ' . ($score->competition?->title ?? 'Lomba'),
                'predicate' => match ($score->predicate) {
                    'prestasi_tinggi' => 'Tinggi',
                    'prestasi_baik' => 'Baik',
                    'prestasi_cukup' => 'Cukup',
                    default => '',
                },
                'role_key' => $role,
                'patrol_name' => $score->patrol?->patrol_name ?? 'Regu',
                'patrol_id' => $score->patrol_id,
                'npp' => $score->patrol?->npp_code ?: $score->patrol?->npp ?: '-',
                'detail' => '[' . ($score->competition?->code ?? '') . '] ' . ($score->competition?->title ?? 'Lomba') . ' • Nilai: ' . number_format((float) $score->final_score, 2, ',', '.'),
                'certificate_number' => $recipient['certificate_number'],
                'download_url' => route('certificates.download.competition-score', $score),
                'badge_class' => match ($score->predicate) {
                    'prestasi_tinggi' => 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-700',
                    'prestasi_baik' => 'bg-slate-100 text-slate-800 border-slate-300 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
                    'prestasi_cukup' => 'bg-orange-100 text-orange-800 border-orange-300 dark:bg-orange-950/60 dark:text-orange-300 dark:border-orange-700',
                    default => 'bg-zinc-100 text-zinc-800 border-zinc-300',
                },
                'icon' => 'sparkles',
            ];
        }

        return $items;
    }

    /**
     * Mengambil daftar sertifikat yang berhak diakses oleh user saat login
     *
     * @return array<int, array>
     */
    public function getCertificatesForUser(User $user, ?Event $event = null): array
    {
        $certificates = [];

        // 1. Jika akun peserta
        if ($user->isParticipant() && $user->participant) {
            $data = $this->recipientDataForParticipant($user->participant, $event);
            if ($data['is_eligible']) {
                $certificates[] = $data;
            }
        }

        // 2. Jika akun pembina pendamping
        if ($user->isChaperone() && $user->chaperone) {
            $data = $this->recipientDataForChaperone($user->chaperone, $event);
            if ($data['is_eligible']) {
                $certificates[] = $data;
            }
        }

        // 3. Jika akun panitia
        if ($user->isCommittee()) {
            $data = $this->recipientDataForOfficial($user, self::ROLE_COMMITTEE, $event);
            if ($data['is_eligible']) {
                $certificates[] = $data;
            }
        }

        // 4. Jika akun dewan juri
        if ($user->isJudge()) {
            $data = $this->recipientDataForOfficial($user, self::ROLE_JUDGE, $event);
            if ($data['is_eligible']) {
                $certificates[] = $data;
            }
        }

        return $certificates;
    }

    /**
     * Render PDF sertifikat tunggal (A4 landscape) dengan desain vektor kode murni
     */
    public function renderPdf(Event $event, string $role, array $recipient): string
    {
        $layout = $this->getLayout($event, $role);

        $logoPath = public_path('logo.png');
        $logoBase64 = is_file($logoPath)
            ? 'data:image/png;base64,'.base64_encode((string) file_get_contents($logoPath))
            : null;

        $committeeSigBase64 = null;
        if (($event->show_signatures_on_certificate ?? true) && $event->committee_chair_signature_path && Storage::disk('public')->exists($event->committee_chair_signature_path)) {
            $path = Storage::disk('public')->path($event->committee_chair_signature_path);
            $mime = @mime_content_type($path) ?: 'image/png';
            $committeeSigBase64 = 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($path));
        }

        $kwarcabSigBase64 = null;
        if (($event->show_signatures_on_certificate ?? true) && $event->kwarcab_chair_signature_path && Storage::disk('public')->exists($event->kwarcab_chair_signature_path)) {
            $path = Storage::disk('public')->path($event->kwarcab_chair_signature_path);
            $mime = @mime_content_type($path) ?: 'image/png';
            $kwarcabSigBase64 = 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($path));
        }

        $html = view('certificates.pdf-single', [
            'event' => $event,
            'role' => $role,
            'recipient' => $recipient,
            'layout' => $layout,
            'logoBase64' => $logoBase64,
            'committeeSigBase64' => $committeeSigBase64,
            'kwarcabSigBase64' => $kwarcabSigBase64,
        ])->render();

        $dompdf = new Dompdf((new Options)
            ->set('isRemoteEnabled', false)
            ->set('chroot', base_path())
            ->set('isFontSubsettingEnabled', true)
            ->set('defaultFont', 'DejaVu Sans'));

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * Respons HTTP PDF sertifikat
     */
    public function responsePdf(Event $event, string $role, array $recipient, bool $download = false): Response
    {
        $pdfContent = $this->renderPdf($event, $role, $recipient);
        $cleanName = Str::slug($recipient['full_name'] ?: 'sertifikat');
        $filename = "Sertifikat-{$cleanName}-{$recipient['id_number']}.pdf";

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => ($download ? 'attachment' : 'inline').'; filename="'.$filename.'"',
            'Cache-Control' => 'no-store, private',
        ]);
    }
}

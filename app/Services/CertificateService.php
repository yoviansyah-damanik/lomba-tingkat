<?php

namespace App\Services;

use App\Enums\RegistrationStatusEnum;
use App\Models\CertificateTemplate;
use App\Models\Chaperone;
use App\Models\Event;
use App\Models\Participant;
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

    public const ROLES = [
        self::ROLE_PARTICIPANT => 'Peserta',
        self::ROLE_CHAPERONE => 'Pembina Pendamping',
        self::ROLE_COMMITTEE => 'Panitia',
        self::ROLE_JUDGE => 'Dewan Juri',
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
        return [
            'certificate_number' => [
                'x' => 50.0,
                'y' => 28.0,
                'font_size' => 11.0,
                'color' => '#6b7280',
                'align' => 'center',
                'bold' => false,
            ],
            'full_name' => [
                'x' => 50.0,
                'y' => 45.0,
                'font_size' => 28.0,
                'color' => '#111827',
                'align' => 'center',
                'bold' => true,
            ],
            'scout_id' => [
                'x' => 50.0,
                'y' => 51.5,
                'font_size' => 13.0,
                'color' => '#4b5563',
                'align' => 'center',
                'bold' => false,
            ],
            'institution' => [
                'x' => 50.0,
                'y' => 57.5,
                'font_size' => 15.0,
                'color' => '#1f2937',
                'align' => 'center',
                'bold' => false,
            ],
            'role_label' => [
                'x' => 50.0,
                'y' => 64.5,
                'font_size' => 16.0,
                'color' => '#b45309',
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
            default => [
                'id_number' => 'S-000001',
                'full_name' => 'Nama Penerima Sertifikat',
                'scout_id' => 'NTA: 02.21.01.00000',
                'institution' => 'Pangkalan Gugusdepan Gerakan Pramuka',
                'role_label' => 'Sebagai: Peserta',
                'certificate_number' => "CERT/LT3-PSP/{$year}/UMM/S000001",
                'verification_url' => route('certificates.index'),
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
            'scout_id' => $participant->scout_id ? 'NTA: ' . $participant->scout_id : 'NTA: -',
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
            'scout_id' => $chaperone->scout_id ? 'NTA: ' . $chaperone->scout_id : 'NTA: -',
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
            self::ROLE_COMMITTEE => $user->committee_position ? 'Sebagai: Panitia (' . $user->committee_position . ')' : 'Sebagai: Panitia Pelaksana',
            self::ROLE_JUDGE => 'Sebagai: Dewan Juri',
            default => 'Sebagai: Aparatur Kegiatan',
        };

        return [
            'id' => $user->id,
            'id_number' => $user->id_number,
            'full_name' => $user->name,
            'scout_id' => $user->scout_id ? 'NTA: ' . $user->scout_id : 'NTA: -',
            'institution' => $institution,
            'role_label' => $roleLabel,
            'certificate_number' => $this->generateCertificateNumber($role, $user->id_number, $event),
            'verification_url' => route('certificates.verify', ['id' => $user->id_number]),
            'role' => $role,
            'is_eligible' => (bool) $user->is_active,
        ];
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
     * Render PDF sertifikat tunggal (A4 landscape)
     */
    public function renderPdf(Event $event, string $role, array $recipient): string
    {
        $layout = $this->getLayout($event, $role);
        $templatePath = $this->getTemplateAbsolutePath($event, $role);

        $templateBase64 = null;
        if ($templatePath && is_file($templatePath)) {
            $ext = pathinfo($templatePath, PATHINFO_EXTENSION);
            $mime = match (strtolower($ext)) {
                'jpg', 'jpeg' => 'image/jpeg',
                'webp' => 'image/webp',
                default => 'image/png',
            };
            $templateBase64 = 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($templatePath));
        }

        $html = view('certificates.pdf-single', [
            'event' => $event,
            'role' => $role,
            'recipient' => $recipient,
            'layout' => $layout,
            'templateBase64' => $templateBase64,
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
            'Content-Disposition' => ($download ? 'attachment' : 'inline') . '; filename="' . $filename . '"',
            'Cache-Control' => 'no-store, private',
        ]);
    }
}

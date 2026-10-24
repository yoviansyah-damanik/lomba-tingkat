<?php

namespace App\Http\Controllers;

use App\Models\ScoutGroup;
use App\Services\PdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

/**
 * Ekspor seluruh berkas pendaftaran kontingen sebagai satu file ZIP
 */
class ZipExportController extends Controller
{
    public function __construct(private readonly PdfService $pdf) {}

    /**
     * Unduh ZIP berisi: Bukti Registrasi, Unggahan Gudep, Form 01 + Form 02 tiap peserta, Form 03 tiap bindamping
     */
    public function downloadScoutGroupZip(Request $request, string $id): Response|RedirectResponse
    {
        $this->liftLimits();
        $scoutGroup = ScoutGroup::with([
            'patrols.participants',
            'chaperones',
            'user',
        ])->findOrFail($id);

        $tempDir = storage_path('app/temp');
        if (! is_dir($tempDir)) {
            @mkdir($tempDir, 0755, true);
        }
        $zipPath = $tempDir . DIRECTORY_SEPARATOR . 'lt3_zip_' . uniqid() . '.zip';
        if (file_exists($zipPath)) {
            @unlink($zipPath);
        }

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->withErrors(['zip' => 'Gagal membuat arsip ZIP.']);
        }

        $schoolSlug = $this->slugify($scoutGroup->school_name);

        // ──────────────────────────────────────────────────────────────────
        // 1. Bukti Registrasi Gugus Depan (PDF baru)
        // ──────────────────────────────────────────────────────────────────
        try {
            $certPdf = $this->pdf->render(
                'admin.scout-groups.registration-certificate',
                ['scoutGroup' => $scoutGroup]
            );
            $zip->addFromString("00_Bukti_Registrasi_{$schoolSlug}.pdf", $certPdf);
        } catch (\Throwable $e) {
            Log::warning("Gagal render Bukti Registrasi [{$scoutGroup->id}]: " . $e->getMessage());
        }

        // ──────────────────────────────────────────────────────────────────
        // 2. Unggahan Gugus Depan: Surat Tugas & Bukti Pembayaran
        // ──────────────────────────────────────────────────────────────────
        $this->addStorageFile(
            $zip,
            $scoutGroup->mandate_letter_path,
            "01_Gudep/Surat_Tugas_Mandat." . $this->ext($scoutGroup->mandate_letter_path)
        );
        $this->addStorageFile(
            $zip,
            $scoutGroup->payment_proof_path,
            "01_Gudep/Bukti_Pembayaran." . $this->ext($scoutGroup->payment_proof_path)
        );

        // ──────────────────────────────────────────────────────────────────
        // 3. Per Regu: Form 01 + Form 02 tiap peserta + unggahan peserta
        // ──────────────────────────────────────────────────────────────────
        foreach ($scoutGroup->patrols as $patrol) {
            $patrol->setRelation('scoutGroup', $scoutGroup);
            $genderLabel = $patrol->isBoys() ? 'Putra' : 'Putri';
            $patrolSlug  = $this->slugify($patrol->patrol_name ?? $genderLabel);
            $folderRegu  = "02_Regu_{$genderLabel}_{$patrolSlug}";

            // Form 01 – Rekap Regu (PDF)
            try {
                $chaperone = $patrol->chaperone;
                $orderedParticipants = $patrol->orderedParticipants();
                $form01Pdf = $this->pdf->render('portal.scout-group.patrols.form-01', [
                    'scoutGroup'          => $scoutGroup,
                    'patrol'              => $patrol,
                    'chaperone'           => $chaperone,
                    'orderedParticipants' => $orderedParticipants,
                ]);
                $zip->addFromString("{$folderRegu}/Form_01_Rekap_Regu_{$patrolSlug}.pdf", $form01Pdf);
            } catch (\Throwable $e) {
                Log::warning("Gagal render Form 01 [{$patrolSlug}]: " . $e->getMessage());
            }

            // Form 02 + Unggahan tiap peserta
            foreach ($patrol->orderedParticipants() as $idx => $participant) {
                $participant->setRelation('patrol', $patrol);
                $no            = str_pad((string) ($idx + 1), 2, '0', STR_PAD_LEFT);
                $namaSlug      = $this->slugify($participant->full_name);
                $folderPeserta = "{$folderRegu}/Peserta_{$no}_{$namaSlug}";

                // Form 02 – Biodata Peserta (PDF)
                try {
                    $form02Pdf = $this->pdf->render('portal.scout-group.participants.print', [
                        'scoutGroup'  => $scoutGroup,
                        'participant' => $participant,
                    ]);
                    $zip->addFromString("{$folderPeserta}/Form_02_Biodata_{$namaSlug}.pdf", $form02Pdf);
                } catch (\Throwable $e) {
                    Log::warning("Gagal render Form 02 [{$namaSlug}]: " . $e->getMessage());
                }

                // Unggahan persyaratan peserta
                $docs = [
                    'photo_path'                  => 'Pas_Foto',
                    'doc_kta_path'                => 'KTA',
                    'doc_bpjs_path'               => 'BPJS',
                    'doc_health_certificate_path' => 'Surat_Kesehatan',
                    'doc_birth_certificate_path'  => 'Akta_Kelahiran',
                    'doc_family_card_path'        => 'Kartu_Keluarga',
                    'doc_sku_path'                => 'SKU',
                    'doc_skk_path'                => 'SKK',
                ];
                foreach ($docs as $field => $label) {
                    $this->addStorageFile(
                        $zip,
                        $participant->{$field},
                        "{$folderPeserta}/{$label}_{$namaSlug}." . $this->ext($participant->{$field})
                    );
                }
            }
        }

        // ──────────────────────────────────────────────────────────────────
        // 4. Form 03 tiap bindamping + unggahan foto
        // ──────────────────────────────────────────────────────────────────
        foreach ($scoutGroup->chaperones as $idx => $chaperone) {
            $chaperone->setRelation('scoutGroup', $scoutGroup);
            $no          = str_pad((string) ($idx + 1), 2, '0', STR_PAD_LEFT);
            $namaSlug    = $this->slugify($chaperone->full_name);
            $genderLabel = $chaperone->gender === 'female' ? 'Putri' : 'Putra';
            $folderBinda = "03_Bindamping_{$no}_{$genderLabel}_{$namaSlug}";

            // Form 03 – Biodata Bindamping (PDF)
            try {
                $form03Pdf = $this->pdf->render('portal.scout-group.chaperones.print', [
                    'scoutGroup' => $scoutGroup,
                    'chaperone'  => $chaperone,
                ]);
                $zip->addFromString("{$folderBinda}/Form_03_Biodata_{$namaSlug}.pdf", $form03Pdf);
            } catch (\Throwable $e) {
                Log::warning("Gagal render Form 03 [{$namaSlug}]: " . $e->getMessage());
            }

            // Unggahan foto bindamping
            $this->addStorageFile(
                $zip,
                $chaperone->photo_path,
                "{$folderBinda}/Pas_Foto_{$namaSlug}." . $this->ext($chaperone->photo_path)
            );
        }

        $zip->close();

        $filename = "Berkas_Kontingen_{$schoolSlug}_" . now()->format('Ymd_His') . ".zip";

        return response()->download($zipPath, $filename, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Alirkan progres pembuatan ZIP secara real-time (Server-Sent Events) ke frontend
     */
    public function streamScoutGroupZip(Request $request, string $id): StreamedResponse
    {
        $scoutGroup = ScoutGroup::with([
            'patrols.participants',
            'chaperones',
            'user',
        ])->findOrFail($id);

        return response()->stream(function () use ($scoutGroup) {
            $this->liftLimits();
            // Matikan buffering agar chunk langsung terkirim ke klien
            while (ob_get_level() > 0) {
                @ob_end_flush();
            }

            $send = function (int $percent, string $step, ?string $downloadUrl = null, string $status = 'processing') {
                echo "data: " . json_encode([
                    'percent' => $percent,
                    'step' => $step,
                    'status' => $status,
                    'download_url' => $downloadUrl,
                ]) . "\n\n";
                if (function_exists('ob_flush')) {
                    @ob_flush();
                }
                flush();
            };

            $send(5, 'Menyiapkan direktori arsip berkas kontingen...');

            $tempDir = storage_path('app/temp');
            if (! is_dir($tempDir)) {
                @mkdir($tempDir, 0755, true);
            }
            $zipPath = $tempDir . DIRECTORY_SEPARATOR . 'lt3_zip_' . uniqid() . '.zip';
            if (file_exists($zipPath)) {
                @unlink($zipPath);
            }

            $zip = new ZipArchive;
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                $send(0, 'Gagal membuka arsip ZIP di server.', null, 'error');
                return;
            }

            $schoolSlug = $this->slugify($scoutGroup->school_name);

            // 1. Bukti Registrasi
            $send(15, 'Mencetak Bukti Registrasi Kontingen (PDF)...');
            try {
                $certPdf = $this->pdf->render(
                    'admin.scout-groups.registration-certificate',
                    ['scoutGroup' => $scoutGroup]
                );
                $zip->addFromString("00_Bukti_Registrasi_{$schoolSlug}.pdf", $certPdf);
            } catch (\Throwable $e) {
                Log::warning("Gagal render Bukti Registrasi [{$scoutGroup->id}]: " . $e->getMessage());
            }

            // 2. Berkas Gudep
            $send(25, 'Menyisipkan Surat Tugas & Bukti Pembayaran...');
            $this->addStorageFile(
                $zip,
                $scoutGroup->mandate_letter_path,
                "01_Gudep/Surat_Tugas_Mandat." . $this->ext($scoutGroup->mandate_letter_path)
            );
            $this->addStorageFile(
                $zip,
                $scoutGroup->payment_proof_path,
                "01_Gudep/Bukti_Pembayaran." . $this->ext($scoutGroup->payment_proof_path)
            );

            // 3. Per Regu: Form 01 + Form 02
            $patrols = $scoutGroup->patrols;
            $totalPatrols = max(1, $patrols->count());
            foreach ($patrols as $pIdx => $patrol) {
                $patrol->setRelation('scoutGroup', $scoutGroup);
                $genderLabel = $patrol->isBoys() ? 'Putra' : 'Putri';
                $patrolSlug  = $this->slugify($patrol->patrol_name ?? $genderLabel);
                $folderRegu  = "02_Regu_{$genderLabel}_{$patrolSlug}";

                $pct = 30 + (int) (($pIdx / $totalPatrols) * 35);
                $send($pct, "Mencetak FORM 01 Regu {$patrol->patrol_name} ({$genderLabel})...");

                try {
                    $chaperone = $patrol->chaperone;
                    $orderedParticipants = $patrol->orderedParticipants();
                    $form01Pdf = $this->pdf->render('portal.scout-group.patrols.form-01', [
                        'scoutGroup'          => $scoutGroup,
                        'patrol'              => $patrol,
                        'chaperone'           => $chaperone,
                        'orderedParticipants' => $orderedParticipants,
                    ]);
                    $zip->addFromString("{$folderRegu}/Form_01_Rekap_Regu_{$patrolSlug}.pdf", $form01Pdf);
                } catch (\Throwable $e) {
                    Log::warning("Gagal render Form 01 [{$patrolSlug}]: " . $e->getMessage());
                }

                $participants = $patrol->orderedParticipants();
                $totalParts = max(1, $participants->count());
                foreach ($participants as $idx => $participant) {
                    $participant->setRelation('patrol', $patrol);
                    $no            = str_pad((string) ($idx + 1), 2, '0', STR_PAD_LEFT);
                    $namaSlug      = $this->slugify($participant->full_name);
                    $folderPeserta = "{$folderRegu}/Peserta_{$no}_{$namaSlug}";

                    $partPct = $pct + (int) ((($idx + 1) / $totalParts) * (35 / $totalPatrols));
                    $send(min(75, $partPct), "Mencetak FORM 02 Biodata: {$participant->full_name}...");

                    try {
                        $form02Pdf = $this->pdf->render('portal.scout-group.participants.print', [
                            'scoutGroup'  => $scoutGroup,
                            'participant' => $participant,
                        ]);
                        $zip->addFromString("{$folderPeserta}/Form_02_Biodata_{$namaSlug}.pdf", $form02Pdf);
                    } catch (\Throwable $e) {
                        Log::warning("Gagal render Form 02 [{$namaSlug}]: " . $e->getMessage());
                    }

                    $docs = [
                        'photo_path'                  => 'Pas_Foto',
                        'doc_kta_path'                => 'KTA',
                        'doc_bpjs_path'               => 'BPJS',
                        'doc_health_certificate_path' => 'Surat_Kesehatan',
                        'doc_birth_certificate_path'  => 'Akta_Kelahiran',
                        'doc_family_card_path'        => 'Kartu_Keluarga',
                        'doc_sku_path'                => 'SKU',
                        'doc_skk_path'                => 'SKK',
                    ];
                    foreach ($docs as $field => $label) {
                        $this->addStorageFile(
                            $zip,
                            $participant->{$field},
                            "{$folderPeserta}/{$label}_{$namaSlug}." . $this->ext($participant->{$field})
                        );
                    }
                }
            }

            // 4. Form 03 tiap bindamping
            foreach ($scoutGroup->chaperones as $idx => $chaperone) {
                $chaperone->setRelation('scoutGroup', $scoutGroup);
                $no          = str_pad((string) ($idx + 1), 2, '0', STR_PAD_LEFT);
                $namaSlug    = $this->slugify($chaperone->full_name);
                $genderLabel = $chaperone->gender === 'female' ? 'Putri' : 'Putra';
                $folderBinda = "03_Bindamping_{$no}_{$genderLabel}_{$namaSlug}";

                $send(80 + ($idx * 7), "Mencetak FORM 03 Biodata Bindamping: {$chaperone->full_name}...");

                try {
                    $form03Pdf = $this->pdf->render('portal.scout-group.chaperones.print', [
                        'scoutGroup' => $scoutGroup,
                        'chaperone'  => $chaperone,
                    ]);
                    $zip->addFromString("{$folderBinda}/Form_03_Biodata_{$namaSlug}.pdf", $form03Pdf);
                } catch (\Throwable $e) {
                    Log::warning("Gagal render Form 03 [{$namaSlug}]: " . $e->getMessage());
                }

                $this->addStorageFile(
                    $zip,
                    $chaperone->photo_path,
                    "{$folderBinda}/Pas_Foto_{$namaSlug}." . $this->ext($chaperone->photo_path)
                );
            }

            // 5. Finalisasi ZIP
            $send(95, 'Mengompresi dan memfinalisasi arsip .ZIP...');
            $zip->close();

            $filename = "Berkas_Kontingen_{$schoolSlug}_" . now()->format('Ymd_His') . ".zip";
            $token = Str::random(40);
            Cache::put("zip_download_{$token}", [
                'path' => $zipPath,
                'filename' => $filename,
            ], now()->addMinutes(15));

            $downloadUrl = route('admin.scout-groups.download-zip-file', ['token' => $token]);
            $send(100, 'Arsip berkas selesai disiapkan! Mengunduh...', $downloadUrl, 'completed');
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-transform',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Unduh file ZIP yang telah selesai dibuat oleh streaming progress
     */
    public function downloadZipFile(string $token): Response|RedirectResponse
    {
        $cached = Cache::pull("zip_download_{$token}");
        if (! $cached || ! file_exists($cached['path'])) {
            return redirect()->route('admin.scout-groups.index')->with('error', 'Tautan unduhan telah kedaluwarsa. Silakan unduh ulang.');
        }

        return response()->download($cached['path'], $cached['filename'], [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Pembuatan ZIP merender puluhan PDF; batas waktu 30 detik bawaan PHP tidak mencukupi
     */
    private function liftLimits(): void
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');
        ignore_user_abort(true);
    }

    /**
     * Tambahkan file dari disk public ke dalam ZIP; lewati jika tidak ada
     */
    private function addStorageFile(ZipArchive $zip, ?string $path, string $zipEntry): void
    {
        if (! $path) {
            return;
        }
        if (! Storage::disk('public')->exists($path)) {
            return;
        }
        try {
            $content = Storage::disk('public')->get($path);
            $zip->addFromString($zipEntry, $content);
        } catch (\Throwable $e) {
            Log::warning("Gagal menyisipkan berkas [{$path}] ke arsip ZIP: " . $e->getMessage());
        }
    }

    /**
     * Ekstensi file dari path; default 'bin' jika tidak ada
     */
    private function ext(?string $path): string
    {
        if (! $path) {
            return 'bin';
        }
        return pathinfo($path, PATHINFO_EXTENSION) ?: 'bin';
    }

    /**
     * Ubah string menjadi slug aman sebagai nama file/folder ZIP
     */
    private function slugify(string $text): string
    {
        $text = preg_replace('/[^\w\s\-]/u', '', $text);
        $text = preg_replace('/[\s\-]+/', '_', trim($text));

        return substr($text, 0, 60) ?: 'dokumen';
    }
}

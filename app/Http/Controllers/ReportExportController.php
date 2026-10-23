<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\PdfService;
use App\Services\ReportExportService;
use App\Services\StandingsService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    public function __construct(
        protected ReportExportService $exportService,
        protected StandingsService $standingsService
    ) {}

    /**
     * Unduh CSV Rekapitulasi Kontingen & Gugus Depan
     */
    public function contingentsCsv(): StreamedResponse
    {
        return $this->exportService->exportContingentsCsv();
    }

    /**
     * Unduh CSV Buku Induk Peserta
     */
    public function participantsCsv(): StreamedResponse
    {
        return $this->exportService->exportParticipantsCsv();
    }

    /**
     * Unduh CSV Rekapitulasi Presensi & Kehadiran Pos
     */
    public function attendanceCsv(): StreamedResponse
    {
        return $this->exportService->exportAttendanceCsv();
    }

    /**
     * Unduh CSV Rekapitulasi Nilai Seluruh Cabang Lomba
     */
    public function scoresCsv(): StreamedResponse
    {
        return $this->exportService->exportScoresCsv();
    }

    /**
     * Unduh CSV Klasemen Akhir Akumulasi Medali
     */
    public function standingsCsv(Request $request): StreamedResponse
    {
        $gender = $request->query('gender', 'all');

        return $this->exportService->exportStandingsCsv($gender);
    }

    /**
     * Pratinjau PDF rekap nilai & regu berprestasi
     */
    public function standingsRecapPreview(): View
    {
        return view('reports.pdf-preview', [
            'title' => 'Rekap Nilai & Regu Berprestasi',
            'pdfUrl' => route('standings.pdf', ['raw' => 1]),
            'downloadUrl' => route('standings.pdf', ['download' => 1]),
            'backUrl' => route('standings'),
        ]);
    }

    /**
     * Berkas PDF rekap nilai & regu berprestasi
     */
    public function standingsRecapPdf(Request $request, PdfService $pdf): Response
    {
        return $pdf->response('reports.standings-recap-pdf', [
            'boysStandings' => $this->topPatrols('boys'),
            'girlsStandings' => $this->topPatrols('girls'),
            'generatedAt' => now()->timezone('Asia/Jakarta'),
        ], 'rekap-regu-berprestasi-'.now()->timezone('Asia/Jakarta')->format('Ymd-His').'.pdf', $request->boolean('download'));
    }

    /**
     * Pratinjau PDF berita acara penetapan hasil akhir
     */
    public function beritaAcaraPreview(): View
    {
        return view('reports.pdf-preview', [
            'title' => 'Berita Acara Penetapan Hasil Akhir & Juara Umum',
            'pdfUrl' => route('admin.reports.pdf.berita-acara', ['raw' => 1]),
            'downloadUrl' => route('admin.reports.pdf.berita-acara', ['download' => 1]),
            'backUrl' => route('admin.reports.index'),
        ]);
    }

    /**
     * Berkas PDF berita acara penetapan hasil akhir
     */
    public function beritaAcaraPdf(Request $request, PdfService $pdf): Response
    {
        return $pdf->response('reports.berita-acara-pdf', [
            'event' => Event::first(),
            'boysStandings' => $this->topPatrols('boys'),
            'girlsStandings' => $this->topPatrols('girls'),
            'generatedAt' => now()->timezone('Asia/Jakarta'),
        ], 'berita-acara-'.now()->timezone('Asia/Jakarta')->format('Ymd-His').'.pdf', $request->boolean('download'));
    }

    /**
     * Regu berprestasi Tinggi, Baik, dan Cukup (peringkat 1-3) pada satu kategori
     */
    private function topPatrols(string $gender): \Illuminate\Support\Collection
    {
        return $this->standingsService->getPatrolStandings($gender)
            ->filter(fn ($st) => $st['rank'] <= 3 && $st['general_rank_title'])
            ->values();
    }
}

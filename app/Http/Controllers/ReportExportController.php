<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\ReportExportService;
use App\Services\StandingsService;
use Illuminate\Http\Request;
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
     * Pratinjau dan Cetak Resmi Berita Acara Penetapan Pemenang & Klasemen Akhir
     */
    public function officialBeritaAcaraPrint(Request $request): View
    {
        $gender = $request->query('gender', 'all');

        $event = Event::first();
        $standings = $this->standingsService->getContingentStandings($gender);
        $boysStandings = $this->standingsService->getContingentStandings('boys')->take(5);
        $girlsStandings = $this->standingsService->getContingentStandings('girls')->take(5);

        return view('reports.berita-acara', [
            'event' => $event,
            'standings' => $standings,
            'boysStandings' => $boysStandings,
            'girlsStandings' => $girlsStandings,
            'selectedGender' => $gender,
            'generatedAt' => now()->timezone('Asia/Jakarta'),
        ]);
    }
}

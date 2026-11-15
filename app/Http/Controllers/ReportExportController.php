<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\Event;
use App\Services\PdfService;
use App\Services\ReportExportService;
use App\Services\StandingsService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
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
     * Pratinjau PDF rekap nilai & prestasi per mata lomba
     */
    public function competitionRecapPreview(Competition $competition): View
    {
        $this->authorizeCompetitionRecapAccess();

        return view('reports.pdf-preview', [
            'title' => 'Rekap Prestasi: '.$competition->title,
            'pdfUrl' => route('standings.competition.pdf', ['competition' => $competition->id, 'raw' => 1]),
            'downloadUrl' => route('standings.competition.pdf', ['competition' => $competition->id, 'download' => 1]),
            'backUrl' => route('standings', ['tab' => 'competitions', 'comp' => $competition->id]),
        ]);
    }

    /**
     * Berkas PDF rekap nilai & prestasi per mata lomba (Khusus Prestasi Tinggi, Baik, dan Cukup)
     */
    public function competitionRecapPdf(Request $request, Competition $competition, PdfService $pdf): Response
    {
        $this->authorizeCompetitionRecapAccess();

        $canUnmask = auth()->check() && auth()->user()->isAdmin();
        $data = $this->prepareCompetitionRecapData($competition, $canUnmask);

        return $pdf->response('reports.competition-recap-pdf', array_merge($data, [
            'event' => Event::first(),
            'generatedAt' => now()->timezone('Asia/Jakarta'),
        ]), 'rekap-lomba-'.$competition->code.'-'.now()->timezone('Asia/Jakarta')->format('Ymd-His').'.pdf', $request->boolean('download'));
    }

    /**
     * Pratinjau PDF rekap nilai & prestasi seluruh atau pilihan cabang mata lomba (Bulk)
     */
    public function bulkCompetitionRecapPreview(Request $request): View
    {
        $this->authorizeCompetitionRecapAccess();

        $competitionIds = $this->resolveBulkCompetitionIds($request);
        $competitionsParam = implode(',', $competitionIds);

        return view('reports.pdf-preview', [
            'title' => 'Rekap Prestasi Seluruh Cabang Mata Lomba ('.count($competitionIds).' Lomba)',
            'pdfUrl' => route('standings.competitions.bulk.pdf', ['competitions' => $competitionsParam, 'raw' => 1]),
            'downloadUrl' => route('standings.competitions.bulk.pdf', ['competitions' => $competitionsParam, 'download' => 1]),
            'backUrl' => route('standings', ['tab' => 'competitions']),
        ]);
    }

    /**
     * Berkas PDF rekap nilai & prestasi seluruh atau pilihan cabang mata lomba (Bulk)
     */
    public function bulkCompetitionRecapPdf(Request $request, PdfService $pdf): Response
    {
        $this->authorizeCompetitionRecapAccess();

        $competitionIds = $this->resolveBulkCompetitionIds($request);
        $competitions = Competition::with('criteria')
            ->whereIn('id', $competitionIds)
            ->orderBy('code')
            ->get();

        abort_if($competitions->isEmpty(), 404, 'Tidak ada cabang mata lomba yang dipilih.');

        $canUnmask = auth()->check() && auth()->user()->isAdmin();
        $competitionsData = $competitions->map(fn ($comp) => $this->prepareCompetitionRecapData($comp, $canUnmask));

        $filename = 'rekap-seluruh-mata-lomba-'.now()->timezone('Asia/Jakarta')->format('Ymd-His').'.pdf';

        return $pdf->response('reports.competitions-bulk-recap-pdf', [
            'event' => Event::first(),
            'competitionsData' => $competitionsData,
            'title' => 'Rekapitulasi Regu Berprestasi Cabang Mata Lomba',
            'generatedAt' => now()->timezone('Asia/Jakarta'),
        ], $filename, $request->boolean('download'));
    }

    /**
     * Siapkan data rekap regu berprestasi Tinggi, Baik, dan Cukup untuk satu cabang lomba
     */
    protected function prepareCompetitionRecapData(Competition $competition, bool $canUnmask): array
    {
        $scores = $this->standingsService->getCompetitionRecap($competition->id, $canUnmask);

        // Hanya tampilkan 3 prestasi: Tinggi (rank 1), Baik (rank 2), dan Cukup (rank 3)
        $boysScores = $scores
            ->filter(fn ($score) => $score->patrol?->isBoys() && $score->rank && $score->rank <= 3)
            ->sortBy('rank')
            ->values();

        $girlsScores = $scores
            ->filter(fn ($score) => ! $score->patrol?->isBoys() && $score->rank && $score->rank <= 3)
            ->sortBy('rank')
            ->values();

        return [
            'competition' => $competition,
            'boysScores' => $boysScores,
            'girlsScores' => $girlsScores,
            'totalScores' => $scores->count(),
        ];
    }

    /**
     * Parsing dan resolusi ID cabang lomba yang dipilih untuk cetak massal
     */
    protected function resolveBulkCompetitionIds(Request $request): array
    {
        $raw = $request->query('competitions') ?? $request->query('ids');

        if (is_array($raw)) {
            $ids = array_filter($raw);
        } elseif (is_string($raw) && trim($raw) !== '') {
            $ids = array_filter(explode(',', $raw));
        } else {
            // Default: seluruh mata lomba aktif/terbit
            $ids = Competition::query()
                ->where('is_published', true)
                ->orderBy('code')
                ->pluck('id')
                ->all();
        }

        return array_values(array_unique($ids));
    }

    /**
     * Pastikan hak akses pengguna terhadap rekap cabang lomba (mematuhi blind scoring)
     */
    protected function authorizeCompetitionRecapAccess(): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(403);
        }

        if ($user->isAdmin() || $user->isJudge()) {
            return;
        }

        $event = Event::first();
        if ($event?->hide_patrol_identities) {
            abort(403, 'Akses rekapitulasi nilai cabang lomba ditutup selama protokol blind scoring aktif.');
        }
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
    private function topPatrols(string $gender): Collection
    {
        return $this->standingsService->getPatrolStandings($gender)
            ->filter(fn ($st) => $st['rank'] <= 3 && $st['general_rank_title'])
            ->values();
    }
}

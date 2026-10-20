<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Services\AttendanceService;
use App\Services\PdfService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AttendanceScannerController extends Controller
{
    /**
     * Dashboard daftar sesi presensi pos kegiatan LT III
     */
    public function index(Request $request): View
    {
        $query = AttendanceSession::accessibleBy($request->user())
            ->with(['schedule.competition', 'attendances'])
            ->orderByDesc('is_active')
            ->orderBy('title');

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhereHas('schedule', function ($sq) use ($term) {
                        $sq->where('arena_location', 'like', "%{$term}%")
                            ->orWhere('session_name', 'like', "%{$term}%")
                            ->orWhereHas('competition', function ($cq) use ($term) {
                                $cq->where('title', 'like', "%{$term}%");
                            });
                    });
            });
        }

        $sessions = $query->paginate(12)->withQueryString();

        $stats = [
            'total_sessions' => AttendanceSession::accessibleBy($request->user())->count(),
            'active_sessions' => AttendanceSession::accessibleBy($request->user())->where('is_active', true)->count(),
            'total_attendances_today' => Attendance::whereHas('session', fn($q) => $q->accessibleBy($request->user()))->whereDate('scanned_at', today())->count(),
        ];

        return view('portal.attendance.index', compact('sessions', 'stats'));
    }

    /**
     * Antarmuka scanner kamera presensi langsung untuk sesi tertentu
     */
    public function scanner(Request $request, AttendanceSession $session): View
    {
        if (! $session->canBeAccessedBy($request->user())) {
            $message = $request->user()?->isJudge()
                ? 'Akses ditolak. Dewan Juri hanya dapat mengakses scanner presensi pos untuk mata lomba yang ditugaskan kepada Anda.'
                : 'Akses ditolak. Panitia hanya memiliki hak akses untuk presensi kehadiran umum (bukan pos mata lomba).';
            abort(403, $message);
        }

        $session->load([
            'schedule.competition',
            'attendances' => function ($q) {
                $q->with(['participant.patrol.scoutGroup', 'chaperone.scoutGroup', 'user', 'scannedBy'])
                    ->orderByDesc('scanned_at');
            },
        ]);

        $recentAttendances = $session->attendances->take(15);
        $totalAttended = $session->attendances->count();

        return view('portal.attendance.scanner', compact('session', 'recentAttendances', 'totalAttended'));
    }

    /**
     * AJAX Endpoint: Proses pemindaian token QR
     */
    public function scan(Request $request, AttendanceSession $session, AttendanceService $service): JsonResponse
    {
        if (! $session->canBeAccessedBy($request->user())) {
            return response()->json([
                'success' => false,
                'code' => 'UNAUTHORIZED_SESSION',
                'message' => $request->user()?->isJudge()
                    ? 'Anda tidak berwenang memindai presensi untuk mata lomba ini karena tidak ditugaskan sebagai juri.'
                    : 'Panitia hanya memiliki wewenang untuk presensi kehadiran dan bukan mata lomba.',
            ], 403);
        }

        $request->validate([
            'token' => 'required|string',
            'device_info' => 'nullable|string',
        ]);

        $result = $service->recordScan(
            $session,
            $request->token,
            $request->user(),
            $request->device_info
        );

        $statusCode = $result['success'] ? 200 : ($result['code'] === 'ALREADY_SCANNED' ? 409 : 422);

        return response()->json($result, $statusCode);
    }

    /**
     * AJAX Endpoint: Input presensi manual
     */
    public function manual(Request $request, AttendanceSession $session, AttendanceService $service): JsonResponse
    {
        if (! $session->canBeAccessedBy($request->user())) {
            return response()->json([
                'success' => false,
                'code' => 'UNAUTHORIZED_SESSION',
                'message' => 'Anda tidak berwenang melakukan input manual pada sesi ini.',
            ], 403);
        }

        $request->validate([
            'entity_type' => 'required|string|in:participant,chaperone,user',
            'entity_id' => 'required|uuid',
            'notes' => 'nullable|string|max:255',
        ]);

        $result = $service->manualCheckIn(
            $session,
            $request->entity_type,
            $request->entity_id,
            $request->user(),
            $request->notes
        );

        $statusCode = $result['success'] ? 200 : ($result['code'] === 'ALREADY_SCANNED' ? 409 : 422);

        return response()->json($result, $statusCode);
    }

    /**
     * AJAX Endpoint: Cari kandidat untuk input presensi manual
     */
    public function search(Request $request, AttendanceSession $session, AttendanceService $service): JsonResponse
    {
        if (! $session->canBeAccessedBy($request->user())) {
            return response()->json([
                'success' => false,
                'candidates' => [],
            ], 403);
        }

        $request->validate([
            'q' => 'required|string|min:2',
        ]);

        $candidates = $service->searchCandidates($session, $request->q);

        return response()->json([
            'success' => true,
            'candidates' => $candidates,
        ]);
    }

    /**
     * AJAX Endpoint: Dapatkan daftar riwayat presensi terbaru secara realtime
     */
    public function logs(Request $request, AttendanceSession $session): JsonResponse
    {
        if (! $session->canBeAccessedBy($request->user())) {
            return response()->json([
                'success' => false,
                'attendances' => [],
            ], 403);
        }

        $attendances = Attendance::with(['participant.patrol.scoutGroup', 'chaperone.scoutGroup', 'user', 'scannedBy'])
            ->where('attendance_session_id', $session->id)
            ->orderByDesc('scanned_at')
            ->limit(30)
            ->get()
            ->map(function ($att) {
                return [
                    'id' => $att->id,
                    'name' => $att->entity_name,
                    'id_number' => $att->entity_id_number,
                    'role' => $att->entity_role,
                    'group' => $att->entity_group,
                    'photo_url' => $att->entity_photo_url,
                    'scanned_at' => $att->scanned_at->format('H:i:s') . ' WIB',
                    'scanned_by' => $att->scannedBy?->name ?: 'Sistem',
                    'status' => $att->status,
                    'notes' => $att->notes,
                ];
            });

        return response()->json([
            'success' => true,
            'total' => Attendance::where('attendance_session_id', $session->id)->count(),
            'attendances' => $attendances,
        ]);
    }

    /**
     * Perbarui data catatan presensi (status, catatan, dan waktu presensi)
     */
    public function update(Request $request, Attendance $attendance): JsonResponse|RedirectResponse
    {
        if (! $attendance->session?->canBeAccessedBy($request->user())) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak berwenang memperbarui presensi pada sesi ini.',
                ], 403);
            }
            abort(403, 'Anda tidak berwenang memperbarui presensi pada sesi ini.');
        }

        $validated = $request->validate([
            'status' => 'required|string|in:present,late,excused,absent',
            'notes' => 'nullable|string|max:255',
            'scanned_at' => 'nullable|date',
        ]);

        $updateData = [
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? $attendance->notes,
        ];

        if (! empty($validated['scanned_at'])) {
            $updateData['scanned_at'] = \Carbon\Carbon::parse($validated['scanned_at']);
        }

        $attendance->update($updateData);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Data presensi {$attendance->entity_name} berhasil diperbarui.",
                'attendance' => $attendance->fresh(),
            ]);
        }

        return redirect()->back()->with('success', "Data presensi {$attendance->entity_name} berhasil diperbarui.");
    }

    /**
     * Hapus / batalkan catatan presensi jika terjadi kesalahan
     */
    public function destroy(Request $request, Attendance $attendance): JsonResponse|RedirectResponse
    {
        if (! $attendance->session?->canBeAccessedBy($request->user())) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak berwenang membatalkan presensi pada sesi ini.',
                ], 403);
            }
            abort(403, 'Anda tidak berwenang membatalkan presensi pada sesi ini.');
        }

        $sessionId = $attendance->attendance_session_id;
        $entityName = $attendance->entity_name;

        $attendance->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Catatan presensi {$entityName} berhasil dibatalkan.",
            ]);
        }

        return redirect()->back()->with('success', "Catatan presensi {$entityName} berhasil dibatalkan.");
    }

    /**
     * Halaman cetak laporan rekapitulasi presensi (Print View)
     */
    protected function recapData(Request $request): array
    {
        $user = $request->user();

        $query = Attendance::whereHas('session', fn($q) => $q->accessibleBy($user))
            ->with(['session.schedule.competition', 'participant.patrol.scoutGroup', 'chaperone.scoutGroup', 'user', 'scannedBy']);

        if ($request->filled('session_id') && $request->session_id !== 'all') {
            $query->where('attendance_session_id', $request->session_id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('entity_type') && $request->entity_type !== 'all') {
            $query->where('entity_type', $request->entity_type);
        }

        if ($request->filled('date')) {
            $query->whereDate('scanned_at', $request->date);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->whereHas('participant', fn($pq) => $pq->where('full_name', 'like', "%{$term}%")->orWhere('id_number', 'like', "%{$term}%"))
                    ->orWhereHas('chaperone', fn($cq) => $cq->where('full_name', 'like', "%{$term}%")->orWhere('id_number', 'like', "%{$term}%"))
                    ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$term}%")->orWhere('id_number', 'like', "%{$term}%"))
                    ->orWhereHas('patrol', fn($ptq) => $ptq->where('patrol_name', 'like', "%{$term}%"))
                    ->orWhere('notes', 'like', "%{$term}%");
            });
        }

        $attendances = $query->orderBy('scanned_at')->get();

        $selectedSession = $request->filled('session_id') && $request->session_id !== 'all'
            ? AttendanceSession::with('schedule.competition')->find($request->session_id)
            : null;

        $stats = [
            'total' => $attendances->count(),
            'present' => $attendances->where('status', 'present')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'excused' => $attendances->where('status', 'excused')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
        ];

        $event = \App\Models\Event::where('is_active', true)->first() ?? \App\Models\Event::first();

        // Satu lembar per sesi presensi. Pada pilihan "seluruh sesi", semua sesi yang dapat diakses
        // ditampilkan terpisah, termasuk sesi yang belum memiliki catatan presensi.
        $sessions = $selectedSession
            ? collect([$selectedSession])
            : AttendanceSession::accessibleBy($user)->with('schedule.competition')->orderBy('opened_at')->orderBy('title')->get();

        $grouped = $attendances->groupBy('attendance_session_id');

        // Nomor urut dokumen mengikuti urutan sesi (stabil, tidak bergantung pada filter atau sesi yang dipilih)
        $sessionOrder = AttendanceSession::orderBy('opened_at')->orderBy('title')->pluck('id')->flip();
        $year = now()->timezone('Asia/Jakarta')->year;

        $sections = $sessions->map(function (AttendanceSession $session) use ($grouped, $sessionOrder, $year) {
            $rows = ($grouped->get($session->id) ?? collect())->values();

            return [
                'session' => $session,
                'number' => sprintf('BAP-LT3/KWARCAB-PSP/%d/%03d', $year, ($sessionOrder[$session->id] ?? 0) + 1),
                'attendances' => $rows,
                'stats' => [
                    'total' => $rows->count(),
                    'present' => $rows->where('status', 'present')->count(),
                    'late' => $rows->where('status', 'late')->count(),
                    'excused' => $rows->where('status', 'excused')->count(),
                    'absent' => $rows->where('status', 'absent')->count(),
                ],
            ];
        })->values();

        if ($sections->isEmpty()) {
            $sections = collect([['session' => null, 'number' => sprintf('BAP-LT3/KWARCAB-PSP/%d/001', $year), 'attendances' => $attendances, 'stats' => $stats]]);
        }

        return compact('attendances', 'selectedSession', 'stats', 'event', 'sections');
    }

    /**
     * Halaman pratinjau PDF rekapitulasi presensi dengan tombol unduh
     */
    public function previewRecap(Request $request): View
    {
        $query = $request->query();

        return view('reports.pdf-preview', [
            'title' => 'Rekapitulasi Presensi & Kehadiran',
            'pdfUrl' => route('attendance.recap.pdf', $query),
            'downloadUrl' => route('attendance.recap.pdf', array_merge($query, ['download' => 1])),
            'backUrl' => route('attendance.recap'),
        ]);
    }

    /**
     * Berkas PDF rekapitulasi presensi (A4 portrait): tampil di pratinjau, atau diunduh dengan ?download=1
     */
    public function pdfRecap(Request $request, PdfService $pdf): Response
    {
        return $pdf->response(
            'portal.attendance.recap-pdf',
            $this->recapData($request),
            'rekap-presensi-'.now()->setTimezone('Asia/Jakarta')->format('Ymd-His').'.pdf',
            $request->boolean('download'),
        );
    }
}


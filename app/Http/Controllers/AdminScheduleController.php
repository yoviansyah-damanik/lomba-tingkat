<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Models\AttendanceSession;
use App\Models\Competition;
use App\Models\CompetitionSchedule;
use App\Models\JudgeAssignment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminScheduleController extends Controller
{
    /**
     * Tampilkan daftar seluruh jadwal sesi pelaksanaan lomba
     */
    public function index(Request $request): View
    {
        $query = CompetitionSchedule::with(['competition', 'judges', 'attendanceSessions'])->orderBy('start_time');

        // Filter berdasarkan cabang lomba
        if ($request->filled('competition_id')) {
            $query->where('competition_id', $request->competition_id);
        }

        // Filter berdasarkan status sesi
        if ($request->filled('status') && $request->status !== 'all_status') {
            $query->where('status', $request->status);
        }

        // Filter lokasi arena pos
        if ($request->filled('arena')) {
            $query->where('arena_location', 'like', "%{$request->arena}%");
        }

        // Filter kategori gender
        if ($request->filled('gender') && $request->gender !== 'all') {
            $query->where('gender_category', $request->gender);
        }

        $schedules = $query->paginate(15)->withQueryString();
        $competitions = Competition::orderBy('title')->get();

        $stats = [
            'total' => CompetitionSchedule::count(),
            'scheduled' => CompetitionSchedule::where('status', 'scheduled')->count(),
            'in_progress' => CompetitionSchedule::where('status', 'in_progress')->count(),
            'completed' => CompetitionSchedule::where('status', 'completed')->count(),
        ];

        return view('admin.schedules.index', compact('schedules', 'competitions', 'stats'));
    }

    /**
     * Tampilkan formulir tambah jadwal sesi lomba baru
     */
    public function create(): View
    {
        $competitions = Competition::published()->get();
        return view('admin.schedules.create', compact('competitions'));
    }

    /**
     * Simpan jadwal lomba baru ke database
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'competition_id' => 'required|exists:competitions,id',
            'session_name' => 'required|string|max:255',
            'gender_category' => 'nullable|in:boys,girls,all',
            'participant_type' => 'nullable|in:patrol,group,individual',
            'arena_location' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'status' => 'required|in:scheduled,in_progress,completed,rescheduled,cancelled',
            'is_published' => 'nullable|boolean',
        ]);

        $validated['is_published'] = $request->boolean('is_published', true);
        $validated['gender_category'] = $validated['gender_category'] ?? 'all';

        $schedule = CompetitionSchedule::create($validated);
        $schedule->loadMissing('competition');
        $competitionTitle = $schedule->competition?->title ?: 'Lomba';

        $genderSuffix = $schedule->gender_category === 'boys' ? ' [Putra]' : ($schedule->gender_category === 'girls' ? ' [Putri]' : '');

        // Pastikan sesi presensi aktif dibuat
        $schedule->attendanceSessions()->firstOrCreate(
            ['competition_schedule_id' => $schedule->id],
            [
                'session_type' => 'participant',
                'title' => 'Presensi: ' . $competitionTitle . ' (' . $schedule->session_name . ')' . $genderSuffix,
                'is_competition' => true,
                'opened_at' => $schedule->checkin_open_time,
                'closed_at' => $schedule->checkin_close_time,
                'is_active' => true,
            ]
        );

        return redirect()->route('admin.schedules.edit', $schedule)
            ->with('status', "Jadwal sesi {$schedule->session_name} berhasil dibuat. Silakan atur penugasan dewan juri.");
    }

    /**
     * Tampilkan formulir edit jadwal, penugasan juri, dan sesi presensi pos
     */
    public function edit(CompetitionSchedule $schedule): View
    {
        $schedule->load(['competition.criteria', 'judgeAssignments.judge', 'attendanceSessions']);
        $competitions = Competition::orderBy('title')->get();

        // Ambil aparatur yang memiliki peran Juri
        $availableJudges = User::whereHas('roles', function ($q) {
            $q->whereIn('name', [RoleEnum::Judge->value, RoleEnum::Committee->value]);
        })->where('is_active', true)->orderBy('name')->get();

        return view('admin.schedules.edit', compact('schedule', 'competitions', 'availableJudges'));
    }

    /**
     * Perbarui jadwal sesi lomba
     */
    public function update(Request $request, CompetitionSchedule $schedule): RedirectResponse
    {
        $validated = $request->validate([
            'competition_id' => 'required|exists:competitions,id',
            'session_name' => 'required|string|max:255',
            'gender_category' => 'nullable|in:boys,girls,all',
            'participant_type' => 'nullable|in:patrol,group,individual',
            'arena_location' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'status' => 'required|in:scheduled,in_progress,completed,rescheduled,cancelled',
            'is_published' => 'nullable|boolean',
        ]);

        $validated['is_published'] = $request->boolean('is_published', true);
        if (isset($validated['gender_category'])) {
            $validated['gender_category'] = $validated['gender_category'] ?: 'all';
        }

        $schedule->update($validated);

        return back()->with('status', "Jadwal {$schedule->session_name} berhasil diperbarui.");
    }

    /**
     * Hapus jadwal sesi lomba
     */
    public function destroy(CompetitionSchedule $schedule): RedirectResponse
    {
        $name = $schedule->session_name;
        $schedule->delete();

        return redirect()->route('admin.schedules.index')
            ->with('status', "Jadwal {$name} berhasil dihapus.");
    }

    /**
     * Tugaskan juri ke sesi jadwal lomba
     */
    public function assignJudge(Request $request, CompetitionSchedule $schedule): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:lead_judge,scoring_judge,companion_judge',
            'notes' => 'nullable|string|max:255',
        ]);

        // Cek apakah juri sudah pernah ditugaskan di sesi ini
        $existing = JudgeAssignment::where('competition_schedule_id', $schedule->id)
            ->where('user_id', $validated['user_id'])
            ->first();

        if ($existing) {
            $existing->update([
                'role' => $validated['role'],
                'notes' => $validated['notes'] ?? $existing->notes,
            ]);

            return back()->with('status', 'Penugasan peran juri berhasil diperbarui.');
        }

        $schedule->judgeAssignments()->create([
            'user_id' => $validated['user_id'],
            'role' => $validated['role'],
            'is_confirmed' => true,
            'notes' => $validated['notes'] ?? null,
        ]);

        $judgeName = User::find($validated['user_id'])->name;

        return back()->with('status', "Juri {$judgeName} berhasil ditugaskan pada sesi {$schedule->session_name}.");
    }

    /**
     * Lepas penugasan juri dari jadwal sesi lomba
     */
    public function removeJudge(CompetitionSchedule $schedule, JudgeAssignment $judgeAssignment): RedirectResponse
    {
        $judgeName = $judgeAssignment->judge?->name ?? 'Juri';
        $judgeAssignment->delete();

        return back()->with('status', "Penugasan juri {$judgeName} pada sesi ini telah dibatalkan.");
    }

    /**
     * Buka / Tutup Sesi Presensi Pos Lomba
     */
    public function toggleAttendanceSession(Request $request, CompetitionSchedule $schedule): RedirectResponse
    {
        $session = $schedule->attendanceSessions()->firstOrCreate(
            ['session_type' => 'participant'],
            [
                'title' => 'Presensi Peserta ' . $schedule->session_name,
                'is_competition' => true,
                'opened_at' => $schedule->attendance_open_time ?: now(),
                'closed_at' => $schedule->attendance_close_time ?: now()->addHours(2),
            ]
        );

        $newStatus = ! $session->is_active;

        $session->update([
            'is_active' => $newStatus,
            'opened_at' => $newStatus ? ($schedule->attendance_open_time ?: now()) : $session->opened_at,
            'closed_at' => ! $newStatus ? now()->setTimezone('Asia/Jakarta') : $schedule->attendance_close_time,
        ]);

        if ($schedule->isAttendanceOpen()) {
            $msg = $newStatus 
                ? "Sesi presensi pos dibuka kembali (Aktif Scan Pos)."
                : "Sesi presensi pos ditutup sementara saat waktu lomba.";
        } elseif ($schedule->isAttendanceNotOpenYet()) {
            $msg = $newStatus 
                ? "Sesi presensi pos diaktifkan (akan otomatis terbuka H-30 menit sebelum lomba)."
                : "Sesi presensi pos dikunci / dinonaktifkan.";
        } else {
            $msg = $newStatus 
                ? "Sesi presensi susulan dibuka."
                : "Sesi presensi pos telah ditutup.";
        }

        return back()->with('status', $msg);
    }
}

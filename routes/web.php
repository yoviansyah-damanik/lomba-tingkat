<?php

use App\Http\Controllers\PublicPageController;
use Illuminate\Support\Facades\Route;

// Public Information & Landing Routes (Modul 1 - Livewire 4)
Route::get('/', [PublicPageController::class, 'home'])->name('home');
Route::get('/announcements', \App\Livewire\Public\AnnouncementsList::class)->name('announcements');
Route::get('/announcements/{slug}', [PublicPageController::class, 'announcementShow'])->name('announcements.show');
Route::get('/competitions', \App\Livewire\Public\CompetitionsCatalog::class)->name('competitions');
Route::get('/schedules', \App\Livewire\Public\SchedulesCalendar::class)->name('schedules');
Route::get('/guide', \App\Livewire\Public\GuidePage::class)->name('guide');
Route::get('/leaderboard', \App\Livewire\Public\LeaderboardPage::class)->name('leaderboard');
Route::get('/participants', \App\Livewire\Public\ParticipantsPage::class)->name('participants');

use App\Http\Controllers\AdminCompetitionController;
use App\Http\Controllers\AdminOfficialController;
use App\Http\Controllers\AdminScheduleController;
use App\Http\Controllers\AdminVerificationController;
use App\Http\Controllers\AttendanceScannerController;
use App\Http\Controllers\Auth\ScoutGroupRegistrationController;
use App\Http\Controllers\DigitalCardController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\ScoutGroupPortalController;

// Phase 9: Web Push Subscriptions & VAPID API
Route::get('/api/push/vapid-public-key', [PushSubscriptionController::class, 'vapidPublicKey'])->name('push.vapid');
Route::post('/api/push-subscriptions', [PushSubscriptionController::class, 'subscribe'])->name('push.subscribe');
Route::post('/api/push-subscriptions/test', [PushSubscriptionController::class, 'test'])->middleware('throttle:5,1')->name('push.test');
Route::delete('/api/push-subscriptions', [PushSubscriptionController::class, 'unsubscribe'])->name('push.unsubscribe');

// Public Digital Card QR Verification Endpoint
Route::get('/digital-cards/verify/{token}', [DigitalCardController::class, 'verify'])->name('digital-cards.verify');

// Public Registration Routes for Scout Group (Pangkalan Sekolah)
Route::middleware('guest')->group(function () {
    Route::get('/register/scout-group', [ScoutGroupRegistrationController::class, 'create'])->name('register.scout-group');
    Route::post('/register/scout-group', [ScoutGroupRegistrationController::class, 'store'])->name('register.scout-group.store');
});

Route::middleware(['auth', 'role'])->group(function () {
    Route::get('dashboard', \App\Livewire\Dashboard::class)->name('dashboard');
    Route::get('notifications', \App\Livewire\Notifications\Index::class)->name('notifications.index');
    Route::get('portal/agenda', \App\Livewire\Portal\UpcomingSchedules::class)->name('agenda.index');
    Route::get('portal/team-assessment', \App\Livewire\Portal\TeamAssessment::class)->middleware('role:participant|chaperone|scout_group')->name('team-assessment.index');

    // Digital Credentials / ID Cards
    Route::get('/digital-cards/participant/{id}', [DigitalCardController::class, 'participantCard'])->name('digital-cards.participant');
    Route::get('/digital-cards/chaperone/{id}', [DigitalCardController::class, 'chaperoneCard'])->name('digital-cards.chaperone');
    Route::get('/digital-cards/staff/{id}', [DigitalCardController::class, 'staffCard'])->name('digital-cards.staff');
    Route::get('/digital-cards/patrol/{patrolId}/batch', [DigitalCardController::class, 'patrolBatchCards'])->name('digital-cards.patrol.batch');
    Route::get('/digital-cards/scout-group/{scoutGroupId}/chaperones/batch', [DigitalCardController::class, 'chaperoneBatchCards'])->name('digital-cards.chaperone.batch');
    Route::get('/digital-cards/scout-group/{scoutGroupId}/batch', [DigitalCardController::class, 'scoutGroupBatchCards'])->name('digital-cards.scout-group.batch');

    // Shared routes: Akses Peserta, Bindamping, dan Gugusdepan (Admin, Panitia, Juri)
    Route::middleware('role:admin|committee|judge')->prefix('admin')->name('admin.')->group(function () {
        // Direktori & Data Kontingen Gugus Depan
        Route::get('/scout-groups', \App\Livewire\Admin\VerificationManagement::class)->name('scout-groups.index');
        Route::get('/scout-groups/{id}', [AdminVerificationController::class, 'show'])->name('scout-groups.show');
        Route::get('/scout-groups/{scoutGroupId}/chaperones/{chaperoneId}/print', [AdminVerificationController::class, 'chaperonePrint'])->name('scout-groups.chaperones.print');
        Route::get('/scout-groups/{scoutGroupId}/participants/{participantId}/print', [AdminVerificationController::class, 'participantPrint'])->name('scout-groups.participants.print');
        Route::get('/scout-groups/{scoutGroupId}/patrols/{patrolId}/form-01', [AdminVerificationController::class, 'form01Print'])->name('scout-groups.patrols.form-01');
        Route::get('/scout-groups/{scoutGroupId}/patrols/{patrolId}/id-cards', [DigitalCardController::class, 'adminPatrolBatchCards'])->name('scout-groups.patrols.id-cards');
        Route::get('/scout-groups/{scoutGroupId}/chaperones/id-cards', [DigitalCardController::class, 'chaperoneBatchCards'])->name('scout-groups.chaperones.id-cards');
        Route::get('/scout-groups/{scoutGroupId}/id-cards', [DigitalCardController::class, 'scoutGroupBatchCards'])->name('scout-groups.id-cards');

        // Direktori Peserta Terverifikasi (Livewire 4 SPA)
        Route::get('/participants', \App\Livewire\Admin\ParticipantsDirectory::class)->name('participants.index');

        // Direktori Pembina Pendamping Terverifikasi (Livewire 4 SPA)
        Route::get('/chaperones', \App\Livewire\Admin\ChaperonesDirectory::class)->name('chaperones.index');
    });

    // Admin routes group (Full management, registration verification actions, officials, competitions, schedules)
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', fn () => redirect()->route('dashboard'))->name('dashboard');

        // Tindakan Keputusan Verifikasi Kontingen
        Route::post('/scout-groups/{id}/verify', [AdminVerificationController::class, 'verify'])->name('scout-groups.verify');
        Route::post('/scout-groups/{id}/revision', [AdminVerificationController::class, 'requestRevision'])->name('scout-groups.revision');
        Route::post('/scout-groups/{id}/reject', [AdminVerificationController::class, 'reject'])->name('scout-groups.reject');

        // Phase 5: Manajemen Mata Lomba & Rubrik Penilaian (Livewire 4 SPA)
        Route::get('/competitions', \App\Livewire\Admin\CompetitionsManagement::class)->name('competitions.index');
        Route::resource('competitions', AdminCompetitionController::class)->except(['index']);
        Route::post('/competitions/{competition}/criteria', [AdminCompetitionController::class, 'storeCriterion'])->name('competitions.criteria.store');
        Route::delete('/competitions/criteria/{criterion}', [AdminCompetitionController::class, 'destroyCriterion'])->name('competitions.criteria.destroy');

        // Phase 5: Manajemen Jadwal Lomba, Penugasan Juri & Sesi Presensi Pos (Livewire 4 SPA)
        Route::get('/schedules', \App\Livewire\Admin\SchedulesManagement::class)->name('schedules.index');
        Route::resource('schedules', AdminScheduleController::class)->except(['index']);
        Route::post('/schedules/{schedule}/judges', [AdminScheduleController::class, 'assignJudge'])->name('schedules.judges.assign');
        Route::delete('/schedules/{schedule}/judges/{judgeAssignment}', [AdminScheduleController::class, 'removeJudge'])->name('schedules.judges.remove');
        Route::post('/schedules/{schedule}/sessions/toggle', [AdminScheduleController::class, 'toggleAttendanceSession'])->name('schedules.sessions.toggle');

        // Rekap Nilai & Klasemen Prestasi di Dasbor (Livewire 4 SPA)
        Route::get('/standings', \App\Livewire\Admin\StandingsLeaderboard::class)->name('standings.index');

        // Phase 10: Pusat Laporan, Ekspor CSV & Berita Acara Resmi (Livewire 4 SPA)
        Route::get('/reports', \App\Livewire\Admin\ReportsCenter::class)->name('reports.index');
        Route::get('/reports/export/contingents', [ReportExportController::class, 'contingentsCsv'])->name('reports.export.contingents');
        Route::get('/reports/export/participants', [ReportExportController::class, 'participantsCsv'])->name('reports.export.participants');
        Route::get('/reports/export/attendance', [ReportExportController::class, 'attendanceCsv'])->name('reports.export.attendance');
        Route::get('/reports/export/scores', [ReportExportController::class, 'scoresCsv'])->name('reports.export.scores');
        Route::get('/reports/export/standings', [ReportExportController::class, 'standingsCsv'])->name('reports.export.standings');
        Route::get('/reports/print/berita-acara', [ReportExportController::class, 'beritaAcaraPreview'])->name('reports.print.berita-acara');
        Route::get('/reports/pdf/berita-acara', [ReportExportController::class, 'beritaAcaraPdf'])->name('reports.pdf.berita-acara');

        // Phase 11: Manajemen Publikasi Pengumuman (Livewire 4 SPA)
        Route::get('/announcements', \App\Livewire\Admin\AnnouncementsManagement::class)->name('announcements.index');
    });

    // Superadmin: aparatur, audit penilaian, tata kelola pengguna & peran, konfigurasi sistem
    Route::middleware('role:superadmin')->prefix('admin')->name('admin.')->group(function () {
        // Aparatur Panitia & Dewan Juri (Livewire 4 SPA)
        Route::get('/officials', \App\Livewire\Admin\OfficialsManagement::class)->name('officials.index');
        Route::resource('officials', AdminOfficialController::class)->except(['show', 'index']);

        // Audit Penilaian Juri & Jejak Rekam Sistem (Livewire 4 SPA)
        Route::get('/assessments', \App\Livewire\Admin\AssessmentsAudit::class)->name('assessments.index');

        // Tata Kelola Pengguna & Peran (Livewire 4 SPA)
        Route::get('/users', \App\Livewire\Admin\UsersManagement::class)->name('users.index');

        // Siaran Notifikasi PWA & Pengumuman Kilat (Livewire 4 SPA)
        Route::get('/push-broadcast', \App\Livewire\Admin\PushBroadcastManagement::class)->name('push-broadcast.index');

        // Konfigurasi Sistem & Master Kegiatan (Livewire 4 SPA)
        Route::get('/settings/system', \App\Livewire\Admin\SystemSettingsManagement::class)->name('settings.system');
    });

    // Akses Dasbor Terpadu: Rekap Nilai & Klasemen Resmi
    Route::get('/standings', \App\Livewire\Admin\StandingsLeaderboard::class)->name('standings');
    Route::get('/standings/print', [ReportExportController::class, 'standingsRecapPreview'])->name('standings.print');
    Route::get('/standings/pdf', [ReportExportController::class, 'standingsRecapPdf'])->name('standings.pdf');

    // Committee routes group (field operational monitoring)
    Route::middleware('role:committee')->prefix('committee')->name('committee.')->group(function () {
        Route::get('/dashboard', fn () => redirect()->route('dashboard'))->name('dashboard');
    });

    // Phase 6: Portal Presensi & Scanner QR Code (Admin, Panitia, & Dewan Juri - Livewire 4 SPA)
    Route::middleware(['auth', 'role:admin|committee|judge'])->prefix('portal/attendance')->name('attendance.')->group(function () {
        Route::get('/', \App\Livewire\Portal\AttendanceDashboard::class)->name('index');
        Route::get('/recap', \App\Livewire\Portal\AttendanceRecap::class)->name('recap');
        Route::get('/recap/preview', [AttendanceScannerController::class, 'previewRecap'])->name('recap.preview');
        Route::get('/recap/pdf', [AttendanceScannerController::class, 'pdfRecap'])->name('recap.pdf');
        Route::get('/scanner/{session}', [AttendanceScannerController::class, 'scanner'])->name('scanner');
        Route::post('/scan/{session}', [AttendanceScannerController::class, 'scan'])->name('scan');
        Route::post('/manual/{session}', [AttendanceScannerController::class, 'manual'])->name('manual');
        Route::get('/search/{session}', [AttendanceScannerController::class, 'search'])->name('search');
        Route::get('/logs/{session}', [AttendanceScannerController::class, 'logs'])->name('logs');
        Route::put('/records/{attendance}', [AttendanceScannerController::class, 'update'])->name('update');
        Route::delete('/records/{attendance}', [AttendanceScannerController::class, 'destroy'])->name('destroy');
    });

    // Judge legacy convenience route
    Route::middleware('role:judge')->prefix('judge')->group(function () {
        Route::get('/dashboard', fn () => redirect()->route('dashboard'))->name('judge.dashboard.redirect');
    });

    // Phase 7: Portal Penjurian & Blind Scoring Cockpit (Dewan Juri & Admin - Livewire 4 SPA)
    Route::middleware(['auth', 'role:judge|admin'])->prefix('portal/judge')->name('judge.')->group(function () {
        Route::get('/', \App\Livewire\Portal\JudgeCockpit::class)->name('index');
        Route::get('/dashboard', fn () => redirect()->route('judge.index'))->name('dashboard');
        Route::get('/scoring/{scheduleId}', \App\Livewire\Portal\JudgeScoring::class)->name('scoring');
    });

    // Scout Group contingent management group
    Route::middleware('role:scout_group')->prefix('portal/scout-group')->name('scout-group.')->group(function () {
        Route::get('/dashboard', fn () => redirect()->route('scout-group.index'))->name('dashboard');
        Route::get('/', \App\Livewire\Portal\ScoutGroupDashboard::class)->name('index');
        Route::get('/profile', [ScoutGroupPortalController::class, 'profile'])->name('profile');
        Route::post('/profile', [ScoutGroupPortalController::class, 'profileUpdate'])->name('profile.update');

        // Patrols & participants (Livewire 4 SPA)
        Route::get('/patrols', \App\Livewire\Portal\PatrolsManagement::class)->name('patrols');
        Route::post('/patrols', [ScoutGroupPortalController::class, 'patrolStore'])->name('patrols.store');
        Route::delete('/patrols/{patrolId}', [ScoutGroupPortalController::class, 'patrolDestroy'])->name('patrols.destroy');
        Route::get('/patrols/{patrolId}/form-01', [ScoutGroupPortalController::class, 'form01Print'])->name('patrols.form-01');
        Route::get('/patrols/{patrolId}/participants/create', [ScoutGroupPortalController::class, 'participantCreate'])->name('participants.create');
        Route::post('/patrols/{patrolId}/participants', [ScoutGroupPortalController::class, 'participantStore'])->name('participants.store');
        Route::get('/participants/{id}/edit', [ScoutGroupPortalController::class, 'participantEdit'])->name('participants.edit');
        Route::put('/participants/{id}', [ScoutGroupPortalController::class, 'participantUpdate'])->name('participants.update');
        Route::get('/participants/{id}/print', [ScoutGroupPortalController::class, 'participantPrint'])->name('participants.print');
        Route::delete('/participants/{id}', [ScoutGroupPortalController::class, 'participantDestroy'])->name('participants.destroy');

        // Chaperones (Bindamping - Livewire 4 SPA)
        Route::get('/chaperones', \App\Livewire\Portal\ChaperonesManagement::class)->name('chaperones');
        Route::get('/chaperones/create', [ScoutGroupPortalController::class, 'chaperoneCreate'])->name('chaperones.create');
        Route::post('/chaperones', [ScoutGroupPortalController::class, 'chaperoneStore'])->name('chaperones.store');
        Route::get('/chaperones/{id}/edit', [ScoutGroupPortalController::class, 'chaperoneEdit'])->name('chaperones.edit');
        Route::put('/chaperones/{id}', [ScoutGroupPortalController::class, 'chaperoneUpdate'])->name('chaperones.update');
        Route::get('/chaperones/{id}/print', [ScoutGroupPortalController::class, 'chaperonePrint'])->name('chaperones.print');
        Route::delete('/chaperones/{id}', [ScoutGroupPortalController::class, 'chaperoneDestroy'])->name('chaperones.destroy');

        // Review & Final Submit (Livewire 4 SPA)
        Route::get('/review', \App\Livewire\Portal\ReviewRegistration::class)->name('review');
        Route::post('/submit', [ScoutGroupPortalController::class, 'submit'])->name('submit');
    });

    // Participant digital access group
    Route::middleware('role:participant')->prefix('portal/participant')->name('participant.')->group(function () {
        Route::get('/dashboard', fn () => redirect()->route('dashboard'))->name('dashboard');
    });
});

require __DIR__.'/settings.php';

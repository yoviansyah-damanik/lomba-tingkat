<?php

use App\Http\Controllers\AdminVerificationController;
use App\Http\Controllers\AttendanceScannerController;
use App\Http\Controllers\PermissionLetterController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\RosterPrintController;
use App\Http\Controllers\ScheduleCalendarController;
use App\Http\Controllers\ScoutGroupPortalController;
use App\Http\Controllers\ZipExportController;
use App\Livewire\Admin\AnnouncementsManagement;
use App\Livewire\Admin\AssessmentsAudit;
use App\Livewire\Admin\ChaperoneDetail;
use App\Livewire\Admin\ChaperonesDirectory;
use App\Livewire\Admin\CompetitionsManagement;
use App\Livewire\Admin\IdentityVerification;
use App\Livewire\Admin\NppGeneration;
use App\Livewire\Admin\OfficialsManagement;
use App\Livewire\Admin\ParticipantDetail;
use App\Livewire\Admin\ParticipantsDirectory;
use App\Livewire\Admin\PermissionRequests;
use App\Livewire\Admin\PushBroadcastManagement;
use App\Livewire\Admin\RegistrationInvitations;
use App\Livewire\Admin\ReportsCenter;
use App\Livewire\Admin\SchedulesManagement;
use App\Livewire\Admin\ScoutGroupChaperoneProfile;
use App\Livewire\Admin\ScoutGroupParticipantProfile;
use App\Livewire\Admin\ScoutGroupProfile;
use App\Livewire\Admin\ScoutGroupsDirectory;
use App\Livewire\Admin\ScoutGroupVerificationDetail;
use App\Livewire\Admin\StandingsLeaderboard;
use App\Livewire\Admin\SystemSettingsManagement;
use App\Livewire\Admin\UsersManagement;
use App\Livewire\Admin\VerificationManagement;
use App\Livewire\Auth\InvitationCodeGate;
use App\Livewire\Auth\OfficialRegistrationForm;
use App\Livewire\Auth\RegistrationSuccess;
use App\Livewire\Auth\ScoutGroupRegistrationForm;
use App\Livewire\Dashboard;
use App\Livewire\DigitalCard\ChaperoneBatch;
use App\Livewire\DigitalCard\ChaperoneCard;
use App\Livewire\DigitalCard\ParticipantCard;
use App\Livewire\DigitalCard\PatrolBatch;
use App\Livewire\DigitalCard\ScoutGroupBatch;
use App\Livewire\DigitalCard\StaffCard;
use App\Livewire\Notifications\Index;
use App\Livewire\Portal\AttendanceDashboard;
use App\Livewire\Portal\AttendanceRecap;
use App\Livewire\Portal\AttendanceScanner;
use App\Livewire\Portal\ChaperoneForm;
use App\Livewire\Portal\ChaperonesManagement;
use App\Livewire\Portal\JudgeCockpit;
use App\Livewire\Portal\JudgeScoring;
use App\Livewire\Portal\MyAttendance;
use App\Livewire\Portal\MyDigitalCard;
use App\Livewire\Portal\ParticipantForm;
use App\Livewire\Portal\PatrolsManagement;
use App\Livewire\Portal\ReviewRegistration;
use App\Livewire\Portal\ScoutGroupDashboard;
use App\Livewire\Portal\TeamAssessment;
use App\Livewire\Portal\UpcomingSchedules;
use App\Livewire\Public\AnnouncementShow;
use App\Livewire\Public\AnnouncementsList;
use App\Livewire\Public\CompetitionsCatalog;
use App\Livewire\Public\CredentialVerification;
use App\Livewire\Public\GuidePage;
use App\Livewire\Public\Home;
use App\Livewire\Public\LeaderboardPage;
use App\Livewire\Public\ParticipantsPage;
use App\Livewire\Public\PermissionLetterVerification;
use App\Livewire\Public\SchedulesCalendar;
use Illuminate\Support\Facades\Route;

// Public Information & Landing Routes (Modul 1 - Livewire 4)
Route::get('/', Home::class)->name('home');
Route::get('/announcements', AnnouncementsList::class)->name('announcements');
Route::get('/announcements/{slug}', AnnouncementShow::class)->name('announcements.show');
Route::get('/announcements/{slug}/files/{index}', [PublicPageController::class, 'announcementDownload'])->whereNumber('index')->name('announcements.download');
Route::get('/competitions', CompetitionsCatalog::class)->name('competitions');
Route::get('/schedules', SchedulesCalendar::class)->name('schedules');
Route::get('/schedules/calendar.ics', [ScheduleCalendarController::class, 'all'])->name('schedules.calendar.all');
Route::get('/schedules/{scheduleId}/calendar.ics', [ScheduleCalendarController::class, 'show'])->name('schedules.calendar.show');
Route::get('/guide', GuidePage::class)->name('guide');
Route::get('/leaderboard', LeaderboardPage::class)->name('leaderboard');
Route::get('/participants', ParticipantsPage::class)->name('participants');

// Phase 9: Web Push Subscriptions & VAPID API
Route::get('/api/push/vapid-public-key', [PushSubscriptionController::class, 'vapidPublicKey'])->name('push.vapid');
Route::post('/api/push-subscriptions', [PushSubscriptionController::class, 'subscribe'])->name('push.subscribe');
Route::post('/api/push-subscriptions/test', [PushSubscriptionController::class, 'test'])->middleware('throttle:5,1')->name('push.test');
Route::delete('/api/push-subscriptions', [PushSubscriptionController::class, 'unsubscribe'])->name('push.unsubscribe');

// Public Digital Card QR Verification Endpoint
Route::get('/digital-cards/verify/{token}', CredentialVerification::class)->name('digital-cards.verify');
Route::get('/permission-letters/verify/{id}', PermissionLetterVerification::class)->name('permission-letters.verify');

// Public Registration Routes for Scout Group (Pangkalan Sekolah)
Route::middleware('guest')->group(function () {
    Route::get('/register/scout-group', InvitationCodeGate::class)->name('register.scout-group');
    Route::get('/register/scout-group/form', ScoutGroupRegistrationForm::class)->name('register.scout-group.form');
    Route::get('/register/aparatur/{token}', OfficialRegistrationForm::class)->middleware('throttle:30,1')->name('register.official');
});
Route::get('/register/scout-group/success', RegistrationSuccess::class)->name('register.scout-group.success')->middleware('auth');

Route::middleware(['auth', 'role'])->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');
    Route::get('notifications', Index::class)->name('notifications.index');
    Route::get('portal/agenda', UpcomingSchedules::class)->name('agenda.index');
    Route::get('portal/digital-card', MyDigitalCard::class)->middleware('role:participant|chaperone|committee|judge')->name('digital-card.index');
    Route::get('portal/my-attendance', MyAttendance::class)->middleware('role:participant|chaperone|committee|judge')->name('my-attendance.index');
    Route::get('portal/team-assessment', TeamAssessment::class)->middleware('role:participant|chaperone|scout_group')->name('team-assessment.index');

    // Digital Credentials / ID Cards
    Route::get('/digital-cards/participant/{id}', ParticipantCard::class)->name('digital-cards.participant');
    Route::get('/digital-cards/chaperone/{id}', ChaperoneCard::class)->name('digital-cards.chaperone');
    Route::get('/digital-cards/staff/{id}', StaffCard::class)->name('digital-cards.staff');
    Route::get('/digital-cards/patrol/{patrolId}/batch', PatrolBatch::class)->name('digital-cards.patrol.batch');
    Route::get('/digital-cards/scout-group/{scoutGroupId}/chaperones/batch', ChaperoneBatch::class)->name('digital-cards.chaperone.batch');
    Route::get('/digital-cards/scout-group/{scoutGroupId}/batch', ScoutGroupBatch::class)->name('digital-cards.scout-group.batch');

    // Verifikasi identitas lewat scan QR: Administrator (termasuk Superadmin) dan Panitia
    Route::middleware('role:admin|committee')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/identity-verification', IdentityVerification::class)->name('identity-verification.index');
    });

    // Cetak PDF daftar Pangkalan Gudep, Peserta (satu gudep per halaman), dan Bindamping
    Route::middleware('role:admin|committee')->prefix('admin/print')->name('admin.print.')->group(function () {
        Route::get('/{document}', [RosterPrintController::class, 'preview'])->whereIn('document', ['scout-groups', 'participants', 'chaperones'])->name('preview');
        Route::get('/{document}/pdf', [RosterPrintController::class, 'pdf'])->whereIn('document', ['scout-groups', 'participants', 'chaperones'])->name('pdf');
    });

    // Shared routes: Akses Peserta, Bindamping, dan Gugusdepan (Admin, Panitia, Juri)
    Route::middleware('role:admin|committee|judge')->prefix('admin')->name('admin.')->group(function () {
        // Direktori & Data Kontingen Gugus Depan
        Route::get('/gudep-directory', ScoutGroupsDirectory::class)->name('gudep-directory');
        Route::get('/gudep-directory/{id}', ScoutGroupProfile::class)->name('gudep-directory.show');
        Route::get('/gudep-directory/{id}/participants/{participantId}', ScoutGroupParticipantProfile::class)->name('gudep-directory.participants.show');
        Route::get('/gudep-directory/{id}/chaperones/{chaperoneId}', ScoutGroupChaperoneProfile::class)->name('gudep-directory.chaperones.show');
        Route::get('/scout-groups', VerificationManagement::class)->name('scout-groups.index');
        Route::get('/scout-groups/{id}', ScoutGroupVerificationDetail::class)->name('scout-groups.show');
        Route::get('/scout-groups/{scoutGroupId}/chaperones/{chaperoneId}/print', [AdminVerificationController::class, 'chaperonePrint'])->name('scout-groups.chaperones.print');
        Route::get('/scout-groups/{scoutGroupId}/participants/{participantId}/print', [AdminVerificationController::class, 'participantPrint'])->name('scout-groups.participants.print');
        Route::get('/scout-groups/{scoutGroupId}/patrols/{patrolId}/form-01', [AdminVerificationController::class, 'form01Print'])->name('scout-groups.patrols.form-01');
        Route::get('/scout-groups/{scoutGroupId}/patrols/{patrolId}/id-cards', PatrolBatch::class)->name('scout-groups.patrols.id-cards');
        Route::get('/scout-groups/{scoutGroupId}/chaperones/id-cards', ChaperoneBatch::class)->name('scout-groups.chaperones.id-cards');
        Route::get('/scout-groups/{scoutGroupId}/id-cards', ScoutGroupBatch::class)->name('scout-groups.id-cards');
        Route::get('/scout-groups/{id}/registration-certificate', [AdminVerificationController::class, 'registrationCertificatePrint'])->name('scout-groups.registration-certificate');
        Route::get('/scout-groups/{id}/download-zip', [ZipExportController::class, 'downloadScoutGroupZip'])->name('scout-groups.download-zip');
        Route::get('/scout-groups/{id}/download-zip-stream', [ZipExportController::class, 'streamScoutGroupZip'])->name('scout-groups.download-zip-stream');
        Route::get('/scout-groups/download-zip-file/{token}', [ZipExportController::class, 'downloadZipFile'])->name('scout-groups.download-zip-file');

        // Direktori & Detail Peserta Terverifikasi
        Route::get('/participants', ParticipantsDirectory::class)->name('participants.index');
        Route::get('/participants/{id}', ParticipantDetail::class)->name('participants.show');
        Route::get('/scout-groups/{scoutGroupId}/participants/{participantId}', ParticipantDetail::class)->name('scout-groups.participants.show');

        // Direktori & Detail Pembina Pendamping Terverifikasi
        Route::get('/chaperones', ChaperonesDirectory::class)->name('chaperones.index');
        Route::get('/chaperones/{id}', ChaperoneDetail::class)->name('chaperones.show');
        Route::get('/scout-groups/{scoutGroupId}/chaperones/{chaperoneId}', ChaperoneDetail::class)->name('scout-groups.chaperones.show');
    });

    // Admin routes group (Full management, registration verification actions, officials, competitions, schedules)
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', fn () => redirect()->route('dashboard'))->name('dashboard');

        // Permohonan izin (meninggalkan perkemahan / kunjungan tamu): hanya Administrator
        Route::get('/permission-requests', PermissionRequests::class)->name('permission-requests.index');
        Route::get('/permission-requests/{id}/letter', [PermissionLetterController::class, 'show'])->name('permission-requests.letter');

        // Generate Nomor Pendaftaran Peserta (NPP) setelah pendaftaran ditutup
        Route::get('/npp', NppGeneration::class)->name('npp.index');

        // Tindakan Keputusan Verifikasi Kontingen

        // Phase 5: Manajemen Mata Lomba & Rubrik Penilaian (Livewire 4 SPA)
        Route::get('/competitions', CompetitionsManagement::class)->name('competitions.index');

        // Phase 5: Manajemen Jadwal Lomba, Penugasan Juri & Sesi Presensi Pos (Livewire 4 SPA)
        Route::get('/schedules', SchedulesManagement::class)->name('schedules.index');

        // Rekap Nilai & Klasemen Prestasi di Dasbor (Livewire 4 SPA)
        Route::get('/standings', StandingsLeaderboard::class)->name('standings.index');

        // Phase 10: Pusat Laporan, Ekspor CSV & Berita Acara Resmi (Livewire 4 SPA)
        Route::get('/reports', ReportsCenter::class)->name('reports.index');
        Route::get('/reports/export/contingents', [ReportExportController::class, 'contingentsCsv'])->name('reports.export.contingents');
        Route::get('/reports/export/participants', [ReportExportController::class, 'participantsCsv'])->name('reports.export.participants');
        Route::get('/reports/export/attendance', [ReportExportController::class, 'attendanceCsv'])->name('reports.export.attendance');
        Route::get('/reports/export/scores', [ReportExportController::class, 'scoresCsv'])->name('reports.export.scores');
        Route::get('/reports/export/standings', [ReportExportController::class, 'standingsCsv'])->name('reports.export.standings');
        Route::get('/reports/print/berita-acara', [ReportExportController::class, 'beritaAcaraPreview'])->name('reports.print.berita-acara');
        Route::get('/reports/pdf/berita-acara', [ReportExportController::class, 'beritaAcaraPdf'])->name('reports.pdf.berita-acara');

        // Phase 11: Manajemen Publikasi Pengumuman (Livewire 4 SPA)
        Route::get('/announcements', AnnouncementsManagement::class)->name('announcements.index');
        Route::get('/announcements/create', \App\Livewire\Admin\AnnouncementForm::class)->name('announcements.create');
        Route::get('/announcements/{id}/edit', \App\Livewire\Admin\AnnouncementForm::class)->name('announcements.edit');

        // Aparatur Panitia & Dewan Juri (Admin & Superadmin)
        Route::get('/registration-invitations', RegistrationInvitations::class)->name('registration-invitations.index');
        Route::get('/officials', OfficialsManagement::class)->name('officials.index');
    });

    // Superadmin: audit penilaian, tata kelola pengguna & peran, konfigurasi sistem
    Route::middleware('role:superadmin')->prefix('admin')->name('admin.')->group(function () {
        // Audit Penilaian Juri & Jejak Rekam Sistem (Livewire 4 SPA)
        Route::get('/assessments', AssessmentsAudit::class)->name('assessments.index');

        // Tata Kelola Pengguna & Peran (Livewire 4 SPA)
        Route::get('/users', UsersManagement::class)->name('users.index');

        // Siaran Notifikasi PWA & Pengumuman Kilat (Livewire 4 SPA)
        Route::get('/push-broadcast', PushBroadcastManagement::class)->name('push-broadcast.index');

        // Konfigurasi Sistem & Master Kegiatan (Livewire 4 SPA)
        Route::get('/settings/system', SystemSettingsManagement::class)->name('settings.system');
    });

    // Akses Dasbor Terpadu: Rekap Nilai & Klasemen Resmi
    Route::get('/standings', StandingsLeaderboard::class)->name('standings');
    Route::get('/standings/print', [ReportExportController::class, 'standingsRecapPreview'])->name('standings.print');
    Route::get('/standings/pdf', [ReportExportController::class, 'standingsRecapPdf'])->name('standings.pdf');

    // Committee routes group (field operational monitoring)
    Route::middleware('role:committee')->prefix('committee')->name('committee.')->group(function () {
        Route::get('/dashboard', fn () => redirect()->route('dashboard'))->name('dashboard');
    });

    // Phase 6: Portal Presensi & Scanner QR Code (Admin, Panitia, & Dewan Juri - Livewire 4 SPA)
    Route::middleware(['auth', 'role:admin|committee|judge'])->prefix('portal/attendance')->name('attendance.')->group(function () {
        Route::get('/', AttendanceDashboard::class)->name('index');
        Route::get('/recap', AttendanceRecap::class)->name('recap');
        Route::get('/recap/preview', [AttendanceScannerController::class, 'previewRecap'])->name('recap.preview');
        Route::get('/recap/pdf', [AttendanceScannerController::class, 'pdfRecap'])->name('recap.pdf');
        Route::get('/scanner/{session}', AttendanceScanner::class)->name('scanner');
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
        Route::get('/', JudgeCockpit::class)->name('index');
        Route::get('/dashboard', fn () => redirect()->route('judge.index'))->name('dashboard');
        Route::get('/scoring/{scheduleId}', JudgeScoring::class)->name('scoring');
    });

    // Scout Group contingent management group
    Route::middleware('role:scout_group')->prefix('portal/scout-group')->name('scout-group.')->group(function () {
        Route::get('/dashboard', fn () => redirect()->route('scout-group.index'))->name('dashboard');
        Route::get('/', ScoutGroupDashboard::class)->name('index');
        Route::get('/profile', App\Livewire\Portal\ScoutGroupProfile::class)->name('profile');
        Route::get('/registration-certificate', [ScoutGroupPortalController::class, 'registrationCertificatePrint'])->name('registration-certificate');

        // Patrols & participants (Livewire 4 SPA)
        Route::get('/patrols', PatrolsManagement::class)->name('patrols');
        Route::get('/patrols/{patrolId}/form-01', [ScoutGroupPortalController::class, 'form01Print'])->name('patrols.form-01');
        Route::get('/patrols/{patrolId}/participants/create', ParticipantForm::class)->name('participants.create');
        Route::get('/participants/{id}', App\Livewire\Portal\ParticipantDetail::class)->name('participants.show');
        Route::get('/participants/{id}/edit', ParticipantForm::class)->name('participants.edit');
        Route::get('/participants/{id}/print', [ScoutGroupPortalController::class, 'participantPrint'])->name('participants.print');

        // Chaperones (Bindamping - Livewire 4 SPA)
        Route::get('/chaperones', ChaperonesManagement::class)->name('chaperones');
        Route::get('/chaperones/create', ChaperoneForm::class)->name('chaperones.create');
        Route::get('/chaperones/{id}', App\Livewire\Portal\ChaperoneDetail::class)->name('chaperones.show');
        Route::get('/chaperones/{id}/edit', ChaperoneForm::class)->name('chaperones.edit');
        Route::get('/chaperones/{id}/print', [ScoutGroupPortalController::class, 'chaperonePrint'])->name('chaperones.print');

        // Review & Final Submit (Livewire 4 SPA)
        Route::get('/review', ReviewRegistration::class)->name('review');
    });

    // Participant digital access group
    Route::middleware('role:participant')->prefix('portal/participant')->name('participant.')->group(function () {
        Route::get('/dashboard', fn () => redirect()->route('dashboard'))->name('dashboard');
    });
});

require __DIR__.'/settings.php';

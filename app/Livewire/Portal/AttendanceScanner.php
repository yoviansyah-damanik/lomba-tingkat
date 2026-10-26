<?php

namespace App\Livewire\Portal;

use App\Models\AttendanceSession;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Antarmuka scanner kamera presensi langsung untuk satu sesi. Pemindaian, pencarian manual, log, dan koreksi
 * tetap memakai endpoint JSON AttendanceScannerController agar kamera tidak bergantung pada siklus render Livewire.
 */
#[Layout('layouts.app')]
class AttendanceScanner extends Component
{
    public AttendanceSession $session;

    public function mount(AttendanceSession $session): void
    {
        $user = auth()->user();

        if (! $session->canBeAccessedBy($user)) {
            abort(403, $user?->isJudge()
                ? 'Akses ditolak. Dewan Juri hanya dapat mengakses scanner presensi pos untuk mata lomba yang ditugaskan kepada Anda.'
                : 'Akses ditolak. Panitia hanya memiliki hak akses untuk presensi kehadiran umum (bukan pos mata lomba).');
        }

        $this->session = $session;
    }

    public function render()
    {
        $this->session->load([
            'schedule.competition',
            'attendances' => fn ($q) => $q->with(['participant.patrol.scoutGroup', 'chaperone.scoutGroup', 'user', 'scannedBy'])
                ->orderByDesc('scanned_at'),
        ]);

        return view('livewire.portal.attendance-scanner', [
            'session' => $this->session,
            'recentAttendances' => $this->session->attendances->take(15),
            'totalAttended' => $this->session->attendances->count(),
        ])->title('Scanner Presensi Pos - '.$this->session->title);
    }
}

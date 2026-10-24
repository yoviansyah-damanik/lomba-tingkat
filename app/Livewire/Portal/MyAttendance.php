<?php

namespace App\Livewire\Portal;

use App\Services\MyAttendanceService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Halaman "Presensi Saya": jadwal kegiatan terkait pengguna (peserta, bindamping, panitia, juri)
 */
#[Layout('layouts.app', ['title' => 'Presensi Saya'])]
#[Title('Presensi Saya — LT III')]
class MyAttendance extends Component
{
    public function render(MyAttendanceService $service)
    {
        return view('livewire.portal.my-attendance', [
            'identity' => $service->identity(auth()->user()),
        ]);
    }
}

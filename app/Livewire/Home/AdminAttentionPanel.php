<?php

namespace App\Livewire\Home;

use App\Enums\RegistrationStatusEnum;
use App\Models\AttendanceSession;
use App\Models\Event;
use App\Models\PermissionRequest;
use App\Models\RegistrationInvitation;
use App\Models\ScoutGroup;
use Livewire\Attributes\Lazy;
use Livewire\Component;

/**
 * Panel dasbor Administrator: hal yang perlu ditindaklanjuti (antrean verifikasi, izin belum kembali,
 * status pendaftaran, penggunaan kode undangan, dan sesi presensi aktif)
 */
#[Lazy]
class AdminAttentionPanel extends Component
{
    public function placeholder(): string
    {
        return '<div class="h-56 animate-pulse rounded-2xl border border-zinc-200 bg-zinc-100 dark:border-zinc-800 dark:bg-zinc-800/60"></div>';
    }

    public function render()
    {
        $event = Event::where('is_active', true)->first();

        return view('livewire.home.admin-attention-panel', [
            'pendingGroups' => ScoutGroup::where('verification_status', RegistrationStatusEnum::Submitted)->orderBy('updated_at')->limit(5)->get(),
            'pendingTotal' => ScoutGroup::where('verification_status', RegistrationStatusEnum::Submitted)->count(),
            'revisionTotal' => ScoutGroup::where('verification_status', RegistrationStatusEnum::NeedsRevision)->count(),
            'verifiedTotal' => ScoutGroup::where('verification_status', RegistrationStatusEnum::Verified)->count(),
            'outstandingPermissions' => PermissionRequest::where('status', 'active')->count(),
            'activeSessions' => AttendanceSession::where('is_active', true)->count(),
            'invitationsTotal' => RegistrationInvitation::count(),
            'invitationsUsable' => RegistrationInvitation::usable()->count(),
            'registrationOpen' => $event?->isRegistrationOpen() ?? false,
            'registrationEnd' => $event?->registration_end_at,
        ]);
    }
}

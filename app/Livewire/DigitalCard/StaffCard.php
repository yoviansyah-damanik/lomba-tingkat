<?php

namespace App\Livewire\DigitalCard;

use App\Models\User;
use App\Services\DigitalCardService;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * ID Card digital aparatur staf: Panitia / Dewan Juri (ukuran resmi B2)
 */
#[Layout('layouts.digital-card')]
class StaffCard extends Component
{
    public string $staffId;

    public function mount(string $id): void
    {
        $staff = User::with('roles')->findOrFail($id);

        if (! $staff->isCommittee() && ! $staff->isJudge() && ! $staff->isAdmin()) {
            abort(404, 'Pengguna bukan aparatur kepanitiaan atau dewan juri.');
        }

        if (auth()->user() && ! auth()->user()->isAdmin() && auth()->id() !== $staff->id) {
            abort(403, 'Anda hanya dapat mengakses ID Card staf milik sendiri.');
        }

        $this->staffId = $staff->id;
    }

    public function render(DigitalCardService $cards)
    {
        $staff = User::with('roles')->findOrFail($this->staffId);

        return view('livewire.digital-card.staff-card', [
            'staff' => $staff,
            'qrDataUri' => $cards->qr($staff->qr_token),
        ])->title('KARTU APARATUR (B2) - '.$staff->name)
            ->layoutData(['bodyClass' => $staff->isJudge() ? 'bg-[#1c0d02]' : 'bg-[#1a0505]']);
    }
}

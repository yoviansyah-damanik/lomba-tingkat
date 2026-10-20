<?php

namespace App\Livewire\Portal;

use App\Enums\RegistrationStatusEnum;
use App\Models\ScoutGroup;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Komponen Livewire 4 Pemeriksaan Ulang & Pengajuan Akhir Pendaftaran Kontingen
 */
#[Layout('layouts.app', ['title' => 'Pemeriksaan Ulang Pendaftaran Kontingen'])]
#[Title('Pemeriksaan Ulang Berkas — LT III')]
class ReviewRegistration extends Component
{
    public bool $showSubmitModal = false;
    public bool $agreed = false;

    #[Computed]
    public function scoutGroup(): ScoutGroup
    {
        return ScoutGroup::firstOrCreate(
            ['user_id' => auth()->id()],
            [
                'registration_code' => 'GDP-'.strtoupper(substr(md5((string) auth()->id()), 0, 6)),
                'school_name' => auth()->user()->name,
                'group_number' => 'Belum Diisi',
                'address' => 'Kota Padangsidimpuan',
                'contact_person' => auth()->user()->name,
                'contact_phone' => auth()->user()->phone_number ?? '08123456789',
                'verification_status' => RegistrationStatusEnum::Draft,
            ]
        );
    }

    #[Computed]
    public function readiness(): array
    {
        $group = $this->scoutGroup;
        $hasPatrol = $group->patrols()->count() > 0;
        $hasChaperone = $group->chaperones()->count() > 0;
        $isProfileComplete = ! empty($group->school_name) && $group->group_number !== 'Belum Diisi';

        $isReady = $hasPatrol && $hasChaperone && $isProfileComplete;

        return [
            'is_ready' => $isReady,
            'has_patrol' => $hasPatrol,
            'has_chaperone' => $hasChaperone,
            'is_profile_complete' => $isProfileComplete,
        ];
    }

    public function openSubmitModal(): void
    {
        if (! $this->readiness['is_ready']) {
            Flux::toast('Mohon lengkapi syarat minimal pendaftaran terlebih dahulu.', variant: 'warning');
            return;
        }

        $this->showSubmitModal = true;
    }

    public function submitRegistration(): void
    {
        $group = $this->scoutGroup;

        if (! $group->isEditable()) {
            Flux::toast('Pendaftaran sudah diajukan atau terkunci.', variant: 'danger');
            return;
        }

        if (! $this->readiness['is_ready']) {
            Flux::toast('Syarat berkas pendaftaran belum lengkap.', variant: 'danger');
            return;
        }

        $group->update([
            'verification_status' => RegistrationStatusEnum::Submitted,
            'submitted_at' => now(),
        ]);

        $this->showSubmitModal = false;
        session()->flash('success', 'Pendaftaran kontingen berhasil diajukan untuk verifikasi Panitia Kwarcab.');
        Flux::toast('Pendaftaran kontingen berhasil diajukan.', variant: 'success');

        $this->redirectRoute('scout-group.index');
    }

    public function render()
    {
        $scoutGroup = $this->scoutGroup;
        $scoutGroup->load(['patrols.participants', 'chaperones']);

        return view('livewire.portal.review-registration', [
            'scoutGroup' => $scoutGroup,
            'readiness' => $this->readiness,
        ]);
    }
}

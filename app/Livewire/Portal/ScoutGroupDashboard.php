<?php

namespace App\Livewire\Portal;

use App\Enums\RegistrationStatusEnum;
use App\Models\Event;
use App\Models\ScoutGroup;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Komponen Livewire 4 Portal Kontingen Pangkalan Gugus Depan
 */
#[Layout('layouts.app')]
#[Title('Portal Gugus Depan — Livewire 4')]
class ScoutGroupDashboard extends Component
{
    // Modal Edit Profil Pangkalan
    public bool $showProfileModal = false;
    public string $school_name = '';
    public string $group_number = '';
    public string $address = '';
    public string $contact_person = '';
    public string $contact_phone = '';

    public function mount(): void
    {
        $group = $this->scoutGroup;
        if ($group) {
            $this->school_name = $group->school_name;
            $this->group_number = $group->group_number;
            $this->address = $group->address ?? '';
            $this->contact_person = $group->contact_person;
            $this->contact_phone = $group->contact_phone;
        }
    }

    #[Computed]
    public function scoutGroup(): ScoutGroup
    {
        return ScoutGroup::firstOrCreate(
            ['user_id' => auth()->id()],
            [
                'registration_code' => 'GDP-' . strtoupper(substr(md5((string) auth()->id()), 0, 6)),
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
    public function activeEvent(): ?Event
    {
        return Event::where('is_active', true)->first();
    }

    /**
     * Buka modal edit profil
     */
    public function openProfileModal(): void
    {
        $group = $this->scoutGroup;
        $this->school_name = $group->school_name;
        $this->group_number = $group->group_number;
        $this->address = $group->address ?? '';
        $this->contact_person = $group->contact_person;
        $this->contact_phone = $group->contact_phone;
        $this->showProfileModal = true;
    }

    /**
     * Simpan pembaruan profil pangkalan secara instan
     */
    public function updateProfile(): void
    {
        $group = $this->scoutGroup;

        if (! $group->isEditable()) {
            Flux::toast(variant: 'danger', heading: 'Gagal', text: 'Data terkunci dan tidak dapat diubah karena sedang dalam proses verifikasi atau telah disetujui.');
            return;
        }

        $this->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'group_number' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string'],
            'contact_person' => ['required', 'string', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:30'],
        ]);

        $groupNumberData = ScoutGroup::formatGroupNumbers($this->group_number);

        $group->update([
            'school_name' => $this->school_name,
            'group_number' => $groupNumberData['combined'],
            'male_group_number' => $groupNumberData['male'],
            'female_group_number' => $groupNumberData['female'],
            'address' => $this->address,
            'contact_person' => $this->contact_person,
            'contact_phone' => $this->contact_phone,
        ]);

        $this->showProfileModal = false;
        Flux::toast(variant: 'success', heading: 'Profil Diperbarui', text: 'Profil pangkalan Gugus Depan berhasil disimpan.');
    }

    public function render()
    {
        $group = $this->scoutGroup;
        $group->load(['patrols.participants', 'chaperones']);

        return view('livewire.portal.scout-group-dashboard', [
            'scoutGroup' => $group,
            'boysPatrol' => $group->boysPatrol,
            'girlsPatrol' => $group->girlsPatrol,
            'activeEvent' => $this->activeEvent,
        ]);
    }
}

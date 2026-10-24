<?php

namespace App\Livewire\Portal;

use App\Enums\RegistrationStatusEnum;
use App\Models\Chaperone;
use App\Models\ScoutGroup;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Komponen Livewire 4 Pengelolaan Pembina Pendamping (Bindamping) Kontingen
 */
#[Layout('layouts.app', ['title' => 'Pembina Pendamping (Bindamping)'])]
#[Title('Pembina Pendamping — LT III')]
class ChaperonesManagement extends Component
{
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

    /**
     * Bindamping hanya dapat didaftarkan untuk gender yang sudah memiliki regu
     */
    public function canRegister(string $gender): bool
    {
        return $this->scoutGroup->canRegisterChaperone($gender);
    }

    public function deleteChaperone(string $id): void
    {
        $group = $this->scoutGroup;

        if (! $group->isEditable()) {
            Flux::toast('Pendaftaran terkunci dan tidak dapat menghapus bindamping.', variant: 'danger');
            return;
        }

        $chaperone = $group->chaperones()->find($id);

        if ($chaperone) {
            $name = $chaperone->full_name;
            $chaperone->delete();
            Flux::toast("Bindamping {$name} berhasil dihapus.", variant: 'success');
        }
    }

    public function render()
    {
        $scoutGroup = $this->scoutGroup;
        $scoutGroup->load('chaperones.patrols');

        return view('livewire.portal.chaperones-management', [
            'scoutGroup' => $scoutGroup,
            'chaperonesByGender' => [
                'male' => $scoutGroup->chaperones->where('gender', 'male')->values(),
                'female' => $scoutGroup->chaperones->where('gender', 'female')->values(),
            ],
        ]);
    }
}

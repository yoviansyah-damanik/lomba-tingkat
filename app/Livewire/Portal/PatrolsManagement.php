<?php

namespace App\Livewire\Portal;

use App\Enums\RegistrationStatusEnum;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Komponen Livewire 4 Pengelolaan Regu & Anggota Peserta Kontingen
 */
#[Layout('layouts.app', ['title' => 'Pengelolaan Regu Kontingen'])]
#[Title('Pengelolaan Regu & Peserta — LT III')]
class PatrolsManagement extends Component
{
    public bool $showCreateModal = false;
    public string $patrol_name = '';
    public string $gender_category = 'boys';

    // State konfirmasi hapus peserta
    public ?string $deletingParticipantId = null;

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

    public function openCreateModal(): void
    {
        $group = $this->scoutGroup;
        if (! $group->boysPatrol) {
            $this->gender_category = 'boys';
        } elseif (! $group->girlsPatrol) {
            $this->gender_category = 'girls';
        }
        $this->patrol_name = '';
        $this->showCreateModal = true;
    }

    public function storePatrol(): void
    {
        $group = $this->scoutGroup;

        if (! $group->isEditable()) {
            Flux::toast('Pendaftaran terkunci dan tidak dapat menambah regu.', variant: 'danger');
            return;
        }

        $this->validate([
            'patrol_name' => ['required', 'string', 'max:100'],
            'gender_category' => ['required', 'in:boys,girls'],
        ]);

        $existing = $group->patrols()->where('gender_category', $this->gender_category)->first();
        if ($existing) {
            $this->addError('gender_category', 'Pangkalan Anda sudah memiliki regu untuk kategori ini.');
            return;
        }

        $group->patrols()->create([
            'patrol_name' => $this->patrol_name,
            'gender_category' => $this->gender_category,
            'status' => RegistrationStatusEnum::Draft,
        ]);

        $this->showCreateModal = false;
        $this->patrol_name = '';
        Flux::toast('Regu berhasil dibentuk.', variant: 'success');
    }

    public function deleteParticipant(string $id): void
    {
        $group = $this->scoutGroup;

        if (! $group->isEditable()) {
            Flux::toast('Pendaftaran terkunci dan tidak dapat menghapus peserta.', variant: 'danger');
            return;
        }

        $participant = Participant::whereHas('patrol', function ($q) use ($group) {
            $q->where('scout_group_id', $group->id);
        })->find($id);

        if ($participant) {
            $participantName = $participant->full_name;
            $participant->delete();
            Flux::toast("Peserta {$participantName} berhasil dihapus.", variant: 'success');
        }
    }

    public function render()
    {
        $scoutGroup = $this->scoutGroup;
        $scoutGroup->load(['patrols.participants']);

        return view('livewire.portal.patrols-management', [
            'scoutGroup' => $scoutGroup,
        ]);
    }
}

<?php

namespace App\Livewire\Portal;

use App\Enums\RegistrationStatusEnum;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use Flux\Flux;
use Illuminate\Validation\Rule;
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

    public function mount(): void
    {
        $this->gender_category = 'boys';
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->patrol_name = '';
        $this->showCreateModal = true;
    }

    public function storePatrol(): void
    {
        $group = $this->scoutGroup;

        if (! $group->isEditable()) {
            $this->addError('status', 'Pendaftaran terkunci dan tidak dapat menambah regu.');
            Flux::toast('Pendaftaran terkunci dan tidak dapat menambah regu.', variant: 'danger');
            return;
        }

        $validated = $this->validate([
            'patrol_name' => ['required', 'string', 'max:100', Rule::unique('patrols', 'patrol_name')->where('scout_group_id', $group->id)],
            'gender_category' => ['required', 'in:boys,girls'],
        ], [
            'patrol_name.required' => 'Nama regu wajib diisi.',
            'patrol_name.max' => 'Nama regu maksimal 100 karakter.',
            'patrol_name.unique' => 'Nama regu sudah digunakan di gugusdepan Anda.',
            'gender_category.required' => 'Kategori regu wajib dipilih.',
            'gender_category.in' => 'Kategori regu tidak valid.',
        ]);

        $patrol = $group->patrols()->create([
            'patrol_name' => $validated['patrol_name'],
            'gender_category' => $validated['gender_category'],
            'status' => RegistrationStatusEnum::Draft,
        ]);

        $this->patrol_name = '';
        $this->showCreateModal = false;

        unset($this->scoutGroup);

        session()->flash('success', "Regu {$patrol->patrol_name} ({$patrol->gender_category->label()}) berhasil dibentuk.");
        Flux::toast(variant: 'success', heading: 'Regu Berhasil Dibentuk', text: "Regu {$patrol->patrol_name} ({$patrol->gender_category->label()}) berhasil dibentuk.");
    }

    public function deletePatrol(string $id): void
    {
        $group = $this->scoutGroup;

        if (! $group->isEditable()) {
            Flux::toast('Pendaftaran terkunci dan data regu tidak dapat dihapus.', variant: 'danger');
            return;
        }

        $patrol = $group->patrols()->with('participants')->find($id);

        if (! $patrol) {
            Flux::toast('Data regu tidak ditemukan.', variant: 'danger');
            return;
        }

        $patrolName = $patrol->patrol_name;
        $genderLabel = $patrol->gender_category?->label();

        // Hapus file foto seluruh anggota peserta di dalam regu jika ada
        foreach ($patrol->participants as $participant) {
            if ($participant->photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($participant->photo_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($participant->photo_path);
            }
        }

        // Hapus regu (peserta terhapus otomatis via cascade)
        $patrol->delete();

        // Reset relasi cache agar render menampilkan data terbaru
        unset($this->scoutGroup);

        session()->flash('success', "Regu {$patrolName} ({$genderLabel}) beserta seluruh data pesertanya berhasil dihapus.");
        Flux::toast(variant: 'success', heading: 'Regu Dihapus', text: "Regu {$patrolName} ({$genderLabel}) berhasil dihapus.");
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
            unset($this->scoutGroup);
            session()->flash('success', "Peserta {$participantName} berhasil dihapus.");
            Flux::toast("Peserta {$participantName} berhasil dihapus.", variant: 'success');
        }
    }

    public function render()
    {
        $scoutGroup = $this->scoutGroup;
        $scoutGroup->load(['patrols.participants', 'patrols.chaperone', 'chaperones']);

        return view('livewire.portal.patrols-management', [
            'scoutGroup' => $scoutGroup,
        ]);
    }
}

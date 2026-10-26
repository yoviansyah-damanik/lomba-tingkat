<?php

namespace App\Livewire\Portal;

use App\Enums\RegistrationStatusEnum;
use App\Models\Chaperone;
use App\Models\ScoutGroup;
use App\Support\BloodType;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Formulir biodata resmi pembina pendamping (tambah / ubah) milik gugus depan yang login
 */
#[Layout('layouts.app')]
#[Title('Biodata Pembina Pendamping — LT III')]
class ChaperoneForm extends Component
{
    use WithFileUploads;

    public ?string $chaperoneId = null;

    /** Kategori bindamping (male/female); tersimpan dan tidak dapat diubah saat edit */
    public string $gender = 'male';

    public string $full_name = '';

    public string $birth_place = '';

    public ?string $birth_date = null;

    public string $religion = '';

    public string $blood_type = '';

    public string $scout_id = '';

    public ?string $national_identity_number = null;

    public string $home_address = '';

    public string $city = 'Kota Padangsidimpuan';

    public string $postal_code = '';

    public ?string $home_phone = null;

    public string $phone_number = '';

    public $photo = null;

    /** @var array<int, array<string, mixed>> */
    public array $formal_education = [];

    /** @var array<int, array<string, mixed>> */
    public array $scout_experiences = [];

    /** @var array<int, array<string, mixed>> */
    public array $scout_trainings = [];

    /** @var array<int, array<string, mixed>> */
    public array $other_trainings = [];

    /** @var array<int, string> */
    public array $patrol_ids = [];

    public function mount(?string $id = null)
    {
        $scoutGroup = ScoutGroup::forUser(auth()->user());

        if (! $scoutGroup->isEditable()) {
            session()->flash('error', 'Pendaftaran terkunci dan data tidak dapat diubah.');

            return $this->redirectRoute('scout-group.chaperones', navigate: true);
        }

        if ($id) {
            $chaperone = $scoutGroup->chaperones()->findOrFail($id);

            $this->chaperoneId = $chaperone->id;
            $this->gender = $chaperone->gender;
            $this->fill($chaperone->only([
                'full_name', 'birth_place', 'religion', 'blood_type', 'scout_id', 'national_identity_number',
                'home_address', 'postal_code', 'home_phone', 'phone_number',
            ]));
            $this->birth_date = $chaperone->birth_date?->format('Y-m-d');
            $this->city = $chaperone->city ?? 'Kota Padangsidimpuan';
            $this->formal_education = ! empty($chaperone->formal_education) ? $chaperone->formal_education : Chaperone::defaultFormalEducation();
            $this->scout_experiences = ! empty($chaperone->scout_experiences) ? $chaperone->scout_experiences : Chaperone::defaultScoutExperiences();
            $this->scout_trainings = ! empty($chaperone->scout_trainings) ? $chaperone->scout_trainings : Chaperone::defaultScoutTrainings();
            $this->other_trainings = ! empty($chaperone->other_trainings) ? array_values($chaperone->other_trainings) : [$this->emptyTraining()];
            $this->patrol_ids = $chaperone->patrols->pluck('id')->map(fn ($patrolId) => (string) $patrolId)->all();

            return;
        }

        $gender = request()->query('gender', 'male');
        $this->gender = in_array($gender, ['male', 'female'], true) ? $gender : 'male';

        // Regu wajib ada lebih dulu; bindamping mengikuti regu putra/putri
        if (! $scoutGroup->canRegisterChaperone($this->gender)) {
            session()->flash('error', ScoutGroup::chaperoneRequiresPatrolMessage($this->gender));

            return $this->redirectRoute($scoutGroup->patrols()->exists() ? 'scout-group.chaperones' : 'scout-group.patrols', navigate: true);
        }

        $this->formal_education = Chaperone::defaultFormalEducation();
        $this->scout_experiences = Chaperone::defaultScoutExperiences();
        $this->scout_trainings = Chaperone::defaultScoutTrainings();
        $this->other_trainings = [$this->emptyTraining()];
        $this->patrol_ids = $scoutGroup->patrols
            ->filter(fn ($p) => ! $p->chaperone_id && ($this->gender === 'male' ? $p->isBoys() : $p->isGirls()))
            ->pluck('id')->map(fn ($patrolId) => (string) $patrolId)->all();
    }

    private function emptyTraining(): array
    {
        return ['name' => '', 'year' => '', 'location' => '', 'notes' => ''];
    }

    public function addTraining(): void
    {
        $this->other_trainings[] = $this->emptyTraining();
    }

    public function removeTraining(int $index): void
    {
        if (count($this->other_trainings) > 1) {
            unset($this->other_trainings[$index]);
            $this->other_trainings = array_values($this->other_trainings);
        }
    }

    private function scoutGroup(): ScoutGroup
    {
        return ScoutGroup::forUser(auth()->user())->load('patrols.chaperone');
    }

    private function chaperone(): ?Chaperone
    {
        return $this->chaperoneId ? $this->scoutGroup()->chaperones()->find($this->chaperoneId) : null;
    }

    protected function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'birth_place' => ['required', 'string', 'max:100'],
            'birth_date' => ['required', 'date'],
            'religion' => ['required', 'string', 'max:50'],
            'blood_type' => ['required', BloodType::rule()],
            'scout_id' => ['required', 'string', 'max:50'],
            'national_identity_number' => ['nullable', 'string', 'max:30'],
            'home_address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'home_phone' => ['nullable', 'string', 'max:30'],
            'phone_number' => ['required', 'string', 'max:30'],
            'photo' => [$this->chaperone()?->photo_path ? 'nullable' : 'required', 'image', 'max:2048'],
            'formal_education' => ['nullable', 'array'],
            'scout_experiences' => ['nullable', 'array'],
            'scout_trainings' => ['nullable', 'array'],
            'other_trainings' => ['nullable', 'array'],
            'patrol_ids' => ['nullable', 'array'],
            'patrol_ids.*' => ['string'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'full_name' => 'Nama lengkap',
            'birth_place' => 'Tempat lahir',
            'birth_date' => 'Tanggal lahir',
            'religion' => 'Agama',
            'blood_type' => 'Golongan darah',
            'scout_id' => 'Nomor KTA',
            'home_address' => 'Alamat rumah',
            'city' => 'Kota / kabupaten',
            'postal_code' => 'Kode pos',
            'phone_number' => 'Nomor HP',
            'photo' => 'Pas foto',
        ];
    }

    public function save()
    {
        $scoutGroup = $this->scoutGroup();
        $chaperone = $this->chaperone();

        if (! $scoutGroup->isEditable()) {
            $this->addError('status', 'Pendaftaran terkunci.');

            return;
        }

        $validated = $this->validate();

        // Kategori bindamping tidak dapat diubah saat edit; saat tambah harus punya regu sekategori
        $validated['gender'] = $chaperone?->gender ?? $this->gender;

        if (! $chaperone && ! $scoutGroup->canRegisterChaperone($validated['gender'])) {
            $this->addError('gender', ScoutGroup::chaperoneRequiresPatrolMessage($validated['gender']));

            return;
        }

        if ($this->photo) {
            if ($chaperone?->photo_path && Storage::disk('public')->exists($chaperone->photo_path)) {
                Storage::disk('public')->delete($chaperone->photo_path);
            }
            $validated['photo_path'] = $this->photo->store('chaperones', 'public');
        }
        unset($validated['photo']);

        $patrolIds = $validated['patrol_ids'] ?? [];
        unset($validated['patrol_ids']);

        if ($chaperone) {
            $chaperone->update($validated);
            $message = 'Biodata resmi pembina pendamping berhasil diperbarui.';
        } else {
            $chaperone = $scoutGroup->chaperones()->create(array_merge($validated, ['status' => RegistrationStatusEnum::Draft]));
            $label = $validated['gender'] === 'male' ? 'Bindamping Putra' : 'Bindamping Putri';
            $message = "Formulir biodata resmi {$label} berhasil disimpan.";
        }

        $scoutGroup->syncChaperonePatrols($chaperone, $patrolIds);

        session()->flash('success', $message);

        return $this->redirectRoute('scout-group.chaperones', navigate: true);
    }

    public function render()
    {
        return view('livewire.portal.chaperone-form', [
            'scoutGroup' => $this->scoutGroup(),
            'chaperone' => $this->chaperone(),
        ])->title($this->chaperoneId ? 'Edit Biodata Pembina Pendamping' : 'Formulir Biodata Pembina Pendamping');
    }
}

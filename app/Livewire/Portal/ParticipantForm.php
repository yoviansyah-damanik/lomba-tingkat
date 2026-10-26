<?php

namespace App\Livewire\Portal;

use App\Enums\PatrolRoleEnum;
use App\Enums\RegistrationStatusEnum;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use App\Support\BloodType;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Formulir biodata peserta (tambah ke regu / ubah) milik gugus depan yang login
 */
#[Layout('layouts.app')]
#[Title('Biodata Peserta — LT III')]
class ParticipantForm extends Component
{
    use WithFileUploads;

    public string $patrolId = '';

    public ?string $participantId = null;

    public string $full_name = '';

    public string $role_in_patrol = 'member';

    public string $gender = 'male';

    public string $religion = '';

    public string $blood_type = '';

    public string $scout_id = '';

    public string $birth_place = 'Padangsidimpuan';

    public ?string $birth_date = null;

    public string $home_address = '';

    public string $city = 'Padangsidimpuan';

    public string $postal_code = '';

    public ?string $home_phone = null;

    public string $phone = '';

    public string $parent_name = '';

    public string $parent_job = '';

    public string $parent_address = '';

    public string $parent_city = 'Padangsidimpuan';

    public string $parent_postal_code = '';

    public ?string $parent_home_phone = null;

    public string $parent_phone = '';

    /** @var array<int, array<string, mixed>> */
    public array $formal_education = [];

    /** @var array<string, mixed> */
    public array $scout_siaga = [];

    /** @var array<string, mixed> */
    public array $scout_penggalang = [];

    public $photo = null;

    public $doc_kta = null;

    public $doc_bpjs = null;

    public $doc_health_certificate = null;

    public $doc_birth_certificate = null;

    public $doc_family_card = null;

    public $doc_sku = null;

    public $doc_skk = null;

    /**
     * Dua rute: participants/create (patrolId) dan participants/{id}/edit
     */
    public function mount(?string $patrolId = null, ?string $id = null): void
    {
        $scoutGroup = ScoutGroup::forUser(auth()->user());

        if ($id) {
            $participant = Participant::whereHas('patrol', fn ($q) => $q->where('scout_group_id', $scoutGroup->id))->findOrFail($id);

            $this->participantId = $participant->id;
            $this->patrolId = $participant->patrol_id;
            $this->fill($participant->only([
                'full_name', 'religion', 'blood_type', 'scout_id', 'birth_place', 'home_address', 'city', 'postal_code',
                'home_phone', 'phone', 'parent_name', 'parent_job', 'parent_address', 'parent_city', 'parent_postal_code',
                'parent_home_phone', 'parent_phone', 'gender',
            ]));
            $this->role_in_patrol = $participant->role_in_patrol?->value ?? (string) $participant->role_in_patrol;
            $this->birth_date = $participant->birth_date?->format('Y-m-d');
            $this->city = $participant->city ?: 'Padangsidimpuan';
            $this->parent_city = $participant->parent_city ?: 'Padangsidimpuan';
            $this->formal_education = $participant->formal_education ?: Participant::defaultFormalEducation();
            $this->scout_siaga = $participant->scout_siaga ?: Participant::defaultScoutSiaga();
            $this->scout_penggalang = $participant->scout_penggalang ?: Participant::defaultScoutPenggalang();

            return;
        }

        $patrol = $scoutGroup->patrols()->findOrFail($patrolId);
        $this->patrolId = $patrol->id;
        $this->gender = $patrol->isBoys() ? 'male' : 'female';
        $this->formal_education = Participant::defaultFormalEducation();
        $this->scout_siaga = Participant::defaultScoutSiaga();
        $this->scout_penggalang = Participant::defaultScoutPenggalang();
    }

    private function scoutGroup(): ScoutGroup
    {
        return ScoutGroup::forUser(auth()->user());
    }

    private function patrol(): Patrol
    {
        return $this->scoutGroup()->patrols()->findOrFail($this->patrolId);
    }

    private function participant(): ?Participant
    {
        return $this->participantId ? Participant::with('patrol')->find($this->participantId) : null;
    }

    /**
     * Aturan validasi: Bagian 1 (identitas), 2 (orang tua), dan 3 (pendidikan formal) wajib diisi
     */
    protected function rules(): array
    {
        $rules = [
            'photo' => [$this->participant()?->photo_path ? 'nullable' : 'required', 'image', 'max:2048'],
            'full_name' => ['required', 'string', 'max:255'],
            'role_in_patrol' => ['required', 'in:patrol_leader,assistant_patrol_leader,member'],
            'gender' => ['required', 'in:male,female'],
            'religion' => ['required', 'string', 'max:50'],
            'blood_type' => ['required', BloodType::rule()],
            'scout_id' => ['required', 'string', 'max:50'],
            'birth_place' => ['required', 'string', 'max:100'],
            'birth_date' => ['required', 'date'],
            'home_address' => ['required', 'string'],
            'city' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:10'],
            'home_phone' => ['nullable', 'string', 'max:30'],
            'phone' => ['required', 'string', 'max:30'],

            'parent_name' => ['required', 'string', 'max:255'],
            'parent_job' => ['required', 'string', 'max:100'],
            'parent_address' => ['required', 'string'],
            'parent_city' => ['required', 'string', 'max:100'],
            'parent_postal_code' => ['required', 'string', 'max:10'],
            'parent_home_phone' => ['nullable', 'string', 'max:30'],
            'parent_phone' => ['required', 'string', 'max:30'],

            'formal_education' => ['required', 'array', 'min:1'],
            'formal_education.*.level' => ['required', 'string', 'max:20'],
            'formal_education.*.school_name' => ['required', 'string', 'max:255'],
            'formal_education.*.city' => ['required', 'string', 'max:100'],
            'formal_education.*.notes' => ['nullable', 'string', 'max:255'],

            'scout_siaga' => ['nullable', 'array'],
            'scout_penggalang' => ['nullable', 'array'],

            'doc_kta' => ['nullable', 'file', 'mimes:pdf', 'max:4096'],
            'doc_bpjs' => ['nullable', 'file', 'mimes:pdf', 'max:4096'],
            'doc_health_certificate' => ['nullable', 'file', 'mimes:pdf', 'max:4096'],
            'doc_birth_certificate' => ['nullable', 'file', 'mimes:pdf', 'max:4096'],
            'doc_family_card' => ['nullable', 'file', 'mimes:pdf', 'max:4096'],
            'doc_sku' => ['nullable', 'file', 'mimes:pdf', 'max:4096'],
            'doc_skk' => ['nullable', 'file', 'mimes:pdf', 'max:4096'],
        ];

        // Tahun lulus wajib diisi, kecuali SMP: peserta diasumsikan masih bersekolah
        foreach ($this->formal_education as $index => $education) {
            $rules["formal_education.$index.graduation_year"] = (($education['level'] ?? null) === 'SMP' ? 'nullable' : 'required').'|string|max:10';
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'photo.image' => 'Pas foto harus berupa gambar JPG atau PNG.',
            'photo.max' => 'Ukuran pas foto maksimal 2MB.',
            'file' => ':attribute harus berupa berkas yang valid.',
            'mimes' => ':attribute harus berformat PDF.',
            'max' => 'Ukuran :attribute maksimal :max KB.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'photo' => 'Pas foto',
            'full_name' => 'Nama lengkap',
            'role_in_patrol' => 'Jabatan dalam regu',
            'gender' => 'Jenis kelamin',
            'religion' => 'Agama',
            'blood_type' => 'Golongan darah',
            'scout_id' => 'Nomor KTA',
            'birth_place' => 'Tempat lahir',
            'birth_date' => 'Tanggal lahir',
            'home_address' => 'Alamat rumah',
            'city' => 'Kota / kabupaten',
            'postal_code' => 'Kode pos',
            'home_phone' => 'Telepon rumah',
            'phone' => 'Nomor HP peserta',
            'parent_name' => 'Nama orang tua / wali',
            'parent_job' => 'Pekerjaan orang tua / wali',
            'parent_address' => 'Alamat orang tua / wali',
            'parent_city' => 'Kota orang tua / wali',
            'parent_postal_code' => 'Kode pos orang tua / wali',
            'parent_home_phone' => 'Telepon rumah orang tua / wali',
            'parent_phone' => 'Nomor HP orang tua / wali',
            'formal_education' => 'Pendidikan formal',
            'formal_education.*.level' => 'Jenjang pendidikan',
            'formal_education.*.school_name' => 'Nama sekolah',
            'formal_education.*.city' => 'Kota sekolah',
            'formal_education.*.graduation_year' => 'Tahun lulus',
            'formal_education.*.notes' => 'Keterangan pendidikan',
            'doc_kta' => 'Scan KTA',
            'doc_bpjs' => 'Scan BPJS',
            'doc_health_certificate' => 'Scan Surat Keterangan Sehat',
            'doc_birth_certificate' => 'Scan Akta Kelahiran',
            'doc_family_card' => 'Scan Kartu Keluarga (KK)',
            'doc_sku' => 'Scan SKU',
            'doc_skk' => 'Scan SKK',
        ];
    }

    public function save()
    {
        $scoutGroup = $this->scoutGroup();
        $patrol = $this->patrol();
        $participant = $this->participant();

        if (! $scoutGroup->isEditable()) {
            $this->addError('status', $participant
                ? 'Pendaftaran terkunci dan data peserta tidak dapat diubah.'
                : 'Pendaftaran terkunci dan tidak dapat menambah peserta.');

            return;
        }

        if (! $participant && $patrol->participants()->count() >= Patrol::REQUIRED_MEMBERS) {
            $this->addError('status', 'Setiap regu maksimal '.Patrol::REQUIRED_MEMBERS.' peserta.');

            return;
        }

        $validated = $this->validate();

        // 1 regu hanya boleh punya 1 Pinru dan 1 Wapinru
        foreach ([
            PatrolRoleEnum::PatrolLeader->value => 'Regu ini sudah memiliki Pimpinan Regu (Pinru).',
            PatrolRoleEnum::AssistantPatrolLeader->value => 'Regu ini sudah memiliki Wakil Pimpinan Regu (Wapinru).',
        ] as $role => $message) {
            $taken = $patrol->participants()->where('role_in_patrol', $role)
                ->when($participant, fn ($q) => $q->where('id', '!=', $participant->id))
                ->exists();

            if ($validated['role_in_patrol'] === $role && $taken) {
                $this->addError('role_in_patrol', $message);

                return;
            }
        }

        $data = $validated;
        unset($data['photo']);

        // Nomor gudep dan pangkalan riwayat Penggalang selalu mengikuti data gugus depan (tidak dapat diubah)
        $data['scout_penggalang'] = array_merge($data['scout_penggalang'] ?? [], [
            'group_number' => $patrol->group_number,
            'pangkalan' => $scoutGroup->school_name,
        ]);

        if ($this->photo) {
            $this->deleteStored($participant?->photo_path);
            $data['photo_path'] = $this->photo->store('participants', 'public');
        }

        foreach (Participant::DOCUMENTS as $inputKey => [$column]) {
            unset($data[$inputKey]);

            if ($this->{$inputKey}) {
                $this->deleteStored($participant?->{$column});
                $data[$column] = $this->{$inputKey}->store('participants/documents', 'public');
            }
        }

        if ($participant) {
            $participant->update($data);
            session()->flash('success', 'Data peserta berhasil diperbarui.');
        } else {
            $patrol->participants()->create(array_merge($data, ['status' => RegistrationStatusEnum::Draft]));
            session()->flash('success', 'Peserta berhasil didaftarkan ke dalam regu.');
        }

        return $this->redirectRoute('scout-group.patrols', navigate: true);
    }

    private function deleteStored(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public function render()
    {
        $patrol = $this->patrol();

        return view('livewire.portal.participant-form', [
            'scoutGroup' => $this->scoutGroup(),
            'patrol' => $patrol,
            'participant' => $this->participant(),
        ])->title($this->participantId ? 'Edit Biodata Peserta' : 'Biodata Peserta — Regu '.$patrol->patrol_name);
    }
}

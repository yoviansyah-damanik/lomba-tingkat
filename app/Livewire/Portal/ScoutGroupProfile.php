<?php

namespace App\Livewire\Portal;

use App\Models\ScoutGroup;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Profil pangkalan Gugus Depan: identitas pangkalan, nomor gudep resmi, dan akun penanggung jawab
 */
#[Layout('layouts.app', ['title' => 'Profil Pangkalan Gugus Depan'])]
#[Title('Profil Pangkalan Gugus Depan — LT III')]
class ScoutGroupProfile extends Component
{
    /**
     * Daftar Kwartir Ranting resmi (sama dengan form registrasi)
     */
    public const KWARRANS = [
        'Kwarran Padangsidimpuan Utara',
        'Kwarran Padangsidimpuan Selatan',
        'Kwarran Padangsidimpuan Batunadua',
        'Kwarran Padangsidimpuan Hutaimbaru',
        'Kwarran Padangsidimpuan Tenggara',
        'Kwarran Padangsidimpuan Angkola Julu',
    ];

    public string $school_name = '';

    public string $kwarran = '';

    public string $address = '';

    public string $group_number = '';

    public string $pic_name = '';

    public string $phone_number = '';

    public string $username = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        $scoutGroup = ScoutGroup::forUser(auth()->user());

        // Pisahkan alamat jalan dari sufiks ", {Kwarran}, Kota Padangsidimpuan" agar sama seperti input registrasi
        $street = (string) $scoutGroup->address;
        $suffix = ', '.$scoutGroup->kwarran_name.', Kota Padangsidimpuan';
        if ($scoutGroup->kwarran_name && str_ends_with($street, $suffix)) {
            $street = substr($street, 0, -strlen($suffix));
        }

        $this->school_name = (string) $scoutGroup->school_name;
        $this->kwarran = (string) $scoutGroup->kwarran_name;
        $this->address = $street;
        $this->group_number = (string) ($scoutGroup->male_group_number ?? $scoutGroup->group_number);
        $this->pic_name = (string) $scoutGroup->contact_person;
        $this->phone_number = (string) $scoutGroup->contact_phone;
        $this->username = (string) auth()->user()->username;
    }

    public function save()
    {
        $scoutGroup = ScoutGroup::forUser(auth()->user());

        if (! $scoutGroup->isEditable()) {
            $this->addError('status', 'Data terkunci dan tidak dapat diubah karena sedang dalam proses verifikasi atau telah disetujui.');

            return;
        }

        $user = auth()->user();

        $validated = $this->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'group_number' => ['required', 'string', 'max:100'],
            'kwarran' => ['required', 'string', 'in:'.implode(',', self::KWARRANS)],
            'address' => ['required', 'string', 'max:500'],
            'pic_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:30'],
            'username' => User::usernameRules($user->id),
            'password' => ['nullable', 'string', 'confirmed', Password::defaults()],
        ], [
            'school_name.required' => 'Nama pangkalan / sekolah wajib diisi.',
            'group_number.required' => 'Nomor Gugus Depan resmi pangkalan wajib diisi.',
            'kwarran.required' => 'Pilih Kwartir Ranting (Kwarran) pangkalan.',
            'kwarran.in' => 'Kwartir Ranting (Kwarran) tidak valid.',
            'address.required' => 'Alamat lengkap pangkalan wajib diisi.',
            'pic_name.required' => 'Nama lengkap pembina penanggung jawab (PIC) wajib diisi.',
            'phone_number.required' => 'Nomor WhatsApp / HP penanggung jawab wajib diisi.',
            ...User::usernameMessages(),
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $groupNumberData = ScoutGroup::formatGroupNumbers($validated['group_number']);
        $fullAddress = trim($validated['address']).', '.$validated['kwarran'].', Kota Padangsidimpuan';

        DB::transaction(function () use ($scoutGroup, $user, $validated, $groupNumberData, $fullAddress) {
            $scoutGroup->update([
                'school_name' => $validated['school_name'],
                'group_number' => $groupNumberData['combined'],
                'male_group_number' => $groupNumberData['male'],
                'female_group_number' => $groupNumberData['female'],
                'kwarran_name' => $validated['kwarran'],
                'address' => $fullAddress,
                'contact_person' => $validated['pic_name'],
                'contact_phone' => $validated['phone_number'],
            ]);

            $userData = [
                'name' => $validated['pic_name'],
                'username' => $validated['username'],
                'phone_number' => $validated['phone_number'],
            ];
            if (! empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }
            $user->update($userData);
        });

        session()->flash('success', 'Profil pangkalan Gugus Depan berhasil diperbarui.');

        return $this->redirectRoute('scout-group.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.portal.scout-group-profile', [
            'scoutGroup' => ScoutGroup::forUser(auth()->user()),
            'kwarrans' => self::KWARRANS,
            'groupNumbers' => preg_match('/\d/', $this->group_number)
                ? ScoutGroup::formatGroupNumbers($this->group_number)
                : ['male' => '', 'female' => '', 'combined' => ''],
        ]);
    }
}

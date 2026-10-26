<?php

namespace App\Livewire\Auth;

use App\Jobs\SendOfficialRegisteredPush;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Pendaftaran mandiri aparatur (panitia dan juri) melalui tautan khusus yang dapat dibuka/ditutup Superadmin.
 * Akun dibuat nonaktif sampai disetujui Administrator pada menu Aparatur.
 */
#[Layout('layouts.public', ['title' => 'Pendaftaran Aparatur Panitia & Juri'])]
#[Title('Pendaftaran Aparatur Panitia & Juri')]
class OfficialRegistrationForm extends Component
{
    use WithFileUploads;

    private const MAX_ATTEMPTS_PER_HOUR = 5;

    /** Kode rahasia dari tautan; tidak cocok berarti halaman dianggap tidak ada */
    #[Locked]
    public string $token = '';

    public string $name = '';

    public string $phone_number = '';

    public string $scout_id = '';

    public string $institution = '';

    public string $position = '';

    /** Posisi kepanitiaan (wajib bila memilih peran Panitia); tampil pada ID Card */
    public string $committee_position = '';

    /** @var array<int, string> */
    public array $roles = [];

    /** Pas foto 3x4 (disimpan sebagai avatar akun) */
    public $photo = null;

    /** ID aparatur (username login) setelah pendaftaran berhasil */
    #[Locked]
    public ?string $registeredId = null;

    /** Kata sandi awal bawaan sistem; hanya ditampilkan sekali setelah pendaftaran berhasil */
    #[Locked]
    public ?string $registeredPassword = null;

    public function mount(string $token): void
    {
        abort_unless($this->event()?->matchesOfficialRegistrationToken($token), 404);

        $this->token = $token;
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:30'],
            'scout_id' => ['required', 'string', 'max:50'],
            'institution' => ['required', 'string', 'max:150'],
            'position' => ['required', 'string', 'max:100'],
            'committee_position' => [in_array('committee', $this->roles, true) ? 'required' : 'nullable', 'string', 'max:100'],
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['in:committee,judge'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'phone_number.required' => 'Nomor telepon/WhatsApp wajib diisi.',
            'scout_id.required' => 'NTA wajib diisi.',
            'position.required' => 'Jabatan wajib diisi.',
            'committee_position.required' => 'Posisi kepanitiaan wajib diisi untuk peran Panitia.',
            'photo.required' => 'Pas foto 3x4 wajib diunggah.',
            'photo.image' => 'Pas foto harus berupa gambar.',
            'photo.mimes' => 'Pas foto harus berformat JPG atau PNG.',
            'photo.max' => 'Ukuran pas foto maksimal 2 MB.',
            'institution.required' => 'Instansi/kwartir wajib diisi.',
            'roles.required' => 'Pilih minimal satu peran (Panitia atau Juri).',
        ];
    }

    private function event(): ?Event
    {
        return Event::where('is_active', true)->first();
    }

    private function throttleKey(): string
    {
        return 'official-registration:'.request()->ip();
    }

    public function register(): void
    {
        // Gerbang dicek ulang saat submit karena tautan dapat ditutup atau kodenya diganti saat formulir masih terbuka
        $event = $this->event();
        abort_unless($event?->matchesOfficialRegistrationToken($this->token), 404);

        if (! $event->isOfficialRegistrationOpen()) {
            $this->addError('name', 'Pendaftaran aparatur sedang ditutup.');

            return;
        }

        if (RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_ATTEMPTS_PER_HOUR)) {
            $this->addError('name', 'Terlalu banyak pendaftaran dari jaringan ini. Coba lagi nanti.');

            return;
        }

        $data = $this->validate();
        RateLimiter::hit($this->throttleKey(), 3600);

        $plainPassword = Str::password(10, letters: true, numbers: true, symbols: false, spaces: false);

        $user = DB::transaction(function () use ($data, $plainPassword) {
            $idNumber = User::generateOfficialIdNumber();

            $user = User::create([
                'name' => $data['name'],
                'id_number' => $idNumber,
                'username' => $idNumber,
                'password' => Hash::make($plainPassword),
                'must_change_password' => true,
                'phone_number' => $data['phone_number'],
                'scout_id' => $data['scout_id'],
                'institution' => $data['institution'],
                'position' => $data['position'],
                'committee_position' => in_array('committee', $data['roles'], true) ? $data['committee_position'] : null,
                'avatar_path' => $this->photo->store('avatars', 'public'),
                'is_active' => false,
                'qr_token' => (string) Str::uuid(),
                'qr_generated_at' => now(),
            ]);

            foreach ($data['roles'] as $role) {
                $user->assignRole($role);
            }

            return $user;
        });

        // Superadmin diberi tahu agar pendaftaran segera diperiksa dan akun diaktifkan
        SendOfficialRegisteredPush::dispatch($user->id);

        $this->registeredId = $user->id_number;
        $this->registeredPassword = $plainPassword;
        $this->reset(['name', 'phone_number', 'scout_id', 'institution', 'position', 'committee_position', 'roles', 'photo']);
    }

    public function render()
    {
        $event = $this->event();

        return view('livewire.auth.official-registration-form', [
            'event' => $event,
            'isOpen' => (bool) $event?->isOfficialRegistrationOpen(),
        ]);
    }
}

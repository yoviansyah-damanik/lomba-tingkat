<?php

namespace App\Livewire\Auth;

use App\Models\Event;
use App\Models\RegistrationInvitation;
use App\Models\ScoutGroup;
use App\Services\ScoutGroupRegistrationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Formulir registrasi akun pangkalan; identitas pangkalan berasal dari kode undangan dan terkunci
 */
#[Layout('layouts.public', ['title' => 'Registrasi Akun Pangkalan Gugus Depan'])]
#[Title('Registrasi Akun Pangkalan Gugus Depan')]
class ScoutGroupRegistrationForm extends Component
{
    /** Masa berlaku sesi formulir setelah kode diverifikasi (detik) */
    private const SESSION_TTL = 1800;

    public string $address = '';

    public string $pic_name = '';

    public string $phone_number = '';

    public bool $terms = false;

    public ?string $invitationId = null;

    public function mount()
    {
        $invitation = $this->resolveInvitation();

        if (! $invitation) {
            return $this->redirect(route('register.scout-group'), navigate: true);
        }

        $this->invitationId = $invitation->id;
    }

    /**
     * Undangan dari sesi; null bila sesi habis, kode dimatikan, atau sudah terpakai
     */
    private function resolveInvitation(): ?RegistrationInvitation
    {
        $state = session(InvitationCodeGate::SESSION_KEY);

        if (! is_array($state) || (time() - ($state['at'] ?? 0)) > self::SESSION_TTL) {
            session()->forget(InvitationCodeGate::SESSION_KEY);

            return null;
        }

        $invitation = RegistrationInvitation::find($state['id'] ?? null);

        return $invitation?->isUsable() ? $invitation : null;
    }

    protected function rules(): array
    {
        return [
            'address' => ['required', 'string', 'max:500'],
            'pic_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'regex:/^(\+62|62|0)8\d{7,12}$/'],
            'terms' => ['accepted'],
        ];
    }

    protected function messages(): array
    {
        return [
            'address.required' => 'Alamat lengkap pangkalan wajib diisi.',
            'pic_name.required' => 'Nama lengkap pembina penanggung jawab (PIC) wajib diisi.',
            'phone_number.required' => 'Nomor WhatsApp / HP penanggung jawab wajib diisi.',
            'phone_number.regex' => 'Format nomor HP tidak valid. Gunakan nomor Indonesia, contoh: 081234567890.',
            'terms.accepted' => 'Anda wajib menyetujui pernyataan keabsahan data pangkalan.',
        ];
    }

    public function register(ScoutGroupRegistrationService $service)
    {
        $event = Event::where('is_active', true)->first();

        if ($event && ! $event->isRegistrationOpen()) {
            $this->addError('registration_closed', 'Batas waktu pendaftaran akun Gugus Depan telah berakhir. Silakan hubungi Sekretariat Panitia Pelaksana LT III Kwarcab Kota Padangsidimpuan.');

            return;
        }

        $invitation = $this->resolveInvitation();

        if (! $invitation) {
            return $this->redirect(route('register.scout-group'), navigate: true);
        }

        $validated = $this->validate();

        try {
            $result = $service->register($invitation->id, $validated, request()->ip(), request()->userAgent());
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->errors());

            return;
        }

        session()->forget(InvitationCodeGate::SESSION_KEY);

        // Login otomatis dan tetapkan konfirmasi password agar dapat langsung ubah password
        Auth::login($result['scoutGroup']->user);
        session()->regenerate();
        session()->put('auth.password_confirmed_at', time());

        session()->flash('newly_registered_credentials', [
            'school_name' => $result['scoutGroup']->school_name,
            'gudep_id' => $result['username'],
            'username' => $result['username'],
            'password' => $result['password'],
            'pic_name' => $validated['pic_name'],
        ]);

        return $this->redirect(route('register.scout-group.success'));
    }

    public function render()
    {
        $event = Event::where('is_active', true)->first();
        $invitation = $this->invitationId ? RegistrationInvitation::find($this->invitationId) : null;

        return view('livewire.auth.scout-group-registration-form', [
            'event' => $event,
            'isRegistrationOpen' => $event ? $event->isRegistrationOpen() : config('registration.is_enabled', true),
            'invitation' => $invitation,
            'groupNumbers' => $invitation ? ScoutGroup::formatGroupNumbers($invitation->group_number) : null,
        ]);
    }
}

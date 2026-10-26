<?php

namespace App\Livewire\Auth;

use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Menampilkan kredensial akun pangkalan yang dibuat sistem setelah registrasi, lalu mengarahkan untuk mengamankan akun
 */
#[Layout('layouts.public', ['title' => 'Pendaftaran Berhasil'])]
class RegistrationSuccess extends Component
{
    /** @var array<string, string> */
    public array $credentials = [];

    public function mount()
    {
        $credentials = session('newly_registered_credentials');

        if (! $credentials) {
            return $this->redirectRoute('scout-group.index', navigate: true);
        }

        // Pertahankan sesi flash jika pengguna me-refresh halaman
        session()->reflash();

        $this->credentials = $credentials;
    }

    public function render()
    {
        return view('livewire.auth.registration-success');
    }
}
